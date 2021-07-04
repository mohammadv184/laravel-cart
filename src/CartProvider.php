<?php

namespace Mohammadv184\Cart;

use Illuminate\Support\ServiceProvider;

class CartProvider extends ServiceProvider
{

    public function register()
    {
        $this->mergeConfigFrom(__DIR__."/Config/cart.php","cart");

        $this->app->singleton("cart",function (){
            return new CartService(config("instanceName","cart"));
        });
    }

    public function boot(){
        $this->publishes([
            __DIR__."/Config/cart.php"=>config_path("cart.php")
        ],"config");

    }

}
