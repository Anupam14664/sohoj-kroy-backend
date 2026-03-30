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

        $apiPhone = ltrim($phone, '+');

        if (str_starts_with($apiPhone, '01')) {
            $apiPhone = '880' . substr($apiPhone, 1);
        }

        try {

            $response = Http::withHeaders([
                    'Accept'        => 'application/json',
                    'Authorization' => 'Bearer ' . config('bd_courier.api_key'),
                ])
                ->timeout(30)
                ->retry(3, 2000)
                ->post(
                    config('bd_courier.base_url') . '/courier-check',
                    ['phone' => $apiPhone]
                );

            if ($response->failed()) {
                return back()->with('error', 'Courier API failed: ' . $response->body());
            }

            $data = $response->json();

            if (!isset($data['data'])) {
                return back()->with('error', 'No data found from courier API');
            }

            return view('admin.pages.couriers.fraud_checker', [
                'response' => $data,
                'phone'    => $apiPhone
            ]);

        } catch (\Exception $e) {

            return back()->with('error', 'Request failed: ' . $e->getMessage());
        }
    }
}
