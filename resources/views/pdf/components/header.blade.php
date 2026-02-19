<!DOCTYPE html>
<html>
<head>
    <style>
        * {
            color: #000 !important;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0; /* remove margin because wkhtmltopdf already applies spacing */
            padding: 0;
        }

        .header-wrapper {
            width: 100%;
            text-align: center;
            position: relative;
        }

        .header-logo {
            display: block;
            margin: 0 auto;
            width: 120px;
            max-width: 100%;
            height: auto;
        }

        .header-info {
            margin-top: 1px;
            font-size: 12px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="header-wrapper">
    <img src="{{ public_path('image/logos/logo-short.png') }}" class="header-logo" />
    <div class="header-info">
        <p style="text-transform: uppercase; margin: 0;">
            <b>ARMED FORCES AND POLICE SAVINGS AND LOAN ASSOCIATION, INC.</b>
        </p>
        <p style="margin: 0;"> (Authorized By the Bangko Sentral Ng Pilipinas) </p>
        <p style="margin: 0;"> Capinpin Avenue, EDSA, Cor. Bonny Serrano Road, Camp Aguinaldo, Quezon City </p>
    </div>
</div>
<br>
</body>
</html>
