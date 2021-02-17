<?php

namespace tylercubell\Billing\Traits;

use tylercubell\Billing\Exceptions\BillingException;
use tylercubell\Billing\Models\CardModel;
use tylercubell\Billing\Models\ChargeModel;
use tylercubell\Billing\Models\CouponModel;
use tylercubell\Billing\Models\CustomerModel;
use tylercubell\Billing\Models\DiscountModel;
use tylercubell\Billing\Models\DisputeModel;
use tylercubell\Billing\Models\InvoiceItemModel;
use tylercubell\Billing\Models\LineItemModel;
use tylercubell\Billing\Models\InvoiceModel;
use tylercubell\Billing\Models\MetadataModel;
use tylercubell\Billing\Models\PlanModel;
use tylercubell\Billing\Models\RefundModel;
use tylercubell\Billing\Models\SubscriptionItemModel;
use tylercubell\Billing\Models\SubscriptionModel;

trait SyncTrait
{
    /**
     * Keeps track of all data updated or created during a sync. After
     * a sync is complete, data that doesn't have this ID can be
     * deleted because it's out of sync with Stripe.
     */
    public $syncId = null;

    /**
     * Gets and sets sync ID.
     *
     * @return string
     */
    public function syncId()
    {
        if ($this->syncId === null) {
            $this->syncId = $this->generateId();
        }

        return $this->syncId;
    }

    /**
     * Get available sync options for the artisan billing:sync command.
     *
     * @return void
     */
    public function getSyncOptions()
    {
        return [
            'charges',
            'coupons',
            'customers',
            'disputes',
            'invoice_items',
            'invoices',
            'plans',
            'refunds',
            'subscriptions'
        ];
    }

    /**
     * Sync all charges and charge metadata.
     *
     * @return void
     */
    public function syncCharges()
    {
        $hasMore = true;
        $arguments = ['limit' => 100];

        while ($hasMore) {
            try {
                $charges = \Stripe\Charge::all($arguments);
            } catch(\Stripe\Error\Card $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\RateLimit $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\InvalidRequest $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\Authentication $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\ApiConnection $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\Base $e) {
                throw new BillingException($e);
            } catch (Exception $e) {
                throw $e;
            }

            foreach ($charges->data as $charge) {
                $this->localUpdateOrCreateCharge($charge);
            }

            if ( ! $charges->has_more) {
                $hasMore = false;
            } else {
                $lastCharge = end($charges->data);
                $arguments['starting_after'] = $lastCharge->id;
            }
        }

        ChargeModel::where('sync_id', '<>', $this->syncId)->delete();

        MetadataModel::where('type', '=', 'charge')
                     ->where('sync_id', '<>', $this->syncId)
                     ->delete();
    }

    /**
     * Sync all coupons and coupon metadata.
     *
     * @return void
     */
    public function syncCoupons()
    {
        $hasMore = true;
        $arguments = ['limit' => 100];

        while ($hasMore) {
            try {
                $coupons = \Stripe\Coupon::all($arguments);
            } catch(\Stripe\Error\Card $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\RateLimit $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\InvalidRequest $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\Authentication $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\ApiConnection $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\Base $e) {
                throw new BillingException($e);
            } catch (Exception $e) {
                throw $e;
            }

            foreach ($coupons->data as $coupon) {
                $this->localUpdateOrCreateCoupon($coupon);
            }

            if ( ! $coupons->has_more) {
                $hasMore = false;
            } else {
                $lastCoupon = end($coupons->data);
                $arguments['starting_after'] = $lastCoupon->id;
            }
        }

        CouponModel::where('sync_id', '<>', $this->syncId)->delete();

        MetadataModel::where('type', '=', 'coupon')
                     ->where('sync_id', '<>', $this->syncId)
                     ->delete();
    }

    /**
     * Sync all customers, customer metadata, customer discounts, customer cards, and customer card metadata.
     *
     * @return void
     */
    public function syncCustomers()
    {
        $hasMore = true;
        $arguments = ['limit' => 100];

        while ($hasMore) {
            try {
                $customers = \Stripe\Customer::all($arguments);
            } catch(\Stripe\Error\Card $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\RateLimit $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\InvalidRequest $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\Authentication $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\ApiConnection $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\Base $e) {
                throw new BillingException($e);
            } catch (Exception $e) {
                throw $e;
            }

            foreach ($customers->data as $customer) {
                $this->localUpdateOrCreateCustomer($customer);
            }

            if ( ! $customers->has_more) {
                $hasMore = false;
            } else {
                $lastCustomer = end($customers->data);
                $arguments['starting_after'] = $lastCustomer->id;
            }
        }

        CustomerModel::where('sync_id', '<>', $this->syncId)->delete();

        MetadataModel::where('type', '=', 'customer')
                     ->where('sync_id', '<>', $this->syncId)
                     ->delete();

        DiscountModel::where('sync_id', '<>', $this->syncId)->delete();

        CardModel::where('sync_id', '<>', $this->syncId)->delete();

        MetadataModel::where('type', '=', 'card')
                     ->where('sync_id', '<>', $this->syncId)
                     ->delete();
    }

    /**
     * Sync all disputes and dispute metadata.
     *
     * @return void
     */
    public function syncDisputes()
    {
        $hasMore = true;
        $arguments = ['limit' => 100];

        while ($hasMore) {
            try {
                $disputes = \Stripe\Dispute::all($arguments);
            } catch(\Stripe\Error\Card $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\RateLimit $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\InvalidRequest $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\Authentication $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\ApiConnection $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\Base $e) {
                throw new BillingException($e);
            } catch (Exception $e) {
                throw $e;
            }

            foreach ($disputes->data as $dispute) {
                $this->localUpdateOrCreateDispute($dispute);
            }

            if ( ! $disputes->has_more) {
                $hasMore = false;
            } else {
                $lastDispute = end($disputes->data);
                $arguments['starting_after'] = $lastDispute->id;
            }
        }

        DisputeModel::where('sync_id', '<>', $this->syncId)->delete();

        MetadataModel::where('type', '=', 'dispute')
                     ->where('sync_id', '<>', $this->syncId)
                     ->delete();
    }

    /**
     * Sync all invoice items and invoice item metadata.
     *
     * @return void
     */
    public function syncInvoiceItems()
    {
        $hasMore = true;
        $arguments = ['limit' => 100];

        while ($hasMore) {
            try {
                $invoiceItems = \Stripe\InvoiceItem::all($arguments);
            } catch(\Stripe\Error\Card $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\RateLimit $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\InvalidRequest $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\Authentication $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\ApiConnection $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\Base $e) {
                throw new BillingException($e);
            } catch (Exception $e) {
                throw $e;
            }

            foreach ($invoiceItems->data as $invoiceItem) {
                $this->localUpdateOrCreateInvoiceItem($invoiceItem);
            }

            if ( ! $invoiceItems->has_more) {
                $hasMore = false;
            } else {
                $lastInvoiceItem = end($invoiceItems->data);
                $arguments['starting_after'] = $lastInvoiceItem->id;
            }
        }

        InvoiceItemModel::where('sync_id', '<>', $this->syncId)->delete();

        MetadataModel::where('type', '=', 'invoice_item')
                     ->where('sync_id', '<>', $this->syncId)
                     ->delete();
    }

    /**
     * Sync all invoices, invoice metadata, invoice line items, and invoice line item metadata.
     *
     * @return void
     */
    public function syncInvoices()
    {
        $hasMore = true;
        $arguments = ['limit' => 100];

        while ($hasMore) {
            try {
                $invoices = \Stripe\Invoice::all($arguments);
            } catch(\Stripe\Error\Card $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\RateLimit $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\InvalidRequest $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\Authentication $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\ApiConnection $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\Base $e) {
                throw new BillingException($e);
            } catch (Exception $e) {
                throw $e;
            }

            foreach ($invoices->data as $invoice) {
                $this->localUpdateOrCreateInvoice($invoice);
            }

            if ( ! $invoices->has_more) {
                $hasMore = false;
            } else {
                $lastInvoice = end($invoices->data);
                $arguments['starting_after'] = $lastInvoice->id;
            }
        }

        InvoiceModel::where('sync_id', '<>', $this->syncId)->delete();

        MetadataModel::where('type', '=', 'invoice')
                     ->where('sync_id', '<>', $this->syncId)
                     ->delete();

        LineItemModel::where('sync_id', '<>', $this->syncId)->delete();

        MetadataModel::where('type', '=', 'invoice_line_item')
                     ->where('sync_id', '<>', $this->syncId)
                     ->delete();
    }

    /**
     * Sync all plans and plan metadata.
     *
     * @return void
     */
    public function syncPlans()
    {
        $hasMore = true;
        $arguments = ['limit' => 100];

        while ($hasMore) {
            try {
                $plans = \Stripe\Plan::all($arguments);
            } catch(\Stripe\Error\Card $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\RateLimit $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\InvalidRequest $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\Authentication $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\ApiConnection $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\Base $e) {
                throw new BillingException($e);
            } catch (Exception $e) {
                throw $e;
            }

            foreach ($plans->data as $plan) {
                $this->localUpdateOrCreatePlan($plan);
            }

            if ( ! $plans->has_more) {
                $hasMore = false;
            } else {
                $lastPlan = end($plans->data);
                $arguments['starting_after'] = $lastPlan->id;
            }
        }

        PlanModel::where('sync_id', '<>', $this->syncId)->delete();

        MetadataModel::where('type', '=', 'plan')
                     ->where('sync_id', '<>', $this->syncId)
                     ->delete();
    }

    /**
     * Sync all refunds and refund metadata.
     *
     * @return void
     */
    public function syncRefunds()
    {
        $hasMore = true;
        $arguments = ['limit' => 100];

        while ($hasMore) {
            try {
               $refunds = \Stripe\Refund::all($arguments); 
            } catch(\Stripe\Error\Card $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\RateLimit $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\InvalidRequest $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\Authentication $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\ApiConnection $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\Base $e) {
                throw new BillingException($e);
            } catch (Exception $e) {
                throw $e;
            }

            foreach ($refunds->data as $refund) {
                $this->localUpdateOrCreateRefund($plan);
            }

            if ( ! $refunds->has_more) {
                $hasMore = false;
            } else {
                $lastRefund = end($refunds->data);
                $arguments['starting_after'] = $lastRefund->id;
            }
        }

        RefundModel::where('sync_id', '<>', $this->syncId)->delete();

        MetadataModel::where('type', '=', 'refund')
                     ->where('sync_id', '<>', $this->syncId)
                     ->delete();
    }

    /**
     * Sync all subscriptions, subscription metadata, subscription discounts, and subscription items.
     *
     * @return void
     */
    public function syncSubscriptions()
    {
        $hasMore = true;
        $arguments = ['status' => 'all', 'limit' => 100];

        while ($hasMore) {
            try {
               $subscriptions = \Stripe\Subscription::all($arguments); 
            } catch(\Stripe\Error\Card $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\RateLimit $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\InvalidRequest $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\Authentication $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\ApiConnection $e) {
                throw new BillingException($e);
            } catch (\Stripe\Error\Base $e) {
                throw new BillingException($e);
            } catch (Exception $e) {
                throw $e;
            }

            foreach ($subscriptions->data as $subscription) {
                $this->localUpdateOrCreateSubscription($subscription);
            }

            if ( ! $subscriptions->has_more) {
                $hasMore = false;
            } else {
                $lastSubscription = end($subscriptions->data);
                $arguments['starting_after'] = $lastSubscription->id;
            }
        }

        SubscriptionModel::where('sync_id', '<>', $this->syncId)->delete();

        DiscountModel::where('sync_id', '<>', $this->syncId)->delete();

        SubscriptionItemModel::where('sync_id', '<>', $this->syncId)->delete();

        MetadataModel::where('type', '=', 'subscription')
                     ->where('sync_id', '<>', $this->syncId)
                     ->delete();
    }
}
