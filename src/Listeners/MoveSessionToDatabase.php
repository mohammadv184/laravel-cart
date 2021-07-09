<?php


namespace Mohammadv184\Cart\Listeners;

use Mohammadv184\Cart\Events\Logined;

class MoveSessionToDatabase
{
    public function handle(Logined $event)
    {
        dd($event);
    }
}
