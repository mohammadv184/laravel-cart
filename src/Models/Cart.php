<?php

namespace Mohammadv184\Cart\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $table = 'cart_items';
    protected $fillable = [
        'rowId',
        'user_id',
        'price',
        'quantity',
        'cartable_id',
        'cartable_type',
    ];

    public function cartable()
    {
        return $this->morphTo();
    }

    public function has($rowId)
    {
        return static::query()->firstWhere('rowId', $rowId) ?? false;
    }
}
