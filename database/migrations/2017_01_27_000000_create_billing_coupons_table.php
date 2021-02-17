<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // See: https://stripe.com/docs/api?lang=php#coupon_object
        Schema::create('billing_coupons', function (Blueprint $table) {
            // Stripe coupon ID
            $table->string('id');
            $table->primary('id');

            // Skipping 'object' field because it's redundant

            $table->integer('amount_off')->nullable();
            $table->integer('created')->nullable();
            $table->string('currency')->nullable();
            $table->string('duration')->nullable();
            $table->integer('duration_in_months')->nullable();
            $table->boolean('livemode')->nullable();
            $table->integer('max_redemptions')->nullable();

            // Skipping 'metadata' field because this information is stored in a separate table

            $table->integer('percent_off')->nullable();
            $table->integer('redeem_by')->nullable();
            $table->integer('times_redeemed')->nullable();
            $table->boolean('valid')->nullable();

            // Keeps track of specific changed records
            $table->string('change_id')->nullable();
            $table->index('change_id');

            // Keeps track of all changed records for a sync
            $table->string('sync_id')->nullable();
            $table->index('sync_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('billing_coupons');
    }
}
