@extends('layouts.app')
@section('title', 'Add Location')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('locations.index') }}">Locations</a></li>
    <li class="breadcrumb-item active">Add location</li>
@endsection
@section('content')
<div class="page-header d-print-none mb-4"><div class="container-fluid"><h2 class="page-title">Add Location</h2><div class="text-secondary mt-1">Branch or site — devices and employees are linked to a location</div></div></div>
<div class="card col-lg-6">
    <div class="card-body">
        <form method="POST" action="{{ route('locations.store') }}">
            @csrf
            <div class="mb-3"><label class="form-label">Name *</label><input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Main Branch" class="form-control @error('name') is-invalid @enderror">@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="mb-3"><label class="form-label">Address (optional)</label><input type="text" name="address" value="{{ old('address') }}" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Timezone (optional)</label><input type="text" name="timezone" value="{{ old('timezone', 'Asia/Kolkata') }}" placeholder="Asia/Kolkata" class="form-control"></div>
            <button type="submit" class="btn btn-primary">Create</button><a href="{{ route('locations.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
        </form>
    </div>
</div>
@endsection
