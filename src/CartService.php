<?php


namespace Mohammadv184\Cart;

class CartService
{
    protected $cart;
    protected $name="cart";
    public function __construct()
    {
        $this->name=config("cart.name");
        $this->cart=\Session::get($this->name)??collect([]);

    }

    public function put(array $value,$model=null): CartService
    {
        if (! is_null($model)){
            $value=array_merge($value,[
                "id"=>\Str::random(10),
                "cartable_id"=>$model->id,
                "cartable_type"=>get_class($model)
            ]);
        }else{
            $value=array_merge($value,[
                "id"=>\Str::random(10)
            ]);

        }
        $this->cart->put($value["id"],$value);
        \Session::put([$this->name=>$this->cart]);
        return $this;



    }
    public function update($value,$key){
        if(is_numeric($value)){
            $cart=collect($this->get($key))->except("product");
            if($cart->isEmpty()){
                return $this;
            }
            $cart["range"]=$value;
            $this->cart=$this->cart->merge([$cart["id"]=>$cart]);

            \Session::put([$this->name=>$this->cart]);

        }
        else{
            $cart=collect($this->get($key))->except("product")->merge($value);
            if($cart->isEmpty()){
                return $this;
            }
            $this->cart=$this->cart->merge([$cart["id"]=>$cart]);

            \session::put([$this->name=>$this->cart]);
        }

        return $this;

    }
    public function has($key){

        return $this->cart->contains("cartable_id",$key->id) && $this->cart->contains("cartable_type",get_class($key));
    }
    public function delete($key){
        $model=$this->get($key);
        $this->cart=$this->cart->except($model["id"]);
        \Session::put([$this->name=>$this->cart]);
    }
    public function flush(){
        $this->cart=collect([]);
        \Session::put([$this->name=>$this->cart]);
    }
    public function get($id){
        return $this->withRelationShip($this->cart->where("cartable_id",$id->id)->where("cartable_type",get_class($id))->first());
    }
    public function all(){
        return $this->cart->map(function ($item){
            return $this->withRelationShip($item);
        });
    }
    protected function withRelationShip($value){
        if(isset($value["cartable_id"]) && isset($value["cartable_type"])){
            $model=(new $value["cartable_type"])->find($value["cartable_id"]);

            $value[strtolower(class_basename($model))]=$model;
            return $value;

        }
        return $value;


    }

    public function totalPrice()
    {
        return $this->all()->sum(function ($item){

            return ($item["product"]->price*$item["range"]);
        });
    }

}
