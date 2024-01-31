<?php

namespace App\Jobs;

use App\Models\Log;
use App\Models\MassMessageLog;
use App\Models\Tenant;
use App\Services\TwilioService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log as Logging;
use App\Services\VariableService;

class ProcessMassMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $mass_message_log;
    private $message_body;
    private $tenant_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($mass_message_log)
    {
        $this->mass_message_log = $mass_message_log;
        $mass_message = $this->mass_message_log->mass_message;
        $this->tenant_id = $mass_message->tenant_id;
        $tenant = Tenant::find($this->tenant_id);

        $variable_service = new VariableService();
        $additional_data = [
            'law_firm_name' => $tenant->tenant_law_firm_name,
            'client_portal_url' => 'https://' . $tenant->tenant_name . '.vinetegrate.com',
            'tenant_id' => $this->tenant_id,
            'client_firstname' => $this->mass_message_log->person_name
        ];
        $message_body = $variable_service->updateVariables($mass_message->message_body, 'is_mass_text', $additional_data);

        $message_body = Str::replaceArray('[tenantname]', [$tenant->tenant_name], $message_body);
        $this->message_body = Str::replaceArray('[name]', [$this->mass_message_log->person_name], $message_body);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $response = null;
            $twilio_api = new TwilioService();
            if (env('APP_ENV') == 'production') {
                $test_sms_number_from = config('twilio.twilio.connections.twilio.mass_message');
                $to_number = isset($this->mass_message_log->person_number) ? $this->mass_message_log->person_number : null;
                if ($to_number !== null) {
                    $response = $twilio_api->send_sms_message($to_number, $test_sms_number_from, $this->message_body);
                    if (isset($response['sid']) and !empty($response['sid'])) {
                        MassMessageLog::whereId($this->mass_message_log->id)->update(['is_sent' => true, 'from_number' => $test_sms_number_from, 'sent_at' => Carbon::now()]);
                    } else {
                        MassMessageLog::whereId($this->mass_message_log->id)->update(['note' => json_encode($response), 'failed_count' => ($this->mass_message_log->failed_count + 1), 'failed_at' => Carbon::now()]);
                    }
                } else {
                    // logging
                    $msg_obj = [
                        'error' => "Process Mass Message Error!",
                        'mass_message_logs' => $this->mass_message_log,
                        'tenant' => $this->tenant_id
                    ];
                    Logging::warning(json_encode($msg_obj));
                    MassMessageLog::whereId($this->mass_message_log->id)->update(['note' => json_encode($msg_obj), 'failed_count' => ($this->mass_message_log->failed_count + 1), 'failed_at' => Carbon::now()]);
                }
            }
        } catch (\Exception $ex) {
            Logging::warning($ex->getMessage());
            MassMessageLog::whereId($this->mass_message_log->id)->update(['note' => json_encode($ex->getMessage()), 'failed_count' => ($this->mass_message_log->failed_count + 1), 'failed_at' => Carbon::now()]);
        }
    }
}
