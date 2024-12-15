@extends('layout.auth')
@section('content')
    @if (session('status'))
        <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
            {{ session('status') }}
        </div>
    @endif
    <h2>Login</h2>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        @if (isset($is_tenant) && $is_tenant)
            <input type='hidden' name='is_tenant' value='true' />
            <input type='hidden' name='path' value="{{ $path }}" />
        @endif
        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input type="email" id="email" name="email" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">{{ __('Password') }}</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" id="remember" name="remember" class="form-check-input">
            <label for="remember" class="form-check-label">{{ __('Remember Me') }}</label>
        </div>
        <button type="submit" class="auth-button w-100">{{ __('Log in') }}</button>
    </form>
    @if (Route::has('password.request'))
        <div class="mt-3 text-center">
            <p class="small">
                <a class="link" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            </p>
        </div>
    @endif
@endsection
