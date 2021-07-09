<?php

namespace Mohammadv184\Cart;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Mohammadv184\Cart\Events\Logined;
use Mohammadv184\Cart\Listeners\MoveSessionToDatabase;
use Mohammadv184\Cart\Middlewares\CartIfLogin;
use Mohammadv184\Cart\Models\Cart;

class CartServiceProvider extends ServiceProvider
{
    public function register()
    {

        $this->mergeConfigFrom(__DIR__.'/Config/cart.php', 'cart');
        $this->app->singleton('cart', function ($app) {
                $storage=\Auth::check()?new Cart():$app['session'];
                return new CartService(config('cart.instanceName', 'cart'), $storage);
        });
    }

    public function boot()
    {

        $this->publishes(
            [
            __DIR__.'/Config/cart.php'=> config_path('cart.php'),
            ],
            'config'
        );
        $date=date("Y_m_d_His");
        $this->publishes(
            [
            __DIR__."/../Database/Migrations/0000_00_00_000000_create_Cart_Items_table.php"=>database_path("migrations/".$date."_create_cart_items.php")
            ],
            "migration"
        );
        Event::listen(Logined::class, [MoveSessionToDatabase::class,"handle"]);

        $kernel=$this->app->make(Kernel::class);
        $kernel->pushMiddleware(CartIfLogin::class);
    }
}
