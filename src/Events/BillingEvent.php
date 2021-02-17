<?php

namespace tylercubell\Billing\Events;

use App\Order;
use Illuminate\Queue\SerializesModels;

class BillingEvent
{
    use SerializesModels;

    public $type;

    public $object;

    /**
     * Create a new event instance.
     *
     * @param $type
     * @param $object
     * @return void
     */
    public function __construct($type, $object)
    {
        $this->type = $type;
        $this->object = $object;
    }
}
