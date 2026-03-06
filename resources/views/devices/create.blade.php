@extends('layouts.app')
@section('title', 'Add Device')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('devices.index') }}">Devices</a></li>
    <li class="breadcrumb-item active">Add device</li>
@endsection
@section('content')
<div class="page-header d-print-none mb-4"><div class="container-fluid"><h2 class="page-title">Add Device</h2><div class="text-secondary mt-1">Register RealTime T304F Mini (or similar) to get an API key for sync</div></div></div>
<div class="card col-lg-6">
    <div class="card-body">
        <form method="POST" action="{{ route('devices.store') }}">
            @csrf
            <div class="mb-3"><label class="form-label">Location *</label>
                <select name="location_id" class="form-select" required>@foreach($locations as $loc)<option value="{{ $loc->id }}" {{ old('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">Name *</label><input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Reception T304F Mini" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Device serial (optional)</label><input type="text" name="device_serial" value="{{ old('device_serial') }}" class="form-control"></div>
            <button type="submit" class="btn btn-primary">Create</button><a href="{{ route('devices.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
        </form>
    </div>
</div>
@endsection
