<?php

return [
    /*
    |--------------------------------------------------------------------------
    | laravel cart session key
    |--------------------------------------------------------------------------
    |
    | This default instanceName will be used when you put a Cart item
    | used in session key.
    |
    */
    'instanceName'=> 'cart',

    /*
   |--------------------------------------------------------------------------
   | laravel cart table name
   |--------------------------------------------------------------------------
   |
   | Here you can set the table name that the laravel cart should use in
   | migration and database table name.
   |
   */
    'table'       => 'cart_items',

    /*
   |--------------------------------------------------------------------------
   | laravel cart session storage
   |--------------------------------------------------------------------------
   |
   | laravel cart session storage status when the user is guest
   |
   */
    'sessionStatus'     => true,
];
