<?php

namespace tylercubell\Billing\Facades;

use Illuminate\Support\Facades\Facade;

class Billing extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'billing';
    }
}
