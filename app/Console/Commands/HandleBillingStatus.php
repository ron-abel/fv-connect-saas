<?php

namespace App\Console\Commands;

use App\Jobs\jobHandleBillingStatus;
use Illuminate\Console\Command;
use App\Services\SubscriptionService;

class HandleBillingStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:status';
    private $subscriptionServices;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update billing status for all tenants daily';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SubscriptionService $subscriptionServices)
    {
        parent::__construct();
        $this->subscriptionServices = $subscriptionServices;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        dispatch(new jobHandleBillingStatus($this->subscriptionServices));
    }
}
