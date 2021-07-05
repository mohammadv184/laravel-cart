<?php


use Mohammadv184\Cart\CartService;
use PHPUnit\Framework\TestCase;
require_once __DIR__."/helpers/SessionMock.php";
require_once __DIR__."/helpers/ModelMock.php";
class CartServiceTest extends TestCase
{

    /**
     * @var CartService
     */
    protected $cart;

    protected function setUp(): void
    {

        $this->cart=new CartService("cart",new SessionMock());
    }
    public function testPut()
    {
        $this->cart->put([
            "quantity",
            "price"
        ],new ModelMock());
        $this->assertEquals(1,$this->cart->all(false)->count(),"Cart should have 1 item on it");
    }
}
