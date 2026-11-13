<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ $subject ?? 'UniGo' }}</title>
</head>

<body style="margin:0;padding:0;background-color:#f3f6fa;font-family:Arial,Helvetica,sans-serif;color:#24364b;">

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#f3f6fa"
        style="width:100%;background-color:#f3f6fa;padding:35px 12px;">
        <tr>
            <td align="center">

                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0"
                    bgcolor="#ffffff"
                    style="width:100%;max-width:600px;background-color:#ffffff;border-radius:12px;overflow:hidden;">

                    <tr>
                        <td align="center" bgcolor="#ffffff"
                            style="background-color:#ffffff;padding:25px 30px;border-bottom:1px solid #edf0f4;">
                            <a href="https://unigo-bd.com" target="_blank" style="text-decoration:none;">
                                <img src="https://unigo-bd.com/images/unigo-logo.png" alt="UniGo" width="190"
                                    style="display:block;width:190px;max-width:100%;height:auto;border:0;outline:none;text-decoration:none;">
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td bgcolor="#ffffff" style="background-color:#ffffff;padding:38px 35px 35px;color:#24364b;">
                            @yield('content')
                        </td>
                    </tr>

                    <tr>
                        <td align="center" bgcolor="#082b4c" style="background-color:#082b4c;padding:30px 20px;">

                            <a href="https://unigo-bd.com" target="_blank" style="text-decoration:none;">
                                <img src="https://unigo-bd.com/images/unigo-footer-logo.png" alt="UniGo"
                                    width="145"
                                    style="display:block;width:145px;max-width:100%;height:auto;border:0;outline:none;text-decoration:none;margin:0 auto 17px;">
                            </a>

                            <p style="margin:0 0 10px;font-size:12px;line-height:20px;color:#ffffff;">
                                International Education &amp; Travel
                            </p>

                            <p style="margin:0;font-size:12px;line-height:20px;">
                                <a href="https://unigo-bd.com" target="_blank"
                                    style="color:#ffffff;text-decoration:none;">
                                    www.unigo-bd.com
                                </a>
                                <span style="color:#94a3b8;">&nbsp;&nbsp;|&nbsp;&nbsp;</span>
                                <a href="mailto:contact@unigo-bd.com" style="color:#ffffff;text-decoration:none;">
                                    Contact Us
                                </a>
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="padding-top:18px;">
                                        <div style="height:1px;background-color:#294968;font-size:1px;line-height:1px;">
                                            &nbsp;
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:17px 0 0;font-size:10px;line-height:17px;color:#cbd5e1;">
                                This is an automated message from UniGo.
                                Please contact our team if you need assistance.
                            </p>

                            <p style="margin:8px 0 0;font-size:10px;line-height:17px;color:#94a3b8;">
                                &copy; {{ date('Y') }} UniGo. All rights reserved.
                            </p>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>
