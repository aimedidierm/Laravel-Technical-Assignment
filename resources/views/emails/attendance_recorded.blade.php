<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2>Attendance {{ $type }} Recorded</h2>
    <p>Dear {{ $employee->name }},</p>
    <p>Your attendance <strong>{{ $type }}</strong> has been successfully recorded at <strong>{{ $time }}</strong>.</p>
    <p>If this was not you, please contact HR immediately.</p>
    <p style="color: #888; font-size: 12px;">Best regards, HR Department</p>
</body>
</html>
