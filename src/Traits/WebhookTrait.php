<?php

namespace tylercubell\Billing\Traits;

use tylercubell\Billing\Exceptions\BillingException;
use tylercubell\Billing\Events\BillingEvent;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles Stripe webhook events.
 *
 * Event types not included:
 *
 * account.*
 * application_fee.*
 * balance.*
 * bitcoin.*
 * order.*
 * order_return.*
 * product.*
 * recipient.*
 * review.*
 * sku.*
 * source.transaction.created
 * transfer.*
 * 
 * @see https://stripe.com/docs/api?lang=php#event_types
 */

trait WebhookTrait
{
    /**
     * Handle a Stripe webhook call.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function webhook(Request $request)
    {
        try {
            $payload = json_decode($request->getContent(), true);
            $event = \Stripe\Event::retrieve($payload['id']);

            if ($event === null) {
                return new Response;
            }
        } catch(\Stripe\Error\Card $e) {
            return new Response;
        } catch (\Stripe\Error\RateLimit $e) {
            return new Response;
        } catch (\Stripe\Error\InvalidRequest $e) {
            return new Response;
        } catch (\Stripe\Error\Authentication $e) {
            return new Response;
        } catch (\Stripe\Error\ApiConnection $e) {
            return new Response;
        } catch (\Stripe\Error\Base $e) {
            return new Response;
        } catch (Exception $e) {
            return new Response;
        }

        $prefix = 'billing.webhook.';
        $type = $event->type;
        $object = $event->data->object;

        switch ($type) {
            // Charges
            case 'charge.captured':
            case 'charge.failed':
            case 'charge.pending':
            case 'charge.refunded':
            case 'charge.succeeded':
            case 'charge.updated':
                $result = $this->localUpdateOrCreateCharge($object);
                event(new BillingEvent($prefix . $type, $result));
                break;

            // Coupons
            case 'coupon.created':
            case 'coupon.updated':
                $result = $this->localUpdateOrCreateCoupon($object);
                event(new BillingEvent($prefix . $type, $result));
                break;
            case 'coupon.deleted':
                $result = $this->deleteCoupon($object);
                event(new BillingEvent($prefix . $type, $result));
                break;

            // Customers
            case 'customer.created':
            case 'customer.updated':
                $result = $this->localUpdateOrCreateCustomer($object);
                event(new BillingEvent($prefix . $type, $result));
                break;
            case 'customer.deleted':
                $result = $this->localDeleteCustomer($object);
                event(new BillingEvent($prefix . $type, $result));
                break;

            // Discounts
            case 'customer.discount.created':
            case 'customer.discount.updated':
                $result = $this->localUpdateOrCreateDiscount($object);
                event(new BillingEvent($prefix . $type, $result));
                break;
            case 'customer.discount.deleted':
                $result = $this->localDeleteDiscount($object);
                event(new BillingEvent($prefix . $type, $result));
                break;

            // Disputes
            case 'charge.dispute.closed':
            case 'charge.dispute.created':
            case 'charge.dispute.funds_reinstated':
            case 'charge.dispute.funds_withdrawn':
            case 'charge.dispute.updated':
                $result = $this->localUpdateOrCreateDispute($object);
                event(new BillingEvent($prefix . $type, $result));
                break;

            // Sources (cards)
            case 'customer.source.created':
            case 'customer.source.updated':
                $result = $this->localUpdateOrCreateCard($object);
                event(new BillingEvent($prefix . $type, $result));
                break;
            case 'customer.source.deleted':
                $result = $this->localDeleteCard($object);
                event(new BillingEvent($prefix . $type, $result));
                break;

            // Subscriptions
            case 'customer.subscription.created':
            case 'customer.subscription.updated':
            case 'customer.subscription.trial_will_end':
                $result = $this->localUpdateOrCreateSubscription($object);
                event(new BillingEvent($prefix . $type, $result));
                break;
            case 'customer.subscription.deleted':
                $result = $this->localUpdateorCreateSubscription($object);
                event(new BillingEvent($prefix . $type, $result));
                break;

            // Invoices
            case 'invoice.created':
            case 'invoice.updated':
            case 'invoice.payment_failed':
            case 'invoice.payment_succeeded':
            case 'invoice.sent':
                $result = $this->localUpdateOrCreateInvoice($object);
                event(new BillingEvent($prefix . $type, $result));
                break;

            // Invoice Items
            case 'invoiceitem.created':
            case 'invoiceitem.updated':
                $result = $this->localUpdateOrCreateInvoiceItem($object);
                event(new BillingEvent($prefix . $type, $result));
                break;
            case 'invoiceitem.deleted':
                $result = $this->localDeleteInvoiceItem($object);
                event(new BillingEvent($prefix . $type, $result));
                break;

            // Plans
            case 'plan.created':
            case 'plan.updated':
                $result = $this->localUpdateOrCreatePlan($object);
                event(new BillingEvent($prefix . $type, $result));
                break;
            case 'plan.deleted':
                $result = $this->localDeletePlan($object);
                event(new BillingEvent($prefix . $type, $result));
                break;

            // Cards
            case 'source.canceled':
            case 'source.chargeable':
            case 'source.failed':
                $result = $this->localUpdateOrCreateCard($object);
                event(new BillingEvent($prefix . $type, $result));
                break;

            // Stripe pings and unsupported events
            case 'ping':
            default:
                break;
        }

        return new Response;
    }
}
