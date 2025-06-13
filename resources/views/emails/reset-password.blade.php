<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #e8e8e8;
            border-radius: 5px;
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #e8e8e8;
        }
        .content {
            padding: 20px 0;
        }
        .button {
            display: inline-block;
            background-color: #4f46e5;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
        }
        .footer {
            padding-top: 20px;
            border-top: 1px solid #e8e8e8;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Reset Password</h2>
        </div>
        <div class="content">
            <p>Halo {{ $Pelanggan->nama_pelanggan }},</p>
            <p>Anda menerima email ini karena kami mendapatkan permintaan reset password untuk akun Anda.</p>
            <p>Klik tombol di bawah ini untuk mereset password Anda:</p>
            <p style="text-align: center; margin: 30px 0;">
                <a href="{{ url('reset-password/' . $token) }}" class="button">Reset Password</a>
            </p>
            <p>Link reset password ini akan kedaluwarsa dalam 24 jam.</p>
            <p>Jika Anda tidak meminta reset password, Anda tidak perlu melakukan apa pun.</p>
            <p>Salam,<br>Tim Aplikasi</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Aplikasi Anda. All rights reserved.</p>
        </div>
    </div>
</body>
</html>