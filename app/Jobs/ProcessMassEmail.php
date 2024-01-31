<?php

namespace App\Jobs;

use App\Models\Log;
use App\Models\MassEmailLog;
use App\Models\MassEmail;
use App\Models\Tenant;
use App\Services\SendGridServices;
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

class ProcessMassEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $mass_email_log;
    private $message_body;
    private $tenant_id;
    private $sendGridServices;
    private $law_firm_name;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($mass_email_log)
    {
        $this->mass_email_log = $mass_email_log;
        $mass_message = $this->mass_email_log->mass_email;
        $this->tenant_id = $mass_message->tenant_id;
        $tenant = Tenant::find($this->tenant_id);

        if(!empty($tenant->tenant_law_firm_name)){
            $this->law_firm_name = $tenant->tenant_law_firm_name;
        }else{
            $this->law_firm_name = $tenant->tenant_name;
        }

        $variable_service = new VariableService();
        $additional_data = [
            'law_firm_name' => $tenant->tenant_law_firm_name,
            'client_portal_url' => 'https://' . $tenant->tenant_name . '.vinetegrate.com',
            'tenant_id' => $this->tenant_id,
            'client_firstname' => $this->mass_email_log->person_name
        ];
        $message_body = $variable_service->updateVariables($mass_message->message_body, 'is_mass_text', $additional_data);
        $message_body = Str::replaceArray('[tenantname]', [$tenant->tenant_name], $message_body);
        $this->message_body = Str::replaceArray('[name]', [$this->mass_email_log->person_name], $message_body);
        $this->sendGridServices = new SendGridServices($this->tenant_id);
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
            if (env('APP_ENV') == 'production') {
                $to_email = isset($this->mass_email_log->person_email) ? $this->mass_email_log->person_email : null;
                $cc_email = isset($this->mass_email_log->cc_email) ? $this->mass_email_log->cc_email : null;
                if ($to_email !== null) {
                    // get media if any
                    $parsed_message = $this->getMediaUrls($this->message_body);

                    $mass_email = MassEmail::whereId($this->mass_email_log->mass_email_id)->first();

                    $subject = "VineConnect Mass Email!";
                    if(!empty($mass_email->campaign_name)){
                        $subject = $mass_email->campaign_name;
                    }

                    $response = $this->sendGridServices->sendMassEmail($to_email, $subject , $parsed_message, $cc_email);

                    if ($response) {
                        MassEmailLog::whereId($this->mass_email_log->id)->update(['is_sent' => true, 'sent_at' => Carbon::now()]);
                    } else {
                        MassEmailLog::whereId($this->mass_email_log->id)->update(['note' => json_encode($response), 'failed_count' => ($this->mass_email_log->failed_count + 1), 'failed_at' => Carbon::now()]);
                    }
                } else {
                    // logging
                    $msg_obj = [
                        'error' => "Process Mass Email Error!",
                        'mass_email_logs' => $this->mass_email_log,
                        'tenant' => $this->tenant_id
                    ];
                    Logging::warning(json_encode($msg_obj));
                    MassEmailLog::whereId($this->mass_email_log->id)->update(['note' => json_encode($msg_obj), 'failed_count' => ($this->mass_email_log->failed_count + 1), 'failed_at' => Carbon::now()]);
                }
            }
        } catch (\Exception $ex) {
            Logging::warning($ex->getMessage());
            MassEmailLog::whereId($this->mass_email_log->id)->update(['note' => json_encode($ex->getMessage()), 'failed_count' => ($this->mass_email_log->failed_count + 1), 'failed_at' => Carbon::now()]);
        }
    }

    private function getMediaUrls($body) {
        $response = [
            'body' => $body,
            'media' => []
        ];
        try {
            $matches = [];
            preg_match_all('/<MEDIA>(.*?)<\/MEDIA>/', $body, $matches);
            if(isset($matches[1]) && count($matches[1]) > 0) {
                $matches_array = $matches[1];
                for ($i=0; $i < count($matches_array); $i++) {
                    $media_locker = MediaLocker::where('media_code', $matches_array[$i])->first();
                    if(isset($media_locker->id)) {
                        // get contents and other details
                        $media_url = $media_locker->media_url;
                        $media_mime = "";
                        $media_name = pathinfo($media_url, PATHINFO_BASENAME);
                        $media_contents = file_get_contents($media_url);
                        $media_headers = implode("\n", $http_response_header);
                        if (preg_match_all("/^content-type\s*:\s*(.*)$/mi", $media_headers, $matches)) {
                            $media_mime = end($matches[1]);
                        }

                        $response['media'][] = [
                            'url'       => $media_url,
                            'name'       => $media_name,
                            'content'   => $media_contents,
                            'mime'      => $media_mime
                        ];
                    }
                }
            }
            $response['body'] = preg_replace('/<MEDIA>(.*?)<\/MEDIA>/', '', $body);
        }
        catch(\Exception $ex) {
            $error = [
                __FILE__,
                __LINE__,
                $ex->getMessage()
            ];
            Logging::warning(json_encode($error));
        }
        return $response;
    }

}
