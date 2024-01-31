<?php

namespace App\Http\Controllers\TenantAdmin;

use Exception;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

use App\Services\FilevineService;

use App\Models\Variable;
use App\Models\VariablePermission;
use App\Models\Tenant;

class VariableController extends Controller
{

    public $cur_tenant_id;

    public function __construct()
    {
        Controller::setSubDomainName();
        $this->cur_tenant_id = session()->get('tenant_id');
    }

    /**
     * [GET] Variable Page for Tenanat Admin
     */
    public function index()
    {
        $data['variables'] = DB::table('variables')->select('variable_permissions.*', 'variables.id as master_id', 'variables.variable_key', 'variables.variable_name')
            ->leftJoin('variable_permissions', 'variables.id', '=', 'variable_permissions.variable_id')
            ->where('variables.is_active', true)
            ->where('variables.is_custom_variable', false)
            ->orderByRaw(DB::raw("FIELD(variables.id, 1,5,2,3,4,6,7)"))
            ->get();

        $data['custom_variables'] = DB::table('variables')->select('variable_permissions.*', 'variables.id as master_id', 'variables.*')
            ->leftJoin('variable_permissions', 'variables.id', '=', 'variable_permissions.variable_id')
            ->where('variables.tenant_id', $this->cur_tenant_id)
            ->where('variables.is_custom_variable', true)
            ->get();

        return $this->_loadContent('admin.pages.variable', $data);
    }

    /**
     *  [GET] Get List of Project Type
     */
    public function getProjectType()
    {
        try {
            $Tenant = Tenant::find($this->cur_tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $fv_service = new FilevineService($apiurl, "");
            $fv_project_type_list = json_decode($fv_service->getProjectTypeList());
            $options = '<option value="">Select Project Type</option>';
            if (isset($fv_project_type_list->items) and !empty($fv_project_type_list->items)) {
                $project_type_lists = $fv_project_type_list->items;
                foreach ($project_type_lists as $project_type) {
                    $options .= '<option value="' . $project_type->projectTypeId->native . '">' . $project_type->name  . '</option>';
                }
            }
            return response()->json([
                'status'  => true,
                'html' => $options
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }


    /**
     *  [POST] Get Section List By Project Type ID
     */
    public function getSection(Request $request)
    {
        try {
            $Tenant = Tenant::find($this->cur_tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $fv_service = new FilevineService($apiurl, "");
            $project_type_id = $request->project_type_id;
            $fv_project_type_section_list = json_decode($fv_service->getProjectTypeSectionList($project_type_id), true);
            $options = '<option value="">Choose Section</option>';
            if (isset($fv_project_type_section_list['items']) and !empty($fv_project_type_section_list['items'])) {
                $sections = collect($fv_project_type_section_list['items']);
                $sections = $sections->where("isCollection", false);
                foreach ($sections as $key => $section) {
                    $options .= '<option value="' . $section['sectionSelector'] . '">' . $section['name'] . '</option>';
                }
            }
            return response()->json([
                'status'  => true,
                'html' => $options
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }


    /**
     *  [GET] Get Project Section Field By Project Type ID & Section Selector
     */
    public function getField(Request $request)
    {
        try {
            $Tenant = Tenant::find($this->cur_tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $fv_service = new FilevineService($apiurl, "");
            $project_type_id = $request->project_type_id;
            $project_section_selector = $request->project_section_selector;
            $fv_project_type_section_field_list = json_decode($fv_service->getProjectTypeSectionFieldList($project_type_id, $project_section_selector), true);
            $options = '<option value="">--Select--</option>';
            if (isset($fv_project_type_section_field_list['customFields']) and !empty($fv_project_type_section_field_list['customFields'])) {
                $customFields = collect($fv_project_type_section_field_list['customFields']);
                $customFields = $customFields->whereIn("customFieldType", ['String', 'Text', 'TextLarge', 'PersonLink', 'PersonList', 'MultiSelectList', 'Date', 'Dropdown', 'Url']);
                foreach ($customFields as $key => $field) {
                    $options .= '<option value="' . $field['fieldSelector'] . '">' . $field['name'] . ' (' . $field['customFieldType'] . ')' . '</option>';
                }
            }
            return response()->json([
                'status'  => true,
                'html' => $options
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }


    /**
     * [POST] Create Variable
     */
    public function addVariable(Request $request)
    {
        try {
            $variable_id = $request->input('variable_id');
            $fv_section_selector = $request->input('fv_section_selector');
            $fv_field_selector = $request->input('fv_field_selector');
            $fv_field_selector_name = $request->input('fv_field_selector_name');
            $variable_key = '[custom.' . $fv_section_selector . '.' . $fv_field_selector . ']';

            $variable = [
                'tenant_id' => $this->cur_tenant_id,
                'is_custom_variable' => 1,
                'variable_name' => $request->input('variable_name'),
                'variable_key' => $variable_key,
                'placeholder' => $request->input('placeholder'),
                'is_active' => true,
                'variable_description' => $request->input('variable_description'),
                'fv_project_type' => $request->input('fv_project_type'),
                'fv_project_type_name' => $request->input('fv_project_type_name'),
                'fv_section_selector' => $request->input('fv_section_selector'),
                'fv_section_selector_name' => $request->input('fv_section_selector_name'),
                'fv_field_selector' => $request->input('fv_field_selector'),
                'fv_field_selector_name' => $fv_field_selector_name,
                'fv_field_selector_type' => substr($fv_field_selector_name, (strrpos($fv_field_selector_name, "(")) + 1,  -1),
            ];

            if (empty($variable_id)) {
                $obj = Variable::create($variable);
                if ($obj->id) {
                    $variable_id = $obj->id;
                }
            } else {
                Variable::where('id', $variable_id)->update($variable);
            }

            if (empty($variable_id)) {
                return redirect(url("/admin/variables"))->with('error', "Unable to add variable at the moment, please try again later!");
            }

            if ($variable_id) {
                $permissions = [
                    'select_all' => !is_null($request->input('select_all')) ? 1 : 0,
                    'is_project_timeline' => !is_null($request->input('is_project_timeline')) ? 1 : 0,
                    'is_timeline_mapping' => !is_null($request->input('is_timeline_mapping')) ? 1 : 0,
                    'is_phase_change_sms' => !is_null($request->input('is_phase_change_sms')) ? 1 : 0,
                    'is_review_request_sms' => !is_null($request->input('is_review_request_sms')) ? 1 : 0,
                    'is_client_banner_message' => !is_null($request->input('is_client_banner_message')) ? 1 : 0,
                    'is_automated_workflow_action' => !is_null($request->input('is_automated_workflow_action')) ? 1 : 0,
                    'is_mass_text' => !is_null($request->input('is_mass_text')) ? 1 : 0,
                    'is_email_template' => !is_null($request->input('is_email_template')) ? 1 : 0,
                ];
                $permission = VariablePermission::where('variable_id', $variable_id)->exists();
                if ($permission) {
                    VariablePermission::where('variable_id', $variable_id)->update($permissions);
                } else {
                    $permissions['variable_id'] = $variable_id;
                    VariablePermission::create($permissions);
                }
            }
            return redirect(url("/admin/variables"))->with('success', "Setting Saved Successfully!");
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }


    /**
     * [POST] Update Variable Permission
     */
    public function updateVariablePermission(Request $request)
    {
        try {
            $variable_id = $request->input('variable_id');
            $permissions = [
                'is_project_timeline' => $request->input('is_project_timeline'),
                'is_timeline_mapping' => $request->input('is_timeline_mapping'),
                'is_phase_change_sms' => $request->input('is_phase_change_sms'),
                'is_review_request_sms' => $request->input('is_review_request_sms'),
                'is_client_banner_message' => $request->input('is_client_banner_message'),
                'is_automated_workflow_action' => $request->input('is_automated_workflow_action'),
                'is_mass_text' => $request->input('is_mass_text'),
                'is_email_template' => $request->input('is_email_template'),
            ];
            $permission = VariablePermission::where('variable_id', $variable_id)->exists();
            if ($permission) {
                VariablePermission::where('variable_id', $variable_id)->update($permissions);
            }
            return Response::json(array('success' => true, 'message' => "Setting Saved Successfully!"));
        } catch (Exception $e) {
            return Response::json(array('success' => false, 'message' => $e->getMessage()));
        }
    }


    /**
     * [POST] Delete Variable for Tenant Admin
     */
    public function deleteVariable($subdomain, $variable_id)
    {
        try {
            $variable =  Variable::where('id', $variable_id)->where('tenant_id', $this->cur_tenant_id)->first();
            if ($variable) {
                VariablePermission::where('variable_id', $variable_id)->delete();
                Variable::find($variable_id)->delete();
                return Response::json(array('success' => true, 'message' => 'Variable Deleted Successfully!'));
            } else {
                return Response::json(array('success' => false, 'message' => 'Unable to Delete Variable at this Moment!'));
            }
        } catch (Exception $e) {
            return Response::json(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    /**
     * [POST] Update Variable Active
     */
    public function updateActive(Request $request)
    {
        try {
            $variable_id = $request->input('variable_id');
            $variable =  Variable::where('id', $variable_id)->first();
            if ($variable) {
                $variable->is_active = $variable->is_active ? false : true;
                $variable->save();
                return Response::json(array('success' => true, 'message' => 'Setting Saved Successfully!'));
            } else {
                return Response::json(array('success' => false, 'message' => 'Unable to Toggle Variable at this Moment!'));
            }
        } catch (Exception $e) {
            return Response::json(array('success' => false, 'message' => $e->getMessage()));
        }
    }
}
