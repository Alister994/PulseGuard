@extends('layouts.app')
@section('title', 'Employees')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Employees</li>
@endsection
@section('content')
<div class="page-header d-print-none mb-4">
    <div class="container-fluid">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Employees</h2>
                <div class="text-secondary mt-1">Manage employees, department and shift assignment</div>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ route('employees.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Add employee</a>
            </div>
        </div>
    </div>
</div>
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('employees.index') }}" class="row g-3">
            <div class="col-auto">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, number, device ID" class="form-control">
            </div>
            <div class="col-auto">
                <select name="department_id" class="form-select">
                    <option value="">All departments</option>
                    @foreach($departments as $d)<option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-auto"><button type="submit" class="btn btn-outline-primary">Filter</button></div>
        </form>
    </div>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table table-striped">
            <thead>
                <tr><th>Employee</th><th>No</th><th>Address</th><th>Department</th><th>Shift</th><th>Salary</th><th class="w-1">Actions</th></tr>
            </thead>
            <tbody>
                @forelse($employees as $e)
                <tr>
                    <td><span class="font-medium">{{ $e->name }}</span>@if(!$e->is_active)<span class="badge bg-amber-lt ms-1">Inactive</span>@endif</td>
                    <td class="text-secondary">{{ $e->employee_no ?? '-' }}</td>
                    <td class="text-secondary">{{ \Illuminate\Support\Str::limit($e->address ?? $e->location?->name ?? '-', 40) }}</td>
                    <td class="text-secondary">{{ $e->department->name ?? '-' }}</td>
                    <td class="text-secondary">{{ $e->shift ? $e->shift->name . ' (' . substr($e->shift->start_time, 0, 5) . ')' : '-' }}</td>
                    <td class="text-secondary">{{ $e->salary_type }} · {{ $e->currency }} {{ number_format($e->salary_value, 0) }}</td>
                    <td><a href="{{ route('employees.edit', $e) }}" class="btn btn-sm btn-ghost-primary">Edit</a></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-secondary py-4">No employees found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($employees->hasPages())<div class="card-footer d-flex align-items-center">{{ $employees->withQueryString()->links() }}</div>@endif
</div>
@endsection
