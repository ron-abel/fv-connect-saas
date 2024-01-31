<?php

namespace App\Services;

use App\Models\API_LOG;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use App\Models\Tenant;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Exception;
use Laravel\Cashier\Cashier;
use Response;
use App\Models\Feedbacks;
use App\Models\MassMessageLog;
use App\Models\Log;
use App\Models\TenantForm;
use App\Models\ClientAuthFailedSubmitLog;
use App\Models\MassMessage;
use App\Models\TenantNotificationLog;
use App\Models\MassEmailLog;

class ExportService
{
    /**
     * Export Tenants CSV
     */
    public function exportTenantsCsv($request)
    {
        try {
            if (config('app.env') == 'local') {
                $http = "http://";
            } else {
                $http = "https://";
            }

            $domainName = config('app.domain');

            $all_plans = [];
            // get all plans
            $subscriptionServices = new SubscriptionService();
            $plans = $subscriptionServices->retrievePlans();
            if (is_array($plans)) {
                foreach ($plans as $plan) {
                    $all_plans[$plan->product->id] = $plan->product->name;
                }
            }

            $all_tenants = Tenant::with('owner', 'customer')->where('tenant_name', '!=', config('app.superadmin'))->get();

            $fileName = 'tenants.csv';

            // headers for file
            $headers = array(
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            );

            // file columns
            $columns = array('Tenant ID', 'Tenant Name', 'Owner', 'Owner Email', 'Registered At', 'Billing Plan', 'Billing Start', 'Billing Amount', 'Status');

            $callback = function () use ($columns, $http, $domainName, $all_plans) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, $columns);
                $allPrices = Cashier::stripe()->prices->all();

                Tenant::with('owner', 'customer')->where('tenant_name', '!=', config('app.superadmin'))->chunk(100, function ($tenants) use ($handle, $http, $domainName, $all_plans, $allPrices) {
                    foreach ($tenants as $tenant) {
                        $row['Tenant ID']  = $tenant->id;
                        $row['Tenant Name']    = $tenant->tenant_name;
                        $row['Subdomain Link'] = $http . $tenant->tenant_name . '.' . $domainName;
                        $row['Registered At']  = $tenant->created_at;

                        $row['Owner']  = ($tenant->owner && $tenant->owner->full_name) ? $tenant->owner->full_name : '';
                        $row['Owner Email']  = ($tenant->owner && $tenant->owner->email) ? $tenant->owner->email : '';
                        $row['Billing Plan']    = ($tenant->customer && $tenant->customer->subscribed('default') && isset($tenant->customer->subscription('default')->items[0])) ? ((isset($all_plans[$tenant->customer->subscription('default')->items[0]->stripe_product])) ? $all_plans[$tenant->customer->subscription('default')->items[0]->stripe_product] : '') : '';
                        $row['Billing Start'] = ($tenant->customer && $tenant->customer->subscribed('default')) ? (new \DateTime($tenant->customer->subscription('default')->created_at))->format('m/d/Y') : '';
                        $row['Billing Amount'] = ($tenant->customer && $tenant->customer->subscribed('default')) ? $allPrices->retrieve($tenant->customer->subscription('default')->stripe_price)->unit_amount : '';
                        $row['Status']  = $tenant->status;
                        $row['Active']  = $tenant->is_active ? '1' : '0';

                        fputcsv($handle, array($row['Tenant ID'], $row['Tenant Name'], $row['Owner'], $row['Owner Email'], date("m/d/Y H:i", strtotime($row['Registered At'])), $row['Billing Plan'], ($row['Billing Start']) ? date("m/d/Y", strtotime($row['Billing Start'])) : "", $row['Billing Amount'], $row['Status']));
                    }
                });
                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * Export Tenants Usage CSV
     */
    public function exportTenantsUsageCsv($request)
    {
        try {
            $all_plans = [];
            $max_price_plan = "";
            $max_price = 0;
            // $plans = [];
            // get all plans
            $subscriptionServices = new SubscriptionService();
            $plans = $subscriptionServices->retrievePlans();
            if (is_array($plans)) {
                foreach ($plans as $plan) {
                    $all_plans[$plan->id] = ($plan->amount / 100) . '/' . $plan->interval;
                }
            }

            $fileName = 'tenants_usage.csv';
            // headers for file
            $headers = array(
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            );

            // file columns
            $columns = array('ID', 'Tenant Name', 'Price Level', 'Renewel Day', 'API Usage for Period', 'Overall Average API Usage Per Day', 'Twilio Aggregated Cost');

            $callback = function () use ($columns, $all_plans) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, $columns);
                Tenant::with('customer')->where('tenant_name', '!=', config('app.superadmin'))->chunk(100, function ($tenants) use ($handle, $all_plans) {
                    foreach ($tenants as $tenant) {
                        $stripe_product = ($tenant->customer && $tenant->customer->subscribed('default')) ? $tenant->customer->subscription('default')->items[0]->stripe_price : '';

                        $row['ID']  = $tenant->id;
                        $row['Tenant Name']    = $tenant->tenant_name;
                        $row['Price Level'] = (!empty($stripe_product) && isset($all_plans[$stripe_product])) ? $all_plans[$stripe_product] : '';
                        $row['Renewel Day']  = ($tenant->customer && $tenant->customer->subscribed('default')) ? (new \DateTime($tenant->customer->subscription('default')->ends_at))->format('d/m/Y') : '';
                        $row['API Usage for Period']  = isset($tenant->usage_stats) ? $tenant->usage_stats['api_usage'] : '';
                        $row['Overall Average API Usage Per Day'] = isset($tenant->usage_stats) ? $tenant->usage_stats['api_usage_per_day'] : '';
                        $row['Twilio Aggregated Cost'] = isset($tenant->usage_stats) ? $tenant->usage_stats['twilio_aggregated_cost'] : '';

                        fputcsv($handle, array($row['ID'], $row['Tenant Name'], $row['Price Level'], $row['Renewel Day'], $row['API Usage for Period'], $row['Overall Average API Usage Per Day'], $row['Twilio Aggregated Cost']));
                    }
                });
                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }



    /**
     * Export APILog CSV
     */
    public function exportAPILogCsv($request)
    {
        try {
            $startDate = request()->has('log_start_date') ? date("Y-m-d", strtotime(request()->get('log_start_date'))) : Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = request()->has('log_end_date') ? date("Y-m-d", strtotime(request()->get('log_end_date'))) : Carbon::now()->format('Y-m-d');
            $tenant_id = request()->has('tenant_id') ? request()->get('tenant_id') : '';

            $all_log_query = API_LOG::whereDate('api_logs.created_at', '>=', $startDate)
                ->whereDate('api_logs.created_at', '<=', $endDate . ' 23:59:59');
            if (!empty($tenant_id)) {
                $all_log_query->where('api_logs.tenant_id', $tenant_id);
            }
            $all_log_query->orderby('api_logs.created_at', 'DESC');
            $all_api_logs = $all_log_query->get();

            $fileName = 'api_log.csv';
            // file columns
            $columns = array('Tenant', 'Domain', '2FA Number', 'FV Project ID', 'API', 'Date');
            $headers = array(
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            );
            $callback = function () use ($columns, $all_api_logs) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, $columns);
                foreach ($all_api_logs as $api_log) {
                    $row['Tenant']  = $api_log->tenant ? $api_log->tenant->tenant_name ?? '' : '';
                    $row['Domain']    = $api_log->request_domain ?? '';
                    $row['2FA Number'] = $api_log->to_number ?? '';
                    $row['FV Project ID']  = $api_log->fv_project_id ?? '';

                    $row['API']  = $api_log->api_name ?? '';
                    $row['Date']    =  $api_log->created_at ? Carbon::parse($api_log->created_at, 'Y-m-d') : '';

                    fputcsv($handle, array($row['Tenant'], $row['Domain'], $row['2FA Number'], $row['FV Project ID'], $row['API'], $row['Date']));
                }
                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * Export Feedbacks CSV
     */
    public function exportFeedbacksCsv($request, $tenant_id = null)
    {
        try {
            if ($tenant_id == null) return null;
            $startDate = request()->has('log_start_date') ? request()->get('log_start_date') : Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = request()->has('log_end_date') ? request()->get('log_end_date') : Carbon::now()->format('Y-m-d');
            $feedbacks = Feedbacks::Where('tenant_id', $tenant_id)
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate . ' 23:59:59')
                ->orderBy('id', 'DESC')
                ->get();

            $fileName = 'feedback.csv';
            // file columns
            $columns = array('Client Name', 'Project Name', 'Project ID', 'Team Member', 'Project Phase', 'Timestamp', 'How satisfied are you with the legal service has provided?', 'How likely are you to recommend our firm to others?', 'How useful have you found this Client Portal to be?', 'Is there anything we could be doing better?');
            $headers = array(
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            );
            $callback = function () use ($columns, $feedbacks) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, $columns);
                foreach ($feedbacks as $single_feedback) {
                    $row['Client Name']  = $single_feedback->client_name ?? '';
                    $row['Project Name']    = $single_feedback->project_name ?? '';
                    $row['Project ID'] = $single_feedback->project_id ?? '';
                    $row['Team Member']  = $single_feedback->legal_team_name ?? '';
                    $row['Project Phase']  = $single_feedback->project_phase ?? '';
                    $row['Timestamp']  = \Carbon\Carbon::parse($single_feedback->created_at)->timezone('America/Vancouver')->format('Y-m-d H:i:s') ?? '';
                    $row['How satisfied are you with the legal service has provided?'] =  $single_feedback->fd_mark_legal_service ?? '';
                    $row['How likely are you to recommend our firm to others?']  = $single_feedback->fd_mark_recommend ?? '';
                    $row['How useful have you found this Client Portal to be?']  = $single_feedback->fd_mark_useful ?? '';
                    $row['Is there anything we could be doing better?']  = $single_feedback->fd_content ?? '';

                    fputcsv($handle, array($row['Client Name'], $row['Project Name'], $row['Project ID'], $row['Team Member'], $row['Project Phase'], $row['Timestamp'], $row['How satisfied are you with the legal service has provided?'], $row['How likely are you to recommend our firm to others?'], $row['How useful have you found this Client Portal to be?'], $row['Is there anything we could be doing better?']));
                }
                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function exportMassMessagesCSV($id)
    {
        try {
            $mass_message_logs = MassMessageLog::where('mass_message_id', $id)->get();
            $fileName = 'mass_message_logs_' . date("Y-m-d-H-i-s") . '.csv';
            $columns = array('Client Name', 'Client Number', 'Created At', 'Sent At', 'Message');
            $headers = array(
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            );
            $callback = function () use ($columns, $mass_message_logs, $id) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, $columns);
                $mass_message = MassMessage::find($id);
                foreach ($mass_message_logs as $record) {
                    $row['Client Name']  = $record->person_name ?? '';
                    $row['Client Number']    = $record->person_number ?? '';
                    $row['Created At'] = (new \DateTime($record->created_at))->format('Y-m-d H:i:s') ?? '';
                    $row['Sent At']  = $record->sent_at ? (new \DateTime($record->sent_at))->format('Y-m-d H:i:s') : '';
                    $row['Message']  = $mass_message->message_body;
                    fputcsv($handle, array($row['Client Name'], $row['Client Number'], $row['Created At'], $row['Sent At'], $row['Message']));
                }
                fclose($handle);
            };
            return response()->stream($callback, 200, $headers);
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }


    public function exportMassMessagesCustomCSV($tenant_id, $log_start_date, $log_end_date)
    {
        try {

            $mass_message_logs = DB::table('mass_messages')
                ->select('mass_message_logs.*', 'mass_messages.message_body')
                ->join('mass_message_logs', 'mass_messages.id', '=', 'mass_message_logs.mass_message_id')
                ->where('mass_messages.tenant_id', '=', $tenant_id)
                ->where('mass_message_logs.created_at', '>=', $log_start_date)
                ->where('mass_message_logs.created_at', '<=', $log_end_date . ' 23:59:59')
                ->get();

            $fileName = 'mass_message_custom_logs_' . date("Y-m-d-H-i-s") . '.csv';
            $columns = array('Client Name', 'Client Number', 'Message Body', 'Created At', 'Sent At');
            $headers = array(
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            );
            $callback = function () use ($columns, $mass_message_logs) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, $columns);
                foreach ($mass_message_logs as $record) {
                    $row['Client Name']  = $record->person_name ?? '';
                    $row['Client Number']   = '+1' . $record->person_number;
                    $row['Message Body']    = $record->message_body;
                    $row['Created At'] = (new \DateTime($record->created_at))->format('Y-m-d H:i:s') ?? '';
                    $row['Sent At']  = $record->sent_at ? (new \DateTime($record->sent_at))->format('Y-m-d H:i:s') : '';
                    fputcsv($handle, array($row['Client Name'], $row['Client Number'], $row['Message Body'], $row['Created At'], $row['Sent At']));
                }
                fclose($handle);
            };
            return response()->stream($callback, 200, $headers);
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }


    /**
     * Export Usage Log CSV
     */
    public function exportUsageLogCsv($request, $tenant_id = null)
    {
        try {
            if ($tenant_id == null) return null;
            $startDateLog = request()->has('log_start') ? request()->get('log_start') : Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDateLog = request()->has('log_end') ? request()->get('log_end') : Carbon::now()->format('Y-m-d');
            $login_status = request()->has('login_status') ? request()->get('login_status') : "";

            $log = Log::Where('tenant_id', $tenant_id);
            if ($login_status == "1") {
                $log = $log->where('Result', $login_status);
            } else if ($login_status == "0") {
                $log = $log->where(function ($query) {
                    $query->where("Result", 0)
                        ->orWhereNull('Result');
                });
            }

            $logs = $log->whereDate('created_at', '>=', $startDateLog)
                ->whereDate('created_at', '<=', $endDateLog)
                ->orderBy('id', 'DESC')
                ->get();

            $fileName = 'usage_log_' . date("Y-m-d") . '.csv';
            // file columns
            $columns = array('Name Entry', 'Matched Client', 'Phone', 'Email', 'Matched Project Id', 'Success', 'Date');
            $headers = array(
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            );
            $callback = function () use ($columns, $logs) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, $columns);
                foreach ($logs as $log) {
                    $row['Name Entry']  = $log->Lookup_Name;
                    $row['Matched Client']    = $log->Result_Client_Name;
                    $row['Phone'] = $log->Lookup_Phone_num;
                    $row['Email'] = $log->lookup_email;
                    $row['Matched Project Id']  = $log->Result_Project_Id;
                    $row['Success']  = $log->Result ? 'Yes' : 'No';
                    $row['Date']  = \Carbon\Carbon::parse($log->created_at)->timezone('America/Vancouver')->format('Y-m-d H:i:s') ?? '';

                    fputcsv($handle, array($row['Name Entry'], $row['Matched Client'], $row['Phone'], $row['Email'], $row['Matched Project Id'], $row['Success'], $row['Date']));
                }
                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * Export Message Log CSV
     */
    public function exportMessageLogCsv($request, $tenant_id = null)
    {
        try {
            if ($tenant_id == null) return null;
            $startDateMsg = request()->has('msg_start_date') ? date("Y-m-d", strtotime(request()->get('msg_start_date'))) : Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDateMsg = request()->has('msg_end_date') ? date("Y-m-d", strtotime(request()->get('msg_end_date'))) : Carbon::now()->format('Y-m-d');
            $type_of_line = request()->has('type_of_line') ? request()->get('type_of_line') : "";

            $fileName = 'message_log_' . date("Y-m-d") . '.csv';
            $headers = array(
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            );

            if (request()->has('msg_type') && request()->has('msg_type') == 'in') {
                $google_review_reply_messages = DB::table('auto_note_google_review_messages')
                    ->join('fv_clients', 'auto_note_google_review_messages.client_id', '=', 'fv_clients.id')
                    ->select('auto_note_google_review_messages.*', 'fv_clients.fv_client_name')
                    ->where(['auto_note_google_review_messages.tenant_id' => $tenant_id])
                    ->where(['auto_note_google_review_messages.msg_type' => 'in'])
                    ->where('auto_note_google_review_messages.created_at', '>=', $startDateMsg)
                    ->where('auto_note_google_review_messages.created_at', '<=', $endDateMsg . ' 23:59:59')
                    ->orderBy('auto_note_google_review_messages.id', 'DESC')
                    ->paginate(50, ['*'], 'google_review_reply_messages');

                foreach ($google_review_reply_messages as $google_review) {
                    $score = (int) filter_var($google_review->message_body, FILTER_SANITIZE_NUMBER_INT);
                    $google_review->score = $score ? $score : '';
                }
                $columns = array('Client Name', 'Client Number', 'Message', 'Score', 'Created At');
                $callback = function () use ($columns, $google_review_reply_messages) {
                    $handle = fopen('php://output', 'w');
                    fputcsv($handle, $columns);
                    foreach ($google_review_reply_messages as $google_review_reply_message) {
                        $row['Client Name']  = $google_review_reply_message->fv_client_name;
                        $row['Client Number']    = ($google_review_reply_message->msg_type == "out") ? ((!empty($google_review_reply_message->to_number) && substr($google_review_reply_message->to_number, 0, 1) != '+') ? '+1' . $google_review_reply_message->to_number : $google_review_reply_message->to_number) : ((!empty($google_review_reply_message->from_number) && substr($google_review_reply_message->from_number, 0, 1) != '+') ? '+1' . $google_review_reply_message->from_number : $google_review_reply_message->from_number);
                        $row['Message'] = $google_review_reply_message->message_body;
                        $row['Score']  = $google_review_reply_message->score;
                        $row['Created At']  = (new \DateTime($google_review_reply_message->created_at))->format('Y-m-d H:i:s') ?? '';
                        fputcsv($handle, array($row['Client Name'], $row['Client Number'], $row['Message'], $row['Score'], $row['Created At']));
                    }
                    fclose($handle);
                };
            } else {

                $mass_message_logs = DB::table('mass_messages')
                    ->join('mass_message_logs', 'mass_messages.id', '=', 'mass_message_logs.mass_message_id')
                    ->select('mass_message_logs.person_name as client_name', 'mass_message_logs.person_number as to_number', 'mass_messages.message_body as message', 'mass_message_logs.from_number', 'mass_message_logs.is_inbound as msg_type', DB::raw("'MassMessage' as type_of_line"), 'mass_message_logs.job_id',  'mass_message_logs.created_at as created_at')
                    ->where('mass_messages.tenant_id', $tenant_id)
                    ->where('mass_message_logs.is_inbound', 1)
                    ->where('mass_message_logs.created_at', '>=', $startDateMsg)
                    ->where('mass_message_logs.created_at', '<=', $endDateMsg . ' 23:59:59');

                $google_review_reply_messages = DB::table('auto_note_google_review_messages as angrm')
                    ->join('fv_clients', 'angrm.client_id', '=', 'fv_clients.id')
                    ->select('fv_clients.fv_client_name as client_name', 'angrm.to_number', 'angrm.message_body as message', 'angrm.from_number', 'angrm.msg_type', 'angrm.type_of_line', DB::raw("Null as job_id"), 'angrm.created_at as created_at')
                    ->where(['angrm.tenant_id' => $tenant_id])
                    ->where('angrm.created_at', '>=', $startDateMsg)
                    ->where('angrm.created_at', '<=', $endDateMsg . ' 23:59:59');


                //Automated Workflow SMS
                $aw_sms = DB::table('automated_workflow_action_logs as aw_action_log')
                    ->join('automated_workflow_actions', 'aw_action_log.action_id', '=', 'automated_workflow_actions.id')
                    ->join('automated_workflow_initial_actions', 'automated_workflow_actions.automated_workflow_initial_action_id', '=', 'automated_workflow_initial_actions.id')
                    ->join('fv_clients', 'aw_action_log.fv_client_id', '=', 'fv_clients.fv_client_id')
                    ->select('fv_clients.fv_client_name as client_name', 'aw_action_log.sms_phones as to_number', 'aw_action_log.note_body as message', DB::raw("Null as from_number"), DB::raw("'out' as msg_type"), DB::raw("'AW SMS' as type_of_line"), DB::raw("Null as job_id"), 'aw_action_log.created_at as created_at')
                    ->where(['automated_workflow_initial_actions.tenant_id' => $tenant_id])
                    ->where(['automated_workflow_initial_actions.action_short_code' => '1'])
                    ->where(['aw_action_log.tenant_id' => $tenant_id])
                    ->where(['fv_clients.tenant_id' => $tenant_id])
                    ->where('aw_action_log.created_at', '>=', $startDateMsg)
                    ->where('aw_action_log.created_at', '<=', $endDateMsg . ' 23:59:59');

                //Automated Workflow Email
                $aw_email = DB::table('automated_workflow_action_logs as aw_action_log')
                    ->join('automated_workflow_actions', 'aw_action_log.action_id', '=', 'automated_workflow_actions.id')
                    ->join('automated_workflow_initial_actions', 'automated_workflow_actions.automated_workflow_initial_action_id', '=', 'automated_workflow_initial_actions.id')
                    ->join('fv_clients', 'aw_action_log.fv_client_id', '=', 'fv_clients.fv_client_id')
                    ->select('fv_clients.fv_client_name as client_name', 'aw_action_log.emails as to_number', 'aw_action_log.note_body as message', DB::raw("Null as from_number"), DB::raw("'email' as msg_type"), DB::raw("'AW Email' as type_of_line"), DB::raw("Null as job_id"), 'aw_action_log.created_at as created_at')
                    ->where(['automated_workflow_initial_actions.tenant_id' => $tenant_id])
                    ->where(['automated_workflow_initial_actions.action_short_code' => '12'])
                    ->where(['aw_action_log.tenant_id' => $tenant_id])
                    ->where(['fv_clients.tenant_id' => $tenant_id])
                    ->where('aw_action_log.created_at', '>=', $startDateMsg)
                    ->where('aw_action_log.created_at', '<=', $endDateMsg . ' 23:59:59');


                if (!empty($type_of_line)) {
                    if ($type_of_line == "AWSMS") {
                        $google_review_reply_messages = $aw_sms->orderBy('created_at', 'DESC')
                            ->paginate(50, ['*'], 'automated_workflow_action_logs');
                    } else if ($type_of_line == "AWEmail") {
                        $google_review_reply_messages = $aw_email->orderBy('created_at', 'DESC')
                            ->paginate(50, ['*'], 'automated_workflow_action_logs');
                    } else if ($type_of_line != "MassMessage") {
                        $google_review_reply_messages = $google_review_reply_messages->where('angrm.type_of_line', '=', $type_of_line)
                            ->orderBy('created_at', 'DESC')->get();
                    } else {
                        $google_review_reply_messages = $google_review_reply_messages->where('angrm.type_of_line', '=', $type_of_line)
                            ->union($mass_message_logs)
                            ->orderBy('created_at', 'DESC')->get();
                    }
                } else {
                    $google_review_reply_messages = $google_review_reply_messages->union($mass_message_logs)->union($aw_sms)->union($aw_email)
                        ->orderBy('created_at', 'DESC')->get();
                }
                $columns = array('Client Name', 'Client Number/Email', 'Message', 'Type', 'Type Of Line', 'Created At');
                $callback = function () use ($columns, $google_review_reply_messages) {
                    $handle = fopen('php://output', 'w');
                    fputcsv($handle, $columns);
                    foreach ($google_review_reply_messages as $google_review_reply_message) {
                        $row['Client Name']  = $google_review_reply_message->client_name;
                        if ($google_review_reply_message->msg_type == "email") {
                            $row['Client Number']    = $google_review_reply_message->to_number;
                        } else {
                            $row['Client Number']    = ($google_review_reply_message->msg_type == "out" || $google_review_reply_message->type_of_line == "MassMessage") ? ((!empty($google_review_reply_message->to_number) && substr($google_review_reply_message->to_number, 0, 1) != '+') ? '+1' . $google_review_reply_message->to_number : $google_review_reply_message->to_number) : ((!empty($google_review_reply_message->from_number) && substr($google_review_reply_message->from_number, 0, 1) != '+') ? '+1' . $google_review_reply_message->from_number : $google_review_reply_message->from_number);
                        }
                        $row['Message'] = $google_review_reply_message->message;
                        $row['Type']  = ($google_review_reply_message->msg_type == "in" || $google_review_reply_message->msg_type == 1) ? "Incoming Message" : "Outgoing Message";
                        $row['Type Of Line']  = $google_review_reply_message->type_of_line;
                        $row['Created At']  = (new \DateTime($google_review_reply_message->created_at))->format('Y-m-d H:i:s') ?? '';
                        fputcsv($handle, array($row['Client Name'], $row['Client Number'], $row['Message'], $row['Type'], $row['Type Of Line'], $row['Created At']));
                    }
                    fclose($handle);
                };
            }

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * Export Form Response Log CSV
     */
    public function exportFormResponseCSV($tenant_id, $log_start_date, $log_end_date)
    {
        try {

            $logs = TenantForm::select('tenant_forms.id', 'tenant_forms.form_name', 'tenant_form_responses.fv_client_id', 'tenant_form_responses.fv_project_id', 'tenant_form_responses.created_at')
                ->join('tenant_form_responses', 'tenant_forms.id', '=', 'tenant_form_responses.tenant_form_id')
                ->where('tenant_id', $tenant_id)
                ->where('tenant_form_responses.created_at', '>=', $log_start_date)
                ->where('tenant_form_responses.created_at', '<=', $log_end_date . ' 23:59:59')
                ->latest()->get();

            $fileName = 'form_responses_logs_' . date("Y-m-d-H-i-s") . '.csv';
            $columns = array('Form Name', 'Project ID', 'Client ID', 'Response Completion', 'Timestamp');
            $headers = array(
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            );
            $callback = function () use ($columns, $logs) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, $columns);
                foreach ($logs as $record) {
                    $row['Form Name']  = $record->form_name;
                    $row['Project ID']   = $record->fv_project_id;
                    $row['Client ID']    = $record->fv_client_id;
                    $row['Response Completion'] = $record->id;
                    $row['Timestamp']  = $record->created_at ? (new \DateTime($record->created_at))->format('Y-m-d H:i:s') : '';
                    fputcsv($handle, array($row['Form Name'], $row['Project ID'], $row['Client ID'], $row['Response Completion'], $row['Timestamp']));
                }
                fclose($handle);
            };
            return response()->stream($callback, 200, $headers);
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }


    /**
     * Export Submitted Log CSV
     */
    public function exportSubmittedLogCsv($request, $tenant_id = null)
    {
        try {
            if ($tenant_id == null) return null;
            $startDateLog = request()->has('log_start') ? request()->get('log_start') : Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDateLog = request()->has('log_end') ? request()->get('log_end') : Carbon::now()->format('Y-m-d');

            $logs = ClientAuthFailedSubmitLog::Where('tenant_id', $tenant_id)
                ->whereDate('created_at', '>=', $startDateLog)
                ->whereDate('created_at', '<=', $endDateLog)
                ->whereNull('deleted_at')
                ->where('is_handled', false)
                ->orderBy('id', 'DESC')
                ->get();

            $fileName = 'submitted_log_' . date("Y-m-d") . '.csv';
            // file columns
            $columns = array('First Name', 'Last Name', 'Phone', 'Email', 'Client IP', 'Status', 'Handled Action', 'At Sent Client Note', 'At Added Black List', 'Note', 'Date');
            $headers = array(
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            );
            $callback = function () use ($columns, $logs) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, $columns);
                foreach ($logs as $log) {
                    $row['First Name']  = $log->lookup_first_name;
                    $row['Last Name']  = $log->lookup_last_name;
                    $row['Phone']  = $log->lookup_phone;
                    $row['Email']  = $log->lookup_email;
                    $row['Client IP']  = $log->client_ip;
                    $row['Is Handled']  = $log->is_handled;
                    $row['Handled Action']  = $log->handled_action;
                    $row['At Sent Client Note']  = \Carbon\Carbon::parse($log->at_sent_client_note)->timezone('America/Vancouver')->format('Y-m-d H:i:s') ?? '';
                    $row['At Added Black List']  = \Carbon\Carbon::parse($log->at_added_black_list)->timezone('America/Vancouver')->format('Y-m-d H:i:s') ?? '';
                    $row['Note']  = $log->note;
                    $row['Date']  = \Carbon\Carbon::parse($log->created_at)->timezone('America/Vancouver')->format('Y-m-d H:i:s') ?? '';
                    fputcsv($handle, $row);
                }
                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }


    /**
     * Export Notification Log CSV
     */
    public function exportNotificationLogCsv($request, $tenant_id = null)
    {
        try {
            if ($tenant_id == null) return null;
            $log_start_date = request()->has('log_start_date') ? request()->get('log_start_date') : Carbon::now()->startOfMonth()->format('Y-m-d');
            $log_end_date = request()->has('log_end_date') ? request()->get('log_end_date') : Carbon::now()->format('Y-m-d');
            $notification_event_name = request()->has('notification_event_name') ? request()->get('notification_event_name') : '';

            $logs = TenantNotificationLog::where('tenant_id', $tenant_id)
                ->where('created_at', '>=', $log_start_date)
                ->where('created_at', '<=', $log_end_date . ' 23:59:59');
            if (!empty($notification_event_name)) {
                $logs = $logs->where('event_name', '=', $notification_event_name);
            }
            $logs = $logs->get();

            $fileName = 'notification_log_' . date("Y-m-d") . '.csv';
            // file columns
            $columns = array('Event Name', 'Project Id', 'Project Name', 'Client Id', 'Client Name', 'Notification Body', 'Created At', 'Email Notification At', 'Post to FV At');
            $headers = array(
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            );
            $callback = function () use ($columns, $logs) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, $columns);
                foreach ($logs as $log) {
                    $row['Event Name']  = $log->event_name;
                    $row['Project Id']  = $log->fv_project_id;
                    $row['Project Name']  = $log->fv_project_name;
                    $row['Client Id']  = $log->fv_client_id;
                    $row['Client Name']  = $log->fv_client_name;
                    $row['Notification Body']  = $log->notification_body;
                    $row['Created At']  = \Carbon\Carbon::parse($log->created_at)->timezone('America/Vancouver')->format('Y-m-d H:i:s') ?? '';
                    $row['Email Notification At']  = \Carbon\Carbon::parse($log->sent_email_notification_at)->timezone('America/Vancouver')->format('Y-m-d H:i:s') ?? '';
                    $row['Post to FV At']  = \Carbon\Carbon::parse($log->sent_post_to_filevine_at)->timezone('America/Vancouver')->format('Y-m-d H:i:s') ?? '';
                    fputcsv($handle, $row);
                }
                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function exportMassEmailsCustomCSV($tenant_id, $log_start_date, $log_end_date)
    {
        try {

            $mass_message_logs = DB::table('mass_emails')
                ->select('mass_email_logs.*', 'mass_emails.message_body')
                ->join('mass_email_logs', 'mass_emails.id', '=', 'mass_email_logs.mass_email_id')
                ->where('mass_emails.tenant_id', '=', $tenant_id)
                ->where('mass_email_logs.created_at', '>=', $log_start_date)
                ->where('mass_email_logs.created_at', '<=', $log_end_date . ' 23:59:59')
                ->get();

            $fileName = 'mass_email_custom_logs_' . date("Y-m-d-H-i-s") . '.csv';
            $columns = array('Client Name', 'Client Email', 'CC Email', 'Message Body', 'Created At', 'Sent At');
            $headers = array(
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            );
            $callback = function () use ($columns, $mass_message_logs) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, $columns);
                foreach ($mass_message_logs as $record) {
                    $row['Client Name']  = $record->person_name ?? '';
                    $row['Client Email']   = $record->person_email;
                    $row['CC Email']   = $record->cc_email;
                    $row['Message Body']    = $record->message_body;
                    $row['Created At'] = (new \DateTime($record->created_at))->format('Y-m-d H:i:s') ?? '';
                    $row['Sent At']  = $record->sent_at ? (new \DateTime($record->sent_at))->format('Y-m-d H:i:s') : '';
                    fputcsv($handle, array($row['Client Name'], $row['Client Email'], $row['CC Email'], $row['Message Body'], $row['Created At'], $row['Sent At']));
                }
                fclose($handle);
            };
            return response()->stream($callback, 200, $headers);
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function exportMassEmailsCSV($id)
    {
        try {
            $mass_message_logs = MassEmailLog::where('mass_email_id', $id)->get();
            $fileName = 'mass_email_logs_' . date("Y-m-d-H-i-s") . '.csv';
            $columns = array('Client Name', 'Client Email', 'CC Email', 'Created At', 'Sent At');
            $headers = array(
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            );
            $callback = function () use ($columns, $mass_message_logs) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, $columns);
                foreach ($mass_message_logs as $record) {
                    $row['Client Name']  = $record->person_name ?? '';
                    $row['Client Email']    = $record->person_email ?? '';
                    $row['CC Email']    = $record->cc_email ?? '';
                    $row['Created At'] = (new \DateTime($record->created_at))->format('Y-m-d H:i:s') ?? '';
                    $row['Sent At']  = $record->sent_at ? (new \DateTime($record->sent_at))->format('Y-m-d H:i:s') : '';
                    fputcsv($handle, array($row['Client Name'], $row['Client Email'], $row['CC Email'], $row['Created At'], $row['Sent At']));
                }
                fclose($handle);
            };
            return response()->stream($callback, 200, $headers);
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }
}
