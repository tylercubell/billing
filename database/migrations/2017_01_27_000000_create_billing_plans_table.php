<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // See: https://stripe.com/docs/api?lang=php#plan_object
        Schema::create('billing_plans', function (Blueprint $table) {
            // Stripe coupon ID
            $table->string('id');
            $table->primary('id');

            // Skipping 'object' field because it's redundant

            $table->integer('amount')->nullable();
            $table->integer('created')->nullable();
            $table->string('currency')->nullable();
            $table->string('interval')->nullable();
            $table->integer('interval_count')->nullable();
            $table->boolean('livemode')->nullable();

            // Skipping 'metadata' field because this information is stored in a separate table

            $table->string('name')->nullable();
            $table->string('statement_descriptor')->nullable();
            $table->integer('trial_period_days')->nullable();

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
        Schema::drop('billing_plans');
    }
}
