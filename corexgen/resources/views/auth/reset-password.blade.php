@extends('layout.auth')
@section('content')
    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
        @endif
    <h2>Set New Password</h2>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
 
        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input type="email" id="email" name="email" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">{{ __('Password') }}</label>
            <input type="password" id="password" name="password" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">{{ __('Confirm Password') }}</label>
            <input type="password" id="password" name="password_confirmation" class="form-control" required autofocus>
        </div>

        <button type="submit" class="auth-button w-100">{{ __('Reset Password') }}</button>
    </form>

@endsection
