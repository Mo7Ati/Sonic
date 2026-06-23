<?php

return [

    'order_status' => [
        'preparing' => [
            'title' => 'Order accepted',
            'body' => 'Your order #:id has been accepted and is now being prepared.',
        ],
        'on_the_way' => [
            'title' => 'Order on the way',
            'body' => 'Your order #:id is on its way to you.',
        ],
        'completed' => [
            'title' => 'Order completed',
            'body' => 'Your order #:id has been completed. Enjoy!',
        ],
        'cancelled' => [
            'title' => 'Order cancelled',
            'body' => 'Your order #:id has been cancelled.',
        ],
        'rejected' => [
            'title' => 'Order rejected',
            'body' => 'Unfortunately your order #:id was rejected.',
        ],
        'pending' => [
            'title' => 'Order updated',
            'body' => 'Your order #:id status has been updated.',
        ],
    ],

];
