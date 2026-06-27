<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta charset="UTF-8" />
        <title>{{$data['report_type']}}</title>
        <link rel="stylesheet" href="{{ base_path('public/css/bootstrap_trimmed.min.css') }}" />
        <link rel="stylesheet" href="{{ base_path('public/css/custom.css') }}" />
    </head>
    <body>
        {{-- DETAILED ASSESSMENTS --}}
        @include('pdf.reports.partials.committee-self-assessment-detailed')

        {{-- SUMMARY ASSESSMENTS --}}
        @include('pdf.reports.partials.committee-self-assessment-summary')

        {{-- CROSSWISE OVERALL SUMMARY --}}
        @include('pdf.reports.partials.committee-self-assessment-crosswise')
    </body>
</html>
