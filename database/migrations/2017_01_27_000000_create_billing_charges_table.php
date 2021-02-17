<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingChargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // See: https://stripe.com/docs/api?lang=php#charge_object
        Schema::create('billing_charges', function (Blueprint $table) {
            // Stripe charge ID
            $table->string('id');
            $table->primary('id');

            // Skipping 'object' field because it's redundant

            $table->integer('amount')->nullable();
            $table->integer('amount_refunded')->nullable();
            $table->string('application')->nullable();
            $table->string('application_fee')->nullable();
            $table->string('balance_transaction')->nullable();
            $table->boolean('captured')->nullable();
            $table->integer('created')->nullable();
            $table->string('currency')->nullable();

            $table->string('customer')->nullable();
            $table->index('customer');

            $table->string('description')->nullable();
            $table->string('destination')->nullable();
            $table->string('dispute')->nullable();
            $table->string('failure_code')->nullable();
            $table->string('failure_message')->nullable();

            // Taking 'fraud_details' field and turning it into multiple fields
            $table->string('fraud_details_user_report')->nullable();
            $table->string('fraud_details_stripe_report')->nullable();

            $table->string('invoice')->nullable();
            $table->boolean('livemode')->nullable();

            // Skipping 'metadata' field because this information is stored in a separate table

            $table->string('on_behalf_of')->nullable();
            $table->string('order')->nullable();

            // Taking 'outcome' field and turning it into multiple fields
            $table->string('outcome_network_status')->nullable();
            $table->string('outcome_reason')->nullable();
            $table->string('outcome_risk_level')->nullable();
            $table->string('outcome_rule')->nullable();
            $table->string('outcome_seller_message')->nullable();
            $table->string('outcome_type')->nullable();

            $table->boolean('paid')->nullable();
            $table->string('receipt_email')->nullable();
            $table->boolean('refunded')->nullable();

            // Skipping 'refunds' field because this information is stored in a separate table

            $table->string('review')->nullable();

            // Taking 'shipping' field and turning it into multiple fields

            // Taking 'address' subfield (from 'shipping') and turning it into multiple fields
            $table->string('shipping_address_city')->nullable();
            $table->string('shipping_address_country')->nullable();
            $table->string('shipping_address_line1')->nullable();
            $table->string('shipping_address_line2')->nullable();
            $table->string('shipping_address_postal_code')->nullable();
            $table->string('shipping_address_state')->nullable();

            $table->string('shipping_carrier')->nullable();
            $table->string('shipping_name')->nullable();
            $table->string('shipping_phone')->nullable();
            $table->string('shipping_tracking_number')->nullable();

            // Taking only source ID (meaning Card ID)
            $table->string('source')->nullable();

            $table->string('source_transfer')->nullable();
            $table->string('statement_descriptor')->nullable();
            $table->string('status')->nullable();
            $table->string('transfer')->nullable();
            $table->string('transfer_group')->nullable();

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
        Schema::drop('billing_charges');
    }
}
