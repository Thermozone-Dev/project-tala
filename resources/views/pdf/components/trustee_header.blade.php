<style scoped>
    table tr td {
        padding: 5px;
    }
    .table-text {
        font-weight: 700;
        font-size: 14px;
    }
    .checkbox {
        display: inline-block;
        width: 10px;
        height: 10px;
        border: 1px solid #000;
        margin-right: 5px;
        vertical-align: middle;
    }

    .checkbox.checked {
        background-color: #000;
    }
</style>
@php
    $isMember = false;
@endphp
<table style="width: 100%; border-collapse: collapse;" border="1.4" >
    <tbody class="table-text">
        <tr class="text-uppercase">
            <td style="width: 30%">Name</td>
            <td colspan="3" >{{$header_data['name']}}</td>
        </tr>
        <tr>
            <td rowspan="{{count($header_data['commitees']) + 1}}" style="width: 30%">Committee Memberships</td>
            @foreach ($header_data['commitees'] as $commitee_chunk)
                <tr>
                    @foreach ($commitee_chunk as $commitee)
                        <td style="white-space: nowrap">
                            <span class="checkbox {{ $commitee['is_member'] ? 'checked' : '' }}"></span>
                            {{$commitee['name']}}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tr>
        <tr>
            <td style="width: 30%">Covered Period</td>
            <td colspan="3">{{$header_data['coverage_period']}}</td>
        </tr>
    </tbody>
</table>