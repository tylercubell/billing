<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingMetadataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_metadata', function (Blueprint $table) {
            // Use generic primary key to avoid Postgres bug
            // https://github.com/laravel/framework/issues/17578
            $table->increments('id');

            // Stripe object ID
            $table->string('stripe_id');
            $table->index('stripe_id');

            $table->string('key')->nullable();
            $table->index('key');

            $table->string('value')->nullable();

            // Stripe object type
            $table->string('type')->nullable();
            $table->index('type');

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
        Schema::drop('billing_metadata');
    }
}
