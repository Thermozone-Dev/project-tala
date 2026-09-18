@php
    $headerStyle = '
        background-color: #31859b;
        color: #caf5fb;
        font-weight: bold;
        text-align: center;
        vertical-align: middle;
        border: 1px solid #000000;
        padding: 8px;
    ';

    $centerStyle = '
        text-align: center;
        vertical-align: middle;
        border: 1px solid #000000;
        padding: 8px;
    ';

    $cellStyle = '
        vertical-align: middle;
        border: 1px solid #000000;
        padding: 8px;
    ';

    $naStyle = $centerStyle . ' background-color: #d9d9d9; color: #808080;';
    $absentStyle = $centerStyle . ' background-color: #f8d7da; color: #c00000; font-weight: bold;';
    $presentStyle = $centerStyle . ' background-color: #d4edda; color: #1e7e34; font-weight: bold;';
@endphp

<table>
    <tr>
        <td colspan="{{ $meetings->count() + 4 }}"
            style="text-align:right;font-weight:bold;font-size: 14px;">
            {{ $meeting_range }}
        </td>
    </tr>
    <tr>
        <td colspan="{{ $meetings->count() + 4 }}" style="{{ $headerStyle }} font-size: 14px;">
            Attendance Meetings
        </td>
    </tr>

    <tr>
        <td style="{{ $headerStyle }}">Trustees</td>
        <td colspan="{{ $meetings->count() }}" style="{{ $headerStyle }}">
            Date of Meetings
        </td>
        <td colspan="3" style="{{ $headerStyle }}"></td>
    </tr>
    <tr>
        <td style="{{ $headerStyle }}"></td>
        @foreach ($meetings as $meeting)
            <td style="{{ $headerStyle }}">
                {{ \Carbon\Carbon::parse($meeting->scheduled_at)->format('M d, Y') }}
            </td>
        @endforeach
        <td style="{{ $headerStyle }}">TOTAL # of<br>Meetings Eligible<br>to Attend</td>
        <td style="{{ $headerStyle }}">TOTAL Present</td>
        <td style="{{ $headerStyle }}">Percentage</td>
    </tr>
    <tr>
        <td style="{{ $headerStyle }}"></td>
        @foreach ($meetings as $meeting)
            <td style="{{ $headerStyle }}">
                Special/Regular
            </td>
        @endforeach
        <td style="{{ $headerStyle }}"></td>
        <td style="{{ $headerStyle }}"></td>
        <td style="{{ $headerStyle }}"></td>
    </tr>

    @foreach ($categories as $index => $category)
        @if ($index > 0)
            <tr>
                <td style="{{ $headerStyle }}">{{ $category['label'] }}</td>
                <td colspan="{{ $meetings->count() + 3 }}" style="{{ $headerStyle }}"></td>
            </tr>
        @endif

        @foreach ($category['users'] as $user)
            <tr>
                <td style="{{ $cellStyle }}">{{ $user->full_name }}</td>
                @foreach ($meetings as $meeting)
                    @php
                        $status = $category['matrix'][$user->id][$meeting->id];
                        $statusStyle = match ($status) {
                            'N/A' => $naStyle,
                            '✗'   => $absentStyle,
                            '✓'   => $presentStyle,
                            default => $centerStyle,
                        };
                    @endphp
                    <td style="{{ $statusStyle }}">
                        {{ $status }}
                    </td>
                @endforeach
                <td style="{{ $centerStyle }}">{{ $category['totals'][$user->id]['eligible'] }}</td>
                <td style="{{ $centerStyle }}">{{ $category['totals'][$user->id]['present'] }}</td>
                <td style="{{ $centerStyle }}">{{ $category['totals'][$user->id]['percentage'] }}%</td>
            </tr>
        @endforeach
    @endforeach

    <tr><td colspan="{{ $meetings->count() + 4 }}" style="border: none;"></td></tr>
    <tr><td colspan="{{ $meetings->count() + 4 }}" style="border: none;"></td></tr>
    <tr><td colspan="{{ $meetings->count() + 4 }}" style="border: none;"></td></tr>
    <tr>
        <td rowspan="2" colspan="{{ $meetings->count() + 4 }}" style="{{ $cellStyle }} font-size: 14px;">
            Noted by: Ms Clarence R Mejia, Head, Office of the Board Secretariat ____________________
        </td>
    </tr>
</table>
