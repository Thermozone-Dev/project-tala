
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
        <div class="container-fluid">
            @foreach ($data['collections'] as $members)
                <div class="page">
                    <div class="row">
                        <div class="col-xs-12 text-center">
                        </div>
                        <div class="col-xs-12" style="margin: 10px 0; margin-bottom: 20px; overflow-x: auto;">
                            <table style="width: 100%; table-layout: auto; word-wrap: break-word; word-break: break-word; margin-bottom: 15px;">
                                <thead class="header-color">
                                    <tr>
                                        <h1 class="text-center"><b><u>{{strtoupper($members['member_name'])}}</u></b></h1>
                                    </tr>
                                    <tr>
                                        <th style="width: 3%; padding: 8px;">#</th>
                                        <th style="padding: 8px; min-width: 200px;">BOT Performance (70%)</th>
                                        @foreach ($members['evaluators_summary'] as $evaluator)
                                            <th style="padding: 8px; min-width: 100px;">{{$evaluator['evaluator_name']}}</th>
                                        @endforeach
                                        <th style="padding: 8px; min-width: 80px;">Total</th>
                                        <th style="padding: 8px; min-width: 90px;">Average</th>
                                        <th style="padding: 8px; min-width: 130px;">Qualitative Rating</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($members['questions'] as $key => $question)
                                        <tr>
                                            <td class="text-center" style="padding: 8px;">{{$key+1}}</td>
                                            <td style="padding: 8px; word-wrap: break-word; word-break: break-word;">{{$question['question']}}</td>
                                            @foreach ($question['evaluators'] as $evaluator)
                                                <td class="text-center" style="padding: 8px;">{{number_format($evaluator['answer_value'],2)}}</td>
                                            @endforeach
                                            <td class="text-center" style="padding: 8px;">{{number_format($question['total_rating'],2)}}</td>
                                            <td class="text-center" style="padding: 8px;">{{number_format($question['average_rating'],2)}}</td>
                                            <td class="text-center" style="padding: 8px;">{{$question['qualitative_rating']}}</td>
                                        </tr>
                                    @endforeach

                                </tbody>
                                <thead class="header-color">
                                    <tr>
                                        <th colspan="2" rowspan="2" >OVER-ALL RATING</th>
                                        @foreach ($members['evaluators_summary'] as $evaluator )
                                            <th>{{$evaluator['total_rating']}}</th>
                                        @endforeach
                                        <th>{{$members['total_rating']}}</th>
                                        <th>{{number_format($members['rating_average'],2)}}</th>
                                        <th>{{ucfirst($members['total_qualitative'])}}</th>
                                    </tr>
                                    <tr>
                                        @foreach ($members['evaluators_summary'] as $evaluator )
                                            <th>{{number_format($evaluator['average_rating'],2)}}</th>

                                        @endforeach
                                        <th>{{number_format($members['total_average'],2)}}</th>
                                        <th>{{number_format($members['rating_average'],2)}}</th>
                                        <th>{{ucfirst($members['total_qualitative'])}}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                    <div class="row" style="white-space:nowrap;">
                        <div class="col-xs-2"></div>

                        <div class="col-xs-8" style="white-space:nowrap; margin: 10px 0">
                            <table>
                                <thead class="header-color">
                                    <tr>
                                        <th>Attendance (30%)</th>
                                        <th>Total no. of meetings</th>
                                        <th>Total Meetings Present</th>
                                        <th>Attendance Rating</th>
                                        <th>Quantitative Rating</th>
                                        <th>Qualitative Rating </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($members['attendance']['attendance'] as $attendance)
                                        <tr>
                                            <td class="text-center">{{$attendance['category']}} </td>
                                            <td class="text-center">{{$attendance['total_meetings']}} </td>
                                            <td class="text-center">{{$attendance['total_present']}} </td>
                                            <td class="text-center">{{$attendance['attendance_percentage']}}%</td>
                                            <td class="text-center">{{$attendance['rating_scale']}} </td>
                                            <td class="text-center">{{$attendance['rating_scale_equivalent']}} </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <thead class="header-color">
                                    <tr>
                                        <th>OVER-ALL RATING</th>
                                        <th>{{$members['attendance']['summary']['total_meetings']}} </th>
                                        <th>{{$members['attendance']['summary']['total_present']}} </th>
                                        <th>{{number_format($members['attendance']['summary']['avg_attendance_percentage'], 2)}}% </th>
                                        <th>{{number_format($members['attendance']['summary']['avg_rating'], 2)}} </th>
                                        <th>{{$members['attendance']['summary']['attendance_rating_qualititative']}}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                        <div class="col-xs-2"></div>

                    </div>

                    <div class="row" style="white-space:nowrap;">
                        <div class="col-xs-2"></div>

                        <div class="col-xs-8" style="white-space:nowrap; margin: 10px 0">
                            <table>
                                <thead class="header-color">
                                    <tr>
                                        <th colspan="3">Summary</th>
                                        <th>Average Rating</th>
                                        <th>Weight</th>
                                        <th>Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        {{-- @dd($members['final_grade']) --}}
                                        <td colspan="3">As a Member of the Board</td>
                                        <td class="text-center">{{number_format($members['rating_average'] ?? 0,2)}}</td>
                                        <td class="text-center">70%</td>
                                        <td class="text-center">{{number_format($members['final_grade']['assesment_grade'] ?? 0,2)}}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">Attendance in Board & Committee Meetings</td>
                                        <td class="text-center">{{number_format($members['attendance_rating'] ?? 0,2)}}</td>
                                        <td class="text-center">30&</td>
                                        <td class="text-center">{{number_format($members['final_grade']['attendance_grade'] ?? 0,2)}}</td>
                                    </tr>
                                    <tr class="header-color">
                                        <th colspan="3">Total Score</th>
                                        <th colspan="3" style="font-size: 1.5em">{{number_format($members['final_grade']['quantitative'] ?? 0,2)}}</th>
                                    </tr>
                                    <tr class="header-color">
                                        <th colspan="3">Qualitative Rating</th>
                                        <th colspan="3" style="font-size: 1.5em">{{$members['final_grade']['qualitative']}}</th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </body>
</html>
