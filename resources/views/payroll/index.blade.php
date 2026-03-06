@extends('layouts.app')
@section('title', 'Payroll')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Payroll</li>
@endsection
@section('content')
<div class="page-header d-print-none mb-4">
    <div class="container-fluid">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Payroll</h2>
                <div class="text-secondary mt-1">Hourly / monthly salary and slips</div>
            </div>
        </div>
    </div>
</div>
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('payroll.index') }}" class="row g-3">
            <div class="col-auto"><select name="month" class="form-select">@for($m=1;$m<=12;$m++)<option value="{{ $m }}" {{ request('month', date('n')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>@endfor</select></div>
            <div class="col-auto"><select name="year" class="form-select">@for($y=date('Y');$y>=date('Y')-5;$y--)<option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>@endfor</select></div>
            <div class="col-auto"><select name="location_id" class="form-select"><option value="">All locations</option>@foreach($locations as $l)<option value="{{ $l->id }}" {{ request('location_id') == $l->id ? 'selected' : '' }}>{{ $l->name }}</option>@endforeach</select></div>
            <div class="col-auto"><select name="department_id" class="form-select"><option value="">All departments</option>@foreach($departments as $d)<option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>@endforeach</select></div>
            <div class="col-auto"><button type="submit" class="btn btn-outline-primary">Filter</button></div>
        </form>
    </div>
</div>
<div class="card mb-4 bg-primary-lt">
    <div class="card-body">
        <h3 class="card-title">Generate payroll</h3>
        <p class="text-secondary mb-3">Calculate salary for all active employees for a month.</p>
        <form method="POST" action="{{ route('payroll.generate') }}" class="row g-3">
            @csrf
            <div class="col-auto"><select name="month" required class="form-select">@for($m=1;$m<=12;$m++)<option value="{{ $m }}">{{ date('F', mktime(0,0,0,$m,1)) }}</option>@endfor</select></div>
            <div class="col-auto"><select name="year" required class="form-select">@for($y=date('Y');$y>=date('Y')-5;$y--)<option value="{{ $y }}">{{ $y }}</option>@endfor</select></div>
            <div class="col-auto"><button type="submit" class="btn btn-primary">Generate payroll</button></div>
        </form>
    </div>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table table-striped">
            <thead><tr><th>Employee</th><th>Address</th><th>Department</th><th class="text-end">Base</th><th class="text-end">Deductions</th><th class="text-end">Net</th><th class="w-1">PDF</th></tr></thead>
            <tbody>
                @forelse($slips as $s)
                <tr>
                    <td class="font-medium">{{ $s->employee->name ?? '-' }}</td>
                    <td class="text-secondary">{{ \Illuminate\Support\Str::limit($s->employee->address ?? $s->employee->location?->name ?? '-', 40) }}</td>
                    <td class="text-secondary">{{ $s->employee->department->name ?? '-' }}</td>
                    <td class="text-end text-secondary">{{ $s->employee->currency ?? 'INR' }} {{ number_format($s->base_amount, 2) }}</td>
                    <td class="text-end text-secondary">{{ number_format($s->deductions, 2) }}</td>
                    <td class="text-end font-medium">{{ $s->employee->currency ?? 'INR' }} {{ number_format($s->net_amount, 2) }}</td>
                    <td><a href="{{ route('salary-slip.pdf', [$s->employee_id, $s->month, $s->year]) }}" target="_blank" class="btn btn-sm btn-ghost-primary">PDF</a></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-secondary py-4">No payroll records. Generate payroll for a month above.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($slips->hasPages())<div class="card-footer d-flex align-items-center">{{ $slips->withQueryString()->links() }}</div>@endif
</div>
@endsection
