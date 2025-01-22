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

    @if (config('app.app_status') == 'demo')
        <div class="container mt-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Demo Login Credentials</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Role</th>
                                    <th>Email</th>
                                    <th>Password</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (getModule() == 'saas')
                                    <tr>
                                        <td><span class="badge bg-success">Super Admin</span></td>
                                        <td>superadmin@example.com</td>
                                        <td>password123</td>
                                        <td>
                                            <button onclick="fillLoginForm('superadmin@example.com', 'password123')"
                                                class="btn btn-sm btn-primary">
                                                Login as Super Admin
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-info">Company Admin</span></td>
                                        <td>admin@company.com</td>
                                        <td>demo123</td>
                                        <td>
                                            <button onclick="fillLoginForm('admin@company.com', 'demo123')"
                                                class="btn btn-sm btn-primary">
                                                Login as Admin
                                            </button>
                                        </td>
                                    </tr>
                                @endif
                                @if (getModule() == 'company')
                                    <tr>
                                        <td><span class="badge bg-info">Company Admin</span></td>
                                        <td>admin@company.com</td>
                                        <td>demo123</td>
                                        <td>
                                            <button onclick="fillLoginForm('admin@company.com', 'demo123')"
                                                class="btn btn-sm btn-primary">
                                                Login as Admin
                                            </button>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @if (Route::has('password.request'))
        <div class="mt-3 text-center">
            <p class="small">
                <a class="link" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            </p>
        </div>
    @endif

    @if (getModule() == 'saas')
        <div class="text-center mt-4">
            <p>Done't have an account? <a href="/company/register" class="text-primary">Register here</a></p>
        </div>
    @endif
@endsection

<script>
    function fillLoginForm(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;

        // Optional: Automatically check the remember me box
        document.getElementById('remember').checked = true;

        // Optional: Add a visual feedback
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = 'Credentials Filled ✓';
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-success');

        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');
        }, 2000);
    }
</script>
