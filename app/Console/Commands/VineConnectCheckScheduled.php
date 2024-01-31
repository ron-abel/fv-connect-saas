<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TenantLive;
use Illuminate\Support\Facades\Log as Logging;



class VineConnectCheckScheduled extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vineconnect:checkscheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        try {
            $date = date('y-m-d');
            $TenantLives = TenantLive::where('scheduled_date', $date)->where('status', 'scheduled')->get();
            if (isset($TenantLives) and !empty($TenantLives)) {
                foreach ($TenantLives as $TenantLive) {
                    $TenantLive->update(['status' => 'live']);
                    $msg = 'tenant_id:' . $TenantLive->tenant_id . ' update to live';
                    Logging::warning($msg);
                }
            }
        } catch (\Exception $ex) {
            Logging::warning($ex->getMessage());
        }
    }
}
