<?php

namespace App\Http\Controllers\Admin;

use App\Models\CourierService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CourierServiceController extends Controller
{
    public function index()
    {
        $couriers = CourierService::paginate(10);
        return view('admin.pages.couriers.index', compact('couriers'));
    }

    public function create()
    {
        return view('admin.pages.couriers.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'merchant_id' => 'required|integer',
            'name' => 'required|string',
            'type' => 'required|in:pathao,steadfast',
            'base_url' => 'required|url',
            'create_order_endpoint' => 'nullable|string',
            'headers' => 'nullable|json',
            'is_active' => 'required|boolean',
        ];


        if ($request->type === 'pathao') {
            $rules = array_merge($rules, [
                'store_id' => 'required|string',
                'client_id' => 'required|string',
                'client_secret' => 'required|string',
                'username' => 'required|string',
                'password' => 'required|string',
                'auth_endpoint' => 'required|string',
            ]);
        } elseif ($request->type === 'steadfast') {
            $rules = array_merge($rules, [
                'api_key' => 'required|string',
                'secret_key' => 'required|string',
            ]);
        }

        $data = $request->validate($rules);


        if (!empty($data['headers'])) {
            $data['headers'] = json_decode($data['headers'], true);
        }

        CourierService::create($data);

        return redirect()->route('admin.couriers.index')->with('success', 'Courier created successfully.');
    }

    public function edit(CourierService $courier)
    {
        return view('admin.pages.couriers.edit', compact('courier'));
    }

    public function update(Request $request, CourierService $courier)
    {
        $rules = [
            'merchant_id' => 'required|integer',
            'name' => 'required|string',
            'type' => 'required|in:pathao,steadfast',
            'base_url' => 'required|url',
            'create_order_endpoint' => 'nullable|string',
            'headers' => 'nullable|json',
            'is_active' => 'required|boolean',
        ];


        if ($request->type === 'pathao') {
            $rules = array_merge($rules, [
                'store_id' => 'required|string',
                'client_id' => 'required|string',
                'client_secret' => 'required|string',
                'username' => 'required|string',
                'password' => 'required|string',
                'auth_endpoint' => 'required|string',
            ]);
        } elseif ($request->type === 'steadfast') {
            $rules = array_merge($rules, [
                'api_key' => 'required|string',
                'secret_key' => 'required|string',
            ]);
        }

        $data = $request->validate($rules);


        if (!empty($data['headers'])) {
            $data['headers'] = json_decode($data['headers'], true);
        }

        $courier->update($data);

        return redirect()->route('admin.couriers.index')->with('success', 'Courier updated successfully.');
    }

    public function destroy(CourierService $courier)
    {
        $courier->delete();
        return back()->with('success', 'Courier deleted successfully.');
    }
}
