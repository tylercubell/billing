<?php

return [

    /*
    |--------------------------------------------------------------------------
    | General Settings
    |--------------------------------------------------------------------------
    */

    // Where to redirect a user if middleware prevents an action.
    // 'billing_status', 'billing_type', and 'billing_message' describing the
    // error are flashed to the session with the redirect.
    'middleware' => [
        'redirect' => '/'
    ],

    /*
    |--------------------------------------------------------------------------
    | Billing Bootstrap
    |--------------------------------------------------------------------------
    |
    | Billing will try to create the plans and coupons defined below in Stripe 
    | and sync them with your application.
    |
    | Run `php artisan billing:bootstrap` once configured.
    |
    | See: https://stripe.com/docs/api?lang=php#create_plan
    | See: https://stripe.com/docs/api?lang=php#create_coupon
    |
    */

    'plans' => [
        [
            'id'                => 'firstplan',
            'amount'            => 100 * 100, // $100, in cents
            'interval'          => 'month',
            'currency'          => 'usd',
            'name'              => 'First Plan',
            'trial_period_days' => 30
        ],
        [
            'id'                => 'secondplan',
            'amount'            => 200 * 100, // $200, in cents
            'interval'          => 'month',
            'currency'          => 'usd',
            'name'              => 'Second Plan',
            'trial_period_days' => 30
        ]
    ],

    'coupons' => [
        [
            'id'          => 'testcoupon',
            'duration'    => 'forever',
            'percent_off' => 20
        ]
    ]

];
