<?php

namespace Mohammadv184\Cart;

use Illuminate\Routing\Router;
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
            $storage = \Auth::check() ? new Cart() : $app['session'];
            $connection = \Auth::check() ? 'database' : 'session';
            $user = \Auth::user();

            return new CartService(config('cart.instanceName', 'cart'), $storage, $connection, $user);
        });
    }

    public function boot()
    {
        $this->publishFiles();
        Event::listen(Logined::class, [MoveSessionToDatabase::class, 'handle']);

        $router = $this->app->make(Router::class);
        $router->pushMiddlewareToGroup('web', CartIfLogin::class);
        $router->pushMiddlewareToGroup('api', CartIfLogin::class);
    }

    private function publishFiles()
    {
        $this->publishes(
            [
                __DIR__.'/Config/cart.php'=> config_path('cart.php'),
            ],
            'config'
        );
        $this->publishes(
            [
                __DIR__.'/../Database/Migrations/0000_00_00_000000_create_Cart_Items_table.php'=> database_path('migrations/'.date('Y_m_d_His').'_create_cart_items.php'),
            ],
            'migrations'
        );
    }
}
