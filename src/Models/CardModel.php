<?php

namespace tylercubell\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use tylercubell\Billing\Models\MetadataModel;

class CardModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'billing_cards';

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
        'available_payout_methods_standard',
        'available_payout_methods_instant'
    ];

    /**
     * Append attributes.
     *
     * @var array
     */
    protected $appends = ['available_payout_methods', 'metadata', 'object'];

    /**
     * Available payout methods attribute.
     *
     * @return array
     */
    public function getAvailablePayoutMethodsAttribute()
    {
        $available_payout_methods = [];

        if ($this->available_payout_methods_standard !== null) {
            $available_payout_methods[] = 'standard';
        }

        if ($this->available_payout_methods_instant !== null) {
            $available_payout_methods[] = 'instant';
        }

        return $available_payout_methods;
    }

    /**
     * Metadata attribute.
     *
     * @return array
     */
    public function getMetadataAttribute()
    {
        return MetadataModel::retrieve('card', $this->id);
    }

    /**
     * Object attribute.
     *
     * @return string
     */
    public function getObjectAttribute()
    {
        return 'card';
    }
}
