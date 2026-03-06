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
        .text-right { text-align: right; }
        h1 { font-size: 16px; margin-bottom: 8px; }
    </style>
</head>
<body>
    <div class="watermark">{{ $watermarkText ?? 'BIOTIME' }}</div>
    <div class="content">
        <h1>Payroll report – {{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }}</h1>
        <table>
            <thead>
                <tr><th>Employee</th><th>No</th><th>Address</th><th>Department</th><th class="text-right">Base</th><th class="text-right">Additions</th><th class="text-right">Deductions</th><th class="text-right">Net</th></tr>
            </thead>
            <tbody>
                @foreach($slips as $s)
                <tr>
                    <td>{{ $s->employee->name ?? '' }}</td>
                    <td>{{ $s->employee->employee_no ?? '' }}</td>
                    <td>{{ $s->employee->address ?? $s->employee->location?->name ?? '' }}</td>
                    <td>{{ $s->employee->department->name ?? '' }}</td>
                    <td class="text-right">{{ number_format($s->base_amount, 2) }}</td>
                    <td class="text-right">{{ number_format($s->additions, 2) }}</td>
                    <td class="text-right">{{ number_format($s->deductions, 2) }}</td>
                    <td class="text-right">{{ number_format($s->net_amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
