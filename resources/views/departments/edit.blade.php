@extends('layouts.app')
@section('title', 'Edit Department')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('departments.index') }}">Departments</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection
@section('content')
<div class="page-header d-print-none mb-4"><div class="container-fluid"><h2 class="page-title">Edit Department</h2></div></div>
<div class="card col-lg-6">
    <div class="card-body">
        <form method="POST" action="{{ route('departments.update', $department) }}">
            @csrf
            @method('PUT')
            <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" value="{{ old('name', $department->name) }}" required class="form-control @error('name') is-invalid @enderror">@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="mb-3"><label class="form-label">Description (optional)</label><textarea name="description" class="form-control">{{ old('description', $department->description) }}</textarea></div>
            <div class="mb-3"><label class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" {{ old('is_active', $department->is_active) ? 'checked' : '' }}><span class="form-check-label">Active</span></label></div>
            <button type="submit" class="btn btn-primary">Update</button><a href="{{ route('departments.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
        </form>
    </div>
</div>
@endsection
