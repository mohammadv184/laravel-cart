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

    protected $model;


    protected function setUp(): void
    {

        $this->cart=new CartService("cart",new SessionMock());
        $this->model=new ModelMock();

    }
    public function testPut()
    {
        $this->cart->put([
            "id"=>"test",
            "quantity"=>1,
            "price"=>1000
        ],$this->model);
        $this->assertEquals(1,$this->cart->all(false)->count(),"Cart should have 1 item on it");
        $this->assertEquals([
            "id"=>"test",
            "quantity"=>1,
            "price"=>1000,
            "cartable_type"=>get_class($this->model),
            "cartable_id"=>1,
            "modelmock"=>$this->model
        ],$this->cart->get($this->model));
        $this->assertTrue($this->cart->has("test"));
        $this->assertTrue($this->cart->has($this->model));
    }
    public function testInstance(){
        $this->cart->instance("test");
        $this->assertEquals("test",$this->cart->getInstanceName());

        $this->cart->instance("shop");
        $this->assertEquals("shop",$this->cart->getInstanceName());

        $this->cart->instance("!QAZ1qaz");
        $this->assertEquals("!QAZ1qaz",$this->cart->getInstanceName());


    }
    public function testUpdate(){
        $this->cart->put([
            "id"=>"test",
            "quantity"=>10000,
            "price"=>1000
        ],$this->model);
        $this->cart->update(950,$this->model);
        $this->assertEquals([
            "id"=>"test",
            "quantity"=>950,
            "price"=>1000,
            'cartable_id' => 1,
            'cartable_type' => 'ModelMock'
        ],$this->cart->get($this->model,false));

    }
}
