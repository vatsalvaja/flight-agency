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
        $userId = session('user_id');
        
        // Drivers only see orders assigned to themselves
        $query = AssignLuggage::with(['company', 'station'])
            ->where('driver_id', $userId);

        // Filter and get data
        $assignments = $query->latest()->get();

        return view('admin.assignable-orders.index', compact('assignments'));
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
     * Update order status to Pickup.
     */
    public function pickup($id)
    {
        $userId = session('user_id');
        $assignment = AssignLuggage::where('driver_id', $userId)
            ->findOrFail($id);

        if ($assignment->status !== 'In Progress') {
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

        return redirect()->route('assignable-orders.show', $id)->with('success_delivered', 'Order successfully delivered!');
    }
}
