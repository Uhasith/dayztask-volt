<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,500;0,600;1,600&family=Roboto+Slab:wght@500;700;800&display=swap" rel="stylesheet">
    <style>
        .montserrat {
            font-family: 'Montserrat', sans-serif !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .responsive-container {
                width: 80%;
            }
        }
    </style>
</head>

<body>
    <div style="display: flex; align-items: center; justify-content: center; flex-direction: column; background-color: #b3e0ff; margin: auto; padding: 20px;">
        <div class="responsive-container" style="max-width: 1024px; margin: auto;">
            <!-- add dayz logo when putting it to live -->
            <img style="width: 100%; max-width: 250px;" src="{{ asset('assets/images/logo.png') }}" alt="email_logo">
        </div>
        <div class="responsive-container email-content" style="margin: auto; flex-direction: column; border: 3px solid #1a75ff; border-radius: 12px; background-color: #ffffff; padding: 20px;">
            <div style="display: flex; flex-direction: column; align-items: center; ">

                <div style="width: 100%; display: flex; flex-direction: column; align-items: center; margin-bottom: 10px;">
                    <!-- add dayz logo when putting it to live -->
                    <img style="width: 100%; max-width: 150px; padding: 25px;" src="{{ asset('assets/images/emailLogo.png') }}" alt="email_logo">
                    <h1 class="montserrat" style="margin: 10px 0; font-weight: 700; text-align: center; font-size: 24px;">{!! $email_subject !!}</h1>
                    <hr style="width: 100%;">
                </div>

                <div>
                    <h1 class="montserrat" style="margin-bottom: 5px; font-weight: 500; text-align: center; font-size: 20px;">Hello {{ explode(' ', $user->name)[0] }}</h1>
                    <p class="montserrat" style="text-align: center; font-size: 18px;">{!! $email_body !!}</p>
                </div>

                <p class="montserrat" style="text-align: center; font-size: 14px;">Go to your Task by <a href="https://dayztasks.com/tasks/edit/{{$task->uuid}}" style="text-decoration: underline; color: #2463EB;">Clicking here.</a></p>
            </div>
        </div>

        <div class="responsive-container" style="margin: 20px auto; border: 3px solid #1a75ff; border-radius: 12px; background-color: #ffffff; padding: 20px;">
            <div style="display: flex; flex-direction: column; align-items: center;">
                <div class="social-icons" style="display: flex; gap: 1.5rem;">
                    <!-- Social media icons -->
                    <!-- Add your social media icons here -->
                </div>

                <div style="display: flex; padding: 10px; justify-content: center; align-items: center;">
                    <p class="montserrat" style="font-weight: 500; text-align: center; font-size: 14px;">
                        Don't want these mails anymore? Change your settings <a href="#">Click here.</a>
                    </p>
                </div>

                <div style="display: flex; justify-content: center; align-items: center;">
                    <p class="montserrat" style="font-weight: 400; text-align: center; font-size: 14px;">
                        © Dayz Solutions || 2024
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
