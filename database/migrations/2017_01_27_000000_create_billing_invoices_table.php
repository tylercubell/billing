<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // See: https://stripe.com/docs/api?lang=php#invoice_object
        Schema::create('billing_invoices', function (Blueprint $table) {
            // Stripe invoice item ID
            $table->string('id');
            $table->primary('id');

            // Skipping 'object' field because it's redundant

            $table->integer('amount_due')->nullable();
            $table->integer('application_fee')->nullable();
            $table->integer('attempt_count')->nullable();
            $table->boolean('attempted')->nullable();
            $table->string('charge')->nullable();
            $table->boolean('closed')->nullable();
            $table->string('currency')->nullable();

            $table->string('customer')->nullable();
            $table->index('customer');

            $table->integer('date')->nullable();
            $table->string('description')->nullable();

            // Skipping 'discount' field because this information is stored in another table

            $table->integer('ending_balance')->nullable();
            $table->boolean('forgiven')->nullable();

            // Skipping 'lines' field because this information is stored in another table

            $table->boolean('livemode')->nullable();

            // Skipping 'metadata' field because this information is stored in another table

            $table->integer('next_payment_attempt')->nullable();
            $table->boolean('paid')->nullable();
            $table->integer('period_end')->nullable();
            $table->integer('period_start')->nullable();
            $table->string('receipt_number')->nullable();
            $table->integer('starting_balance')->nullable();
            $table->string('statement_descriptor')->nullable();
            $table->string('subscription')->nullable();
            $table->integer('subscription_proration_date')->nullable();
            $table->integer('subtotal')->nullable();
            $table->integer('tax')->nullable();

            // Todo: figure out what the exact decimal should be
            $table->decimal('tax_percent', 5, 2)->nullable();

            $table->integer('total')->nullable();
            $table->integer('webhooks_delivered_at')->nullable();

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
        Schema::drop('billing_invoices');
    }
}
