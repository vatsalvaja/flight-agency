<?php

namespace App\Http\Controllers;

use App\Models\AssignLuggage;
use App\Models\Company;
use App\Models\Station;
use App\Models\Role;
use App\Models\User;
use App\Http\Requests\StoreAssignLuggageRequest;
use App\Http\Requests\UpdateAssignLuggageRequest;
use App\Services\SMTPConfigurationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AssignLuggageController extends Controller
{
    /**
     * Display a listing of the luggage assignments.
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');

        return view('admin.assign-luggage.index', compact('search'));
    }

    /**
     * Return assignments for AJAX rendering.
     */
    public function list(Request $request)
    {
        $assignments = $this->assignmentQuery($request->input('search'))
            ->latest()
            ->get()
            ->map(function (AssignLuggage $assignment) {
                return $this->formatAssignment($assignment);
            });

        return response()->json([
            'success' => true,
            'message' => 'Assignments loaded successfully.',
            'data' => $assignments,
        ]);
    }

    /**
     * Return a single assignment for AJAX view/edit usage.
     */
    public function getDataById($id)
    {
        $assignment = AssignLuggage::with(['company', 'station', 'driver', 'creator'])->findOrFail($id);
        $authResponse = $this->authorizeAssignmentAccess($assignment);

        if ($authResponse) {
            return $authResponse;
        }

        return response()->json([
            'success' => true,
            'message' => 'Assignment loaded successfully.',
            'data' => $this->formatAssignment($assignment),
        ]);
    }

    private function assignmentQuery(?string $search)
    {
        $userId = session('user_id');
        $user = User::find($userId);
        
        $query = AssignLuggage::with(['company', 'station', 'driver', 'creator']);

        // Filter assignments: Managers can only see their own assigned list, Drivers can only see their own assigned list
        if ($user && $user->role_id !== 0 && $user->role) {
            if (stripos($user->role->role_name, 'manager') !== false) {
                $query->where('created_by', $userId);
            } elseif (stripos($user->role->role_name, 'driver') !== false) {
                $query->where('driver_id', $userId);
            }
        }

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

        return $query;
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

        $managerRole = Role::where('role_name', 'Manager')->first();
        $managers = User::where('role_id', $managerRole ? $managerRole->id : -1)
                        ->where('status', 0)
                        ->orderBy('name', 'asc')
                        ->get();

        $indigoCompanyId = $this->indigoCompanyId($companies);

        return view('admin.assign-luggage.create', compact('companies', 'stations', 'drivers', 'managers', 'indigoCompanyId'));
    }

    /**
     * Store a newly created assignment in storage.
     */
    public function store(StoreAssignLuggageRequest $request)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return $this->save($request);
        }

        $data = $request->validated();
        
        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $this->storePublicUpload($file, 'luggages');
                $images[] = $path;
            }
        }

        $data['images'] = $images;

        $userId = session('user_id');
        $user = User::find($userId);
        if ($user && $user->role_id > 0 && $user->role && stripos($user->role->role_name, 'driver') !== false) {
            $data['created_by'] = $request->input('manager_id');
        } else {
            $data['created_by'] = $userId;
        }

        $data['status'] = 'In Progress'; // Automatically set to In Progress on creation

        $assignment = AssignLuggage::create($data);

        // Database Assignment Successful -> WhatsApp Notification -> Email Notification
        Log::info("WhatsApp Notification: Simulated successfully for assignment #{$assignment->id}");

        try {
            $assignment->load(['driver', 'creator', 'company', 'station']);
            app(SMTPConfigurationService::class)->sendOrderAssignmentEmail($assignment);
        } catch (\Exception $e) {
            Log::error('SMTP Notification Error (store): ' . $e->getMessage());
        }

        return redirect()->route('assign-luggage.index')->with('success', 'Luggage assignment created successfully.');
    }

    /**
     * Save an assignment through one AJAX endpoint for create and update.
     */
    public function save(Request $request)
    {
        $assignment = $request->filled('id') ? AssignLuggage::find($request->input('id')) : null;

        if ($request->filled('id') && ! $assignment) {
            return $this->assignmentErrorResponse($request, 'Luggage assignment not found.', 404);
        }

        if ($assignment) {
            $authResponse = $this->authorizeAssignmentAccess($assignment);
            if ($authResponse) {
                return $authResponse;
            }
        }

        $this->prepareDriverAssignmentFields($request);

        $validator = Validator::make($request->all(), $this->validationRules());

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

        $data = $validator->validated();
        unset($data['manager_id'], $data['retained_images']);

        if ($assignment) {
            $data['status'] = $assignment->status;
            $data['images'] = $this->mergeAssignmentImages($request, $assignment);
            $data['created_by'] = $this->createdByForAssignment($request, $assignment);
            $assignment->update($data);
            $message = 'Luggage assignment updated successfully.';
        } else {
            $data['images'] = $this->uploadedAssignmentImages($request);
            $data['created_by'] = $this->createdByForAssignment($request);
            $data['status'] = 'In Progress';
            $assignment = AssignLuggage::create($data);
            $message = 'Luggage assignment created successfully.';
        }

        $this->sendAssignmentNotifications($assignment, $assignment->wasRecentlyCreated ? 'store' : 'update');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $this->formatAssignment($assignment->fresh(['company', 'station', 'driver', 'creator'])),
            ]);
        }

        return redirect()->route('assign-luggage.index')->with('success', $message);
    }

    /**
     * Display the specified assignment.
     */
    public function show($id)
    {
        $assignment = AssignLuggage::with(['company', 'station', 'driver', 'creator'])->findOrFail($id);
        $userId = session('user_id');
        $user = User::find($userId);
        if ($user && $user->role_id !== 0 && $user->role) {
            if (stripos($user->role->role_name, 'driver') !== false && $assignment->driver_id !== $userId) {
                return redirect()->route('assign-luggage.index')->with('error', 'Unauthorized access to this luggage assignment.');
            }
            if (stripos($user->role->role_name, 'manager') !== false && $assignment->created_by !== $userId) {
                return redirect()->route('assign-luggage.index')->with('error', 'Unauthorized access to this luggage assignment.');
            }
        }
        return view('admin.assign-luggage.show', compact('assignment'));
    }

    /**
     * Show the form for editing the specified assignment.
     */
    public function edit($id)
    {
        $assignment = AssignLuggage::findOrFail($id);
        $userId = session('user_id');
        $user = User::find($userId);
        if ($user && $user->role_id !== 0 && $user->role) {
            if (stripos($user->role->role_name, 'driver') !== false && $assignment->driver_id !== $userId) {
                return redirect()->route('assign-luggage.index')->with('error', 'Unauthorized access to this luggage assignment.');
            }
            if (stripos($user->role->role_name, 'manager') !== false && $assignment->created_by !== $userId) {
                return redirect()->route('assign-luggage.index')->with('error', 'Unauthorized access to this luggage assignment.');
            }
        }

        $companies = Company::where('status', 'active')->orderBy('company_name', 'asc')->get();
        $stations = Station::where('status', 'active')->orderBy('station_name', 'asc')->get();

        $driverRole = Role::where('role_name', 'Driver')->first();
        $drivers = User::where('role_id', $driverRole ? $driverRole->id : -1)
                       ->where('status', 0)
                       ->orderBy('name', 'asc')
                       ->get();

        $managerRole = Role::where('role_name', 'Manager')->first();
        $managers = User::where('role_id', $managerRole ? $managerRole->id : -1)
                        ->where('status', 0)
                        ->orderBy('name', 'asc')
                        ->get();

        $indigoCompanyId = $this->indigoCompanyId($companies);

        return view('admin.assign-luggage.edit', compact('assignment', 'companies', 'stations', 'drivers', 'managers', 'indigoCompanyId'));
    }

    /**
     * Update the specified assignment in storage.
     */
    public function update(UpdateAssignLuggageRequest $request, $id)
    {
        if ($request->expectsJson() || $request->ajax()) {
            $request->merge(['id' => $id]);

            return $this->save($request);
        }

        $assignment = AssignLuggage::findOrFail($id);
        $userId = session('user_id');
        $user = User::find($userId);
        if ($user && $user->role_id !== 0 && $user->role) {
            if (stripos($user->role->role_name, 'driver') !== false && $assignment->driver_id !== $userId) {
                return redirect()->route('assign-luggage.index')->with('error', 'Unauthorized access to this luggage assignment.');
            }
            if (stripos($user->role->role_name, 'manager') !== false && $assignment->created_by !== $userId) {
                return redirect()->route('assign-luggage.index')->with('error', 'Unauthorized access to this luggage assignment.');
            }
        }

        $data = $request->validated();
        
        // Status is managed by the driver workflow; do not let manager edit it.
        $data['status'] = $assignment->status;

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
            $this->deletePublicUpload($img);
        }

        // Upload any new images
        $newImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $this->storePublicUpload($file, 'luggages');
                $newImages[] = $path;
            }
        }

        $data['images'] = array_merge($retainedImages, $newImages);

        if ($user && $user->role_id > 0 && $user->role && stripos($user->role->role_name, 'driver') !== false) {
            $data['created_by'] = $request->input('manager_id');
        } else {
            $data['created_by'] = $assignment->created_by ?? $userId;
        }

        $assignment->update($data);

        // Database Assignment Successful -> WhatsApp Notification -> Email Notification
        Log::info("WhatsApp Notification: Simulated successfully for assignment #{$assignment->id}");

        try {
            $assignment->load(['driver', 'creator', 'company', 'station']);
            app(SMTPConfigurationService::class)->sendOrderAssignmentEmail($assignment);
        } catch (\Exception $e) {
            Log::error('SMTP Notification Error (update): ' . $e->getMessage());
        }

        return redirect()->route('assign-luggage.index')->with('success', 'Luggage assignment updated successfully.');
    }

    /**
     * Remove the specified assignment from storage.
     */
    public function destroy($id)
    {
        $assignment = AssignLuggage::findOrFail($id);
        $userId = session('user_id');
        $user = User::find($userId);
        if ($user && $user->role_id !== 0 && $user->role) {
            if (stripos($user->role->role_name, 'driver') !== false && $assignment->driver_id !== $userId) {
                if (request()->expectsJson() || request()->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized access to this luggage assignment.', 'data' => null], 403);
                }
                return redirect()->route('assign-luggage.index')->with('error', 'Unauthorized access to this luggage assignment.');
            }
            if (stripos($user->role->role_name, 'manager') !== false && $assignment->created_by !== $userId) {
                if (request()->expectsJson() || request()->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized access to this luggage assignment.', 'data' => null], 403);
                }
                return redirect()->route('assign-luggage.index')->with('error', 'Unauthorized access to this luggage assignment.');
            }
        }

        $images = $assignment->images ?? [];

        foreach ($images as $img) {
            $this->deletePublicUpload($img);
        }

        // Remove the stored IndiGo source document, if any.
        $this->deletePublicUpload($assignment->indigo_document_path);

        $assignment->delete();

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Luggage assignment deleted successfully.',
                'data' => null,
            ]);
        }

        return redirect()->route('assign-luggage.index')->with('success', 'Luggage assignment deleted successfully.');
    }

    /**
     * Resolve the IndiGo company id from the loaded companies collection.
     * Matched by IATA code "6E" or a company name containing "indigo".
     * Used only to reveal the IndiGo document upload panel on the form.
     */
    private function indigoCompanyId($companies): ?int
    {
        $match = $companies->first(function ($company) {
            return strcasecmp((string) $company->company_code, '6E') === 0
                || stripos((string) $company->company_name, 'indigo') !== false;
        });

        return $match?->id;
    }

    private function authorizeAssignmentAccess(AssignLuggage $assignment)
    {
        $userId = session('user_id');
        $user = User::find($userId);

        if ($user && $user->role_id !== 0 && $user->role) {
            if (stripos($user->role->role_name, 'driver') !== false && (int) $assignment->driver_id !== (int) $userId) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access to this luggage assignment.', 'data' => null], 403);
            }

            if (stripos($user->role->role_name, 'manager') !== false && (int) $assignment->created_by !== (int) $userId) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access to this luggage assignment.', 'data' => null], 403);
            }
        }

        return null;
    }

    private function prepareDriverAssignmentFields(Request $request): void
    {
        $userId = session('user_id');
        $user = User::find($userId);

        if ($user && $user->role_id > 0 && $user->role && stripos($user->role->role_name, 'driver') !== false) {
            $request->merge(['driver_id' => $userId]);
        }
    }

    private function validationRules(): array
    {
        $rules = [
            'id' => 'nullable|exists:assign_luggages,id',
            'company_id' => 'required|exists:companies,id',
            'station_id' => 'required|exists:stations,id',
            'driver_id' => 'required|exists:users,id',
            'pickup_location' => 'required|string|max:255',
            'pickup_latitude' => 'nullable|numeric',
            'pickup_longitude' => 'nullable|numeric',
            'drop_location' => 'required|string|max:255',
            'drop_latitude' => 'nullable|numeric',
            'drop_longitude' => 'nullable|numeric',
            'distance_km' => 'nullable|numeric',
            'expected_delivery_date' => 'required|date_format:Y-m-d\TH:i',
            'status' => 'nullable|in:Pickup,In Progress,Delivered',
            'notes' => 'nullable|string',
            'retained_images' => 'nullable',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            // IndiGo document auto-fill fields (optional; never break the existing flow)
            'reference_number' => 'nullable|string|max:255',
            'number_of_bags' => 'nullable|integer|min:0|max:65535',
            'pickup_date' => 'nullable|date',
            'delivery_date' => 'nullable|date',
            'pnr_number' => 'nullable|string|max:255',
            'customer_name' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:50',
            'customer_address' => 'nullable|string|max:1000',
            'pincode' => 'nullable|string|max:20',
            'indigo_document_path' => 'nullable|string|max:2048',
        ];

        $userId = session('user_id');
        $user = User::find($userId);
        if ($user && $user->role_id > 0 && $user->role && stripos($user->role->role_name, 'driver') !== false) {
            $rules['manager_id'] = 'required|exists:users,id';
        }

        return $rules;
    }

    private function uploadedAssignmentImages(Request $request): array
    {
        $images = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $images[] = $this->storePublicUpload($file, 'luggages');
            }
        }

        return $images;
    }

    private function mergeAssignmentImages(Request $request, AssignLuggage $assignment): array
    {
        $existingImages = $assignment->images ?? [];
        $retainedInput = $request->input('retained_images', $existingImages);
        $retainedImages = is_string($retainedInput) ? (json_decode($retainedInput, true) ?? []) : (array) $retainedInput;

        foreach (array_diff($existingImages, $retainedImages) as $img) {
            $this->deletePublicUpload($img);
        }

        return array_merge($retainedImages, $this->uploadedAssignmentImages($request));
    }

    private function createdByForAssignment(Request $request, ?AssignLuggage $assignment = null): ?int
    {
        $userId = session('user_id');
        $user = User::find($userId);

        if ($user && $user->role_id > 0 && $user->role && stripos($user->role->role_name, 'driver') !== false) {
            return (int) $request->input('manager_id');
        }

        return $assignment->created_by ?? $userId;
    }

    private function sendAssignmentNotifications(AssignLuggage $assignment, string $context): void
    {
        Log::info("WhatsApp Notification: Simulated successfully for assignment #{$assignment->id}");

        try {
            $assignment->load(['driver', 'creator', 'company', 'station']);
            app(SMTPConfigurationService::class)->sendOrderAssignmentEmail($assignment);
        } catch (\Exception $e) {
            Log::error("SMTP Notification Error ({$context}): " . $e->getMessage());
        }
    }

    private function formatAssignment(AssignLuggage $assignment): array
    {
        $assignment->loadMissing(['company', 'station', 'driver', 'creator']);

        return [
            'id' => $assignment->id,
            'company_id' => $assignment->company_id,
            'company_name' => $assignment->company->company_name ?? 'N/A',
            'company_code' => $assignment->company->company_code ?? null,
            'company_logo_url' => ($assignment->company && $assignment->company->logo) ? asset($assignment->company->logo) : null,
            'station_id' => $assignment->station_id,
            'station_name' => $assignment->station->station_name ?? 'N/A',
            'station_code' => $assignment->station->station_code ?? null,
            'driver_id' => $assignment->driver_id,
            'driver_name' => $assignment->driver->name ?? 'N/A',
            'driver_initials' => $assignment->driver ? $assignment->driver->getInitials() : 'NA',
            'driver_photo_url' => ($assignment->driver && $assignment->driver->profile_photo) ? asset($assignment->driver->profile_photo) : null,
            'creator_name' => $assignment->creator->name ?? 'System',
            'pickup_location' => $assignment->pickup_location,
            'pickup_latitude' => $assignment->pickup_latitude,
            'pickup_longitude' => $assignment->pickup_longitude,
            'drop_location' => $assignment->drop_location,
            'drop_latitude' => $assignment->drop_latitude,
            'drop_longitude' => $assignment->drop_longitude,
            'distance_km' => $assignment->distance_km,
            'expected_delivery_date' => $assignment->expected_delivery_date ? $assignment->expected_delivery_date->format('Y-m-d\TH:i') : null,
            'expected_delivery_display' => $assignment->expected_delivery_date ? $assignment->expected_delivery_date->format('M d, Y h:i A') : 'N/A',
            'status' => $assignment->status,
            'notes' => $assignment->notes,
            'reference_number' => $assignment->reference_number,
            'number_of_bags' => $assignment->number_of_bags,
            'pickup_date' => $assignment->pickup_date ? $assignment->pickup_date->format('Y-m-d') : null,
            'pickup_date_display' => $assignment->pickup_date ? $assignment->pickup_date->format('M d, Y') : null,
            'delivery_date' => $assignment->delivery_date ? $assignment->delivery_date->format('Y-m-d') : null,
            'delivery_date_display' => $assignment->delivery_date ? $assignment->delivery_date->format('M d, Y') : null,
            'pnr_number' => $assignment->pnr_number,
            'customer_name' => $assignment->customer_name,
            'contact_number' => $assignment->contact_number,
            'customer_address' => $assignment->customer_address,
            'pincode' => $assignment->pincode,
            'indigo_document_path' => $assignment->indigo_document_path,
            'indigo_document_url' => $assignment->indigo_document_path ? asset($assignment->indigo_document_path) : null,
            'images' => collect($assignment->images ?? [])->map(fn ($path) => ['path' => $path, 'url' => asset($path)])->values(),
            'show_url' => route('assign-luggage.show', $assignment->id),
            'edit_url' => route('assign-luggage.edit', $assignment->id),
            'delete_url' => route('assign-luggage.destroy', $assignment->id),
            'data_url' => route('assign-luggage.data', $assignment->id),
            'created_at' => $assignment->created_at ? $assignment->created_at->format('M d, Y') : null,
            'created_at_full' => $assignment->created_at ? $assignment->created_at->format('M d, Y H:i A') : null,
            'updated_at' => $assignment->updated_at ? $assignment->updated_at->format('M d, Y h:i A') : null,
        ];
    }

    private function assignmentErrorResponse(Request $request, string $message, int $status)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'data' => null,
            ], $status);
        }

        return redirect()->route('assign-luggage.index')->with('error', $message);
    }
}
