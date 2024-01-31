<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log as Logging;
use App\Jobs\HandleFVClientPhoneTimezoneJob;

use App\Models\FvClientPhones;


class HandleFVClientPhoneTimezone extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fvclientphone:updatetimezone';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update FV Client Timezone';

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

            $client_phones = FvClientPhones::where('client_phone_timezone', '=', '')
                ->orWhereNull('client_phone_timezone')
                ->get();

            foreach ($client_phones as $client_phone) {
                $job_delay_time = now()->addSeconds(10);
                $job = (new HandleFVClientPhoneTimezoneJob($client_phone))->delay($job_delay_time);
                $jId = app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatch($job);
            }
        } catch (\Exception $e) {
            Logging::warning($e->getMessage());
        }
    }
}
