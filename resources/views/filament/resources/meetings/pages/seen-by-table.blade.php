<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User List</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            color: #333;
        }
        tr:hover {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
<table style="table-layout: fixed">
    <thead>
    <tr>
        <th colspan="2">Name</th>
        <th>Last seen at</th>
    </tr>
    </thead>
    <tbody>
        @foreach($attendees as $attendee)
            <tr>
                <td colspan="2" style="text-wrap">{{$attendee->user->fullname}}</td>
                <td>{{$attendee->seen_at?->format('M d, Y h:i A')}}</td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
