<?php

namespace tylercubell\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use tylercubell\Billing\Models\MetadataModel;
use tylercubell\Billing\Models\DiscountModel;
use tylercubell\Billing\Models\LineItemModel;
use tylercubell\Billing\Models\SubscriptionModel;
use Carbon\Carbon;

class InvoiceModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'billing_invoices';

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
        'discount',
        'lines',
        'currency_symbol',
        'formatted_amount_due',
        'formatted_date',
        'formatted_period_end',
        'formatted_period_start',
        'formatted_subtotal',
        'formatted_tax',
        'for_trial',
        'formatted_application_fee',
        'formatted_ending_balance',
        'formatted_starting_balance',
    ];

    /**
     * Metadata attribute.
     *
     * @return array
     */
    public function getMetadataAttribute()
    {
        return MetadataModel::retrieve('invoice', $this->id);
    }

    /**
     * Object attribute.
     *
     * @return string
     */
    public function getObjectAttribute()
    {
        return 'invoice';
    }

    /**
     * Discount attribute.
     *
     * @return array
     */
    public function getDiscountAttribute()
    {
        // Assuming invoice discount object is the discount for a subscription
        $discount = DiscountModel::where('subscription', '=', $this->subscription)->first();
        return ($discount !== null) ? $discount->toArray() : null;
    }

    /**
     * Lines attribute.
     *
     * @return array
     */
    public function getLinesAttribute()
    {
        // The individual line items that make up the invoice. lines is sorted as follows: 
        // invoice items in reverse chronological order, followed by the subscription, if any.
        $lines = LineItemModel::where('invoice', '=', $this->invoice)
                              ->orderBy('period_end', 'desc')
                              ->get()
                              ->toArray();

        $subscriptions = SubscriptionModel::where('id', '=', $this->subscription)
                                          ->limit(1)
                                          ->get()
                                          ->toArray();

        return [
            'object'      => 'list',
            'data'        => array_merge($lines, $subscriptions),
            'has_more'    => false,
            'total_count' => count($lines) + count($subscriptions),
            'url'         => '/v1/invoices/' . $this->id . '/lines'
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
     * Formatted amount due attribute.
     *
     * @return string
     */
    public function getFormattedAmountDueAttribute()
    {
        // Stripe amounts are in cents
        return number_format($this->amount_due / 100, 2);
    }

    /**
     * Formatted date attribute.
     *
     * @return string
     */
    public function getFormattedDateAttribute()
    {
        return Carbon::createFromTimestamp($this->date)->format('n/j/Y');
    }

    /**
     * Formatted period start attribute.
     *
     * @return string
     */
    public function getFormattedPeriodStartAttribute()
    {
        return Carbon::createFromTimestamp($this->period_start)->format('n/j/Y');
    }

    /**
     * Formatted period end attribute.
     *
     * @return string
     */
    public function getFormattedPeriodEndAttribute()
    {
        return Carbon::createFromTimestamp($this->period_end)->format('n/j/Y');
    }

    /**
     * Formatted subtotal attribute.
     *
     * @return string
     */
    public function getFormattedSubtotalAttribute()
    {
        // Stripe amounts are in cents
        return number_format($this->subtotal / 100, 2);
    }

    /**
     * Formatted tax attribute.
     *
     * @return string
     */
    public function getFormattedTaxAttribute()
    {
        if ($this->tax === null) {
            return null;
        } else {
            // Stripe amounts are in cents
            return number_format($this->tax / 100, 2);
        }
    }

    /**
     * For trial attribute.
     *
     * Check if invoice is created for a subscription trial. It must meet three criteria:
     *
     * 1. The invoice must be created at the same time the subscription trial starts.
     * 2. The amount due must be $0.00.
     * 3. The invoice must be closed.
     *
     * @return string
     */
    public function getForTrialAttribute()
    {
        $subscription = SubscriptionModel::select('created')->where('id', '=', $this->subscription)->first();
        
        if ($subscription === null) {
            return false;
        }

        return ($subscription->created === $this->date) && 
               ($this->amount_due === 0) &&
               ($this->closed === true);
    }

    /**
     * Formatted application fee attribute.
     *
     * @return string
     */
    public function getFormattedApplicationFeeAttribute()
    {
        // Stripe amounts are in cents
        return number_format($this->application_fee / 100, 2);
    }

    /**
     * Formatted ending balance attribute.
     *
     * @return string
     */
    public function getFormattedEndingBalanceAttribute()
    {
        // Stripe amounts are in cents
        return number_format($this->ending_balance / 100, 2);
    }

    /**
     * Formatted starting balance attribute.
     *
     * @return string
     */
    public function getFormattedStartingBalanceAttribute()
    {
        // Stripe amounts are in cents
        return number_format($this->starting_balance / 100, 2);
    }
}
