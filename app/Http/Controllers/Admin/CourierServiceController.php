<?php

namespace App\Http\Controllers\Admin;

use App\Models\CourierService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CourierServiceController extends Controller
{
    public function index()
    {
        $couriers = CourierService::paginate('10');
        return view('admin.pages.couriers.index', compact('couriers'));
    }

    public function create()
    {
        return view('admin.pages.couriers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'base_url' => 'required|url',
            'create_order_endpoint' => 'required|string',
            'api_key' => 'required|string',
            'secret_key' => 'required|string',
            'headers' => 'nullable|json',
            'is_active' => 'required|boolean'
        ]);

        CourierService::create($request->all());

        return redirect()->route('admin.couriers.index')->with('success', 'Courier created.');
    }

    public function edit(CourierService $courier)
    {
        return view('admin.pages.couriers.edit', compact('courier'));
    }

    public function update(Request $request, CourierService $courier)
    {
        $request->validate([
            'name' => 'required|string',
            'base_url' => 'required|url',
            'create_order_endpoint' => 'required|string',
            'api_key' => 'required|string',
            'secret_key' => 'required|string',
            'headers' => 'nullable|json',
            'is_active' => 'required|boolean'
        ]);

        $courier->update($request->all());

        return redirect()->route('admin.couriers.index')->with('success', 'Courier updated.');
    }

    public function destroy(CourierService $courier)
    {
        $courier->delete();
        return back()->with('success', 'Courier deleted.');
    }
}