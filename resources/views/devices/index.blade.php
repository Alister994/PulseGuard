@extends('layouts.app')
@section('title', 'Devices')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Devices</li>
@endsection
@section('content')
<div class="page-header d-print-none mb-4">
    <div class="container-fluid">
        <div class="row g-2 align-items-center">
            <div class="col"><h2 class="page-title">Devices</h2><div class="text-secondary mt-1">RealTime T304F Mini (or similar) — register and get API key for sync</div></div>
            <div class="col-auto ms-auto"><a href="{{ route('devices.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Add device</a></div>
        </div>
    </div>
</div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('new_device_api_key'))<div class="alert alert-info"><strong>API key:</strong> <code>{{ session('new_device_api_key') }}</code></div>@endif
<div class="card mb-4">
    <div class="card-header"><h3 class="card-title">How to send punches (live)</h3></div>
    <div class="card-body">
        <p class="mb-2">All devices use the <strong>same push URL</strong>; the <strong>API key in header</strong> <code>X-Device-Key</code> identifies which device (each device has its own key in the central database). PIN can be device user id (e.g. <code>00000001</code>).</p>
        <pre class="bg-dark text-light p-3 rounded small mb-0"><code>curl -X POST '{{ url('/api/device/push') }}' \
  -H 'X-Device-Key: YOUR_DEVICE_API_KEY' \
  -H 'Content-Type: application/json' \
  -d '{"PIN":"00000001","DateTime":"{{ now()->format('Y-m-d H:i:s') }}"}'</code></pre>
        <p class="text-secondary small mt-2 mb-0">Get <code>YOUR_DEVICE_API_KEY</code> from each device (Edit device → copy API key).</p>
    </div>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table table-striped">
            <thead><tr><th>Name</th><th>Location</th><th>API key</th><th>Last sync</th><th>Logs</th><th></th></tr></thead>
            <tbody>
                @forelse($devices as $d)
                <tr>
                    <td>{{ $d->name }}</td>
                    <td>{{ $d->location->name ?? '-' }}</td>
                    <td><span class="text-secondary">per device</span> <a href="{{ route('devices.edit', $d) }}" class="btn btn-sm btn-ghost-primary">Copy key</a></td>
                    <td>{{ $d->last_sync_at ? $d->last_sync_at->format('M j, H:i') : '-' }}</td>
                    <td>{{ $d->attendance_logs_count }}</td>
                    <td><a href="{{ route('devices.edit', $d) }}" class="btn btn-sm btn-ghost-primary">Edit</a>
                        <form action="{{ route('devices.destroy', $d) }}" method="POST" class="d-inline">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-ghost-danger">Delete</button></form></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-secondary py-4">No devices. Add your RealTime T304F Mini to get an API key for the sync agent.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($devices->hasPages())<div class="card-footer">{{ $devices->links() }}</div>@endif
</div>
@endsection
