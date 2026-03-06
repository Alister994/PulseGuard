<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeviceController extends Controller
{
    public function index(): View
    {
        $devices = Device::with('location')->withCount('attendanceLogs')->orderBy('name')->paginate(20);
        return view('devices.index', compact('devices'));
    }

    public function create(): View
    {
        $locations = Location::where('is_active', true)->orderBy('name')->get();
        return view('devices.create', compact('locations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'location_id' => ['required', 'exists:locations,id'],
            'name' => ['required', 'string', 'max:128'],
            'device_serial' => ['nullable', 'string', 'max:128'],
        ]);
        $device = Device::create($validated + ['is_active' => true]);
        return redirect()->route('devices.index')->with('success', 'Device created. Use the API key below for the sync agent.')->with('new_device_api_key', $device->api_key);
    }

    public function edit(Device $device): View
    {
        $locations = Location::where('is_active', true)->orderBy('name')->get();
        return view('devices.edit', compact('device', 'locations'));
    }

    public function update(Request $request, Device $device): RedirectResponse
    {
        $validated = $request->validate([
            'location_id' => ['required', 'exists:locations,id'],
            'name' => ['required', 'string', 'max:128'],
            'device_serial' => ['nullable', 'string', 'max:128'],
            'is_active' => ['nullable', 'in:0,1'],
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        $device->update($validated);
        return redirect()->route('devices.index')->with('success', 'Device updated.');
    }

    public function destroy(Device $device): RedirectResponse
    {
        $device->delete();
        return redirect()->route('devices.index')->with('success', 'Device deleted.');
    }
}
