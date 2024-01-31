<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as Logging;

use App\Services\FilevineService;

use App\Models\Tenant;
use App\Models\CalendarSetting;
use App\Models\CalendarSettingSectionField;

class CalendarController extends Controller
{
    public $cur_tenant_id;

    public function __construct()
    {
        try {
            Controller::setSubDomainName();
            $this->cur_tenant_id = session()->get('tenant_id');
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * [GET] Calendar Page for Admin
     */
    public function index()
    {
        try {

            $tenant_id = $this->cur_tenant_id;
            $data['calendar_setting'] = $calendar_setting = CalendarSetting::where('tenant_id', $tenant_id)->first();
            $data['calendar_setting_section_fields'] = [];
            if (isset($calendar_setting->id)) {
                $data['calendar_setting_section_fields'] = CalendarSettingSectionField::where('calendar_setting_id', $calendar_setting->id)->pluck('field_id')->toArray();
            }

            $Tenant = Tenant::find($this->cur_tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $fv_service = new FilevineService($apiurl, "");

            $project_type_list = [];
            $fv_project_type_list = json_decode($fv_service->getProjectTypeList());
            if (isset($fv_project_type_list->items) and !empty($fv_project_type_list->items)) {
                $project_type_list = $fv_project_type_list->items;
            }
            $data['project_type_list'] = $project_type_list;

            $data['collection_sections'] = [];
            $data['collection_section_fields'] = [];
            $data['collection_section_display_fields'] = [];

            if (isset($calendar_setting->id)) {
                $fv_project_type_section_list = json_decode($fv_service->getProjectTypeSectionList($calendar_setting->project_type_id), true);
                if (isset($fv_project_type_section_list['items']) and !empty($fv_project_type_section_list['items'])) {
                    $sections = collect($fv_project_type_section_list['items']);
                    $data['collection_sections'] = $sections->where("isCollection", true);
                }

                $fv_project_type_section_field_list = json_decode($fv_service->getProjectTypeSectionFieldList($calendar_setting->project_type_id, $calendar_setting->collection_section_id), true);
                if (isset($fv_project_type_section_field_list['customFields']) and !empty($fv_project_type_section_field_list['customFields'])) {
                    $customFields = collect($fv_project_type_section_field_list['customFields']);
                    /* if (isset($calendar_setting->sync_feedback_type) && $calendar_setting->sync_feedback_type == '1') {
                        $eligibleCustomFieldType = ['Text', 'Date'];
                    } else {
                        $eligibleCustomFieldType = ['String', 'Text', 'TextLarge', 'Date'];
                    }
                    $data['collection_section_fields'] = $customFields->whereIn("customFieldType", $eligibleCustomFieldType); */

                    $data['collection_section_fields'] = $customFields;
                    $ignoreCustomFieldType = ['Header', 'DocList', 'ActionButton', 'Instructions', 'MultiDocGen', 'Doc'];
                    $data['collection_section_display_fields'] = $customFields->whereNotIn("customFieldType", $ignoreCustomFieldType);
                }
            }

            return $this->_loadContent("admin.pages.calendar", $data);
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
            $fv_project_type_section_list = json_decode($fv_service->getProjectTypeSectionList($project_type_id), true);
            $options = '<option value="">Choose Section</option>';
            if (isset($fv_project_type_section_list['items']) and !empty($fv_project_type_section_list['items'])) {
                $sections = collect($fv_project_type_section_list['items']);
                $sections = $sections->where("isCollection", true);
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

            $project_type_id = $request->project_type_id;
            $collection_section_id = $request->collection_section_id;
            $sync_feedback_type = $request->sync_feedback_type;
            $fv_project_type_section_field_list = json_decode($fv_service->getProjectTypeSectionFieldList($project_type_id, $collection_section_id), true);
            $options = '<option value="">Choose Field</option>';
            $display_field_options = '<option value="">Choose Field</option>';
            if (isset($fv_project_type_section_field_list['customFields']) and !empty($fv_project_type_section_field_list['customFields'])) {
                $customFields = collect($fv_project_type_section_field_list['customFields']);
                $ignoreCustomFieldType = ['Header', 'DocList', 'ActionButton', 'Instructions', 'MultiDocGen', 'Doc'];
                $collection_section_display_fields = $customFields->whereNotIn("customFieldType", $ignoreCustomFieldType);
                foreach ($collection_section_display_fields as $key => $field) {
                    $display_field_options .= '<option value="' . $field['fieldSelector'] . '">' . $field['name'] . ' (' . $field['customFieldType'] . ')' . '</option>';
                }
                /*  if ($sync_feedback_type == '1') {
                    $eligibleCustomFieldType = ['Text', 'Date'];
                } else {
                    $eligibleCustomFieldType = ['String', 'Text', 'TextLarge', 'Date'];
                }
                $customFields = $customFields->whereIn("customFieldType", $eligibleCustomFieldType); */

                foreach ($customFields as $key => $field) {
                    $options .= '<option value="' . $field['fieldSelector'] . '">' . $field['name'] . ' (' . $field['customFieldType'] . ')' . '</option>';
                }
            }
            return response()->json([
                'status'  => true,
                'html' => $options,
                'display_field_options' => $display_field_options
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }



    /**
     *  [POST] Save Calendar Setting Data
     */
    public function save(Request $request)
    {
        try {
            $tenant_id = $this->cur_tenant_id;
            $calendar_setting = CalendarSetting::where('tenant_id', $tenant_id)->first();
            if ($calendar_setting == null) {
                $calendar_setting = new CalendarSetting();
                $calendar_setting->tenant_id = $tenant_id;
                $calendar_setting->save();
                $calendar_setting = CalendarSetting::where('tenant_id', $tenant_id)->first();
            }

            if (isset($request->calendar_visibility) && $request->calendar_visibility == 'on') {
                $calendar_setting->calendar_visibility = true;
                $calendar_setting->collect_appointment_feedback = isset($request->collect_appointment_feedback) && $request->collect_appointment_feedback == 'on' ? true : false;
                $calendar_setting->feedback_type = $request->feedback_type;
                $calendar_setting->sync_feedback_type = $request->sync_feedback_type;
                $calendar_setting->project_type_id = $request->project_type_id;
                $calendar_setting->collection_section_id = $request->collection_section_id;
                $calendar_setting->display_as = isset($request->display_as) && $request->display_as == 'on' ? true : false;
                $calendar_setting->display_item_collection_section_id = $request->display_item_collection_section_id;

                $field_id = $request->field_id;
                if (!empty($field_id)) {

                    $Tenant = Tenant::find($this->cur_tenant_id);
                    $apiurl = config('services.fv.default_api_base_url');
                    if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                        $apiurl = $Tenant->fv_api_base_url;
                    }
                    $fv_service = new FilevineService($apiurl, "");

                    $customFieldData = [];
                    $fv_project_type_section_field_list = json_decode($fv_service->getProjectTypeSectionFieldList($request->project_type_id, $request->collection_section_id), true);
                    if (isset($fv_project_type_section_field_list['customFields']) and !empty($fv_project_type_section_field_list['customFields'])) {
                        $customFields = collect($fv_project_type_section_field_list['customFields']);
                        //$eligibleCustomFieldType = ['String', 'Text', 'TextLarge', 'Date'];
                        //$customFields = $customFields->whereIn("customFieldType", $eligibleCustomFieldType);
                        foreach ($customFields as $key => $field) {
                            $customFieldData[$field['fieldSelector']] = ['name' => $field['name'], 'type' => $field['customFieldType']];
                        }
                    }

                    CalendarSettingSectionField::where('calendar_setting_id', $calendar_setting->id)->delete();
                    foreach ($field_id as $val) {
                        $calendar_setting_section_field = new CalendarSettingSectionField();
                        $calendar_setting_section_field->calendar_setting_id = $calendar_setting->id;
                        $calendar_setting_section_field->field_id = $val;
                        $calendar_setting_section_field->field_name = array_key_exists($val, $customFieldData) ? $customFieldData[$val]['name'] : $val;
                        $calendar_setting_section_field->field_type = array_key_exists($val, $customFieldData) ? $customFieldData[$val]['type'] : $val;
                        $calendar_setting_section_field->save();
                    }
                }
            } else {
                $calendar_setting->calendar_visibility = false;
            }

            $calendar_setting->save();

            return redirect(url("/admin/calendar"))->with('success', "Setting saved successfully!");
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
