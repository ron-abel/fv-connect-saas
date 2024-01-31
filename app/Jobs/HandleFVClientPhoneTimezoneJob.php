<?php

namespace App\Jobs;

use App\Models\FvClientPhones;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log as Logging;


class HandleFVClientPhoneTimezoneJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $client_phone;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($client_phone)
    {
        $this->client_phone = $client_phone;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            $fv_client_phone = FvClientPhones::find($this->client_phone->id);

            // Need to call Twilio API here and update timezone by phone number
            $fv_client_phone->client_phone_state = "US";
            $fv_client_phone->client_phone_timezone = "US/Eastern";
            $fv_client_phone->save();

        } catch (\Exception $ex) {
            Logging::warning($ex->getMessage());
        }
    }
}
