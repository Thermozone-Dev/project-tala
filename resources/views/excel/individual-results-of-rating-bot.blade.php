@php
    $headerStyle = '
        background-color:#31859b;
        color:#caf5fb;
        font-weight:bold;
        text-align:center;
        vertical-align:middle;
        border:1px solid #000000;
    ';

    $cellStyle = '
        border:1px solid #000000;
        vertical-align:top;
    ';

    $centerStyle = '
        border:1px solid #000000;
        text-align:center;
        vertical-align:middle;
    ';

    $titleStyle = '
        font-size:18px;
        font-weight:bold;
        text-decoration:underline;
        vertical-align:middle;
    ';
@endphp
<table style="border-collapse:collapse; width:100%;">
    {{-- TITLE --}}
    <tr>
        <td colspan="7" style="{{ $titleStyle }}">{{ strtoupper($collection['member_name']) }}</td>
    </tr>

    <tr>
        <td colspan="{{ 4 + count($collection['evaluators_summary']) }}">
            &nbsp;
        </td>
    </tr>

    {{-- HEADER --}}
    <thead>
    <tr>
        <th style="{{ $headerStyle }}">#</th>

        <th style="{{ $headerStyle }}">
            BOT Performance (70%)
        </th>

        @foreach ($collection['evaluators_summary'] as $evaluator)
            <th style="{{ $headerStyle }}">
                {{ $evaluator['evaluator_name'] }}
            </th>
        @endforeach

        <th style="{{ $headerStyle }}">Total</th>
        <th style="{{ $headerStyle }}">Average</th>
        <th style="{{ $headerStyle }}">Qualitative Rating</th>
    </tr>
    </thead>

    {{-- QUESTIONS --}}
    <tbody>
    @foreach ($collection['questions'] as $key => $question)
        <tr>

            <td style="{{ $centerStyle }}">
                {{ $key + 1 }}
            </td>

            <td style="{{ $cellStyle }}">
                {{ $question['question'] }}
            </td>

            @foreach ($question['evaluators'] as $evaluator)
                <td style="{{ $centerStyle }}">
                    {{ number_format($evaluator['answer_value'], 2) }}
                </td>
            @endforeach

            <td style="{{ $centerStyle }}">
                {{ number_format($question['total_rating'], 2) }}
            </td>

            <td style="{{ $centerStyle }}">
                {{ number_format($question['average_rating'], 2) }}
            </td>

            <td style="{{ $centerStyle }}">
                {{ $question['qualitative_rating'] }}
            </td>

        </tr>
    @endforeach
    </tbody>

    {{-- OVERALL RATING --}}
    <tfoot>

    <tr>
        <th colspan="2" rowspan="2" style="{{ $headerStyle }}">
            OVER-ALL RATING
        </th>

        @foreach ($collection['evaluators_summary'] as $evaluator)
            <th style="{{ $headerStyle }}">
                {{ $evaluator['total_rating'] }}
            </th>
        @endforeach

        <th style="{{ $headerStyle }}">
            {{ $collection['total_rating'] }}
        </th>

        <th style="{{ $headerStyle }}">
            {{ number_format($collection['rating_average'], 2) }}
        </th>

        <th style="{{ $headerStyle }}">
            {{ ucfirst($collection['total_qualitative']) }}
        </th>
    </tr>

    <tr>
        @foreach ($collection['evaluators_summary'] as $evaluator)
            <th style="{{ $headerStyle }}">
                {{ number_format($evaluator['average_rating'], 2) }}
            </th>
        @endforeach

        <th style="{{ $headerStyle }}">
            {{ number_format($collection['total_average'], 2) }}
        </th>

        <th style="{{ $headerStyle }}">
            {{ number_format($collection['rating_average'], 2) }}
        </th>

        <th style="{{ $headerStyle }}">
            {{ ucfirst($collection['total_qualitative']) }}
        </th>
    </tr>

    </tfoot>

</table>
{{-- ATTENDANCE --}}
<br>
<br>
<table style="width: 100%; table-layout: fixed;">
    <tr>
        <td colspan="1">

        </td>
        <td colspan="6">
            <table style="border-collapse:collapse; width:100%;">

    <thead>
    <tr>
        <th style="{{ $headerStyle }}">Attendance (30%)</th>
        <th style="{{ $headerStyle }}">Total no. of meetings</th>
        <th style="{{ $headerStyle }}">Total Meetings Present</th>
        <th style="{{ $headerStyle }}">Attendance Rating</th>
        <th style="{{ $headerStyle }}">Quantitative Rating</th>
        <th style="{{ $headerStyle }}">Qualitative Rating</th>
    </tr>
    </thead>

    <tbody>

    @foreach ($collection['attendance']['attendance'] as $attendance)
        <tr>

            <td style="{{ $centerStyle }}">
                {{ $attendance['category'] }}
            </td>

            <td style="{{ $centerStyle }}">
                {{ $attendance['total_meetings'] }}
            </td>

            <td style="{{ $centerStyle }}">
                {{ $attendance['total_present'] }}
            </td>

            <td style="{{ $centerStyle }}">
                {{ $attendance['attendance_percentage'] }}%
            </td>

            <td style="{{ $centerStyle }}">
                {{ $attendance['rating_scale'] }}
            </td>

            <td style="{{ $centerStyle }}">
                {{ $attendance['rating_scale_equivalent'] }}
            </td>

        </tr>
    @endforeach

    </tbody>

    <tfoot>
    <tr>

        <th style="{{ $headerStyle }}">
            OVER-ALL RATING
        </th>

        <th style="{{ $headerStyle }}">
            {{ $collection['attendance']['summary']['total_meetings'] }}
        </th>

        <th style="{{ $headerStyle }}">
            {{ $collection['attendance']['summary']['total_present'] }}
        </th>

        <th style="{{ $headerStyle }}">
            {{ number_format(
                $collection['attendance']['summary']['avg_attendance_percentage'],
                2
            ) }}%
        </th>

        <th style="{{ $headerStyle }}">
            {{ number_format(
                $collection['attendance']['summary']['avg_rating'],
                2
            ) }}
        </th>

        <th style="{{ $headerStyle }}">
            {{ $collection['attendance']['summary']['attendance_rating_qualititative'] }}
        </th>

    </tr>
    </tfoot>

</table>
        </td>
    </tr>
</table>
{{-- SUMMARY --}}
<br>
<br>
<table style="width: 100%; table-layout: fixed;">
            <tr>
                <td colspan="1">

                </td>
                <td colspan="6">
                    <table style="border-collapse:collapse; width:100%;">

                        <thead>
                            <tr>
                                <th colspan="3" style="{{ $headerStyle }}">
                                    Summary
                                </th>

                                <th style="{{ $headerStyle }}">
                                    Average Rating
                                </th>

                                <th style="{{ $headerStyle }}">
                                    Weight
                                </th>

                                <th style="{{ $headerStyle }}">
                                    Score
                                </th>
                            </tr>
                        </thead>

                        <tbody>

                        <tr>
                            <td colspan="3" style="{{ $cellStyle }}">
                                As a Member of the Board
                            </td>

                            <td style="{{ $centerStyle }}">
                                {{ number_format(
                                    $collection['rating_average'] ?? 0,
                                    2
                                ) }}
                            </td>

                            <td style="{{ $centerStyle }}">
                                70%
                            </td>

                            <td style="{{ $centerStyle }}">
                                {{ number_format(
                                    $collection['final_grade']['assesment_grade'] ?? 0,
                                    2
                                ) }}
                            </td>

                        </tr>

                        <tr>

                            <td colspan="3" style="{{ $cellStyle }}">
                                Attendance in Board &amp; Committee Meetings
                            </td>

                            <td style="{{ $centerStyle }}">
                                {{ number_format(
                                    $collection['attendance_rating'] ?? 0,
                                    2
                                ) }}
                            </td>

                            <td style="{{ $centerStyle }}">
                                30%
                            </td>

                            <td style="{{ $centerStyle }}">
                                {{ number_format(
                                    $collection['final_grade']['attendance_grade'] ?? 0,
                                    2
                                ) }}
                            </td>

                        </tr>

                        </tbody>

                        <tfoot>

                        <tr>

                            <th colspan="3" style="{{ $headerStyle }}">
                                Total Score
                            </th>

                            <th colspan="3"
                                style="
                                        {{ $headerStyle }}
                                        font-size:18px;
                                    ">
                                {{ number_format(
                                    $collection['final_grade']['quantitative'] ?? 0,
                                    2
                                ) }}
                            </th>

                        </tr>

                        <tr>

                            <th colspan="3" style="{{ $headerStyle }}">
                                Qualitative Rating
                            </th>

                            <th colspan="3"
                                style="
                                        {{ $headerStyle }}
                                        font-size:18px;
                                    ">
                                {{ $collection['final_grade']['qualitative'] }}
                            </th>

                        </tr>

                        </tfoot>

                    </table>
                </td>
            </tr>
        </table>

