<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('messages.password_reset') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <style>
        body {
            font-family: 'Figtree', sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
            color: #334155;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .content {
            padding: 40px 30px;
        }

        .content h2 {
            color: #1e293b;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .content p {
            line-height: 1.6;
            margin-bottom: 20px;
            color: #64748b;
        }

        .button {
            display: inline-block;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 6px;
            font-weight: 500;
            margin: 20px 0;
            text-align: center;
        }

        .button:hover {
            background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
        }

        .footer {
            background-color: #f8fafc;
            padding: 30px;
            text-align: center;
            color: #64748b;
            font-size: 14px;
        }

        .footer p {
            margin: 5px 0;
        }

        .warning {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }

        .warning p {
            margin: 0;
            color: #92400e;
            font-size: 14px;
        }

        @media (max-width: 600px) {
            .container {
                margin: 10px;
            }

            .header, .content, .footer {
                padding: 20px;
            }

            .header h1 {
                font-size: 20px;
            }

            .content h2 {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('app.name', 'Laravel') }}</h1>
        </div>

        <div class="content">
            <h2>{{ __('messages.password_reset_request') }}</h2>

            <p>{{ __('messages.hello') }},</p>

            <p>{{ __('messages.password_reset_email_text') }}</p>

            <div style="text-align: center;">
                <a href="{{ $actionUrl }}" class="button">
                    {{ __('messages.reset_password') }}
                </a>
            </div>

            <div class="warning">
                <p><strong>{{ __('messages.security_notice') }}:</strong> {{ __('messages.reset_link_expires') }}</p>
            </div>

            <p>{{ __('messages.if_not_requested') }}</p>

            <p>{{ __('messages.best_regards') }}<br>
            {{ config('app.name', 'Laravel') }} {{ __('messages.team') }}</p>
        </div>

        <div class="footer">
            <p>{{ __('messages.email_footer_text') }}</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. {{ __('messages.all_rights_reserved') }}</p>
        </div>
    </div>
</body>
</html>
