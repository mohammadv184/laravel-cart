<?php

namespace Mohammadv184\Cart\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'rowId',
        'user_id',
        'price',
        'quantity',
        'cartable_id',
        'cartable_type',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('cart.table', 'cart_items');
    }

    public function cartable()
    {
        return $this->morphTo();
    }

    public function has($rowId)
    {
        return static::query()->firstWhere('rowId', $rowId) ?? false;
    }
}
