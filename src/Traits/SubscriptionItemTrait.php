<?php

namespace tylercubell\Billing\Traits;

use tylercubell\Billing\Exceptions\BillingException;
use tylercubell\Billing\Events\BillingEvent;
use tylercubell\Billing\Models\SubscriptionItemModel;

trait SubscriptionItemTrait
{
    /**
     * Create subscription item.
     *
     * @see https://stripe.com/docs/api?lang=php#create_subscription_item
     *
     * @param array $arguments
     * @return array|boolean
     */
    public function createSubscriptionItem(array $arguments)
    {
        try {
            $subscriptionItem = \Stripe\SubscriptionItem::create($arguments);

            $result = $this->localUpdateOrCreateSubscriptionItem($subscriptionItem, $arguments['subscription']);
            event(new BillingEvent('billing.subscription_item.create', $result));
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
     * Retrieve subscription item.
     *
     * @see https://stripe.com/docs/api?lang=php#retrieve_subscription_item
     *
     * @param string $subscriptionItemId
     * @param bool $sync
     * @return array
     */
    public function retrieveSubscriptionItem(string $subscriptionItemId, bool $sync = false)
    {
        if ($sync) {
            try {
                $subscriptionItem = \Stripe\SubscriptionItem::retrieve($subscriptionItemId);
                return $this->localUpdateOrCreateSubscriptionItem($subscriptionItem);
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

        $subscriptionItem = SubscriptionItemModel::where('id', '=', $subscriptionItemId)->first();

        return ($subscriptionItem !== null) ? $subscriptionItem->toArray() : null;
    }

    /**
     * Update subscription item.
     *
     * @see https://stripe.com/docs/api?lang=php#update_subscription_item
     *
     * @param string $subscriptionItemId
     * @param array $arguments
     * @return array|boolean
     */
    public function updateSubscriptionItem(string $subscriptionItemId, array $arguments)
    {
        try {
            $subscriptionItem = \Stripe\SubscriptionItem::retrieve($subscriptionItemId);

            foreach ($arguments as $key => $value) {
                $subscriptionItem->{$key} = $value;
            }

            $update = $subscriptionItem->save();

            $result = $this->localUpdateOrCreateSubscriptionItem($update, $subscriptionItemId);
            event(new BillingEvent('billing.subscription_item.update', $result));
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
     * Delete subscription item.
     *
     * @see https://stripe.com/docs/api?lang=php#delete_subscription_item
     *
     * @param string $subscriptionItemId
     * @return array|boolean
     */
    public function deleteSubscriptionItem(string $subscriptionItemId)
    {
        try {
            $subscriptionItem = \Stripe\SubscriptionItem::retrieve($subscriptionItemId);
            $delete = $subscriptionItem->delete();

            $result = $this->localDeleteSubscriptionItem($delete);
            event(new BillingEvent('billing.subscription_item.delete', $result));
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
     * List all subscription items for a given subscription.
     *
     * @see https://stripe.com/docs/api?lang=php#list_subscription_items
     *
     * @param string $subscriptionId
     * @param array $arguments
     * @param bool $sync
     * @return array
     */
    public function listAllSubscriptionItems(string $subscriptionId, array $arguments = [], bool $sync = false)
    {
        if ($sync) {
            try {
                $hasMore = true;
                $arguments = array_merge($arguments, [
                    'subscription' => $subscriptionId,
                    'limit'        => 100
                ]);

                while ($hasMore) {
                    $subscriptionItems = \Stripe\SubscriptionItem::all($arguments);

                    foreach ($subscriptionItems->data as $subscriptionItem) {
                        $this->localUpdateOrCreateSubscriptionItem($subscriptionItem);
                    }

                    if ( ! $subscriptionItems->has_more) {
                        $hasMore = false;
                    } else {
                        $lastSubscriptionItem = end($subscriptionItems->data);
                        $arguments['starting_after'] = $lastSubscriptionItem->id;
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

        $data = SubscriptionItemModel::where('subscription', '=', $subscriptionId);

        if (isset($arguments['limit'])) {
            $data = $data->limit($arguments['limit']);
        }

        $data = $data->orderBy('created', 'desc')->get()->toArray();

        return [
            'object'      => 'list',
            'data'        => $data,
            'has_more'    => false,
            'total_count' => count($data),
            'url'         => '/v1/subscription_items'
        ];
    }

    /**
     * Update or create subscription item locally.
     *
     * @param $subscriptionItem
     * @param string $subscriptionId
     * @param string $changeId
     * @return array
     */
    public function localUpdateOrCreateSubscriptionItem($subscriptionItem, string $subscriptionId, string $changeId = null)
    {
        $changeId = $changeId ? $changeId : $this->generateId();

        $result = SubscriptionItemModel::updateOrCreate(
            ['id' => $subscriptionItem->id],
            [
                'subscription'   => $subscriptionId,
                'created'        => $subscriptionItem->created,
                'plan'           => $subscriptionItem->plan->id,
                'quantity'       => $subscriptionItem->quantity,
                'change_id'      => $changeId,
                'sync_id'        => $this->syncId()
            ]
        );

        return $result->toArray();
    }

    /**
     * Delete subscription item locally.
     *
     * @param $subscriptionItem
     * @return array
     */
    public function localDeleteSubscriptionItem($subscriptionItem)
    {
        $result = SubscriptionItemModel::where('id', '=', $subscriptionItem->id)->first();

        SubscriptionItemModel::where('id', '=', $subscriptionItem->id)->delete();

        return $result;
    }
}
