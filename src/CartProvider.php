<?php

namespace Mohammadv184\Cart;

use Illuminate\Support\ServiceProvider;

class CartProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->bind("cart",function (){
            return new CartService();
        });

        $this->mergeConfigFrom(__DIR__."/Config/cart.php","cart");
    }

    public function boot(){
        $this->publishes([
            __DIR__."/Config/cart.php"=>config_path("cart.php")
        ],"config");

    }

}
