<?php

namespace tylercubell\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use tylercubell\Billing\Models\PlanModel;

class SubscriptionItemModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'billing_subscription_items';

    /**
     * The primary key for the table.
     *
     * @var string
     */
    protected $primaryKey = 'subscription';

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
        'object',
        'subscription'
    ];

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
        return 'subscription_item';
    }
}
