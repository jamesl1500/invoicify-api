<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Onboarding Notification</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
        <h2 style="color: #17332d;">Welcome to {{ env("APP_NAME") }}!</h2>
        <p>Dear {{ $client->name }},</p>
        <p>You're receiving this email because {{ $user->name }} has added you as one of their clients!</p>
        <p>Here are your onboarding details:</p>
        <ul>
            <li><strong>Account Name:</strong> {{ $client->name }}</li>
            <li><strong>Account Email:</strong> {{ $client->email }}</li>
            <li><strong>Account Phone:</strong> {{ $client->phone }}</li>
            <li><strong>Account Address:</strong> {{ $client->address }}</li>
        </ul>
        <p>To get started, please log in to your account using the link below:</p>
        <p><a href="{{ env("FRONTEND_URL") }}/client/login?onboard_token={{ $client->onboard_token }}"  style="background-color: #17332d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">Start onboarding</a></p>
        <p>We look forward to working with you!</p>
        <p>Best regards,</p>
        <p><strong>The {{ env("APP_NAME") }} Team</strong></p>
    </div>
</body>
</html>