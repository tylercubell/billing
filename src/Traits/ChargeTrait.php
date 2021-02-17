<?php

namespace tylercubell\Billing\Traits;

use tylercubell\Billing\Exceptions\BillingException;
use tylercubell\Billing\Events\BillingEvent;
use tylercubell\Billing\Models\ChargeModel;
use tylercubell\Billing\Models\MetadataModel;

trait ChargeTrait
{
    /**
     * Create charge.
     *
     * @see https://stripe.com/docs/api?lang=php#create_charge
     *
     * @param array $arguments
     * @return array|boolean
     */
    public function createCharge(array $arguments)
    {
        try {
            $charge = \Stripe\Charge::create($arguments);

            $result = $this->localUpdateOrCreateCharge($charge);
            event(new BillingEvent('billing.charge.create', $result));
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
     * Retrieve charge.
     *
     * @see https://stripe.com/docs/api?lang=php#retrieve_charge
     *
     * @param string $chargeId
     * @param bool $sync
     * @return array
     */
    public function retrieveCharge(string $chargeId, bool $sync = false)
    {
        if ($sync) {
            try {
                $charge = \Stripe\Charge::retrieve($chargeId);
                return $this->localUpdateOrCreateCharge($charge);
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

        $charge = ChargeModel::where('id', '=', $chargeId)->first();

        return ($charge !== null) ? $charge->toArray() : null;
    }

    /**
     * Update charge.
     *
     * @see https://stripe.com/docs/api?lang=php#update_charge
     *
     * @param string $chargeId
     * @param array $arguments
     * @return array|boolean
     */
    public function updateCharge(string $chargeId, array $arguments)
    {
        try {
            $charge = \Stripe\Charge::retrieve($chargeId);

            foreach ($arguments as $key => $value) {
                $charge->{$key} = $value;
            }

            $update = $charge->save();

            $result = $this->localUpdateOrCreateCharge($update);
            event(new BillingEvent('billing.charge.update', $result));
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
     * Capture charge.
     *
     * @see https://stripe.com/docs/api?lang=php#capture_charge
     *
     * @param string $chargeId
     * @return array|boolean
     */
    public function captureCharge(string $chargeId)
    {
        try {
            $charge = \Stripe\Charge::retrieve($chargeId);
            $capture = $charge->capture();
            
            $result = $this->localUpdateOrCreateCharge($capture);
            event(new BillingEvent('billing.charge.capture', $result));
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
     * List all previously created charges.
     *
     * @see https://stripe.com/docs/api?lang=php#list_charges
     *
     * @param array $arguments
     * @param bool $sync
     * @return array
     */
    public function listAllCharges(array $arguments = [], bool $sync = false)
    {
        if ($sync) {
            try {
                $hasMore = true;
                $arguments = array_merge($arguments, ['limit' => 100]);

                while ($hasMore) {
                    $charges = \Stripe\Charge::all($arguments);

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

        $data = new ChargeModel;

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

        if (isset($arguments['source'])) {
            $data = $data->whereIn('source', '=', $arguments['source']);
        }

        if (isset($arguments['transfer_group'])) {
            $data = $data->whereIn('transfer_group', '=', $arguments['transfer_group']);
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
            'url'         => '/v1/charges'
        ];
    }

    /**
     * Update or create charge locally.
     *
     * @param $charge
     * @param string $changeId
     * @return array
     */
    public function localUpdateOrCreateCharge($charge, string $changeId = null)
    {
        $changeId = $changeId ? $changeId : $this->generateId();

        // Update or create charge
        $result = ChargeModel::updateOrCreate(
            ['id' => $charge->id],
            [
                'amount'                       => $charge->amount,
                'amount_refunded'              => $charge->amount_refunded,
                'application'                  => $charge->application,
                'application_fee'              => $charge->application_fee,
                'balance_transaction'          => $charge->balance_transaction,
                'captured'                     => $charge->captured,
                'created'                      => $charge->created,
                'currency'                     => $charge->currency,
                'customer'                     => $charge->customer,
                'description'                  => $charge->description,
                'destination'                  => $charge->destination,
                'dispute'                      => $charge->dispute,
                'failure_code'                 => $charge->failure_code,
                'failure_message'              => $charge->failure_message,
                'fraud_details_user_report'    => isset($charge->fraud_details->user_report)
                                                  ? $charge->fraud_details->user_report : null,
                'fraud_details_stripe_report'  => isset($charge->fraud_details->stripe_report)
                                                  ? $charge->fraud_details->stripe_report : null,
                'invoice'                      => $charge->invoice,
                'livemode'                     => $charge->livemode,
                'on_behalf_of'                 => $charge->on_behalf_of,
                'order'                        => $charge->order,
                'outcome_network_status'       => isset($charge->outcome->network_status)
                                                  ? $charge->outcome->network_status : null,
                'outcome_reason'               => isset($charge->outcome->reason)
                                                  ? $charge->outcome->reason : null,
                'outcome_risk_level'           => isset($charge->outcome->risk_level)
                                                  ? $charge->outcome->risk_level : null,
                'outcome_rule'                 => isset($charge->outcome->rule)
                                                  ? $charge->outcome->rule : null,
                'outcome_seller_message'       => isset($charge->outcome->seller_message)
                                                  ? $charge->outcome->seller_message : null,
                'outcome_type'                 => isset($charge->outcome->rule->type)
                                                  ? $charge->outcome->rule->type : null,
                'paid'                         => $charge->paid,
                'receipt_email'                => $charge->receipt_email,
                'refunded'                     => $charge->refunded,
                'review'                       => $charge->review,
                'shipping_address_city'        => isset($charge->shipping->address->city)
                                                  ? $charge->shipping->address->city : null,
                'shipping_address_country'     => isset($charge->shipping->address->country)
                                                  ? $charge->shipping->address->country : null,
                'shipping_address_line1'       => isset($charge->shipping->address->line1)
                                                  ? $charge->shipping->address->line1 : null,
                'shipping_address_line2'       => isset($charge->shipping->address->line2)
                                                  ? $charge->shipping->address->line2 : null,
                'shipping_address_postal_code' => isset($charge->shipping->address->postal_code)
                                                  ? $charge->shipping->address->postal_code : null,
                'shipping_address_state'       => isset($charge->shipping->address_state)
                                                  ? $charge->shipping->address_state : null,
                'shipping_carrier'             => isset($charge->shipping->carrier)
                                                  ? $charge->shipping->carrier : null,
                'shipping_name'                => isset($charge->shipping->name)
                                                  ? $charge->shipping->name : null,
                'shipping_phone'               => isset($charge->shipping->phone)
                                                  ? $charge->shipping->phone : null,
                'shipping_tracking_number'     => isset($charge->shipping->tracking_number)
                                                  ? $charge->shipping->tracking_number : null,
                'source'                       => isset($charge->source->id)
                                                  ? $charge->source->id : null,
                'source_transfer'              => $charge->source_transfer,
                'statement_descriptor'         => $charge->statement_descriptor,
                'status'                       => $charge->status,
                'transfer'                     => isset($charge->transfer)
                                                  ? $charge->transfer : null,
                'transfer_group'               => isset($charge->transfer_group)
                                                  ? $charge->transfer_group : null,
                'change_id'                    => $changeId,
                'sync_id'                      => $this->syncId()
            ]
        );

        // Update or create charge metadata
        if ( ! empty($charge->metadata)) {
            foreach ($charge->metadata as $key => $value) {
                MetadataModel::updateOrCreate(
                    ['stripe_id' => $charge->id],
                    [
                        'key'       => $key,
                        'value'     => $value,
                        'type'      => 'charge',
                        'change_id' => $changeId,
                        'sync_id'   => $this->syncId()
                    ]
                );
            }
        }

        // Delete charge metadata that doesn't exist in Stripe
        MetadataModel::where('type', '=', 'charge')
                     ->where('stripe_id', '=', $charge->id)
                     ->where('change_id', '<>', $changeId)
                     ->delete();

        return $result->toArray();
    }
}
