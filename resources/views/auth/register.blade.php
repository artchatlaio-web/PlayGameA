@extends('layouts.app')

@section('content')
<div class="glass-card auth-box">
    <h2 style="font-size:1.5rem; font-weight:700; margin-bottom:1.5rem; text-align:center;">{{ __('Register') }}</h2>
    <form action="{{ route('auth', ['action' => 'register']) }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label">{{ __('Name') }}</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
        </div>
        <div class="form-group">
            <label class="form-label">{{ __('Email') }}</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">{{ __('Password') }}</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label class="form-label">{{ __('Confirm Password') }}</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">{{ __('Register') }}</button>
        <div style="text-align:center; margin-top:1.2rem; font-size:0.85rem; color:#94a3b8;">
            Already have an account? <a href="{{ route('auth', ['action' => 'login']) }}" style="color:#60a5fa;">{{ __('Login') }}</a>
        </div>
    </form>
</div>
@endsection

