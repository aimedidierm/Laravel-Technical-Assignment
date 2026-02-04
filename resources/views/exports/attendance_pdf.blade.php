<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        h1 { color: #2d3748; border-bottom: 2px solid #4299e1; padding-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th { background-color: #4299e1; color: white; padding: 10px; text-align: left; }
        td { border: 1px solid #e2e8f0; padding: 10px; }
        tr:nth-child(even) { background-color: #f7fafc; }
        .empty { color: #a0aec0; font-style: italic; }
    </style>
</head>
<body>
    <h1>Attendance Report</h1>
    <p><strong>Date:</strong> {{ $date }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Employee</th>
                <th>Email</th>
                <th>Check In</th>
                <th>Check Out</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $index => $attendance)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $attendance->employee->name }}</td>
                <td>{{ $attendance->employee->email }}</td>
                <td>{{ $attendance->check_in?->format('H:i:s') ?? 'N/A' }}</td>
                <td>{{ $attendance->check_out?->format('H:i:s') ?? 'N/A' }}</td>
            </tr>
            @endforeach

            @if ($attendances->isEmpty())
            <tr>
                <td colspan="5" class="empty">No attendance records for this date.</td>
            </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
