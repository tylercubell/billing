<?php

namespace tylercubell\Billing\Traits;

use tylercubell\Billing\Exceptions\BillingException;
use tylercubell\Billing\Events\BillingEvent;
use tylercubell\Billing\Models\CardModel;
use tylercubell\Billing\Models\MetadataModel;

trait CardTrait
{
    /**
     * Create card.
     *
     * @see https://stripe.com/docs/api?lang=php#create_card
     *
     * @param string $customerId
     * @param array $arguments
     * @return array|boolean
     */
    public function createCard(string $customerId, array $arguments)
    {
        try {
            $customer = \Stripe\Customer::retrieve($customerId);
            $card = $customer->sources->create($arguments);

            $result = $this->localUpdateOrCreateCard($card);
            event(new BillingEvent('billing.card.create', $result));

            // Sync customer object because it might have changed
            $this->retrieveCustomer($customerId, true);

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
     * Retrieve card.
     *
     * @see https://stripe.com/docs/api?lang=php#retrieve_card
     *
     * @param string $customerId
     * @param string $cardId
     * @param bool $sync
     * @return array|boolean
     */
    public function retrieveCard(string $customerId, string $cardId, bool $sync = false)
    {
        if ($sync) {
            try {
                $customer = \Stripe\Customer::retrieve($customerId);
                $card = $customer->sources->retrieve($cardId);
                return $this->localUpdateOrCreateCard($card);
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

        $card = CardModel::where('customer', '=', $customerId)
                         ->where('id', '=', $cardId)
                         ->first();

        return ($card !== null) ? $card->toArray() : null;
    }

    /**
     * Update card.
     *
     * @see https://stripe.com/docs/api?lang=php#update_card
     *
     * @param string $customerId
     * @param string $cardId
     * @param array $arguments
     * @return array|boolean
     */
    public function updateCard(string $customerId, string $cardId, array $arguments)
    {
        try {
            $customer = \Stripe\Customer::retrieve($customerId);
            $card = $customer->sources->retrieve($cardId);

            foreach ($arguments as $key => $value) {
                $card->{$key} = $value;
            }

            $update = $card->save();

            $result = $this->localUpdateOrCreateCard($update);
            event(new BillingEvent('billing.card.update', $result));

            // Sync customer object because it might have changed
            $this->retrieveCustomer($customerId, true);

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
     * Delete card.
     *
     * @see https://stripe.com/docs/api?lang=php#delete_card
     *
     * @param string $customerId
     * @param string $cardId
     * @return array|boolean
     */
    public function deleteCard(string $customerId, string $cardId)
    {
        try {
            $customer = \Stripe\Customer::retrieve($customerId);
            $card = $customer->sources->retrieve($cardId);
            $card->delete();

            $result = $this->localDeleteCard($card);
            event(new BillingEvent('billing.card.delete', $result));

            // Sync customer object because it might have changed
            $this->retrieveCustomer($customerId, true);

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
     * List all cards belonging to a customer.
     *
     * @see https://stripe.com/docs/api?lang=php#list_cards
     *
     * @param string $customerId
     * @param array $arguments
     * @param bool $sync
     * @return array
     */
    public function listAllCards(string $customerId, array $arguments = [], bool $sync = false)
    {
        if ($sync) {
            try {
                $hasMore = true;
                $arguments = array_merge($arguments, [
                    'object' => 'card',
                    'limit' => 100
                ]);

                while ($hasMore) {
                    $cards = \Stripe\Customer::retrieve($customerId)->sources->all($arguments);

                    foreach ($cards->data as $card) {
                        $this->localUpdateOrCreateCard($card);
                    }

                    if ( ! $cards->has_more) {
                        $hasMore = false;
                    } else {
                        $lastCard = end($cards->data);
                        $arguments['starting_after'] = $lastCard->id;
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

        $data = CardModel::where('customer', '=', $customerId);

        if (isset($arguments['limit'])) {
            $data = $data->limit($arguments['limit']);
        }

        // Note: no field available to sort cards by created date
        $data = $data->get()->toArray();

        return [
            'object'      => 'list',
            'data'        => $data,
            'has_more'    => false,
            'total_count' => count($data),
            'url'         => '/v1/customers/' . $customerId . '/sources'
        ];
    }

    /**
     * Update or create card locally.
     *
     * @param $card
     * @param string $changeId
     * @return array
     */
    public function localUpdateOrCreateCard($card, $changeId = null)
    {
        $changeId = $changeId ? $changeId : $this->generateId();

        // Update or create card
        $result = CardModel::updateOrCreate(
            ['id' => $card->id],
            [
                'account'                           => isset($card->account)
                                                       ? $card->account : null,
                'address_city'                      => $card->address_city,
                'address_country'                   => $card->address_country,
                'address_line1'                     => $card->address_line1,
                'address_line1_check'               => $card->address_line1_check,
                'address_line2'                     => $card->address_line2,
                'address_state'                     => $card->address_state,
                'address_zip'                       => $card->address_zip,
                'address_zip_check'                 => $card->address_zip_check,
                'available_payout_methods_standard' => isset($card->available_payout_methods['standard'])
                                                       ? $card->available_payout_methods['standard'] : null,
                'available_payout_methods_instant'  => isset($card->available_payout_methods['instant'])
                                                       ? $card->available_payout_methods['instant'] : null,
                'brand'                             => $card->brand,
                'country'                           => $card->country,
                'currency'                          => isset($card->currency)
                                                       ? $card->currency : null,
                'customer'                          => $card->customer,
                'cvc_check'                         => $card->cvc_check,
                'default_for_currency'              => isset($card->default_for_currency)
                                                       ? $card->default_for_currency : null,
                'dynamic_last4'                     => $card->dynamic_last4,
                'exp_month'                         => $card->exp_month,
                'exp_year'                          => $card->exp_year,
                'fingerprint'                       => $card->fingerprint,
                'funding'                           => $card->funding,
                'last4'                             => $card->last4,
                'name'                              => $card->name,
                'recipient'                         => isset($card->recipient)
                                                       ? $card->recipient : null,
                'tokenization_method'               => $card->tokenization_method,
                'change_id'                         => $changeId,
                'sync_id'                           => $this->syncId()
            ]
        );

        // Update or create card metadata
        if ( ! empty($card->metadata)) {
            foreach ($card->metadata as $key => $value) {
                MetadataModel::updateOrCreate(
                    ['stripe_id' => $card->id],
                    [
                        'key'       => $key,
                        'value'     => $value,
                        'type'      => 'card',
                        'change_id' => $changeId,
                        'sync_id'   => $this->syncId()
                    ]
                );
            }
        }

        // Delete card metadata that doesn't exist in Stripe
        MetadataModel::where('type', '=', 'card')
                     ->where('stripe_id', '=', $card->id)
                     ->where('change_id', '<>', $changeId)
                     ->delete();

        return $result->toArray();
    }

    /**
     * Delete card locally.
     *
     * @param $card
     * @return void
     */
    public function localDeleteCard($card)
    {
        $result = CardModel::where('customer', '=', $card->customer)->first();

        CardModel::where('customer', '=', $card->customer)->delete();

        MetadataModel::where('type', '=', 'card')
                     ->where('stripe_id', '=', $card->id)
                     ->delete();

        return $result;
    }
}
