<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; background-color: #f4f4f4; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <!-- Header -->
        <header style="background-color: #2d3748; padding: 20px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 24px;">{{config('app.name')}}</h1>
        </header>

        <!-- Main Content -->
        <main style="padding: 40px 20px; background-color: #ffffff;">
            <!-- Content Section -->
            <div style="background-color: #ffffff; border-radius: 5px; padding: 20px;">
                @yield('content')
            </div>
        </main>

        <!-- Footer -->
        <footer style="background-color: #f8fafc; padding: 20px; text-align: center; border-top: 1px solid #e2e8f0;">
            <p style="margin: 0; color: #718096; font-size: 14px;">Thank you for using our application!</p>
            <div style="margin-top: 15px;">
                <p style="margin: 0; color: #a0aec0; font-size: 12px;">
                    This is an automated message, please do not reply directly to this email.
                </p>
            </div>
        </footer>
    </div>
</body>
</html>
