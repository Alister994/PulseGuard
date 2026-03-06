@extends('layouts.app')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800">Dashboard</h1>
    <p class="text-slate-600 mt-1">Real-time uptime overview and incident history</p>
</div>

<form method="get" action="{{ route('dashboard') }}" class="flex flex-wrap gap-4 mb-6">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search sites..." class="rounded-lg border-slate-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
    <select name="status" class="rounded-lg border-slate-300 shadow-sm focus:ring-emerald-500">
        <option value="">All sites</option>
        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active only</option>
        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive only</option>
    </select>
    <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-700">Filter</button>
</form>

@if($summary->isEmpty())
    <div class="rounded-xl border border-slate-200 bg-white p-12 text-center">
        <p class="text-slate-600">No monitored sites yet.</p>
        <p class="text-slate-500 text-sm mt-2">Add sites via the API or run migrations and seed data.</p>
    </div>
@else
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @foreach($summary as $item)
            <a href="{{ route('dashboard.site', $item['site']) }}" class="block rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-slate-300 transition">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="font-semibold text-slate-800">{{ $item['site']->name }}</h2>
                        <p class="text-sm text-slate-500 truncate max-w-[200px]">{{ $item['site']->url }}</p>
                    </div>
                    @if($item['status'] === 'up')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Up</span>
                    @elseif($item['status'] === 'down' || $item['status'] === 'timeout')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Down</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">Unknown</span>
                    @endif
                </div>
                <div class="mt-4 flex items-center justify-between text-sm">
                    <span class="text-slate-600">Uptime (30d): <strong>{{ number_format($item['uptime_percentage'], 1) }}%</strong></span>
                    @if($item['response_time_ms'] !== null)
                        <span class="text-slate-500">{{ $item['response_time_ms'] }} ms</span>
                    @endif
                </div>
                @if($item['has_open_incident'])
                    <p class="mt-2 text-xs text-amber-600">Open incident since {{ $item['open_incident']->started_at->format('M j, H:i') }}</p>
                @endif
                @if($item['last_checked_at'])
                    <p class="mt-1 text-xs text-slate-400">Last check: {{ $item['last_checked_at']->diffForHumans() }}</p>
                @endif
            </a>
        @endforeach
    </div>
@endif
@endsection
