<?php


namespace Mohammadv184\Cart;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class CartService
{
    /**
     * the cart items
     * @var Collection
     */
    protected $cart;
    /**
     * the cart session key
     *
     * @var
     */
    protected $instanceName="cart";

    /**
     * the cart session
     * @var
     */
    protected $session;


    public function __construct($instanceName,$session)
    {
        $this->instanceName=$instanceName;
        $this->session=$session;
        $this->cart=$this->session->get($this->instanceName)??collect([]);

    }

    /**
     * put data in cart session
     *
     * @param array $item
     * @param null $model
     * @return $this
     */
    public function put(array $item, $model): CartService
    {
        if ($this->has($model)) {
            return $this->update($this->get($model, false)["quantity"] + 1, $model);
        }
        $item = [
            "id" => $item["id"]??Str::random(10),
            "price" => $item["price"] ?? 0,
            "quantity" => $item["quantity"] ?? 0,
            "cartable_id" => $model->id,
            "cartable_type" => get_class($model)
        ];
        $this->cart->put($item["id"], $item);
        $this->save();
        return $this;
    }

    /**
     * get instance name of the cart
     *
     * @return string
     */
    public function getInstanceName(): string
    {
        return $this->instanceName;
    }

    /**
     * set instance name
     * @param $instanceName
     * @return $this
     */
    public function instance($instanceName): CartService
    {
        $this->instanceName=$instanceName;
        return $this;
    }


    /**
     * Update cart values
     * @param $value
     * @param $key
     * @return $this
     */
    public function update($value, $key): CartService
    {
        $item=collect($this->get($key,false));
        if($item->isEmpty()){
            return $this;
        }
        if(is_numeric($value)){
            $item["quantity"]=$value;
        }
        else{
            $this->cart->forget($item["id"]);
            $item=$item->merge($value);
        }
        $this->cart->put($item["id"], $item->toArray());
        $this->save();
        return $this;

    }

    /**
     * Check if exists in Cart
     * @param $key
     * @return bool
     */
    public function has($key): bool
    {
        return is_object($key)
            ?$this->cart->contains("cartable_id",$key->id) && $this->cart->contains("cartable_type",get_class($key))
            :$this->cart->contains("id",$key);

    }

    /**
     * Delete item
     * @param $key
     * @return $this
     */
    public function delete($key): CartService
    {
        $item=collect($this->get($key,false));
        if ($item->isEmpty()){
            return $this;
        }
        $this->cart->forget($item["id"]);
        $this->save();
        return $this;
    }

    /**
     * flush all item in cart
     * @return $this
     */
    public function flush(): CartService
    {
        $this->cart=collect([]);
        $this->save();
        return $this;
    }

    /**
     * get item in cart
     * @param $id
     * @param bool $withRelationShip
     * @return array
     */
    public function get($id,bool $withRelationShip=true):array
    {
        if (!is_object($id)){
            return $withRelationShip
                ?$this->withRelationShip($this->cart->where("id",$id)->first())
                :$this->cart->where("id",$id)->first();
        }
        return $withRelationShip
            ?$this->withRelationShip($this->cart->where("cartable_id",$id->id)->where("cartable_type",get_class($id))->first())
            :$this->cart->where("cartable_id",$id->id)->where("cartable_type",get_class($id))->first();
    }

    /**
     * return all items in cart
     * @param bool $withRelationShip
     * @return Collection
     */
    public function all(bool $withRelationShip=true):Collection
    {
        return $withRelationShip
            ? $this->cart->map(function ($item){
                return $this->withRelationShip($item);
            })
            :$this->cart;
    }

    /**
     * return item with Model RelationShip
     * @param $value
     * @return array
     */
    protected function withRelationShip($value):array
    {
        if(isset($value["cartable_id"]) && isset($value["cartable_type"])){
            $model=(new $value["cartable_type"])->find($value["cartable_id"]);

            $value[strtolower(class_basename($model))]=$model;
        }
        return $value;


    }

    /**
     * Totla cart price
     * @return int
     */
    public function totalPrice():int
    {
        return $this->all()->sum(function ($item){
            return $item["price"]*$item["quantity"];
        });
    }

    /**
     * save value in cart and session
     */
    protected function save(): void
    {
        $this->session->put([$this->instanceName => $this->cart]);
    }

}
