<!DOCTYPE html>
<html>

    <head>
        <title>
            {{ env('APP_NAME') }}
        </title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta http-equiv="Content-Type" content="text/html; ">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style type="text/css">
            #outlook a {
                padding: 0;
            }

            body {
                margin: 0;
                padding: 0;
                -webkit-text-size-adjust: 100%;
                -ms-text-size-adjust: 100%;
            }

            table,
            td {
                border-collapse: collapse;
                mso-table-lspace: 0pt;
                mso-table-rspace: 0pt;
            }

            img {
                border: 0;
                height: auto;
                line-height: 100%;
                outline: none;
                text-decoration: none;
                -ms-interpolation-mode: bicubic;
            }

            p {
                display: block;
                margin: 13px 0;
            }
        </style>

        <style type="text/css">
            @import url(https://fonts.googleapis.com/css?family=Lato:300,400,500,700);
        </style>
        <style type="text/css">
            @media only screen and (min-width:480px) {
                .mj-column-per-100 {
                    width: 100% !important;
                    max-width: 100%;
                }

                .mj-column-per-50 {
                    width: 50% !important;
                    max-width: 50%;
                }
            }
        </style>
        <style media="screen and (min-width:480px)">
            .moz-text-html .mj-column-per-100 {
                width: 100% !important;
                max-width: 100%;
            }

            .moz-text-html .mj-column-per-50 {
                width: 50% !important;
                max-width: 50%;
            }
        </style>
        <style type="text/css">
            @media only screen and (max-width:480px) {
                table.mj-full-width-mobile {
                    width: 100% !important;
                }

                td.mj-full-width-mobile {
                    width: auto !important;
                }
            }
        </style>
        <style type="text/css">
            body {
                font-family: Lato, 'Lucida Grande', 'Lucida Sans Unicode', Tahoma, sans-serif;
                font-size: 18px;
                line-height: 1.5;
                color: #e3e4e8;
            }

            img {
                border: 0;
                height: auto;
                line-height: 100%;
                outline: none;
                text-decoration: none;
                max-width: 100%;
            }

            p,
            li {
                color: #e3e4e8;
                line-height: 1.5;
                font-size: 18px;
                margin: 0 0 15px 0;
            }

            li {
                margin-bottom: 10px;
            }

            blockquote {
                background: none;
                border-left: 1px solid gray;
                padding-left: 10px;
                margin: 0 0 15px 10px;
            }

            h1,
            h2,
            h3 {
                color: white;
            }

            h1 {
                font-size: 28px;
                line-height: 1.2;
            }

            h2 {
                font-size: 26px;
                margin: 0;
                line-height: 1.2;
            }

            h3 {
                font-size: 24px;
                margin: 20px 0 10px 0;
                line-height: 1.2;
            }

            @media only screen and (max-width: 400px) {
                h1 {
                    font-size: 22px;
                }

                p {
                    font-size: 14px;
                }


            }
        </style>
    </head>

    <body style="word-spacing:normal;background-color:#1c1d22;">
        <div style="background:#434857;background-color:#434857;margin:0px auto;max-width:600px;">
            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation"
                style="background:#000;background-color:#000;width:100%;">
                <tbody>
                    <tr>
                        <td style="direction:ltr;font-size:0px;padding:0;text-align:center;">
                            <div class="mj-column-per-100 mj-outlook-group-fix"
                                style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                    style="vertical-align:top;" width="100%">
                                    <tbody>
                                        <tr>
                                            <td align="center" style="font-size:0px;padding:0;word-break:break-word;">
                                                <table border="0" cellpadding="0" cellspacing="0"
                                                    role="presentation"
                                                    style="border-collapse:collapse;border-spacing:0px;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="width:600px;">
                                                                <img height="auto"
                                                                    src="{{ asset('/public/logo.png') }}"
                                                                    style="border:0;display:block;outline:none;margin: 0 auto;padding-top: 30px;margin-bottom: 30px;"
                                                                    width="auto">
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="left"
                                                style="font-size:0px;padding:10px 25px; background: #ae860c;">
                                                <div
                                                    style="font-family:'Lato', system-ui, sans-serif;text-align:center;">
                                                    <h1 style="margin: 0;">Registration Completion With {{ env('APP_NAME') }}.</h1>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td align="left"
                                                style="font-size:0px;padding:10px 25px;padding-top:30px;padding-right:35px;padding-bottom:0;padding-left:35px;word-break:break-word;">
                                                <div
                                                    style="font-family:'Lato', system-ui, sans-serif;font-size:13px;line-height:125%;text-align:left;color:#e7f0ff;">
                                                    <p>Hi {{ $full_name }},</p>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="left"
                                                style="padding:10px 25px;padding-top:0;padding-right:35px;padding-left:35px;word-break:break-word;">
                                                <div style="font-family:Lato, system-ui, sans-serif;font-size:13px;text-align:left;color:white;"
                                                    class="news-content">
                                                    <p>{{ $content }}</p>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="left"
                                                style="padding:5px 25px;padding-top:0;padding-right:35px;padding-left:35px;word-break:break-word;">
                                                <div style="font-family:Lato, system-ui, sans-serif;font-size:13px;text-align:left;color:white;"
                                                    class="news-content">
                                                    <p style="margin-bottom: 6px;"> Registered Username: {{ $email }}</p>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="left"
                                                style="padding:5px 25px;padding-top:0;padding-right:35px;padding-left:35px;word-break:break-word;">
                                                <div style="font-family:Lato, system-ui, sans-serif;font-size:13px;text-align:left;color:white;"
                                                    class="news-content">
                                                    <p style="margin-bottom: 6px;"> Login Password: {{ !empty($pwd) ? $pwd : 'Password enter in registration step 1.'}}
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="left"
                                                style="padding:10px 25px;padding-top:0;padding-right:35px;padding-left:35px;word-break:break-word;">
                                                <div style="font-family:Lato, system-ui, sans-serif;font-size:13px;text-align:left;color:white;"
                                                    class="news-content">
                                                    <p>Thank you,</p>
                                                    <p>{{ env('APP_NAME') }}</p>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!--[if mso | IE]></td></tr></table><![endif]-->
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </body>

</html>
