<?php

namespace tylercubell\Billing\Traits;

use tylercubell\Billing\Exceptions\BillingException;
use tylercubell\Billing\Events\BillingEvent;
use tylercubell\Billing\Models\DisputeModel;
use tylercubell\Billing\Models\MetadataModel;

trait DisputeTrait
{
    /**
     * Retrieve a dispute.
     *
     * @see https://stripe.com/docs/api?lang=php#retrieve_dispute
     *
     * @param string $disputeId
     * @param bool $sync
     * @return array
     */
    public function retrieveDispute(string $disputeId, bool $sync = false)
    {
        if ($sync) {
            try {
                $dispute = \Stripe\Dispute::retrieve($disputeId);
                return $this->localUpdateOrCreateDispute($dispute);
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

        $dispute = DisputeModel::where('id', '=', $disputeId)->first();

        return ($dispute !== null) ? $dispute->toArray() : null;
    }

    /**
     * Update a dispute.
     *
     * @see https://stripe.com/docs/api?lang=php#update_dispute
     *
     * @param string $disputeId
     * @param array $evidence
     * @param array $metadata
     * @return array|boolean
     */
    public function updateDispute(string $disputeId, array $evidence = [], array $metadata = [])
    {
        try {
            $dispute = \Stripe\Dispute::retrieve($disputeId);

            if ( ! empty($evidence)) {
                $dispute->evidence = $evidence;
            }

            if ( ! empty($metadata)) {
                $dispute->metadata = $metadata;
            }
            
            $update = $dispute->save();

            $result = $this->localUpdateOrCreateDispute($update);
            event(new BillingEvent('billing.dispute.update', $result));
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
     * Close a dispute.
     *
     * @see https://stripe.com/docs/api?lang=php#close_dispute
     *
     * @param string $disputeId
     * @return array|boolean
     */
    public function closeDispute(string $disputeId)
    {
        try {
            $dispute = \Stripe\Dispute::retrieve($disputeId);
            $close = $dispute->close();

            $result = $this->localUpdateOrCreateDispute($close);
            event(new BillingEvent('billing.dispute.close', $result));
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
     * List all disputes.
     *
     * @see https://stripe.com/docs/api?lang=php#list_disputes
     *
     * @param array $arguments
     * @param bool $sync
     * @return array
     */
    public function listAllDisputes(array $arguments = [], bool $sync = false)
    {
        if ($sync) {
            try {
                $hasMore = true;
                $arguments = array_merge($arguments, ['limit' => 100]);

                while ($hasMore) {
                    $disputes = \Stripe\Dispute::all($arguments);

                    foreach ($disputes->data as $dispute) {
                        $this->localUpdateOrCreateDispute($dispute);
                    }

                    if ( ! $disputes->has_more) {
                        $hasMore = false;
                    } else {
                        $lastDispute = end($disputes->data);
                        $arguments['starting_after'] = $lastDispute->id;
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

        $data = new DisputeModel;

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
            'url'         => '/v1/disputes'
        ];
    }

    /**
     * Update or create dispute locally.
     *
     * @param $dispute
     * @param string $changeId
     * @return array
     */
    public function localUpdateOrCreateDispute($dispute, string $changeId = null)
    {
        $changeId = $changeId ? $changeId : $this->generateId();

        // Update or create dispute
        $result = DisputeModel::updateOrCreate(
            ['id' => $dispute->id],
            [
                'amount'                         => $dispute->amount,
                'charge'                         => $dispute->charge,
                'created'                        => $dispute->created,
                'currency'                       => $dispute->currency,
                'access_activity_log'            => $dispute->evidence->access_activity_log,
                'billing_address'                => $dispute->evidence->billing_address,
                'cancellation_policy'            => $dispute->evidence->cancellation_policy,
                'cancellation_policy_disclosure' => $dispute->evidence->cancellation_policy_disclosure,
                'cancellation_rebuttal'          => $dispute->evidence->cancellation_rebuttal,
                'customer_communication'         => $dispute->evidence->customer_communication,
                'customer_email_address'         => $dispute->evidence->customer_email_address,
                'customer_name'                  => $dispute->evidence->customer_name,
                'customer_purchase_ip'           => $dispute->evidence->customer_purchase_ip,
                'customer_signature'             => $dispute->evidence->customer_signature,
                'duplicate_charge_documentation' => $dispute->evidence->duplicate_charge_documentation,
                'duplicate_charge_explanation'   => $dispute->evidence->duplicate_charge_explanation,
                'duplicate_charge_id'            => $dispute->evidence->duplicate_charge_id,
                'product_description'            => $dispute->evidence->product_description,
                'receipt'                        => $dispute->evidence->receipt,
                'refund_policy'                  => $dispute->evidence->refund_policy,
                'refund_policy_disclosure'       => $dispute->evidence->refund_policy_disclosure,
                'refund_refusal_explanation'     => $dispute->evidence->refund_refusal_explanation,
                'service_date'                   => $dispute->evidence->service_date,
                'service_documentation'          => $dispute->evidence->service_documentation,
                'shipping_address'               => $dispute->evidence->shipping_address,
                'shipping_carrier'               => $dispute->evidence->shipping_carrier,
                'shipping_date'                  => $dispute->evidence->shipping_date,
                'shipping_documentation'         => $dispute->evidence->shipping_documentation,
                'shipping_tracking_number'       => $dispute->evidence->shipping_tracking_number,
                'uncategorized_file'             => $dispute->evidence->uncategorized_file,
                'uncategorized_text'             => $dispute->evidence->uncategorized_text,
                'due_by'                         => $dispute->evidence_details->due_by,
                'has_evidence'                   => $dispute->evidence_details->has_evidence,
                'past_due'                       => $dispute->evidence_details->past_due,
                'submission_count'               => $dispute->evidence_details->submission_count,
                'is_charge_refundable'           => $dispute->evidence_details->is_charge_refundable,
                'livemode'                       => $dispute->evidence_details->livemode,
                'reason'                         => $dispute->reason,
                'status'                         => $dispute->status,
                'change_id'                      => $changeId,
                'sync_id'                        => $this->syncId()
            ]
        );

        // Update or create dispute metadata
        if ( ! empty($dispute->metadata)) {
            foreach ($dispute->metadata as $key => $value) {
                MetadataModel::updateOrCreate(
                    ['stripe_id' => $dispute->id],
                    [
                        'key'       => $key,
                        'value'     => $value,
                        'type'      => 'dispute',
                        'change_id' => $changeId,
                        'sync_id'   => $this->syncId()
                    ]
                );
            }
        }

        // Delete dispute metadata that doesn't exist in Stripe
        MetadataModel::where('type', '=', 'dispute')
                     ->where('stripe_id', '=', $dispute->id)
                     ->where('change_id', '<>', $changeId)
                     ->delete();

        return $result->toArray();
    }
}
