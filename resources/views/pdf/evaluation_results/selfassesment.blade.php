@extends('pdf.evaluation_results.c_layouts')
@section('content')
    <div class="page">
        <div class="content" style="text-align: justify;">
            <div class="row col-xs-12">
                <div class="col-xs-12">
                    <h3 class="header3 text-uppercase"> {{$title}} </h3>
                    @if ($period_covered)
                        <div style="margin:auto;  width: 70%;">
                            <span class="col-xs-5 no_margin">
                                <h4 class="header4 no_margin">
                                    Period Covered:
                                </h4>
                            </span>
                            <span class="col-xs-7">
                                <h4 class="header4 no_margin">
                                    {{$period_covered}}
                                </h4>
                            </span>
                        </div>
                    @endif
                    @if (isset($committee) && $committee)
                        <div style="margin:auto;  width: 70%;">
                            <span class="col-xs-5">
                                <h4 class="header4 no_margin">
                                    BOT Committee:
                                </h4>
                            </span>
                            <span class="col-xs-7">
                                <h4 class="header4 no_margin">
                                    {{$committee}}
                                </h4>
                            </span>
                        </div>
                    @endif

                </div>

                @if (isset($show_bot_self_instruction) && $show_bot_self_instruction)
                    <div style="padding-top: 10px">
                        <p>
                            <em>
                                This questionnaire is meant to gather responses on the quality of BOT performance as contributed by both the individual Trustee and the BOT as a group. The responses will be used to further improve the way the BOT applies corporate governance principles in carrying out their regular functions as members of the Board.
                            </em>
                        </p>
                    </div>
                @endif
                @if (isset($show_committee_self_instruction) && $show_committee_self_instruction)
                    <div style="padding-top: 10px">
                        <p>
                            <em>
                                This questionnaire is meant to get responses on the quality of performance of the {{($committee) ? $committee : null}} as a group. Responses will be used as basis for further improvements in the way the committee apply corporate governance principles in carrying out their regular functions as an Advisory Committee of the Board of Trustees.
                            </em>
                        </p>
                    </div>
                @endif

                @include('pdf.components.rating_scale_2')
            </div>
            @foreach ($sections as $section)
                @if ($section['section_type'] == 1)
                    <div class="row col-xs-12" style="padding-top: 10px">
                        @include('pdf.components.questionaire_1', ['section_data' => $section])
                    </div>
                @elseif($section['section_type'] == 2)
                    <div class="row col-xs-12" style="padding-top: 10px">
                        @include('pdf.components.attendance')
                    </div>
                    <div class="row col-xs-12" style="padding-top: 10px">
                        @include('pdf.components.attendance_rating_scale')
                    </div>
                @endif
            @endforeach
        </div>
        <div class="row col-xs-12" style="padding-top: 10px">
            @include('pdf.components.other_comments')

            <div style="margin-top:20px;">
                @include('pdf.components.cforms_footer')
            </div>
        </div>
    </div>
@endsection
