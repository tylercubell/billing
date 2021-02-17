<?php

namespace tylercubell\Billing\Traits;

use tylercubell\Billing\Exceptions\BillingException;
use tylercubell\Billing\Events\BillingEvent;
use tylercubell\Billing\Models\LineItemModel;
use tylercubell\Billing\Models\MetadataModel;

trait LineItemTrait
{
    /**
     * Retrieve an invoice's line items.
     *
     * @see https://stripe.com/docs/api?lang=php#invoice_lines
     *
     * @param string $invoiceId
     * @param bool $sync
     * @return array
     */
    public function retrieveInvoiceLineItems(string $invoiceId, bool $sync = false)
    {
        if ($sync) {
            try {
                $lineItemsHasMore = true;
                $lineItemsArguments = ['limit' => 100];

                while ($lineItemsHasMore) {
                    $lineItems = \Stripe\Invoice::retrieve($invoiceId)->lines->all($lineItemsArguments);

                    foreach ($lineItems->data as $lineItem) {
                        $this->localUpdateOrCreateLineItem($lineItem, $invoiceId);
                    }

                    if ( ! $lineItems->has_more) {
                        $lineItemsHasMore = false;
                    } else {
                        $lastInvoiceLineItem = end($lineItems->data);
                        $lineItemsArguments['starting_after'] = $lastInvoiceLineItem->id;
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

        return LineItemModel::where('invoice', '=', $invoiceId)
                            ->get()
                            ->toArray();
    }

    /**
     * Update or create (invoice) line item locally.
     *
     * @param $lineItem
     * @param string $invoiceId
     * @param string $changeId
     * @return array
     */
    public function localUpdateOrCreateLineItem($lineItem, string $invoiceId, string $changeId = null)
    {
        $changeId = $changeId ? $changeId : $this->generateId();

        // Update or create invoice line item
        $result = LineItemModel::updateOrCreate(
            ['id' => $lineItem->id],
            [
                'invoice'             => $invoiceId,
                'amount'              => $lineItem->amount,
                'currency'            => $lineItem->currency,
                'description'         => $lineItem->description,
                'discountable'        => $lineItem->discountable,
                'livemode'            => $lineItem->livemode,
                'period_start'        => $lineItem->period->start,
                'period_end'          => $lineItem->period->end,
                'plan'                => $lineItem->plan->id,
                'proration'           => $lineItem->proration,
                'quantity'            => $lineItem->quantity,
                'subscription'        => $lineItem->subscription,
                'subscription_item'   => $lineItem->subscription_item,
                'type'                => $lineItem->type,
                'change_id'           => $changeId,
                'sync_id'             => $this->syncId()
            ]
        );

        // Update or create invoice line item metadata
        if ( ! empty($lineItem->metadata)) {
            foreach ($lineItem->metadata as $key => $value) {
                MetadataModel::updateOrCreate(
                    ['stripe_id' => $lineItem->id],
                    [
                        'key'       => $key,
                        'value'     => $value,
                        'type'      => 'invoice_line_item',
                        'change_id' => $changeId,
                        'sync_id'   => $this->syncId()
                    ]
                );
            }
        }

        // Delete invoice line item metadata that doesn't exist in Stripe
        MetadataModel::where('type', '=', 'invoice_line_item')
                     ->where('stripe_id', '=', $lineItem->id)
                     ->where('change_id', '<>', $changeId)
                     ->delete();

        return $result->toArray();
    }
}
