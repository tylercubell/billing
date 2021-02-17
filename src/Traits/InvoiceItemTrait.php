<?php

namespace tylercubell\Billing\Traits;

use tylercubell\Billing\Exceptions\BillingException;
use tylercubell\Billing\Events\BillingEvent;
use tylercubell\Billing\Models\InvoiceItemModel;
use tylercubell\Billing\Models\MetadataModel;

trait InvoiceItemTrait
{
    /**
     * Create invoice item.
     *
     * @see https://stripe.com/docs/api?lang=php#create_invoiceitem
     *
     * @param array $arguments
     * @return array|boolean
     */
    public function createInvoiceItem(array $arguments)
    {
        try {
            $invoiceItem = \Stripe\InvoiceItem::create($arguments);
            
            $result = $this->localUpdateOrCreateInvoiceItem($invoiceItem);
            event(new BillingEvent('billing.invoice_item.create', $result));
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
     * Retrieve invoice item.
     *
     * @see https://stripe.com/docs/api?lang=php#retrieve_invoiceitem
     *
     * @param string $invoiceId
     * @param bool $sync
     * @return array
     */
    public function retrieveInvoiceItem(string $invoiceItemId, bool $sync = false)
    {
        if ($sync) {
            try {
                $invoiceItem = \Stripe\InvoiceItem::retrieve($invoiceItemId);
                return $this->localUpdateOrCreateInvoiceItem($invoiceItem);
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

        $invoiceItem = InvoiceItemModel::where('id', '=', $invoiceItemId)->first();

        return ($invoiceItem !== null) ? $invoiceItem->toArray() : null;
    }

    /**
     * Update an invoice item.
     *
     * @see https://stripe.com/docs/api?lang=php#update_invoiceitem
     *
     * @param string $invoiceItemId
     * @param array $arguments
     * @return array|boolean
     */
    public function updateInvoiceItem(string $invoiceItemId, array $arguments)
    {
        try {
            $invoiceItem = \Stripe\InvoiceItem::retrieve($invoiceItemId);

            foreach ($arguments as $key => $value) {
                $invoiceItem->{$key} = $value;
            }

            $update = $invoiceItem->save();

            $result = $this->localUpdateOrCreateInvoiceItem($update);
            event(new BillingEvent('billing.invoice_item.update', $result));
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
     * Delete invoice item.
     *
     * @see https://stripe.com/docs/api?lang=php#delete_invoiceitem
     *
     * @param string $invoiceItemId
     * @return array|boolean
     */
    public function deleteInvoiceItem(string $invoiceItemId)
    {
        try {
            $invoiceItem = \Stripe\InvoiceItem::retrieve($invoiceItemId);
            $delete = $invoiceItem->delete();

            $result = $this->localDeleteInvoiceItem($delete);
            event(new BillingEvent('billing.invoice_item.delete', $result));
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
     * List all invoice items.
     *
     * @see https://stripe.com/docs/api?lang=php#list_invoiceitems
     *
     * @param array $arguments
     * @param bool $sync
     * @return array
     */
    public function listAllInvoiceItems(array $arguments = [], bool $sync = false)
    {
        if ($sync) {
            try {
                $hasMore = true;
                $arguments = array_merge($arguments, ['limit' => 100]);

                while ($hasMore) {
                    $invoiceItems = \Stripe\InvoiceItem::all($arguments);

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

        $data = new InvoiceItemModel;

        // Note: invoice item object doesn't have a 'created' field so 'date' is substituted
        // for local queries.
        if (isset($arguments['date'])) {
            if (isset($arguments['date']['gt'])) {
                $data = $data->where('date', '>', $arguments['date']['gt']);
            }

            if (isset($arguments['date']['gte'])) {
                $data = $data->where('date', '>=', $arguments['date']['gte']);
            }

            if (isset($arguments['date']['lt'])) {
                $data = $data->where('date', '<', $arguments['date']['lt']);
            }

            if (isset($arguments['date']['lte'])) {
                $data = $data->where('date', '<=', $arguments['date']['lte']);
            }
        }

        if (isset($arguments['customer'])) {
            $data = $data->where('customer', '=', $arguments['customer']);
        }

        if (isset($arguments['limit'])) {
            $data = $data->limit($arguments['limit']);
        }

        $data = $data->orderBy('date', 'desc')->get()->toArray();

        return [
            'object'      => 'list',
            'data'        => $data,
            'has_more'    => false,
            'total_count' => count($data),
            'url'         => '/v1/invoiceitems'
        ];
    }

    /**
     * Update or create invoice item locally.
     *
     * @param $invoiceItem
     * @param string $changeId
     * @return array
     */
    public function localUpdateOrCreateInvoiceItem($invoiceItem, string $changeId = null)
    {
        $changeId = $changeId ? $changeId : $this->generateId();

        $result = InvoiceItemModel::updateOrCreate(
            ['id' => $invoiceItem->id],
            [
                'amount'            => $invoiceItem->amount,
                'currency'          => $invoiceItem->currency,
                'customer'          => $invoiceItem->customer,
                'date'              => $invoiceItem->date,
                'description'       => $invoiceItem->description,
                'discountable'      => $invoiceItem->discountable,
                'invoice'           => $invoiceItem->invoice,
                'livemode'          => $invoiceItem->livemode,
                'period_start'      => $invoiceItem->period->start,
                'period_end'        => $invoiceItem->period->end,
                'plan'              => isset($invoiceItem->plan->id)
                                       ? $invoiceItem->plan->id : null,
                'proration'         => $invoiceItem->proration,
                'quantity'          => $invoiceItem->quantity,
                'subscription'      => $invoiceItem->subscription,
                'subscription_item' => $invoiceItem->subscription_item,
                'change_id'         => $changeId,
                'sync_id'           => $this->syncId()
            ]
        );

        // Update or create invoice item metadata
        if ( ! empty($invoiceItem->metadata)) {
            foreach ($invoiceItem->metadata as $key => $value) {
                MetadataModel::updateOrCreate(
                    ['stripe_id' => $invoiceItem->id],
                    [
                        'key'       => $key,
                        'value'     => $value,
                        'type'      => 'invoice_item',
                        'change_id' => $changeId,
                        'sync_id'   => $this->syncId()
                    ]
                );
            }
        }

        // Delete invoice item metadata that doesn't exist in Stripe
        MetadataModel::where('type', '=', 'invoice_item')
                     ->where('stripe_id', '=', $invoiceItem->id)
                     ->where('change_id', '<>', $changeId)
                     ->delete();

        return $result->toArray();
    }

    /**
     * Delete invoice item locally.
     *
     * @param $invoiceItem
     * @return array
     */
    public function localDeleteInvoiceItem($invoiceItem)
    {
        $result = InvoiceItemModel::where('id', '=', $invoiceItem->id)->first();

        InvoiceItemModel::where('id', '=', $invoiceItem->id)->delete();

        MetadataModel::where('type', '=', 'invoice_item')
                     ->where('stripe_id', '=', $invoiceItem->id)
                     ->delete();

        return $result;
    }
}
