<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingDisputesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // See: https://stripe.com/docs/api?lang=php#dispute_object
        //      https://stripe.com/docs/api?lang=php#dispute_evidence_object
        Schema::create('billing_disputes', function (Blueprint $table) {
            // Stripe refund ID
            $table->string('id');
            $table->primary('id');

            // Skipping 'object' field because it's redundant

            $table->integer('amount')->nullable();

            // Altogether skipping 'balance_transactions' because it's out of the scope of this project

            $table->string('charge')->nullable();
            $table->index('charge');

            $table->integer('created')->nullable();
            $table->string('currency')->nullable();

            // Taking 'evidence' field (dispute_evidence object) and turning it into multiple fields
            $table->string('access_activity_log')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('cancellation_policy')->nullable();
            $table->string('cancellation_policy_disclosure')->nullable();
            $table->string('cancellation_rebuttal')->nullable();
            $table->string('customer_communication')->nullable();
            $table->string('customer_email_address')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_purchase_ip')->nullable();
            $table->string('customer_signature')->nullable();
            $table->string('duplicate_charge_documentation')->nullable();
            $table->string('duplicate_charge_explanation')->nullable();
            $table->string('duplicate_charge_id')->nullable();
            $table->string('product_description')->nullable();
            $table->string('receipt')->nullable();
            $table->string('refund_policy')->nullable();
            $table->string('refund_policy_disclosure')->nullable();
            $table->string('refund_refusal_explanation')->nullable();
            $table->string('service_date')->nullable();
            $table->string('service_documentation')->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('shipping_carrier')->nullable();
            $table->string('shipping_date')->nullable();
            $table->string('shipping_documentation')->nullable();
            $table->string('shipping_tracking_number')->nullable();
            $table->string('uncategorized_file')->nullable();
            $table->string('uncategorized_text')->nullable();

            // Taking 'evidence_details' field and turning it into multiple fields
            $table->integer('due_by')->nullable();
            $table->boolean('has_evidence')->nullable();
            $table->boolean('past_due')->nullable();
            $table->integer('submission_count')->nullable();

            $table->boolean('is_charge_refundable')->nullable();
            $table->boolean('livemode')->nullable();

            // Skipping 'metadata' field because this information is in another table

            $table->string('reason')->nullable();
            $table->string('status')->nullable();

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
        Schema::drop('billing_disputes');
    }
}
