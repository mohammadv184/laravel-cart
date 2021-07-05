<?php


namespace Mohammadv184\Cart\Facades;


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Mohammadv184\Cart\CartService;

/**
 * Class Cart
 * @method static CartService put(array $value,$model)
 * @method static string getInstanceName()
 * @method static CartService update($value,$key)
 * @method static CartService has($key)
 * @method static CartService delete($key)
 * @method static CartService flush()
 * @method static array get($id,bool $withRelationShip=true)
 * @method static Collection all(bool $withRelationShip=true)
 * @method static int totalPrice()
 *
 * @see \Mohammadv184\Cart\CartService
 */
class Cart extends Facade
{
    protected static function getFacadeAccessor()
    {
        return "cart";
    }
}