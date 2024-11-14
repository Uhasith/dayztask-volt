<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dayztasks - Team Invitation</title>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Figtree', sans-serif;
            background-color: white;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 680px;
            height: auto;
            margin: 20px auto;
        }
        .header {
            background: #E1F29680;
            padding: 16px 80px;
            text-align: center;
        }
        .header img {
            width: 144px;
            height: 24px;
        }
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            color: black;
            margin: 10px 0 5px 0;
        }
        .header p {
            color: black;
            font-size: 12px;
            margin: 0;
        }
        .content {
            padding: 24px;
            color: black;
        }
        .content p {
            font-size: 14px;
            margin: 0;
            color: black
        }
        .content ul {
            list-style-type: disc;
            padding-left: 20px;
            margin-top: 10px;
            font-size: 14px;
        }
        .button {
            background:#5BB98A;
            border-radius: 20px;
            padding: 10px 20px;
            text-align: center;
            display: inline-block;
            margin: 20px auto;
            text-decoration: none;
        }
        .button-container{
            max-width: fit-content;
            margin-left: auto;
            margin-right: auto;
        }
        .content h2{
            font-size: 24px;
            color: black;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #343A3F;
        }
        .social-links img {
            height: 24px;
            margin: 0 5px;
        }
        .sub-content p{
            color: #343A3F;
            font-size: 14px
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            {{-- <img src="{{ asset('assets/images/logo.png') }}"alt="logo"> --}}
            <h1>Welcome to Dayztasks!</h1>
            <p>Collaborate, Organize, and Achieve Together</p>
        </div>

        <!-- Content -->
        <div class="content">
            <h2>Hi {{$username}},</h2>
            <p>You’ve been invited to join our project team on Dayztasks! As a team member, you’ll have access to all the tools and resources needed to make this project a success. Here are your login details to get started:</p>

            <!-- Credentials -->
            <ul>
                <li><strong>Email:</strong> <span id="username">{{$email}}</span></li>
                <li><strong>Password:</strong> <span id="password">{{$password}}</span></li>
            </ul>

            <p class="mt-4">Log in to your account and access the Dayztasks platform through the link below:</p>

            <!-- Log in Button -->
            <div class="button-container">
                <a href="{{ $loginUrl }}" class="button" style="color:white">Join the Team</a>
            </div>

            <div class="sub-content">
                <p>We’re excited to have you onboard! If you have any questions or need assistance, don’t hesitate to reach out.</p>
                <p style="margin-top: 5px">Best Regards,</p>
                <p style="font-weight: 600; margin-top:4px">Dayztasks Team</p>
            </div>
        </div>

        {{-- <div style="width: 100%; height: 1px; background: #F2F4F8; margin: 20px 0;"></div>

        <!-- Social Links -->
        <div class="social-links" style="text-align: center;">
            <a href="#"><img src="{{asset('images/insta.png')}}" alt="Instagram"></a>
            <a href="#"><img src="{{asset('images/facebook.png')}}" alt="Facebook"></a>
        </div> --}}

        <div style="width: 100%; height: 1px; background: #F2F4F8; margin: 20px 0;"></div>

        <!-- Footer Links -->
        <div class="footer">
            <p>&copy; support@example.com. All rights reserved.</p>
            <div>
                <a href="{{ url('/html/privacy.html') }}" style="color: #016553; text-decoration: none;" target="_blank">Privacy policy</a> •
                <a href="{{ url('/html/terms-and-conditions.html') }}" style="color: #016553; text-decoration: none;" target="_blank">Terms of service</a> •
                <a href="#" style="color: #016553; text-decoration: none;">Help center</a> •
                <a href="#" style="color: #016553; text-decoration: none;">Unsubscribe</a>
            </div>
        </div>
    </div>
</body>
</html>