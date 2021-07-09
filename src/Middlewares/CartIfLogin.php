<?php


namespace Mohammadv184\Cart\Middlewares;

use Closure;
use Mohammadv184\Cart\Events\Logined;
use Mohammadv184\Cart\Facades\Cart;

class CartIfLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (\Auth::check()&&Cart::hasSession()) {
            event(new Logined());
        }
        return $next($request);
    }
}
