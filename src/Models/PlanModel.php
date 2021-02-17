<?php

namespace tylercubell\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use tylercubell\Billing\Models\MetadataModel;

class PlanModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'billing_plans';

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
        'object',
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
        return MetadataModel::retrieve('plan', $this->id);
    }

    /**
     * Object attribute.
     *
     * @return string
     */
    public function getObjectAttribute()
    {
        return 'plan';
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
}
