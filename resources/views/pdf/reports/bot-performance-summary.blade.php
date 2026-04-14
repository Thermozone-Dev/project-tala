
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta charset="UTF-8" />
        <title>{{$data['report_type']}}</title>
        <link rel="stylesheet" href="{{ base_path('public/css/bootstrap_trimmed.min.css') }}" />
        <style type="text/css">

            h1 {
                font-size: 2em;
                font-weight: bold;
            }

            h3, h4, h5 {
                font-weight: bold;
            }

            .row {
                margin-bottom: 5px;
            }

            table {
                table-layout: fixed;

                width: 100%;
                border-collapse: collapse;
            }

            td, th {
                word-wrap: break-word;
                white-space: normal;
            }

            th {
                border: 1px solid black;
                text-align: center;
            }

            td {
                border: 1px solid black;
                text-align: left;
                font-size: 11.5px;
                padding: 2px;
            }

            .page {
                page-break-after: always;
                /*width: 100%;*/
            }

            .font-bold{
                font-weight: bold;
            }

            .header-color{
                background-color: #31859b;
                color: #92cddc
            }
        </style>
    </head>
    <body>
        @foreach($data['collections'] as $collection)
            <div class="container-fluid">
                <div class="page">
                    <div class="row" style="white-space:nowrap;">
                        <div class="col-xs-12 text-right">
                            <h5><b>{{ strtoupper($data['evaluation_period']) }}</b></h5>
                        </div>
                        <div class="col-xs-12 text-center">
                            <h1><b><u>{{strtoupper($collection['name'])}}</u></b></h1>
                        </div>
                    </div>

                    <div class="row" style="white-space:nowrap;">
                        <div class="col-xs-12" style="white-space:nowrap; margin: 10px 0">
                            <table>
                                <thead class="header-color">
                                <tr>
                                    <th rowspan="2" style="width: 3%;">#</th>
                                    <th rowspan="2" style="width: 37%;">{{ $collection['header'] }}</th>
                                    <th colspan="2" style="width: 20%;">{!! $collection['header2'] !!}</th>
                                    <th colspan="2" style="width: 20%;">Attendance Rating<br>(30%)</th>
                                    <th colspan="2" style="width: 20%;">TOTAL</th>
                                </tr>
                                <tr>
                                    <th>Quantitative</th>
                                    <th>Qualitative</th>
                                    <th>Quantitative</th>
                                    <th>Qualitative</th>
                                    <th>Quantitative</th>
                                    <th>Qualitative</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if ($collection['group_by_committee'])
                                    @php $no = 1; @endphp
                                    @foreach ($collection['members'] as $committee)
                                        <tr class="header-color">
                                            <th colspan="8" class="text-left">&emsp;{{ $committee['committee_name'] }}</th>
                                        </tr>
                                        @foreach ($committee['members'] as $member)
                                            <tr>
                                                <td class="text-center">{{$no++}}</td>
                                                <td class="text-wrap">{{$member['name']}}</td>
                                                <td class="text-center">{{ $member['assessment_quantitative'] ? number_format($member['assessment_quantitative'],2) : ''}}</td>
                                                <td class="text-center">{{$member['assessment_qualitative']}}</td>
                                                <td class="text-center">{{ $member['attendance_quantitative'] ? number_format($member['attendance_quantitative'],2) : ''}}</td>
                                                <td class="text-center">{{$member['attendance_qualitative']}}</td>
                                                <td class="text-center">{{ $member['total_quantitative'] ? number_format($member['total_quantitative'],2) : ''}}</td>
                                                <td class="text-center">{{$member['total_qualitative']}}</td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                @else
                                    {{-- BOT / CO: flat list --}}
                                    @foreach ($collection['members'] as $index => $member)
                                        <tr>
                                            <td class="text-center">{{$index+1}}</td>
                                            <td class="text-wrap">{{$member['name']}}</td>
                                            <td class="text-center">{{ $member['assessment_quantitative'] ? number_format($member['assessment_quantitative'],2) : ''}}</td>
                                            <td class="text-center">{{$member['assessment_qualitative']}}</td>
                                            <td class="text-center">{{ $member['attendance_quantitative'] ? number_format($member['attendance_quantitative'],2) : ''}}</td>
                                            <td class="text-center">{{$member['attendance_qualitative']}}</td>
                                            <td class="text-center">{{ $member['total_quantitative'] ? number_format($member['total_quantitative'],2) : ''}}</td>
                                            <td class="text-center">{{$member['total_qualitative']}}</td>
                                        </tr>
                                    @endforeach
                                @endif
{{--                                @for($a = 1; $a <= 95; $a++)--}}
{{--                                    <tr>--}}
{{--                                        <td class="text-center">{{$a}}</td>--}}
{{--                                        <td class="text-wrap">LTGEN CONNOR ANTHONY D CANLAS SR PAF (RET)</td>--}}
{{--                                        <td class="text-center">5.0</td>--}}
{{--                                        <td class="text-center">Excellent</td>--}}
{{--                                        <td class="text-center">5.0</td>--}}
{{--                                        <td class="text-center">Excellent</td>--}}
{{--                                        <td class="text-center">5.0</td>--}}
{{--                                        <td class="text-center">Excellent</td>--}}
{{--                                    </tr>--}}
{{--                                @endfor--}}

                                </tbody>
                            </table>
                            <p>{!! $collection['weight_distribution'] !!}</p>
                        </div>
                    </div>

                    <div class="row" style="page-break-inside: avoid;">
                        <div class="col-xs-4">
                            <br>
                            <div>Noted by:</div><br>
                            <div>______________________________</div>
                            <div><b>ATTY DEXTER HAROLD E EMPERADOR</b></div>
                            <div>Corporate Secretary</div>
                        </div>
                        <div class="col-xs-8" style="white-space:nowrap;">
                            <table>
                                <thead style="background-color: #31859b; color: #92cddc">
                                <tr>
                                    <th colspan="2" style="width: 40%;">PERFORMANCE ASSESSMENT</th>
                                    <th colspan="4" style="width: 60%;">ATTENDANCE RATING SCALE</th>
                                </tr>
                                <tr>
                                    <th>Quantitative</th>
                                    <th>Qualitative</th>
                                    <th colspan="2"></th>
                                    <th>Quantitative</th>
                                    <th>Qualitative</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($data['rating_scales'] as $rating_scale)
                                    <tr class="font-bold">
                                        <td class="text-center">{{ $rating_scale['assessment_quantitative'] }}</td>
                                        <td class="text-center">{{ $rating_scale['assessment_qualitative'] }}</td>
                                        <td colspan="2" class="text-center">{{ $rating_scale['attendance_name'] }}</td>
                                        <td class="text-center">{{ $rating_scale['attendance_quantitative'] }}</td>
                                        <td class="text-center">{{ $rating_scale['attendance_qualitative'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </body>
</html>
