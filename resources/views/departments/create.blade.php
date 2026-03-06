@extends('layouts.app')
@section('title', 'Add Department')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('departments.index') }}">Departments</a></li>
    <li class="breadcrumb-item active">Add department</li>
@endsection
@section('content')
<div class="page-header d-print-none mb-4"><div class="container-fluid"><h2 class="page-title">Add Department</h2></div></div>
<div class="card col-lg-6">
    <div class="card-body">
        <form method="POST" action="{{ route('departments.store') }}">
            @csrf
            <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" value="{{ old('name') }}" required class="form-control @error('name') is-invalid @enderror">@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="mb-3"><label class="form-label">Description (optional)</label><textarea name="description" class="form-control">{{ old('description') }}</textarea></div>
            <button type="submit" class="btn btn-primary">Create</button><a href="{{ route('departments.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
        </form>
    </div>
</div>
@endsection
