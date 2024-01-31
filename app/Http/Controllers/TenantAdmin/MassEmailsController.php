<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessMassEmail;
use App\Models\Blacklist;
use App\Models\MassEmail;
use App\Models\MassEmailLog;
use App\Models\Tenant;
use App\Services\FilevineService;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log as Logging;
use Illuminate\Support\Facades\DB;
use Auth;
use App\Models\Variable;

class MassEmailsController extends Controller
{
    public $cur_tenant_id;
    public $filevine_api;

    public function __construct()
    {
        Controller::setSubDomainName();
        $this->cur_tenant_id = session()->get('tenant_id');
        $tenant_id = $this->cur_tenant_id;
        $Tenant = Tenant::find($tenant_id);
        $api_url = config('services.fv.default_api_base_url');
        if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
            $api_url = $Tenant->fv_api_base_url;
        }
        $this->filevine_api = new FilevineService($api_url, "");
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {

        try {

            $contact_metadata = collect(json_decode($this->filevine_api->getContactMetadata()));
            $person_types = $contact_metadata->where('selector', 'personTypes')->first();

            $accepted_types = ['adjuster', 'employee', 'hospital', 'staff', 'user', 'attorney', 'client', 'court', 'defendant', 'expert', 'insurance company', 'involved party', 'judge', 'investigating agency', 'medical examiner', 'treatment provider'];
            $allowedValuesArr = [];
            if (isset($person_types->allowedValues)) {
                foreach ($person_types->allowedValues as $item) {
                    if (in_array(strtolower($item->name), $accepted_types)) {
                        array_push($allowedValuesArr, $item);
                    }
                }
                $person_types->allowedValues = $allowedValuesArr;
            }

            $mass_message_logs = MassEmailLog::whereHas('mass_email', function ($query) {
                $query->where('tenant_id', $this->cur_tenant_id);
            })->get();

            $mass_messages = MassEmail::select('mass_emails.*', 'mass_emails.created_at', 'users.full_name as created_by')->leftJoin('users', 'mass_emails.created_by', '=', 'users.id')->where('mass_emails.tenant_id', $this->cur_tenant_id)->with('mass_email_logs')->orderBy('mass_emails.id', 'desc')->get();

            $variable_keys = Variable::getVariableKeyByPage('is_mass_text');

            return $this->_loadContent("admin.pages.mass_emails", [
                'person_types' => $person_types,
                'mass_message_logs' => $mass_message_logs,
                'mass_messages' => $mass_messages,
                'variable_keys' => $variable_keys
            ]);
        } catch (\Exception $ex) {
            $error = [
                __FILE__,
                __LINE__,
                $ex->getMessage()
            ];
            Logging::warning(json_encode($error));
            return view('error');
        }
    }


    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function mass_emails_logs($domain, $id)
    {
        $mass_email_logs = MassEmailLog::where('mass_email_id', $id)->get();

        return response()->json([
            'data' => $mass_email_logs,
            'status' => true,
        ]);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function fetch_contacts(Request $request)
    {
        $params = [
            'limit' => 1000,
            'personType' => $request->person_type,
            'requestedFields' => "personId,emails,fullName"
        ];

        return response()->json($this->getContactsByPersonType($params, $request->has('is_exclude_blacklist')));
    }

    /**
     * POST : submit the mass_messages record.
     */
    public function send_email_messages(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'message_body' => 'required',
            'campaign_name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $data = $request->all();
        $data['created_by'] = Auth::user()['id'];

        $mass_message = $this->save_messages_and_dispatch($data, $data['contacts']);

        return response()->json([
            'data' => $mass_message,
            'status' => true,
        ]);
    }

    /**
     * @param $params
     * @param $is_exclude_blacklist
     * @return array
     */
    private function getContactsByPersonType($params, $is_exclude_blacklist)
    {
        try {
            $tenant_id = $this->cur_tenant_id;
            $contacts = json_decode($this->filevine_api->getContacts($params));
            if ($is_exclude_blacklist) {
                $blacklists = Blacklist::where('tenant_id', $tenant_id)->where('is_allow_client_potal', 0)->get();
                $blacklistClientIds = $blacklists->pluck("fv_client_id")->toArray();
                if (count($blacklistClientIds) && $contacts->count > 0) {
                    $contacts = collect($contacts->items)->filter(function ($item) use ($blacklistClientIds) {
                        return !collect($blacklistClientIds)->contains($item->personId->native);
                    });
                }
            } else {
                $contacts = $contacts->items;
            }

            $num_of_contact = count($contacts);
            $contacts = collect($contacts)->where('emails', '!=', null)->toArray();
            $contacts_data = [];

            foreach ($contacts as $contact) {
                if (isset($contact->emails)) {
                    foreach ($contact->emails as $email) {
                        if (isset($email->address) && !empty($email->address)) {
                            array_push($contacts_data, [
                                'person_name' => $contact->fullName,
                                'person_email' => $email->address
                            ]);
                        }
                    }
                }
            }

            return [
                "contacts" => $contacts_data,
                "count" => count($contacts_data),
                "note" => $num_of_contact . " contacts, " . count($contacts_data) . " emails"
            ];
        } catch (\Exception $ex) {
            Logging::warning($ex->getMessage());
        }
    }

    /**
     * [POST] Upload CSV to public folder
     */
    public function upload_csv(Request $request)
    {
        try {

            $target_dir =  public_path('/assets/uploads/mass-emails');

            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $target_dir = $target_dir . "/";

            $target_file = $target_dir . basename($_FILES["csv_file"]["name"]);
            if ($_FILES["csv_file"]["size"] == 0) {
                return response()->json([
                    'data' => "No file attached",
                    'status' => false,
                ], 400);
            }

            $uploadOk = 1;
            $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));


            // Check file size
            if ($_FILES["csv_file"]["size"] > 500000) {
                return response()->json([
                    'data' => "Failed Records: Sorry, your file is too large.",
                    'status' => false,
                ], 400);
            }
            // Allow certain file formats
            if ($fileType != "csv") {
                return response()->json([
                    'data' => "Failed Records: Sorry, only CSV files are allowed.",
                    'status' => false,
                ], 400);
            }
            // Check if $uploadOk is set to 0 by an error
            if ($uploadOk == 0) {
                return response()->json([
                    'data' => "Failed Records: Sorry, your file was not uploaded.",
                    'status' => false,
                ], 400);
            } else {
                if (move_uploaded_file($_FILES["csv_file"]["tmp_name"], $target_file)) {
                    return $this->get_contacts_from_csv($target_file, $_FILES["csv_file"]["name"]);
                } else {
                    return response()->json([
                        'data' => "Failed Records: Sorry, there was an error uploading your file.",
                        'status' => false,
                    ], 400);
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'data' => $e->getMessage(),
                'status' => false,
            ], 400);
        }
    }

    /**
     * Get Contacts from csv
     * @param $file_path
     * @return array
     */
    public function get_contacts_from_csv($file_path, $file_name)
    {
        $csv_content_array = $this->convertCSVtoArray($file_path);
        $contacts = [];

        foreach ($csv_content_array as $index => $csv_content) {
            if ($index > 0) {
                if (isset($csv_content[1]) && !empty($csv_content[1])) {
                    array_push($contacts, [
                        'person_name' => mb_convert_encoding($csv_content[0], 'UTF-8', 'UTF-8'),
                        'person_email' => $csv_content[1],
                        'cc_email' => $csv_content[2],
                    ]);
                }
            }
        }

        return [
            "contacts" => $contacts,
            "count" => count($contacts),
            "note" => count($contacts) . " contacts, " . count($contacts) . " numbers",
            'upload_csv_file_name' => $file_name
        ];
    }

    /**
     * sub function: create new jobs to send mass messages
     */
    public function save_messages_and_dispatch($data, $mass_message_data)
    {
        try {
            $job_delay_time = (int)env('MASS_TEXT_BUFFER_MINS', 10) * 60;
            $data['tenant_id'] = $this->cur_tenant_id;
            $data['is_schedule_job'] = $data['is_schedule_job'] ? true : false;
            if ($data['is_schedule_job'] && !empty($data['schedule_time'])) {
                $data['schedule_time'] = date('Y-m-d H:i:s', strtotime($data['schedule_time']));
                $time_diff = strtotime($data['schedule_time']) - time();
                if ($time_diff > 0) {
                    $job_delay_time = $time_diff;
                }
            } else {
                $data['schedule_time'] = null;
            }

            $mass_message = MassEmail::create($data);

            $mass_message_logs = $mass_message->mass_email_logs()->createMany($mass_message_data);
            if (count($mass_message_logs)) {
                $msg_obj = [
                    'mass_message_logs' => $mass_message_logs,
                    'mass_message_data' => $mass_message_data,
                ];

                foreach ($mass_message_logs as $mass_message_log) {
                    $job = (new ProcessMassEmail($mass_message_log))->delay($job_delay_time);
                    $jId = app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatch($job);
                    $mass_message_log->job_id = $jId;
                    $mass_message_log->save();
                }
            }
            return $mass_message;
        } catch (\Exception $ex) {
            Logging::warning($ex->getMessage());
        }
    }

    /**
     * Get CSV data and convert to array format
     */
    public function convertCSVtoArray($filename)
    {
        try {
            // The nested array to hold all the arrays
            $result = [];
            // Open the file for reading
            if (($h = fopen("{$filename}", "r")) !== FALSE) {
                // Each line in the file is converted into an individual array that we call $data
                // The items of the array are comma separated
                while (($data = fgetcsv($h, 1000, ",")) !== FALSE) {
                    // Each individual array is being pushed into the nested array
                    $result[] = $data;
                }
                // Close the file
                fclose($h);
            }
            return $result;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Get Mass Email Log In CSV Format
     */
    public function massEmailsExportcsv($domain, $id)
    {
        $service = new ExportService();
        return $service->exportMassEmailsCSV($id);
    }

    /**
     * [GET] Mass Email Custom Log
     */
    public function massEmailsCustomLog(Request $request)
    {
        $log_start_date = $request->log_start_date;
        $log_end_date = $request->log_end_date;

        $mass_message_logs = DB::table('mass_emails')
            ->select('mass_email_logs.*', 'mass_emails.message_body')
            ->join('mass_email_logs', 'mass_emails.id', '=', 'mass_email_logs.mass_email_id')
            ->where('mass_emails.tenant_id', '=', $this->cur_tenant_id)
            ->where('mass_email_logs.created_at', '>=', $log_start_date)
            ->where('mass_email_logs.created_at', '<=', $log_end_date . ' 23:59:59')
            ->get();

        return response()->json([
            'data' => $mass_message_logs,
            'status' => true,
        ]);
    }

    /**
     * [POST] Generate Mass Email Log from mass_email_logs
     */
    public function massEmailsCustomLogCSV(Request $request)
    {
        $log_start_date = $request->log_start_date;
        $log_end_date = $request->log_end_date;

        $service = new ExportService();
        return $service->exportMassEmailsCustomCSV($this->cur_tenant_id, $log_start_date, $log_end_date);
    }

    /**
     * [POST] Delete Jobs, mass_email_logs & mass_emails
     */
    public function massEmailsDelete(Request $request)
    {
        $mass_messages_id = $request->input('mass_messages_id');
        DB::table('jobs')
            ->join('mass_email_logs', 'mass_email_logs.job_id', '=', 'jobs.id')
            ->where('mass_email_logs.mass_email_id', '=', $mass_messages_id)
            ->delete();
        MassEmailLog::where('mass_email_id', $mass_messages_id)->delete();
        MassEmail::where('id', $mass_messages_id)->delete();

        return response()->json([
            'message' => 'Mass Email deleted successfully!',
            'status' => true,
        ]);
    }

    /**
     * Manully Re-create failed job from job history
     */
    public function reCreateJob(Request $request)
    {
        try {
            $re_create_all_job = $request->re_create_all_job;
            if ($re_create_all_job) {
                $mass_messages_id = $request->mass_messages_id;
                DB::table('jobs')
                    ->join('mass_email_logs', 'mass_email_logs.job_id', '=', 'jobs.id')
                    ->where('mass_email_logs.mass_email_id', '=', $mass_messages_id)
                    ->delete();

                $mass_message_logs = MassEmailLog::whereHas('mass_email', function ($query) {
                    $query->where('tenant_id', $this->cur_tenant_id);
                })->where('mass_email_id', $mass_messages_id)->get();

                if (count($mass_message_logs)) {
                    foreach ($mass_message_logs as $mass_message_log) {
                        $job_delay_time = now()->addSeconds(10);
                        $job = (new ProcessMassEmail($mass_message_log))->delay($job_delay_time);
                        $jId = app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatch($job);
                        $mass_message_log->job_id = $jId;
                        $mass_message_log->save();
                    }
                }

                $mass_message = MassEmail::find($mass_messages_id);
                $mass_message->is_schedule_job = false;
                $mass_message->save();
            } else {
                $mass_messages_logs_id = $request->mass_messages_logs_id;
                $mass_message_logs = MassEmailLog::whereHas('mass_email', function ($query) {
                    $query->where('tenant_id', $this->cur_tenant_id);
                })->where('id', $mass_messages_logs_id)->get();

                if (count($mass_message_logs)) {
                    foreach ($mass_message_logs as $mass_message_log) {
                        $job_delay_time = now()->addSeconds(10);
                        $job = (new ProcessMassEmail($mass_message_log))->delay($job_delay_time);
                        $jId = app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatch($job);
                        $mass_message_log->job_id = $jId;
                        $mass_message_log->save();
                    }
                }
            }

            return response()->json([
                'message' => 'Job Created successfully!',
                'status' => true,
            ]);
        } catch (\Exception $ex) {
            Logging::warning($ex->getMessage());
        }
    }
}
