@extends('layouts.app')
@section('title', 'Shifts')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Shifts</li>
@endsection
@section('content')
<div class="page-header d-print-none mb-4">
    <div class="container-fluid">
        <div class="row g-2 align-items-center">
            <div class="col"><h2 class="page-title">Shifts</h2><div class="text-secondary mt-1">Department shifts and break timings</div></div>
            <div class="col-auto ms-auto"><a href="{{ route('shifts.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Add shift</a></div>
        </div>
    </div>
</div>
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-auto"><label class="form-label col-form-label">Department</label></div>
            <div class="col-auto"><select name="department_id" onchange="this.form.submit()" class="form-select"><option value="">All departments</option>@foreach($departments as $dept)<option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>@endforeach</select></div>
        </form>
    </div>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table table-striped">
            <thead><tr><th>Department</th><th>Shift</th><th>Time</th><th>Employees</th><th class="w-1">Actions</th></tr></thead>
            <tbody>
                @foreach($shifts as $s)
                <tr>
                    <td>{{ $s->department->name }}</td>
                    <td class="font-medium">{{ $s->name }}</td>
                    <td class="text-secondary">{{ $s->start_time ? substr($s->start_time, 0, 5) : '-' }} – {{ $s->end_time ? substr($s->end_time, 0, 5) : '-' }}{{ $s->is_night_shift ? ' (night)' : '' }}</td>
                    <td>{{ $s->employees_count }}</td>
                    <td><a href="{{ route('shifts.edit', $s) }}" class="btn btn-sm btn-ghost-primary">Edit</a>
                        <form action="{{ route('shifts.destroy', $s) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this shift?');">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-ghost-danger">Delete</button></form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($shifts->hasPages())<div class="card-footer d-flex align-items-center">{{ $shifts->links() }}</div>@endif
</div>
@endsection
