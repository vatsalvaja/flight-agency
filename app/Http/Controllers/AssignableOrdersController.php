<?php

namespace App\Http\Controllers;

use App\Models\AssignLuggage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AssignableOrdersController extends Controller
{
    /**
     * Display a listing of driver's assignments.
     */
    public function index(Request $request)
    {
        return view('admin.assignable-orders.index');
    }

    /**
     * Return assigned driver orders for AJAX rendering.
     */
    public function list(Request $request)
    {
        $userId = session('user_id');

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please login again.',
            ], 401);
        }

        $assignments = AssignLuggage::with(['company', 'station'])
            ->where('driver_id', $userId)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'counts' => [
                'all' => $assignments->count(),
                'in_progress' => $assignments->where('status', 'In Progress')->count(),
                'pickup' => $assignments->where('status', 'Pickup')->count(),
                'delivered' => $assignments->where('status', 'Delivered')->count(),
            ],
            'data' => $assignments->map(fn ($assignment) => $this->formatAssignmentForAjax($assignment))->values(),
        ]);
    }

    /**
     * Display the specified assigned order.
     */
    public function show($id)
    {
        $userId = session('user_id');
        $assignment = AssignLuggage::with(['company', 'station', 'driver'])
            ->where('driver_id', $userId)
            ->findOrFail($id);

        return view('admin.assignable-orders.show', compact('assignment'));
    }

    /**
     * Return order details for AJAX rendering.
     */
    public function data($id)
    {
        $userId = session('user_id');

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please login again.',
            ], 401);
        }

        $assignment = AssignLuggage::with(['company', 'station', 'driver'])
            ->where('driver_id', $userId)
            ->find($id);

        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or not assigned to you.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatAssignmentForAjax($assignment, true),
        ]);
    }

    /**
     * Update order status to Pickup.
     */
    public function pickup(Request $request, $id)
    {
        $userId = session('user_id');
        $assignment = AssignLuggage::where('driver_id', $userId)
            ->findOrFail($id);

        if ($assignment->status !== 'In Progress') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Workflow step violation. Order is not in "In Progress" status.',
                ], 422);
            }

            return redirect()->back()->with('error', 'Workflow step violation. Order is not in "In Progress" status.');
        }

        $assignment->update([
            'status' => 'Pickup'
        ]);

        try {
            $assignment->load(['driver', 'creator', 'company', 'station']);
            app(\App\Services\SMTPConfigurationService::class)->sendOrderPickedUpEmail($assignment);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('SMTP Notification Error (pickup): ' . $e->getMessage());
        }

        if ($request->expectsJson()) {
            $assignment->refresh()->load(['company', 'station', 'driver']);

            return response()->json([
                'success' => true,
                'message' => 'Order status updated to Pickup.',
                'data' => $this->formatAssignmentForAjax($assignment, true),
            ]);
        }

        return redirect()->route('assignable-orders.show', $id)->with('success', 'Order status updated to Pickup.');
    }

    /**
     * Mark the order as Delivered.
     */
    public function deliver(Request $request, $id)
    {
        $userId = session('user_id');
        $assignment = AssignLuggage::where('driver_id', $userId)
            ->findOrFail($id);

        if ($assignment->status !== 'Pickup') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Workflow step violation. Order is not in "Pickup" status.',
                ], 422);
            }

            return redirect()->back()->with('error', 'Workflow step violation. Order is not in "Pickup" status.');
        }

        $request->validate([
            'proof_images' => 'required|array|min:1',
            'proof_images.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'proof_images.required' => 'You must upload at least one delivery proof image.',
            'proof_images.*.image' => 'The file must be an image.',
            'proof_images.*.mimes' => 'Allowed formats are: jpeg, png, jpg, webp.',
            'proof_images.*.max' => 'Maximum file size allowed is 2MB per image.',
        ]);

        $proofImages = [];
        if ($request->hasFile('proof_images')) {
            foreach ($request->file('proof_images') as $file) {
                // Store image in proofs folder
                $path = $this->storePublicUpload($file, 'proofs');
                $proofImages[] = $path;
            }
        }

        $assignment->update([
            'status' => 'Delivered',
            'delivered_at' => Carbon::now(),
            'delivery_proof_images' => $proofImages
        ]);

        try {
            $assignment->load(['driver', 'creator', 'company', 'station']);
            app(\App\Services\SMTPConfigurationService::class)->sendOrderDeliveredEmail($assignment);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('SMTP Notification Error (deliver): ' . $e->getMessage());
        }

        if ($request->expectsJson()) {
            $assignment->refresh()->load(['company', 'station', 'driver']);

            return response()->json([
                'success' => true,
                'message' => 'Order successfully delivered!',
                'data' => $this->formatAssignmentForAjax($assignment, true),
            ]);
        }

        return redirect()->route('assignable-orders.show', $id)->with('success_delivered', 'Order successfully delivered!');
    }

    private function formatAssignmentForAjax(AssignLuggage $assignment, bool $includeDetails = false): array
    {
        $company = $assignment->company;
        $station = $assignment->station;

        $data = [
            'id' => $assignment->id,
            'order_number' => 'ORD-' . str_pad($assignment->id, 5, '0', STR_PAD_LEFT),
            'status' => $assignment->status,
            'company' => [
                'name' => optional($company)->company_name ?? 'N/A',
                'logo' => optional($company)->logo ? asset($company->logo) : null,
                'initial' => optional($company)->company_name ? substr($company->company_name, 0, 1) : 'C',
                'code' => optional($company)->company_code,
            ],
            'station' => [
                'name' => optional($station)->station_name ?? 'N/A',
                'code' => optional($station)->station_code,
            ],
            'pickup_location' => $assignment->pickup_location,
            'pickup_latitude' => $assignment->pickup_latitude !== null ? (float) $assignment->pickup_latitude : null,
            'pickup_longitude' => $assignment->pickup_longitude !== null ? (float) $assignment->pickup_longitude : null,
            'drop_location' => $assignment->drop_location,
            'drop_latitude' => $assignment->drop_latitude !== null ? (float) $assignment->drop_latitude : null,
            'drop_longitude' => $assignment->drop_longitude !== null ? (float) $assignment->drop_longitude : null,
            'distance_km' => $assignment->distance_km ?? '0.00',
            'expected_delivery_date' => optional($assignment->expected_delivery_date)->format('d M, Y h:i A') ?? 'N/A',
            'created_at' => optional($assignment->created_at)->format('d M, Y H:i A') ?? 'N/A',
            'delivered_at' => optional($assignment->delivered_at)->format('d M, Y H:i A'),
            'show_url' => route('assignable-orders.show', $assignment->id),
            'data_url' => route('assignable-orders.data', $assignment->id),
            'pickup_url' => route('assignable-orders.pickup', $assignment->id),
            'deliver_url' => route('assignable-orders.deliver', $assignment->id),
            'location_url' => route('assignable-orders.location', $assignment->id),
        ];

        if ($includeDetails) {
            $data['notes'] = $assignment->notes ?: 'No special handling instructions provided.';
            $data['delivery_proof_images'] = collect($assignment->delivery_proof_images ?? [])
                ->map(fn ($image) => [
                    'path' => $image,
                    'url' => asset($image),
                ])
                ->values();
        }

        return $data;
    }
}
