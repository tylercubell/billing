<?php

namespace tylercubell\Billing\Traits;

use tylercubell\Billing\Exceptions\BillingException;
use tylercubell\Billing\Events\BillingEvent;
use tylercubell\Billing\Models\RefundModel;
use tylercubell\Billing\Models\MetadataModel;

trait RefundTrait
{
    /**
     * Create refund.
     *
     * @see https://stripe.com/docs/api?lang=php#create_refund
     *
     * @param array $arguments
     * @return array|boolean
     */
    public function createRefund(array $arguments)
    {
        try {
            $refund = \Stripe\Refund::create($arguments);

            $result = $this->localUpdateOrCreateRefund($refund);
            event(new BillingEvent('billing.refund.create', $result));
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
     * Retrieve refund.
     *
     * @see https://stripe.com/docs/api?lang=php#retrieve_refund
     *
     * @param string $refundId
     * @param bool $sync
     * @return array
     */
    public function retrieveRefund(string $refundId, bool $sync = false)
    {
        if ($sync) {
            try {
                $refund = \Stripe\Refund::retrieve($refundId);
                return $this->localUpdateOrCreateRefund($refund);
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

        $refund = RefundModel::where('id', '=', $refundId)->first();

        return ($refund !== null) ? $refund->toArray() : null;
    }

    /**
     * Update refund.
     *
     * @see https://stripe.com/docs/api?lang=php#update_refund
     *
     * @param string $refundId
     * @param array $arguments
     * @return array
     */
    public function updateRefund(string $refundId, array $arguments)
    {
        try {
            $refund = \Stripe\Refund::retrieve($refundId);

            foreach ($arguments as $key => $value) {
                $refund->{$key} = $value;
            }

            $update = $refund->save();

            $result = $this->localUpdateOrCreateRefund($update);
            event(new BillingEvent('billing.refund.update', $result));
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
     * List all previously created refunds.
     *
     * @see https://stripe.com/docs/api?lang=php#list_refunds
     *
     * @param array $arguments
     * @param bool $sync
     * @return array
     */
    public function listAllRefunds(array $arguments = [], bool $sync = false)
    {
        if ($sync) {
            try {
                $hasMore = true;
                $arguments = array_merge($arguments, ['limit' => 100]);

                while ($hasMore) {
                    $refunds = \Stripe\Refund::all($arguments);

                    foreach ($refunds->data as $refund) {
                        $this->localUpdateOrCreateRefund($refund);
                    }

                    if ( ! $refunds->has_more) {
                        $hasMore = false;
                    } else {
                        $lastRefund = end($refunds->data);
                        $arguments['starting_after'] = $lastRefund->id;
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

        $data = new RefundModel;

        if (isset($arguments['charge'])) {
            $data = $data->where('charge', '=', $arguments['charge']);
        }

        if (isset($arguments['limit'])) {
            $data = $data->limit($arguments['limit']);
        }

        $data = $data->orderBy('created', 'desc')->get()->toArray();

        return [
            'object'      => 'list',
            'data'        => $data,
            'has_more'    => false,
            'total_count' => count($data),
            'url'         => '/v1/refunds'
        ];
    }

    /**
     * Update or create refund locally.
     *
     * @param $refund
     * @param string $changeId
     * @return array
     */
    public function localUpdateOrCreateRefund($refund, string $changeId = null)
    {
        $changeId = $changeId ? $changeId : $this->generateId();

        // Update or create refund
        $result = RefundModel::updateOrCreate(
            ['id' => $refund->id],
            [
                'amount'                => $refund->amount,
                'balance_transaction'   => $refund->balance_transaction,
                'charge'                => $refund->charge,
                'created'               => $refund->created,
                'currency'              => $refund->currency,
                'description'           => $refund->description,
                'reason'                => $refund->reason,
                'receipt_number'        => $refund->receipt_number,
                'status'                => $refund->status,
                'change_id'             => $changeId,
                'sync_id'               => $this->syncId()
            ]
        );

        // Update or create refund metadata
        if ( ! empty($refund->metadata)) {
            foreach ($refund->metadata as $key => $value) {
                MetadataModel::updateOrCreate(
                    ['stripe_id' => $refund->id],
                    [
                        'key'       => $key,
                        'value'     => $value,
                        'type'      => 'refund',
                        'change_id' => $changeId,
                        'sync_id'   => $this->syncId()
                    ]
                );
            }
        }

        // Delete refund metadata that doesn't exist in Stripe
        MetadataModel::where('type', '=', 'refund')
                     ->where('stripe_id', '=', $refund->id)
                     ->where('change_id', '<>', $changeId)
                     ->delete();

        return $result->toArray();
    }
}
