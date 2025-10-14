<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DeliveryOption;
use Illuminate\Http\Request;

class DeliveryOptionController extends Controller
{
public function index()
{
    $deliveryOptions = DeliveryOption::where('is_active', true)
        ->whereHas('freeDeliveryProducts')
        ->with('freeDeliveryProducts') 
        ->get();

    return response()->json([
        'success' => true,
        'data' => $deliveryOptions
    ]);
}
}
