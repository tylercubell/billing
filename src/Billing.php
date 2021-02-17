<?php

namespace tylercubell\Billing;

use App\Http\Controllers\Controller;

class Billing extends Controller
{
    use Traits\CardTrait,
        Traits\ChargeTrait,
        Traits\CouponTrait,
        Traits\CustomerTrait,
        Traits\DiscountTrait,
        Traits\DisputeTrait,
        Traits\InvoiceTrait,
        Traits\InvoiceItemTrait,
        Traits\LineItemTrait,
        Traits\PlanTrait,
        Traits\RefundTrait,
        Traits\SubscriptionTrait,
        Traits\SubscriptionItemTrait,
        Traits\SyncTrait,
        Traits\WebhookTrait;

    /**
     * Stripe API settings.
     */
    public function __construct()
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        \Stripe\Stripe::setApiVersion('2017-01-27');
    }

    /**
     * Generate ID for keeping track of changes.
     */
    public function generateId()
    {
        return time() . '-' . str_random(10);
    }
}
