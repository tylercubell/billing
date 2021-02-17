<?php

namespace tylercubell\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use tylercubell\Billing\Models\DiscountModel;
use tylercubell\Billing\Models\SubscriptionItemModel;
use tylercubell\Billing\Models\MetadataModel;
use tylercubell\Billing\Models\PlanModel;
use Carbon\Carbon;

class SubscriptionModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'billing_subscriptions';

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
        'discount',
        'metadata',
        'items',
        'object',
        'on_grace_period',
        'trial_days_left',
        'trial_start_date',
        'trial_end_date'
    ];

    /**
     * Discount attribute.
     *
     * @return void
     */
    public function getDiscountAttribute()
    {
        $discount = DiscountModel::where('subscription', $this->id)->first();
        return ($discount !== null) ? $discount->toArray() : null;
    }

    /**
     * Items attribute.
     *
     * @return void
     */
    public function getItemsAttribute()
    {
        $data = SubscriptionItemModel::where('subscription', '=', $this->id)->get()->toArray();

        return [
            'object' => 'list',
            'data' => $data,
            'has_more' => false,
            'total_count' => count($data),
            'url' => '/v1/subscription_items?subscription=' . $this->id
        ];
    }

    /**
     * Metadata attribute.
     *
     * @return array
     */
    public function getMetadataAttribute()
    {
        return MetadataModel::retrieve('subscription', $this->id);
    }

    /**
     * Get the plan object instead of returning the plan ID.
     *
     * @param string $value
     * @return void
     */
    public function getPlanAttribute($value)
    {
        $plan = PlanModel::where('id', '=', $value)->first();
        return ($plan !== null) ? $plan->toArray() : null;
    }

    /**
     * Object attribute.
     *
     * @return string
     */
    public function getObjectAttribute()
    {
        return 'subscription';
    }

    /**
     * On grace period attribute.
     *
     * @return string
     */
    public function getOnGracePeriodAttribute()
    {
        if ($this->ends_at !== null) {
            return time() < $this->ends_at;
        } else {
            return null;
        }
    }

    /**
     * Trial days left attribute.
     *
     * @return string
     */
    public function getTrialDaysLeftAttribute()
    {
        if ($this->status === 'trialing' && $this->trial_end !== null) {
            return Carbon::createFromTimestamp($this->trial_end)->diffInDays(Carbon::now());
        } else {
            return null;
        }
    }

    /**
     * Trial start date attribute.
     *
     * @return string
     */
    public function getTrialStartDateAttribute()
    {
        if ($this->status === 'trialing' && $this->trial_start !== null) {
            return Carbon::createFromTimestamp($this->trial_start)->format('n/j/Y');
        } else {
            return null;
        }
    }

    /**
     * Trial end date attribute.
     *
     * @return string
     */
    public function getTrialEndDateAttribute()
    {
        if ($this->status === 'trialing' && $this->trial_end !== null) {
            return Carbon::createFromTimestamp($this->trial_end)->format('n/j/Y');
        } else {
            return null;
        }
    }
}
