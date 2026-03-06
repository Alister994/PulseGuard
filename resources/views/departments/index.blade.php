@extends('layouts.app')
@section('title', 'Departments')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Departments</li>
@endsection
@section('content')
<div class="page-header d-print-none mb-4">
    <div class="container-fluid">
        <div class="row g-2 align-items-center">
            <div class="col"><h2 class="page-title">Departments</h2><div class="text-secondary mt-1">Manage departments and assign employees</div></div>
            <div class="col-auto ms-auto"><a href="{{ route('departments.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Add department</a></div>
        </div>
    </div>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table table-striped">
            <thead><tr><th>Name</th><th>Description</th><th>Employees</th><th class="w-1">Actions</th></tr></thead>
            <tbody>
                @foreach($departments as $d)
                <tr>
                    <td class="font-medium">{{ $d->name }}</td>
                    <td class="text-secondary">{{ $d->description ?? '-' }}</td>
                    <td>{{ $d->employees_count }}</td>
                    <td><a href="{{ route('departments.edit', $d) }}" class="btn btn-sm btn-ghost-primary">Edit</a>
                        <form action="{{ route('departments.destroy', $d) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this department?');">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-ghost-danger">Delete</button></form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($departments->hasPages())<div class="card-footer d-flex align-items-center">{{ $departments->links() }}</div>@endif
</div>
@endsection
