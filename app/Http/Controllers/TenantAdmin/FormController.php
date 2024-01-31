<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantForm;
use App\Models\TenantFormMapping;
use App\Models\TenantFormResponse;
use App\Services\FilevineService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ExportService;
use Illuminate\Support\Facades\Log as Logging;

class FormController extends Controller
{
    private $cur_tenant_id;
    public function __construct()
    {
        Controller::setSubDomainName();
        $this->cur_tenant_id = session()->get('tenant_id');
    }

    public function index()
    {
        try {
            $forms = TenantForm::where('tenant_id', $this->cur_tenant_id)->latest()->get();
            if (count($forms) > 0) {
                foreach ($forms as $key => $form) {
                    $forms[$key]->responses = TenantFormResponse::where('tenant_form_id', $form->id)->get()->count();
                }
            }
            $logs = TenantForm::select('tenant_forms.id', 'tenant_forms.form_name', 'tenant_form_responses.fv_client_id', 'tenant_form_responses.fv_project_id', 'tenant_form_responses.created_at')
                ->join('tenant_form_responses', 'tenant_forms.id', '=', 'tenant_form_responses.tenant_form_id')
                ->where('tenant_id', $this->cur_tenant_id)->latest()->get();
            return $this->_loadContent('admin.pages.forms', ['forms' => $forms, 'logs' => $logs]);
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
    public function form($subdomain, $id = null)
    {
        try {

            $Tenant = Tenant::find($this->cur_tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $fv_service = new FilevineService($apiurl, "");
            $fv_project_type_list = json_decode($fv_service->getProjectTypeList());
            if (isset($fv_project_type_list->items) and !empty($fv_project_type_list->items)) {
                $project_type_lists = $fv_project_type_list->items;
            } else {
                $project_type_lists = [];
            }

            if ($id != null) {
                try {
                    $form = TenantForm::where('id', $id)->first();
                    $form_mappings = TenantFormMapping::where('form_id', $id)->get();
                    $form_mappings_count = $form_mappings->count();
                    return $this->_loadContent('admin.pages.form_builder', ['form' => $form, 'project_type_lists' => $project_type_lists, 'form_mappings' => $form_mappings, 'form_mappings_count' => $form_mappings_count]);
                } catch (Exception $ex) {
                    return $ex->getMessage();
                }
            } else {

                return $this->_loadContent('admin.pages.form_builder', ['project_type_lists' => $project_type_lists]);
            }
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }
    public function save_form_data(Request $request)
    {
        $request->validate([
            'name' => 'required | min:4',
            'form_data' => 'required'
        ]);
        try {

            $url = request()->getHost() . '/form?tenant_id=' . $this->cur_tenant_id . '&name=' . (isset($request['name']) ? $request['name'] : '');
            if (isset($request['form_id']) && !is_null($request['form_id'])) {
                $res = TenantForm::where('id', $request['form_id'])
                    ->update([
                        'form_name' => (isset($request['name']) ? $request['name'] : ''),
                        'form_url' => $url,
                        'form_description' => $request['description'],
                        'form_fields_json' => $request['form_data'],
                        'is_active' => $request['is_active'],
                        'is_public_form' => $request['is_public_form'],
                        'create_fv_project' => $request['create_fv_project'],
                        'fv_project_type_id' => $request['fv_project_type_id'],
                        'fv_project_type_name' => $request['fv_project_type_id_name'],
                        'success_message' => $request['success_message'],
                        'sync_existing_fv_project' => $request['sync_existing_fv_project'],
                        'fv_project_id' => $request['fv_project_id'],
                        'fv_project_name' => $request['fv_project_name'],
                        'assign_project_name_as' => $request['assign_project_name_as'],
                    ]);

                $form_id = $request['form_id'];
                TenantFormMapping::where('form_id', $form_id)->delete();

                //Save form mapping data into
                $form_data = json_decode($request['form_data'], true);
                $form_mapping_enable = json_decode($request['form_mapping_enable'], true);
                $fv_project_type_name = json_decode($request['fv_project_type_name'], true);
                $fv_section_name = json_decode($request['fv_section_name'], true);
                $fv_field_name = json_decode($request['fv_field_name'], true);

                $counter = 0;
                $section_type = "static";
                $collection_item_index = 0;
                for ($i = 0; $i < count($form_data); $i++) {

                    if (isset($form_data[$i]['className']) && $form_data[$i]['className'] == 'collection-section-start') {
                        $section_type = "collection";
                    }

                    if (isset($form_data[$i]['className']) && $form_data[$i]['className'] == 'collection-section-end') {
                        $section_type = "static";
                        $collection_item_index += 1;
                    }

                    if (isset($form_data[$i]['name'])) {
                        TenantFormMapping::create([
                            'form_id' => $form_id,
                            'form_item_name' => $form_data[$i]['name'],
                            'form_item_type' => $form_data[$i]['type'],
                            'form_item_label' => $form_data[$i]['label'],
                            'fv_project_type_id' => isset($fv_project_type_name[$counter]['fv_project_type_id']) ? $fv_project_type_name[$counter]['fv_project_type_id'] : '',
                            'form_mapping_enable' => isset($form_mapping_enable[$counter]['form_mapping_enable']) ? $form_mapping_enable[$counter]['form_mapping_enable'] : 0,
                            'fv_project_type_name' => isset($fv_project_type_name[$counter]['fv_project_type_name']) ? $fv_project_type_name[$counter]['fv_project_type_name'] : '',
                            'section_type' => $section_type,
                            'fv_section_id' => isset($fv_section_name[$counter]['fv_section_id']) ? $fv_section_name[$counter]['fv_section_id'] : '',
                            'fv_section_name' => isset($fv_section_name[$counter]['fv_section_name']) ? $fv_section_name[$counter]['fv_section_name'] : '',
                            'fv_field_id' => isset($fv_field_name[$counter]['fv_field_id']) ? $fv_field_name[$counter]['fv_field_id'] : '',
                            'fv_field_name' => isset($fv_field_name[$counter]['fv_field_name']) ? $fv_field_name[$counter]['fv_field_name'] : '',
                            'collection_item_index' => ($section_type == 'static') ? null : $collection_item_index
                        ]);
                        $counter++;
                    }
                }

                return json_encode($res);
            } else {
                $res = TenantForm::create([
                    'tenant_id' => $this->cur_tenant_id,
                    'form_url' => $url,
                    'form_name' => (isset($request['name']) ? $request['name'] : ''),
                    'form_description' => $request['description'],
                    'form_fields_json' => $request['form_data'],
                    'is_active' => $request['is_active'],
                    'is_public_form' => $request['is_public_form'],
                    'create_fv_project' => $request['create_fv_project'],
                    'fv_project_type_id' => $request['fv_project_type_id'],
                    'fv_project_type_name' => $request['fv_project_type_id_name'],
                    'success_message' => $request['success_message'],
                    'sync_existing_fv_project' => $request['sync_existing_fv_project'],
                    'fv_project_id' => $request['fv_project_id'],
                    'fv_project_name' => $request['fv_project_name'],
                    'assign_project_name_as' => $request['assign_project_name_as'],
                ]);

                //Save form mapping data into
                $form_data = json_decode($request['form_data'], true);
                $form_mapping_enable = json_decode($request['form_mapping_enable'], true);
                $fv_project_type_name = json_decode($request['fv_project_type_name'], true);
                $fv_section_name = json_decode($request['fv_section_name'], true);
                $fv_field_name = json_decode($request['fv_field_name'], true);
                $counter = 0;
                $section_type = "static";
                $collection_item_index = 0;
                for ($i = 0; $i < count($form_data); $i++) {

                    if (isset($form_data[$i]['className']) && $form_data[$i]['className'] == 'collection-section-start') {
                        $section_type = "collection";
                    }

                    if (isset($form_data[$i]['className']) && $form_data[$i]['className'] == 'collection-section-end') {
                        $section_type = "static";
                        $collection_item_index += 1;
                    }

                    if (isset($form_data[$i]['name'])) {
                        TenantFormMapping::create([
                            'form_id' => $res->id,
                            'form_item_name' => $form_data[$i]['name'],
                            'form_item_type' => $form_data[$i]['type'],
                            'form_item_label' => $form_data[$i]['label'],
                            'form_mapping_enable' => isset($form_mapping_enable[$counter]['form_mapping_enable']) ? $form_mapping_enable[$counter]['form_mapping_enable'] : 0,
                            'fv_project_type_id' => isset($fv_project_type_name[$counter]['fv_project_type_id']) ? $fv_project_type_name[$counter]['fv_project_type_id'] : '',
                            'fv_project_type_name' => isset($fv_project_type_name[$counter]['fv_project_type_name']) ? $fv_project_type_name[$counter]['fv_project_type_name'] : '',
                            'section_type' => $section_type,
                            'fv_section_id' => isset($fv_section_name[$counter]['fv_section_id']) ? $fv_section_name[$counter]['fv_section_id'] : '',
                            'fv_section_name' => isset($fv_section_name[$counter]['fv_section_name']) ? $fv_section_name[$counter]['fv_section_name'] : '',
                            'fv_field_id' => isset($fv_field_name[$counter]['fv_field_id']) ? $fv_field_name[$counter]['fv_field_id'] : '',
                            'fv_field_name' => isset($fv_field_name[$counter]['fv_field_name']) ? $fv_field_name[$counter]['fv_field_name'] : '',
                            'collection_item_index' => ($section_type == 'static') ? null : $collection_item_index
                        ]);
                        $counter++;
                    }
                }

                return json_encode($res);
            }
        } catch (Exception $ex) {
            $error = [
                __FILE__,
                __LINE__,
                $ex->getMessage()
            ];
            Logging::warning(json_encode($error));
            return json_encode($error);
        }
    }
    public function get_forms()
    {
        try {
            $forms = TenantForm::where('tenant_id', $this->cur_tenant_id)->latest()->get();
            return json_encode(['status' => 200, 'forms' => $forms]);
        } catch (Exception $ex) {
            return json_encode(['status' => 404]);
        }
    }
    public function toggle_form_eligibility(Request $request)
    {
        try {
            if (!empty($request['form_id']) && ($request['is_active'] == 1 || $request['is_active'] == 0)) {
                $res = TenantForm::where('id', $request['form_id'])
                    ->update(['is_active' => $request['is_active']]);
                if ($res == 1) {
                    return response()->json([
                        'success' => true,
                        'value' => $request['is_active'],
                        'message' => 'The form eligibility has been updated successfully!'
                    ], 200);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Something went wrong. Please try again later.'
                    ], 400);
                }
            }
        } catch (Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage()
            ], 400);
        }
    }
    public function view_form($subdomain, $id = null)
    {
        if ($id != null) {
            try {
                $form = TenantForm::where('id', $id)->first();
                return $this->_loadContent('admin.pages.form_view', ['form_id' => $id, 'form_name' => $form->form_name]);
            } catch (Exception $ex) {
                return $ex->getMessage();
            }
        } else {
            return $this->_loadContent('admin.pages.forms');
        }
    }
    public function form_responses($subdomain, $id = null)
    {
        if ($id != null) {
            try {
                $response1 = DB::table('tenant_form_responses as tfr')
                    ->select('tfr.*', 'fc.fv_client_name as client_name')
                    ->join('fv_clients as fc', 'fc.fv_client_id', '=', 'tfr.fv_client_id')
                    ->join('tenant_forms as tf', 'tf.id', '=', 'tfr.tenant_form_id')
                    ->where('tfr.tenant_form_id', $id)
                    ->where('tf.is_active', 1)
                    ->where('tf.deleted_at', null)
                    ->orderBy('tfr.created_at', 'DESC')
                    ->get();
                $response2 = DB::table('tenant_form_responses as tfr')
                    ->select('tfr.*', DB::raw('"" as client_name'))
                    ->join('tenant_forms as tf', 'tf.id', '=', 'tfr.tenant_form_id')
                    ->where('tfr.fv_client_id', 0)
                    ->where('tfr.tenant_form_id', $id)
                    ->where('tf.is_active', 1)
                    ->where('tf.deleted_at', null)
                    ->orderBy('tfr.created_at', 'DESC')
                    ->get();
                $responses = $response1->merge($response2);

                if (count($responses) > 0) {
                    $Tenant = Tenant::find($this->cur_tenant_id);
                    $apiurl = config('services.fv.default_api_base_url');
                    if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                        $apiurl = $Tenant->fv_api_base_url;
                    }
                    $filevine_api = new FilevineService($apiurl, "");
                    foreach ($responses as $key => $response) {
                        if (!empty($response->fv_project_id) && empty($response->fv_project_name)) {
                            $project = json_decode($filevine_api->getProjectsById($response->fv_project_id));
                            $responses[$key]->project_name = isset($project->projectName) ? $project->projectName : '';
                            TenantFormResponse::where('id', $response->id)->update([
                                'fv_project_name' => isset($project->projectName) ? $project->projectName : '',
                            ]);
                        } else {
                            $responses[$key]->project_name = $response->fv_project_name;
                        }
                    }
                }
                $form = TenantForm::find($id);

                return $this->_loadContent('admin.pages.form_responses', ['responses' => $responses, 'form_name' => $form->form_name]);
            } catch (Exception $ex) {
                return $ex->getMessage();
            }
        } else {
            return $this->_loadContent('admin.pages.form_builder');
        }
    }

    public function delete_form(Request $request)
    {
        try {
            $res = TenantForm::where('id', $request['form_id'])
                ->delete();
            if ($res == 1) {
                return response()->json([
                    'success' => true,
                    'message' => 'The form has been trashed successfully!'
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong. Please try again later.'
                ], 400);
            }
        } catch (Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage()
            ], 400);
        }
    }

    /**
     * [GET] Form Response Log
     */
    public function formResponseLog(Request $request)
    {
        $log_start_date = $request->log_start_date;
        $log_end_date = $request->log_end_date;

        $logs = TenantForm::select('tenant_forms.id', 'tenant_forms.form_name', 'tenant_form_responses.fv_client_id', 'tenant_form_responses.fv_project_id', 'tenant_form_responses.created_at', 'tenant_form_responses.form_response_values_json')
            ->join('tenant_form_responses', 'tenant_forms.id', '=', 'tenant_form_responses.tenant_form_id')
            ->where('tenant_id', $this->cur_tenant_id)
            ->where('tenant_form_responses.created_at', '>=', $log_start_date)
            ->where('tenant_form_responses.created_at', '<=', $log_end_date . ' 23:59:59')
            ->latest()->get();

        return response()->json([
            'data' => $logs,
            'status' => true,
        ]);
    }

    /**
     * [GET] Form Response Log
     */
    public function formResponseLogCsv(Request $request)
    {
        $log_start_date = $request->log_start_date;
        $log_end_date = $request->log_end_date;

        $log_start_date = $request->log_start_date;
        $log_end_date = $request->log_end_date;

        $service = new ExportService();
        return $service->exportFormResponseCSV($this->cur_tenant_id, $log_start_date, $log_end_date);
    }

    /**
     *  [POST] Get Section List By Project Type ID
     */
    public function getSectionListByProjectType(Request $request)
    {
        try {
            $Tenant = Tenant::find($this->cur_tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $fv_service = new FilevineService($apiurl, "");

            $project_type_id = $request->project_type_id;
            $is_collection = $request->is_collection;

            $fv_project_type_section_list = json_decode($fv_service->getProjectTypeSectionList($project_type_id), true);
            $options = '<option value="">Select Section</option>';
            if (isset($fv_project_type_section_list['items']) and !empty($fv_project_type_section_list['items'])) {
                $sections = collect($fv_project_type_section_list['items']);
                $sections = $sections->where("isCollection", $is_collection == 'true' ? true : false);
                foreach ($sections as $key => $section) {
                    $options .= '<option value="' . $section['sectionSelector'] . '">' . $section['name'] . '</option>';
                }
            }
            return response()->json([
                'status'  => true,
                'sections' => $fv_project_type_section_list['items'],
                'html' => $options
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     *  [GET] Get Project Section By Project Type ID
     */
    public function getProjectSectionField(Request $request)
    {
        try {
            $Tenant = Tenant::find($this->cur_tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $fv_service = new FilevineService($apiurl, "");

            $mapping_types = [
                'text' => [
                    'String'
                ],
                'number' => [
                    'Integer', 'Currency'
                ],
                'date' => [
                    'Date'
                ],
                'select' => [
                    'Dropdown', 'MultiSelectList'
                ],
                'radio-group' => [
                    'Dropdown',
                ],
                'radio-group-boolean' => [
                    'Boolean'
                ],
                'checkbox-group' => [
                    'MultiSelectList'
                ],
                'textarea' => [
                    'StringList', 'Text', 'TextLarge'
                ],
                'header' => [
                    'Header'
                ],
            ];

            $form_item_type = trim($request->form_item_type);
            $project_type_id = $request->project_type_id;
            $project_section_selector = $request->project_section_selector;
            $current_mapping_type = $request->field_type;
            $customFields = [];
            if(empty($current_mapping_type)) {
                $current_mapping_type = $mapping_types[$form_item_type];
            }
            $fv_project_type_section_field_list = json_decode($fv_service->getProjectTypeSectionFieldList($project_type_id, $project_section_selector), true);
            $options = '<option value="">Select Field</option>';
            if (isset($fv_project_type_section_field_list['customFields']) and !empty($fv_project_type_section_field_list['customFields'])) {
                $customFields = collect($fv_project_type_section_field_list['customFields']);
                $customFieldsConditions = $customFields->whereIn("customFieldType", $current_mapping_type);
                foreach ($customFieldsConditions as $key => $field) {
                    $options .= '<option value="' . $field['fieldSelector'] . '">' . $field['name'] . ' (' . $field['customFieldType'] . ')'  . '</option>';
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
     *  [GET] Get List of Project by Project Type
     */
    public function getProjectListByProjectType(Request $request)
    {
        try {
            $Tenant = Tenant::find($this->cur_tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $fv_service = new FilevineService($apiurl, "");
            $project_type_id = $request->project_type_id;
            $options = '<option value="">Choose Project</option>';
            $offset = 0;
            $limit = 1000;
            do {
                $projects_object = json_decode($fv_service->getProjectsList($limit, $offset));
                if (isset($projects_object->items)) {
                    foreach ($projects_object->items as $item) {
                        if ($item->projectTypeId->native == $project_type_id) {
                            $options .= '<option value="' . $item->projectId->native . '">' . $item->projectName  . '</option>';
                        }
                    }
                }
                $hasMore = isset($projects_object->hasMore) ? $projects_object->hasMore : false;
                $offset += $limit;
            } while ($hasMore);

            return response()->json([
                'status'  => true,
                'html' => $options
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
