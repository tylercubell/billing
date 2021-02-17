<?php

namespace tylercubell\Billing\Traits;

use tylercubell\Billing\Exceptions\BillingException;
use tylercubell\Billing\Events\BillingEvent;
use tylercubell\Billing\Models\InvoiceModel;
use tylercubell\Billing\Models\LineItemModel;
use tylercubell\Billing\Models\MetadataModel;

trait InvoiceTrait
{
    /**
     * Create invoice.
     *
     * @see https://stripe.com/docs/api?lang=php#create_invoice
     *
     * @param array $arguments
     * @return array|boolean
     */
    public function createInvoice(array $arguments)
    {
        try {
            $invoice = \Stripe\Invoice::create($arguments);

            $result = $this->localUpdateOrCreateInvoice($invoice);
            event(new BillingEvent('billing.invoice.create', $result));
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
     * Retrieve invoice.
     *
     * @see https://stripe.com/docs/api?lang=php#retrieve_invoice
     *
     * @param string $invoiceId
     * @param bool $sync
     * @return array
     */
    public function retrieveInvoice(string $invoiceId, bool $sync = false)
    {
        if ($sync) {
            try {
                $invoice = \Stripe\Invoice::retrieve($invoiceId);
                return $this->localUpdateOrCreateInvoice($invoice);
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

        $invoice = InvoiceModel::where('id', '=', $invoiceId)->first();

        return ($invoice !== null) ? $invoice->toArray() : null;
    }

    /**
     * Retrieve an upcoming invoice.
     *
     * Exception: This information is not stored locally.
     *
     * @see https://stripe.com/docs/api?lang=php#upcoming_invoice
     *
     * @param array $arguments
     * @return array
     */
    public function retrieveUpcomingInvoice(array $arguments)
    {
        return \Stripe\Invoice::upcoming($arguments)->__toArray(true);
    }

    /**
     * Update an invoice.
     *
     * @see https://stripe.com/docs/api?lang=php#update_invoice
     *
     * @param string $invoiceId
     * @param array $arguments
     * @return array|boolean
     */
    public function updateInvoice(string $invoiceId, array $arguments)
    {
        try {
            $invoice = \Stripe\Invoice::retrieve($invoiceId);

            foreach ($arguments as $key => $value) {
                $invoice->{$key} = $value;
            }

            $update = $invoice->save();

            $result = $this->localUpdateOrCreateInvoice($update);
            event(new BillingEvent('billing.invoice.update', $result));
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
     * Pay an invoice.
     *
     * @see https://stripe.com/docs/api?lang=php#pay_invoice
     *
     * @param string $invoiceId
     * @return array|boolean
     */
    public function payInvoice(string $invoiceId)
    {
        try {
            $invoice = \Stripe\Invoice::retrieve($invoiceId);
            $pay = $invoice->pay();
            
            $result = $this->localUpdateOrCreateInvoice($pay);
            event(new BillingEvent('billing.invoice.pay', $result));
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
     * List all invoices.
     *
     * @see https://stripe.com/docs/api?lang=php#list_invoices
     *
     * @param array $arguments
     * @param bool $sync
     * @return array
     */
    public function listAllInvoices(array $arguments = [], bool $sync = false)
    {
        if ($sync) {
            try {
                $hasMore = true;
                $arguments = array_merge($arguments, ['limit' => 100]);

                while ($hasMore) {
                    $invoices = \Stripe\Invoice::all($arguments);

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

        $data = new InvoiceModel;

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
            'url'         => '/v1/invoices'
        ];
    }

    /**
     * Update or create invoice locally.
     *
     * Exception: Needs to call Stripe API if invoice has over 10 line items.
     *
     * @param $invoice
     * @param string $changeId
     * @return array
     */
    public function localUpdateOrCreateInvoice($invoice, string $changeId = null)
    {
        $changeId = $changeId ? $changeId : $this->generateId();

        // Update or create invoice
        $result = InvoiceModel::updateOrCreate(
            ['id' => $invoice->id],
            [
                'amount_due'                  => $invoice->amount_due,
                'application_fee'             => $invoice->application_fee,
                'attempt_count'               => $invoice->attempt_count,
                'attempted'                   => $invoice->attempted,
                'charge'                      => $invoice->charge,
                'closed'                      => $invoice->closed,
                'currency'                    => $invoice->currency,
                'customer'                    => $invoice->customer,
                'date'                        => $invoice->date,
                'description'                 => $invoice->description,
                'ending_balance'              => $invoice->ending_balance,
                'forgiven'                    => $invoice->forgiven,
                'livemode'                    => $invoice->livemode,
                'next_payment_attempt'        => $invoice->next_payment_attempt,
                'paid'                        => $invoice->paid,
                'period_end'                  => $invoice->period_end,
                'period_start'                => $invoice->period_start,
                'receipt_number'              => $invoice->receipt_number,
                'starting_balance'            => $invoice->starting_balance,
                'statement_descriptor'        => $invoice->statement_descriptor,
                'subscription'                => $invoice->subscription,
                'subscription_proration_date' => isset($invoice->subscription_proration_date)
                                                 ? $invoice->subscription_proration_date : null,
                'subtotal'                    => $invoice->subtotal,
                'tax'                         => $invoice->tax,
                'tax_percent'                 => $invoice->tax_percent,
                'total'                       => $invoice->total,
                'webhooks_delivered_at'       => $invoice->webhooks_delivered_at,
                'change_id'                   => $changeId,
                'sync_id'                     => $this->syncId()
            ]
        );

        // Update or create invoice line items
        if ( ! empty($invoice->lines->data)) {
            // Invoice has less than or equal to 10 invoice line items so no need for extra API calls
            if ($invoice->lines->has_more === false) {
                foreach ($invoice->lines->data as $lineItem) {
                    // Check instance because lines array also includes a subscription object at the end
                    if ($lineItem instanceof \Stripe\LineItem) {
                        $this->localUpdateOrCreateLineItem($lineItem, $invoice->id, $changeId);
                    }
                }
            // Invoice has over 10 invoice line items so we need to make multiple requests
            } else {
                $lineItemsHasMore = true;
                $lineItemsArguments = ['limit' => 100];

                while ($lineItemsHasMore) {
                    $lineItems = \Stripe\Invoice::retrieve($invoice->id)->lines->all($lineItemsArguments);

                    foreach ($lineItems->data as $lineItem) {
                        $this->localUpdateOrCreateLineItem($lineItem, $invoice->id, $changeId);
                    }

                    if ( ! $lineItems->has_more) {
                        $lineItemsHasMore = false;
                    } else {
                        $lastInvoiceLineItem = end($lineItems->data);
                        $lineItemsArguments['starting_after'] = $lastInvoiceLineItem->id;
                    }
                }
            }
        }

        // Delete invoice line items that don't exist for an invoice in Stripe

        // In order to not leave invoice line item metadata that doesn't exist 
        // in Stripe, check each out of sync invoice line item for metadata 
        // and delete it if found.
        $outOfSyncInvoiceLineItems = LineItemModel::where('invoice', '=', $invoice->id)
                                                  ->where('change_id', '<>', $changeId);

        foreach ($outOfSyncInvoiceLineItems->get() as $outOfSyncInvoiceLineItem) {
            MetadataModel::where('type', '=', 'invoice_line_item')
                         ->where('stripe_id', '=', $outOfSyncInvoiceLineItem->id)
                         ->delete();
        }

        // Finally, delete out of sync invoice line items
        $outOfSyncInvoiceLineItems->delete();

        // Update or create invoice metadata
        if ( ! empty($invoice->metadata)) {
            foreach ($invoice->metadata as $key => $value) {
                MetadataModel::updateOrCreate(
                    ['stripe_id' => $invoice->id],
                    [
                        'key'       => $key,
                        'value'     => $value,
                        'type'      => 'invoice',
                        'change_id' => $changeId,
                        'sync_id'   => $this->syncId()
                    ]
                );
            }
        }

        // Delete invoice metadata that doesn't exist in Stripe
        MetadataModel::where('type', '=', 'invoice')
                     ->where('stripe_id', '=', $invoice->id)
                     ->where('change_id', '<>', $changeId)
                     ->delete();

        return $result->toArray();
    }
}
