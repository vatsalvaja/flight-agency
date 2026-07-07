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
            'expected_delivery_date' => 'required|date',
            'status' => 'required|in:Pickup,In Progress,Delivered',
            'notes' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];

        $userId = session('user_id');
        $user = \App\Models\User::find($userId);
        if ($user && $user->role_id > 0 && $user->role && stripos($user->role->role_name, 'driver') !== false) {
            $rules['manager_id'] = 'required|exists:users,id';
        }

        return $rules;
    }
}
