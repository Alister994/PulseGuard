@extends('layouts.app')

@section('content')
<div class="mb-6">
    <a href="{{ route('dashboard') }}" class="text-slate-600 hover:text-slate-800 text-sm">&larr; Back to dashboard</a>
</div>

<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800">{{ $site->name }}</h1>
    <p class="text-slate-600 break-all">{{ $site->url }}</p>
</div>

<div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4 mb-8">
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm text-slate-500">Uptime ({{ $days }}d)</p>
        <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($uptime, 1) }}%</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm text-slate-500">Avg response time</p>
        <p class="text-2xl font-bold text-slate-800 mt-1">{{ $responseTime['avg'] }} ms</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm text-slate-500">SSL</p>
        <p class="text-lg font-semibold mt-1">
            @if($lastSsl?->is_valid)
                <span class="text-emerald-600">Valid until {{ $lastSsl->valid_until->format('M j, Y') }}</span>
            @elseif($lastSsl)
                <span class="text-amber-600">Invalid / expired</span>
            @else
                <span class="text-slate-400">Not checked</span>
            @endif
        </p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm text-slate-500">Check interval</p>
        <p class="text-lg font-semibold text-slate-800 mt-1">{{ $site->check_interval_minutes }} min</p>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-2 mb-8">
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="font-semibold text-slate-800 mb-4">Response time (last 7 days)</h2>
        @if(!empty($responseTime['points']))
            <div class="h-48 flex items-end gap-0.5">
                @php $max = max(1, $responseTime['max']); @endphp
                @foreach(array_slice($responseTime['points'], -24) as $point)
                    <div class="flex-1 bg-emerald-500 rounded-t min-h-[2px]" style="height: {{ min(100, ($point['ms'] / $max) * 100) }}%"></div>
                @endforeach
            </div>
            <p class="text-xs text-slate-500 mt-2">Min: {{ $responseTime['min'] }} ms · Max: {{ $responseTime['max'] }} ms</p>
        @else
            <p class="text-slate-500 text-sm">No data yet.</p>
        @endif
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="font-semibold text-slate-800 mb-4">Recent HTTP checks</h2>
        <div class="space-y-2 max-h-64 overflow-y-auto">
            @forelse($recentChecks as $check)
                <div class="flex justify-between text-sm py-1 border-b border-slate-100">
                    <span>{{ $check->checked_at->format('M j H:i:s') }}</span>
                    <span>
                        @if($check->status === 'up')
                            <span class="text-emerald-600">{{ $check->status_code }} · {{ $check->response_time_ms }} ms</span>
                        @else
                            <span class="text-red-600">{{ $check->status ?? $check->error_message }}</span>
                        @endif
                    </span>
                </div>
            @empty
                <p class="text-slate-500 text-sm">No checks yet.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200">
        <h2 class="font-semibold text-slate-800">Incident history</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase">Started</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase">Resolved</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase">Summary</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($incidents as $incident)
                    <tr>
                        <td class="px-5 py-3 text-sm text-slate-800">{{ $incident->started_at->format('M j, Y H:i') }}</td>
                        <td class="px-5 py-3 text-sm text-slate-600">{{ $incident->resolved_at?->format('M j, Y H:i') ?? '–' }}</td>
                        <td class="px-5 py-3">
                            @if($incident->isResolved())
                                <span class="text-emerald-600 text-sm">Resolved</span>
                            @else
                                <span class="text-amber-600 text-sm">Open</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-sm text-slate-600">{{ Str::limit($incident->summary, 50) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-slate-500">No incidents recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($incidents->hasPages())
        <div class="px-5 py-3 border-t border-slate-200">
            {{ $incidents->links() }}
        </div>
    @endif
</div>
@endsection
