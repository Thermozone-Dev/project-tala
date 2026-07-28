{{-- DETAILED SELF-ASSESSMENT PAGES - One page per committee --}}
@if (isset($data['collections']['detailed']) && is_array($data['collections']['detailed']) && count($data['collections']['detailed']) > 0)
    @foreach ($data['collections']['detailed'] as $assessment)
        <div class="container-fluid">
            <div class="page">
                <div class="row">
                    <div class="col-xs-12 text-right">
                        <h5><b>{{ strtoupper($data['evaluation_period']) }}</b></h5>
                    </div>
                    <div class="col-xs-12">
                        <h1><b>{{ strtoupper($assessment['committee_name'] ?? 'BOT') }} COMMITTEE</b></h1>
                        <div class="col-xs-3" style="margin-bottom: 8px">
                            <table style="width: 100%; table-layout: auto; margin: 0;">
                                <thead>
                                    <tr>
                                        <th colspan="2" class="text-left" style="padding: 3px; font-weight:bold">
                                            SCALE:
                                        </th>
                                    </tr>

                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="" style="padding: 4px; font-size: 10px;">1 - Strongly Disagree</td>
                                        <td class="" style="padding: 4px; font-size: 10px;">3 - Somewhat Agree</td>
                                    </tr>
                                        <tr>
                                        <td class="" style="padding: 4px; font-size: 10px;">2 - Somewhat Disagree</td>
                                        <td class="" style="padding: 4px; font-size: 10px;">4 - Somewhat Agree</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                @if (isset($assessment['sections']) && is_array($assessment['sections']) && count($assessment['sections']) > 0)
                    @foreach ($assessment['sections'] as $section)
                        <div class="row" style="white-space:nowrap; margin: 0; margin-bottom: 20px;">
                            <div class="col-xs-12" style="white-space:nowrap; padding: 0;">
                                @php
                                    $chunkSize = 8; // Split into 8 questions per table
                                    $questionChunks = array_chunk($section['questions'], $chunkSize);
                                @endphp

                                @foreach ($questionChunks as $chunkIndex => $chunk)
                                    <table style="width: 100%; table-layout: auto; margin: 0; margin-bottom: 15px;">
                                        <thead class="header-color">
                                            @if ($chunkIndex === 0)
                                                <tr>
                                                    <th colspan="{{ (isset($section['questions'][0]['evaluators']) ? count($section['questions'][0]['evaluators']) : 0) + 5 }}" class="text-left" style="padding: 3px;">
                                                        &emsp;{{ strtoupper($section['section_title']) }}
                                                    </th>
                                                </tr>
                                            @endif
                                            <tr>
                                                <th class="pdf-header" style="width: 3%;">#</th>
                                                <th class="pdf-header" style="min-width: 250px;">Question</th>
                                                @if (isset($section['questions'][0]['evaluators']))
                                                    @foreach ($section['questions'][0]['evaluators'] as $evaluator)
                                                        <th class="pdf-header" style="min-width: 80px;">{{ $evaluator['evaluator_name'] }}</th>
                                                    @endforeach
                                                @endif
                                                <th class="pdf-header" style="min-width: 80px;">Total</th>
                                                <th class="pdf-header" style="min-width: 90px;">Average</th>
                                                <th class="pdf-header" style="min-width: 120px;">Qualitative</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($chunk as $qIndex => $question)
                                                <tr>
                                                    <td class="text-center" style="padding: 4px; font-size: 10px;">{{ $qIndex + 1 }}</td>
                                                    <td style="padding: 4px; word-wrap: break-word; font-size: 10px;">{{ $question['question'] ?? 'N/A' }}</td>
                                                    @if (isset($question['evaluators']) && is_array($question['evaluators']))
                                                        @foreach ($question['evaluators'] as $evaluator)
                                                            <td class="text-center" style="padding: 4px; font-size: 10px;">
                                                                {{ isset($evaluator['answer_value']) ? number_format($evaluator['answer_value'], 2) : '-' }}
                                                            </td>
                                                        @endforeach
                                                    @endif
                                                    <td class="text-center" style="padding: 4px; font-size: 10px;">
                                                        {{ isset($question['total_rating']) ? number_format($question['total_rating'], 2) : '-' }}
                                                    </td>
                                                    <td class="text-center" style="padding: 4px; font-size: 10px;">
                                                        {{ isset($question['average_rating']) ? number_format($question['average_rating'], 2) : '-' }}
                                                    </td>
                                                    <td class="text-center" style="padding: 4px; font-size: 10px;">
                                                        {{ $question['qualitative_rating'] ?? '-' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endforeach

                                {{-- Section Summary Row - Separate Table --}}
                                <table style="width: 100%; table-layout: auto; margin: 0; margin-bottom: 15px;">
                                    <thead class="header-color">
                                        <tr>
                                            <th colspan="{{ (isset($section['questions'][0]['evaluators']) ? count($section['questions'][0]['evaluators']) : 0) + 5 }}" class="text-left" style="padding: 3px;">
                                                &nbsp;
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="header-color">
                                            <th colspan="2" style="padding: 4px; font-size: 10px;">{{ strtoupper($section['section_title']) }} SUMMARY</th>
                                            @if (isset($section['questions'][0]['evaluators']))
                                                @foreach ($section['questions'][0]['evaluators'] as $evaluator)
                                                    <th class="text-center" style="padding: 4px; font-size: 10px;">-</th>
                                                @endforeach
                                            @endif
                                            <th class="text-center" style="padding: 4px; font-size: 10px;">{{ isset($section['section_total_rating']) ? number_format($section['section_total_rating'], 2) : '-' }}</th>
                                            <th class="text-center" style="padding: 4px; font-size: 10px;">{{ isset($section['section_average_rating']) ? number_format($section['section_average_rating'], 2) : '-' }}</th>
                                            <th class="text-center" style="padding: 4px; font-size: 10px;">{{ $section['individual_summary_qualitative'] ?? '-' }}</th>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="row">
                        <div class="col-xs-12 text-center">
                            <p>No questions available</p>
                        </div>
                    </div>
                @endif

                <div class="row" style="white-space:nowrap; margin-top: 30px;">
                    <div class="col-xs-12">
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
                </div>
            </div>
        </div>
    @endforeach
@endif
