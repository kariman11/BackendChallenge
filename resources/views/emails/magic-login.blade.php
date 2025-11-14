<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Magic Login Link</title>
    <style>
        body {
            background: #f7f7f7;
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f7f7f7;
            padding: 20px 0;
        }
        .main {
            background: #ffffff;
            width: 100%;
            max-width: 520px;
            margin: 0 auto;
            border-radius: 8px;
            padding: 30px;
            border: 1px solid #e5e5e5;
        }
        h1 {
            color: #333;
            font-size: 22px;
            margin-bottom: 20px;
        }
        p {
            color: #555;
            font-size: 16px;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            background-color: #4F46E5;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 20px;
            margin: 20px 0;
            border-radius: 6px;
            font-weight: bold;
            text-align: center;
        }
        .footer {
            margin-top: 20px;
            font-size: 14px;
            color: #999;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="main">
        <h1>Magic Login Link</h1>

        <p>Hello!</p>

        <p>
            Click the button below to sign in instantly without using your password.
        </p>

        <p style="text-align:center;">
            <a href="{{ $url }}" class="btn">Login Now</a>
        </p>

        <p>
            This link will expire in <strong>15 minutes</strong> and can only be used <strong>once</strong>.
        </p>

        <p>
            If you didn’t request this, you can safely ignore this email.
        </p>

        <p class="footer">
            Thanks,<br>
            {{ config('app.name') }}
        </p>
    </div>
</div>

</body>
</html>
