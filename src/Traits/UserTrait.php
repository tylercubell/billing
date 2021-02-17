<?php

namespace tylercubell\Billing\Traits;

use tylercubell\Billing\Exceptions\BillingException;
use Billing;

trait UserTrait
{
    /**
     * Retrieve Stripe customer.
     *
     * @return array
     */
    public function customer()
    {
        return Billing::retrieveCustomerByUserId($this->id);
    }

    /**
     * Retrieve most recently created subscription.
     *
     * @return array|null
     */
    public function subscription()
    {
        $customer = $this->customer();

        if ($customer !== null) {
            $subscription = Billing::listAllSubscriptions([
                'customer' => $customer['id'],
                'status'   => 'all', 
                'limit'    => 1
            ]);

            if (count($subscription) > 0) {
                return $subscription['data'][0];
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * List all subscriptions. Sorted by most recent first.
     *
     * @return array|null
     */
    public function subscriptions()
    {
        $customer = $this->customer();

        if ($customer !== null) {
            $subscriptions = Billing::listAllSubscriptions([
                'customer' => $customer['id'],
                'status'   => 'all'
            ]);

            if (count($subscriptions) > 0) {
                return $subscriptions['data'];
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * Retrieve customer's default source (card).
     *
     * @return array|null
     */
    public function card()
    {
        $customer = $this->customer();

        if ($customer !== null) {
            $defaultSource = $customer['default_source'];

            if ($defaultSource !== null) {
                return Billing::retrieveCard($customer['id'], $defaultSource);
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * List all cards.
     *
     * @return array|null
     */
    public function cards()
    {
        $customer = $this->customer();

        if ($customer !== null) {
            $cards = Billing::listAllCards($customer['id']);
            return $cards['data'];
        } else {
            return null;
        }
    }

    /**
     * Retrieve discount applied to customer.
     *
     * @return array|null
     */
    public function discount()
    {
        $customer = $this->customer();

        if ($customer !== null) {
            return Billing::retrieveCustomerDiscount($customer['id']);
        } else {
            return null;
        }
    }

    /**
     * List most recent customer invoices. Default limit is 10.
     *
     * @param int $limit
     * @return array|null
     */
    public function invoices(int $limit = 10)
    {
        $customer = $this->customer();

        if ($customer !== null) {
            $invoices = Billing::listAllInvoices([
                'customer' => $customer['id'],
                'limit'   => $limit
            ]);

            if (count($invoices) > 0) {
                return $invoices['data'];
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
}
