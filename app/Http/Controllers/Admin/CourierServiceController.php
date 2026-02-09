<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\CourierService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

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

    public function courierCheckPage()
    {
        return view('admin.pages.couriers.fraud_checker', [
        'response' => null
    ]);
    }
    public function customerReport(Request $request)
    {
        $request->validate([
            'phone' => 'required'
        ]);

        $phone = $request->phone;

        if (str_starts_with($phone, '01')) {
            $phone = '+88' . $phone;
        }

        $response = Http::withHeaders([
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer ' . config('bd_courier.api_key'),
        ])
        ->timeout(config('bd_courier.timeout'))
        ->post(
            config('bd_courier.base_url') . '/courier-check',
            ['phone' => $phone]
        );

        if ($response->failed()) {
            return back()->with('error', 'Courier Insight API failed');
        }

        return view('admin.pages.couriers.fraud_checker', [
            'response' => $response->json(),
            'phone'    => $phone
        ]);
    }
}
