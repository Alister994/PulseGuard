<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Device;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceLogController extends Controller
{
    /**
     * Super admin only: view raw attendance logs with search and filters.
     */
    public function index(Request $request): View
    {
        $query = AttendanceLog::with('device.location', 'employee')
            ->orderByDesc('punch_time');

        if ($request->filled('location_id')) {
            $query->whereHas('device', fn ($q) => $q->where('location_id', $request->location_id));
        }
        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('punch_time', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('punch_time', '<=', $request->date_to);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($qb) use ($q) {
                $qb->where('device_user_id', 'like', "%{$q}%")
                    ->orWhereHas('employee', fn ($eq) => $eq->where('name', 'like', "%{$q}%")
                        ->orWhere('employee_no', 'like', "%{$q}%")
                        ->orWhere('device_user_id', 'like', "%{$q}%"));
            });
        }

        $logs = $query->paginate(50)->withQueryString();

        $locations = Location::where('is_active', true)->orderBy('name')->get();
        $devices = Device::with('location')->orderBy('name')->get();

        return view('attendance-logs.index', compact('logs', 'locations', 'devices'));
    }
}
