@extends('layouts.app')

@section('content')
<div class="glass-card auth-box">
    <h2 style="font-size:1.5rem; font-weight:700; margin-bottom:0.6rem; text-align:center;">{{ __('Verify Email') }}</h2>
    <p style="text-align:center; font-size:0.88rem; color:#94a3b8; margin-bottom:1.5rem;">
        Enter the 6-digit confirmation code sent to <strong style="color:#f8fafc;">{{ $user->email ?? 'your email' }}</strong>
    </p>

    <form action="{{ route('verify') }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label">{{ __('Verification Code') }}</label>
            <input type="text" name="code" class="form-control" maxlength="6" placeholder="123456" required style="letter-spacing:6px; font-size:1.4rem; text-align:center; font-weight:700;" autofocus>
        </div>
        <button type="submit" class="btn btn-primary btn-block">{{ __('Verify Email') }}</button>
    </form>

    @if(config('mail.default') === 'log' || config('app.debug'))
        <div style="margin-top:1rem; padding:0.6rem; background:rgba(59,130,246,0.1); border:1px dashed rgba(59,130,246,0.4); border-radius:8px; font-size:0.82rem; color:#93c5fd; text-align:center;">
            Код подтверждения: <strong style="letter-spacing:3px; color:#fff; font-size:1rem;">{{ $user->verify_code }}</strong>
        </div>
    @endif

    <div style="text-align:center; margin-top:1.5rem; border-top:1px solid rgba(255,255,255,0.1); padding-top:1.2rem;">
        <form action="{{ route('auth', ['action' => 'resend']) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline btn-sm">{{ __('Resend Code') }}</button>
        </form>
    </div>
</div>
@endsection
