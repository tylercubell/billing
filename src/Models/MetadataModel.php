<?php

namespace tylercubell\Billing\Models;

use Illuminate\Database\Eloquent\Model;

class MetadataModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'billing_metadata';

    /**
     * The primary key for the table.
     *
     * No primary key because objects can have multiple keys.
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
     * Retrieve metadata as an associative array.
     *
     * @param string $type
     * @param string $stripeId
     * @return array
     */
    public static function retrieve($type, $stripeId)
    {
        $metadataItems = self::where('type', '=', $type)
                             ->where('stripe_id', '=', $stripeId)
                             ->select('key', 'value')
                             ->get();

        $results = [];

        foreach ($metadataItems as $metadataItem) {
            $results[$metadataItem['key']] = $metadataItem['value'];
        }

        return $results;
    }
}
