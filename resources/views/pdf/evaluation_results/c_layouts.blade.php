<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{config('app.name')}}</title>
    <link rel="stylesheet" href="{{base_path('public/css/bootstrap_trimmed.min.css')}}" />
    <style type="text/css">
        *{
            color: #000 !important;
            font-size: 15px;

        }
        html {
            height:100%;
        }
        body {
            height:100%;
            font-family: 'DejaVu Sans', sans-serif;
            margin-left: 0.3in;
            margin-right: 0.3in;
        }
        h1 {
            font-size: 2em;
            font-weight: bold;
        }
        .vcenter {
            display: inline-block;
            vertical-align: middle;
            float: none;
        }
        .row {
            margin-bottom: 5px;
        }
        .page {
            page-break-after:always;

        }
        .overlay{
            position: relative;
        }
        .test{
            position: absolute;
            bottom: 1px; /* slight padding from bottom of image */
            left: 0;
            width: 100%;
            text-align: center;
        }
        .test p {
            line-height: 0.1;
        }
        .display{
            border-bottom: 1px solid black;
            text-align:center;
        }
        .no_margin{
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding: 0 !important;
        }
        .display-text{
            height: 10px;
        }
        .header3{
            font-weight: 700;
            text-align:center;
        }
        .header4{
            font-weight: 900;
            font-size: 18px;
            text-align:left;
        }
        table tr td {
            padding: 5px;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        @yield('content')
    </div>
</body>

</html>
