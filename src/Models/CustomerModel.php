<?php

namespace tylercubell\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use tylercubell\Billing\Models\DiscountModel;
use tylercubell\Billing\Models\MetadataModel;
use tylercubell\Billing\Models\CardModel;
use tylercubell\Billing\Models\SubscriptionModel;

class CustomerModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'billing_customers';

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
        'shipping_address_city',
        'shipping_address_country',
        'shipping_address_line1',
        'shipping_address_line2',
        'shipping_address_postal_code',
        'shipping_address_state',
        'shipping_name',
        'shipping_phone'
    ];

    /**
     * Append attributes.
     *
     * @var array
     */
    protected $appends = [
        'discount',
        'shipping',
        'sources',
        'subscriptions',
        'metadata',
        'object',
        'formatted_account_balance'
    ];

    /**
     * Discount attribute.
     *
     * @return void
     */
    public function getDiscountAttribute()
    {
        $discount = DiscountModel::where('customer', $this->id)->first();
        return ($discount !== null) ? $discount->toArray() : null;
    }

    /**
     * Metadata attribute.
     *
     * @return void
     */
    public function getMetadataAttribute()
    {
        return MetadataModel::retrieve('customer', $this->id);
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
                'name'  => $this->shipping_name,
                'phone' => $this->shipping_phone
            ];
        }
    }

    /**
     * Sources attribute.
     *
     * @return void
     */
    public function getSourcesAttribute()
    {
        $data = CardModel::where('customer', '=', $this->id)->get()->toArray();

        return [
            'object'      => 'list',
            'data'        => $data,
            'has_more'    => false,
            'total_count' => count($data),
            'url'         => '/v1/customers/' . $this->id . '/sources'
        ];
    }

    /**
     * Subscriptions attribute.
     *
     * @return void
     */
    public function getSubscriptionsAttribute()
    {
        $data = SubscriptionModel::where('customer', '=', $this->id)->get()->toArray();

        return [
            'object'      => 'list',
            'data'        => $data,
            'has_more'    => false,
            'total_count' => count($data),
            'url'         => '/v1/customers/' . $this->id . '/subscriptions'
        ];
    }

    /**
     * Object attribute.
     *
     * @return string
     */
    public function getObjectAttribute()
    {
        return 'customer';
    }

    /**
     * Formatted account balance attribute.
     *
     * @return string
     */
    public function getFormattedAccountBalanceAttribute()
    {
        // Stripe amounts are in cents
        return number_format($this->account_balance / 100, 2);
    }
}
