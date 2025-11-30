<?php

namespace App\Http\Controllers\Admin;

use App\Models\CourierService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CourierServiceController extends Controller
{
    public function index()
    {
        $couriers = CourierService::orderBy('id', 'desc')->paginate(10);
        return view('admin.pages.couriers.index', compact('couriers'));
    }

    public function create()
    {
        return view('admin.pages.couriers.create');
    }


    public function store(Request $request)
    {
        // Common Validation
        $rules = [
            'name' => 'required|string',
            'base_url' => 'required|url',
            'create_order_endpoint' => 'required|string',
            'headers' => 'nullable|json',
            'is_active' => 'required|boolean',
        ];

        // Steadfast needs api_key + secret_key
        if ($request->name === 'Steadfast') {
            $rules['api_key'] = 'required|string';
            $rules['secret_key'] = 'required|string';
        }

        // Pathao extra validation
        if ($request->name === 'Pathao') {
            $rules['client_id'] = 'required|string';
            $rules['client_secret'] = 'required|string';
            $rules['username'] = 'required|string';
            $rules['password'] = 'required|string';
            $rules['auth_endpoint'] = 'required|string';
        }

        $validated = $request->validate($rules);

        // Remove unwanted fields when courier is steadfast
        if ($request->name === 'Steadfast') {
            $validated['client_id'] = null;
            $validated['client_secret'] = null;
            $validated['username'] = null;
            $validated['password'] = null;
            $validated['auth_endpoint'] = null;
        }

        // Remove API key fields for Pathao (optional)
        if ($request->name === 'Pathao') {
            $validated['api_key'] = $validated['api_key'] ?? null;
            $validated['secret_key'] = $validated['secret_key'] ?? null;
        }

        // Save courier
        CourierService::create($validated);

        return redirect()->route('admin.couriers.index')->with('success', 'Courier created.');
    }


    public function edit(CourierService $courier)
    {
        return view('admin.pages.couriers.edit', compact('courier'));
    }


    public function update(Request $request, CourierService $courier)
    {
        // Common Validation
        $rules = [
            'name' => 'required|string',
            'base_url' => 'required|url',
            'create_order_endpoint' => 'required|string',
            'headers' => 'nullable|json',
            'is_active' => 'required|boolean',
        ];

        // Steadfast validation
        if ($request->name === 'Steadfast') {
            $rules['api_key'] = 'required|string';
            $rules['secret_key'] = 'required|string';
        }

        // Pathao validation
        if ($request->name === 'Pathao') {
            $rules['client_id'] = 'required|string';
            $rules['client_secret'] = 'required|string';
            $rules['username'] = 'required|string';
            $rules['password'] = 'required|string';
            $rules['auth_endpoint'] = 'required|string';
        }

        $validated = $request->validate($rules);

        // Cleanup unused fields
        if ($request->name === 'Steadfast') {
            $validated['client_id'] = null;
            $validated['client_secret'] = null;
            $validated['username'] = null;
            $validated['password'] = null;
            $validated['auth_endpoint'] = null;
        }

        if ($request->name === 'Pathao') {
            $validated['api_key'] = $validated['api_key'] ?? null;
            $validated['secret_key'] = $validated['secret_key'] ?? null;
        }

        $courier->update($validated);

        return redirect()->route('admin.couriers.index')->with('success', 'Courier updated.');
    }

    public function destroy(CourierService $courier)
    {
        $courier->delete();
        return back()->with('success', 'Courier deleted.');
    }

    public function show(CourierService $courier)
    {
        return view('admin.pages.couriers.show', compact('courier'));
    }

}
