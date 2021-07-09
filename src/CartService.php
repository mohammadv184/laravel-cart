<?php

namespace Mohammadv184\Cart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Mohammadv184\Cart\Models\Cart;

class CartService
{
    /**
     * the cart items.
     *
     * @var Collection
     */
    protected $cart;
    /**
     * the cart session key.
     *
     * @var
     */
    protected $instanceName = 'cart';

    /**
     * the cart storage.
     *
     * @var Cart|\Session
     */
    protected $storage;

    public function __construct($instanceName, $storage)
    {
        $this->instanceName = $instanceName;
        $this->storage = $storage;
        $this->cart = $this->storage instanceof Model
            ? $this->storage->all()->mapWithKeys(function ($item) {
                return [$item['rowId']=> [
                    'id'            => $item['rowId'],
                    'price'         => $item['price'],
                    'quantity'      => $item['quantity'],
                    'cartable_id'   => $item['cartable_id'],
                    'cartable_type' => $item['cartable_type'],
                ]];
            })
            : $this->storage->get($this->instanceName) ?? collect([]);
    }

    /**
     * put data in cart session.
     *
     * @param array $item
     * @param null  $model
     *
     * @return $this
     */
    public function put(array $item, $model): CartService
    {
        if ($this->has($model)) {
            return $this->update($this->get($model, false)['quantity'] + 1, $model);
        }
        $item = [
            'id'            => $item['id'] ?? Str::random(10),
            'price'         => $item['price'] ?? 0,
            'quantity'      => $item['quantity'] ?? 0,
            'cartable_id'   => $model->id,
            'cartable_type' => get_class($model),
        ];
        $this->cart->put($item['id'], $item);
        $this->save();

        return $this;
    }

    /**
     * get instance name of the cart.
     *
     * @return string
     */
    public function getInstanceName(): string
    {
        return $this->instanceName;
    }

    /**
     * set instance name.
     *
     * @param $instanceName
     *
     * @return $this
     */
    public function instance($instanceName): CartService
    {
        $this->instanceName = $instanceName;

        return $this;
    }

    /**
     * Update cart values.
     *
     * @param $value
     * @param $key
     *
     * @return $this
     */
    public function update($value, $key): CartService
    {
        $item = collect($this->get($key, false));
        if ($item->isEmpty()) {
            return $this;
        }
        if (is_numeric($value)) {
            $item['quantity'] = $value;
        } else {
            $this->cart->forget($item['id']);
            $item = $item->merge($value);
        }
        $this->cart->put($item['id'], $item->toArray());
        $this->save();

        return $this;
    }

    /**
     * Check if exists in Cart.
     *
     * @param $key
     *
     * @return bool
     */
    public function has($key): bool
    {
        return is_object($key)
            ? $this->cart->contains('cartable_id', $key->id) && $this->cart->contains('cartable_type', get_class($key))
            : $this->cart->contains('id', $key);
    }

    /**
     * @return bool
     */
    public function hasSession()
    {
        $session = \Session::get($this->instanceName) ?? collect([]);

        return $session->isNotEmpty();
    }

    public function moveSessionToDatabase()
    {
        if ($this->hasSession() && $this->storage instanceof Model) {
            $session = \Session::get($this->instanceName);
            \Session::forget($this->instanceName);
            $this->cart = $this->cart->merge($session->toArray());
            $this->save();
        }

        return $this;
    }

    /**
     * Delete item.
     *
     * @param $key
     *
     * @return $this
     */
    public function delete($key): CartService
    {
        $item = collect($this->get($key, false));
        if ($item->isEmpty()) {
            return $this;
        }
        $this->cart->forget($item['id']);
        if ($this->storage instanceof Model) {
            $this->storage->firstWhere('rowId', $key)->delete();
        } else {
            $this->save();
        }

        return $this;
    }

    /**
     * flush all item in cart.
     *
     * @return $this
     */
    public function flush(): CartService
    {
        $this->cart = collect([]);
        if ($this->storage instanceof Model) {
            $this->storage->truncate();
        } else {
            $this->save();
        }

        return $this;
    }

    /**
     * get item in cart.
     *
     * @param $id
     * @param bool $withRelationShip
     *
     * @return array
     */
    public function get($id, bool $withRelationShip = true): array
    {
        if (!is_object($id)) {
            return $withRelationShip
                ? $this->withRelationShip($this->cart->where('id', $id)->first())
                : $this->cart->where('id', $id)->first();
        }

        return $withRelationShip
            ? $this->withRelationShip($this->cart->where('cartable_id', $id->id)->where('cartable_type', get_class($id))->first())
            : $this->cart->where('cartable_id', $id->id)->where('cartable_type', get_class($id))->first();
    }

    /**
     * return all items in cart.
     *
     * @param bool $withRelationShip
     *
     * @return Collection
     */
    public function all(bool $withRelationShip = true): Collection
    {
        return $withRelationShip
            ? $this->cart->map(
                function ($item) {
                    return $this->withRelationShip($item);
                }
            )
            : $this->cart;
    }

    /**
     * return item with Model RelationShip.
     *
     * @param $value
     *
     * @return array
     */
    protected function withRelationShip($value): array
    {
        if (isset($value['cartable_id']) && isset($value['cartable_type'])) {
            $model = (new $value['cartable_type']())->find($value['cartable_id']);

            $value[strtolower(class_basename($model))] = $model;
        }

        return $value;
    }

    /**
     * Totla cart price.
     *
     * @return int
     */
    public function totalPrice(): int
    {
        return $this->all()->sum(
            function ($item) {
                return $item['price'] * $item['quantity'];
            }
        );
    }

    /**
     * save value in cart and session.
     */
    protected function save(): void
    {
        if ($this->storage instanceof Model) {
            $this->cart->each(
                function ($item) {
                    if ($cart = $this->storage->has($item['id'])) {
                        $cart->update(
                            [
                                'rowId'        => $item['id'],
                                'price'        => $item['price'],
                                'quantity'     => $item['quantity'],
                                'cartable_id'  => $item['cartable_id'],
                                'cartable_type'=> $item['cartable_type'],
                            ]
                        );
                    } else {
                        $this->storage->create(
                            [
                                'rowId'        => $item['id'],
                                'price'        => $item['price'],
                                'quantity'     => $item['quantity'],
                                'cartable_id'  => $item['cartable_id'],
                                'cartable_type'=> $item['cartable_type'],
                            ]
                        );
                    }
                }
            );
        } else {
            $this->storage->put([$this->instanceName => $this->cart]);
        }
    }
}
