<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as Logging;
use Illuminate\Support\Facades\Session;

use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Support\Facades\DB;

use App\Models\Tenant;
use App\Models\Blacklist;
use App\Models\Log;
use App\Models\LanguageLog;
use App\Services\FilevineService;
use App\Models\ConfigCustomProjectName;
use App\Models\CalendarSetting;
use App\Models\CalendarSettingSectionField;


class CalendarController extends Controller
{
    public $cur_tenant_id;
    public function __construct()
    {
        Controller::setSubDomainName();
        $this->cur_tenant_id = session()->get('tenant_id');
    }

    /**
     *  [GET] Look up page for client
     */
    public function index(Request $request, $subdomain, $lookup_project_id)
    {
        try {
            $tr = new GoogleTranslate();
            $tr->setSource();
            $tr->setTarget(!is_null(session()->get('lang')) ? session()->get('lang') : 'en');
            $tenant_id = $this->cur_tenant_id;
            $tenant_data = Tenant::find($tenant_id);
            $lookup_data = [];
            $selected_client_native_id = null;


            $config_details = DB::table('config')->where('tenant_id', $tenant_id)->first();
            $config_details_custom = ConfigCustomProjectName::where('tenant_id', $tenant_id)->first();
            $blacklists = Blacklist::where('tenant_id', $tenant_id)->where('is_allow_client_potal', 0)->get();
            $blacklistProjectIds = $blacklists->pluck("fv_project_id")->toArray();


            $subdomain_name = session()->get('subdomain');
            if (!Session::has('contact_id') && empty(session()->get('contact_id'))) {
                return redirect()->route('client', ['subdomain' => $subdomain_name]);
            }

            $apiurl = config('services.fv.default_api_base_url');
            if (isset($tenant_data->fv_api_base_url) and !empty($tenant_data->fv_api_base_url)) {
                $apiurl = $tenant_data->fv_api_base_url;
            }
            $filevine_api = new FilevineService($apiurl, "");

            $project_details = json_decode($filevine_api->getProjectsById($lookup_project_id), true);

            if (!empty($project_details) && isset($project_details['projectId'])) {
                if (in_array($project_details['projectId']['native'], $blacklistProjectIds)) {
                    return redirect()->route('client', ['subdomain' => $subdomain_name])->with('someError', 'We are not able to retrieve your case details at this time. Please check back later.');
                }
                if (isset($project_details['clientId']['native'], $project_details['projectTypeId']['native'], $project_details['projectId']['native']) && $project_details['projectId']['native'] == $lookup_project_id) {
                    $project_id = "";
                    $last_login = "";
                    $project_id = $project_details['projectId']['native'];
                    $selected_client_native_id = $project_details['clientId']['native'];
                    if ($selected_client_native_id) {
                        $last_login_details = Log::where('fv_client_id', $selected_client_native_id)
                            ->where('tenant_id', $tenant_id)
                            ->orderBy('id', 'DESC')
                            ->limit(1)->first();
                        if ($last_login_details && isset($last_login_details->created_at)) {
                            $last_login = date_create($last_login_details->created_at);
                            $last_login =  date_format($last_login, "Y/m/d H:i A");
                        }
                    }

                    $appointment_items = [];
                    $appointments = json_decode($filevine_api->getProjectAppointments($lookup_project_id), true);
                    if (!empty($appointments) && isset($appointments['items'])) {
                        foreach ($appointments['items'] as $appointment) {
                            $appointment_items[] = [
                                'groupId' => $appointment['appointmentId']['native'],
                                'projectId' => $appointment['projectId']['native'],
                                'title' => $appointment['title'],
                                'notes' => isset($appointment['notes']) ? $appointment['notes'] : '',
                                'start' => date('Y-m-d', strtotime($appointment['startUtc'])),
                                'end' => date('Y-m-d', strtotime($appointment['endUtc'])),
                                'start_view' => date('d-m-Y', strtotime($appointment['startUtc'])),
                                'location' => $appointment['location'],
                                'all_day' => $appointment['allDay']
                            ];
                        }
                    }

                    $calendar_setting = CalendarSetting::where('tenant_id', $tenant_id)->first();
                    $calendar_setting_section_fields = [];
                    $calendar_setting_section_field_has_date = false;
                    $collection_section_items = [];
                    if (isset($calendar_setting->id)) {
                        $calendar_setting_section_fields = CalendarSettingSectionField::where('calendar_setting_id', $calendar_setting->id)->get();
                        $calendar_setting_section_field_has_date = CalendarSettingSectionField::where('calendar_setting_id', $calendar_setting->id)->where('field_type', 'Date')->exists();
                        $collection_field_details = json_decode($filevine_api->getProjectCollectionSelectorItem($lookup_project_id, $calendar_setting->collection_section_id), true);
                        if (isset($collection_field_details['items']) and !empty($collection_field_details['items'])) {
                            foreach ($collection_field_details['items'] as $field_details) {
                                $i = 0;
                                foreach ($field_details['dataObject'] as $key => $val) {
                                    if ($calendar_setting->display_item_collection_section_id == $key) {
                                        $i++;
                                        $item_val = "";
                                        if (is_array($val)) {
                                            if (isset($val['fullname'])) {
                                                $item_val = $val['fullname'];
                                            } else {
                                                $item_val = $key . '(' . $i . ')';
                                            }
                                        } else {
                                            $item_val = $val;
                                        }
                                        $collection_section_items[$field_details['itemId']['native']] = $item_val;
                                    }
                                }
                            }
                        }
                    }

                    $lookup_data[] = array(
                        'last_login' => $tr->translate($last_login),
                        'results'    => $project_details,
                        'config_details'     => $config_details,
                        'active_project_id'  => $lookup_project_id,
                        'project_id'  => $project_id,
                        'project_override_name' => $this->customizeProjectName($filevine_api, $config_details, $config_details_custom, $project_details['projectId']['native'], $project_details['clientName']),
                        'appointment_items' => $appointment_items
                    );
                } else {
                    $lookup_data[] = array(
                        'results'    => $project_details,
                        'project_id' => $project_details['projectId']['native'],
                        'active_project_id' => $lookup_project_id,
                        'project_override_name' => $this->customizeProjectName($filevine_api, $config_details, $config_details_custom, $project_details['projectId']['native'], $project_details['clientName'])
                    );
                }

                // Save data into fv_client_language_logs
                $languageMatch = [
                    'tenant_id' => $tenant_id,
                    'fv_client_id' => $selected_client_native_id,
                    'client_ip' => $request->getClientIp(),
                    'language' => !is_null(session()->get('lang')) ? session()->get('lang') : 'en'
                ];
                $languageData = $languageMatch;
                $languageData['updated_at'] = date('Y-m-d H:i:s');
                LanguageLog::updateOrCreate($languageMatch, $languageData);

                return $this->_loadContent(
                    'client.pages.calendar',
                    [
                        'lookup_data' => $lookup_data,
                        'selected_client_native_id' => $selected_client_native_id,
                        'lookup_project_id' => $lookup_project_id,
                        'calendar_setting' => $calendar_setting,
                        'calendar_setting_section_fields' => $calendar_setting_section_fields,
                        'calendar_setting_section_field_has_date' => $calendar_setting_section_field_has_date,
                        'collection_section_items' => $collection_section_items
                    ]
                );
            } else {
                return redirect()->route('client', ['subdomain' => $subdomain_name]);
            }
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
     *  [POST] Calendar data update
     */
    public function update(Request $request)
    {
        try {
            $tenant_id = $this->cur_tenant_id;
            $tenant_data = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($tenant_data->fv_api_base_url) and !empty($tenant_data->fv_api_base_url)) {
                $apiurl = $tenant_data->fv_api_base_url;
            }
            $filevine_api = new FilevineService($apiurl, "");

            $appointmentId = $request->appointmentId;
            $projectId = $request->projectId;
            $client_submitted_feedback = $request->client_submitted_feedback;
            $title_current_value = $request->title_current_value;
            $calendar_setting = CalendarSetting::where('tenant_id', $tenant_id)->first();

            if (isset($calendar_setting->calendar_visibility) && $calendar_setting->calendar_visibility) {

                $prepend_text = "\nFrom Client Portal [" . date('Y-m-d H:i:s') . "]: ";

                if ($calendar_setting->collect_appointment_feedback) {

                    if ($calendar_setting->feedback_type == 1) {

                        // Get Appointment Notes Current Value
                        $notes_current_value = $request->notes_current_value;

                        $filevine_api->updateAppointment($appointmentId, [
                            'notes' => $notes_current_value . ' | ' . $prepend_text . $client_submitted_feedback . " #fromClientPortal"
                        ]);
                    } else if ($calendar_setting->feedback_type == 2) {

                        if ($calendar_setting->sync_feedback_type == 1) {

                            $dataObject = [];

                            /* $calendar_setting_section_fields = CalendarSettingSectionField::where('calendar_setting_id', $calendar_setting->id)->where('field_type', '!=', 'Date')->first();
                            if ($calendar_setting_section_fields != null) {
                                $prepend_text = "Client Portal Feedback Received On Appointment " . $title_current_value . " [" . date('Y-m-d H:i:s') . "]: ";
                                $dataObject[$calendar_setting_section_fields->field_id] = $prepend_text . $client_submitted_feedback;
                            }
                            $calendar_setting_section_field_date = CalendarSettingSectionField::where('calendar_setting_id', $calendar_setting->id)->where('field_type', 'Date')->first();
                            if ($calendar_setting_section_field_date != null) {
                                $dataObject[$calendar_setting_section_field_date->field_id] = $request->client_submitted_feedback_date;
                            } */

                            $section_fields = $request->section_fields;
                            $calendar_setting_section_fields = CalendarSettingSectionField::where('calendar_setting_id', $calendar_setting->id)->get();
                            foreach ($calendar_setting_section_fields as $key => $section_field) {
                                $field_id = $section_field->field_id;
                                if (!empty($field_id) && isset($section_fields[$key]) && !empty($section_fields[$key])) {
                                    $prepend_text = "From Client Portal [" . date('Y-m-d H:i:s') . "]: ";
                                    $dataObject[$field_id] = $prepend_text . trim($section_fields[$key]);
                                }
                            }

                            if (count($dataObject)) {
                                $field_params["dataObject"] = $dataObject;
                                $filevine_api->createCollectionItem($projectId, $calendar_setting->collection_section_id, $field_params);
                            }
                        } else if ($calendar_setting->sync_feedback_type == 2) {

                            $section_fields = $request->section_fields;
                            $display_item_collection_section_item = $request->display_item_collection_section_item;
                            $existing_field_values = [];

                            $collection_field_details = json_decode($filevine_api->getProjectCollectionSelectorItem($projectId, $calendar_setting->collection_section_id, $display_item_collection_section_item), true);
                            if (isset($collection_field_details['dataObject']) and !empty($collection_field_details['dataObject'])) {
                                $existing_field_values = $collection_field_details['dataObject'];
                            }

                            $dataObject = [];
                            $calendar_setting_section_fields = CalendarSettingSectionField::where('calendar_setting_id', $calendar_setting->id)->get();
                            foreach ($calendar_setting_section_fields as $key => $section_field) {
                                $field_id = $section_field->field_id;
                                if (!empty($field_id) && isset($section_fields[$key]) && !empty($section_fields[$key])) {
                                    if (array_key_exists($field_id, $existing_field_values)) {
                                        $dataObject[$field_id] = $existing_field_values[$field_id] . $prepend_text . trim($section_fields[$key]);
                                    } else {
                                        $prepend_text = "From Client Portal [" . date('Y-m-d H:i:s') . "]: ";
                                        $dataObject[$field_id] = $prepend_text . trim($section_fields[$key]);
                                    }
                                }
                            }

                            if (!empty($display_item_collection_section_item) && count($dataObject)) {
                                $field_params["dataObject"] = $dataObject;
                                $filevine_api->updateProjectCollectionSelectorItem($projectId, $calendar_setting->collection_section_id, $display_item_collection_section_item, $field_params);
                            }
                        }
                    }
                }
            }

            // Call automated workflow webhook trigger function
            app('App\Http\Controllers\API\AutomatedWorkflowWebhookController')->calendarFeedback(['Object' => 'CalendarFeedback', 'Event' => 'Received', 'ProjectId' => $projectId, 'fv_client_id' => (!is_null(session()->get('contact_id')) ? session()->get('contact_id') : 0), 'tenant_id' => $this->cur_tenant_id]);

            return redirect()->back()->with('success', 'Feedback Received!');
        } catch (\Exception $ex) {
            $error = [
                __FILE__,
                __LINE__,
                $ex->getMessage()
            ];
            return response()->json($error);
        }
    }


    public function customizeProjectName($filevine_api, $config_details, $config_details_custom, $project_id, $clientName)
    {
        try {
            $project_override_name = "";
            if ($config_details->is_client_custom_project_name) {
                if (!empty($config_details_custom) && isset($config_details_custom->selected_option)) {
                    // get primary field value
                    if ($config_details_custom->selected_option == 'client_full_name') {
                        $project_override_name = $clientName;
                        // get sencodary field value
                        if ($config_details->is_client_custom_project_name_append_another_field) {
                            $pod1 = json_decode($filevine_api->getProjectFormsTeamInfo(['projectId' => $project_id, 'section' => $config_details_custom->sec_fv_section_id, 'fields' => $config_details_custom->sec_fv_field_id]), true);
                            if (isset($pod1[$config_details_custom->sec_fv_field_id])) {
                                $project_override_name .= (!empty($pod1[$config_details_custom->sec_fv_field_id]) ? ' - ' . $pod1[$config_details_custom->sec_fv_field_id] : '');
                            }
                        }
                    } else if ($config_details_custom->selected_option == 'field_value') {
                        $pod = json_decode($filevine_api->getProjectFormsTeamInfo(['projectId' => $project_id, 'section' => $config_details_custom->fv_section_id, 'fields' => $config_details_custom->fv_field_id]), true);
                        if (isset($pod[$config_details_custom->fv_field_id])) {
                            $project_override_name = (!empty($pod[$config_details_custom->fv_field_id]) ? $pod[$config_details_custom->fv_field_id] : $clientName);
                        }
                        // get sencodary field value
                        if ($config_details->is_client_custom_project_name_append_another_field) {
                            $pod1 = json_decode($filevine_api->getProjectFormsTeamInfo(['projectId' => $project_id, 'section' => $config_details_custom->sec_fv_section_id, 'fields' => $config_details_custom->sec_fv_field_id]), true);
                            if (isset($pod1[$config_details_custom->sec_fv_field_id])) {
                                $project_override_name .= (!empty($pod1[$config_details_custom->sec_fv_field_id]) ? ' - ' . $pod1[$config_details_custom->sec_fv_field_id] : '');
                            }
                        }
                    }
                }
            }
            return $project_override_name;
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
