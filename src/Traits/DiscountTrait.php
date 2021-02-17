<?php

namespace tylercubell\Billing\Traits;

use tylercubell\Billing\Exceptions\BillingException;
use tylercubell\Billing\Events\BillingEvent;
use tylercubell\Billing\Models\DiscountModel;

trait DiscountTrait
{
    /**
     * Apply customer discount.
     *
     * @see https://stripe.com/docs/api?lang=php#update_customer
     *
     * @param string $customerId
     * @param string $couponId
     * @return array|boolean
     */
    public function applyCustomerDiscount(string $customerId, string $couponId)
    {
        try {
            $customer = \Stripe\Customer::retrieve($customerId);
            $customer->coupon = $couponId;
            $result = $customer->save();
            $discount = $result->discount;
            
            $result = $this->localUpdateOrCreateDiscount($discount);
            event(new BillingEvent('billing.discount.customer.create', $result));
            return $result;
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
    }

    /**
     * Retrieve customer discount.
     *
     * @see https://stripe.com/docs/api?lang=php#customer_object
     *
     * @param string $customerId
     * @param bool $sync
     * @return array
     */
    public function retrieveCustomerDiscount(string $customerId, bool $sync = false)
    {
        if ($sync) {
            try {
                $customer = \Stripe\Customer::retrieve($customerId);
                $discount = $customer->discount;
                return $this->localUpdateOrCreateDiscount($discount);
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
        }

        $discount = DiscountModel::where('customer', '=', $customerId)->first();

        return ($discount !== null) ? $discount->toArray() : null;
    }

    /**
     * Delete customer discount.
     *
     * @see https://stripe.com/docs/api?lang=php#delete_discount
     *
     * @param string $customerId
     * @return array|boolean
     */
    public function deleteCustomerDiscount(string $customerId)
    {
        try {
            $customer = \Stripe\Customer::retrieve($customerId);
            $discount = $customer->discount;
            $customer->deleteDiscount();

            $result = $this->localDeleteDiscount($discount);
            event(new BillingEvent('billing.discount.customer.delete', $result));
            return $result;
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
    }

    /**
     * Apply subscription discount.
     *
     * @see https://stripe.com/docs/api?lang=php#update_subscription
     *
     * @param string $subscriptionId
     * @param string $couponId
     * @return array|boolean
     */
    public function applySubscriptionDiscount(string $subscriptionId, string $couponId)
    {
        try {
            $subscription = \Stripe\Subscription::retrieve($subscriptionId);
            $subscription->coupon = $couponId;
            $result = $subscription->save();
            $discount = $result->discount;
            
            $result = $this->localUpdateOrCreateDiscount($discount);
            event(new BillingEvent('billing.discount.subscription.create', $result));
            return $result;
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
    }

    /**
     * Retrieve subscription discount.
     *
     * @see https://stripe.com/docs/api?lang=php#subscription_object
     *
     * @param string $subscriptionId
     * @param bool $sync
     * @return array
     */
    public function retrieveSubscriptionDiscount(string $subscriptionId, bool $sync = false)
    {
        if ($sync) {
            try {
                $subscription = \Stripe\Subscription::retrieve($subscriptionId);
                $discount = $subscription->discount;
                return $this->localUpdateOrCreateDiscount($discount);
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
        }

        $discount = DiscountModel::where('subscription', '=', $subscriptionId)->first();

        return ($discount !== null) ? $discount->toArray() : null;
    }

    /**
     * Delete subscription discount.
     *
     * @see https://stripe.com/docs/api?lang=php#delete_subscription_discount
     *
     * @param string $subscriptionId
     * @return array|boolean
     */
    public function deleteSubscriptionDiscount(string $subscriptionId)
    {
        try {
            $subscription = \Stripe\Subscription::retrieve($subscriptionId);
            $discount = $subscription->discount;
            $subscription->deleteDiscount();
            
            $result = $this->localDeleteDiscount($discount);
            event(new BillingEvent('billing.discount.subscription.delete', $result));
            return $result;
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
    }

    /**
     * Update or create discount locally.
     *
     * @param $discount
     * @param string $changeId
     * @return array
     */
    public function localUpdateOrCreateDiscount($discount, string $changeId = null)
    {
        $changeId = $changeId ? $changeId : $this->generateId();

        // Change attributes depending on discount type (customer or subscription)
        $customer = ['customer' => $discount->customer];
        $subscription = ['subscription' => $discount->subscription];
        $attributes = [
            'coupon'    => $discount->coupon->id,
            'end'       => $discount->end,
            'start'     => $discount->start,
            'change_id' => $changeId,
            'sync_id'   => $this->syncId()
        ];

        if ($discount->subscription !== null) {
            $find = $subscription;
            $attributes = array_merge($attributes, $customer);
        } else {
            $find = $customer;
            $attributes = array_merge($attributes, $subscription);
        }

        // Update or create discount
        $result = DiscountModel::updateOrCreate($find, $attributes);

        // Delete discounts that doesn't exist in Stripe
        if (isset($find['subscription'])) {
            DiscountModel::where('subscription', '=', $find['subscription'])
                         ->where('change_id', '<>', $changeId)
                         ->delete();
        } else {
            DiscountModel::where('customer', '=', $find['customer'])
                         ->where('change_id', '<>', $changeId)
                         ->delete();
        }

        return $result->toArray();
    }

    /**
     * Delete discount locally.
     *
     * @param $discount
     * @return array
     */
    public function localDeleteDiscount($discount)
    {
        if ($discount->subscription !== null) {
            $result = DiscountModel::where('subscription', '=', $discount->subscription)->first();
            DiscountModel::where('subscription', '=', $discount->subscription)->delete();
        } else {
            $result = DiscountModel::where('customer', '=', $discount->customer)->first();
            DiscountModel::where('customer', '=', $discount->customer)->delete();
        }

        return $result;
    }
}
