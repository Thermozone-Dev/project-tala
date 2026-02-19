
<h3 class="header4" style="margin-bottom: 2px">
    {{$section['title']}}

</h3>
<table style="width: 100%; border-collapse: collapse;" border="1" >
    <thead>
        <th></th>
        @foreach ($section['attendance']['criteria'] as $criteria)
            <th style="text-align:center">{{$criteria}}</th>
        @endforeach
    </thead>
    <tbody class="table-text">
        @foreach ($section['attendance']['meetings'] as $meeting)
            <tr>
                <td style="font-weight: normal">{{$meeting}}</td>
                @for ($i = 0; $i < count($section['attendance']['criteria']); $i++)
                    <td style="text-align:center">
                        {{-- @if (isset($attendance['data'][$meeting][$i]) && $attendance['data'][$meeting][$i])
                            &#10003;
                        @endif --}}
                    </td>
                @endfor
            </tr>
        @endforeach
    </tbody>
</table>
<p><em>*during incumbency</em></p>
