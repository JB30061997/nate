<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['order_number', 'external_reference', 'customer_name', 'customer_phone', 'customer_city', 'customer_address', 'customer_notes', 'subtotal', 'delivery_fee', 'total', 'status'])]
class Order extends Model
{
    public static function shortReference(int $orderId): string
    {
        return 'NATE-'.str_pad((string) $orderId, 6, '0', STR_PAD_LEFT);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }
}
