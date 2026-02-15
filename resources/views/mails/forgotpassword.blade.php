<DOCTYPE html>
    <html lang="en-US">

    <head>
        <meta charset="utf-8">
    </head>

    <body>
        <h2 style="text-align: center; background: #a18F62; color: #fff">Forgot password OTP Mail</h2>
        <p> Your OTP is: {{ $otp }}</p>
        <p><small style="color: red">OTP expired after 60 Sec.</small></p>

        <h4 style="text-align: center; background: #a18F62; color: #fff;">Thank you. {{env('APP_NAME')}}.</h4>
    </body>

    </html>
