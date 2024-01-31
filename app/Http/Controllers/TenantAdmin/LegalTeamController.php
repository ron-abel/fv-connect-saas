<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Services\FilevineService;
use Illuminate\Http\Request;
use App\Models\LegalteamConfig;
use App\Models\LegalteamPersonConfig;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log as Logging;

class LegalTeamController extends Controller
{
    public $domainName;
    public $cur_tenant_id;

    public function __construct()
    {
        Controller::setSubDomainName();
        $this->domainName = session()->get('subdomain');
        $this->cur_tenant_id = session()->get('tenant_id');
    }

    /**
     * [GET] Legal Team Page for Admin
     */
    public function index()
    {
        try {
            $tenant_id = $this->cur_tenant_id;
            $legal_tem_config_types = [];
            $Tenant = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }

            $obj = new FilevineService($apiurl, "");

            // get project types
            $fv_project_type_list = $obj->getProjectTypeList();
            if ($fv_project_type_list != null) {
                $fv_project_type_list = json_decode($fv_project_type_list, true);
            } else {
                $fv_project_type_list = [];
            }

            $fv_projects = $obj->getProjectsList();
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
                $response = $obj->getTeamOrgRoles($fv_project_id);
                $data = json_decode($response, true);
                if (isset($data['items']) && count($data['items']) > 0) {
                    $legal_tem_config_types = array_values($data['items']);
                }
            }
            $config_details = DB::table('config')->where('tenant_id', $tenant_id)->first();
            $legal_team_by_role = true;
            if (isset($config_details) and !empty($config_details)) {
                $legal_team_by_role = $config_details->is_legal_team_by_roles;
            }
            $data = LegalteamConfig::where('tenant_id', $this->cur_tenant_id)->orderBy('role_order', 'asc')->get();
            $data_person_config = LegalteamPersonConfig::where('tenant_id', $this->cur_tenant_id)->orderBy('sort_order', 'asc')->get();
            $tenant_override_title = DB::table('tenant_override_titles')
                ->where('tenant_id', $this->cur_tenant_id)->first();

            return $this->_loadContent('admin.pages.legal_team', [
                'data'                   => $data,
                'legal_tem_config_types' => $legal_tem_config_types,
                'legal_team_by_role' => $legal_team_by_role,
                'fv_project_type_list' => $fv_project_type_list,
                'tenant_override_title' => $tenant_override_title->title ?? "",
                'data_person_config' => $data_person_config
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
     * [POST] Update legal team person config
     */
    public function update_legalteam_config(Request $request)
    {
        try {
            $tenant_id = $this->cur_tenant_id;
            if ($request->type == "update-data") {
                $fv_project_type_id = $request->fv_project_type_id;
                $fv_section_id = $request->fv_section_id;
                $fv_person_field_id = $request->fv_person_field_id;
                $person_config = LegalteamPersonConfig::where('tenant_id', $this->cur_tenant_id)->where('fv_section_id', $fv_section_id)->where('fv_project_type_id', $fv_project_type_id)->where('fv_person_field_id', $fv_person_field_id)->first();
                if (isset($person_config) and !empty($person_config)) {
                    $data = $request->all();
                    $data['type'] = $data['fetchType'];
                    $person_config->update($data);
                }
            } elseif ($request->type == "delete-data") {
                $fv_project_type_id = $request->fv_project_type_id;
                $fv_section_id = $request->fv_section_id;
                $fv_person_field_id = $request->fv_person_field_id;
                $person_config = LegalteamPersonConfig::where('tenant_id', $this->cur_tenant_id)->where('fv_section_id', $fv_section_id)->where('fv_project_type_id', $fv_project_type_id)->where('fv_person_field_id', $fv_person_field_id)->first();
                if (isset($person_config) and !empty($person_config)) {
                    $person_config->delete();
                }
            } else {
                $config_details = DB::table('config')->where('tenant_id', $tenant_id)->first();
                $is_legal_team_by_roles = true;
                if ($request->type == "person-fields") {
                    $is_legal_team_by_roles = false;
                }
                DB::table('config')->where('tenant_id', $tenant_id)->update(['is_legal_team_by_roles' => $is_legal_team_by_roles]);
            }
            return;
        } catch (\Throwable $th) {
            return $e->getMessage();
        }
    }
    public function update_all_legalteam_config(Request $request)
    {
        try {
            $tenant_id = $this->cur_tenant_id;
            if ($request->title) {
                DB::table('tenant_override_titles')->updateOrInsert(
                    ['tenant_id' => $tenant_id],
                    ['title' => $request->title, "created_at" => date('Y-m-d H:i:s'), "updated_at" => date('Y-m-d H:i:s')]
                );
            }

            if ($request->formData) {
                foreach ($request->formData as $formData) {
                    $fv_project_type_id = $formData['fv_project_type_id'];
                    $fv_section_id = $formData['fv_section_id'];
                    $fv_person_field_id = $formData['fv_person_field_id'];
                    $person_config = LegalteamPersonConfig::where('tenant_id', $this->cur_tenant_id)->where('fv_section_id', $fv_section_id)->where('fv_project_type_id', $fv_project_type_id)->where('fv_person_field_id', $fv_person_field_id)->first();
                    if (isset($person_config) and !empty($person_config)) {
                        $data = $formData;
                        $data['type'] = $formData['fetchType'];
                        $person_config->update($data);
                    }
                }
            }

            if($request->settings_details){
                DB::table('config')->where('tenant_id',$tenant_id)->updateOrInsert(
                    ['tenant_id' => $tenant_id],
                    ['is_legal_team_by_roles' => $request->is_legal_team_by_roles, "created_at" => date('Y-m-d H:i:s'), "updated_at" => date('Y-m-d H:i:s')]
                );
            }
            return;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    /**
     * [GET] Get Project Section
     */
    public function get_project_section($subdomain, $type_id)
    {
        try {
            $tenant_id = $this->cur_tenant_id;
            $Tenant = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $obj = new FilevineService($apiurl, "");
            // get project types sections
            $fv_project_type_section_list = $obj->getProjectTypeSectionList($type_id);
            if ($fv_project_type_section_list != null) {
                $fv_project_type_section_list = json_decode($fv_project_type_section_list, true);
            }
            $html = '<option value="">Select Project Type Section</option>';
            if (isset($fv_project_type_section_list['items']) and !empty($fv_project_type_section_list['items'])) {
                $sections = collect($fv_project_type_section_list['items']);
                $sectionsWithIsCollection = $sections->where("isCollection", false);
                foreach ($sectionsWithIsCollection as $key => $section) {
                    $html .= '<option value="' . $section['sectionSelector'] . '">' . $section['name'] . '</option>';
                }
            }
            echo $html;
            return;
        } catch (\Throwable $th) {
            return $e->getMessage();
        }
    }

    /**
     * [GET] Get Project Section Field
     */
    public function get_project_section_field($subdomain, $type_id, $selectionFilter)
    {
        try {
            $tenant_id = $this->cur_tenant_id;
            $legal_tem_config_types = [];
            $Tenant = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $obj = new FilevineService($apiurl, "");

            // get project types sections field selectors
            $fv_project_type_section_field_list = $obj->getProjectTypeSectionFieldList($type_id, $selectionFilter);
            if ($fv_project_type_section_field_list != null) {
                $fv_project_type_section_field_list = json_decode($fv_project_type_section_field_list, true);
            }
            $html = '<option value="">Select a person field</option>';
            if (isset($fv_project_type_section_field_list['customFields']) and !empty($fv_project_type_section_field_list['customFields'])) {
                $customFields = collect($fv_project_type_section_field_list['customFields']);
                $customFieldsPerson = $customFields->where("customFieldType", "PersonLink");
                foreach ($customFieldsPerson as $key => $field) {
                    $html .= '<option value="' . $field['fieldSelector'] . '">' . $field['name'] . '</option>';
                }
            }
            echo $html;
            return;
        } catch (\Throwable $th) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Add Leagal Team Person Config for Admin
     */
    public function legalteam_person(Request $request)
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
                return redirect(url("/admin/legal_team"))->with('error', "Field is already exist!");
            } else {
                $max = LegalteamPersonConfig::where('tenant_id', $data['tenant_id'])->orderBy('sort_order', 'DESC')->first();
                if (isset($max['sort_order'])) {
                    $data['sort_order'] = $max['sort_order'] + 1;
                }
                LegalteamPersonConfig::create($data);
            }
            return redirect(url("/admin/legal_team"))->with('success', "Setting saved successfully!");;
        } catch (\Throwable $th) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Add Leagal Team Data for Admin
     */
    public function store(Request $request)
    {
        try {
            $tenant_id = $request->tenant_details->id;
            $legalteamConfig = LegalteamConfig::where('id', $request->id)->first();
            if (empty($legalteamConfig)) {
                $legalteamConfig = new LegalteamConfig();
                $legalteamConfig->is_active = 1;

                // max role order.
                $max = LegalteamConfig::where('tenant_id', $tenant_id)->orderBy('role_order', 'DESC')->first();
                if (isset($max['role_order'])) {
                    $legalteamConfig->role_order = $max['role_order'] + 1;
                };
            }

            // validate role id. with the same : role_title, tenant,
            $same_legal_roles = LegalteamConfig::where('tenant_id', $tenant_id)
                ->where('type', $request->type)
                ->where('role_title', $request->role_title);
            if (isset($legalteamConfig->id)) {
                $same_legal_roles = $same_legal_roles->where('id', '!=',  $legalteamConfig->id);
            }
            $same_legal_roles = $same_legal_roles->get();
            if (count($same_legal_roles) > 0) {
                // already exist the same role type.
                return response()->json([
                    'status'  => false,
                    'message' => "The same Role Title is already exist!",
                ]);
            }

            $legalteamConfig->tenant_id = $request->tenant_details->id;
            $legalteamConfig->type = $request->type;
            $legalteamConfig->role_title = $request->role_title;
            $legalteamConfig->is_enable_feedback = !empty($request->enable_feedback) ? LegalteamConfig::YES : LegalteamConfig::NO;
            if ($request->type == LegalteamConfig::TYPE_FETCH) {
                $legalteamConfig->fv_role_id = $request->role;
                $legalteamConfig->is_follower_required = !empty($request->follower_required) ? LegalteamConfig::YES : LegalteamConfig::NO;
                $legalteamConfig->is_enable_email = !empty($request->enable_email) ? LegalteamConfig::YES : LegalteamConfig::NO;
            } else {
                $legalteamConfig->name = $request->role_name;
                $legalteamConfig->email = $request->email;
                $legalteamConfig->phone = $request->phone;
            }
            $legalteamConfig->save();
            $legalteamConfig_id  = $legalteamConfig->id;

            return response()->json([
                'status'  => true,
                'message' => 'Setting saved successfully!',
                'legalteamConfig_id' => $legalteamConfig_id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    //org save
    public function storeAll(Request $request)
    {
        $tenant_id = $request->tenant_details->id;
        $errormsg = '';
        $LegalteamConfigData = [];

        if ($request->title) {
            DB::table('tenant_override_titles')->updateOrInsert(
                ['tenant_id' => $tenant_id],
                ['title' => $request->title, "created_at" => date('Y-m-d H:i:s'), "updated_at" => date('Y-m-d H:i:s')]
            );
        }

        if ($request->is_legal_team_by_roles) {
            DB::table('config')->updateOrInsert(
                ['tenant_id' => $tenant_id],
                ['is_legal_team_by_roles' => $request->is_legal_team_by_roles, "created_at" => date('Y-m-d H:i:s'), "updated_at" => date('Y-m-d H:i:s')]
            );
        }


        foreach ($request->formData as $data) {
            $LegalteamConfigData['tenant_id'] = $request->tenant_details->id;
            $LegalteamConfigData['type'] = $data['type'];
            $LegalteamConfigData['role_title'] = $data['role_title'];

            $LegalteamConfigData['is_enable_feedback'] = !empty($data['enable_feedback']) ? LegalteamConfig::YES : LegalteamConfig::NO;
            if ($data['type'] == LegalteamConfig::TYPE_FETCH) {
                $LegalteamConfigData['fv_role_id'] = $data['role'];
                $LegalteamConfigData['is_follower_required'] = !empty($data['follower_required']) ? LegalteamConfig::YES : LegalteamConfig::NO;
                $LegalteamConfigData['is_enable_email'] = !empty($data['enable_email']) ? LegalteamConfig::YES : LegalteamConfig::NO;
            } else {
                $LegalteamConfigData['name'] = $data['role_name'];
                $LegalteamConfigData['email'] = $data['email'];
                $LegalteamConfigData['phone'] = $data['phone'];
            }

            $legalteamConfig = LegalteamConfig::where('id', $data['id'])->first();


            if (empty($legalteamConfig)) {
                $LegalteamConfigData['is_active'] = 1;
                // max role order.
                $max = LegalteamConfig::where('tenant_id', $tenant_id)->orderBy('role_order', 'DESC')->first();
                if (isset($max['role_order'])) {
                    $LegalteamConfigData['role_order'] = $max['role_order'] + 1;
                };
            }
            $same_legal_roles = LegalteamConfig::where('tenant_id', $tenant_id)
                ->where('type', $data['type'])
                ->where('role_title', $data['role_title']);
            if (isset($legalteamConfig['id'])) {
                $same_legal_roles = $same_legal_roles->where('id', '!=',  $legalteamConfig['id']);
            }
            $same_legal_roles = $same_legal_roles->get();

            if (count($same_legal_roles) > 0) {
                // already exist the same role type.
                $errormsg .= $data['role_title'] . ' The same Role Title is already exist!';
            }
            if (empty($legalteamConfig)) {
                if (count($same_legal_roles) == 0) {
                    $LegalteamConfigData['created_at'] = date('Y-m-d');
                    LegalteamConfig::insert($LegalteamConfigData);
                }
            } else {
                $LegalteamConfigData['updated_at'] = date('Y-m-d');
                LegalteamConfig::where('id', $data['id'])->update($LegalteamConfigData);
            }
        }
        if (isset($errormsg) and !empty($errormsg)) {
            return response()->json([
                'status'  => false,
                'message' => $errormsg,
            ]);
        } else {
            return response()->json([
                'status'  => true,
                'message' => 'Setting saved successfully!'
            ]);
        }
    }

    /**
     * Remove record
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        LegalteamConfig::where('id', $request->id)->delete();
        return response()->json([
            'status'  => true,
            'message' => 'Setting saved successfully!',
        ]);
    }

    /**
     * Sort record
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sortable(Request $request)
    {
        if (is_array($request->data) && count($request->data) > 0) {
            $collection = collect($request->data);
            $legalteamConfig = LegalteamConfig::whereIn('id', $collection->pluck('id')->toArray())->get();
            DB::beginTransaction();
            try {
                foreach ($legalteamConfig as $item) {
                    $item->role_order = $collection->where('id', $item->id)->pluck('index')->first();
                    $item->save();
                }
                DB::commit();
                return response()->json([
                    'status'  => true,
                    'message' => 'Setting saved successfully!',
                ]);
            } catch (\Exception $exception) {
                DB::rollBack();
                return response()->json([
                    'status'  => false,
                    'message' => $exception->getMessage(),
                ]);
            }
        }
    }

    /**
     * Sort record
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function person_sortable(Request $request)
    {
        if (is_array($request->data) && count($request->data) > 0) {
            $collection = collect($request->data);
            $legalteamConfig = LegalteamPersonConfig::whereIn('id', $collection->pluck('id')->toArray())->get();
            DB::beginTransaction();
            try {
                foreach ($legalteamConfig as $item) {
                    $item->sort_order = $collection->where('id', $item->id)->pluck('index')->first();
                    $item->save();
                }
                DB::commit();
                return response()->json([
                    'status'  => true,
                    'message' => 'Setting saved successfully!',
                ]);
            } catch (\Exception $exception) {
                DB::rollBack();
                return response()->json([
                    'status'  => false,
                    'message' => $exception->getMessage(),
                ]);
            }
        }
    }
}
