@extends('reports.pdf_watermark')

@section('content')
<div class="content">
    <h1>Salary Slip - {{ $slip->employee->name }}</h1>
    <p><strong>Month:</strong> {{ \Carbon\Carbon::createFromDate($slip->year, $slip->month, 1)->format('F Y') }}</p>
    <p><strong>Employee No:</strong> {{ $slip->employee->employee_no ?? '-' }}</p>
    <table>
        <tr><th>Base Amount</th><td>{{ $slip->employee->currency }} {{ number_format($slip->base_amount, 2) }}</td></tr>
        <tr><th>Additions</th><td>{{ $slip->employee->currency }} {{ number_format($slip->additions, 2) }}</td></tr>
        <tr><th>Deductions</th><td>{{ $slip->employee->currency }} {{ number_format($slip->deductions, 2) }}</td></tr>
        <tr><th><strong>Net Amount</strong></th><td><strong>{{ $slip->employee->currency }} {{ number_format($slip->net_amount, 2) }}</strong></td></tr>
    </table>
    @if($slip->breakdown)
    <h2 style="font-size:14px; margin-top:16px;">Breakdown</h2>
    <table>
        @foreach($slip->breakdown as $key => $value)
        <tr><th>{{ ucfirst(str_replace('_',' ', $key)) }}</th><td>{{ is_numeric($value) ? number_format((float)$value, 2) : $value }}</td></tr>
        @endforeach
    </table>
    @endif
</div>
@endsection
