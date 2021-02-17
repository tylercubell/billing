<?php

namespace tylercubell\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use tylercubell\Billing\Models\MetadataModel;
use tylercubell\Billing\Models\RefundModel;
use tylercubell\Billing\Models\CardModel;

class ChargeModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'billing_charges';

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
        'fraud_details_user_report',
        'fraud_details_stripe_report',
        'outcome_network_status',
        'outcome_reason',
        'outcome_risk_level',
        'outcome_rule',
        'outcome_seller_message',
        'outcome_type',
        'shipping_address_city',
        'shipping_address_country',
        'shipping_address_line1',
        'shipping_address_line2',
        'shipping_address_postal_code',
        'shipping_address_state',
        'shipping_carrier',
        'shipping_name',
        'shipping_phone',
        'shipping_tracking_number'
    ];

    /**
     * Append attributes.
     *
     * @var array
     */
    protected $appends = [
        'metadata',
        'object',
        'fraud_details',
        'outcome',
        'refunds',
        'shipping',
        'formatted_amount',
        'currency_symbol',
        'formatted_amount_refunded'
    ];

    /**
     * Metadata attribute.
     *
     * @return array
     */
    public function getMetadataAttribute()
    {
        return MetadataModel::retrieve('charge', $this->id);
    }

    /**
     * Object attribute.
     *
     * @return string
     */
    public function getObjectAttribute()
    {
        return 'charge';
    }

    /**
     * Fraud details attribute.
     *
     * @return array
     */
    public function getFraudDetailsAttribute()
    {
        $fraudDetails = [];

        if ($this->fraud_details_user_report !== null) {
            $fraudDetails['user_report'] = $this->fraud_details_user_report;
        }

        if ($this->fraud_details_stripe_report !== null) {
            $fraudDetails['stripe_report'] = $this->fraud_details_stripe_report;
        }

        return $fraudDetails;
    }

    /**
     * Outcome attribute.
     *
     * @return array
     */
    public function getOutcomeAttribute()
    {
        return [
            'network_status' => $this->outcome_network_status,
            'reason'         => $this->outcome_reason,
            'risk_level'     => $this->outcome_risk_level,
            'seller_message' => $this->outcome_seller_message,
            'type'           => $this->outcome_type
        ];
    }

    /**
     * Refunds attribute.
     *
     * @return array
     */
    public function getRefundsAttribute()
    {
        $data = RefundModel::where('charge', '=', $this->id)->get()->toArray();

        return [
            'object'      => 'list',
            'data'        => $data,
            'has_more'    => false,
            'total_count' => count($data),
            'url'         => '/v1/charges/' . $this->id . '/refunds'
        ];
    }

    /**
     * Shipping attribute.
     *
     * @return void
     */
    public function getShippingAttribute()
    {
        // It follows that other attributes would also be null if shipping address city is null.
        if ($this->shipping_address_city === null) {
            return null;
        } else {
            return [
                'address' => [
                    'city'        => $this->shipping_address_city,
                    'country'     => $this->shipping_address_country,
                    'line1'       => $this->shipping_address_line1,
                    'line2'       => $this->shipping_address_line2,
                    'postal_code' => $this->shipping_address_postal_code,
                    'state'       => $this->shipping_address_state
                ],
                'name'            => $this->shipping_name,
                'phone'           => $this->shipping_phone,
                'tracking_number' => $this->shipping_tracking_number
            ];
        }
    }

    /**
     * Source attribute.
     *
     * @return void
     */
    public function getSourceAttribute($value)
    {
        $card = CardModel::where('id', '=', $value)->first();
        return ($card) ? $card : null;
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
    public function getFormattedAmountAttribute()
    {
        // Stripe amounts are in cents
        return number_format($this->amount / 100, 2);
    }

    /**
     * Formatted amount refunded attribute.
     *
     * @return string
     */
    public function getFormattedAmountRefundedAttribute()
    {
        // Stripe amounts are in cents
        return number_format($this->amount_refunded / 100, 2);
    }
}
