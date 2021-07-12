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

    /**
     * the cart connection.
     *
     * @var string
     */
    protected $connection;

    /**
     * the user model.
     *
     * @var mixed|null
     */
    protected $user;

    /**
     * the session status.
     *
     * @var mixed|null
     */
    protected $sessionStatus;

    /**
     * @throws \Exception
     */
    public function __construct($instanceName, $storage, $connection, $user = null, $sessionStatus = true)
    {
        $this->instanceName = $instanceName;
        $this->storage = $storage;
        $this->connection = $connection;
        $this->user = $user;
        $this->sessionStatus = $sessionStatus;
        $cart = $this->connection == 'database'
            ? $this->storage->all()->where('user_id', $this->user->id)->mapWithKeys(function ($item) {
                return [$item['rowId'] => [
                    'id'            => $item['rowId'],
                    'price'         => $item['price'],
                    'quantity'      => $item['quantity'],
                    'cartable_id'   => $item['cartable_id'],
                    'cartable_type' => $item['cartable_type'],
                ]];
            })->toArray()
            : ($this->storage->get($this->instanceName) ?? collect([]))->toArray();
        $this->cart = collect($cart);
        if ($this->connection == 'database' && is_null($this->user)) {
            throw new \Exception('user is required');
        }
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
     * check if exists session.
     *
     * @return bool
     */
    public function hasSession()
    {
        $session = \Session::get($this->instanceName) ?? collect([]);

        return $session->isNotEmpty();
    }

    /**
     * Move session to database.
     *
     * @return $this
     */
    public function moveSessionToDatabase()
    {
        if ($this->hasSession() && $this->connection == 'database') {
            $session = \Session::get($this->instanceName);
            \Session::forget($this->instanceName);
            $session->each(function ($item) {
                $cart = $this->cart->where('cartable_id', $item['cartable_id'])->where('cartable_type', $item['cartable_type'])->first();
                if (!is_null($cart)) {
                    $item['id'] = $cart['id'];
                    $item['quantity'] += $cart['quantity'];
                    $item['price'] = $cart['price'];
                }
                $this->cart->put($item['id'], $item);
            });
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
        if ($this->connection == 'database') {
            $this->storage->where('user_id', $this->user->id)->firstWhere('rowId', $key)->delete();
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
        if ($this->connection == 'database') {
            $this->storage->where('id', $this->user->id)->delete();
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
        $model = (new $value['cartable_type']())->find($value['cartable_id']);
        $value[strtolower(class_basename($model))] = $model;

        return $value;
    }

    /**
     * Total cart price.
     *
     * @param bool $withNumberFormat
     *
     * @return int | string
     */
    public function totalPrice(bool $withNumberFormat = false)
    {
        $total = $this->all()->sum(
            function ($item) {
                return $item['price'] * $item['quantity'];
            }
        );

        return $withNumberFormat
            ? number_format($total)
            : $total;
    }

    /**
     * save value in cart and session.
     */
    protected function save(): void
    {
        if ($this->connection == 'database') {
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
                                'user_id'      => $this->user->id,
                                'price'        => $item['price'],
                                'quantity'     => $item['quantity'],
                                'cartable_id'  => $item['cartable_id'],
                                'cartable_type'=> $item['cartable_type'],
                            ]
                        );
                    }
                }
            );
        } elseif ($this->sessionStatus) {
            $this->storage->put([$this->instanceName => $this->cart]);
        }
    }
}
