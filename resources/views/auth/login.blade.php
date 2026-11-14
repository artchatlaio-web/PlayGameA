@extends('layouts.app')

@section('content')
<div class="glass-card auth-box">
    <h2 style="font-size:1.5rem; font-weight:700; margin-bottom:1.5rem; text-align:center;">{{ __('Login') }}</h2>
    <form action="{{ route('auth', ['action' => 'login']) }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label">{{ __('Email') }}</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
        </div>
        <div class="form-group">
            <label class="form-label">{{ __('Password') }}</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-group" style="display:flex; align-items:center; gap:0.5rem;">
            <input type="checkbox" name="remember" id="remember">
            <label for="remember" style="font-size:0.85rem; color:#94a3b8; cursor:pointer;">Remember me</label>
        </div>
        <button type="submit" class="btn btn-primary btn-block">{{ __('Login') }}</button>
        <div style="text-align:center; margin-top:1.2rem; font-size:0.85rem; color:#94a3b8;">
            Don't have an account? <a href="{{ route('auth', ['action' => 'register']) }}" style="color:#60a5fa;">{{ __('Register') }}</a>
        </div>
    </form>
</div>
@endsection

