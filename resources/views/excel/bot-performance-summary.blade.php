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
        vertical-align:middle;
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
        text-align:center;
        vertical-align:middle;
    ';
@endphp
<table>
    <tr>
        <td colspan="8"
            style="text-align:right;font-weight:bold;">
            {{ strtoupper($data['evaluation_period']) }}
        </td>
    </tr>

    <tr>
        <td colspan="8" style="{{ $titleStyle }}">
            {{ strtoupper($collection['name']) }}
        </td>
    </tr>

    <tr>
        <td colspan="8">&nbsp;</td>
    </tr>
</table>
{{-- MAIN SUMMARY TABLE --}}
<table>

    <thead>
    <tr>

        <th rowspan="2"
            style="{{ $headerStyle }}">
            #
        </th>

        <th rowspan="2"
            style="{{ $headerStyle }}">
            {{ $collection['code'] === 'BOT'
                ? 'Member Board of Trustees'
                : ($collection['code'] === 'CO'
                    ? 'Corporate Officers &amp; Role'
                    : 'Lead Resource Person') }}
        </th>

        <th colspan="2"
            style="{{ $headerStyle }}">
            Evaluation Rating<br>(70%)
        </th>

        <th colspan="2"
            style="{{ $headerStyle }}">
            Attendance Rating<br>(30%)
        </th>

        <th colspan="2"
            style="{{ $headerStyle }}">
            TOTAL
        </th>

    </tr>

    <tr>

        <th style="{{ $headerStyle }}">
            Quantitative
        </th>

        <th style="{{ $headerStyle }}">
            Qualitative
        </th>

        <th style="{{ $headerStyle }}">
            Quantitative
        </th>

        <th style="{{ $headerStyle }}">
            Qualitative
        </th>

        <th style="{{ $headerStyle }}">
            Quantitative
        </th>

        <th style="{{ $headerStyle }}">
            Qualitative
        </th>

    </tr>
    </thead>


    <tbody>

    {{-- LRP --}}
    @if ($collection['code'] === 'LRP')
        @php
            $no = 1;
        @endphp

        @if (
            isset($collection['members']) &&
            is_array($collection['members']) &&
            count($collection['members']) > 0
        )
            @foreach ($collection['members'] as $committee)
                {{-- COMMITTEE HEADER --}}
                <tr>

                    <th colspan="8"
                        style="
                                {{ $headerStyle }}
                                text-align:left;
                            ">
                        {{ $committee['committee_name'] ?? 'Unknown Committee' }}
                    </th>

                </tr>
                @if (
                    isset($committee['members']) &&
                    is_array($committee['members'])
                )

                    @foreach ($committee['members'] as $member)

                        <tr>

                            <td style="{{ $centerStyle }}">
                                {{ $no++ }}
                            </td>

                            <td style="{{ $cellStyle }}">
                                {{ $member['member_name'] ?? 'N/A' }}
                            </td>

                            <td style="{{ $centerStyle }}">
                                {{
                                    isset($member['rating_average']) &&
                                    $member['rating_average']
                                        ? number_format($member['rating_average'], 2)
                                        : '-'
                                }}
                            </td>

                            <td style="{{ $centerStyle }}">
                                {{ $member['total_qualitative'] ?? '-' }}
                            </td>

                            <td style="{{ $centerStyle }}">
                                {{
                                    isset($member['attendance_avg_rating']) &&
                                    $member['attendance_avg_rating']
                                        ? number_format($member['attendance_avg_rating'], 2)
                                        : '-'
                                }}
                            </td>

                            <td style="{{ $centerStyle }}">
                                {{ $member['attendance_qualitative'] ?? '-' }}
                            </td>

                            <td style="{{ $centerStyle }}">
                                {{
                                    isset($member['final_grade_quantitative']) &&
                                    $member['final_grade_quantitative']
                                        ? number_format($member['final_grade_quantitative'], 2)
                                        : '-'
                                }}
                            </td>

                            <td style="{{ $centerStyle }}">
                                {{ $member['final_grade_qualitative'] ?? '-' }}
                            </td>

                        </tr>
                    @endforeach
                @endif
            @endforeach
        @else
            <tr>
                <td colspan="8"
                    style="{{ $centerStyle }}">
                    No data available
                </td>
            </tr>
        @endif
        {{-- BOT / CO --}}
    @else
        @if (
            isset($collection['members']) &&
            is_array($collection['members']) &&
            count($collection['members']) > 0
        )
            @foreach ($collection['members'] as $index => $member)
                <tr>

                    <td style="{{ $centerStyle }}">
                        {{ $index + 1 }}
                    </td>

                    <td style="{{ $cellStyle }}">

                        {{ $member['member_name'] ?? 'N/A' }}

                        @if(isset($member['role']) && $member['role'])
                            <br>
                            <i>{{ $member['role'] }}</i>
                        @endif

                    </td>

                    <td style="{{ $centerStyle }}">
                        {{
                            isset($member['rating_average']) &&
                            $member['rating_average']
                                ? number_format($member['rating_average'], 2)
                                : '-'
                        }}
                    </td>

                    <td style="{{ $centerStyle }}">
                        {{ $member['total_qualitative'] ?? '-' }}
                    </td>

                    <td style="{{ $centerStyle }}">
                        {{
                            isset($member['attendance_avg_rating']) &&
                            $member['attendance_avg_rating']
                                ? number_format($member['attendance_avg_rating'], 2)
                                : '-'
                        }}
                    </td>

                    <td style="{{ $centerStyle }}">
                        {{ $member['attendance_qualitative'] ?? '-' }}
                    </td>

                    <td style="{{ $centerStyle }}">
                        {{
                            isset($member['final_grade_quantitative']) &&
                            $member['final_grade_quantitative']
                                ? number_format($member['final_grade_quantitative'], 2)
                                : '-'
                        }}
                    </td>

                    <td style="{{ $centerStyle }}">
                        {{ $member['final_grade_qualitative'] ?? '-' }}
                    </td>

                </tr>

            @endforeach
        @else
            <tr>
                <td colspan="8"
                    style="{{ $centerStyle }}">
                    No data available
                </td>
            </tr>

        @endif

    @endif
    </tbody>
</table>
{{-- WEIGHT DISTRIBUTION --}}
<table>
    <tr>
        <td colspan="8" style="padding-top:10px;">
            <b>*</b>
            @if($collection['code'] === 'BOT')
                Weight Distribution is 70% Evaluation Rating and 30% Attendance
            @elseif($collection['code'] === 'CO')
                Weight Distribution is 70% Evaluation Rating and 30% Attendance
            @else
                Weight Distribution is 70% Committee Members' Rating and 30% Attendance
            @endif
        </td>
    </tr>
</table>
<br>
{{-- RATING SCALE --}}
<table style="width: 100%; table-layout: fixed;">
    <tr>
        <td colspan="1">

        </td>
        <td colspan="6">

            <table style="width: 100%; table-layout: fixed;">

                <colgroup>
                    <col style="width: 15%;">
                    <col style="width: 20%;">
                    <col style="width: 15%;">
                    <col style="width: 15%;">
                    <col style="width: 15%;">
                    <col style="width: 20%;">
                </colgroup>

                <thead>
                <tr>
                    <th colspan="2" style="{{ $headerStyle }}">
                        PERFORMANCE ASSESSMENT
                    </th>

                    <th colspan="4" style="{{ $headerStyle }}">
                        ATTENDANCE RATING SCALE
                    </th>
                </tr>

                <tr>
                    <th style="{{ $headerStyle }}">
                        Quantitative
                    </th>

                    <th style="{{ $headerStyle }}">
                        Qualitative
                    </th>

                    <th colspan="2" style="{{ $headerStyle }}">
                    </th>

                    <th style="{{ $headerStyle }}">
                        Quantitative
                    </th>

                    <th style="{{ $headerStyle }}">
                        Qualitative
                    </th>
                </tr>
                </thead>

                <tbody>
                @foreach ($data['rating_scales'] as $rating_scale)
                    <tr>
                        <td style="{{ $centerStyle }}">
                            {{ $rating_scale['assessment_quantitative'] ?? '-' }}
                        </td>

                        <td style="{{ $centerStyle }}">
                            {{ $rating_scale['assessment_qualitative'] ?? '-' }}
                        </td>

                        <td colspan="2" style="{{ $centerStyle }}">
                            {{ $rating_scale['attendance_name'] ?? '-' }}
                        </td>

                        <td style="{{ $centerStyle }}">
                            {{ $rating_scale['attendance_quantitative'] ?? '-' }}
                        </td>

                        <td style="{{ $centerStyle }}">
                            {{ $rating_scale['attendance_qualitative'] ?? '-' }}
                        </td>
                    </tr>
                @endforeach
                </tbody>

            </table>

        </td>
    </tr>
</table>
{{-- SIGNATORY --}}
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

