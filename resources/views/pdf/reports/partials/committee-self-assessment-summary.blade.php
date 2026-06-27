{{-- SUMMARY ASSESSMENTS - One page per committee --}}
@if (isset($data['collections']['summary']) && is_array($data['collections']['summary']) && count($data['collections']['summary']) > 0)
    @foreach ($data['collections']['summary'] as $summary)
        <div class="container-fluid">
            <div class="page">
                <div class="row">
                    <div class="col-xs-12 text-right">
                        <h5><b>{{ strtoupper($data['evaluation_period']) }}</b></h5>
                    </div>
                    <div class="col-xs-12 text-center">
                        <h1><b><u>{{ strtoupper($summary['committee_name'] ?? 'BOT') }} SUMMARY</u></b></h1>
                    </div>
                </div>

                @if (isset($summary['sections_summary']) && is_array($summary['sections_summary']) && count($summary['sections_summary']) > 0)
                    <div class="row" style="white-space:nowrap; margin: 0;">
                        <div class="col-xs-6" style="white-space:nowrap; padding: 0;">
                            <h4 style="text-transform: uppercase"><b>summary rating</b></h4>
                            <table style="width: 100%; table-layout: auto; margin: 0;">
                                <thead class="header-color">
                                    <tr>
                                        <th style="width: 60%; padding: 3px; font-size: 11px; text-transform:uppercase">Governance Anchors</th>
                                        <th style="width: 13%; padding: 3px; font-size: 11px;">Total</th>
                                        <th style="width: 13%; padding: 3px; font-size: 11px;">Average</th>
                                        <th style="width: 14%; padding: 3px; font-size: 11px;">Qualitative</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($summary['sections_summary'] as $section)
                                        <tr>
                                            <td style="padding: 4px; font-size: 10px;">{{ $section['section_title'] ?? 'N/A' }}</td>
                                            <td class="text-center" style="padding: 4px; font-size: 10px;">
                                                {{ isset($section['total_rating']) ? number_format($section['total_rating'], 2) : '-' }}
                                            </td>
                                            <td class="text-center" style="padding: 4px; font-size: 10px;">
                                                {{ isset($section['average_rating']) ? number_format($section['average_rating'], 2) : '-' }}
                                            </td>
                                            <td class="text-center" style="padding: 4px; font-size: 10px;">
                                                {{ $section['qualitative'] ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach

                                    {{-- Overall Summary Row --}}
                                    <tr class="header-color">
                                        <th style="padding: 4px; font-size: 10px;">OVERALL SUMMARY</th>
                                        <th class="text-center" style="padding: 4px; font-size: 10px;">
                                            {{ isset($summary['overall_summary']['total_rating']) ? number_format($summary['overall_summary']['total_rating'], 2) : '-' }}
                                        </th>
                                        <th class="text-center" style="padding: 4px; font-size: 10px;">
                                            {{ isset($summary['overall_summary']['average_rating']) ? number_format($summary['overall_summary']['average_rating'], 2) : '-' }}
                                        </th>
                                        <th class="text-center" style="padding: 4px; font-size: 10px;">
                                            {{ $summary['overall_summary']['qualitative'] ?? '-' }}
                                        </th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="row">
                        <div class="col-xs-12 text-center">
                            <p>No summary data available</p>
                        </div>
                    </div>
                @endif

                {{-- Assessment Guide --}}
                <div class="row" style="white-space:nowrap; margin: 15px 0 0 0;">
                    <div class="col-xs-6" style="padding: 0;">
                        <h4 style="margin: 0 0 5px 0; font-size: 11px;"><b>ASSESSMENT GUIDE</b></h4>
                        <table style="width: 100%; table-layout: auto; border-collapse: collapse; margin: 0;">
                            <thead class="header-color">
                                <tr>
                                    <th style="padding: 4px; border: 1px solid #333; font-size: 10px;">Quantitative Rating</th>
                                    <th style="padding: 4px; border: 1px solid #333; font-size: 10px;">Qualitative Assessment</th>
                                    <th style="padding: 4px; border: 1px solid #333; font-size: 10px;">Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px;">3.50 to 4.00</td>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px;">EXCEPTIONAL</td>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px;"> - Highly commendable governance practices are observed. There is a need to sustain the performance.</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px;">3.00 to < 3.50</td>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px;">SUPERIOR</td>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px;"> - Observance of governance practices exceeds exceptions. Need for improvements deemed a minor concern.</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px;">2.50 to < 3.00</td>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px;">SATISFACTORY</td>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px;">- Observance of governance practices matches the set standards. There are rooms for futher improvements.</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px;">2.00 to < 2.50</td>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px;">NEEDS IMPROVEMENT</td>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px;">- Observance of goverance practices falls below expectations. Need for improvement is highly necessary to mitigate potential governance risks.</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px;">Below 2.00</td>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px;">UNSATISFACTORY</td>
                                    <td style="padding: 4px; border: 1px solid #ccc; font-size: 10px;">- Governance practices not consistently observed. Need for improvements is deemed critical, to contain potential governance risks.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row" style="white-space:nowrap; margin-top: 40px;">
                    <div class="col-xs-6">
                        <br>
                        <div>Prepared by:</div><br>
                        <div>______________________________</div>
                        <div><b>MS. CLARENCE R. MEJIA</b></div>
                        <div>Head, Office the Board Secretariat</div>
                    </div>
                    <div class="col-xs-6">
                        <br>
                        <div>Noted by:</div><br>
                        <div>______________________________</div>
                        <div><b>ATTY DEXTER HAROLD E EMPERADOR</b></div>
                        <div>Corporate Secretary</div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif
