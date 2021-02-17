<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingSubscriptionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // See: https://stripe.com/docs/api?lang=php#subscription_item_object
        Schema::create('billing_subscription_items', function (Blueprint $table) {
            // Stripe subscription item ID
            $table->string('id');
            $table->primary('id');

            // Doesn't exist in Stripe API but this is needed to link
            // subscription item to "parent" subscription
            $table->string('subscription')->nullable();
            $table->index('subscription');

            // Skipping 'object' field because it's redundant

            $table->integer('created')->nullable();

            // 'plan' field lists 'plan id'
            // plan information stored in a separate table
            $table->string('plan')->nullable();

            $table->integer('quantity')->nullable();

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
        Schema::drop('billing_subscription_items');
    }
}
