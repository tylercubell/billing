<?php

namespace tylercubell\Billing\Traits;

use tylercubell\Billing\Exceptions\BillingException;
use tylercubell\Billing\Events\BillingEvent;
use tylercubell\Billing\Models\CardModel;
use tylercubell\Billing\Models\CustomerModel;
use tylercubell\Billing\Models\DiscountModel;
use tylercubell\Billing\Models\MetadataModel;

trait CustomerTrait
{
    /**
     * Create customer.
     *
     * @see https://stripe.com/docs/api?lang=php#create_customer
     *
     * @param array $arguments
     * @return array|boolean
     */
    public function createCustomer(array $arguments = [])
    {
        try {
            // Temporarily remove 'user_id' for creating a customer in Stripe.
            // 'user_id' links Stripe customer to user.
            if (isset($arguments['user_id'])) {
                $userId = $arguments['user_id'];
                unset($arguments['user_id']);
            }

            $customer = \Stripe\Customer::create($arguments);

            // Bring it back for creating customer locally.
            if (isset($userId)) {
                $customer->user_id = $userId;
            }

            $result = $this->localUpdateOrCreateCustomer($customer);
            event(new BillingEvent('billing.customer.create', $result));
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
     * Retrieve customer.
     *
     * @see https://stripe.com/docs/api?lang=php#retrieve_customer
     *
     * @param string $customerId
     * @param bool $sync
     * @return array
     */
    public function retrieveCustomer(string $customerId, bool $sync = false)
    {
        if ($sync) {
            try {
                $customer = \Stripe\Customer::retrieve($customerId);
                return $this->localUpdateOrCreateCustomer($customer);
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

        $customer = CustomerModel::where('id', '=', $customerId)->first();

        return ($customer !== null) ? $customer->toArray() : null;
    }

    /**
     * Retrieve customer by user ID.
     *
     * @param int $userId
     * @return array
     */
    public function retrieveCustomerByUserId(int $userId)
    {
        $customer = CustomerModel::where('user_id', '=', $userId)->first();
        return ($customer !== null) ? $customer->toArray() : null;
    }


    /**
     * Update customer.
     *
     * @see https://stripe.com/docs/api?lang=php#update_customer
     *
     * @param string $id
     * @param array $arguments
     * @return array|boolean
     */
    public function updateCustomer(string $customerId, array $arguments)
    {
        try {
            $customer = \Stripe\Customer::retrieve($customerId);

            foreach ($arguments as $key => $value) {
                $customer->{$key} = $value;
            }

            $update = $customer->save();

            $result = $this->localUpdateOrCreateCustomer($update);
            event(new BillingEvent('billing.customer.update', $result));
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
     * Delete customer.
     *
     * @see https://stripe.com/docs/api?lang=php#delete_customer
     *
     * @param string $customerId
     * @return array|boolean
     */
    public function deleteCustomer(string $customerId)
    {
        try {
            $customer = \Stripe\Customer::retrieve($customerId);
            $delete = $customer->delete();

            $result = $this->localDeleteCustomer($delete);
            event(new BillingEvent('billing.customer.delete', $result));
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
     * List all customers.
     *
     * @see https://stripe.com/docs/api?lang=php#list_customers
     *
     * @param array $arguments
     * @param bool $sync
     * @return array
     */
    public function listAllCustomers(array $arguments = [], bool $sync = false)
    {
        if ($sync) {
            try {
                $hasMore = true;
                $arguments = array_merge($arguments, ['limit' => 100]);

                while ($hasMore) {
                    $customers = \Stripe\Customer::all($arguments);

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

        $data = new CustomerModel;

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

        if (isset($arguments['limit'])) {
            $data = $data->limit($arguments['limit']);
        }

        $data = $data->orderBy('created', 'desc')->get()->toArray();

        return [
            'object'      => 'list',
            'data'        => $data,
            'has_more'    => false,
            'total_count' => count($data),
            'url'         => '/v1/customers'
        ];
    }

    /**
     * Update or create customer locally.
     *
     * Exception: Needs to call Stripe API if customer has over 10 sources.
     *
     * @param $customer
     * @param string $changeId
     * @return array
     */
    public function localUpdateOrCreateCustomer($customer, $changeId = null)
    {
        $changeId = $changeId ? $changeId : $this->generateId();

        $fields = [
            'account_balance'              => $customer->account_balance,
            'business_vat_id'              => isset($customer->business_vat_id)
                                              ? $customer->business_vat_id : null,
            'created'                      => $customer->created,
            'currency'                     => $customer->currency,
            'default_source'               => $customer->default_source,
            'delinquent'                   => $customer->delinquent,
            'description'                  => $customer->description,
            'email'                        => $customer->email,
            'livemode'                     => $customer->livemode,
            'shipping_address_city'        => isset($customer->shipping->address->city)
                                              ? $customer->shipping->address->city : null,
            'shipping_address_country'     => isset($customer->shipping->address->country)
                                              ? $customer->shipping->address->country : null,
            'shipping_address_line1'       => isset($customer->shipping->address->line1)
                                              ? $customer->shipping->address->line1 : null,
            'shipping_address_line2'       => isset($customer->shipping->address->line2)
                                              ? $customer->shipping->address->line2 : null,
            'shipping_address_postal_code' => isset($customer->shipping->address->postal_code)
                                              ? $customer->shipping->address->postal_code : null,
            'shipping_address_state'       => isset($customer->shipping->address->state)
                                              ? $customer->shipping->address->state : null,
            'shipping_name'                => isset($customer->shipping->name)
                                              ? $customer->shipping->name : null,
            'shipping_phone'               => isset($customer->shipping->phone)
                                              ? $customer->shipping->phone: null,
            'change_id'                    => $changeId,
            'sync_id'                      => $this->syncId()
        ];

        // Just for creating a customer in order to link Stripe customer to user
        if (isset($customer->user_id)) {
            $fields['user_id'] = $customer->user_id;
        }

        // Update or create customer
        $result = CustomerModel::updateOrCreate(
            ['id' => $customer->id],
            $fields
        );

        // Update or create customer metadata
        if ( ! empty($customer->metadata)) {
            foreach ($customer->metadata as $key => $value) {
                MetadataModel::updateOrCreate(
                    ['stripe_id' => $customer->id],
                    [
                        'key'       => $key,
                        'value'     => $value,
                        'type'      => 'customer',
                        'change_id' => $changeId,
                        'sync_id'   => $this->syncId()
                    ]
                );
            }
        }

        // Delete customer metadata that doesn't exist in Stripe
        MetadataModel::where('type', '=', 'customer')
                     ->where('stripe_id', '=', $customer->id)
                     ->where('change_id', '<>', $changeId)
                     ->delete();

        // Update or create customer discount
        if ($customer->discount !== null) {
            $this->localUpdateOrCreateDiscount($customer->discount);
        }

        // Update or create customer cards
        if ( ! empty($customer->sources->data)) {
            // Customer has less than or equal to 10 sources so no need for extra API calls
            // See https://stripe.com/docs/api?lang=php#list_cards
            if ($customer->sources->has_more === false) {
                foreach ($customer->sources->data as $card) {
                    // We don't care about BitcoinReceiver objects so just call the source a card
                    // and check to make sure.
                    if ($card->object === 'card') {
                        $this->localUpdateOrCreateCard($card, $changeId);
                    }
                }
            // Customer has over 10 sources so we need to make multiple requests
            } else {
                $cardsHasMore = true;
                $cardsArguments = ['limit' => 100];

                while ($cardsHasMore) {
                    $cards = \Stripe\Customer::retrieve($customer->id)->sources->all($cardsArguments);

                    foreach ($cards->data as $card) {
                        $this->localUpdateOrCreateCard($card, $changeId);
                    }

                    if ( ! $cards->has_more) {
                        $cardsHasMore = false;
                    } else {
                        $lastCard = end($cards->data);
                        $cardsArguments['starting_after'] = $lastCard->id;
                    }
                }
            }
        }

        // Delete customer cards that don't exist in Stripe

        // In order to not leave customer card metadata that doesn't exist in Stripe, 
        // check each out of sync customer card for metadata and delete it if found.
        $outOfSyncCards = CardModel::where('customer', '=', $customer->id)
                                   ->where('change_id', '<>', $changeId);

        foreach ($outOfSyncCards->get() as $outOfSyncCard) {
            MetadataModel::where('type', '=', 'card')
                         ->where('stripe_id', '=', $outOfSyncCard->id)
                         ->delete();
        }

        // Finally, delete out of sync cards
        $outOfSyncCards->delete();

        return $result->toArray();
    }

    /**
     * Delete customer locally.
     *
     * @param $customer
     * @return array
     */
    public function localDeleteCustomer($customer)
    {
        $result = CustomerModel::where('id', $customer->id)->first();

        CustomerModel::where('id', $customer->id)->delete();

        MetadataModel::where('type', '=', 'customer')
                     ->where('stripe_id', '=', $customer->id)
                     ->delete();

        DiscountModel::where('customer', '=', $customer->id)->delete();

        $cards = CardModel::where('customer', '=', $customer->id);

        foreach ($cards->get() as $card) {
            MetadataModel::where('type', '=', 'card')
                         ->where('stripe_id', '=', $card->id)
                         ->delete();
        }

        $cards->delete();

        return $result;
    }
}
