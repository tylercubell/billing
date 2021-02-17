<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // See: https://stripe.com/docs/api?lang=php#subscription_object
        Schema::create('billing_subscriptions', function (Blueprint $table) {
            // Stripe subscription ID
            $table->string('id');
            $table->primary('id');

            // Skipping 'object' field because it's redundant

            // Todo: determine what this decimal should exactly be
            $table->decimal('application_fee_percent', 5, 2)->nullable();

            $table->boolean('cancel_at_period_end')->nullable();
            $table->integer('canceled_at')->nullable();
            $table->integer('created')->nullable();
            $table->integer('current_period_end')->nullable();
            $table->integer('current_period_start')->nullable();

            $table->string('customer')->nullable();
            $table->index('customer');

            // Skipping 'discount' field because this information is stored in a separate table

            $table->integer('ended_at')->nullable();

            // Skipping 'items' field because this information is stored in a separate table

            $table->boolean('livemode')->nullable();

            // Skipping 'metadata' field because this information is stored in a separate table

            // 'plan' field lists 'plan id' stored in a separate table
            $table->string('plan')->nullable();

            $table->integer('quantity')->nullable();
            $table->integer('start')->nullable();
            $table->string('status')->nullable();

            // Todo: determine what this decimal should exactly be
            $table->decimal('tax_percent', 5, 2)->nullable();

            $table->integer('trial_end')->nullable();
            $table->integer('trial_start')->nullable();

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
        Schema::drop('billing_subscriptions');
    }
}
