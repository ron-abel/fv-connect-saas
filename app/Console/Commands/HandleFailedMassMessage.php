<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log as Logging;

use App\Jobs\ProcessMassMessage;

use App\Models\MassMessageLog;

class HandleFailedMassMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'massmessage:handle_failed_jobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle Failed Mass Message';

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

            $mass_message_logs = MassMessageLog::whereHas('mass_message', function ($query) {
                $query->where('tenant_id', '!=', 0);
            })->where('is_sent', false)->whereNull('is_inbound')->where('failed_count', '<', 3)->whereNotNull('failed_at')->get();

            if (count($mass_message_logs)) {
                foreach ($mass_message_logs as $mass_message_log) {
                    $job_delay_time = now()->addSeconds(10);
                    $job = (new ProcessMassMessage($mass_message_log))->delay($job_delay_time);
                    $jId = app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatch($job);
                    $mass_message_log->job_id = $jId;
                    $mass_message_log->save();
                }
            }
        } catch (\Exception $e) {
            Logging::warning($e->getMessage());
        }
    }
}
