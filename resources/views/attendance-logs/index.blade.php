@extends('layouts.app')
@section('title', 'Attendance logs')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Attendance logs</li>
@endsection
@section('content')
<div class="page-header d-print-none mb-4">
    <div class="container-fluid">
        <div class="row g-2 align-items-center">
            <div class="col"><h2 class="page-title">Attendance logs</h2><div class="text-secondary mt-1">Raw punch data from devices (super admin only)</div></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('attendance-logs.index') }}" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Search</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Device user ID or employee" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Location</label>
                <select name="location_id" class="form-select">
                    <option value="">All</option>
                    @foreach($locations as $loc)
                    <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Device</label>
                <select name="device_id" class="form-select">
                    <option value="">All</option>
                    @foreach($devices as $dev)
                    <option value="{{ $dev->id }}" {{ request('device_id') == $dev->id ? 'selected' : '' }}>{{ $dev->name }} ({{ $dev->location->name ?? '-' }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From date</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">To date</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('attendance-logs.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table table-striped">
            <thead>
                <tr>
                    <th>Punch time</th>
                    <th>Device user ID</th>
                    <th>Employee</th>
                    <th>Device</th>
                    <th>Location</th>
                    <th>Synced at</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td>{{ $log->punch_time->format('Y-m-d H:i:s') }}</td>
                    <td><code>{{ $log->device_user_id }}</code></td>
                    <td>@if($log->employee){{ $log->employee->name }}@else<span class="text-warning">Unmapped</span>@endif</td>
                    <td>{{ $log->device->name ?? '-' }}</td>
                    <td>{{ $log->device->location->name ?? '-' }}</td>
                    <td class="text-secondary">{{ $log->synced_at?->format('Y-m-d H:i') ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-secondary py-4">No logs. Use filters or wait for device punches.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())<div class="card-footer d-flex align-items-center">{{ $logs->links() }}</div>@endif
</div>
@endsection
