<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Http\Requests\StoreStationRequest;
use App\Http\Requests\UpdateStationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('search', '');

        return view('admin.stations.index', compact('search'));
    }

    /**
     * Return stations for AJAX rendering.
     */
    public function list(Request $request)
    {
        $stations = $this->stationQuery($request->query('search'))
            ->orderBy('id', 'desc')
            ->get()
            ->map(function (Station $station) {
                return $this->formatStation($station);
            });

        return response()->json([
            'success' => true,
            'message' => 'Stations loaded successfully.',
            'data' => $stations,
        ]);
    }

    /**
     * Return one station for AJAX details/edit population.
     */
    public function getDataById(Station $station)
    {
        return response()->json([
            'success' => true,
            'message' => 'Station loaded successfully.',
            'data' => $this->formatStation($station),
        ]);
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
        if ($request->expectsJson() || $request->ajax()) {
            return $this->save($request);
        }

        Station::create($request->validated());

        return redirect()->route('stations.index')->with('success', 'Station created successfully.');
    }

    /**
     * Save a station through a single AJAX endpoint for create and update.
     */
    public function save(Request $request)
    {
        $station = $request->filled('id') ? Station::find($request->input('id')) : null;

        if ($request->filled('id') && ! $station) {
            return $this->stationErrorResponse($request, 'Station not found.', 404);
        }

        $validator = Validator::make($request->all(), $this->validationRules($station?->id));

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please check the form errors below.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        if ($station) {
            $station->update($validated);
            $message = 'Station updated successfully.';
        } else {
            $station = Station::create($validated);
            $message = 'Station created successfully.';
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $this->formatStation($station->fresh()),
            ]);
        }

        return redirect()->route('stations.index')->with('success', $message);
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
        if ($request->expectsJson() || $request->ajax()) {
            $request->merge(['id' => $station->id]);

            return $this->save($request);
        }

        $station->update($request->validated());

        return redirect()->route('stations.index')->with('success', 'Station updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Station $station)
    {
        $station->delete();

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Station deleted successfully.',
                'data' => null,
            ]);
        }

        return redirect()->route('stations.index')->with('success', 'Station deleted successfully.');
    }

    private function stationQuery(?string $search)
    {
        $query = Station::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('station_name', 'like', '%' . $search . '%')
                    ->orWhere('station_code', 'like', '%' . $search . '%')
                    ->orWhere('city', 'like', '%' . $search . '%')
                    ->orWhere('state', 'like', '%' . $search . '%')
                    ->orWhere('country', 'like', '%' . $search . '%');
            });
        }

        return $query;
    }

    private function validationRules(?int $stationId = null): array
    {
        return [
            'station_name' => 'required|string|max:255',
            'station_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('stations', 'station_code')->ignore($stationId),
            ],
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'address' => 'nullable|string',
            'contact_number' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive',
        ];
    }

    private function formatStation(Station $station): array
    {
        return [
            'id' => $station->id,
            'station_name' => $station->station_name,
            'station_code' => $station->station_code,
            'city' => $station->city,
            'state' => $station->state,
            'country' => $station->country,
            'address' => $station->address,
            'contact_number' => $station->contact_number,
            'email' => $station->email,
            'status' => $station->status,
            'show_url' => route('stations.show', $station->id),
            'edit_url' => route('stations.edit', $station->id),
            'delete_url' => route('stations.destroy', $station->id),
            'data_url' => route('stations.data', $station->id),
            'created_at' => $station->created_at ? $station->created_at->format('M d, Y h:i A') : null,
            'updated_at' => $station->updated_at ? $station->updated_at->format('M d, Y h:i A') : null,
        ];
    }

    private function stationErrorResponse(Request $request, string $message, int $status)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'data' => null,
            ], $status);
        }

        return redirect()->route('stations.index')->with('error', $message);
    }
}
