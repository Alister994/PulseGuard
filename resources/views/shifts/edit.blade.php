@extends('layouts.app')
@section('title', 'Edit Shift')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('shifts.index') }}">Shifts</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection
@section('content')
<div class="page-header d-print-none mb-4"><div class="container-fluid"><h2 class="page-title">Edit Shift: {{ $shift->name }} ({{ $shift->department->name }})</h2></div></div>
<div class="card col-lg-8">
    <div class="card-body">
        <form method="POST" action="{{ route('shifts.update', $shift) }}">
            @csrf
            @method('PUT')
            <div class="mb-3"><label class="form-label">Shift name</label><input type="text" name="name" value="{{ old('name', $shift->name) }}" required class="form-control"></div>
            <div class="row mb-3"><div class="col-md-6"><label class="form-label">Start time</label><input type="time" name="start_time" value="{{ old('start_time', $shift->start_time ? substr($shift->start_time, 0, 5) : '09:00') }}" required class="form-control"></div><div class="col-md-6"><label class="form-label">End time</label><input type="time" name="end_time" value="{{ old('end_time', $shift->end_time ? substr($shift->end_time, 0, 5) : '18:00') }}" required class="form-control"></div></div>
            <div class="mb-3"><label class="form-check"><input type="checkbox" name="is_night_shift" value="1" class="form-check-input" {{ old('is_night_shift', $shift->is_night_shift) ? 'checked' : '' }}><span class="form-check-label">Night shift</span></label></div>
            <div class="mb-3"><label class="form-label">Grace minutes</label><input type="number" name="grace_minutes" value="{{ old('grace_minutes', $shift->grace_minutes) }}" min="0" max="120" class="form-control w-25"></div>
            <div class="mb-3"><label class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" {{ old('is_active', $shift->is_active) ? 'checked' : '' }}><span class="form-check-label">Active</span></label></div>
            <div class="mb-4"><label class="form-label">Breaks</label>
                @foreach($shift->shiftBreaks as $i => $b)
                <div class="break-row row g-2 align-items-end mb-2">
                    <input type="hidden" name="breaks[{{ $i }}][id]" value="{{ $b->id }}">
                    <div class="col-auto"><select name="breaks[{{ $i }}][break_type]" class="form-select"><option value="lunch" {{ $b->break_type === 'lunch' ? 'selected' : '' }}>Lunch</option><option value="dinner" {{ $b->break_type === 'dinner' ? 'selected' : '' }}>Dinner</option><option value="tea" {{ $b->break_type === 'tea' ? 'selected' : '' }}>Tea</option></select></div>
                    <div class="col-auto"><input type="time" name="breaks[{{ $i }}][start_time]" value="{{ $b->start_time ? substr($b->start_time, 0, 5) : '' }}" class="form-control"></div>
                    <div class="col-auto"><input type="time" name="breaks[{{ $i }}][end_time]" value="{{ $b->end_time ? substr($b->end_time, 0, 5) : '' }}" class="form-control"></div>
                    <div class="col-auto"><input type="number" name="breaks[{{ $i }}][duration_minutes]" value="{{ $b->duration_minutes }}" min="0" class="form-control"></div>
                    <input type="hidden" name="breaks[{{ $i }}][sort_order]" value="{{ $i }}">
                </div>
                @endforeach
                <div class="break-row row g-2 align-items-end mb-2">
                    <input type="hidden" name="breaks[{{ $shift->shiftBreaks->count() }}][id]" value="">
                    <div class="col-auto"><select name="breaks[{{ $shift->shiftBreaks->count() }}][break_type]" class="form-select"><option value="lunch">Lunch</option><option value="dinner">Dinner</option><option value="tea">Tea</option></select></div>
                    <div class="col-auto"><input type="time" name="breaks[{{ $shift->shiftBreaks->count() }}][start_time]" class="form-control"></div>
                    <div class="col-auto"><input type="time" name="breaks[{{ $shift->shiftBreaks->count() }}][end_time]" class="form-control"></div>
                    <div class="col-auto"><input type="number" name="breaks[{{ $shift->shiftBreaks->count() }}][duration_minutes]" min="0" placeholder="Min" class="form-control"></div>
                    <input type="hidden" name="breaks[{{ $shift->shiftBreaks->count() }}][sort_order]" value="{{ $shift->shiftBreaks->count() }}">
                </div>
                <p class="text-secondary small mt-1">Add one more row above to add another break. Leave new row empty to ignore.</p>
            </div>
            <button type="submit" class="btn btn-primary">Update</button><a href="{{ route('shifts.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
        </form>
    </div>
</div>
@endsection
