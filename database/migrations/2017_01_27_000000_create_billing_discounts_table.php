<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // See: https://stripe.com/docs/api?lang=php#discount_object
        Schema::create('billing_discounts', function (Blueprint $table) {
            // Skipping 'object' field because it's redundant

            // Use generic primary key to avoid Postgres bug
            // https://github.com/laravel/framework/issues/17578
            $table->increments('id');

            // 'coupon' field lists 'coupon id'
            // coupon information is stored in a separate table
            $table->string('coupon')->nullable();

            $table->string('customer')->nullable();
            $table->index('customer');

            $table->integer('end')->nullable();
            $table->integer('start')->nullable();

            $table->string('subscription')->nullable();
            $table->index('subscription');

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
        Schema::drop('billing_discounts');
    }
}
