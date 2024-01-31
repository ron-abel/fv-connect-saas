<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\TwilioService;

use Illuminate\Support\Facades\Log as Logging;

use App\Models\AutoNoteGoogleReviewReplyMessages;

class HandleSmsOutboundTextJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $tenant_id, $to_numbers, $from_number, $msg_content, $log_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($tenant_id, $to_numbers, $from_number, $msg_content, $log_id = null)
    {
        $this->tenant_id = $tenant_id;
        $this->to_numbers = $to_numbers;
        $this->from_number = $from_number;
        $this->msg_content = $msg_content;
        $this->log_id = $log_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            if (env('APP_ENV') == 'production') {
                $twilio_api = new TwilioService();
                $msgresponse = $twilio_api->send_sms_message($this->to_numbers, $this->from_number, $this->msg_content);

                $msg_obj = ['tenant_id => ' . $this->tenant_id ,'Sent SMS By Job to: ' . $this->to_numbers, $this->msg_content];
                Logging::warning(json_encode($msg_obj));

                if (isset($msgresponse['sid']) and !empty($msgresponse['sid'])) {
                    $array = [
                        'message_id' => $msgresponse['sid']
                    ];

                    if (!empty($this->log_id)) {
                        $addMessage = AutoNoteGoogleReviewReplyMessages::where('id', $this->log_id)->update($array);
                    }
                }
            }
        } catch (\Exception $e) {
            Logging::warning($e->getMessage());
        }
    }
}
