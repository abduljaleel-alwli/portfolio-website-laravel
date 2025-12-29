<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>{{ $siteName }}</title>
</head>

<body style="
    margin:0;
    padding:0;
    background-color:#f4f6f8;
    font-family:Tahoma, Arial, sans-serif;
">

<table width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0;">
    <tr>
        <td align="center">

            <!-- Container -->
            <table width="600" cellpadding="0" cellspacing="0" style="
                background-color:#ffffff;
                border-radius:14px;
                overflow:hidden;
                box-shadow:0 10px 30px rgba(0,0,0,0.05);
            ">

                <!-- Header -->
                <tr>
                    <td style="
                        background-color:{{ $secondary }};
                        padding:24px;
                        text-align:center;
                    ">

                        @if (!empty($logoUrl))
                            <img src="{{ $logoUrl }}"
                                 alt="{{ $siteName }}"
                                 style="
                                     max-width:120px;
                                     max-height:60px;
                                     display:block;
                                     margin:0 auto 12px auto;
                                 ">
                        @endif

                        <h1 style="
                            margin:0;
                            color:#ffffff;
                            font-size:22px;
                            font-weight:600;
                            letter-spacing:0.3px;
                        ">
                            {{ $siteName }}
                        </h1>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="
                        padding:28px;
                        color:#1f2937;
                        font-size:14px;
                        line-height:1.9;
                    ">

                        <p style="margin-top:0;">
                            {{ __('Hello') }} <strong>{{ $contact->name }}</strong>,
                        </p>

                        <p>
                            {{ __('Thank you for contacting us. We have reviewed your message and are happy to share our response below:') }}
                        </p>

                        <!-- Reply Box -->
                        <div style="
                            background-color:#f8fafc;
                            border-left:4px solid {{ $accent }};
                            padding:18px;
                            border-radius:10px;
                            margin:24px 0;
                            color:#0f172a;
                        ">
                            {!! nl2br(e($reply)) !!}
                        </div>

                        <hr style="
                            border:none;
                            border-top:1px solid #e5e7eb;
                            margin:28px 0;
                        ">

                        <p style="
                            font-size:13px;
                            color:#64748b;
                            margin-bottom:0;
                        ">
                            <strong style="color:#334155;">
                                {{ __('Your original message:') }}
                            </strong><br><br>
                            {{ $contact->message }}
                        </p>

                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="
                        background-color:#f9fafb;
                        padding:18px;
                        text-align:center;
                        font-size:12px;
                        color:#6b7280;
                        line-height:1.6;
                    ">
                        <strong style="color:#374151;">
                            {{ $siteName }}
                        </strong><br>
                        {{ $location }}<br>
                        {{ $phone }}
                    </td>
                </tr>

            </table>
            <!-- /Container -->

        </td>
    </tr>
</table>

</body>
</html>
