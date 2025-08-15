<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'total_amount',
        'shipping_cost',
        'status',
        'payment_status',
        'payment_method',
        'payment_reference',
        'payment_proof',
        'admin_notes',
        'payment_verified_at',
        'verified_by',
        'shipping_address',
        'customer_name',
        'customer_phone',
        'customer_email',
        'notes',
        'shipped_at',
        'delivered_at'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'payment_verified_at' => 'datetime'
    ];

    // Relationship with user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with order items
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Relationship with verifier (admin who verified payment)
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Generate order number
    public static function generateOrderNumber()
    {
        return 'ORD-' . date('Ymd') . '-' . str_pad(static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
    }

    // Calculate total from order items
    public function calculateTotal()
    {
        return $this->orderItems->sum('total') + $this->shipping_cost;
    }
}
