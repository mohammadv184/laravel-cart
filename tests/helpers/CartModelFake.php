<?php

namespace Mohammadv184\Cart\Tests\helpers;

class CartModelFake
{
    protected $cart;

    public function __construct($value = null)
    {
        $this->cart = $value ?? collect([]);
    }

    public function all()
    {
        return $this->cart;
    }

    public function has($id)
    {
        return is_null($this->cart->firstWhere('rowId', $id)) ? false : $this;
    }

    public function create($value)
    {
        $this->cart = $this->cart->merge([$value]);

        return $this;
    }

    public function update($value)
    {
    }

    public function where($find, $for)
    {
        return new static($this->cart->where($find, $for));
    }

    public function firstWhere($find, $for)
    {
        return new static($this->cart->where($find, $for));
    }

    public function delete()
    {
        return $this;
    }
}
