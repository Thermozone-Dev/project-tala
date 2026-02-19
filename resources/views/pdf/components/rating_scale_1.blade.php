<p>
    <em>
        Please use the following rating scale:
    </em>
</p>
@php
    $values = $assesment_rating;
    $count = $values->count();

    $topRow = [];
    $bottomRow = [];
    $lastItem = null;

    foreach ($values as $index => $item) {
        // If odd total and last element → rowspan cell
        if ($count % 2 !== 0 && $index === $count - 1) {
            $lastItem = $item;
        } elseif ($index % 2 === 0) {
            $topRow[] = $item;
        } else {
            $bottomRow[] = $item;
        }
}
@endphp
<table style="width: 100%; border-collapse: collapse;" border="1.4" >
    <tr class="text-uppercase">
        @foreach ($topRow as $item)
            <td>
                {{ $item['value'] }} - {{ $item['qualitative'] }}
            </td>
        @endforeach

        @if ($lastItem)
            <td rowspan="2" style="width:25%">
                {{ $lastItem['value'] }} - {{ $lastItem['qualitative'] }}
            </td>
        @endif
    </tr>

    <tr class="text-uppercase">
        @foreach ($bottomRow as $item)
            <td>
                {{ $item['value'] }} - {{ $item['qualitative'] }}
            </td>
        @endforeach
    </tr>
</table>