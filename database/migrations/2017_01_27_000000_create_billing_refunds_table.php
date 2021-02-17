<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // See: https://stripe.com/docs/api?lang=php#refund_object
        Schema::create('billing_refunds', function (Blueprint $table) {
            // Stripe refund ID
            $table->string('id');
            $table->primary('id');

            // Skipping 'object' field because it's redundant

            $table->integer('amount')->nullable();
            $table->string('balance_transaction')->nullable();

            $table->string('charge')->nullable();
            $table->index('charge');

            $table->integer('created')->nullable();
            $table->string('currency')->nullable();
            $table->string('description')->nullable();

            // Skipping 'metadata' field because this information is stored in a separate table

            $table->string('reason')->nullable();
            $table->string('receipt_number')->nullable();
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
        Schema::drop('billing_refunds');
    }
}
