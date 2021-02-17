<?php

namespace tylercubell\Billing\Console\Commands;

use tylercubell\Billing\Exceptions\BillingException;
use Illuminate\Console\Command;
use Billing;
use File;

class BootstrapCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:bootstrap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bootstrap plans and coupons.';

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
        if ( ! File::exists(config_path('billing.php'))) {
            return $this->error('Bootstrap config not found. Please run `php artisan vendor:publish --tag=billing` first.');
        }

        foreach (config('billing.plans') as $plan) {
            $this->line('Creating plan: "' . $plan['id'] . '"');

            try {
                $result = Billing::createPlan($plan);
                $this->info('Success');
            } catch (BillingException $e) {
                $this->error('Stripe API error: ' . $e->message);
            }
        }

        foreach (config('billing.coupons') as $coupon) {
            $this->line('Creating coupon: "' . $coupon['id'] . '"');

            try {
                $result = Billing::createCoupon($coupon);
                $this->info('Success');
            } catch (BillingException $e) {
                $this->error('Stripe API error: ' . $e->message);
            }
        }

        $this->info('Done!');
    }
}
