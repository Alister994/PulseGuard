<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(): View
    {
        $locations = Location::withCount(['devices', 'employees'])->orderBy('name')->paginate(20);
        return view('locations.index', compact('locations'));
    }

    public function create(): View
    {
        return view('locations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:128'],
            'address' => ['nullable', 'string', 'max:255'],
            'timezone' => ['nullable', 'string', 'max:64'],
        ]);
        Location::create($validated + ['is_active' => true]);
        return redirect()->route('locations.index')->with('success', 'Location created.');
    }

    public function edit(Location $location): View
    {
        return view('locations.edit', compact('location'));
    }

    public function update(Request $request, Location $location): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:128'],
            'address' => ['nullable', 'string', 'max:255'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        $location->update($validated);
        return redirect()->route('locations.index')->with('success', 'Location updated.');
    }

    public function destroy(Location $location): RedirectResponse
    {
        if ($location->devices()->exists() || $location->employees()->exists()) {
            return redirect()->route('locations.index')->with('error', 'Cannot delete location with devices or employees. Remove or reassign them first.');
        }
        $location->delete();
        return redirect()->route('locations.index')->with('success', 'Location deleted.');
    }
}
