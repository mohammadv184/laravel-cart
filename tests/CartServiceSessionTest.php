<?php

namespace Mohammadv184\Cart\Tests;

use Mohammadv184\Cart\CartService;
use Mohammadv184\Cart\Tests\helpers\ModelFake;
use Mohammadv184\Cart\Tests\helpers\SessionFake;

class CartServiceSessionTest extends TestCase
{
    /**
     * @var CartService
     */
    protected $cart;

    /**
     * @var ModelFake
     */
    protected $model;

    /**
     *setup test method.
     * @throws \Exception
     */
    protected function setUp(): void
    {
        $config=[
            'instanceName'=> 'cart',
            'sessionStatus'=> false
        ];
        $this->cart = new CartService($config, new SessionFake(), 'session');
        $this->model = new ModelFake();
    }

    /**
     *test Cart put method.
     */
    public function testPut()
    {
        $this->cart->put([
            'id'      => 'test',
            'quantity'=> 1,
            'price'   => 1000,
        ], $this->model);
        $this->assertEquals(1, $this->cart->all(false)->count(), 'Cart should have 1 item on it');
        $this->assertEquals([
            'id'           => 'test',
            'quantity'     => 1,
            'price'        => 1000,
            'cartable_type'=> get_class($this->model),
            'cartable_id'  => 1,
            'modelfake'    => $this->model,
        ], $this->cart->get($this->model));
        $this->assertTrue($this->cart->has('test'));
        $this->assertTrue($this->cart->has($this->model));
    }

    /**
     *test Cart Update method.
     */
    public function testUpdate()
    {
        $this->cart->put([
            'id'      => 'test',
            'quantity'=> 10000,
            'price'   => 1000,
        ], $this->model);
        $this->cart->update(950, $this->model);
        $this->assertEquals([
            'id'            => 'test',
            'quantity'      => 950,
            'price'         => 1000,
            'cartable_id'   => 1,
            'cartable_type' => 'Mohammadv184\Cart\Tests\helpers\ModelFake',
        ], $this->cart->get($this->model, false));

        $this->cart->update([
            'id'      => 'test2',
            'quantity'=> 960,
            'price'   => 0,
        ], $this->model);

        $this->assertEquals([
            'id'            => 'test2',
            'quantity'      => 960,
            'price'         => 0,
            'cartable_id'   => 1,
            'cartable_type' => 'Mohammadv184\Cart\Tests\helpers\ModelFake',
        ], $this->cart->get($this->model, false));
    }

    /**
     *test Cart Has method.
     */
    public function testHas()
    {
        $this->cart->put([
            'id'      => 'test',
            'quantity'=> 10000,
            'price'   => 1000,
        ], $this->model);
        $this->assertTrue($this->cart->has($this->model));

        $this->assertTrue($this->cart->has('test'));

        $this->assertFalse($this->cart->has('test2'));

        $this->model->id = 2;
        $this->assertFalse($this->cart->has($this->model));
    }

    /**
     *test Cart Delete method.
     */
    public function testDelete()
    {
        $this->cart->put([
            'id'      => 'test',
            'quantity'=> 10000,
            'price'   => 1000,
        ], $this->model);

        $this->assertEquals(1, $this->cart->all(false)->count());

        $this->cart->delete('test');

        $this->assertEquals(0, $this->cart->all(false)->count());
    }

    /**
     *test Cart Flush method.
     */
    public function testFlush()
    {
        $this->cart->put([
            'id'      => 'test1',
            'quantity'=> 10000,
            'price'   => 1000,
        ], $this->model);
        $this->model->id = 2;
        $this->cart->put([
            'id'      => 'test2',
            'quantity'=> 9508,
            'price'   => 384,
        ], $this->model);
        $this->model->id = 3;
        $this->cart->put([
            'id'      => 'test3',
            'quantity'=> 50,
            'price'   => 1,
        ], $this->model);

        $this->assertEquals(3, $this->cart->all()->count());

        $this->cart->flush();

        $this->assertEquals(0, $this->cart->all()->count());
    }

    /**
     *test Cart Get method.
     */
    public function testGet()
    {
        $this->cart->put([
            'id'      => 'test1',
            'quantity'=> 10000,
            'price'   => 1000,
        ], $this->model);
        $this->model->id = 2;
        $this->cart->put([
            'id'      => 'test2',
            'quantity'=> 9508,
            'price'   => 384,
        ], $this->model);
        $this->model->id = 3;
        $this->cart->put([
            'id'      => 'test3',
            'quantity'=> 50,
            'price'   => 1,
        ], $this->model);

        $this->assertEquals([
            'id'            => 'test3',
            'quantity'      => 50,
            'price'         => 1,
            'cartable_id'   => 3,
            'cartable_type' => 'Mohammadv184\Cart\Tests\helpers\ModelFake',
        ], $this->cart->get('test3', false));
        $this->model->id = 2;
        $this->assertEquals([
            'id'            => 'test2',
            'quantity'      => 9508,
            'price'         => 384,
            'cartable_id'   => 2,
            'cartable_type' => 'Mohammadv184\Cart\Tests\helpers\ModelFake',
            'modelfake'     => $this->model,

        ], $this->cart->get('test2'));
    }

    /**
     *test Cart All method.
     */
    public function testAll()
    {
        $this->cart->put([
            'id'      => 'test1',
            'quantity'=> 10000,
            'price'   => 1000,
        ], $this->model);
        $this->model->id = 2;
        $this->cart->put([
            'id'      => 'test2',
            'quantity'=> 9508,
            'price'   => 384,
        ], $this->model);
        $this->model->id = 3;
        $this->cart->put([
            'id'      => 'test3',
            'quantity'=> 50,
            'price'   => 1,
        ], $this->model);

        $this->assertEquals(3, $this->cart->all()->count());

        $this->assertEquals([
            'id'            => 'test3',
            'price'         => 1,
            'quantity'      => 50,
            'cartable_id'   => 3,
            'cartable_type' => 'Mohammadv184\Cart\Tests\helpers\ModelFake',
            'modelfake'     => $this->model,

        ], $this->cart->all()['test3']);
    }

    /**
     *test Cart TotalPrice method.
     */
    public function testTotalPrice()
    {
        $this->cart->put([
            'id'      => 'test1',
            'quantity'=> 10000,
            'price'   => 1000,
        ], $this->model);
        $this->model->id = 2;
        $this->cart->put([
            'id'      => 'test2',
            'quantity'=> 9508,
            'price'   => 215,
        ], $this->model);
        $this->model->id = 3;
        $this->cart->put([
            'id'      => 'test3',
            'quantity'=> 50,
            'price'   => 8000,
        ], $this->model);

        $this->assertEquals(12444220, $this->cart->totalPrice());
        $this->assertEquals('12,444,220', $this->cart->totalPrice(true));
    }
}
