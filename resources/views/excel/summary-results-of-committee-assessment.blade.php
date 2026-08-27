@php
    $committeeName = $committeeName ?? 'BOT';

    $assessment = collect($data['collections']['detailed'] ?? [])
        ->firstWhere('committee_name', $committeeName);

    $summary = collect($data['collections']['summary'] ?? [])
        ->firstWhere('committee_name', $committeeName);

    $crosswise = $data['collections']['crosswise'] ?? [];
@endphp
@php
    /*
    |--------------------------------------------------------------------------
    | EXCEL STYLES
    |--------------------------------------------------------------------------
    */

    $tableStyle = '
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    ';

    $headerStyle = '
        background-color: #31859b;
        color: #caf5fb;
        font-weight: bold;
        text-align: center;
        vertical-align: middle;
        border: 1px solid #000000;
        padding: 6px;
    ';

    $centerStyle = '
        text-align: center;
        vertical-align: middle;
        border: 1px solid #000000;
        padding: 6px;
    ';

    $cellStyle = '
        vertical-align: middle;
        border: 1px solid #000000;
        padding: 6px;
    ';

    $titleStyle = '
        text-align: center;
        font-size: 18px;
        font-weight: bold;
        padding: 10px;
    ';

    $sectionTitleStyle = '
        background-color: #31859b;
        color: #caf5fb;
        font-weight: bold;
        border: 1px solid #000000;
        padding: 6px;
    ';

    $subTitleStyle = '
        font-size: 14px;
        font-weight: bold;
        padding: 6px 0;
    ';
@endphp

@if ($assessment)
    <table>
        <tr>
            <td colspan="8"
                style="text-align:right;font-weight:bold;">
                {{ strtoupper($data['evaluation_period']) }}
            </td>
        </tr>

        <tr>
            <td colspan="8" style="{{ $titleStyle }}">
                {{ strtoupper($assessment['committee_name'] ?? $committeeName) }}
                COMMITTEE
            </td>
        </tr>

        <tr>
            <td colspan="8">&nbsp;</td>
        </tr>
    </table>

    <br>

    {{-- SCALE --}}
    <table style="{{ $tableStyle }}">
        <tr>
            <td colspan="2" style="{{ $sectionTitleStyle }}">
                SCALE
            </td>
        </tr>
        <tr>
            <td colspan="2" style="{{ $cellStyle }}">
                1 - Strongly Disagree
            </td>
        </tr>
        <tr>
            <td colspan="2" style="{{ $cellStyle }}">
                2 - Somewhat Disagree
            </td>
        </tr>
        <tr>
            <td colspan="2" style="{{ $cellStyle }}">
                3 - Somewhat Agree
            </td>
        </tr>
        <tr>
            <td colspan="2" style="{{ $cellStyle }}">
                4 - Somewhat Agree
            </td>
        </tr>
    </table>

    <br>

    {{-- SECTIONS --}}
    @foreach ($assessment['sections'] ?? [] as $section)

        @php
            $questions = collect($section['questions'] ?? [])->toArray();
            $chunkSize = 8;
            $questionChunks = array_chunk($questions, $chunkSize);

            $evaluatorCount = isset($questions[0]['evaluators'])
                && is_array($questions[0]['evaluators'])
                    ? count($questions[0]['evaluators'])
                    : 0;

            $totalColumns = $evaluatorCount + 5;
        @endphp

        @foreach ($questionChunks as $chunkIndex => $chunk)

            <table style="{{ $tableStyle }}">

                @if ($chunkIndex === 0)
                    <tr>
                        <td
                            colspan="{{ $totalColumns }}"
                            style="{{ $sectionTitleStyle }}"
                        >
                            {{ strtoupper($section['section_title'] ?? 'N/A') }}
                        </td>
                    </tr>
                @endif

                <tr>
                    <th style="{{ $headerStyle }}">#</th>

                    <th style="{{ $headerStyle }}">
                        Question
                    </th>

                    @foreach ($questions[0]['evaluators'] ?? [] as $evaluator)
                        <th style="{{ $headerStyle }}">
                            {{ $evaluator['evaluator_name'] ?? '-' }}
                        </th>
                    @endforeach

                    <th style="{{ $headerStyle }}">Total</th>
                    <th style="{{ $headerStyle }}">Average</th>
                    <th style="{{ $headerStyle }}">Qualitative</th>
                </tr>

                @foreach ($chunk as $qIndex => $question)
                    <tr>
                        <td style="{{ $centerStyle }}">
                            {{ $qIndex + 1 }}
                        </td>

                        <td style="{{ $cellStyle }}">
                            {{ $question['question'] ?? 'N/A' }}
                        </td>

                        @foreach ($question['evaluators'] ?? [] as $evaluator)
                            <td style="{{ $centerStyle }}">
                                {{
                                    isset($evaluator['answer_value'])
                                        ? number_format($evaluator['answer_value'], 2)
                                        : '-'
                                }}
                            </td>
                        @endforeach

                        <td style="{{ $centerStyle }}">
                            {{
                                isset($question['total_rating'])
                                    ? number_format($question['total_rating'], 2)
                                    : '-'
                            }}
                        </td>

                        <td style="{{ $centerStyle }}">
                            {{
                                isset($question['average_rating'])
                                    ? number_format($question['average_rating'], 2)
                                    : '-'
                            }}
                        </td>

                        <td style="{{ $centerStyle }}">
                            {{ $question['qualitative_rating'] ?? '-' }}
                        </td>
                    </tr>
                @endforeach

            </table>

            <br>

        @endforeach

        {{-- SECTION SUMMARY --}}
        <table style="{{ $tableStyle }}">

            <tr>
                <td
                    colspan="{{ $totalColumns }}"
                    style="{{ $sectionTitleStyle }}"
                >
                    {{ strtoupper($section['section_title'] ?? 'N/A') }}
                    SUMMARY
                </td>
            </tr>

            <tr>
                <th colspan="2" style="{{ $headerStyle }}">
                    {{ strtoupper($section['section_title'] ?? 'N/A') }} SUMMARY
                </th>

                @foreach ($questions[0]['evaluators'] ?? [] as $evaluator)
                    <th style="{{ $headerStyle }}">-</th>
                @endforeach

                <th style="{{ $headerStyle }}">
                    {{
                        isset($section['section_total_rating'])
                            ? number_format($section['section_total_rating'], 2)
                            : '-'
                    }}
                </th>

                <th style="{{ $headerStyle }}">
                    {{
                        isset($section['section_average_rating'])
                            ? number_format($section['section_average_rating'], 2)
                            : '-'
                    }}
                </th>

                <th style="{{ $headerStyle }}">
                    {{ $section['individual_summary_qualitative'] ?? '-' }}
                </th>
            </tr>

        </table>

        <br>

    @endforeach

@endif
{{-- SUMMARY ASSESSMENTS - EXCEL VERSION--}}
@if ($summary)

    <table style="{{ $tableStyle }}">

        <tr>
            <td colspan="8" style="{{ $sectionTitleStyle }}">
                SUMMARY ASSESSMENT
            </td>
        </tr>

        <tr>
            <td colspan="2" style="{{ $cellStyle }}">
                Committee
            </td>

            <td colspan="6" style="{{ $cellStyle }}">
                {{ strtoupper($summary['committee_name'] ?? $committeeName) }}
            </td>
        </tr>

        <tr>
            <td colspan="2" style="{{ $cellStyle }}">
                Period Covered
            </td>

            <td colspan="6" style="{{ $cellStyle }}">
                {{ strtoupper($data['evaluation_period']) }}
            </td>
        </tr>

    </table>

    <br>

    @if (
        isset($summary['sections_summary']) &&
        is_array($summary['sections_summary']) &&
        count($summary['sections_summary']) > 0
    )

        <table style="{{ $tableStyle }}">

            <tr>
                <td colspan="5" style="{{ $sectionTitleStyle }}">
                    SUMMARY RATING
                </td>
            </tr>

            <tr>
                <th colspan="2" style="{{ $headerStyle }}">
                    Governance Anchors
                </th>

                <th style="{{ $headerStyle }}">
                    Total
                </th>

                <th style="{{ $headerStyle }}">
                    Average
                </th>

                <th style="{{ $headerStyle }}">
                    Qualitative
                </th>
            </tr>

            @foreach ($summary['sections_summary'] as $section)

                <tr>
                    <td colspan="2" style="{{ $cellStyle }}">
                        {{ $section['section_title'] ?? 'N/A' }}
                    </td>

                    <td style="{{ $centerStyle }}">
                        {{
                            isset($section['total_rating'])
                                ? number_format($section['total_rating'], 2)
                                : '-'
                        }}
                    </td>

                    <td style="{{ $centerStyle }}">
                        {{
                            isset($section['average_rating'])
                                ? number_format($section['average_rating'], 2)
                                : '-'
                        }}
                    </td>

                    <td style="{{ $centerStyle }}">
                        {{ $section['qualitative'] ?? '-' }}
                    </td>
                </tr>

            @endforeach

            <tr>
                <th colspan="2" tyle="{{ $headerStyle }}">
                    OVERALL SUMMARY
                </th>

                <th style="{{ $headerStyle }}">
                    {{
                        isset($summary['overall_summary']['total_rating'])
                            ? number_format(
                                $summary['overall_summary']['total_rating'],
                                2
                            )
                            : '-'
                    }}
                </th>

                <th style="{{ $headerStyle }}">
                    {{
                        isset($summary['overall_summary']['average_rating'])
                            ? number_format(
                                $summary['overall_summary']['average_rating'],
                                2
                            )
                            : '-'
                    }}
                </th>

                <th style="{{ $headerStyle }}">
                    {{ $summary['overall_summary']['qualitative'] ?? '-' }}
                </th>
            </tr>

        </table>

    @endif

@endif
<br>
{{-- CROSSWISE OVERALL SUMMARY - Excel Version --}}
@if ($crosswise)
    {{-- ============================================================
         TITLE
    ============================================================= --}}
    <table>
        <tr>
            <td colspan="8"
                style="text-align:right;font-weight:bold;">
                {{ strtoupper($data['evaluation_period']) }}
            </td>
        </tr>

        <tr>
            <td colspan="8" style="{{ $titleStyle }}">
                SELF-ASSESSMENT CONSOLIDATED SUMMARY
            </td>
        </tr>

        <tr>
            <td colspan="8">&nbsp;</td>
        </tr>
    </table>
    <br>

    {{-- ============================================================
         BOT SUMMARY
    ============================================================= --}}
    @if (
        (
            isset($crosswise['bot_sections']) &&
            is_array($crosswise['bot_sections']) &&
            count($crosswise['bot_sections']) > 0
        )
        ||
        (
            isset($crosswise['committee_sections_matrix']) &&
            is_array($crosswise['committee_sections_matrix']) &&
            count($crosswise['committee_sections_matrix']) > 0
        )
    )

        <table style="{{ $tableStyle }}">
            <tr>
                <td colspan="4" style="{{ $sectionTitleStyle }}">
                    BOT SELF-ASSESSMENT SUMMARY
                </td>
            </tr>

            @if (
                isset($crosswise['bot_sections']) &&
                is_array($crosswise['bot_sections']) &&
                count($crosswise['bot_sections']) > 0
            )

                <tr>
                    <th colspan="2" style="{{ $headerStyle }}">Section</th>
                    <th style="{{ $headerStyle }}">Average</th>
                    <th style="{{ $headerStyle }}">Qualitative</th>
                </tr>

                @foreach ($crosswise['bot_sections'] as $section)
                    <tr>
                        <td colspan="2" style="{{ $cellStyle }}">
                            {{ $section['section_title'] ?? 'N/A' }}
                        </td>

                        <td style="{{ $centerStyle }}">
                            {{
                                isset($section['section_overall_average'])
                                    ? number_format($section['section_overall_average'], 2)
                                    : '-'
                            }}
                        </td>

                        <td style="{{ $centerStyle }}">
                            {{ $section['section_overall_qualitative'] ?? '-' }}
                        </td>
                    </tr>
                @endforeach

                {{-- GRAND OVERALL --}}
                <tr>
                    <th colspan="2" style="{{ $headerStyle }}">
                        GRAND OVERALL
                    </th>

                    <th style="{{ $headerStyle }}">
                        @if (isset($crosswise['overall_averages'][0]))
                            {{
                                number_format(
                                    $crosswise['overall_averages'][0]['overall_average_rating'],
                                    2
                                )
                            }}
                        @else
                            -
                        @endif
                    </th>

                    <th style="{{ $headerStyle }}">
                        @if (isset($crosswise['overall_averages'][0]))
                            {{
                                $crosswise['overall_averages'][0]['overall_qualitative']
                                ?? '-'
                            }}
                        @else
                            -
                        @endif
                    </th>
                </tr>

            @else

                <tr>
                    <td colspan="3" style="{{ $centerStyle }}">
                        No data available yet
                    </td>
                </tr>

            @endif
        </table>

        <br>

        {{-- ============================================================
             ASSESSMENT GUIDE
        ============================================================= --}}
        <table style="{{ $tableStyle }}">
            <tr>
                <td colspan="8" style="{{ $sectionTitleStyle }}">
                    ASSESSMENT GUIDE
                </td>
            </tr>

            <tr>
                <th colspan="2" style="{{ $headerStyle }}">
                    Quantitative Rating
                </th>

                <th style="{{ $headerStyle }}">
                    Qualitative Assessment
                </th>

                <th colspan="5" style="{{ $headerStyle }}">
                    Description
                </th>
            </tr>

            <tr>
                <td colspan="2" style="{{ $cellStyle }}">
                    3.50 to 4.00
                </td>
                <td style="{{ $cellStyle }}">
                    EXCEPTIONAL
                </td>
                <td colspan="5" style="{{ $cellStyle }}">
                    - Highly commendable governance practices are observed.
                    There is a need to sustain the performance.
                </td>
            </tr>

            <tr>
                <td colspan="2" style="{{ $cellStyle }}">
                    3.00 to &lt; 3.50
                </td>
                <td style="{{ $cellStyle }}">
                    SUPERIOR
                </td>
                <td colspan="5" style="{{ $cellStyle }}">
                    - Observance of governance practices exceeds exceptions.
                    Need for improvements deemed a minor concern.
                </td>
            </tr>

            <tr>
                <td colspan="2" style="{{ $cellStyle }}">
                    2.50 to &lt; 3.00
                </td>
                <td style="{{ $cellStyle }}">
                    SATISFACTORY
                </td>
                <td colspan="5" style="{{ $cellStyle }}">
                    - Observance of governance practices matches the set standard.
                    There are rooms for further improvements.
                </td>
            </tr>

            <tr>
                <td colspan="2" style="{{ $cellStyle }}">
                    2.00 to &lt; 2.50
                </td>
                <td style="{{ $cellStyle }}">
                    NEEDS IMPROVEMENT
                </td>
                <td colspan="5" style="{{ $cellStyle }}">
                    - Observance of governance practices falls below expectations.
                    Need for improvement is highly necessary to mitigate potential
                    governance risks.
                </td>
            </tr>

            <tr>
                <td colspan="2" style="{{ $cellStyle }}">
                    Below 2.00
                </td>
                <td style="{{ $cellStyle }}">
                    UNSATISFACTORY
                </td>
                <td colspan="5" style="{{ $cellStyle }}">
                    - Governance practices not consistently observed.
                    Need for improvements is deemed critical, to contain
                    potential governance risks.
                </td>
            </tr>
        </table>

        <br>

        {{-- ============================================================
             COMMITTEE SELF-ASSESSMENT SUMMARY
        ============================================================= --}}
        @if (
            isset($crosswise['committee_sections_matrix']) &&
            is_array($crosswise['committee_sections_matrix']) &&
            count($crosswise['committee_sections_matrix']) > 0
        )

            @php
                $committeeList =
                    isset($crosswise['committee_list']) &&
                    is_array($crosswise['committee_list'])
                        ? $crosswise['committee_list']
                        : [];

                $committeeCount = count($committeeList);

                /*
                 * Section + committees + Total + Average + Qualitative
                 */
                $matrixColumns = $committeeCount + 4;
            @endphp

            <table style="{{ $tableStyle }}">

                {{-- TITLE --}}
                <tr>
                    <td colspan="{{ $matrixColumns + 1 }}" style="{{ $sectionTitleStyle }}">
                        COMMITTEE SELF-ASSESSMENT SUMMARY
                    </td>
                </tr>

                {{-- HEADER --}}
                <tr>
                    <th colspan="2" style="{{ $headerStyle }}">
                        Section
                    </th>

                    @foreach ($committeeList as $committee)
                        <th style="{{ $headerStyle }}">
                            {{ strtoupper($committee['committee_name'] ?? '-') }}
                        </th>
                    @endforeach

                    <th style="{{ $headerStyle }}">
                        Total
                    </th>

                    <th style="{{ $headerStyle }}">
                        Average
                    </th>

                    <th style="{{ $headerStyle }}">
                        Qualitative
                    </th>
                </tr>

                {{-- SECTION ROWS --}}
                @foreach (
                    $crosswise['committee_sections_matrix']
                    as $section
                )

                    <tr>

                        <td colspan="2" style="{{ $cellStyle }}">
                            {{ $section['section_title'] ?? 'N/A' }}
                        </td>

                        @if (
                            isset($section['committees']) &&
                            is_array($section['committees'])
                        )

                            @foreach ($section['committees'] as $committee)

                                <td style="{{ $centerStyle }}">
                                    {{
                                        isset($committee['average_rating']) &&
                                        $committee['average_rating'] > 0
                                            ? number_format($committee['average_rating'], 2)
                                            : '-'
                                    }}
                                </td>

                            @endforeach

                        @else

                            @foreach ($committeeList as $committee)
                                <td style="{{ $centerStyle }}">
                                    -
                                </td>
                            @endforeach

                        @endif

                        {{-- TOTAL --}}
                        <td style="{{ $centerStyle }} background-color: #f5f5f5;">
                            {{
                                isset($section['total_rating']) &&
                                $section['total_rating'] > 0
                                    ? number_format($section['total_rating'], 2)
                                    : '-'
                            }}
                        </td>

                        {{-- AVERAGE --}}
                        <td style="{{ $centerStyle }} background-color: #f5f5f5;">
                            {{
                                isset($section['average_rating']) &&
                                $section['average_rating'] > 0
                                    ? number_format($section['average_rating'], 2)
                                    : '-'
                            }}
                        </td>

                        {{-- QUALITATIVE --}}
                        <td style="{{ $centerStyle }} background-color: #f5f5f5;">
                            {{ $section['qualitative'] ?? '-' }}
                        </td>

                    </tr>

                @endforeach

                {{-- ====================================================
                     OVERALL GRADE
                ===================================================== --}}
                <tr>

                    <th colspan="2" style="{{ $headerStyle }}">
                        OVERALL GRADE
                    </th>

                    @php
                        $totalOverallAverage = 0;
                        $countCommittees = 0;
                    @endphp

                    @foreach (
                        $crosswise['overall_averages'] ?? []
                        as $overall
                    )

                        @if (($overall['committee_name'] ?? '') !== 'BOT')

                            <th style="{{ $headerStyle }}">
                                {{
                                    isset($overall['overall_average_rating'])
                                        ? number_format($overall['overall_average_rating'], 2)
                                        : '-'
                                }}
                            </th>

                            @php
                                $totalOverallAverage +=
                                    $overall['overall_average_rating'] ?? 0;

                                $countCommittees++;
                            @endphp

                        @endif

                    @endforeach

                    {{-- GRAND CONSOLIDATED AVERAGE --}}
                    <th style="{{ $headerStyle }}">
                        {{
                            isset($crosswise['grand_consolidated_average'])
                                ? number_format(
                                    $crosswise['grand_consolidated_average'],
                                    2
                                )
                                : '-'
                        }}
                    </th>

                    {{-- COMMITTEE AVERAGE --}}
                    <th style="{{ $headerStyle }}">
                        {{
                            $countCommittees > 0
                                ? number_format(
                                    $totalOverallAverage / $countCommittees,
                                    2
                                )
                                : '-'
                        }}
                    </th>

                    {{-- QUALITATIVE --}}
                    <th style="{{ $headerStyle }}">
                        {{
                            $crosswise['grand_consolidated_qualitative']
                            ?? '-'
                        }}
                    </th>

                </tr>

            </table>

        @else

            <table style="{{ $tableStyle }}">
                <tr>
                    <td style="{{ $centerStyle }}">
                        No committee data available yet
                    </td>
                </tr>
            </table>

        @endif

    @else

        <table style="{{ $tableStyle }}">
            <tr>
                <td style="{{ $centerStyle }}">
                    No summary data available yet
                </td>
            </tr>
        </table>

    @endif

    <br>

    {{-- ============================================================
         SIGNATORIES
    ============================================================= --}}
    <table>
        <tr>
            <td>
                Prepared by:
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
        @if (isset($data['evaluation_period_obj']) &&$data['evaluation_period_obj']->secretariatUser)
            <tr>
                <td>
                    <b>
                        {{ strtoupper($data['evaluation_period_obj']->secretariatUser->full_name) }}
                    </b>
                </td>
            </tr>
            <tr>
                <td>
                    Head, Office the Board Secretariat
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
@endif



