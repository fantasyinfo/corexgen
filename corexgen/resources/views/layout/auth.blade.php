<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap/bootstrap.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/fontawesome/css/all.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/custom/colors.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/custom/style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/custom/custom.css') }}" />
    <style>
        body {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            /* color: #fff; */
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            margin: 0;
        }

        /* Background Image with Gradient Overlay */
        body::before {
            content: "";
            background: url('./img/bg.jpg') center center/cover no-repeat;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.3;
            z-index: -1;
        }

        .auth-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .auth-header img {
            max-width: 100px;
        }

        .auth-button {
            background-color: var(--primary-color);
            border: none;
            color: #fff;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .auth-button:hover {
            background-color: var(--primary-hover);
        }

        .form-check-label {
            color: var(--neutral-gray);
        }
    </style>
</head>

<body>
    <div class="auth-card">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="auth-header">
            {{ config('app.name') }}
        </div>

        @yield('content')

        <div class="mt-3 text-center">
            <p class="small">
                <a href="{{config('app.contact_link')}}"
                    class="text-decoration-none">{{ __('Need Help?') }}</a>
            </p>
        </div>
    </div>

    <script src="{{ asset('js/boostrap/bootstrap.bundle.min.js') }}"></script>
</body>

</html>
