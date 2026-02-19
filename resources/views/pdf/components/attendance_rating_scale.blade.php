<h3 class="header4" style="margin-bottom: 2px">
    Rating Scale:
</h3>

<table style="width: 100%; border-collapse: collapse;" border="1" >
    <thead>
        <th style="width: 60%"></th>
        <th style="text-align:center">Quantitative</th>
        <th style="text-align:center">Qualitative</th>
    </thead>
    <tbody class="table-text">
        @foreach ($attendance_rating as $scale)
            <tr>
                <td style="font-weight: normal">{{$scale->name}}</td>
                <td style="font-weight: normal">{{$scale->value}}</td>
                <td style="font-weight: normal">{{$scale->qualitative}}</td>
            </tr>
        @endforeach
    </tbody>
</table>