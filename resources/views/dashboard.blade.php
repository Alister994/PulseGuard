@extends('layouts.app')
@section('title', 'Dashboard')
@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection
@section('content')
<div class="page-header d-print-none mb-4">
    <div class="container-fluid">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Dashboard</h2>
                <div class="text-secondary mt-1">Overview of your attendance and payroll</div>
            </div>
        </div>
    </div>
</div>
<div class="row row-deck row-cards mb-4">
    <div class="col-sm-6 col-lg-3">
        <a href="{{ route('employees.index') }}" class="card card-link card-link-pop">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Employees</div>
                    <div class="ms-auto lh-1">
                        <span class="avatar avatar-sm bg-primary-lt text-primary"><i class="ti ti-users"></i></span>
                    </div>
                </div>
                <div class="d-flex align-items-baseline">
                    <div class="h1 mb-0 me-2">{{ $stats['employees'] ?? 0 }}</div>
                </div>
                <div class="text-secondary"><span class="text-reset">View all</span> <i class="ti ti-chevron-right"></i></div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a href="{{ route('departments.index') }}" class="card card-link card-link-pop">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Locations</div>
                    <div class="ms-auto lh-1">
                        <span class="avatar avatar-sm bg-amber-lt text-amber"><i class="ti ti-building"></i></span>
                    </div>
                </div>
                <div class="d-flex align-items-baseline">
                    <div class="h1 mb-0 me-2">{{ $stats['locations'] ?? 0 }}</div>
                </div>
                <div class="text-secondary"><span class="text-reset">Departments</span> <i class="ti ti-chevron-right"></i></div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a href="{{ route('reports.index') }}" class="card card-link card-link-pop">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Present today</div>
                    <div class="ms-auto lh-1">
                        <span class="avatar avatar-sm bg-green-lt text-green"><i class="ti ti-check"></i></span>
                    </div>
                </div>
                <div class="d-flex align-items-baseline">
                    <div class="h1 mb-0 me-2">{{ $stats['today_present'] ?? 0 }}</div>
                </div>
                <div class="text-secondary"><span class="text-reset">Reports</span> <i class="ti ti-chevron-right"></i></div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-3">
        <a href="{{ route('notifications.index') }}" class="card card-link card-link-pop">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Notifications</div>
                    <div class="ms-auto lh-1">
                        <span class="avatar avatar-sm bg-red-lt text-red"><i class="ti ti-bell"></i></span>
                    </div>
                </div>
                <div class="d-flex align-items-baseline">
                    <div class="h1 mb-0 me-2">{{ $stats['unread_notifications'] ?? 0 }}</div>
                </div>
                <div class="text-secondary"><span class="text-reset">View all</span> <i class="ti ti-chevron-right"></i></div>
            </div>
        </a>
    </div>
</div>
<div class="row row-deck row-cards">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick actions</h3>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('employees.create') }}" class="btn btn-primary"><i class="ti ti-user-plus me-1"></i>Add employee</a>
                    <a href="{{ route('payroll.index') }}" class="btn btn-outline-primary"><i class="ti ti-currency-rupee me-1"></i>Payroll</a>
                    <a href="{{ route('reports.index') }}" class="btn btn-outline-primary"><i class="ti ti-download me-1"></i>Export reports</a>
                    <a href="{{ route('departments.create') }}" class="btn btn-outline-secondary"><i class="ti ti-building me-1"></i>New department</a>
                    <a href="{{ route('shifts.create') }}" class="btn btn-outline-secondary"><i class="ti ti-clock me-1"></i>New shift</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent notifications</h3>
                @if(auth()->user()->unreadNotifications()->count() > 0)
                <div class="card-actions">
                    <form method="POST" action="{{ route('notifications.readAll') }}" class="d-inline">@csrf<button type="submit" class="btn btn-outline-secondary btn-sm">Mark all read</button></form>
                </div>
                @endif
            </div>
            <div class="card-body">
                @php $recent = auth()->user()->notifications()->take(5)->get(); @endphp
                @if($recent->isEmpty())
                    <p class="text-secondary mb-0">No notifications yet.</p>
                @else
                    <ul class="list-unstyled list-separated mb-0">
                        @foreach($recent as $n)
                        <li class="list-separated-item {{ $n->read_at ? 'opacity-75' : '' }}">
                            <div class="row align-items-center">
                                <div class="col-auto"><span class="status-dot {{ $n->read_at ? 'status-dot-secondary' : 'status-dot-primary' }} d-block"></span></div>
                                <div class="col text-truncate">
                                    <span class="text-reset">{{ $n->data['message'] ?? 'Notification' }}</span>
                                    <small class="d-block text-secondary">{{ $n->created_at->diffForHumans() }}</small>
                                </div>
                                @if(!$n->read_at)
                                <div class="col-auto">
                                    <form method="POST" action="{{ route('notifications.read', $n->id) }}" class="d-inline">@csrf<button type="submit" class="btn btn-sm btn-ghost-primary">Mark read</button></form>
                                </div>
                                @endif
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-link mt-2">View all notifications</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
