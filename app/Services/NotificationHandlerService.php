<?php

namespace App\Services;


use Illuminate\Support\Facades\Log as Logging;

use Exception;

use App\Services\SendGridServices;

use App\Models\Tenant;
use App\Models\TenantFormResponse;
use App\Models\FeedbackNoteManager;
use App\Models\TenantNotificationLog;
use Carbon\Carbon;

class NotificationHandlerService
{


    public static function callActionService($notification_config, $params)
    {
        try {

            // Insert Notification Log
            $log_data = [
                'tenant_id' => $notification_config->tenant_id,
                'event_name' => $notification_config->event_name,
                'fv_project_id' => isset($params['project_id']) ? $params['project_id'] : 0,
                'fv_project_name' => isset($params['project_name']) ? $params['project_name'] : '',
                'fv_client_id' => isset($params['client_id']) ? $params['client_id'] : 0,
                'fv_client_name' => isset($params['client_name']) ? $params['client_name'] : '',
                'notification_body' => $params['note_body'],
                'created_at' => Carbon::now(),
            ];
            $log_id = TenantNotificationLog::insertGetId($log_data);

            $is_email_notification = $notification_config->is_email_notification;
            $is_post_to_filevine = $notification_config->is_post_to_filevine;

            $params['tenant_id'] = $notification_config->tenant_id;

            if ($is_email_notification) {
                self::emailNotification($params, $log_id);
            }

            if ($is_post_to_filevine) {
                self::postToFilevine($params, $log_id);
            }
        } catch (Exception $e) {
            Logging::warning($e->getMessage());
        }
    }

    public static function emailNotification($params, $log_id)
    {
        try {
            $client_name = isset($params['client_name']) ? $params['client_name'] : '';
            $client_id = isset($params['client_id']) ? $params['client_id'] : 0;

            $tenant_id = isset($params['tenant_id']) ? $params['tenant_id'] : 0;
            $project_id = isset($params['project_id']) ? $params['project_id'] : 0;
            if ($tenant_id && $project_id) {
                $tenant_details = Tenant::where('id', $tenant_id)->first();
                $apiurl = config('services.fv.default_api_base_url');
                if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                    $apiurl = $tenant_details->fv_api_base_url;
                }
                $filevine_api = new FilevineService($apiurl, "", $tenant_id);
                $project_details = json_decode($filevine_api->getProjectsById($project_id));
                $params['project_name'] = isset($project_details->projectName) ? $project_details->projectName : '';

                if (empty($client_name)) {
                    if ($client_id) {
                        $client_details = json_decode($filevine_api->getContactByContactId($client_id));
                        $client_name = isset($client_details->fullName) ? $client_details->fullName : '';
                    }
                }
            }

            // Send form response as CSV attachment
            $full_path = "";
            if (isset($params['tenant_form_response_id'])) {
                $tenant_form_response = TenantFormResponse::where('id', $params['tenant_form_response_id'])->first();
                if ($tenant_form_response != null) {
                    $form_response_values_json = json_decode($tenant_form_response->form_response_values_json);
                    $file_path = storage_path('app/public/form-submitted');
                    if (!file_exists($file_path)) {
                        mkdir($file_path, 0775, true);
                    }
                    $file_path = $file_path . "/";
                    $file_name = $params['tenant_id'] . $client_name . '.csv';
                    $full_path = $file_path . $file_name;
                    $file = fopen($full_path, 'w');
                    $header = [];
                    $data_row = [];
                    for ($i = 0; $i < count($form_response_values_json); $i++) {
                        $header[] = $form_response_values_json[$i]->label;
                        $data_row[] = $form_response_values_json[$i]->value;
                    }
                    fputcsv($file, $header);
                    fputcsv($file, $data_row);
                    fclose($file);
                }
            }

            $sg_data = [
                'trigger_name' => 'Admin Notification Email',
                'action_name' => $params['action_name'],
                'notification_body' => $params['note_body'],
                'client_fullname' => $client_name,
                'client_id' => $client_id,
                'fv_project_id' => isset($params['project_id']) ? $params['project_id'] : 0,
                'fv_project_name' => isset($params['project_name']) ? $params['project_name'] : '',
                'full_path' => $full_path,
            ];

            $send_grid_obj = new SendGridServices($tenant_id);
            $emailrecords = FeedbackNoteManager::where('tenant_id', $tenant_id)->get();
            foreach ($emailrecords as $record) {
                $send_grid_obj->sendTenantAdminNotificationEmail(trim($record->email), $sg_data);
            }

            // Update Notification Log
            TenantNotificationLog::where('id', $log_id)->update([
                'sent_email_notification_at' => Carbon::now()
            ]);
        } catch (\Exception $e) {
            Logging::warning($e->getMessage());
        }
    }


    public static function postToFilevine($params, $log_id)
    {
        try {
            if (isset($params['tenant_id']) && !empty($params['tenant_id']) && isset($params['project_id']) && !empty($params['project_id']) && isset($params['client_id']) && !empty($params['client_id'])) {
                $tenant_details = Tenant::where('id', $params['tenant_id'])->first();
                $apiurl = config('services.fv.default_api_base_url');
                if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                    $apiurl = $tenant_details->fv_api_base_url;
                }
                $filevine_api = new FilevineService($apiurl, "", $params['tenant_id']);

                $fv_params = [
                    'projectId' => ['native' => $params['project_id'], 'partner' => null],
                    'body' => $params['note_body'],
                    'authorId' => ['native' => $params['client_id'], 'partner' => null]
                ];
                $filevine_api->createNote($fv_params);

                // Update Notification Log
                TenantNotificationLog::where('id', $log_id)->update([
                    'sent_post_to_filevine_at' => Carbon::now()
                ]);
            }
        } catch (\Exception $e) {
            Logging::warning($e->getMessage());
        }
    }
}
