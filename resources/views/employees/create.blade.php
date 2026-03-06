@extends('layouts.app')
@section('title', 'Add employee')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
    <li class="breadcrumb-item active">Add employee</li>
@endsection
@section('content')
<div class="page-header d-print-none mb-4">
    <div class="container-fluid">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Add employee</h2>
                <div class="text-secondary mt-1">Create a new employee and assign department & shift</div>
            </div>
        </div>
    </div>
</div>
<div class="card col-lg-8">
    <div class="card-body">
        <form method="POST" action="{{ route('employees.store') }}">
            @csrf
            <div class="row mb-3">
                <label class="form-label col-3 col-form-label">Name *</label>
                <div class="col"><input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            </div>
            <div class="row mb-3">
                <label class="form-label col-3 col-form-label">Employee no</label>
                <div class="col"><input type="text" name="employee_no" value="{{ old('employee_no') }}" class="form-control"></div>
            </div>
            <div class="row mb-3">
                <label class="form-label col-3 col-form-label">Address</label>
                <div class="col"><textarea name="address" rows="3" class="form-control @error('address') is-invalid @enderror" placeholder="Street, city, state, PIN">{{ old('address') }}</textarea>@error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            </div>
            <div class="row mb-3">
                <label class="form-label col-3 col-form-label">Department</label>
                <div class="col"><select name="department_id" id="department_id" class="form-select"><option value="">— Select —</option>@foreach($departments as $d)<option value="{{ $d->id }}" {{ old('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>@endforeach</select></div>
            </div>
            <div class="row mb-3">
                <label class="form-label col-3 col-form-label">Shift</label>
                <div class="col"><select name="shift_id" class="form-select"><option value="">— Select —</option>@foreach($shifts as $s)<option value="{{ $s->id }}" data-department="{{ $s->department_id }}" {{ old('shift_id') == $s->id ? 'selected' : '' }}>{{ $s->department->name }} – {{ $s->name }}</option>@endforeach</select></div>
            </div>
            <div class="row mb-3">
                <label class="form-label col-3 col-form-label">Device user ID</label>
                <div class="col"><input type="text" name="device_user_id" value="{{ old('device_user_id') }}" placeholder="e.g. 00000001 (must match device PIN)" class="form-control"></div>
            </div>
            <div class="row mb-3">
                <label class="form-label col-3 col-form-label">Email</label>
                <div class="col"><input type="email" name="email" value="{{ old('email') }}" class="form-control"></div>
            </div>
            <div class="row mb-3">
                <label class="form-label col-3 col-form-label">Phone</label>
                <div class="col"><input type="text" name="phone" value="{{ old('phone') }}" class="form-control"></div>
            </div>
            <div class="row mb-3">
                <label class="form-label col-3 col-form-label">Join date</label>
                <div class="col"><input type="date" name="join_date" value="{{ old('join_date') }}" class="form-control"></div>
            </div>
            <div class="row mb-3">
                <label class="form-label col-3 col-form-label">Salary type *</label>
                <div class="col"><select name="salary_type" required class="form-select"><option value="monthly" {{ old('salary_type') == 'monthly' ? 'selected' : '' }}>Monthly</option><option value="hourly" {{ old('salary_type') == 'hourly' ? 'selected' : '' }}>Hourly</option><option value="daily" {{ old('salary_type') == 'daily' ? 'selected' : '' }}>Daily</option></select></div>
            </div>
            <div class="row mb-3">
                <label class="form-label col-3 col-form-label">Salary value *</label>
                <div class="col"><input type="number" name="salary_value" value="{{ old('salary_value', 0) }}" step="0.01" min="0" required class="form-control"></div>
            </div>
            <div class="row mb-3">
                <label class="form-label col-3 col-form-label">Currency</label>
                <div class="col"><input type="text" name="currency" value="{{ old('currency', 'INR') }}" class="form-control"></div>
            </div>
            <div class="row"><div class="col offset-3"><button type="submit" class="btn btn-primary">Create employee</button><a href="{{ route('employees.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a></div></div>
        </form>
    </div>
</div>
@endsection
