<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // See: https://stripe.com/docs/api?lang=php#card_object
        Schema::create('billing_cards', function (Blueprint $table) {
            // Stripe card ID
            $table->string('id');
            $table->primary('id');

            // Skipping 'object' field because it's redundant

            $table->string('account')->nullable();
            $table->string('address_city')->nullable();
            $table->string('address_country')->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line1_check')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('address_state')->nullable();
            $table->string('address_zip')->nullable();
            $table->string('address_zip_check')->nullable();

            // Taking 'available_payout_methods' list field and turn it into multiple fields
            $table->boolean('available_payout_methods_standard')->nullable();
            $table->boolean('available_payout_methods_instant')->nullable();

            $table->string('brand')->nullable();
            $table->string('country')->nullable();
            $table->string('currency')->nullable();

            $table->string('customer')->nullable();
            $table->index('customer');

            $table->string('cvc_check')->nullable();
            $table->boolean('default_for_currency')->nullable();
            $table->string('dynamic_last4')->nullable();
            $table->integer('exp_month')->nullable();
            $table->integer('exp_year')->nullable();
            $table->string('fingerprint')->nullable();
            $table->string('funding')->nullable();
            $table->string('last4')->nullable();

            // Skipping 'metadata' field because this information is stored in a separate table

            $table->string('name')->nullable();
            $table->string('recipient')->nullable();

            // Skipping 'three_d_secure' because I have no idea who needs this

            $table->string('tokenization_method')->nullable();

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
        Schema::drop('billing_cards');
    }
}
