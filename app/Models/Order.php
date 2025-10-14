<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'name',
        'phone',
        'address',
        'district',
        'thana',
        'subtotal',
        'delivery_charge',
        'discount',
        'total',
        'coupon_code',
        'status',
        'comment',
        'ip_address',
        'delivery_option_id',
        'courier_response',
        'tracking_code',
        'consignment_id',
        'courier_service_id',
        'admin_discount',
        'custom_link',
        'admin_comment'
    ];


    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'admin_discount' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_code', 'code');
    }
    public function returnStock()
{
    foreach ($this->items as $item) {
        $product = $item->product;

        if ($product->has_variants && $item->variant_option_id) {
            $variantOption = ProductVariantOption::find($item->variant_option_id);
            if ($variantOption) {
                $variantOption->increment('stock', $item->quantity);
                $product->updateStock();
            }
        } else {
            $product->increment('total_stock', $item->quantity);
        }
    }
}

public function deliveryOption()
{
    return $this->belongsTo(DeliveryOption::class, 'delivery_option_id');
}

public function courier()
{
    return $this->belongsTo(CourierService::class, 'courier_service_id');
}

public function getCourierStatusAttribute()
{
    if (!$this->courier_response) return null;

    $response = json_decode($this->courier_response, true);

    return $response['status'] ?? null;
}


protected $appends = ['pdf_url', 'delivery_status'];

public function getPdfUrlAttribute()
{
    return $this->pdf_path ? Storage::url($this->pdf_path) : null;
}

public function getDeliveryStatusAttribute()
{
    return getDeliveryStatus($this);
}
}
