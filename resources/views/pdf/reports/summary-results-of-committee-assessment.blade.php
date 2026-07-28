<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta charset="UTF-8" />
        <title>{{$data['report_type']}}</title>
        <link rel="stylesheet" href="{{ base_path('public/css/bootstrap_trimmed.min.css') }}" />
        <link rel="stylesheet" href="{{ base_path('public/css/custom.css') }}" />
        <style>
            /* Disable header repetition - treat thead as regular row group */
            thead { display: table-row-group; }
            tfoot { display: table-row-group; }

            /* Prevent rows from breaking - keep entire row together */
            tr { page-break-inside: avoid; }
            tbody tr { page-break-inside: avoid; }

            /* PDF Header Class - Fixed height with responsive text */
            .pdf-header {
                height: 60px;
                padding: 2px !important;
                vertical-align: middle;
                word-break: break-word;
                overflow-wrap: break-word;
                line-height: 1.2;
                font-size: 15px;
            }

            /* Body cells - minimal styling */
            tbody td, tbody th {
                vertical-align: top;
            }
        </style>
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
