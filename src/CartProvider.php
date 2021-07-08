<?php

namespace Mohammadv184\Cart;

use Illuminate\Support\ServiceProvider;

class CartProvider extends ServiceProvider
{

    public function register()
    {
        $this->mergeConfigFrom(__DIR__."/Config/cart.php","cart");

        $this->app->singleton("cart",function ($app){
            return new CartService(config("cart.instanceName","cart"),$app["session"]);
        });
    }

    public function boot(){
        $this->publishes([
            __DIR__."/Config/cart.php"=>config_path("cart.php")
        ],"cart-config");
    }

}
