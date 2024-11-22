<!DOCTYPE html>
<html>
<head>
    <title>Welcome to CoreXgen</title>
</head>
<body>
    <h1>Welcome, {{ $details['name'] }}</h1>
    <p>Thank you for setting up as a Super Admin at CoreXgen!</p>
    <p>Here are your details:</p>
    <ul>
        <li>Email: {{ $details['email'] }}</li>
        <li>Password: As Defined</li>
        <li>Identification Number (for login as user): {{ $details['buyer_id'] }}</li>
    </ul>
    <p>We are excited to have you on board. Let us know if you need any assistance.</p>
    <p>Thank you, <br> The CoreXgen Team</p>
</body>
</html>
