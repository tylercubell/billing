<?php

namespace tylercubell\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use tylercubell\Billing\Models\MetadataModel;

class DisputeModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'billing_disputes';

    /**
     * The primary key for the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Non-incrementing primary key.
     *
     * @var boolean
     */
    public $incrementing = false;

    /**
     * Disable Eloquent timestamps.
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Make all attributes mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'change_id',
        'sync_id',
        'access_activity_log',
        'billing_address',
        'cancellation_policy',
        'cancellation_policy_disclosure',
        'cancellation_rebuttal',
        'customer_communication',
        'customer_email_address',
        'customer_name',
        'customer_purchase_ip',
        'customer_signature',
        'duplicate_charge_documentation',
        'duplicate_charge_explanation',
        'duplicate_charge_id',
        'product_description',
        'receipt',
        'refund_policy',
        'refund_policy_disclosure',
        'refund_refusal_explanation',
        'service_date',
        'service_documentation',
        'shipping_address',
        'shipping_carrier',
        'shipping_date',
        'shipping_documentation',
        'shipping_tracking_number',
        'uncategorized_file',
        'uncategorized_text',
        'due_by',
        'has_evidence',
        'past_due',
        'submission_count'
    ];

    /**
     * Append attributes.
     *
     * @var array
     */
    protected $appends = [
        'metadata',
        'object',
        'evidence',
        'evidence_details',
        'currency_symbol',
        'formatted_amount'
    ];

    /**
     * Metadata attribute.
     *
     * @return array
     */
    public function getMetadataAttribute()
    {
        return MetadataModel::retrieve('dispute', $this->id);
    }

    /**
     * Object attribute.
     *
     * @return string
     */
    public function getObjectAttribute()
    {
        return 'dispute';
    }

    /**
     * Evidence attribute.
     *
     * @return array
     */
    public function getEvidenceAttribute()
    {
        return [
            'access_activity_log'            => $this->access_activity_log,
            'billing_address'                => $this->billing_address,
            'cancellation_policy'            => $this->cancellation_policy,
            'cancellation_policy_disclosure' => $this->cancellation_policy_disclosure,
            'cancellation_rebuttal'          => $this->cancellation_rebuttal,
            'customer_communication'         => $this->customer_communication,
            'customer_email_address'         => $this->customer_email_address,
            'customer_name'                  => $this->customer_name,
            'customer_purchase_ip'           => $this->customer_purchase_ip,
            'customer_signature'             => $this->customer_signature,
            'duplicate_charge_documentation' => $this->duplicate_charge_documentation,
            'duplicate_charge_explanation'   => $this->duplicate_charge_explanation,
            'duplicate_charge_id'            => $this->duplicate_charge_id,
            'product_description'            => $this->product_description,
            'receipt'                        => $this->receipt,
            'refund_policy'                  => $this->refund_policy,
            'refund_policy_disclosure'       => $this->refund_policy_disclosure,
            'refund_refusal_explanation'     => $this->refund_refusal_explanation,
            'service_date'                   => $this->service_date,
            'service_documentation'          => $this->service_documentation,
            'shipping_address'               => $this->shipping_address,
            'shipping_carrier'               => $this->shipping_carrier,
            'shipping_date'                  => $this->shipping_date,
            'shipping_documentation'         => $this->shipping_documentation,
            'shipping_tracking_number'       => $this->shipping_tracking_number,
            'uncategorized_file'             => $this->uncategorized_file,
            'uncategorized_text'             => $this->uncategorized_text
        ];
    }

    /**
     * Evidence details attribute.
     *
     * @return array
     */
    public function getEvidenceDetailsAttribute()
    {
        return [
            'due_by'           => $this->due_by,
            'has_evidence'     => $this->has_evidence,
            'past_due'         => $this->past_due,
            'submission_count' => $this->submission_count
        ];
    }

    /**
     * Currency symbol attribute.
     *
     * @return string
     */
    public function getCurrencySymbolAttribute()
    {
        return \Symfony\Component\Intl\Intl::getCurrencyBundle()
                                           ->getCurrencySymbol(strtoupper($this->currency));
    }

    /**
     * Formatted amount attribute.
     *
     * @return string
     */
    public function getFormattedAmountOffAttribute()
    {
        // Stripe amounts are in cents
        return number_format($this->amount / 100, 2);
    }
}
