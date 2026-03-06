<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Shift;
use App\Models\ShiftBreak;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function index(Request $request): View
    {
        $query = Shift::with('department')->withCount('employees');
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        $shifts = $query->orderBy('department_id')->orderBy('name')->paginate(20);
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        return view('shifts.index', compact('shifts', 'departments'));
    }

    public function create(Request $request): View
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $departmentId = $request->get('department_id');
        return view('shifts.create', compact('departments', 'departmentId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:64'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'is_night_shift' => ['boolean'],
            'grace_minutes' => ['nullable', 'integer', 'min:0', 'max:120'],
            'breaks' => ['nullable', 'array'],
            'breaks.*.break_type' => ['required', 'string', 'in:lunch,dinner,tea'],
            'breaks.*.start_time' => ['nullable', 'date_format:H:i'],
            'breaks.*.end_time' => ['nullable', 'date_format:H:i'],
            'breaks.*.duration_minutes' => ['nullable', 'integer', 'min:0', 'max:240'],
            'breaks.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        $shift = Shift::create([
            'department_id' => $validated['department_id'],
            'name' => $validated['name'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'is_night_shift' => $request->boolean('is_night_shift'),
            'grace_minutes' => $validated['grace_minutes'] ?? 0,
            'is_active' => true,
        ]);
        if (! empty($validated['breaks'])) {
            $sort = 0;
            foreach ($validated['breaks'] as $b) {
                if (empty($b['start_time']) && empty($b['end_time']) && empty($b['duration_minutes'])) {
                    continue;
                }
                ShiftBreak::create([
                    'shift_id' => $shift->id,
                    'break_type' => $b['break_type'],
                    'start_time' => $b['start_time'] ?? null,
                    'end_time' => $b['end_time'] ?? null,
                    'duration_minutes' => $b['duration_minutes'] ?? null,
                    'sort_order' => $b['sort_order'] ?? $sort++,
                ]);
            }
        }
        return redirect()->route('shifts.index')->with('success', 'Shift created.');
    }

    public function edit(Shift $shift): View
    {
        $shift->load('shiftBreaks');
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        return view('shifts.edit', compact('shift', 'departments'));
    }

    public function update(Request $request, Shift $shift): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:64'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'is_night_shift' => ['boolean'],
            'grace_minutes' => ['nullable', 'integer', 'min:0', 'max:120'],
            'is_active' => ['boolean'],
            'breaks' => ['nullable', 'array'],
            'breaks.*.id' => ['nullable', 'exists:shift_breaks,id'],
            'breaks.*.break_type' => ['required', 'string', 'in:lunch,dinner,tea'],
            'breaks.*.start_time' => ['nullable', 'date_format:H:i'],
            'breaks.*.end_time' => ['nullable', 'date_format:H:i'],
            'breaks.*.duration_minutes' => ['nullable', 'integer', 'min:0', 'max:240'],
            'breaks.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        $shift->update([
            'name' => $validated['name'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'is_night_shift' => $request->boolean('is_night_shift'),
            'grace_minutes' => $validated['grace_minutes'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);
        $keepIds = [];
        if (! empty($validated['breaks'])) {
            $sort = 0;
            foreach ($validated['breaks'] as $b) {
                $attrs = [
                    'shift_id' => $shift->id,
                    'break_type' => $b['break_type'],
                    'start_time' => $b['start_time'] ?? null,
                    'end_time' => $b['end_time'] ?? null,
                    'duration_minutes' => $b['duration_minutes'] ?? null,
                    'sort_order' => $b['sort_order'] ?? $sort++,
                ];
                if (! empty($b['id'])) {
                    $breakModel = ShiftBreak::where('shift_id', $shift->id)->find($b['id']);
                    if ($breakModel) {
                        $breakModel->update($attrs);
                        $keepIds[] = $breakModel->id;
                        continue;
                    }
                }
                if (empty($attrs['start_time']) && empty($attrs['end_time']) && empty($attrs['duration_minutes'])) {
                    continue;
                }
                $newBreak = ShiftBreak::create($attrs);
                $keepIds[] = $newBreak->id;
            }
        }
        $shift->shiftBreaks()->whereNotIn('id', $keepIds)->delete();
        return redirect()->route('shifts.index')->with('success', 'Shift updated.');
    }

    public function destroy(Shift $shift): RedirectResponse
    {
        if ($shift->employees()->exists()) {
            return redirect()->route('shifts.index')->with('error', 'Cannot delete shift with assigned employees. Reassign first.');
        }
        $shift->shiftBreaks()->delete();
        $shift->delete();
        return redirect()->route('shifts.index')->with('success', 'Shift deleted.');
    }
}
