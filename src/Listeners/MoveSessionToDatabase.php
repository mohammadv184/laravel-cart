<?php


namespace Mohammadv184\Cart\Listeners;

use Mohammadv184\Cart\Events\Logined;
use Mohammadv184\Cart\Facades\Cart;

class MoveSessionToDatabase
{
    public function handle(Logined $event)
    {
        Cart::moveSessionToDatabase();
    }
}
