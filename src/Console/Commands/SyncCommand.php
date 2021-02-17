<?php

namespace tylercubell\Billing\Console\Commands;

use Illuminate\Console\Command;
use Billing;

class SyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:sync {types?* : Data types to sync.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync billing data.';

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
        $options = Billing::getSyncOptions();

        function formatForConsole($type) {
            return ucwords(str_replace('_', ' ', $type));
        }

        function formatForMethod($type) {
            return str_replace(' ','', ucwords(str_replace('_', ' ', $type)));
        }

        $types = $this->argument('types');

        if (empty($types)) {
            // Sync all data
            foreach ($options as $option) {
                $this->line('Syncing: ' . formatForConsole($option));
                call_user_func('Billing::sync' . formatForMethod($option));
            }

            $this->info('Done!');
        } else {
            // Check each type before executing syncs
            foreach ($types as $type) {
                if ( ! in_array($type, $options)) {
                    $this->error("'$type' is not a valid option.");
                    $this->error('Available options: ' . implode(', ', $options) . '.');
                    return;
                }
            }

            // Sync selected types of data
            foreach ($types as $type) {
                $this->line('Syncing: ' . formatForConsole($type));
                call_user_func('Billing::sync' . formatForMethod($type));
            }

            $this->info('Done!');
        }
    }
}
