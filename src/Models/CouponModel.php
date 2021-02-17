<?php

namespace tylercubell\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use tylercubell\Billing\Models\MetadataModel;

class CouponModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'billing_coupons';

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
    protected $hidden = ['change_id', 'sync_id'];

    /**
     * Append attributes.
     *
     * @var array
     */
    protected $appends = [
        'metadata',
        'currency_symbol',
        'formatted_amount_off',
        'terms',
        'object'
    ];

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
     * Formatted amount off attribute.
     *
     * @return string
     */
    public function getFormattedAmountOffAttribute()
    {
        // Stripe amounts are in cents
        return number_format($this->amount_off / 100, 2);
    }

    /**
     * Terms attribute.
     *
     * @return void
     */
    public function getTermsAttribute()
    {
        $text = '';

        if ($this->amount_off !== null) {
            $text .= $this->currency_symbol . $this->formatted_amount_off . ' off';
        } elseif ($this->percent_off !== null) {
            $text .= $this->percent_off . '% off';
        }

        if ($this->duration === 'forever') {
            $text .= ' forever';
        } elseif ($this->duration === 'once') {
            $text .= ' once';
        } elseif ($this->duration === 'repeating') {
            $text .= ' for ' . $this->duration_in_months . ' months';
        }

        return $text;
    }

    /**
     * Metadata attribute.
     *
     * @return array
     */
    public function getMetadataAttribute()
    {
        return MetadataModel::retrieve('coupon', $this->id);
    }

    /**
     * Object attribute.
     *
     * @return string
     */
    public function getObjectAttribute()
    {
        return 'coupon';
    }
}
