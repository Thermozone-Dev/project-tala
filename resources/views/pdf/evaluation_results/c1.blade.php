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
            font-weight: 700;
            text-align:left;
        }

        footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 20%;
        }

    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="page">
            <div class="content" style="text-align: justify;">
                <div class="row" class="col-xs-12">
                    <h3 class="header3">
                        BOT EVALUATION FORM C.1 - CHAIRMAN OF THE BOARD
                    </h3>
                    @include('pdf.components.trustee_header')
                    <div style="padding-top: 10px">
                        <p>
                            <em>
                                This questionnaire is meant to get feedback on the quality of performance of the concerned Trustee both as a member of the Board and BOT Committee.  Responses will be used as basis for possible qualification or disqualification for nomination for the immediately succeeding term as a member of the AFPSLAI Board of Trustees.
                            </em>
                        </p>
                    </div>
                    @include('pdf.components.rating_scale_1')
                </div>
                @foreach ($sections as $section)
                    <div class="row" style="padding-top: 10px" class="col-xs-12">
                        @include('pdf.components.questionaire_1', ['section_data' => $section])
                    </div>
                @endforeach
                <div class="row" style="padding-top: 10px" class="col-xs-12">
                    @include('pdf.components.attendance')
                </div>
                <div class="row" style="padding-top: 10px" class="col-xs-12">
                    @include('pdf.components.attendance_rating_scale')
                </div>
            </div>
        </div>

        <div class="page">
            <div class="page">
                @include('pdf.components.other_comments')

                <div style="margin-top:20px;">
                    @include('pdf.components.cforms_footer')
                </div>
            </div>

        </div>

    </div>
</body>

</html>
