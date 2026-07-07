<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.companies.index');
    }

    /**
     * Return the companies listing for AJAX rendering.
     */
    public function list()
    {
        $companies = Company::orderBy('id', 'desc')->get()->map(function (Company $company) {
            return $this->formatCompany($company);
        });

        return response()->json([
            'success' => true,
            'message' => 'Companies loaded successfully.',
            'data' => $companies,
        ]);
    }

    /**
     * Return one company for AJAX edit form population.
     */
    public function getDataById(Company $company)
    {
        return response()->json([
            'success' => true,
            'message' => 'Company loaded successfully.',
            'data' => $this->formatCompany($company),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.companies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return $this->save($request);
    }

    /**
     * Save a company through a single AJAX endpoint for create and update.
     */
    public function save(Request $request)
    {
        $company = $request->filled('id') ? Company::find($request->input('id')) : null;

        if ($request->filled('id') && ! $company) {
            return $this->companyErrorResponse($request, 'Company not found.', 404);
        }

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

        $validated = $validator->validated();

        if ($request->hasFile('logo')) {
            if ($company && $company->logo) {
                $this->deletePublicUpload($company->logo);
            }

            $validated['logo'] = $this->storePublicUpload($request->file('logo'), 'companies');
        }

        if ($company) {
            $company->update($validated);
            $message = 'Company updated successfully.';
        } else {
            $company = Company::create($validated);
            $message = 'Company created successfully.';
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $this->formatCompany($company->fresh()),
            ]);
        }

        return redirect()->route('companies.index')->with('success', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company)
    {
        return view('admin.companies.show', compact('company'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        return view('admin.companies.edit', compact('company'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        $request->merge(['id' => $company->id]);

        return $this->save($request);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        if ($company->logo) {
            $this->deletePublicUpload($company->logo);
        }
        $company->delete();

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Company deleted successfully.',
                'data' => null,
            ]);
        }

        return redirect()->route('companies.index')->with('success', 'Company deleted successfully.');
    }

    private function validationRules(): array
    {
        return [
            'company_name' => 'required|string|max:255',
            'company_code' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ];
    }

    private function formatCompany(Company $company): array
    {
        return [
            'id' => $company->id,
            'company_name' => $company->company_name,
            'company_code' => $company->company_code,
            'logo' => $company->logo,
            'logo_url' => $company->logo ? asset($company->logo) : null,
            'contact_person' => $company->contact_person,
            'email' => $company->email,
            'phone' => $company->phone,
            'address' => $company->address,
            'status' => $company->status,
            'show_url' => route('companies.show', $company->id),
            'edit_url' => route('companies.edit', $company->id),
            'delete_url' => route('companies.destroy', $company->id),
            'data_url' => route('companies.data', $company->id),
            'created_at' => $company->created_at ? $company->created_at->format('M d, Y h:i A') : null,
            'updated_at' => $company->updated_at ? $company->updated_at->format('M d, Y h:i A') : null,
        ];
    }

    private function companyErrorResponse(Request $request, string $message, int $status)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'data' => null,
            ], $status);
        }

        return redirect()->route('companies.index')->with('error', $message);
    }
}
