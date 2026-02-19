<p>
    <em>
        Please use the numeral rating scale described below:
    </em>
</p>
<table style="width: 80%; border-collapse: collapse; padding-top:10px; margin:auto" border="1.4" >
    <thead>
        <th style="text-align: center; width: 40%"> RATING SCALE</th>
        <th style="text-align: center"> DESCRIPTION</th>
    </thead>
    <tbody class="table-text text-center">
        @foreach ($assesment_rating as $scale)
            <tr class="text-uppercase">
                <td>{{$scale['value']}}</td>
                <td>{{$scale['qualitative']}}</td>
            </tr>
        @endforeach
    </tbody>
</table>