<?php

namespace App\Http\Controllers;

use App\Models\AssignLuggage;
use App\Models\DriverCurrentLocation;
use App\Models\DriverLocationLog;
use App\Events\DriverLocationUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DriverLocationController extends Controller
{
    /**
     * Store and broadcast the driver's current location.
     */
    public function updateLocation(Request $request, $orderId)
    {
        $userId = session('user_id');

        // 1. Authorize: Is this assignment assigned to the authenticated user?
        $assignment = AssignLuggage::where('driver_id', $userId)
            ->where('id', $orderId)
            ->first();

        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized or order not found.'
            ], 403);
        }

        // 2. Validate: Is the order actively in "Pickup" status (meaning picked up, in transit)?
        if ($assignment->status !== 'Pickup') {
            return response()->json([
                'success' => false,
                'message' => 'Tracking is inactive for this order status (' . $assignment->status . ').'
            ], 400);
        }

        // 3. Validate coordinates payload
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'speed' => 'nullable|numeric|min:0',
            'heading' => 'nullable|numeric|between:0,360',
            'battery_level' => 'nullable|integer|between:0,100',
        ]);

        try {
            // 4. Update the latest current location of the driver
            DriverCurrentLocation::updateOrCreate(
                ['driver_id' => $userId],
                [
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                    'speed' => $validated['speed'] ?? null,
                    'heading' => $validated['heading'] ?? null,
                    'battery_level' => $validated['battery_level'] ?? null,
                    'is_online' => true,
                    'updated_at' => now(),
                ]
            );

            // 5. Append coordinate to the historical log
            DriverLocationLog::create([
                'order_id' => $assignment->id,
                'driver_id' => $userId,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'speed' => $validated['speed'] ?? null,
                'heading' => $validated['heading'] ?? null,
            ]);

            // 6. Broadcast the live event to subscribers
            broadcast(new DriverLocationUpdated(
                $assignment->id,
                $validated['latitude'],
                $validated['longitude'],
                $validated['speed'] ?? null,
                $validated['heading'] ?? null,
                $validated['battery_level'] ?? null
            ));

            return response()->json([
                'success' => true,
                'message' => 'Location updated and broadcasted successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Driver Location Update Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to save location update.'
            ], 500);
        }
    }

    /**
     * Fetch initial tracking configuration and history logs for the manager dashboard map.
     */
    public function getTrackingData($orderId)
    {
        $userId = session('user_id');

        // Retrieve order with relationships
        $assignment = AssignLuggage::with(['company', 'station', 'driver', 'creator'])->findOrFail($orderId);

        // Authorize: Admin (role_id === 0), the assigned driver, or the assigning manager
        $authorized = false;
        if ($userId) {
            if ($assignment->driver_id === $userId || $assignment->created_by === $userId) {
                $authorized = true;
            } else {
                $user = \App\Models\User::find($userId);
                if ($user && $user->role_id === 0) {
                    $authorized = true;
                }
            }
        }

        if (!$authorized) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view tracking data.'
            ], 403);
        }

        // Fetch latest known driver coordinates
        $currentLocation = DriverCurrentLocation::where('driver_id', $assignment->driver_id)->first();

        // Fetch route path history for this order
        $history = DriverLocationLog::where('order_id', $assignment->id)
            ->orderBy('created_at', 'asc')
            ->select('latitude', 'longitude', 'created_at')
            ->get();

        return response()->json([
            'success' => true,
            'order_id' => $assignment->id,
            'status' => $assignment->status,
            'pickup' => [
                'address' => $assignment->pickup_location,
                'lat' => (float) $assignment->pickup_latitude,
                'lng' => (float) $assignment->pickup_longitude,
            ],
            'destination' => [
                'address' => $assignment->drop_location,
                'lat' => (float) $assignment->drop_latitude,
                'lng' => (float) $assignment->drop_longitude,
            ],
            'driver' => [
                'name' => $assignment->driver->name,
                'email' => $assignment->driver->email,
                'initials' => $assignment->driver->getInitials(),
                'profile_photo' => $assignment->driver->profile_photo ? asset($assignment->driver->profile_photo) : null,
            ],
            'last_location' => $currentLocation ? [
                'lat' => (float) $currentLocation->latitude,
                'lng' => (float) $currentLocation->longitude,
                'speed' => $currentLocation->speed !== null ? (float) $currentLocation->speed : null,
                'heading' => $currentLocation->heading !== null ? (float) $currentLocation->heading : null,
                'battery_level' => $currentLocation->battery_level !== null ? (int) $currentLocation->battery_level : null,
                'updated_at' => $currentLocation->updated_at->toDateTimeString(),
            ] : null,
            'route_history' => $history->map(fn($log) => [
                'lat' => (float) $log->latitude,
                'lng' => (float) $log->longitude
            ])
        ]);
    }
}
