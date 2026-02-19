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
            <td colspan="3" >MGEN ADRIANO S PEREZ JR PA (RET)</td>
        </tr>
        <tr>
            <td rowspan="3" style="width: 30%">Committee Memberships</td>
            <tr>
                <td>
                    <span class="checkbox {{ $isMember ? 'checked' : '' }}"></span>
                    Governance
                </td>
                <td>
                    <span class="checkbox {{ $isMember ? 'checked' : '' }}"></span>
                    Audit & Compliance
                </td>
                <td>
                    <span class="checkbox {{ $isMember ? 'checked' : '' }}"></span>
                    Risk Oversight
                </td>
            </tr>
            <tr>
                <td>
                    <span class="checkbox {{ $isMember ? 'checked' : '' }}"></span>
                    Governance
                </td>
                <td>
                    <span class="checkbox {{ $isMember ? 'checked' : '' }}"></span>
                    Audit & Compliance
                </td>
                <td>
                    <span class="checkbox {{ $isMember ? 'checked' : '' }}"></span>
                    Risk Oversight
                </td>
            </tr>
        </tr>
        <tr>
            <td style="width: 20%">Covered Period</td>
            <td colspan="3">JUNE 2025 TO APRIL 2026</td>
        </tr>
    </tbody>
</table>
    {{-- <div class="text-right" style="text-transform: uppercase; border:1px solid rgb(173, 167, 167); padding:8px">
        <p><span style="margin-right: 10px">Subtotal</span>₱ {{number_format($sub_total)}}</p>
        @if ($discount)
            <p><span style="margin-right: 10px">Disc@ {{$discount}}</span>₱ {{number_format($discount_val,2)}}</p>

        @endif
        <p style="font-weight:bold"><span style="margin-right: 10px">Grand total</span>₱ {{number_format($grand_total,2)}}</p>
    </div> --}}