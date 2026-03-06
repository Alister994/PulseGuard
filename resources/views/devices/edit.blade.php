@extends('layouts.app')
@section('title', 'Edit Device')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('devices.index') }}">Devices</a></li>
    <li class="breadcrumb-item active">Edit device</li>
@endsection
@section('content')
<div class="page-header d-print-none mb-4"><div class="container-fluid"><h2 class="page-title">Edit Device</h2></div></div>
<div class="card col-lg-6">
    <div class="card-body">
        <form method="POST" action="{{ route('devices.update', $device) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Location *</label>
                <select name="location_id" class="form-select @error('location_id') is-invalid @enderror" required>
                    @foreach($locations as $loc)
                    <option value="{{ $loc->id }}" {{ old('location_id', $device->location_id) == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                    @endforeach
                </select>
                @error('location_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Name *</label>
                <input type="text" name="name" value="{{ old('name', $device->name) }}" required class="form-control @error('name') is-invalid @enderror">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Device serial (optional)</label>
                <input type="text" name="device_serial" value="{{ old('device_serial', $device->device_serial) }}" class="form-control">
            </div>
            <div class="mb-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $device->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active (sync allowed)</label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('devices.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
        </form>
        <hr class="my-3">
        <p class="text-secondary small mb-1">API key (for sync agent / T304F):</p>
        <code class="user-select-all d-block p-2 bg-light rounded">{{ $device->api_key }}</code>
        <p class="text-secondary small mt-2 mb-1">Live example (send this header):</p>
        <pre class="bg-dark text-light p-3 rounded small mb-0 user-select-all"><code>curl -X POST '{{ url('/api/device/push') }}' \
  -H 'X-Device-Key: {{ $device->api_key }}' \
  -H 'Content-Type: application/json' \
  -d '{"PIN":"00000001","DateTime":"{{ now()->format('Y-m-d H:i:s') }}"}'</code></pre>
    </div>
</div>
@endsection
