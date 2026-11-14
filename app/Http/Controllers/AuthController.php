<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function handle(Request $request, string $action)
    {
        if ($action === 'register') {
            return $this->register($request);
        }
        if ($action === 'login') {
            return $this->login($request);
        }
        if ($action === 'logout') {
            return $this->logout($request);
        }
        if ($action === 'resend') {
            return $this->resend($request);
        }
        abort(404);
    }

    public function register(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('auth.register');
        }

        $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $code = (string) random_int(100000, 999999);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'user',
            'is_active' => false,
            'verify_code' => $code,
            'verify_attempts' => 0,
        ]);

        session(['pending_user_id' => $user->id]);

        try {
            Mail::html("<h2>PlayGame Platform</h2><p>Ваш шестизначный код подтверждения:</p><h1 style='color:#3b82f6;letter-spacing:5px;'>{$code}</h1><p>Введите его на сайте для активации аккаунта.</p>", function ($msg) use ($user, $code) {
                $msg->to($user->email)->subject("PlayGame - Код подтверждения: {$code}");
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return redirect()->route('verify')->with('success', 'Verification code sent to your email.');
    }

    public function verify(Request $request)
    {
        $userId = session('pending_user_id') ?? auth()->id();
        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('auth', ['action' => 'login']);
        }

        if ($request->isMethod('get')) {
            return view('auth.verify', compact('user'));
        }

        $request->validate([
            'code' => 'required|digits:6',
        ]);

        if ($user->verify_attempts >= 5) {
            return back()->withErrors(['code' => 'Too many attempts. Request a new code.']);
        }

        if ($user->verify_code !== $request->code) {
            $user->increment('verify_attempts');
            return back()->withErrors(['code' => 'Invalid confirmation code.']);
        }

        $user->update([
            'is_active' => true,
            'verify_code' => null,
            'verify_attempts' => 0,
            'email_verified_at' => now(),
        ]);

        session()->forget('pending_user_id');
        Auth::login($user);

        return redirect()->route('download.index')->with('success', 'Account activated successfully!');
    }

    public function resend(Request $request)
    {
        $userId = session('pending_user_id') ?? auth()->id();
        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('auth', ['action' => 'login']);
        }

        $throttleKey = 'resend-code:' . $user->id;
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            return back()->withErrors(['code' => 'Please wait before requesting a new code.']);
        }
        RateLimiter::hit($throttleKey, 60);

        $code = (string) random_int(100000, 999999);
        $user->update([
            'verify_code' => $code,
            'verify_attempts' => 0,
        ]);

        try {
            Mail::html("<h2>PlayGame Platform</h2><p>Ваш новый шестизначный код подтверждения:</p><h1 style='color:#3b82f6;letter-spacing:5px;'>{$code}</h1>", function ($msg) use ($user, $code) {
                $msg->to($user->email)->subject("PlayGame - Новый код подтверждения: {$code}");
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return back()->with('success', 'New verification code sent!');
    }

    public function login(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('auth.login');
        }

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $throttleKey = 'login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return back()->withErrors(['email' => 'Too many failed login attempts. Try again later.']);
        }

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 60);
            return back()->withErrors(['email' => 'Invalid email or password.']);
        }

        if (!$user->is_active) {
            session(['pending_user_id' => $user->id]);
            return redirect()->route('verify')->with('warning', 'Please verify your email address first.');
        }

        RateLimiter::clear($throttleKey);
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('download.index'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('download.index');
    }
}
