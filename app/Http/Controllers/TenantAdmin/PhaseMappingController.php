<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Models\PhaseMapping;
use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\FilevineService;
use Exception;
use App\Models\Tenant;
use App\Models\MediaLocker;
use App\Models\Variable;
use Illuminate\Support\Facades\Log as Logging;

class PhaseMappingController extends Controller
{
    public $cur_tenant_id;
    public function __construct()
    {
        Controller::setSubDomainName();
        $this->cur_tenant_id = session()->get('tenant_id');
    }

    /**
     * [GET] Phase Mapping Page for Admin
     */
    public function index()
    {
        try {
            $template_name = "";
            $current_project_typeid = "";
            $current_project_type_name = "";
            $current_template_name = "";
            $current_phase = "";
            $current_cat = "";
            $message = "";
            $tenant_id = $this->cur_tenant_id;

            // get phase categories if added any
            $mappings = DB::select(DB::raw("
                            select pm.*, t.`template_name`
                            from phase_mappings as pm
                            LEFT JOIN phase_categories AS pc ON pc.`id` = pm.`Phase_category_Id`
                            LEFT JOIN templates AS t ON t.`Id` = pc.`template_id`
                            where pm.tenant_id = $tenant_id
                            group by Project_Type_Id
                            "));
            $added_projects = [];
            $mapped_fv_project_type_ids = [];
            if (count($mappings) > 0) {
                $current_project_typeid = $mappings[0]->project_type_id;
                $current_project_type_name = $mappings[0]->project_type_name;
                $current_template_name = $mappings[0]->template_name;

                $project_types = [];
                $project_types[0] = [
                    'projectTypeId' => [
                        'native' => $mappings[0]->project_type_id,
                    ],
                    'name' => $mappings[0]->project_type_name,
                ];
                $already_added = true;
                $added_projects = $mappings;
                $mapped_fv_project_type_ids = array_column($mappings, 'project_type_id');
            }

            // for tab selection
            $phase_mapping_project_typeid = session('phase_mapping_project_typeid');
            if (!empty($phase_mapping_project_typeid)) {
                $mappingsbyid = DB::select(DB::raw("
                            select pm.*, t.`template_name`
                            from phase_mappings as pm
                            LEFT JOIN phase_categories AS pc ON pc.`id` = pm.`Phase_category_Id`
                            LEFT JOIN templates AS t ON t.`Id` = pc.`template_id`
                            where pm.tenant_id = $tenant_id AND project_type_id = $phase_mapping_project_typeid"));
                if (count($mappingsbyid) > 0) {
                    $current_project_typeid = $mappingsbyid[0]->project_type_id;
                    $current_project_type_name = $mappingsbyid[0]->project_type_name;
                    $current_template_name = $mappingsbyid[0]->template_name;
                }
            }

            // get all $mapped_phase_template_ids , mapped_phase_templates
            $mapped_phase_templates = DB::select(DB::raw("
                                            SELECT pt.`template_category_name`, t.template_name
                                            FROM phase_mappings AS pm
                                            LEFT JOIN phase_categories AS pc ON pc.`id` = pm.`Phase_category_Id`
                                            LEFT JOIN template_categories AS pt ON pt.`id` = pc.`template_category_id`
                                            LEFT JOIN templates AS t ON t.`id` = pc.`template_id`
                                            where pm.tenant_id = $tenant_id
                                            GROUP BY pt.`template_category_name`
                                            ORDER BY pt.`id`"));

            $mapped_phase_templates_arr = [];
            if (count($mapped_phase_templates) > 0) {
                $mapped_phase_templates_arr = array_column($mapped_phase_templates, 'template_name');
            }

            // get all phase_templates_all array from phase_category table.
            $phase_templates_all  = DB::select(DB::raw("
                                        SELECT t.id, t.template_name
                                        FROM phase_categories pc
                                        INNER JOIN templates t ON pc.template_id = t.id
                                        where pc.tenant_id = $tenant_id
                                        GROUP BY t.template_name; "));

            // get all project types
            $Tenant = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $filevine_api = new FilevineService($apiurl, "");
            $fv_project_type_list = json_decode($filevine_api->getProjectTypeList(), true);

            $available_fv_project_types_all = [];
            if (isset($fv_project_type_list['items'])) {
                $available_fv_project_types_all = $fv_project_type_list['items'];
            }

            $variable_keys = Variable::getVariableKeyByPage('is_timeline_mapping');

            $config = DB::table('config')->where('tenant_id', $tenant_id)->first();

            return $this->_loadContent("admin.pages.phase_mapping", [
                "available_fv_project_types_all" => $available_fv_project_types_all,
                "mapped_fv_project_type_ids" => $mapped_fv_project_type_ids,
                "mapped_phase_templates_arr" => $mapped_phase_templates_arr,
                "phase_templates_all"        => $phase_templates_all,
                "added_projects"             => $added_projects,
                "current_project_typeid"     => $current_project_typeid,
                "current_project_type_name"  => $current_project_type_name,
                "current_template_name"      => $current_template_name,
                "current_phase"              => $current_phase,
                "current_cat"                => $current_cat,
                "variable_keys" => $variable_keys,
                "config"    => $config
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
    public function getImageList()
    {
        $images = MediaLocker::where('media_url', 'NOT LIKE', '%.mp4')->get();
        // dd($images);
        $imageList = [];
    
        foreach ($images as $image) {
            $imageList[] = [
                'title' => $image->media_code, // Replace with the actual field for the title
                'value' => $image->media_url,   // Replace with the actual field for the image URL
            ];
        }
    
        return response()->json($imageList);
    }

    /**
     * [POST] Single Save
     */
    public function phase_mapping_single_save(Request $request)
    {
        try {
            $tenant_id = $this->cur_tenant_id;
            $data = $request->all();
            $current_project_typeid = $data['project_type_id'];
            $phase_id = $data['type_phase_id'];
            $data['tenant_id'] = $tenant_id;
            $id = $data['id'];
            unset($data['tenant_details']);
            unset($data['id']);
            unset($data['settings_details']);
            unset($data['_token']);
            $data['phase_category_id'] = empty($data['phase_category_id']) ? 0 : $data['phase_category_id'];
            $phaseInfo = DB::table('phase_mappings')->where('id', $id)->first();
            $is_default = $data['is_default'];
            if ($is_default) {
                DB::table('phase_mappings')->where('tenant_id', $tenant_id)->update(['is_default' => 0]);
            }

            if (isset($phaseInfo) and !empty($phaseInfo)) {
                DB::table('phase_mappings')->where('id', $id)->update($data);
            } else {
                DB::table('phase_mappings')->insert($data);
            }
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Add Phase Mapping Data for Admin
     */
    public function store(Request $request)
    {
        try {
            $tenant_id = $this->cur_tenant_id;


            if (isset($request->currentProjectTypeId) && !empty($request->currentProjectTypeId) && isset($request->currentProjectTypeName) && !empty($request->currentProjectTypeName) && isset($request->RowCount)) {
                $rowCount = $request->RowCount;
                $template_name = $request->currentProjectTypeName;
                $current_project_typeid = $request->currentProjectTypeId;
                $title = $request->title ? $request->title : null;
                DB::table('tenant_phase_mapping_override_titles')->updateOrInsert(
                    ['tenant_id' => $tenant_id, 'fv_project_type_id' => $current_project_typeid],
                    ['fv_project_type_name' => $request->currentProjectTypeName, 'title' => $title, "created_at" => date('Y-m-d H:i:s'), "updated_at" => date('Y-m-d H:i:s')]
                );


                if ($template_name != "" && $rowCount > 0) {
                    //--Update Category Description
                    for ($i = 0; $i < $rowCount; $i++) {
                        $get_phase_id = 'lstTemplateCategory_' . $i;
                        $get_phase_name = 'SelectedCatName_' . $i;
                        $get_category_id = 'lstTemplateCategoryInner_' . $i;
                        $get_category_description = 'txtDescription_' . $i;
                        $get_existing_id = 'existingId_' . $i;
                        $get_overwrite_pname = 'OverwritePhaseName_' . $i;
                        $get_is_default = 'isDefault_' . $i;

                        $phase_id = $request->$get_phase_id;
                        $phase_name = $request->$get_phase_name;
                        $category_id = empty($request->$get_category_id) ? 0 : $request->$get_category_id;
                        $category_description = $request->$get_category_description;
                        $category_description = str_replace("'", "''", $category_description);
                        $existingId = isset($request->$get_existing_id) ? $request->$get_existing_id : '';
                        $overwrite_pname = isset($request->$get_overwrite_pname) ? $request->$get_overwrite_pname : '';
                        $is_default = ($request->$get_is_default == 'on') ? 1 : 0;

                        // check if category already exist
                        if (!empty($existingId)) {
                            $existing_mapping = DB::select(DB::raw("
                                    update phase_mappings
                                    set type_phase_id='$phase_id', type_phase_name='$phase_name', phase_description='$category_description', phase_category_id='$category_id', overrite_phase_name='$overwrite_pname',is_default=$is_default
                                    where tenant_id = $tenant_id and project_type_id='$current_project_typeid' and id='$existingId' "));
                        } else {
                            $phaseInfo = DB::select(DB::raw("
                                                select * from phase_mappings
                                                where tenant_id = $tenant_id and project_type_id='$current_project_typeid' and type_phase_id='$phase_id' "));
                            $RecordCount = count($phaseInfo);
                            if ($RecordCount == 0) {
                                DB::select(DB::raw("
                                        insert into phase_mappings
                                        (tenant_id, project_type_id, project_type_name, type_phase_id, type_phase_name, phase_category_id, phase_description, overrite_phase_name, is_default, created_at, updated_at)
                                        values('" . $tenant_id . "','" . $current_project_typeid . "', '" . $template_name . "', '" . $phase_id . "', '" . $phase_name . "', '" . $category_id . "', '" . $category_description . "', '" . $overwrite_pname . "', " . $is_default . ", '" . date("Y-m-d H:i:s") . "','" . date("Y-m-d H:i:s") . "') "));
                            } else {
                                DB::select(DB::raw("
                                        update phase_mappings
                                        set phase_description='$category_description', phase_category_id='$category_id', overrite_phase_name='$overwrite_pname',is_default=$is_default
                                        where tenant_id = $tenant_id and project_type_id='$current_project_typeid' and type_phase_id='$phase_id' "));
                            }
                        }
                    }
                    session()->put('phase_mapping_project_typeid',  $current_project_typeid);
                    return redirect()->back()->with('message', "Phase mapping data successfully updated!");
                    $msg_err = true;
                } else {
                    return redirect()->back();
                }
            }
            return redirect()->back()->with('message', 'Add Mapping by Project type and Phase templates');
        } catch (\Exception $e) {
            return  redirect()->back()->with('message', $e->getMessage());
        }
    }


    /**
     * Get Category Description By Id
     */
    function get_phase_category_description_by_id(Request $request)
    {
        try {
            $phase_category = DB::table('phase_categories')->where('id', $request->id)->first();
            return response()->json([
                'phase_category' => $phase_category,
            ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Get Mapping Title By Project Id
     */
    function get_phase_mapping_override_title_by_id(Request $request)
    {

        try {
            $tenant_phase_mapping_override_titles = DB::table('tenant_phase_mapping_override_titles')->where('fv_project_type_id', $request->project_id)->first();

            return response()->json([
                'tenant_phase_mapping_override_titles' => $tenant_phase_mapping_override_titles,
            ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [GET] Phase Category Info for Admin
     */
    public function get_phase_category_info(Request $request)
    {
        try {
            $phaseType = $request->query('type');
            $phaseId = $request->query('phase');
            $rowId = $request->query('row');
            $typeId = $request->query('typeId');
            $fetch = $request->query('fetch');
            $fetchId = $request->query('fetchId');
            $template_name = $request->query('template_name');
            $tenant_id = $this->cur_tenant_id;
            $function_name = $request->query('function');
            $phase_mapping_id = $request->query('pm_id');
            $Tenant = Tenant::find($tenant_id);
            if (isset($phaseType) && isset($phaseId)) {

                $category = DB::select(DB::raw("
                                select * from phase_mappings
                                where tenant_id = $tenant_id and type_phase_Id='$phaseId' "));

                $description = '';
                if (count($category) > 0) {
                    $description = $category[0]->phase_description;
                }
                echo $description;
                return;
            } else if (isset($rowId) && isset($typeId) && !empty($typeId)) {
                $catlstId = "New_" . rand(1001, 9999);

                // get phase types first
                $apiurl = config('services.fv.default_api_base_url');
                if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                    $apiurl = $Tenant->fv_api_base_url;
                }

                $filevine_api = new FilevineService($apiurl, "");
                $fv_project_type_phases = json_decode($filevine_api->getProjectTypePhaseList($typeId), true);
                $project_type_phases = [];
                if (isset($fv_project_type_phases['items'])) {
                    $project_type_phases = $fv_project_type_phases['items'];
                }

                $mappings = DB::select(DB::raw("
                                select * from phase_mappings
                                where tenant_id = $tenant_id and project_type_id='$typeId'"));


                $categories = $this->getCategoriesInfo($template_name);

                $html = "<tr>";
                $html .= "<td style='vertical-align:top;'>";
                $html .= "<label class='font-weight-bold'>Choose a Phase</label>";
                $html .= "<select name='lstTemplateCategory_" . $rowId . "' id='CategoryList_" . $rowId . "' class='lstcats form-control' required>";
                $html .= "<option value=''>--Select Phase--</option>";
                $selected_cat_id = "0";
                $selected_cat_name = "";
                $phaseIds = array_column($mappings, 'type_phase_id');
                foreach ($project_type_phases as $cat) {
                    if (!in_array($cat['phaseId']['native'], $phaseIds)) {
                        $html .= "<option value='" . $cat['phaseId']['native'] . "'>" . $cat['name'] . "</option>";
                    }
                }
                $html .= "</select>";
                $html .= "<input type='hidden' class='TemplateCats' id='SelectedCat_" . $rowId . "' name='SelectedCat_" . $rowId . "' value='" . $selected_cat_id . "'/>";
                $html .= "<input type='hidden' class='TemplateCatNames' id='SelectedCatName_" . $rowId . "' name='SelectedCatName_" . $rowId . "' value='" . $selected_cat_name . "'/>";
                //$html .= "</td>";
                //$html .= "<td style='vertical-align:top;padding-left:0px;'>";
                $html .= "<br><label class='font-weight-bold'>Map to Timeline</label>";
                $html .= "<select name='lstTemplateCategoryInner_" . $rowId . "' id='CategoryListInner_" . $rowId . "' class='form-control cats' data-row-id='" . $rowId . "'>";
                $html .= "<option value=''>--Category--</option>";
                foreach ($categories as $cat) {
                    $html .= "<option value='" . $cat->id . "' >" . $cat->phase_category_name . "</option>";
                }
                $html .= "</select>";
                $html .= "</td>";
                $html .= "<td style='padding-left:0px;'>";
                $html .= "<textarea class='form-control phaseMappingtxtDescription' name='txtDescription_" . $rowId . "' id='txtDescription_" . $rowId . "' cols='60' rows='5'></textarea>";
                $html .= "</td>";
                $html .= "<td style='padding-left:0px;'>";
                //$html .= "<input type= 'checkbox' class='form-control goog-check isDefault' name='isDefault_" . $rowId . "' id='isDefault_" . $rowId . "'>";
                $html .= "</td>";
                $html .= "<td style=''>";
                $html .= "<button role='button' type='button' class='btn btn-success save_phase_mapping mr-2' data-target='" . $rowId . "'>Save</button>";
                $html .= "</td>";
                $html .= "<td></td>";
                $html .= "</tr>";
                echo $html;
                return;
            } else if (isset($phaseType) && !empty($phaseType)) {

                $template_name = "";
                $template_name = $request->query('template_name');
                if (isset($template_name) && $template_name != "") {
                    $template_name = $template_name;
                }

                $html = "";

                $apiurl = config('services.fv.default_api_base_url');
                if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                    $apiurl = $Tenant->fv_api_base_url;
                }
                $filevine_api = new FilevineService($apiurl, "");
                $typeId = $phaseType;

                $fv_project_type_phases = json_decode($filevine_api->getProjectTypePhaseList($typeId), true);
                $project_type_phases = [];
                if (isset($fv_project_type_phases['items'])) {
                    $project_type_phases = $fv_project_type_phases['items'];
                }

                $categories = $this->getCategoriesInfo($template_name);
                $categoryCount = count($project_type_phases);

                $categorieAll = DB::select(DB::raw("
                                    select pm.*
                                    from phase_mappings pm
                                    where pm.tenant_id = $tenant_id and  pm.project_type_id='$typeId' order by pm.id"));

                $html = "<table id='tblCatInfo' style='width:100%;'>";
                // $html = "";
                $all_selected_cat_ids = "";
                $row = 0;
                if (count($categorieAll) > 0) {
                    foreach ($categorieAll as $tcat) {
                        $selected_cat_id = "0";
                        $selected_cat_name = "";
                        $options = "";

                        foreach ($project_type_phases as $cat) {
                            if ($tcat->type_phase_id == $cat['phaseId']['native']) {
                                $selected_cat_id = $cat['phaseId']['native'];
                                $selected_cat_name = $cat['name'];
                                if ($all_selected_cat_ids == "") {
                                    $all_selected_cat_ids .= $cat['phaseId']['native'];
                                } else {
                                    $all_selected_cat_ids .= ',' . $cat['phaseId']['native'];
                                }
                                $options .= "<option value='" . $cat['phaseId']['native'] . "' selected='selected'>" . $cat['name'] . "</option>";
                            } else {
                                $options .= "<option value='" . $cat['phaseId']['native'] . "'>" . $cat['name'] . "</option>";
                            }
                        }
                        $html .= "<tr>";
                        $html .= "";
                        $html .= "<td style='vertical-align:top;'>";
                        $html .= "<input type='hidden' name='existingId_" . $row . "' class='catid' value='" . $tcat->id . "'/>";
                        $html .= "<input type='hidden' id='Row_" . $row . '_' . $tcat->type_phase_id . "' value='" . $tcat->type_phase_id . "'/>";
                        $html .= "<label class='font-weight-bold'>Choose a Phase</label>";
                        $html .= "<select name='lstTemplateCategory_" . $row . "' id='CategoryList_" . $row . "' class='lstcats form-control " . (empty($selected_cat_id) ? 'text-danger' : '') . "' required>";
                        $html .= "<option value=''>--Select Phase--</option>";
                        $html .= $options;
                        $html .= "</select>";
                        if (isset($tcat->overrite_phase_name) and !empty($tcat->overrite_phase_name)) {
                            $html .= "<label style='margin-top: 10px' for='OverwritePhaseName_" . $row . "'>Override Project Phase</label>";
                            $html .= "<input type='text' style='margin-top: 5px' class='OverwritePhaseName form-control ' id='OverwritePhaseName_" . $row . "' name='OverwritePhaseName_" . $row . "' value='" . $tcat->overrite_phase_name . "'/>";
                        } else {
                            $html .= "<label style='margin-top: 10px' class='label_" . $tcat->id . "' for='OverwritePhaseName_" . $row . "'></label>";
                            $html .= "<input type='hidden' style='margin-top: 5px' class='OverwritePhaseName form-control ' id='OverwritePhase_" . $tcat->id . "' name='OverwritePhaseName_" . $row . "' value='" . $tcat->overrite_phase_name . "'/>";
                        }
                        $html .= "<input type='hidden' class='TemplateCats' id='SelectedCat_" . $row . "' name='SelectedCat_" . $row . "' value='" . $selected_cat_id . "'/>";
                        $html .= "<input type='hidden' class='TemplateCatNames' id='SelectedCatName_" . $row . "' name='SelectedCatName_" . $row . "' value='" . $selected_cat_name . "'/>";
                        //$html .= "</td>";
                        //$html .= "<td style='vertical-align:top;padding-left:0px;'>";
                        $html .= "<br><label class='font-weight-bold'>Map to Timeline</label>";
                        $html .= "<br><select name='lstTemplateCategoryInner_" . $row . "' id='CategoryListInner_" . $row . "' class='form-control mb-2 cats' data-row-id='" . $row . "'>";
                        $html .= "<option value=''>--Category--</option>";
                        foreach ($categories as $cat) {
                            if ($tcat->phase_category_id == $cat->id) {
                                $html .= "<option value='" . $cat->id . "' selected='selected'>" . $cat->phase_category_name . "</option>";
                            } else {
                                $html .= "<option value='" . $cat->id . "'>" . $cat->phase_category_name . "</option>";
                            }
                        }
                        $html .= "</select>";
                        $html .= "<button role='button' type='button' class='btn btn-success save_phase_mapping mr-1' data-target='" . $tcat->id . "'>Save</button>";
                        $html .= "<button role='button' type='button' class='btn btn-danger delete_phase_mapping mr-1' data-target='" . $tcat->id . "'><span class='fa fa-trash'></span></button>";
                        if (empty($tcat->overrite_phase_name)) {
                            $html .= "<button role='button' type='button' class='btn btn-success edit_phase_mapping' data-target='" . $tcat->id . "'><span class='fa fa-edit'></span></button>";
                        }
                        $html .= "</td>";
                        $html .= "<td style=' vertical-align:top;padding-left:0px; width: 72%;'>";
                        $html .= "<textarea class='form-control phaseMappingtxtDescription' name='txtDescription_" . $row . "' id='txtDescription_" . $row . "' cols='60' rows='5' required>" . $this->get_category_description_by_type_and_phase($typeId, $tcat->type_phase_id, $tcat->phase_category_id) . "</textarea>";
                        $html .= "</td>";
                        $html .= "<td style='padding-left:0px;'>";
                        //$html .= "<input type= 'checkbox' class='form-control goog-check isDefault' name='isDefault_" . $row . "' id='isDefault_" . $row . "'" . ($tcat->is_default ? 'checked' : '') . ">";
                        $html .= "</td>";
                        $html .= "<td style='display: flex; align-items: center'>";
                       
                        $html .= "</td>";
                        $html .= "";
                        $html .= "</tr>";
                        $row++;
                    }
                }
                $html .= "";
                $html .= "</table>";
                $html .= "<input type='hidden' name='AllSelectedCatIds' id='AllSelectedCatIds' value='" . $all_selected_cat_ids . "'/>";
                $html .= "<input type='hidden' name= 'RowCount' id='RowCount' value='" . $row . "'/>";
                $html .= "<input type='hidden' id='CategoryCount' value='" . $categoryCount . "'/>";
                echo $html;
                return;
            } else if (isset($fetch) && isset($fetchId) && !empty($fetchId)) {
                header('Content-Type: application/json');
                $response = ['status' => true, 'message' => ''];
                $typeId = $fetchId;
                $apiurl = config('services.fv.default_api_base_url');
                if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                    $apiurl = $Tenant->fv_api_base_url;
                }
                $filevine_api = new FilevineService($apiurl, "");
                $project_type_phases = json_decode($filevine_api->getProjectTypePhaseList($typeId), true)['items'];

                $mappings = DB::select(DB::raw("
                            select count(id) from phase_mappings
                            where tenant_id = $tenant_id and project_type_id='$typeId'"));
                $phase_ids = [];
                $mapped_ids = [];
                $not_mapped_message = '';
                if (count($mappings) > 0) {
                    // check for any change
                    foreach ($project_type_phases as $ptp) {
                        $phaseId = $ptp['phaseId']['native'];
                        $phase_name = $ptp['name'];
                        $phase_ids[] = $phaseId;
                        $phase = DB::select(DB::raw("
                                    select * from phase_mappings
                                    where tenant_id = $tenant_id and project_type_id='$typeId' and type_phase_id='$phaseId'"));
                        if (count($phase) > 0) {
                            $mapped_ids[] = $phaseId;
                            // compare if name changed
                            if ($phase[0]->type_phase_name !== $phase_name) {
                                DB::select(
                                    DB::raw("
                                        update phase_mappings
                                        set type_phase_name='$phase_name'
                                        where project_type_id='$typeId' and type_phase_id='$phaseId'")
                                );
                                $response['message'] .= '<p>"' . $phase[0]['Type_Phase_Name'] . '" was changed as "' . $phase_name . '"</p>';
                            }
                        } else {
                            $not_mapped_message .= '<p>New "' . $phase_name . '" is added in Filevine, so please map that as well</p>';
                        }
                    }
                    // now check for any to delete
                    foreach ($mappings as $mp) {
                        // check if not exist in fv phases
                        if (isset($mp->type_phase_id) && !in_array($mp->type_phase_id, $phase_ids) && !empty($mp->type_phase_name)) {
                            $phase_id = $mp->type_phase_id;
                            DB::select(DB::raw("
                                    delete from phase_mappings
                                    where tenant_id = $tenant_id and project_type_id='$typeId' and type_phase_id='$phase_id"));
                            $response['message'] .= '<p>"' . $mp['Type_Phase_Name'] . '" was removed</p>';
                        }
                    }
                    $response['message'] .= $not_mapped_message;
                    if (empty($response['message'])) {
                        $response['message'] = 'Everything is up to date';
                    }
                } else {
                    $response['message'] = "No phases mapped yet, please add at least one to update";
                }
                echo json_encode($response);
                return;
            } else if (isset($function_name) && $function_name == "delete" && !empty($phase_mapping_id)) {
                $response = ['success' => false, 'message' => ''];
                $mappingDelete = DB::select(DB::raw("DELETE FROM phase_mappings where id='$phase_mapping_id'"));
                $response['success'] = true;
                $response['message'] = 'Setting saved successfully!';
                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            echo json_encode(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    /**
     * [GET] Categories Info for Admin
     */
    function getCategoriesInfo($template_name = null)
    {
        try {
            $tenant_id = $this->cur_tenant_id;
            if ($template_name != "" && $template_name != null) {
                $categories = DB::select(DB::raw('
                                    select pc.*,CASE WHEN LENGTH(pc.override_phase_name) = 0 OR pc.override_phase_name IS NULL THEN pc.phase_category_name ELSE pc.override_phase_name END as phase_category_name from phase_categories as pc
                                    inner join templates as t on t.`id` = pc.`template_id`
                                    where pc.tenant_id = "' . $tenant_id . '" and t.template_name = "' . $template_name . '"
                                    order by pc.id'));
            } else {
                $categories = DB::select(DB::raw("
                                    select *,CASE WHEN LENGTH(override_phase_name) = 0 OR override_phase_name IS NULL THEN phase_category_name ELSE override_phase_name END as phase_category_name from phase_categories
                                    where tenant_id = $tenant_id
                                    order by id"));
            }

            return $categories;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Get Categories Description by Type and Phase for Admin
     */
    function get_category_description_by_type_and_phase($type, $phase, $cat)
    {
        try {
            $tenant_id = $this->cur_tenant_id;
            $info = "";
            $catInfo = DB::select(DB::raw("
                            select phase_description as Description
                            from phase_mappings
                            where tenant_id = $tenant_id and project_type_id='$type' and type_phase_id='$phase' and phase_category_id='$cat'"));
            if (count($catInfo)) {
                $info = $catInfo[0]->Description;
            }
            return $info;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function delete_mapped_timeline($subdomain, $projectTypeId = null)
    {
        if (!is_null($projectTypeId)) {
            try {
                $res = DB::table('phase_mappings')->where(['tenant_id' => $this->cur_tenant_id, 'project_type_id' => $projectTypeId])->delete();
                return response()->json([
                    'success' => $res,
                    'message' => 'Success'
                ], 200);
            } catch (\Throwable $th) {
                return response()->json([
                    'success' => false,
                    'message' => $th->getMessage()
                ], 400);
            }
        }
    }

    /**
     *  [POST] Update default phase mapping on config table
     */
    public function updateDefaultPhaseMapping(Request $request)
    {
        try {
            $tenant_id = $this->cur_tenant_id;
            $config = Config::where('tenant_id', $tenant_id)->first();
            $config->default_phase_mapping = $config->default_phase_mapping ? false : true;
            $config->save();

            return response()->json([
                'message' => 'Setting saved successfully!'
            ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Add all phase mapping
     */
    public function addAllPhaseMapping(Request $request)
    {
        try {
            $tenant_id = $this->cur_tenant_id;
            $current_project_typeid = $request->current_project_typeid;
            $current_project_typename = $request->current_project_typename;

            $Tenant = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $obj = new FilevineService($apiurl, "");

            $fv_project_type_phases = json_decode($obj->getProjectTypePhaseList($current_project_typeid), true);
            if (isset($fv_project_type_phases['items'])) {
                $project_type_phases = $fv_project_type_phases['items'];
                if (!empty($project_type_phases)) {
                    foreach ($project_type_phases as $projectPhase) {
                        $phase_id = $projectPhase['phaseId']['native'];
                        $phase_name = $projectPhase['name'];
                        $phaseInfo = DB::select(DB::raw("
                        select * from phase_mappings
                        where tenant_id = $tenant_id and project_type_id='$current_project_typeid' and type_phase_id='$phase_id' "));
                        $RecordCount = count($phaseInfo);
                        if ($RecordCount == 0) {
                            DB::table('phase_mappings')->insert([
                                'tenant_id' => $tenant_id,
                                'project_type_id' => $current_project_typeid,
                                'project_type_name' => $current_project_typename,
                                'type_phase_id' => $phase_id,
                                'type_phase_name' => $phase_name,
                                'created_at' => date("Y-m-d H:i:s"),
                            ]);
                        }
                    }
                }
            }

            return response()->json([
                'status'  => true,
                'message' => "Setting saved successfully!"
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

}
