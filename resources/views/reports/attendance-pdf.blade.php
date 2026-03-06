<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #333; position: relative; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 72px; color: rgba(0,0,0,0.06); white-space: nowrap; z-index: 0; }
        .content { position: relative; z-index: 1; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f1f5f9; }
        h1 { font-size: 16px; margin-bottom: 8px; }
    </style>
</head>
<body>
    <div class="watermark">{{ $watermarkText ?? 'BIOTIME' }}</div>
    <div class="content">
        <h1>Attendance report</h1>
        <p>{{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}</p>
        <table>
            <thead>
                <tr><th>Date</th><th>Employee</th><th>No</th><th>Address</th><th>Department</th><th>Duty In</th><th>Duty Out</th><th>Work (hrs)</th><th>Lunch</th><th>Tea</th><th>Late</th><th>Status</th></tr>
            </thead>
            <tbody>
                @foreach($rows as $r)
                <tr>
                    <td>{{ $r->date->format('Y-m-d') }}</td>
                    <td>{{ $r->employee->name ?? '' }}</td>
                    <td>{{ $r->employee->employee_no ?? '' }}</td>
                    <td>{{ $r->employee->address ?? $r->employee->location?->name ?? '' }}</td>
                    <td>{{ $r->employee->department->name ?? '' }}</td>
                    <td>{{ $r->punch_1_at?->format('H:i') ?? '-' }}</td>
                    <td>{{ $r->punch_6_at?->format('H:i') ?: ($r->punch_4_at?->format('H:i') ?? '-') }}</td>
                    <td>{{ number_format($r->work_minutes / 60, 2) }}</td>
                    <td>{{ $r->lunch_minutes }}</td>
                    <td>{{ $r->tea_minutes }}</td>
                    <td>{{ $r->late_minutes }}</td>
                    <td>{{ $r->status }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
