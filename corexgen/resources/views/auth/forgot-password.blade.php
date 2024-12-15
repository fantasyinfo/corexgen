@extends('layout.auth')
@section('content')
    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
        @endif
    <h2>Forgot Password</h2>
    <p class="my-3 ">
            
    {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </p>
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
 
        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input type="email" id="email" name="email" class="form-control" required autofocus>
        </div>

        <button type="submit" class="auth-button w-100">{{ __('Email Password Reset Link') }}</button>
    </form>

@endsection
