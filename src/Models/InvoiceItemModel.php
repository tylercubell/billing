<?php

namespace tylercubell\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use tylercubell\Billing\Models\MetadataModel;
use tylercubell\Billing\Models\PlanModel;

class InvoiceItemModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'billing_invoice_items';

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
        'period_start',
        'period_end'
    ];

    /**
     * Append attributes.
     *
     * @var array
     */
    protected $appends = [
        'metadata',
        'object',
        'period',
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
        return MetadataModel::retrieve('invoice_item', $this->id);
    }

    /**
     * Object attribute.
     *
     * @return string
     */
    public function getObjectAttribute()
    {
        return 'invoice_item';
    }

    /**
     * Period attribute.
     *
     * @return array
     */
    public function getPeriodAttribute()
    {
        return [
            'start' => $this->period_start,
            'end'   => $this->period_end
        ];
    }

    /**
     * Plan attribute.
     *
     * @return array
     */
    public function getPlanAttribute($value)
    {
        $plan = PlanModel::where('id', '=', $value)->first();
        return ($plan !== null) ? $plan->toArray() : null;
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
