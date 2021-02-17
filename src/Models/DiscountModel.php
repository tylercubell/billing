<?php

namespace tylercubell\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use tylercubell\Billing\Models\CouponModel;

class DiscountModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'billing_discounts';

    /**
     * The primary key for the table.
     *
     * No primary key because discounts can either be applied to customers or subscriptions.
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
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['change_id', 'sync_id'];

    /**
     * Make all attributes mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Append attributes.
     *
     * @var array
     */
    protected $appends = ['object'];

    /**
     * Object attribute.
     *
     * @return string
     */
    public function getObjectAttribute()
    {
        return 'discount';
    }

    /**
     * Object attribute.
     *
     * @return array
     */
    public function getCouponAttribute($value)
    {
        $coupon = CouponModel::where('id', '=', $value)->first();
        return ($coupon !== null) ? $coupon->toArray() : null;
    }
}
