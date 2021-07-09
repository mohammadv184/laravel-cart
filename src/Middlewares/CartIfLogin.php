<?php


namespace Mohammadv184\Cart\Middlewares;

use Closure;

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

        return $next($request);
    }
}
