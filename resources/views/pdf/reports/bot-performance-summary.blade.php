
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
        @if (isset($data['collections']))
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
                        <div class="col-xs-12" style="white-space:nowrap; margin: 10px 0; margin-bottom: 20px;">
                            <table style="margin-bottom: 15px;">
                                <thead class="header-color">
                                <tr>
                                    <th rowspan="2" style="width: 3%;">#</th>
                                    <th rowspan="2" style="width: 37%;">{{ $collection['code'] === 'BOT' ? 'Member Board of Trustees' : ($collection['code'] === 'CO' ? 'Corporate Officers & Role' : 'Lead Resource Person') }}</th>
                                    <th colspan="2" style="width: 20%;">Evaluation Rating<br>(70%)</th>
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
                                    {{-- LRP: grouped by committee --}}
                                    @if ($collection['code'] === 'LRP')
                                        @php $no = 1; @endphp
                                        @if (isset($collection['members']) && is_array($collection['members']) && count($collection['members']) > 0)
                                            @foreach ($collection['members'] as $committee)
                                                <tr class="header-color">
                                                    <th colspan="8" class="text-left">&emsp;{{ $committee['committee_name'] ?? 'Unknown Committee' }}</th>
                                                </tr>
                                                @if (isset($committee['members']) && is_array($committee['members']))
                                                    @foreach ($committee['members'] as $member)
                                                        <tr>
                                                            <td class="text-center">{{$no++}}</td>
                                                            <td class="text-wrap">{{ isset($member['member_name']) ? $member['member_name'] : 'N/A' }}</td>
                                                            <td class="text-center">{{ (isset($member['rating_average']) && $member['rating_average']) ? number_format($member['rating_average'], 2) : '-' }}</td>
                                                            <td class="text-center">{{ isset($member['total_qualitative']) ? $member['total_qualitative'] : '-' }}</td>
                                                            <td class="text-center">{{ (isset($member['attendance_avg_rating']) && $member['attendance_avg_rating']) ? number_format($member['attendance_avg_rating'], 2) : '-' }}</td>
                                                            <td class="text-center">{{ isset($member['attendance_qualitative']) ? $member['attendance_qualitative'] : '-' }}</td>
                                                            <td class="text-center">{{ (isset($member['final_grade_quantitative']) && $member['final_grade_quantitative']) ? number_format($member['final_grade_quantitative'], 2) : '-' }}</td>
                                                            <td class="text-center">{{ isset($member['final_grade_qualitative']) ? $member['final_grade_qualitative'] : '-' }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="8" class="text-center">No data available</td>
                                            </tr>
                                        @endif
                                    {{-- BOT / CO: flat list --}}
                                    @else
                                        @if (isset($collection['members']) && is_array($collection['members']) && count($collection['members']) > 0)
                                            @foreach ($collection['members'] as $index => $member)
                                                <tr>
                                                    <td class="text-center">{{$index+1}}</td>
                                                    <td class="text-wrap">
                                                        {{ isset($member['member_name']) ? $member['member_name'] : 'N/A' }}
                                                        @if(isset($member['role']) && $member['role'])
                                                            <br><small><i>{{ $member['role'] }}</i></small>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">{{ (isset($member['rating_average']) && $member['rating_average']) ? number_format($member['rating_average'], 2) : '-' }}</td>
                                                    <td class="text-center">{{ isset($member['total_qualitative']) ? $member['total_qualitative'] : '-' }}</td>
                                                    <td class="text-center">{{ (isset($member['attendance_avg_rating']) && $member['attendance_avg_rating']) ? number_format($member['attendance_avg_rating'], 2) : '-' }}</td>
                                                    <td class="text-center">{{ isset($member['attendance_qualitative']) ? $member['attendance_qualitative'] : '-' }}</td>
                                                    <td class="text-center">{{ (isset($member['final_grade_quantitative']) && $member['final_grade_quantitative']) ? number_format($member['final_grade_quantitative'], 2) : '-' }}</td>
                                                    <td class="text-center">{{ isset($member['final_grade_qualitative']) ? $member['final_grade_qualitative'] : '-' }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="8" class="text-center">No data available</td>
                                            </tr>
                                        @endif
                                    @endif
                                </tbody>
                            </table>
                            <p style="margin-top: 10px;">
                                <span style="color: red">*</span>
                                @if($collection['code'] === 'BOT')
                                    Weight Distribution is 70% Evaluation Rating and 30% Attendance
                                @elseif($collection['code'] === 'CO')
                                    Weight Distribution is 70% Evaluation Rating and 30% Attendance
                                @else
                                    Weight Distribution is 70% Committee Members' Rating and 30% Attendance
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row" style="page-break-inside: avoid;">
                        <div class="col-xs-4">
                            <br>
                            <div>Noted by:</div><br>
                            <div>______________________________</div>
                            @if(isset($data['evaluation_period_obj']) && $data['evaluation_period_obj']->corporateSecretarySign)
                                <div><b>{{ strtoupper($data['evaluation_period_obj']->corporateSecretarySign->full_name) }}</b></div>
                                <div>Corporate Secretary</div>
                            @else
                                <div><b>_____________________________</b></div>
                                <div style="font-size: 9px; color: #999;">(No signatory encoded)</div>
                            @endif
                        </div>
                        <div class="col-xs-8" style="white-space:nowrap;">
                            <table>
                                <thead class="header-color">
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
                                    @if (isset($data['rating_scales']) && is_array($data['rating_scales']))
                                        @foreach ($data['rating_scales'] as $rating_scale)
                                            <tr class="font-bold">
                                                <td class="text-center">{{ isset($rating_scale['assessment_quantitative']) ? $rating_scale['assessment_quantitative'] : '-' }}</td>
                                                <td class="text-center">{{ isset($rating_scale['assessment_qualitative']) ? $rating_scale['assessment_qualitative'] : '-' }}</td>
                                                <td colspan="2" class="text-center">{{ isset($rating_scale['attendance_name']) ? $rating_scale['attendance_name'] : '-' }}</td>
                                                <td class="text-center">{{ isset($rating_scale['attendance_quantitative']) ? $rating_scale['attendance_quantitative'] : '-' }}</td>
                                                <td class="text-center">{{ isset($rating_scale['attendance_qualitative']) ? $rating_scale['attendance_qualitative'] : '-' }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="6" class="text-center">No rating scales available</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        @else
            <div class="container-fluid">
                <div class="page">
                    <div class="row">
                        <div class="col-xs-12 text-center">
                            <h2>No data available</h2>
                            <p>No collections data found for this report.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </body>
</html>
