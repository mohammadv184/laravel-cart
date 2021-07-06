# laravel-cart
[![Latest Stable Version](http://poser.pugx.org/mohammadv184/laravel-cart/v)](https://packagist.org/packages/mohammadv184/laravel-cart) 
[![Total Downloads](http://poser.pugx.org/mohammadv184/laravel-cart/downloads)](https://packagist.org/packages/mohammadv184/laravel-cart) 
[![Monthly Downloads](http://poser.pugx.org/mohammadv184/laravel-cart/d/monthly)](https://packagist.org/packages/mohammadv184/laravel-cart)
[![Build Status](https://travis-ci.com/mohammadv184/laravel-cart.svg?branch=main)](https://travis-ci.com/mohammadv184/laravel-cart)
[![License](http://poser.pugx.org/mohammadv184/laravel-cart/license)](https://packagist.org/packages/mohammadv184/laravel-cart)

A Cart for Laravel Framework

## Installation

Install the package through [Composer](http://getcomposer.org/).

Run the Composer require command from the Terminal:

    composer require mohammadv184/laravel-cart

If you're using Laravel 5.5, this is all there is to do.

Should you still be on version 5.4 of Laravel, the final steps for you are to add the service provider of the package and alias the package. To do this open your `config/app.php` file.

Add a new line to the `providers` array:

	Mohammadv184\Cart\CartProvider::class

And optionally add a new line to the `aliases` array:

	'Cart' => Mohammadv184\Cart\Facades\Cart::class,

Now you're ready to start using the laravel-cart in your application.

## Overview
Look at one of the following topics to learn more about Laravel-cart

* [Usage](#usage)
* [Instances](#instances)
* [Example](#example)

## Usage

The laravel-cart gives you the following methods to use:
### Cart::put()

Adding an item to the cart is really simple, you just use the `put()` method, which accepts a variety of parameters.

In its most basic form you can specify the quantity, price and product model of the product you'd like to add to the cart.

```php
$product=Product::find(1);
Cart::put([
    "price"=>10,
    "quantity"=>1
],$product);
```
### Cart::update()

To update an item in the cart, you'll first need the rowId of the item.
Next you can use the `update()` method to update it.

If you simply want to update the quantity, you'll pass the update method the product model or id and the new quantity:

```php
$product=Product::find(1);
Cart::update(2,$product); // Will update the quantity
```
OR

```php
$id = 'Biuwla5871';
Cart::update($id, 2); // Will update the quantity
```

If you want to update more attributes of the item, you can either pass the update method an array as the first parameter. This way you can update all information of the item with the given id.

```php
Cart::update(['price' => 1000],"16cdac2cs8"); // Will update the price
$product=Product::find(1);
Cart::update(['id'=>"1c6a4c6a75",'price'=>1000], $product); // Will update the id and price
```
### Cart::delete()

To delete an item for the cart, you'll again need the product model or id. This id you simply pass to the `delete()` method and it will delete the item from the cart.

```php
$product=Product::find(1);
Cart::delete($product);
```

OR

```php
$id = 'da39a3ee5e';
Cart::delete($id);
```
### Cart::get()

If you want to get an item from the cart using its id or product model, you can simply call the `get()` method on the cart and pass it the id or product model.

```php
$product = Product::find(1);
Cart::get($product);
```

OR

```php
$id = 'da39a3ee5e';
Cart::get($id);
```
### Cart::all()

Of course you also want to get the all items in cart. This is where you'll use the `all` method. This method will return a Collection of Cart Items.

```php
Cart::all();
```

This method will return the all items of the current cart instance, if you want the all items of another instance, simply chain the calls.

```php
Cart::instance('wishlist')->all();
```
### Cart::flush()

If you want to completely delete the all items of a cart, you can call the flush method on the cart. This will delete all Cart Items from the cart for the current cart instance.

```php
Cart::flush();
```
### Cart::totalPrice()

The `totalPrice()` method can be used to get the totalPrice of all items in the cart.

```php
Cart::totalPrice();
```
## Instances

The packages supports multiple instances of the cart. The way this works is like this:

You can set the current instance of the cart by calling `Cart::instance('newInstance')`. From this moment, the active instance of the cart will be `newInstance`, so when you add, remove or get the content of the cart, you're work with the `newInstance` instance of the cart.
If you want to switch instances, you just call `Cart::instance('otherInstance')` again, and you're working with the `otherInstance` again.

So a little example:

```php
$product=Product::find(1);
Cart::instance('shopping')->put(['quantity'=>1,'price'=>9.99],$product);
// Get the all of the 'shopping' cart
Cart::all();
$product=Product::find(2);
Cart::instance('wishlist')->put(['quantity'=>1,'price'=>2.5],$product);
// Get the all of the 'wishlist' cart
Cart::all();
// If you want to get the all of the 'shopping' cart again
Cart::instance('shopping')->all();
```
## Example

Below is a little example of how to list the cart content in a table:

```php
// Add some items in your Controller.
$product=Product::find(1);
Cart::put(['quantity'=>1,'price'=>9.99],$product);
$product=Product::find(2);
Cart::put(['quantity'=>2,'price'=>10],$product);
// Display the content in a View.
<table>
   	<thead>
       	<tr>
           	<th>Product</th>
           	<th>quantity</th>
           	<th>Price</th>
        </tr>
   	</thead>
   	<tbody>
   		@foreach(Cart::all() as $row)
       		<tr>
           		<td>
               		<p><strong>{{$row->product->name}}</strong></p>
               	</td>
           		<td><p>{{$row->quantity}}</p></td>
           		<td>${{$row->price}}</td>
       		</tr>
	   	@endforeach
   	</tbody>
   	
   	<tfoot>
   		<tr>
   			<td colspan="2">&nbsp;</td>
   			<td>Total</td>
   			<td>{{Cart::totalPrice()}}</td>
   		</tr>
   	</tfoot>
</table>
```