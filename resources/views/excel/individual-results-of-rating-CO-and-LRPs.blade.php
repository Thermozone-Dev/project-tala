@php
    $members = $collection;

    $headerStyle = '
        background-color: #31859b;
        color: #caf5fb;
        font-weight: bold;
        text-align: center;
        vertical-align: middle;
        border: 1px solid #000000;
        padding: 8px;
    ';

    $centerStyle = '
        text-align: center;
        vertical-align: middle;
        border: 1px solid #000000;
        padding: 8px;
    ';

    $cellStyle = '
        vertical-align: middle;
        border: 1px solid #000000;
        padding: 8px;
    ';
@endphp
<table style=" width: 100%;">

    <thead>
    {{-- TITLE --}}
    <tr>
        <th
            colspan="{{ 5 + count($members['evaluators_summary'] ?? []) }}"
            style="
                    text-align: center;
                    font-size: 18px;
                    font-weight: bold;
                    padding: 10px;
                    border: 1px solid #000000;
                "
        >
            {{ strtoupper($members['member_name']) }}
        </th>
    </tr>

    {{-- HEADER --}}
    <tr>

        <th style="{{ $headerStyle }}">
            #
        </th>

        <th
            style="{{ $headerStyle }}"
        >
            {{ ucfirst($members['committee_name']) }}
            Committee Performance (70%)
        </th>

        @foreach ($members['evaluators_summary'] ?? [] as $evaluator)

            <th style="{{ $headerStyle }}">
                {{ $evaluator['evaluator_name'] }}
            </th>

        @endforeach

        <th style="{{ $headerStyle }}">
            Total
        </th>

        <th style="{{ $headerStyle }}">
            Average
        </th>

        <th style="{{ $headerStyle }}">
            Qualitative Rating
        </th>

    </tr>

    </thead>

    <tbody>

    @foreach ($members['questions'] as $key => $question)

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

    <tfoot>

    <tr>
        <th colspan="2" rowspan="2" style="{{ $headerStyle }}">
            OVER-ALL RATING
        </th>

        @foreach ($members['evaluators_summary'] ?? [] as $evaluator)

            <th style="{{ $headerStyle }}">
                {{ number_format($evaluator['total_rating'], 2) }}
            </th>

        @endforeach

        <th style="{{ $headerStyle }}">
            {{ number_format($members['total_rating'], 2) }}
        </th>

        <th style="{{ $headerStyle }}">
            {{ number_format($members['rating_average'], 2) }}
        </th>

        <th style="{{ $headerStyle }}">
            {{ ucfirst($members['total_qualitative']) }}
        </th>

    </tr>

    <tr>
        @foreach ($members['evaluators_summary'] ?? [] as $evaluator)

            <th style="{{ $headerStyle }}">
                {{ number_format($evaluator['average_rating'], 2) }}
            </th>

        @endforeach

        <th style="{{ $headerStyle }}">
            {{ number_format($members['total_average'], 2) }}
        </th>

        <th style="{{ $headerStyle }}">
            {{ number_format($members['rating_average'], 2) }}
        </th>

        <th style="{{ $headerStyle }}">
            {{ ucfirst($members['total_qualitative']) }}
        </th>

    </tr>

    </tfoot>

</table>
<br><br>
{{-- TABLE 2 - ATTENDANCE --}}
<table style="width: 100%; ">

    <thead>
    <tr>
        <th colspan="2" style="{{ $headerStyle }}">
            Attendance (30%)
        </th>

        <th style="{{ $headerStyle }}">
            Total no. of meetings
        </th>

        <th style="{{ $headerStyle }}">
            Total Meetings Present
        </th>

        <th style="{{ $headerStyle }}">
            Attendance Rating
        </th>

        <th style="{{ $headerStyle }}">
            Quantitative Rating
        </th>

        <th style="{{ $headerStyle }}">
            Qualitative Rating
        </th>

    </tr>

    </thead>
    <tbody>
    @if (
        isset($members['attendance']['attendance']) &&
        !empty($members['attendance']['attendance'])
    )

        @foreach ($members['attendance']['attendance'] as $attendance)

            <tr>

                <td colspan="2" style="{{ $centerStyle }}">
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
                    {{ number_format($attendance['rating_scale'], 2) }}
                </td>

                <td style="{{ $centerStyle }}">
                    {{ $attendance['rating_scale_equivalent'] }}
                </td>

            </tr>

        @endforeach

    @else
        <tr>
            <td colspan="7" style="{{ $centerStyle }}">
                -
            </td>
        </tr>
    @endif

    </tbody>


    <tfoot>

    <tr>

        <th colspan="2" style="{{ $headerStyle }}">
            OVER-ALL RATING
        </th>

        <th style="{{ $headerStyle }}">
            {{
                isset($members['attendance']) &&
                !empty($members['attendance'])
                    ? number_format(
                        $members['attendance']['summary']['total_meetings'] ?? 0,
                        2
                    )
                    : '-'
            }}
        </th>

        <th style="{{ $headerStyle }}">
            {{
                isset($members['attendance']) &&
                !empty($members['attendance'])
                    ? number_format(
                        $members['attendance']['summary']['total_present'] ?? 0,
                        2
                    )
                    : '-'
            }}
        </th>

        <th style="{{ $headerStyle }}">
            {{
                isset($members['attendance']) &&
                !empty($members['attendance'])
                    ? number_format(
                        $members['attendance']['summary']['avg_attendance_percentage'] ?? 0,
                        2
                    )
                    : '-'
            }}%
        </th>

        <th style="{{ $headerStyle }}">
            {{
                isset($members['attendance_rating'])
                    ? number_format($members['attendance_rating'], 2)
                    : '-'
            }}
        </th>

        <th style="{{ $headerStyle }}">
            {{
                isset($members['attendance']) &&
                !empty($members['attendance'])
                    ? ($members['attendance']['summary']['attendance_rating_qualititative'] ?? '-')
                    : '-'
            }}
        </th>

    </tr>

    </tfoot>

</table>

<br><br>
{{-- TABLE 3 - SUMMARY --}}
<table style="width: 100%; ">

    <thead>
    <tr>
        <th
            colspan="4"
            style="{{ $headerStyle }}"
        >
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

        <td colspan="4"
            style="{{ $cellStyle }}">
            {{ ucfirst($members['committee_name']) }}
            Committee Performance
        </td>

        <td style="{{ $centerStyle }}">
            {{ number_format($members['rating_average'], 2) }}
        </td>

        <td style="{{ $centerStyle }}">
            70%
        </td>

        <td style="{{ $centerStyle }}">
            {{ number_format($members['rating_average'] * 0.7, 2) }}
        </td>

    </tr>
    <tr>
        <td colspan="4" style="{{ $cellStyle }}">
            Attendance in Meetings
        </td>

        <td style="{{ $centerStyle }}">
            {{
                isset($members['attendance_rating'])
                    ? number_format($members['attendance_rating'], 2)
                    : '-'
            }}
        </td>

        <td style="{{ $centerStyle }}">
            30%
        </td>

        <td style="{{ $centerStyle }}">
            {{
                isset($members['attendance_rating'])
                    ? number_format(
                        $members['attendance_rating'] * 0.3,
                        2
                    )
                    : '-'
            }}
        </td>

    </tr>
    </tbody>

    <tfoot>

    <tr>
        <th colspan="4" style="{{ $headerStyle }}">
            Total Score
        </th>

        <th
            colspan="3"
            style="{{ $headerStyle }} font-size: 18px;"
        >
            {{
                isset($members['final_grade'])
                    ? number_format(
                        $members['final_grade']['quantitative'],
                        2
                    )
                    : '-'
            }}
        </th>
    </tr>

    <tr>
        <th colspan="4" style="{{ $headerStyle }}">
            Qualitative Rating
        </th>

        <th colspan="3" style="{{ $headerStyle }} font-size: 18px;">
            {{
                isset($members['final_grade'])
                    ? ucfirst($members['final_grade']['qualitative'])
                    : '-'
            }}
        </th>

    </tr>

    </tfoot>

</table>

<br><br>
{{-- NOTED BY --}}
<table>
    <tr>
        <td>
            Noted by:
        </td>
    </tr>

    <tr>
        <td>
            ______________________________
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    @if($data['evaluation_period_obj']->corporateSecretarySign)
        <tr>
            <td>
                <b>
                    {{ strtoupper($data['evaluation_period_obj']->corporateSecretarySign?->full_name) }}
                </b>
            </td>
        </tr>
        <tr>
            <td>
                Corporate Secretary
            </td>
        </tr>
    @else
        <tr>
            <td>
                <b>_____________________________</b>
            </td>
        </tr>
        <tr>
            <td>
                <b>(No signatory encoded)</b>
            </td>
        </tr>

    @endif
</table>
<br><br>

