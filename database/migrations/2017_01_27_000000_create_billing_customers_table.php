<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // See: https://stripe.com/docs/api?lang=php#customer_object
        Schema::create('billing_customers', function (Blueprint $table) {
            // Laravel user account ID
            $table->integer('user_id')->nullable();
            $table->index('user_id');

            // Stripe customer ID
            $table->string('id');
            $table->primary('id');

            // Skipping 'object' field because it's redundant

            $table->integer('account_balance')->nullable();
            $table->string('business_vat_id')->nullable();
            $table->integer('created')->nullable();
            $table->string('currency')->nullable();
            $table->string('default_source')->nullable();
            $table->boolean('delinquent')->nullable();
            $table->string('description')->nullable();

            // Skipping 'discount' field because this information is stored in a separate table

            $table->string('email')->nullable();
            $table->index('email');

            $table->boolean('livemode')->nullable();

            // Skipping 'metadata' field because this information is stored in a separate table

            // Taking 'shipping' field and turn it into multiple fields instead of creating separate table
            $table->string('shipping_address_city')->nullable();
            $table->string('shipping_address_country')->nullable();
            $table->string('shipping_address_line1')->nullable();
            $table->string('shipping_address_line2')->nullable();
            $table->string('shipping_address_postal_code')->nullable();
            $table->string('shipping_address_state')->nullable();
            $table->string('shipping_name')->nullable();
            $table->string('shipping_phone')->nullable();

            // Skipping 'sources' field because this information is stored in a separate table

            // Skipping 'subscriptions' field because this information is stored in a separate table

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
        Schema::drop('billing_customers');
    }
}
