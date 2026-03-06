@extends('layouts.app')
@section('title', 'Notifications')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Notifications</li>
@endsection
@section('content')
<div class="page-header d-print-none mb-4">
    <div class="container-fluid">
        <div class="row g-2 align-items-center">
            <div class="col"><h2 class="page-title">Notifications</h2><div class="text-secondary mt-1">Late entries and hours shortfall alerts</div></div>
            @if(auth()->user()->unreadNotifications()->count() > 0)
            <div class="col-auto ms-auto"><form method="POST" action="{{ route('notifications.readAll') }}" class="d-inline">@csrf<button type="submit" class="btn btn-outline-secondary">Mark all as read</button></form></div>
            @endif
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        @if($notifications->isEmpty())
        <p class="text-secondary text-center py-5 mb-0">No notifications yet.</p>
        @else
        <ul class="list-unstyled list-separated mb-0">
            @foreach($notifications as $n)
            <li class="list-separated-item {{ $n->read_at ? '' : 'bg-primary-lt' }}">
                <div class="row align-items-center">
                    <div class="col-auto"><span class="status-dot {{ $n->read_at ? 'status-dot-secondary' : 'status-dot-primary' }} d-block"></span></div>
                    <div class="col text-truncate">
                        <span class="text-reset">{{ $n->data['message'] ?? 'Notification' }}</span>
                        <small class="d-block text-secondary">{{ $n->created_at->format('d M Y, H:i') }} · {{ $n->created_at->diffForHumans() }}</small>
                    </div>
                    @if(!$n->read_at)<div class="col-auto"><form method="POST" action="{{ route('notifications.read', $n->id) }}" class="d-inline">@csrf<button type="submit" class="btn btn-sm btn-ghost-primary">Mark read</button></form></div>@endif
                </div>
            </li>
            @endforeach
        </ul>
        @if($notifications->hasPages())<div class="card-footer d-flex align-items-center">{{ $notifications->links() }}</div>@endif
        @endif
    </div>
</div>
@endsection
