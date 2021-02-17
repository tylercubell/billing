<?php

namespace tylercubell\Billing\Console\Commands;

use tylercubell\Billing\Models\CardModel;
use tylercubell\Billing\Models\ChargeModel;
use tylercubell\Billing\Models\CouponModel;
use tylercubell\Billing\Models\CustomerModel;
use tylercubell\Billing\Models\DiscountModel;
use tylercubell\Billing\Models\DisputeModel;
use tylercubell\Billing\Models\InvoiceModel;
use tylercubell\Billing\Models\InvoiceItemModel;
use tylercubell\Billing\Models\LineItemModel;
use tylercubell\Billing\Models\PlanModel;
use tylercubell\Billing\Models\RefundModel;
use tylercubell\Billing\Models\SubscriptionModel;
use tylercubell\Billing\Models\SubscriptionItemModel;
use Illuminate\Console\Command;

class ClearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all billing data.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->confirm('Are you sure you want to clear all billing data?')) {
            $this->line('Clearing: Cards');
            CardModel::truncate();

            $this->line('Clearing: Charges');
            ChargeModel::truncate();

            $this->line('Clearing: Coupons');
            CouponModel::truncate();

            $this->line('Clearing: Customers');
            CustomerModel::truncate();

            $this->line('Clearing: Discounts');
            DiscountModel::truncate();

            $this->line('Clearing: Disputes');
            DisputeModel::truncate();

            $this->line('Clearing: Invoices');
            InvoiceModel::truncate();

            $this->line('Clearing: Invoice Items');
            InvoiceItemModel::truncate();

            $this->line('Clearing: Line Items');
            LineItemModel::truncate();

            $this->line('Clearing: Plans');
            PlanModel::truncate();

            $this->line('Clearing: Refunds');
            RefundModel::truncate();

            $this->line('Clearing: Subscriptions');
            SubscriptionModel::truncate();

            $this->line('Clearing: Subscription Items');
            SubscriptionItemModel::truncate();

            $this->info('Done!');
        }
    }
}
