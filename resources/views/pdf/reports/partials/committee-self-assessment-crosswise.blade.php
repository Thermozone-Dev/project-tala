{{-- CROSSWISE OVERALL SUMMARY - BOT Summary, Assessment Guide, Committee Summary --}}
@if (isset($data['collections']['crosswise']) && is_array($data['collections']['crosswise']))
    <div class="container-fluid">
        <div class="page">
            <div class="row">
                <div class="col-xs-12 text-right">
                    <h5><b>{{ strtoupper($data['evaluation_period']) }}</b></h5>
                </div>
                <div class="col-xs-12 text-center">
                    <h1><b><u>SELF-ASSESSMENT CONSOLIDATED SUMMARY</u></b></h1>
                </div>
            </div>

            {{-- BOT SUMMARY & ASSESSMENT GUIDE (Side by Side) --}}
            @if ((isset($data['collections']['crosswise']['bot_sections']) && is_array($data['collections']['crosswise']['bot_sections']) && count($data['collections']['crosswise']['bot_sections']) > 0) || (isset($data['collections']['crosswise']['committee_sections_matrix']) && is_array($data['collections']['crosswise']['committee_sections_matrix']) && count($data['collections']['crosswise']['committee_sections_matrix']) > 0))
                <div class="row" style="white-space:nowrap; margin: 0; margin-bottom: 20px;">
                    {{-- BOT Self Assessment Summary with Sections --}}
                    <div class="col-xs-6" style="padding: 0; padding-right: 5px;">
                        <h4 style="margin: 0 0 5px 0; font-size: 11px;"><b>BOT SELF-ASSESSMENT SUMMARY</b></h4>
                        @if (isset($data['collections']['crosswise']['bot_sections']) && is_array($data['collections']['crosswise']['bot_sections']) && count($data['collections']['crosswise']['bot_sections']) > 0)
                        <table style="width: 100%; table-layout: auto; border-collapse: collapse; margin: 0; margin-bottom: 15px;">
                            <thead class="header-color">
                                <tr>
                                    <th style="padding: 4px; border: 1px solid #333; font-size: 10px;">Section</th>
                                    <th style="padding: 4px; border: 1px solid #333; text-align: center; font-size: 10px;">Average</th>
                                    <th style="padding: 4px; border: 1px solid #333; text-align: center; font-size: 10px;">Qualitative</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data['collections']['crosswise']['bot_sections'] as $section)
                                    <tr>
                                        <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px;">
                                            {{ $section['section_title'] }}
                                        </td>
                                        <td style="padding: 4px; border: 1px solid #ccc; text-align: center; font-size: 10px;">
                                            {{ isset($section['section_overall_average']) ? number_format($section['section_overall_average'], 2) : '-' }}
                                        </td>
                                        <td style="padding: 4px; border: 1px solid #ccc; text-align: center; font-size: 10px;">
                                            {{ $section['section_overall_qualitative'] ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach

                                {{-- Grand Overall Row for BOT --}}
                                <tr class="header-color">
                                    <td style="padding: 4px; border: 1px solid #333; font-size: 10px;"><b>GRAND OVERALL</b></td>
                                    <td style="padding: 4px; border: 1px solid #333; text-align: center; font-size: 10px;">
                                        @if (isset($data['collections']['crosswise']['overall_averages'][0]))
                                            <b>{{ number_format($data['collections']['crosswise']['overall_averages'][0]['overall_average_rating'], 2) }}</b>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td style="padding: 4px; border: 1px solid #333; text-align: center; font-size: 10px;">
                                        @if (isset($data['collections']['crosswise']['overall_averages'][0]))
                                            <b>{{ $data['collections']['crosswise']['overall_averages'][0]['overall_qualitative'] ?? '-' }}</b>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        @else
                        <div style="padding: 10px; text-align: center; border: 1px solid #ccc;">
                            <p style="margin: 0; font-size: 10px;">No data available yet</p>
                        </div>
                        @endif
                    </div>

                    {{-- Assessment Guide (7x3 Blank Table) --}}
                    <div class="col-xs-6" style="padding: 0; padding-left: 5px;">
                        <h4 style="margin: 0 0 5px 0; font-size: 11px;"><b>ASSESSMENT GUIDE</b></h4>
                        <table style="width: 100%; table-layout: auto; border-collapse: collapse; margin: 0; margin-bottom: 15px;">
                            <thead class="header-color">
                                <tr>
                                    <th colspan="3" style="padding: 4px; border: 1px solid #333; font-size: 10px; text-align: center;">
                                        <b>Assessment Guide</b>
                                    </th>
                                </tr>
                                <tr>
                                    <th style="padding: 4px; border: 1px solid #ccc; font-size: 10px; min-height: 30px;">&nbsp;Quantitative Rating</th>
                                    <th style="padding: 4px; border: 1px solid #ccc; font-size: 10px; min-height: 30px;">&nbsp;Qualitative Assessment</th>
                                    <th style="padding: 4px; border: 1px solid #ccc; font-size: 10px; min-height: 30px;">&nbsp;Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px; min-height: 30px;">&nbsp;3.50 to 4.00</td>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px; min-height: 30px;">&nbsp;EXCEPTIONAL</td>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px; min-height: 30px;">&nbsp;- Highly commendable governance practices are observed. There is a need to sustain the performance</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px; min-height: 30px;">&nbsp;3.00 < 3.50</td>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px; min-height: 30px;">&nbsp;SUPERIOR</td>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px; min-height: 30px;">&nbsp;- Observance of governance practices exceeds exceptions. Need for improvements deemed a minor concern.</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px; min-height: 30px;">&nbsp;2.50 < 3.00</td>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px; min-height: 30px;">&nbsp;SATISFACTORY</td>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px; min-height: 30px;">&nbsp;- Observance of governance practices matches the set standard. There are rooms for further improvements.</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px; min-height: 30px;">&nbsp;2.00 > 2.50</td>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px; min-height: 30px;">&nbsp;NEEDS IMPROVEMENT</td>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px; min-height: 30px;">&nbsp;- Obsercance of governance practices falls below expectations. Need for improvement is highly necessary to mitigate potential governance risks.</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px; min-height: 30px;">&nbsp; Below 2.00</td>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px; min-height: 30px;">&nbsp; UNSATISFACTORY</td>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px; min-height: 30px;">&nbsp;- Governance practices not consistently observed. Need for improvements is deemed critical, to contain potenatial governance risks.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- COMMITTEE SELF ASSESSMENT MATRIX (Sections vs Committees) --}}
                @if (isset($data['collections']['crosswise']['committee_sections_matrix']) && is_array($data['collections']['crosswise']['committee_sections_matrix']) && count($data['collections']['crosswise']['committee_sections_matrix']) > 0)
                    <div class="row" style="white-space:nowrap; margin: 15px 0 0 0; margin-bottom: 20px;">
                        <div class="col-xs-12" style="padding: 0; overflow-x: auto;">
                            <h4 style="margin: 0 0 5px 0; font-size: 11px;"><b>COMMITTEE SELF-ASSESSMENT SUMMARY</b></h4>
                            <table style="width: 100%; table-layout: auto; border-collapse: collapse; margin: 0; margin-bottom: 15px;">
                                <thead>
                                    <tr class="header-color">
                                        <th style="padding: 4px; border: 1px solid #333; font-size: 10px;">Section</th>
                                        @if (isset($data['collections']['crosswise']['committee_list']) && is_array($data['collections']['crosswise']['committee_list']))
                                            @foreach ($data['collections']['crosswise']['committee_list'] as $committee)
                                                <th style="padding: 4px; border: 1px solid #333; font-size: 10px; text-align: center;">
                                                    {{ $committee['committee_name'] }}
                                                </th>
                                            @endforeach
                                        @endif
                                        <th style="padding: 4px; border: 1px solid #333; font-size: 10px; text-align: center;">
                                            <b>Total</b>
                                        </th>
                                        <th style="padding: 4px; border: 1px solid #333; font-size: 10px; text-align: center;">
                                            <b>Average</b>
                                        </th>
                                        <th style="padding: 4px; border: 1px solid #333; font-size: 10px; text-align: center;">
                                            <b>Qualitative</b>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Section Rows --}}
                                    @foreach ($data['collections']['crosswise']['committee_sections_matrix'] as $section)
                                        <tr>
                                            <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px;">
                                                {{ $section['section_title'] }}
                                            </td>
                                            @if (isset($section['committees']) && is_array($section['committees']))
                                                @foreach ($section['committees'] as $committee)
                                                    <td style="padding: 4px; border: 1px solid #ccc; text-align: center; font-size: 10px;">
                                                        {{ isset($committee['average_rating']) && $committee['average_rating'] > 0 ? number_format($committee['average_rating'], 2) : '-' }}
                                                    </td>
                                                @endforeach
                                            @endif
                                            <td style="padding: 4px; border: 1px solid #ccc; background-color: #f5f5f5; text-align: center; font-size: 10px;">
                                                {{ isset($section['total_rating']) && $section['total_rating'] > 0 ? number_format($section['total_rating'], 2) : '-' }}
                                            </td>
                                            <td style="padding: 4px; border: 1px solid #ccc; background-color: #f5f5f5; text-align: center; font-size: 10px;">
                                                {{ isset($section['average_rating']) && $section['average_rating'] > 0 ? number_format($section['average_rating'], 2) : '-' }}
                                            </td>
                                            <td style="padding: 4px; border: 1px solid #ccc; background-color: #f5f5f5; text-align: center; font-size: 10px;">
                                                {{ $section['qualitative'] ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach

                                    {{-- Overall Grade Row --}}
                                    <tr class="header-color">
                                        <th style="padding: 4px; border: 1px solid #333; font-size: 10px;"><b>OVERALL GRADE</b></th>
                                        @php $totalOverallAverage = 0; $countCommittees = 0; @endphp
                                        @foreach ($data['collections']['crosswise']['overall_averages'] as $overall)
                                            @if ($overall['committee_name'] !== 'BOT')
                                                <th style="padding: 4px; border: 1px solid #333; text-align: center; font-size: 10px;">
                                                    <b>{{ number_format($overall['overall_average_rating'], 2) }}</b>
                                                </th>
                                                @php $totalOverallAverage += $overall['overall_average_rating']; $countCommittees++; @endphp
                                            @endif
                                        @endforeach
                                        <th style="padding: 4px; border: 1px solid #333; text-align: center; font-size: 10px;">
                                            <b>{{ isset($data['collections']['crosswise']['grand_consolidated_average']) ? number_format($data['collections']['crosswise']['grand_consolidated_average'], 2) : '-' }}</b>
                                        </th>
                                        <th style="padding: 4px; border: 1px solid #333; text-align: center; font-size: 10px;">
                                            <b>{{ $countCommittees > 0 ? number_format($totalOverallAverage / $countCommittees, 2) : '-' }}</b>
                                        </th>
                                        <th style="padding: 4px; border: 1px solid #333; text-align: center; font-size: 10px;">
                                            <b>{{ $data['collections']['crosswise']['grand_consolidated_qualitative'] ?? '-' }}</b>
                                        </th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="row" style="white-space:nowrap; margin: 15px 0 0 0;">
                        <div class="col-xs-12" style="padding: 0;">
                            <div style="padding: 10px; text-align: center; border: 1px solid #ccc;">
                                <p style="margin: 0; font-size: 10px;">No committee data available yet</p>
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <div class="row">
                    <div class="col-xs-12 text-center">
                        <p>No summary data available yet</p>
                    </div>
                </div>
            @endif

            <div class="row" style="white-space:nowrap; margin-top: 40px;">

                <div class="col-xs-6">
                    <div>Prepared by:</div><br>
                    <br>
                    <div>______________________________</div>
                    @if(isset($data['evaluation_period_obj']) && $data['evaluation_period_obj']->secretariatUser)
                        <div><b>{{ strtoupper($data['evaluation_period_obj']->secretariatUser->full_name) }}</b></div>
                        <div>Head, Office the Board Secretariat</div>
                    @else
                        <div><b>_____________________________</b></div>
                        <div style="font-size: 9px; color: #999;">(No signatory encoded)</div>
                    @endif
                </div>
                <div class="col-xs-6">
                    <div>Noted by:</div><br>
                    <br>
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
@endif
