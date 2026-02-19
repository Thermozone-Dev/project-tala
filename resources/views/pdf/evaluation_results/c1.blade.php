@extends('pdf.evaluation_results.c_layouts')
@section('content')
    <div class="page">
        <div class="content" style="text-align: justify;">
            <div class="row col-xs-12">
                <h3 class="header3">
                    {{$title}}
                </h3>
                @include('pdf.components.trustee_header')
                @if (isset($show_instruction) && $show_instruction)
                    <div style="padding-top: 10px">
                        <p>
                            <em>
                                This questionnaire is meant to get feedback on the quality of performance of the concerned Corporate Office. Responses will be used as a basis for possible qualification or disqualification for nomination for the immediately succeeding term as AFPSLAI Corporate Officer.
                            </em>
                        </p>
                    </div>
                @endif
                @if (isset($assesment_rating) && $assesment_rating)
                    @include('pdf.components.rating_scale_1')
                @endif
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
                @endif
            @endforeach

            @if ($attendance_rating)
                <div class="row col-xs-12" style="padding-top: 10px">
                    @include('pdf.components.attendance_rating_scale')
                </div>
            @endif

            <div class="row col-xs-12" style="padding-top: 10px">
                @include('pdf.components.other_comments')

                <div style="margin-top:20px;">
                    @include('pdf.components.cforms_footer')
                </div>
            </div>
        </div>
    </div>
@endsection
