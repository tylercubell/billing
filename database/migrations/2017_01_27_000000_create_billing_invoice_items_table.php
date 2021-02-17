<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingInvoiceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // See: https://stripe.com/docs/api?lang=php#invoiceitem_object
        Schema::create('billing_invoice_items', function (Blueprint $table) {
            // Stripe invoice item ID
            $table->string('id')->nullable();
            $table->primary('id');

            $table->string('amount')->nullable();
            $table->string('currency')->nullable();

            $table->string('customer');
            $table->index('customer');

            $table->integer('date')->nullable();
            $table->string('description')->nullable();
            $table->boolean('discountable')->nullable();

            $table->string('invoice')->nullable();
            $table->index('invoice');

            $table->boolean('livemode')->nullable();

            // Skipping 'metadata' field because this information is stored in another table

            // Taking 'period' field and turning it into multiple fields
            $table->integer('period_start')->nullable();
            $table->integer('period_end')->nullable();

            // 'plan' field lists 'plan id'
            // plan information stored in a separate table
            $table->string('plan')->nullable();

            $table->boolean('proration')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('subscription')->nullable();
            $table->string('subscription_item')->nullable();

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
        Schema::drop('billing_invoice_items');
    }
}
