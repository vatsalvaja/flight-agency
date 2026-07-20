<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssignLuggageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $userId = session('user_id');
        $user = \App\Models\User::find($userId);
        if ($user && $user->role_id > 0 && $user->role && stripos($user->role->role_name, 'driver') !== false) {
            $this->merge([
                'driver_id' => $userId,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
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
            'status' => 'required|in:Pickup,In Progress,Delivered',
            'notes' => 'nullable|string',
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
        $user = \App\Models\User::find($userId);
        if ($user && $user->role_id > 0 && $user->role && stripos($user->role->role_name, 'driver') !== false) {
            $rules['manager_id'] = 'required|exists:users,id';
        }

        return $rules;
    }
}
