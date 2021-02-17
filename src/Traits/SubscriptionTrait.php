<?php

namespace tylercubell\Billing\Traits;

use tylercubell\Billing\Exceptions\BillingException;
use tylercubell\Billing\Events\BillingEvent;
use tylercubell\Billing\Models\MetadataModel;
use tylercubell\Billing\Models\SubscriptionModel;
use tylercubell\Billing\Models\SubscriptionItemModel;

trait SubscriptionTrait
{
    /**
     * Create subscription.
     *
     * @see https://stripe.com/docs/api?lang=php#create_subscription
     *
     * @param array $arguments
     * @return array|boolean
     */
    public function createSubscription(array $arguments)
    {
        try {
            $subscription = \Stripe\Subscription::create($arguments);

            $time = time();

            $result = $this->localUpdateOrCreateSubscription($subscription);
            event(new BillingEvent('billing.subscription.create', $result));

            // Sync any newly created invoices
            $this->listAllInvoices([
                'date' => [
                    'gte' => $time
                ]
            ], true);

            // Sync any newly created charges
            $this->listAllCharges([
                'created' => [
                    'gte' => $time
                ]
            ], true);

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
     * Retrieve subscription.
     *
     * @see https://stripe.com/docs/api?lang=php#retrieve_subscription
     *
     * @param string $subscriptionId
     * @param bool $sync
     * @return array
     */
    public function retrieveSubscription(string $subscriptionId, bool $sync = false)
    {
        if ($sync) {
            try {
                $subscription = \Stripe\Subscription::retrieve($subscriptionId);
                return $this->localUpdateOrCreateSubscription($subscription);
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

        $subscription = SubscriptionModel::where('id', '=', $subscriptionId)->first();

        return ($subscription !== null) ? $subscription->toArray() : null;
    }

    /**
     * Update subscription.
     *
     * @see https://stripe.com/docs/api?lang=php#update_subscription
     *
     * @param string $subscriptionId
     * @param array $arguments
     * @return array|boolean
     */
    public function updateSubscription(string $subscriptionId, array $arguments)
    {
        try {
            $subscription = \Stripe\Subscription::retrieve($subscriptionId);

            foreach ($arguments as $key => $value) {
                $subscription->{$key} = $value;
            }

            $update = $subscription->save();

            $time = time();

            $result = $this->localUpdateOrCreateSubscription($update);
            event(new BillingEvent('billing.subscription.update', $result));

            // Sync any newly created invoices
            $this->listAllInvoices([
                'date' => [
                    'gte' => $time
                ]
            ], true);

            // Sync any newly created charges
            $this->listAllCharges([
                'created' => [
                    'gte' => $time
                ]
            ], true);

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
     * Cancel subscription.
     *
     * @see https://stripe.com/docs/api?lang=php#cancel_subscription
     *
     * @param string $subscriptionId
     * @return array|boolean
     */
    public function cancelSubscription(string $subscriptionId)
    {
        try {
            $subscription = \Stripe\Subscription::retrieve($subscriptionId);
            $cancel = $subscription->cancel();

            $time = time();

            $result = $this->localUpdateOrCreateSubscription($cancel);
            event(new BillingEvent('billing.subscription.cancel', $result));

            // Sync any newly created invoices
            $this->listAllInvoices([
                'date' => [
                    'gte' => $time
                ]
            ], true);

            // Sync any newly created charges
            $this->listAllCharges([
                'created' => [
                    'gte' => $time
                ]
            ], true);

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
     * List all subscriptions.
     *
     * @see https://stripe.com/docs/api?lang=php#list_subscriptions
     *
     * @param array $arguments
     * @param bool $sync
     * @return array
     */
    public function listAllSubscriptions(array $arguments = [], bool $sync = false)
    {
        if ($sync) {
            try {
                $hasMore = true;
                $arguments = array_merge($arguments, ['limit' => 100]);

                while ($hasMore) {
                    $subscriptions = \Stripe\Subscription::all($arguments);

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

        $data = new SubscriptionModel;

        if (isset($arguments['created'])) {
            if (isset($arguments['created']['gt'])) {
                $data = $data->where('created', '>', $arguments['created']['gt']);
            }

            if (isset($arguments['created']['gte'])) {
                $data = $data->where('created', '>=', $arguments['created']['gte']);
            }

            if (isset($arguments['created']['lt'])) {
                $data = $data->where('created', '<', $arguments['created']['lt']);
            }

            if (isset($arguments['created']['lte'])) {
                $data = $data->where('created', '<=', $arguments['created']['lte']);
            }
        }

        if (isset($arguments['customer'])) {
            $data = $data->where('customer', '=', $arguments['customer']);
        }

        if (isset($arguments['limit'])) {
            $data = $data->limit($arguments['limit']);
        }

        if (isset($arguments['plan'])) {
            $data = $data->where('plan', '=', $arguments['plan']);
        }

        if (isset($arguments['status'])) {
            if ($arguments['status'] !== 'all') {
                $data = $data->where('status', '=', $arguments['status']);
            }
        }

        $data = $data->orderBy('created', 'desc')->get()->toArray();

        return [
            'object'      => 'list',
            'data'        => $data,
            'has_more'    => false,
            'total_count' => count($data),
            'url'         => '/v1/subscriptions'
        ];
    }

    /**
     * Update or create subscription locally.
     *
     * Exception: Needs to call Stripe API if subscription has over 10 subscription items.
     *
     * @param $subscription
     * @param string $changeId
     * @return array
     */
    public function localUpdateOrCreateSubscription($subscription, string $changeId = null)
    {
        $changeId = $changeId ? $changeId : $this->generateId();

        // Update or create subscription
        $result = SubscriptionModel::updateOrCreate(
            ['id' => $subscription->id],
            [
                'application_fee_percent' => $subscription->application_fee_percent,
                'cancel_at_period_end'    => $subscription->cancel_at_period_end,
                'canceled_at'             => $subscription->canceled_at,
                'created'                 => $subscription->created,
                'current_period_end'      => $subscription->current_period_end,
                'current_period_start'    => $subscription->current_period_start,
                'customer'                => $subscription->customer,
                'ended_at'                => $subscription->ended_at,
                'livemode'                => $subscription->livemode,
                'plan'                    => $subscription->plan->id,
                'quantity'                => $subscription->quantity,
                'start'                   => $subscription->start,
                'status'                  => $subscription->status,
                'tax_percent'             => $subscription->tax_percent,
                'trial_end'               => $subscription->trial_end,
                'trial_start'             => $subscription->trial_start,
                'change_id'               => $changeId,
                'sync_id'                 => $this->syncId()
            ]
        );

        // Update or create subscription metadata
        if ( ! empty($subscription->metadata)) {
            foreach ($subscription->metadata as $key => $value) {
                MetadataModel::updateOrCreate(
                    ['stripe_id' => $subscription->id],
                    [
                        'key'       => $key,
                        'value'     => $value,
                        'type'      => 'subscription',
                        'change_id' => $changeId,
                        'sync_id'   => $this->syncId()
                    ]
                );
            }
        }

        // Delete subscription metadata that doesn't exist in Stripe
        MetadataModel::where('type', '=', 'subscription')
                     ->where('stripe_id', '=', $subscription->id)
                     ->where('change_id', '<>', $changeId)
                     ->delete();

        // Update or create subscription discount
        if ($subscription->discount !== null) {
            $this->localUpdateOrCreateDiscount($subscription->discount);
        }

        // Update or create subscription items
        if ( ! empty($subscription->items->data)) {
            // Subscription has less than or equal to 10 subscription items so no need for extra API calls
            if ($subscription->items->has_more === false) {
                foreach ($subscription->items->data as $subscriptionItem) {
                    $this->localUpdateOrCreateSubscriptionItem($subscriptionItem, $subscription->id, $changeId);
                }
            // Subscription has over 10 subscription items so we need to make multiple requests
            } else {
                $subscriptionItemsHasMore = true;
                $subscriptionItemsArguments = ['subscription' => $subscription->id, 'limit' => 100];

                while ($subscriptionItemsHasMore) {
                    $subscriptionItems = \Stripe\SubscriptionItem::all($subscriptionItemsArguments);

                    foreach ($subscriptionItems->data as $subscriptionItem) {
                        $this->localUpdateOrCreateSubscriptionItem($subscriptionItem, $subscription->id, $changeId);
                    }

                    if ( ! $subscriptionItems->has_more) {
                        $subscriptionItemsHasMore = false;
                    } else {
                        $lastSubscriptionItem = end($subscriptionItems->data);
                        $subscriptionItemsArguments['starting_after'] = $lastSubscriptionItem->id;
                    }
                }
            }
        }

        // Delete subscription items for a subscription that don't exist in Stripe
        SubscriptionItemModel::where('subscription', '=', $subscription->id)
                             ->where('change_id', '<>', $changeId)
                             ->delete();

        return $result->toArray();
    }
}
