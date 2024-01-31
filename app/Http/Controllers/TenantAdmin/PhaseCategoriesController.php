<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\FilevineService;
use App\Models\LegalteamConfig;
use App\Models\TemplateCategory;
use App\Models\PhaseCategorie;
use App\Models\Tenant;
use App\Models\PhaseMapping;
use App\Models\MediaLocker;
use App\Models\Templates;
use App\Models\Variable;
use App\Models\Config;
use Exception;
use Illuminate\Support\Facades\Log as Logging;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;


class PhaseCategoriesController extends Controller
{
    public $cur_tenant_id;
    public function __construct()
    {
        Controller::setSubDomainName();
        $this->cur_tenant_id = session()->get('tenant_id');
    }

    /**
     * [GET] Phase Categories Page for Admin
     */
    public function index(Request $request)
    {
        try {
            // get phase categories if added any
            $categories = DB::select(DB::raw("
                            select pt.id, t.template_name, pc.template_id
                            from phase_categories pc
                            inner join template_categories pt on pc.template_category_id=pt.id
                            inner join templates t on pt.template_id = t.id
                            where pc.tenant_id = $this->cur_tenant_id
                            and t.tenant_id is null
                            group by t.template_name"));
            $selected_template_ids = [];
            $selected_phase_templates = [];
            $available_phase_templates = [];
            if (count($categories) > 0) {
                $template_name = $categories[0]->template_name;
                $already_added = true;
                $selected_template_ids = array_column($categories, 'template_id');
                $selected_phase_templates = $categories;
            }
            // Used To get All The Templates
            if (count($selected_template_ids) > 0) {
                $selected_templates_ids_str = implode("','", $selected_template_ids);

                $available_phase_templates = DB::select(DB::raw("
                                        select tc.id, tc.template_category_name ,t.template_name
                                        from template_categories tc
                                        inner join templates t on tc.template_id = t.id
                                        where t.id not in('$selected_templates_ids_str')
                                        and t.tenant_id is null
                                        group by t.template_name"));
            } else {
                $available_phase_templates = DB::select(DB::raw("
                                        select t.template_name, pt.id, pt.template_category_name
                                        from template_categories pt
                                        inner join templates t on pt.template_id = t.id
                                        where t.tenant_id is null
                                        group by t.template_name"));
            }
            $selected_phase_template = "";
            if (isset($template_name) && $template_name != "") {
                $selected_phase_template = $template_name;
            }

            // get tenant related templates
            $available_tenant_templates = DB::select(DB::raw("
                select t.id, t.template_name from templates t
                where t.tenant_id = $this->cur_tenant_id
                group by t.template_name"));
            $selected_tenant_phase_template = "";
            if(count($available_tenant_templates) > 0) {
                $selected_tenant_phase_template = $available_tenant_templates[0]->template_name;
            }

            $variable_keys = Variable::getVariableKeyByPage('is_project_timeline');

            $config = Config::where('tenant_id', $this->cur_tenant_id)->first();

            return $this->_loadContent('admin.pages.phase_categories', [
                'selected_phase_templates' => $selected_phase_templates,
                'selected_phase_template'  => $selected_phase_template,
                'available_phase_templates' => $available_phase_templates,
                'available_tenant_templates' => $available_tenant_templates,
                'selected_tenant_phase_template' => $selected_tenant_phase_template,
                'variable_keys' => $variable_keys,
                'config' => $config
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

    public function phase_category_title(Request $request)
    {
        $template = Templates::where('template_name', $request->template_name)->first();

        $tenant_phase_category_override_title = DB::table('tenant_phase_category_override_titles')
            ->where('tenant_id', $this->cur_tenant_id)
            ->where('template_id',  $template->id)->first();

        return response()->json([
            'tenant_phase_category_override_title' => $tenant_phase_category_override_title
        ]);
    }

    /**
     * [GET] Phase Category Info for Admin
     */
    public function get_category_info(Request $request)
    {
        try {
            $get_catID = $request->query('cat_id');
            $get_type  = $request->query('type');
            $get_row   = $request->query('row');
            $get_template_Name = $request->query('template_name');
            $function_name = $request->query('function');
            $phase_category_id = $request->query('pc_id');

            $tenant_id = $this->cur_tenant_id;
            if (isset($get_catID) && isset($get_type) && $get_type == 'description') {
                $catId = $get_catID;
                $catInfo = DB::select(DB::raw("
                                select *
                                from phase_categories
                                where tenant_id = $tenant_id and template_category_id=" . $catId . " ORDER BY sort_order ASC "));
                if (count($catInfo) > 0) {
                    echo $catInfo[0]->phase_category_description;
                }
                return;
            } else if (isset($get_catID) && isset($get_type) && $get_type == 'name') {
                $catId = $get_catID;
                $catInfo = DB::select(DB::raw("
                                select *
                                from template_categories
                                where id=" . $catId));
                if (count($catInfo) > 0) {
                    echo $catInfo[0]->template_category_name;
                }
                return;
            } else if (isset($get_row) && isset($get_template_Name) && !empty($get_template_Name)) {
                $catlstId = "New_" . rand(1001, 9999);
                $template_name = $get_template_Name;
                $template_name_query = str_replace("'", "\'", $template_name);
                $template_type = $request->query('template_type');
                $cur_tenant_id = $this->cur_tenant_id;

                $categories = DB::select(DB::raw("select t.template_name, pt.* from template_categories pt inner join templates t on pt.template_id = t.id where t.template_name ='$template_name_query' order by id "));

                $html = "<tr>";
                $html .= "<td style='vertical-align:top'>";
                if($template_type == "custom") {
                    $html .= "<input name='lstTemplateCategory_" . $get_row . "' id='CategoryList_" . $get_row . "' class='form-control' required data-row-id='" . $get_row . "' value='' />";
                }
                else {
                    $html .= "<select name='lstTemplateCategory_" . $get_row . "' id='CategoryList_" . $get_row . "' class='lstcats form-control' required data-row-id='" . $get_row . "'>";
                    $html .= "<option value=''>--Select Category--</option>";

                    foreach ($categories as $cat) {
                        $html .= "<option value='" . $cat->id . "'>" . $cat->template_category_name . "</option>";
                    }
                    $html .= "</select>";
                }
                $html .= "<input type='hidden' class='TemplateCats' name='SelectedCat_" . $get_row . "' id='SelectedCat_" . $get_row . "' value='0'/>";
                $html .= "<input type='hidden' class='TemplateCatNames' name='SelectedCatName_" . $get_row . "' id='SelectedCatName_" . $get_row . "' value=''/>";
                $html .= "</td>";
                $html .= "<td>";
                $html .= "<textarea class='form-control phaseCategorytxtDescription' name='txtDescription_" . $get_row . "' id='txtDescription_" . $get_row . "' cols='60' rows='5' ></textarea>";
                $html .= "</td>";
                $html .= "</tr>";
                echo $html;
                return;
            } else if (isset($get_template_Name) && !empty($get_template_Name)) {
                $template_name = $get_template_Name;
                $template_name_query = str_replace("'", "\'", $template_name);
                $template_type = $request->query('template_type');
                $cur_tenant_id = $this->cur_tenant_id;

                if($template_type == "custom") {
                    $categories =  DB::select(DB::raw("select t.template_name, pt.* from template_categories pt inner join templates t on pt.template_id = t.id where t.template_name ='$template_name_query' and t.tenant_id='$cur_tenant_id' and pt.tenant_id='$cur_tenant_id' order by id "));
                }
                else {
                    $categories =  DB::select(DB::raw("select t.template_name, pt.* from template_categories pt inner join templates t on pt.template_id = t.id where t.template_name ='$template_name_query' order by id "));
                }
                $categoryCount = count($categories);

                $categorieAll = DB::select(DB::raw("
                                    select pc.id as pc_id, pt.id, pc.is_default, pc.template_category_id, pc.phase_category_name, pc.template_category_id, pc.phase_category_description, pc.override_phase_name
                                    from phase_categories pc
                                    inner join template_categories pt on pc.template_category_id =pt.id
                                    inner join templates t on pt.template_id = t.id
                                    where pc.tenant_id = $tenant_id and t.template_name='$template_name_query' ORDER BY pc.sort_order ASC "));
                $template_name = str_replace("'", "''", $template_name);
                $html = "<table id='tblCatInfo'  style='width:100%;'><tbody data-repeater-list='group-a' class='ui-sortable'>";
                $all_selected_cat_ids = "";
                $row = 0;
                if (count($categorieAll) > 0) {
                    foreach ($categorieAll as $tcat) {
                        $selected_cat_id = "0";
                        $selected_cat_name = "";
                        $options = "";

                        foreach ($categories as $cat) {
                            if ($tcat->template_category_id == $cat->id) {
                                $selected_cat_id = $cat->id;
                                $selected_cat_name = $cat->template_category_name;
                                if ($all_selected_cat_ids == "") {
                                    $all_selected_cat_ids .= $cat->id;
                                } else {
                                    $all_selected_cat_ids .= ',' . $cat->id;
                                }
                                $options .= "<option value='" . $cat->id . "' selected='selected'>" . $cat->template_category_name . "</option>";
                            } else {
                                $options .= "<option value='" . $cat->id . "'>" . $cat->template_category_name . "</option>";
                            }
                        }

                        $html .= "<tr>";
                        $html .= "<td style='width:300px; vertical-align:top'>";
                        $html .= "<input type='hidden' class='id-user' id='Row_" . $row . '_' . $tcat->id . "' value='" . $tcat->pc_id . "'/>";
                        // check for template type
                       
                        if($template_type == 'custom') {
                            $html .= "<input name='lstTemplateCategory_" . $row . "' id='CategoryList_" . $row . "' class='form-control " . (empty($selected_cat_name) ? 'text-danger' : '') . "' required data-row-id='" . $row . "' value='".$selected_cat_name."' />";
                        }
                        else {
                            $html .= "<select name='lstTemplateCategory_" . $row . "' id='CategoryList_" . $row . "' class='lstcats form-control mb-2" . (empty($selected_cat_name) ? 'text-danger' : '') . "' required data-row-id='" . $row . "'>";
                            $html .= "<option value=''>--Select Category--</option>";
                            $html .= $options;
                            $html .= "</select>";
                        }
                        $html .= "<br/><input style='width:25px; height:25px' type= 'checkbox' class='form-control goog-check isDefault mr-4' name='isDefault_" . $row . "' id='isDefault_" . $row . "'" . ($tcat->is_default ? 'checked' : '') . ">";
                        $html .= "<button role='button' type='button' class='btn btn-danger delete_phase_category' data-target='" . $tcat->pc_id . "'><span class='fa fa-trash'></span></button>";
                        if (empty($tcat->override_phase_name) && $template_type != 'custom') {
                            $html .= "<button role='button' type='button' class='btn btn-success edit_phase_cat ml-1' data-target='" . $tcat->id . "'><span class='fa fa-edit'></span></button>";
                        }
                        $html .= "<button  type='button' class='btn btn-hover-bg-secondary btn-drag-drop btn-icon ml-1'><span class='fa fa-sort'></span></button>";
                        
                        if (isset($tcat->override_phase_name) and !empty($tcat->override_phase_name)) {
                            $html .= "<label style='margin-top: 10px;float: left;' class='label_" . $tcat->id . "' for='OverwriteCatName_" . $row . "'>Override Phase Category</label>";
                            $html .= "<input type='text' style='margin-top: 5px' id='OverwritePhase_" . $tcat->id . "' class='OverwriteCatName form-control ' id='OverwriteCatName_" . $row . "' name='OverwriteCatName_" . $row . "' value='" . $tcat->override_phase_name . "'/>";
                        } else {
                            $html .= "<label style='margin-top: 10px;float: left;' class='label_" . $tcat->id . "' for='OverwriteCatName_" . $row . "'></label>";
                            $html .= "<input type='hidden' style='margin-top: 5px' id='OverwritePhase_" . $tcat->id . "' class='OverwriteCatName form-control ' id='OverwriteCatName_" . $row . "' name='OverwriteCatName_" . $row . "' value=''/>";
                        }
                        // $html .= "<label style='margin-top: 10px;float: left;' for='OverwriteCatName_" . $row . "'>Override Phase Category</label>";
                        // $html .= "<input type='text' style='margin-top: 5px' class='OverwriteCatName form-control ' id='OverwriteCatName_" . $row . "' name='OverwriteCatName_" . $row . "' value='" . $tcat->override_phase_name . "'/>";
                        $html .= "<input type='hidden' class='TemplateCats' name='SelectedCat_" . $row . "' id='SelectedCat_" . $row . "' value='" . $selected_cat_id . "'/>";
                        $html .= "<input type='hidden' class='TemplateCatNames' name='SelectedCatName_" . $row . "' id='SelectedCatName_" . $row . "' value='" . $selected_cat_name . "'/>";
                        $html .= "</td>";
                        $html .= "<td style='width:100%'>";
                        $html .= "<textarea class='form-control phaseCategorytxtDescription' name='txtDescription_" . $row . "' id='txtDescription_" . $row . "' cols='60' rows='5' required>" . $this->get_category_description_by_template($tcat->template_category_id, $tcat->phase_category_name) . "</textarea>";
                        $html .= "</td>";
                        $html .= "<td style='display: flex'>";
                        $html .= "</td>";
                        $html .= "</tr>";
                        $row++;
                    }
                }
                $html .= "</tbody></table>";
                $html .= "<input type='hidden' name='AllSelectedCatIds' id='AllSelectedCatIds' value='" . $all_selected_cat_ids . "'/>";
                $html .= "<input type='hidden' name= 'RowCount' id='RowCount' value='" . $row . "'/>";
                $html .= "<input type='hidden' id='CategoryCount' value='" . $categoryCount . "'/>";

                echo $html;
            } else if (isset($function_name) && $function_name == "delete" && !empty($phase_category_id)) {
                $response = ['success' => false, 'message' => ''];
                // check if this category linked to any phase mappings
                $phaseInfo = DB::select(DB::raw("select * from phase_mappings where phase_category_id='$phase_category_id'"));
                $phaseCount = count($phaseInfo);
                if ($phaseCount > 0) {
                    $response['message'] = 'This Phase Category is already used on Phase Mapping page. Please check it first.';
                } else {
                    // delete template category as well in case of custom template category
                    $phase_cat = PhaseCategorie::where('id', $phase_category_id)->first();
                    $template = Templates::where(['id' => $phase_cat->template_id, 'tenant_id' => $tenant_id])->first();

                    $phaseDelete = DB::select(DB::raw("DELETE FROM phase_categories where id='$phase_category_id'"));
                    // delete template category as well if tenant category
                    if(isset($template->id)) {
                        TemplateCategory::where('id', $phase_cat->template_category_id)->delete();
                    }
                    $response['success'] = true;
                    $response['message'] = 'Setting saved successfully!';
                }
                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            dd($e);
            echo $e->getMessage();
        }
    }

    /**
     * Get Category Description By Template
     */
    function get_category_description_by_template($template, $category_name)
    {
        try {
            $tenant_id = $this->cur_tenant_id;
            $catInfo = DB::select(DB::raw("
                            select *
                            from phase_categories
                            where tenant_id = $tenant_id and template_category_id =" . $template . " and phase_category_name ='$category_name' "));
            return $catInfo[0]->phase_category_description;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function uploadImage(Request $request)
    {
        $rules = Validator::make($request->all(), [
            'code' => 'required|unique:media_locker,media_code,',
            'file' => 'nullable|mimetypes:video/*,audio/*,image/*'
        ]);
        if($rules->fails()){
            return response()->json(['error_message' => $rules->errors()]);
        }
        try {
            $tenant_id = $this->cur_tenant_id;
            $Tenant = Tenant::find($tenant_id);
            $file = $request->file;
            $mediaCode =  $file->getClientOriginalName();
            $mediaCode = explode('.', $mediaCode)[0];
            $name = time() . $file->getClientOriginalName();
            $filePath = $Tenant->tenant_name . '/' . $name;
            \Storage::disk('s3')->put($filePath, file_get_contents($file), 'public');
            $publicUrl = \Storage::disk('s3')->url($filePath);
            $values = array(
                'tenant_id' => $tenant_id,
                'media_code' => $request->code
            );
            $values['media_url'] = $publicUrl;
            $media = MediaLocker::create($values);
            return response()->json(['location' => "$publicUrl"]);
        } catch (\Exception $e) {
            return response()->json(['error_message' => $e->getMessage()]);
        }
    }
    /**
     * [POST] Add Categories Data for Admin
     */
    public function store(Request $request)
    {
        try {
            $rowCount      = $request->RowCount;
            $template_name = $request->currentTemplateX;
            $tenant_id = $this->cur_tenant_id;
            $existingCount = 0;
            if (isset($rowCount) && isset($template_name)) {
                $template_type = $request->currentTemplateXType;
                if ($template_name != "" && $rowCount > 0) {
                    $template = Templates::where('template_name', $template_name);
                    if($template_type == 'custom') {
                        $template = $template->where('tenant_id', $tenant_id);
                    }
                    $template = $template->first();

                    DB::table('tenant_phase_category_override_titles')->updateOrInsert(
                        ['tenant_id' => $tenant_id, 'template_id' => $template->id],
                        ['title' => $request->title, "created_at" => date('Y-m-d H:i:s'), "updated_at" => date('Y-m-d H:i:s')]
                    );

                    //--Update Category Description
                    for ($i = 0; $i < $rowCount; $i++) {
                        $get_category_id  = 'lstTemplateCategory_' . $i;
                        $get_category_description = 'txtDescription_' . $i;
                        $get_category_name        = 'SelectedCatName_' . $i;
                        $get_overwrite_cname = 'OverwriteCatName_' . $i;
                        $get_is_default = 'isDefault_' . $i;
                        $get_selected_category_id = 'SelectedCat_' . $i;
                        // check if index exists or deleted
                        if(!isset($request->$get_category_id)) {
                            continue;
                        }

                        $category_id = $request->$get_category_id;
                        $category_description = $request->$get_category_description;
                        $category_name = $request->$get_category_name;
                        $is_default = isset($request->$get_is_default) ? 1 : 0;
                        $overwrite_cname = $request->$get_overwrite_cname ?? '';
                        $category_description = str_replace("'", "''", $category_description);
                        // check if custom template and template categories needs to be created
                        if($template_type == 'custom') {
                            $selected_category_id = isset($request->$get_selected_category_id) ? $request->$get_selected_category_id : 0;
                            $overwrite_cname = '';
                            if($selected_category_id == 0) {
                                $template_category = TemplateCategory::create([
                                    'template_id' => $template->id,
                                    'template_category_name' => $category_id,
                                    'template_category_description' => $category_id,
                                    'tenant_id' => $tenant_id
                                ]);
                                $category_id = $template_category->id;
                                $category_name = $template_category->template_category_name;
                            }
                            else {
                                TemplateCategory::where(['tenant_id' => $tenant_id, 'id' => $selected_category_id])
                                ->update([
                                    'template_id' => $template->id,
                                    'template_category_name' => $category_id,
                                    'template_category_description' => $category_id,
                                    'tenant_id' => $tenant_id
                                ]);
                                $category_name = $category_id;
                                $category_id = $selected_category_id;
                            }
                        }
                        // check if category already exist
                        $phaseInfo = DB::select(DB::raw("
                                            select pc.* , t.id as templateID
                                            from phase_categories pc
                                            INNER JOIN templates t on pc.template_id = t.id
                                            where pc.tenant_id = $tenant_id and template_category_id='$category_id' "));
                        $templateID = DB::select(DB::raw("
                                            select pt.* , t.id as templateID
                                            from template_categories pt
                                            INNER JOIN templates t on pt.template_id = t.id
                                            where pt.id='" . $category_id . "' "));
                        $RecordCount = count($phaseInfo);
                        $existingCount += DB::table('phase_categories')->where([
                            ['tenant_id', '=', $tenant_id],
                            ['template_category_id', '=', $category_id],
                            ['template_id', '=', $templateID[0]->templateID],
                            ['phase_category_name', '=', $category_name],
                            ['phase_category_description', '=', $category_description],
                            ['is_default', '=', $is_default],
                        ])->get()->count();
                        if ($RecordCount == 0) {
                            if ($templateID) {
                                DB::select(DB::raw("
                                        insert into phase_categories
                                        (tenant_id, template_id , template_category_id, phase_category_name, phase_category_description, override_phase_name, is_default, Created_at, Updated_at)
                                        values('$tenant_id', '" . $templateID[0]->templateID . "','" . $category_id . "', '" . $category_name . "', '" . $category_description . "', '" . $overwrite_cname . "', '" . $is_default . "', '" . date("Y-m-d H:i:s") . "','" . date("Y-m-d H:i:s") . "') "));
                            }
                        } else {

                            DB::select(DB::raw("
                                        update phase_categories
                                        set phase_category_description ='" . $category_description . "', override_phase_name ='" . $overwrite_cname . "', is_default ='" . $is_default . "'
                                        where tenant_id = $tenant_id and template_category_id=" . $category_id));
                            if($template_type == 'custom') {
                                PhaseCategorie::where(['tenant_id' => $tenant_id, 'template_category_id' => $category_id])
                                ->update(['phase_category_name' => $category_name]);
                            }
                        }
                    }
                    if ($existingCount == $rowCount) {
                        return redirect()->back();
                    }
                    return redirect()->back()->withInput(array('message' => "Setting saved successfully!", 'template_name' => $template_name));
                } else {
                    return redirect()->back()->withInput(array('message' => "Select Phase Template!"));
                }
            } else {
                return redirect()->back()->withInput(array('message' => "Select Phase Template!"));
            }
        } catch (\Exception $e) {
            die($e->getMessage());
            return redirect()->back()->withInput(array('message' => $e->getMessage()));
        }
    }

    public function category_sortable(Request $request)
    {
        if (is_array($request->data) && count($request->data) > 0) {
            $collection = collect($request->data);
            $PhaseCategories = PhaseCategorie::whereIn('id', $collection->pluck('id')->toArray())->get();
            DB::beginTransaction();
            try {
                foreach ($PhaseCategories as $item) {
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


    public function deleteTemplate(Request $request)
    {
        $response['success'] = false;
        $tenant_id = $this->cur_tenant_id;
        $template_name = urldecode($request->data);
        $template_type = $request->template_type;
        $template = Templates::where('template_name', $template_name);
        if($template_type == 'custom') {
            $template = $template->where('tenant_id', $tenant_id);
        }
        $template = $template->first();
        if ($template) {
            $cur_tenant_id = $this->cur_tenant_id;
            $template_id = $template->id;
            $category_ids = PhaseCategorie::where('template_id', $template_id)
                ->where('tenant_id', $cur_tenant_id)
                ->pluck('id')
                ->toArray();

            $phase_mappings_count = PhaseMapping::whereIn('phase_category_id', $category_ids)
                ->where('tenant_id', $cur_tenant_id)->count();

            if ($phase_mappings_count) {
                $response['message'] = "This Phase Template was used already in Phase Mapping!";
            } else {
                PhaseCategorie::where('template_id', $template_id)
                    ->where('tenant_id', $cur_tenant_id)->delete();
                // delete template as well if custom template
                if($template_type == 'custom') {
                    $result = Templates::where('id', $template->id)->delete();
                }
                $response['success'] = true;
                $response['message'] = "Setting saved successfully!";
            }
        } else {
            $response['message'] = "There is no template with this name!";
        }
        return response()->json($response, 200);
    }

    /**
     * Get Template Category Description By Id
     */
    function get_template_category_description_by_id(Request $request)
    {
        try {
            $template_category = DB::table('template_categories')->where('id', $request->id)->first();
            return response()->json([
                'template_category' => $template_category,
            ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Save Custom Template Category
     */
    public function custom_template_save(Request $request)
    {

        try {
            // $validator = $request->validate();
            $validator = Validator::make($request->all(),
                [
                    'template_name' => 'required|string|max:255',
                    'template_description' => 'required|string|max:512',
                    'is_default' => ['required', Rule::in([0, 1])],
                ], [], []
            );

            if(!$validator->passes()) {
				$errors = (array) $validator->errors()->all();
				$error_message = $errors[0];
                return response()->json([
                    'status'  => false,
                    'message' => $error_message,
                ]);
			}
            $cur_tenant_id = $this->cur_tenant_id;
            $template_old_name = "";
            // check the case
            if($request->has('id') && !empty($request->id)) {
                // check if template exist or not
                $template = Templates::where('id', $request->id)
                ->where('tenant_id', $cur_tenant_id)
                ->first();
                if(!isset($template->id)) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Requested template not found',
                    ]);
                }

                // check if name already exists
                $is_exist = Templates::where('template_name', $request->template_name)
                ->where('id', '!=', $request->id)
                ->first();
                if(isset($is_exist->id)) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Template name already exists',
                    ]);
                }
                else {
                    $template_old_name = $template->template_name;
                    $template->template_name = $request->template_name;
                    $template->template_description = $request->template_description;
                    $template->is_default = $request->is_default;
                    $template->save();
                    $template->old_name = $template_old_name;
                    return response()->json([
                        'status'  => true,
                        'message' => 'Template updated successfully',
                        'template' => $template,
                    ]);
                }
            }
            else {
                $is_exist = Templates::where('template_name', $request->template_name)
                ->first();
                if(isset($is_exist->id)) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Template name already exists',
                    ]);
                }
                else {
                    $template = Templates::create([
                        'template_name' => $request->template_name,
                        'template_description' => $request->template_description,
                        'tenant_id' => $cur_tenant_id,
                        'is_default' => $request->is_default
                    ]);
                    if(isset($template->id)) {
                        return response()->json([
                            'status'  => true,
                            'message' => 'Template created successfully',
                            'template' => $template,
                        ]);
                    }
                    else {
                        return response()->json([
                            'status'  => true,
                            'message' => 'Unable to create template at the moment',
                        ]);
                    }
                }
            }

        } catch (\Exception $exception) {
            return response()->json([
                'status'  => false,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Get Custom Template By Id
     */
    function get_custom_template(Request $request)
    {
        try {
            $template = Templates::where('id', $request->template_id)
            ->where('tenant_id', $this->cur_tenant_id)
            ->first();
            if(isset($template->id)) {
                return response()->json([
                    'status' => true,
                    'message' => 'Here is template',
                    'template' => $template
                ]);
            }
            else {
                return response()->json([
                    'status' => false,
                    'message' => 'Requested template not found',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Unable to get requested template at the moment',
            ]);
        }
    }

    /**
     *  [POST] Update timeline mapping on config table
     */
    public function updateTimelineMappingConfig(Request $request)
    {
        try {
            $config = Config::where('tenant_id', $this->cur_tenant_id)->first();
            $config->is_display_timeline = $config->is_display_timeline ? false : true;
            $config->save();

            return response()->json([
                'message' => 'Setting saved successfully!'
            ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
