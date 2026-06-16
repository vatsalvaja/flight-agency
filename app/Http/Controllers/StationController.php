<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Http\Requests\StoreStationRequest;
use App\Http\Requests\UpdateStationRequest;
use Illuminate\Http\Request;

class StationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = Station::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('station_name', 'like', '%' . $search . '%')
                  ->orWhere('station_code', 'like', '%' . $search . '%')
                  ->orWhere('city', 'like', '%' . $search . '%')
                  ->orWhere('state', 'like', '%' . $search . '%')
                  ->orWhere('country', 'like', '%' . $search . '%');
            });
        }

        $stations = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.stations.index', compact('stations', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.stations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStationRequest $request)
    {
        Station::create($request->validated());

        return redirect()->route('stations.index')->with('success', 'Station created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Station $station)
    {
        return view('admin.stations.show', compact('station'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Station $station)
    {
        return view('admin.stations.edit', compact('station'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStationRequest $request, Station $station)
    {
        $station->update($request->validated());

        return redirect()->route('stations.index')->with('success', 'Station updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Station $station)
    {
        $station->delete();

        return redirect()->route('stations.index')->with('success', 'Station deleted successfully.');
    }
}
