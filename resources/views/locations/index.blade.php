@extends('layouts.app')
@section('title', 'Locations')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Locations</li>
@endsection
@section('content')
<div class="page-header d-print-none mb-4">
    <div class="container-fluid">
        <div class="row g-2 align-items-center">
            <div class="col"><h2 class="page-title">Locations</h2><div class="text-secondary mt-1">Branches — create first, then add devices and employees per location</div></div>
            <div class="col-auto ms-auto"><a href="{{ route('locations.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Add location</a></div>
        </div>
    </div>
</div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table table-striped">
            <thead><tr><th>Name</th><th>Address</th><th>Timezone</th><th>Devices</th><th>Employees</th><th>Active</th><th class="w-1">Actions</th></tr></thead>
            <tbody>
                @foreach($locations as $loc)
                <tr>
                    <td class="font-medium">{{ $loc->name }}</td>
                    <td class="text-secondary">{{ $loc->address ?? '-' }}</td>
                    <td>{{ $loc->timezone ?? 'Asia/Kolkata' }}</td>
                    <td>{{ $loc->devices_count }}</td>
                    <td>{{ $loc->employees_count }}</td>
                    <td>{{ $loc->is_active ? 'Yes' : 'No' }}</td>
                    <td>
                        <a href="{{ route('locations.edit', $loc) }}" class="btn btn-sm btn-ghost-primary">Edit</a>
                        <form action="{{ route('locations.destroy', $loc) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this location?');">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-ghost-danger">Delete</button></form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($locations->hasPages())<div class="card-footer d-flex align-items-center">{{ $locations->links() }}</div>@endif
</div>
@endsection
