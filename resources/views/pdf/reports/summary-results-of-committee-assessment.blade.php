
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
                <div class="page">
                    <div class="row" style="page-break-inside: avoid;">
                        <div class="col-xs-4" style="white-space:nowrap;border: 1px solid black">
                            <h3>SCALE:</h3>
                            @foreach (array_reverse($data['rating_scales']) as $index => $rating_scale)
                                @if($index < 4)
                                    <p>{{ $rating_scale['attendance_quantitative'] }} - {{ $rating_scale['scale'] }}</p>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="row" style="white-space:nowrap;">
                        <div class="col-xs-12 text-center">
                            <h1><b><u>{{strtoupper('BOT SELF-ASSESSMENT')}}</u></b></h1>
                        </div>
                    </div>

                    <div class="row" style="white-space:nowrap;">
                        <div class="col-xs-12" style="white-space:nowrap; margin: 10px 0">
                            <table>
                                <thead class="header-color">
                                    <tr>
                                        <th style="width: 3%;">#</th>
                                        <th>Gen Romeo S Brawner PA</th>
                                        <th>RADM Nichols A Driz PN (Ret)</th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th colspan="9" class="text-center">Assessment Rating</th>
                                        <th>Total</th>
                                        <th>Average</th>
                                        <th>Qualitative Rating</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- data --}}
                                    <tr class="header-color">
                                        <th colspan="12" class="text-left">&emsp;A. PERFORMANCE AS A GOVERNING BOARD</th>
                                    </tr>
                                    <tr>
                                        <td class="text-center">1</td>
                                        <td class="text-center">-</td>
                                        <td class="text-center">-</td>
                                        <td class="text-center">-</td>
                                        <td class="text-center">-</td>
                                        <td class="text-center">-</td>
                                        <td class="text-center">-</td>
                                        <td class="text-center">-</td>
                                        <td class="text-center">-</td>
                                        <td class="text-center">-</td>
                                        <td class="text-center">-</td>
                                        <td class="text-center">Strongly Agree</td>
                                    </tr>

                                    <tr>
                                        <td colspan="9" class="text-center"></td>
                                        <td class="text-center font-bold">299.00</td>
                                        <td class="text-center font-bold">3.99</td>
                                        <td class="text-center font-bold">Somewhat Agree</td>
                                    </tr>

                                    <tr class="header-color">
                                        <th colspan="12" class="text-left">&emsp;B. COMPOSITION AND QUALITY OF LEADERSHIP</th>
                                    </tr>
                                    <tr>
                                        <td class="text-center">1</td>
                                        <td class="text-center">-</td>
                                        <td class="text-center">-</td>
                                        <td class="text-center">-</td>
                                        <td class="text-center">-</td>
                                        <td class="text-center">-</td>
                                        <td class="text-center">-</td>
                                        <td class="text-center">-</td>
                                        <td class="text-center">-</td>
                                        <td class="text-center">-</td>
                                        <td class="text-center">-</td>
                                        <td class="text-center">Strongly Agree</td>
                                    </tr>

                                    <tr>
                                        <td colspan="9" class="text-center"></td>
                                        <td class="text-center font-bold">299.00</td>
                                        <td class="text-center font-bold">3.99</td>
                                        <td class="text-center font-bold">Somewhat Agree</td>
                                    </tr>

                                    <tr class="header-color">
                                        <th colspan="9" class="text-center"> Over-all Average</th>
                                        <th class="text-center font-bold">299.00</th>
                                        <th class="text-center font-bold">3.99</th>
                                        <th class="text-center font-bold">Somewhat Agree</th>
                                    </tr>
                                    {{-- data --}}

                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
    </body>
</html>
