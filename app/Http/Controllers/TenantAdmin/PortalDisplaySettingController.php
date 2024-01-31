<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Models\LegalteamConfig;
use App\Models\LegalteamPersonConfig;
use Illuminate\Http\Request;
use App\Services\FilevineService;
use App\Models\Tenant;
use App\Models\TenantCustomVital;
use App\Models\ConfigProjectVital;
use App\Models\TenantLive;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as Logging;


class PortalDisplaySettingController extends Controller
{

    public $cur_tenant_id;

    public function __construct()
    {
        Controller::setSubDomainName();
        $this->cur_tenant_id = session()->get('tenant_id');
    }

    /**
     * [GET] Client Portal Setup Page for Tenanat Admin
     */
    public function index()
    {
        try {

            $tenant_id = $this->cur_tenant_id;
            $Tenant = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }


            //            Display Your Project's Team Assignments in Client Portal Start
            $filevine_api = new FilevineService($apiurl, "");
            $fv_project_type_list = $filevine_api->getProjectTypeList();
            if ($fv_project_type_list != null) {
                $fv_project_type_list = json_decode($fv_project_type_list, true);
            } else {
                $fv_project_type_list = [];
            }
            //            Display Your Project's Team Assignments in Client Portal END
            //            Project Naming Convention Start
            if (isset($fv_project_type_list['items']) and !empty($fv_project_type_list['items'])) {
                $project_type_lists = $fv_project_type_list['items'];
            } else {
                $project_type_lists = [];
            }
            //            Project Naming Convention End
            //            Setting Up Your Custom Project Vitals Start
            $tenantCustomVital = TenantCustomVital::where('tenant_id', $tenant_id)->first();
            //            Setting Up Your Custom Project Vitals END
            $legal_tem_config_types = [];

            $fv_projects = $filevine_api->getProjectsList();
            if ($fv_projects != null) {
                $fv_projects = json_decode($fv_projects, true);
            } else {
                $fv_projects = [];
            }
            $fv_project_id = null;
            if (isset($fv_projects['count'], $fv_projects['items'], $fv_projects['items'][0], $fv_projects['items'][0]['projectId'], $fv_projects['items'][0]['projectId']['native'])) {
                $fv_project_id = $fv_projects['items'][0]['projectId']['native'];
            }

            if ($fv_project_id != null) {
                $response = $filevine_api->getTeamOrgRoles($fv_project_id);
                $data = json_decode($response, true);
                if (isset($data['items']) && count($data['items']) > 0) {
                    $legal_tem_config_types = array_values($data['items']);
                }
            }

            //            Brand Your Client Portal Start
            $config_details = DB::table('config')->where('tenant_id', $tenant_id)->first();
            $legal_team_by_role = true;
            if (isset($config_details) and !empty($config_details)) {
                $legal_team_by_role = $config_details->is_legal_team_by_roles;
            }
            //            Brand Your Client Portal End
            $data = LegalteamConfig::where('tenant_id', $this->cur_tenant_id)->orderBy('role_order', 'asc')->get();
            $data_person_config = LegalteamPersonConfig::where('tenant_id', $this->cur_tenant_id)->orderBy('sort_order', 'asc')->get();
            $tenant_override_title = DB::table('tenant_override_titles')
                ->where('tenant_id', $this->cur_tenant_id)->first();

            $tenantLive = TenantLive::where('tenant_id', $tenant_id)->first();

            return $this->_loadContent('admin.pages.portal_display_settings', compact('tenantLive', 'data', 'project_type_lists', 'Tenant', 'tenantCustomVital', 'legal_tem_config_types', 'legal_team_by_role', 'fv_project_type_list', 'tenant_override_title', 'data_person_config'));
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
     * [POST] Get Project Vital By Project Type ID
     */
    public function project_vitals_by_project_type_id(Request $request, $subdomain)
    {
        try {

            $projectTypeId = $request->input('projectTypeId');
            $tenant_id = $this->cur_tenant_id;
            $Tenant = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $filevine_api = new FilevineService($apiurl, "");

            $offset = 0;
            $limit = 1000;
            $projectId = 0;
            do {
                $projects_object = json_decode($filevine_api->getProjectsList($limit, $offset), TRUE);
                $next_link = trim($projects_object['links']['next']);
                if (isset($projects_object['items'])) {
                    $projectWithTypes = [];
                    foreach ($projects_object['items'] as $project) {
                        $projectWithTypes[$project['projectTypeId']['native']] = $project['projectId']['native'];
                    }

                    if (array_key_exists($projectTypeId, $projectWithTypes)) {
                        $projectId = $projectWithTypes[$projectTypeId];
                        break;
                    }

                    if (count($projects_object['items'])) {
                        $offset += $limit;
                    }
                }
            } while ($next_link);

            $projectsVitals = json_decode($filevine_api->getProjectsVitalsById($projectId));
            $html_vitals = '<option value="">Select Vital to Display</option>';
            foreach ($projectsVitals as $projectsVital) {
                $fieldName = $projectsVital->fieldName;
                $config_project_vitals = ConfigProjectVital::where('tenant_id', $tenant_id)->where('fv_project_type_id', $projectTypeId)->where('vital_name', $fieldName)->first();
                if ($config_project_vitals == null) {
                    $html_vitals .= '<option value="' . $fieldName . '">' . $projectsVital->friendlyName . '</option>';
                }
            }

            return response()->json([
                'status'  => true,
                'html_vitals' => $html_vitals
            ]);
        } catch (\Exception $ex) {
            Logging::warning($ex->getMessage());
        }
    }


    /**
     * [POST] Get Current Project Vital By Project Type ID
     */

    public function current_project_vitals_by_project_type_id(Request $request, $subdomain)
    {

        $projectTypeId = $request->input('projectTypeId');
        $tenant_id = $this->cur_tenant_id;
        $ConfigProjectVitals = ConfigProjectVital::where('fv_project_type_id', $projectTypeId)->where('tenant_id', $tenant_id)->get();
        $html_vitals_slot = '';
        foreach ($ConfigProjectVitals as $ConfigProjectVital) {
            $html_vitals_slot .= '<div class="row project-vital"> <div class="col-md-2 pt-6 vitalSlotOrder">Vital Slot #</div><div class="col-md-3 pt-3">';
            $html_vitals_slot .= '<input class="form-control friendly_name" readonly name="friendly_name" value="' . $ConfigProjectVital->friendly_name . '">';
            $html_vitals_slot .= '<input type="hidden" class="form-control field_name"  name="field_name" value="' . $ConfigProjectVital->vital_name . '">';
            $html_vitals_slot .= '</div><div class="col-md-3 pt-3">';
            $html_vitals_slot .= '<input class="form-control override_title" name="override_title" value="' . $ConfigProjectVital->override_title . '">';
            $html_vitals_slot .= '</div><div class="col-md-4 pt-3">';
            $html_vitals_slot .= '<button type="button" class="btn btn-sm btn-danger remove"><i class="fa fa-trash"></i></button>';
            $html_vitals_slot .= '<button type="button" class="btn btn-sm btn-grey moveup"><i class="fa fa-arrow-up"></i></button>';
            $html_vitals_slot .= '<button type="button" class="btn btn-sm btn-grey movedown" style="padding:0px"><i class="fa fa-arrow-down"></i></button>';
            $html_vitals_slot .= '</div></div>';
        }
        return response()->json([
            'status'  => true,
            'html_vitals_slot' => $html_vitals_slot,
        ]);
    }

    /**
     * [POST] Save Project Vital Data and Others Data
     */
    public function project_vitals_post(Request $request, $subdomain)
    {
        $tenant_id = $this->cur_tenant_id;
        $TenantCustomVital = TenantCustomVital::where('tenant_id', $tenant_id)->first();
        if ($TenantCustomVital != null) {
            $TenantCustomVital->update(['project_vital_override_title' => $request->project_vital_override_title, 'is_show_project_sms_number' => $request->projectSMSNumber, 'is_show_project_email' => $request->projectEmail, 'is_show_project_clientname' => $request->projectClient, 'is_show_project_name' => $request->projectName, 'is_show_project_id' => $request->projectId]);
        } else {
            TenantCustomVital::create(['project_vital_override_title' => $request->project_vital_override_title, 'tenant_id' => $tenant_id, 'is_show_project_sms_number' => $request->projectSMSNumber, 'is_show_project_email' => $request->projectEmail, 'is_show_project_clientname' => $request->projectClient, 'is_show_project_name' => $request->projectName, 'is_show_project_id' => $request->projectId]);
        }

        if (isset($request->vital_value) && count($request->vital_value) > 0) {
            ConfigProjectVital::where('fv_project_type_id', $request->projectTypeId)->where('tenant_id', $tenant_id)->delete();
            foreach ($request->vital_value as $vitalValue) {
                ConfigProjectVital::create(['tenant_id' => $tenant_id, 'fv_project_type_id' => $request->projectTypeId, 'fv_project_type' => $request->projectType, 'vital_name' => $vitalValue['field_name'], 'friendly_name' => $vitalValue['friendly_name'], 'override_title' => $vitalValue['override_title']]);
            }
        }

        return response()->json([
            'status'  => true,
            'message' => 'Setting saved successfully!'
        ]);
    }

    /**
     * [POST] Save tenant custom vitals display phone number
     */
    public function save_tenant_portal_display_settings(Request $request, $subdomain)
    {
        $display_phone_number = $request->input('display_phone_number');
        if (!$display_phone_number) {
            $display_phone_number = "";
        }
        $tenant_id = $this->cur_tenant_id;
        $TenantCustomVital = TenantCustomVital::where('tenant_id', $tenant_id)->first();
        if ($TenantCustomVital != null) {
            $TenantCustomVital->update(['display_phone_number' => $display_phone_number]);
        } else {
            TenantCustomVital::create(['tenant_id' => $tenant_id, 'display_phone_number' => $display_phone_number]);
        }
        return response()->json([
            'status'  => true,
            'message' => 'Setting saved successfully!'
        ]);
    }

    public function settings_portal_form(Request $request, $subdomain)
    {
        try {

            if ($request->isMethod('post')) {
                // $validatedData = $request->validate([
                //     'fv_api_key' => "required_without:image",
                //     'fv_key_secret' => 'required_without:image',
                // ]);

                $current_date = date('Y-m-d H:i:s');
                $fv_tenant_base_url = rtrim($request->input('fv_tenant_base_url'), '/');
                $msg_err = false;

                if ($msg_err) {
                    return redirect()->route('settings', ['subdomain' => $subdomain])
                        ->with('msg_err', $msg_err);
                }

                if ($request->settings_details) {
                    if ($request->hasFile('image')) {
                        $imageName = time() . '-' . $request->image->getClientOriginalName();
                        $request->image->move(public_path('/assets/uploads/client_logo/'), $imageName);

                        $values = array('logo' => $imageName, 'updated_at' => $current_date);
                    } else {
                        $values = array('updated_at' => $current_date);
                    }
                    DB::table('config')->where('tenant_id', $request->tenant_details->id)->update($values);

                    // DB::table('config')->where('tenant_id', $request->tenant_details->id)->update([
                    //     'is_show_archieved_phase' => $request['show_archieved_phase'] == 'on' ? 1 : 0
                    // ]);

                    // update law firm display name
                    if (!empty($request->input('lf_display_name'))) {
                        Tenant::where('id', $request->tenant_details->id)->update(['tenant_law_firm_name' => $request->input('lf_display_name')]);
                    }
                    if (!empty($request->input('fv_tenant_base_url'))) {
                        $fv_api_base_url = $this->_getFVAPIBaseUrl($fv_tenant_base_url);

                        Tenant::where('id', $request->tenant_details->id)
                            ->update([
                                'fv_tenant_base_url' => $fv_tenant_base_url,
                                'fv_api_base_url' => $fv_api_base_url
                            ]);
                    }
                } else {

                    if ($request->hasFile('image')) {
                        $imageName = time() . '-' . $request->image->getClientOriginalName();
                        $request->image->move(public_path('/assets/uploads/client_logo/'), $imageName);
                        $values = array('tenant_id' => $request->tenant_details->id, 'logo' => $imageName, 'sms_buffer_time' => env('SMS_NOTE_BUFFER_DEFAULT_TIME', '300'), 'created_at' => $current_date);
                    } else {
                        $values = array('tenant_id' => $request->tenant_details->id, 'sms_buffer_time' => env('SMS_NOTE_BUFFER_DEFAULT_TIME', '300'), 'created_at' => $current_date);
                    }

                    $configs = DB::table('config')->where('tenant_id', $request->tenant_details->id)->first();
                    if ($configs == null) {
                        DB::table('config')->insert($values);
                    } else {
                        DB::table('config')->where('tenant_id', $request->tenant_details->id)->update($values);
                    }
                }

                return redirect()->route('portal_display_settings', ['subdomain' => $subdomain])
                    ->with('success', 'Setting saved successfully!')
                    ->with('msg_err', $msg_err);
            }
        } catch (\Exception $ex) {
            Logging::warning($ex->getMessage());
        }
    }


    /**
     * [POST] Save tenant test number setting
     */
    public function save_tenant_test_number_settings(Request $request, $subdomain)
    {
        $test_tfa_number = $request->input('test_tfa_number');
        if (!$test_tfa_number) {
            $test_tfa_number = "";
        }
        $tenant_id = $this->cur_tenant_id;
        $TenantLive = TenantLive::where('tenant_id', $tenant_id)->first();
        if ($TenantLive != null) {
            $TenantLive->update(['test_tfa_number' => $test_tfa_number]);
        } else {
            TenantLive::create(['tenant_id' => $tenant_id, 'test_tfa_number' => $test_tfa_number]);
        }
        return response()->json([
            'status'  => true,
            'message' => 'Setting saved successfully!'
        ]);
    }

    /**
     * [POST] Add Leagal Team Person Config for Admin
     */
    public function legalteam_person(Request $request, $subdomain)
    {
        try {
            $data = [];
            $data['tenant_id'] = $this->cur_tenant_id;
            $data['fv_project_type_id'] = $request->fv_project_type_id;
            $data['fv_project_type_name'] = $request->fv_project_type_name;
            $data['fv_section_id'] = $request->fv_section_id;
            $data['fv_section_name'] = $request->fv_section_name;
            $data['fv_person_field_id'] = $request->fv_person_field_id;
            $data['fv_person_field_name'] = $request->fv_person_field_name;
            $data['override_name'] = $request->override_name ?? "";
            $data['override_phone'] = $request->override_phone ?? "";
            $data['override_email'] = $request->override_email ?? "";
            $data['type'] = $request->type ?? "";
            $person_config = LegalteamPersonConfig::where('tenant_id', $this->cur_tenant_id)->where('fv_section_id', $request->fv_section_id)->where('fv_project_type_id', $request->fv_project_type_id)->where('fv_person_field_id', $request->fv_person_field_id)->first();
            if (isset($person_config) and !empty($person_config) && $data['type'] != 'static') {
                return redirect()->route('portal_display_settings', ['subdomain' => $subdomain])->with('msg_err', "Field is already exist!");
            } else {
                $max = LegalteamPersonConfig::where('tenant_id', $data['tenant_id'])->orderBy('sort_order', 'DESC')->first();
                if (isset($max['sort_order'])) {
                    $data['sort_order'] = $max['sort_order'] + 1;
                }
                LegalteamPersonConfig::create($data);
            }
            return redirect()->route('portal_display_settings', ['subdomain' => $subdomain])
                ->with('success', 'Setting saved successfully!');
        } catch (\Exception $ex) {
            Logging::warning($ex->getMessage());
        }
    }
}
