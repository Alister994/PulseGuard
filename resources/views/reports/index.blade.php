@extends('layouts.app')
@section('title', 'Reports')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Reports</li>
@endsection
@section('content')
<div class="page-header d-print-none mb-4">
    <div class="container-fluid">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Reports</h2>
                <div class="text-secondary mt-1">Export attendance and payroll with filters to Excel or PDF</div>
            </div>
        </div>
    </div>
</div>
<div class="row row-deck row-cards">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Attendance report</h3></div>
            <div class="card-body">
                <p class="text-secondary mb-4">Export daily attendance by date range. Filter by location and department.</p>
                <form method="GET" action="{{ route('reports.attendance') }}" target="_blank" id="att-form">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6"><label class="form-label">From date *</label><input type="date" name="from" value="{{ request('from', now()->startOfMonth()->format('Y-m-d')) }}" required class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">To date *</label><input type="date" name="to" value="{{ request('to', now()->format('Y-m-d')) }}" required class="form-control"></div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6"><label class="form-label">Location</label><select name="location_id" class="form-select"><option value="">All</option>@foreach($locations as $l)<option value="{{ $l->id }}">{{ $l->name }}</option>@endforeach</select></div>
                        <div class="col-md-6"><label class="form-label">Department</label><select name="department_id" class="form-select"><option value="">All</option>@foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select></div>
                    </div>
                    <input type="hidden" name="format" value="excel" id="att-format">
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('att-format').value='excel'; document.getElementById('att-form').submit();"><i class="ti ti-file-spreadsheet me-1"></i>Export Excel</button>
                    <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('att-format').value='pdf'; document.getElementById('att-form').submit();"><i class="ti ti-file-text me-1"></i>Export PDF</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Payroll report</h3></div>
            <div class="card-body">
                <p class="text-secondary mb-4">Export payroll summary for a month. Filter by location and department.</p>
                <form method="GET" action="{{ route('reports.payroll') }}" target="_blank" id="pay-form">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6"><label class="form-label">Month *</label><select name="month" required class="form-select">@for($m=1;$m<=12;$m++)<option value="{{ $m }}" {{ request('month', date('n')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>@endfor</select></div>
                        <div class="col-md-6"><label class="form-label">Year *</label><select name="year" required class="form-select">@for($y=date('Y');$y>=date('Y')-5;$y--)<option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>@endfor</select></div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6"><label class="form-label">Location</label><select name="location_id" class="form-select"><option value="">All</option>@foreach($locations as $l)<option value="{{ $l->id }}">{{ $l->name }}</option>@endforeach</select></div>
                        <div class="col-md-6"><label class="form-label">Department</label><select name="department_id" class="form-select"><option value="">All</option>@foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select></div>
                    </div>
                    <input type="hidden" name="format" value="excel" id="pay-format">
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('pay-format').value='excel'; document.getElementById('pay-form').submit();"><i class="ti ti-file-spreadsheet me-1"></i>Export Excel</button>
                    <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('pay-format').value='pdf'; document.getElementById('pay-form').submit();"><i class="ti ti-file-text me-1"></i>Export PDF</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
