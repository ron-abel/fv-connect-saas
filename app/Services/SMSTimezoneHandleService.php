<?php

namespace App\Services;

use App\Jobs\HandleSmsOutboundTextJob;
use Illuminate\Support\Facades\Log as Logging;
use App\Services\FilevineService;
use Illuminate\Support\Carbon;

use App\Models\FvClientPhones;
use App\Models\FvClients;
use App\Models\Tenant;



class SMSTimezoneHandleService
{

    /**
     * Create SMS Job By Timezone
     */
    public function createSMSJobByTimezone($client_phone, $sms_number_from, $message_body, $tenant_id, $log_id = null)
    {
        try {
            $tenant_details = Tenant::where('id', $tenant_id)->first();
            $fv_client_phone = FvClientPhones::where('client_phone', $client_phone)->first();
            if ($fv_client_phone == null) {
                $apiurl = config('services.fv.default_api_base_url');
                if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                    $apiurl = $tenant_details->fv_api_base_url;
                }
                $filevine_api = new FilevineService($apiurl, "", $tenant_id);
                $params = [
                    'limit' => 1000,
                    'phone' => $client_phone,
                    'requestedFields' => "personId,fullName,addresses"
                ];
                $contacts = json_decode($filevine_api->getContacts($params));

                if (isset($contacts->items) and !empty($contacts->items)) {
                    $items = collect($contacts->items);
                    $clientId = isset($items[0]->personId->native) ? $items[0]->personId->native : 0;
                    $fullName = isset($items[0]->fullName) ? $items[0]->fullName : '';
                    $full_address = isset($items[0]->addresses[0]->fullAddress) ? $items[0]->addresses[0]->fullAddress : '';
                    $zip_code = isset($items[0]->addresses[0]->postalCode) ? $items[0]->addresses[0]->postalCode : '';

                    if (!empty($clientId)) {
                        $values = array(
                            'tenant_id' => $tenant_details->id,
                            'fv_client_id' => $clientId,
                            'fv_client_name' => $fullName,
                            'fv_client_address' => $full_address,
                            'fv_client_zip' => $zip_code,
                            'created_at' => date('Y-m-d H:i:s')
                        );
                        $fv_client = FvClients::create($values);
                        $fv_client_phone_values = array('client_id' => $fv_client->id,  'client_phone_state' => 'US', 'client_phone_timezone' => 'US/Eastern', 'client_phone' => $client_phone, 'created_at' => date('Y-m-d H:i:s'));
                        $fv_client_phone = FvClientPhones::create($fv_client_phone_values);
                    }
                }
            }

            if ($fv_client_phone != null) {
                $client_phone_timezone = $fv_client_phone->client_phone_timezone;
                $start_time = Carbon::parse('today 9am', $client_phone_timezone);
                $end_time = Carbon::parse('today 9pm', $client_phone_timezone);
                $now_time = Carbon::now($client_phone_timezone);

                if ($now_time->gte($start_time) && $now_time->lte($end_time)) {
                    $job_delay_time = now()->addSeconds(1);
                } else {
                    $delay_second = $now_time->diffInSeconds($start_time);
                    $job_delay_time = Carbon::now($client_phone_timezone)->addSeconds($delay_second);
                }

                $first_sms_sent_at = $fv_client_phone->first_sms_sent_at;
                $auto_communication_stop_at = !empty($fv_client_phone->auto_communication_stop_at) ? strtotime($fv_client_phone->auto_communication_stop_at) : 0;
                $auto_communication_start_at = !empty($fv_client_phone->auto_communication_start_at) ? strtotime($fv_client_phone->auto_communication_start_at) : 0;

                if ($auto_communication_stop_at > $auto_communication_start_at) {
                    return;
                }

                if (empty($first_sms_sent_at)) {
                    $first_message_body = "This is an automated message from " . $tenant_details->tenant_law_firm_name . ". We will send you regular communications throughout the duration of your case. Text msg rates and charges may apply. Reply STOP to end communications and START to re-start.";
                    $job = (new HandleSmsOutboundTextJob($tenant_id, $client_phone, $sms_number_from, $first_message_body))->delay($job_delay_time);
                    $jId = app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatch($job);

                    FvClientPhones::where('client_phone', $client_phone)->update(['first_sms_sent_at' => Carbon::now()]);
                }

                $job = (new HandleSmsOutboundTextJob($tenant_id, $client_phone, $sms_number_from, $message_body, $log_id))->delay($job_delay_time);
                $jId = app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatch($job);
            } else {
                $job = (new HandleSmsOutboundTextJob($tenant_id, $client_phone, $sms_number_from, $message_body, $log_id))->delay(now()->addSeconds(1));
                $jId = app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatch($job);
            }
        } catch (\Exception $ex) {
            $error = [
                __FILE__,
                __LINE__,
                $ex->getMessage()
            ];
            Logging::warning(json_encode($error));
        }
    }
}
