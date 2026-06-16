<?php

namespace App\Http\Controllers;

use App\Models\AssignLuggage;
use App\Models\Company;
use App\Models\Station;
use App\Models\Role;
use App\Models\User;
use App\Http\Requests\StoreAssignLuggageRequest;
use App\Http\Requests\UpdateAssignLuggageRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignLuggageController extends Controller
{
    /**
     * Display a listing of the luggage assignments.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = AssignLuggage::with(['company', 'station', 'driver', 'creator']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('pickup_location', 'like', "%{$search}%")
                  ->orWhere('drop_location', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhereHas('company', function($c) use ($search) {
                      $c->where('company_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('driver', function($d) use ($search) {
                      $d->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $assignments = $query->latest()->paginate(10);
        return view('admin.assign-luggage.index', compact('assignments', 'search'));
    }

    /**
     * Show the form for creating a new assignment.
     */
    public function create()
    {
        $companies = Company::where('status', 'active')->orderBy('company_name', 'asc')->get();
        $stations = Station::where('status', 'active')->orderBy('station_name', 'asc')->get();
        
        $driverRole = Role::where('role_name', 'Driver')->first();
        $drivers = User::where('role_id', $driverRole ? $driverRole->id : -1)
                       ->where('status', 0)
                       ->orderBy('name', 'asc')
                       ->get();

        return view('admin.assign-luggage.create', compact('companies', 'stations', 'drivers'));
    }

    /**
     * Store a newly created assignment in storage.
     */
    public function store(StoreAssignLuggageRequest $request)
    {
        $data = $request->validated();
        
        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('luggages', 'public');
                $images[] = $path;
            }
        }

        $data['images'] = $images;
        $data['created_by'] = session('user_id');

        AssignLuggage::create($data);

        return redirect()->route('assign-luggage.index')->with('success', 'Luggage assignment created successfully.');
    }

    /**
     * Display the specified assignment.
     */
    public function show($id)
    {
        $assignment = AssignLuggage::with(['company', 'station', 'driver', 'creator'])->findOrFail($id);
        return view('admin.assign-luggage.show', compact('assignment'));
    }

    /**
     * Show the form for editing the specified assignment.
     */
    public function edit($id)
    {
        $assignment = AssignLuggage::findOrFail($id);
        $companies = Company::where('status', 'active')->orderBy('company_name', 'asc')->get();
        $stations = Station::where('status', 'active')->orderBy('station_name', 'asc')->get();

        $driverRole = Role::where('role_name', 'Driver')->first();
        $drivers = User::where('role_id', $driverRole ? $driverRole->id : -1)
                       ->where('status', 0)
                       ->orderBy('name', 'asc')
                       ->get();

        return view('admin.assign-luggage.edit', compact('assignment', 'companies', 'stations', 'drivers'));
    }

    /**
     * Update the specified assignment in storage.
     */
    public function update(UpdateAssignLuggageRequest $request, $id)
    {
        $assignment = AssignLuggage::findOrFail($id);
        $data = $request->validated();

        $existingImages = $assignment->images ?? [];
        
        // Retained images submitted from the form (as JSON string or array)
        $retainedInput = $request->input('retained_images', []);
        if (is_string($retainedInput)) {
            $retainedImages = json_decode($retainedInput, true) ?? [];
        } else {
            $retainedImages = $retainedInput;
        }

        // Delete removed files from disk
        $deletedImages = array_diff($existingImages, $retainedImages);
        foreach ($deletedImages as $img) {
            Storage::disk('public')->delete($img);
        }

        // Upload any new images
        $newImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('luggages', 'public');
                $newImages[] = $path;
            }
        }

        $data['images'] = array_merge($retainedImages, $newImages);
        $data['created_by'] = session('user_id');

        $assignment->update($data);

        return redirect()->route('assign-luggage.index')->with('success', 'Luggage assignment updated successfully.');
    }

    /**
     * Remove the specified assignment from storage.
     */
    public function destroy($id)
    {
        $assignment = AssignLuggage::findOrFail($id);
        $images = $assignment->images ?? [];
        
        foreach ($images as $img) {
            Storage::disk('public')->delete($img);
        }

        $assignment->delete();

        return redirect()->route('assign-luggage.index')->with('success', 'Luggage assignment deleted successfully.');
    }
}
