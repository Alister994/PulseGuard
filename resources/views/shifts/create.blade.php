@extends('layouts.app')
@section('title', 'Add Shift')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('shifts.index') }}">Shifts</a></li>
    <li class="breadcrumb-item active">Add shift</li>
@endsection
@section('content')
<div class="page-header d-print-none mb-4"><div class="container-fluid"><h2 class="page-title">Add Shift</h2></div></div>
<div class="card col-lg-8">
    <div class="card-body">
        <form method="POST" action="{{ route('shifts.store') }}">
            @csrf
            <div class="mb-3"><label class="form-label">Department</label><select name="department_id" required class="form-select">@foreach($departments as $d)<option value="{{ $d->id }}" {{ (old('department_id') ?? $departmentId ?? '') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">Shift name (e.g. Day, Night)</label><input type="text" name="name" value="{{ old('name') }}" required class="form-control"></div>
            <div class="row mb-3"><div class="col-md-6"><label class="form-label">Start time</label><input type="time" name="start_time" value="{{ old('start_time', '09:00') }}" required class="form-control"></div><div class="col-md-6"><label class="form-label">End time</label><input type="time" name="end_time" value="{{ old('end_time', '18:00') }}" required class="form-control"></div></div>
            <div class="mb-3"><label class="form-check"><input type="checkbox" name="is_night_shift" value="1" class="form-check-input" {{ old('is_night_shift') ? 'checked' : '' }}><span class="form-check-label">Night shift (end time is next day)</span></label></div>
            <div class="mb-3"><label class="form-label">Grace minutes (late tolerance)</label><input type="number" name="grace_minutes" value="{{ old('grace_minutes', 0) }}" min="0" max="120" class="form-control w-25"></div>
            <div class="mb-4"><label class="form-label">Breaks (lunch / dinner / tea)</label>
                <div id="breaks">
                    <div class="break-row row g-2 align-items-end mb-2">
                        <div class="col-auto"><select name="breaks[0][break_type]" class="form-select"><option value="lunch">Lunch</option><option value="dinner">Dinner</option><option value="tea">Tea</option></select></div>
                        <div class="col-auto"><input type="time" name="breaks[0][start_time]" placeholder="Start" class="form-control"></div>
                        <div class="col-auto"><input type="time" name="breaks[0][end_time]" placeholder="End" class="form-control"></div>
                        <div class="col-auto"><input type="number" name="breaks[0][duration_minutes]" placeholder="Duration min" min="0" class="form-control"></div>
                        <input type="hidden" name="breaks[0][sort_order]" value="0">
                    </div>
                </div>
                <p class="text-secondary small mt-1">Use fixed start/end times or duration in minutes. Add more breaks in Edit.</p>
            </div>
            <button type="submit" class="btn btn-primary">Create Shift</button><a href="{{ route('shifts.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
        </form>
    </div>
</div>
@endsection
