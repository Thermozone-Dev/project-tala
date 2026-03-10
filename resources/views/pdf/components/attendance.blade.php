
<h3 class="header4" style="margin-bottom: 2px">
    {{$section['title']}}
</h3>
<table style="width: 100%; border-collapse: collapse;" border="1" >
    <thead>
        <th></th>
        @php
            $criteria_count = 0;
        @endphp
        @foreach ($section['attendance']['criteria'] as $criteria)
            @if ($criteria['show'] == true)
                @php
                    $criteria_count++;
                @endphp
                <th style="text-align:center">{{$criteria['name']}}</th>
            @endif
        @endforeach
    </thead>
    <tbody class="table-text">
        @foreach ($section['attendance']['meetings'] as $meeting)
            <tr>
                <td style="font-weight: normal">{{$meeting['name']}}</td>
                @foreach ($section['attendance']['criteria'] as $index => $column)
                    {{-- @dd($column, $index) --}}
                    @if ($column['show'] == true)
                        <td style="text-align:center">
                            @if (isset($meeting[$index]) && $meeting[$index])
                                {{$meeting[$index]}}
                            @else
                                {{-- &#10003; --}}
                            @endif
                        </td>
                    @endif
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
<p><em>*during incumbency</em></p>
