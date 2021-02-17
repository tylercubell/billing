<?php

namespace tylercubell\Billing\Console\Commands;

use tylercubell\Billing\Models\CustomerModel;
use Illuminate\Console\Command;
use App\User;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class LinkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:link {--log : Log the results of linking each user account.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Link user accounts to Stripe customers.';

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
        $log = $this->option('log');

        if ($log) {
            $logger = new Logger('Billing Link Log');
            $stream = new StreamHandler(storage_path('logs/billing_link_' . date('Y-m-d') . '_' . time() . '.log'), Logger::INFO);
            $formatter = new LineFormatter("%message%\n");
            $stream->setFormatter($formatter);
            $logger->pushHandler($stream);
            $logger->addInfo('Billing Link Results                ' . date('Y-m-d H:i:s'));
            $logger->addInfo('');
            $logger->addInfo('Result  | Message                          | User Email');
            $logger->addInfo('-------------------------------------------------------');
        } else {
            $logger = null;
        }

        $totalUsers = 0;
        $linkedUsers = 0;
        $unlinkedUsers = 0;
        $unlinkableUsers = 0;
        $this->line('Linking user accounts to Stripe customers...');

        User::chunk(200, function ($users) use ($log, $logger, $totalUsers, $linkedUsers, $unlinkedUsers, $unlinkableUsers) {
            foreach ($users as $user) {
                $totalUsers++;

                // There is no unique constraint for Stripe customer emails, however there is a
                // unique constraint for user emails.
                $count = CustomerModel::where('email', '=', $user->email)
                                      ->count();

                // No Stripe customers found for user email. Move on to next user.
                if ($count === 0) {
                    if ($log) {
                        $logger->addInfo('Info    | No Stripe customer found.        | ' . $user->email);
                    }

                    $unlinkedUsers++;

                    continue;
                // Single Stripe customer found with user email. Yipee.
                } elseif ($count === 1) {
                    CustomerModel::where('email', '=', $user->email)
                                 ->update(['user_id' => $user->id]);

                    if ($log) {
                        $logger->addInfo('Success | Stripe customer found. Updated!  | ' . $user->email);
                    }

                    $linkedUsers++;
                // Multiple Stripe customers found with user email. Uh oh...
                } else {
                    $this->error(
                        "Error: Multiple instances of '" . $user->email . "' in Stripe customers. " . 
                        "You must manually resolve this conflict in Stripe. Skipping..."
                    );

                    if ($log) {
                        $logger->addInfo('Error   | Multiple Stripe customers found. | ' . $user->email);
                        $unlinkableUsers++;
                    }
                }
            }
        });

        $this->line($totalUsers . ' users checked in total.');
        $this->line($linkedUsers . ' linked users.');
        $this->line($unlinkedUsers . ' unlinked users.');
        $this->line($unlinkableUsers . ' unlinkable users.');
        $this->info('Done!');
    }
}
