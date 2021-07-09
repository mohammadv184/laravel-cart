<?php


namespace Mohammadv184\Cart\Events;

use Illuminate\Foundation\Events\Dispatchable;

use Illuminate\Queue\SerializesModels;
use Mohammadv184\Cart\CartService;

class Logined
{
    use Dispatchable, SerializesModels;
}
