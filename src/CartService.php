<?php


namespace Mohammadv184\Cart;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class CartService
{
    protected $cart;
    /**
     * the cart session key
     *
     * @var
     */
    protected $instanceName="cart";

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
     * @param array $value
     * @param null $model
     * @return $this
     */
    public function put(array $value, $model=null): CartService
    {
        if (! is_null($model)){
            $value=[
                "id"=>Str::random(10),
                "price"=>$value["price"]??0,
                "quantity"=>$value["quantity"]??0,
                "cartable_id"=>$model->id,
                "cartable_type"=>get_class($model)
            ];
        }else{
            $value=[
                "id"=>Str::random(10),
                "price"=>$value["price"]??0,
                "quantity"=>$value["quantity"]??0
            ];

        }
        $this->cart->put($value["id"],$value);
        $this->session->put([$this->instanceName=>$this->cart]);
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
     * Update cart values
     * @param $value
     * @param $key
     * @return $this
     */
    public function update($value, $key): CartService
    {
        if(is_numeric($value)){
            $cart=collect($this->get($key,false));
            if($cart->isEmpty()){
                return $this;
            }
            $cart["quantity"]=$value;
            $this->cart=$this->cart->merge([$cart["id"]=>$cart]);
        }
        else{
            $cart=collect($this->get($key,false));
            if($cart->isEmpty()){
                return $this;
            }
            $cart->merge($value);
            $this->cart=$this->cart->merge([$cart["id"]=>$cart]);
        }
        \session::put([$this->instanceName=>$this->cart]);
        return $this;

    }

    /**
     * Check if exists in Cart
     * @param $key
     * @return bool
     */
    public function has($key): bool
    {
        return $this->cart->contains("cartable_id",$key->id) && $this->cart->contains("cartable_type",get_class($key));
    }

    /**
     * Delete item
     * @param $key
     * @return $this
     */
    public function delete($key): CartService
    {
        $model=collect($this->get($key,false));
        if ($model->isEmpty()){
            return $this;
        }
        $this->cart=$this->cart->except($model->id);
        $this->session->put([$this->instanceName=>$this->cart]);
        return $this;
    }

    /**
     * flush all item in cart
     * @return $this
     */
    public function flush(): CartService
    {
        $this->cart=collect([]);
        $this->session->put([$this->instanceName=>$this->cart]);
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
            return $item["product"]->price*$item["range"];
        });
    }

}
