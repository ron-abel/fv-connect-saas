<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log as Logging;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;

use Stichoza\GoogleTranslate\GoogleTranslate;
use App\Actions\NotificationFileVineNoteAction;
use App\Services\NotificationHandlerService;
use Carbon\Carbon;
use Exception;
use App\Services\VariableService;

use App\Models\ClientNotification;
use App\Models\Tenant;
use App\Models\Blacklist;
use App\Models\Log;
use App\Models\Feedbacks;
use App\Models\FvNoteComment;
use App\Models\LegalteamConfig;
use App\Models\LegalteamPersonConfig;
use App\Models\TenantCustomVital;
use App\Models\ConfigProjectVital;
use App\Models\FeedbackNoteManager;
use App\Models\FvClients;
use App\Models\LanguageLog;
use App\Models\TenantForm;
use App\Models\TenantFormResponse;
use App\Models\TenantNotificationConfig;
use App\Models\User;
use App\Services\FilevineService;
use App\Services\SendGridServices;
use Mockery\Generator\StringManipulation\Pass\Pass;
use App\Models\TenantFormMapping;
use App\Models\ClientFileUploadConfiguration;
use App\Models\ConfigCustomProjectName;
use App\Models\FvTeamMembers;
use App\Models\CalendarSetting;
use App\Models\TenantNotificationLog;
use App\Models\FvSharedDocument;
use App\Models\FvClientUploadDocument;

class LookupController extends Controller
{

    private $sendGridServices;
    public $cur_tenant_id;
    public function __construct()
    {
        Controller::setSubDomainName();
        $this->cur_tenant_id = session()->get('tenant_id');
        $this->sendGridServices = new SendGridServices($this->cur_tenant_id);
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

            $subdomain_name = session()->get('subdomain');
            if (Session::has('contact_id') && !empty(session()->get('contact_id'))) {
                $contact_id =  session()->get('contact_id');
            } else {
                return redirect()->route('client', ['subdomain' => $subdomain_name]);
            }

            $tenant_id = $this->cur_tenant_id;
            $config_details = DB::table('config')->where('tenant_id', $tenant_id)->first();
            $config_details_custom = ConfigCustomProjectName::where('tenant_id', $tenant_id)->first();
            $tenant_data = Tenant::find($tenant_id);
            if (isset($tenant_data->tenant_law_firm_name)) {
                $tenant_name = $tenant_data->tenant_law_firm_name;
            } else {
                $tenant_name = $tenant_data->tenant_name;
            }
            $blacklists = Blacklist::where('tenant_id', $tenant_id)->where('is_allow_client_potal', 0)->get();

            // Get tenant_custom_vitals table data
            $tenant_custom_vital = TenantCustomVital::where('tenant_id', $tenant_id)->first();

            $calendar_setting = CalendarSetting::where('tenant_id', $tenant_id)->first();
            $calendar_visibility = isset($calendar_setting->calendar_visibility) ? $calendar_setting->calendar_visibility : false;

            $apiurl = config('services.fv.default_api_base_url');
            if (isset($tenant_data->fv_api_base_url) and !empty($tenant_data->fv_api_base_url)) {
                $apiurl = $tenant_data->fv_api_base_url;
            }

            $filevine_api = new FilevineService($apiurl, "");

            $blacklistProjectIds = $blacklists->pluck("fv_project_id")->toArray();

            $offset = 0;
            $limit = 1000;
            $project_ids = [];
            $all_projects_data = null;

            do {
                $client_projects = json_decode($filevine_api->getProjectsByContactId($contact_id, $limit, $offset), TRUE);
                $next_link = trim($client_projects['links']['next']);

                if (isset($client_projects['items'])) {
                    foreach ($client_projects['items'] as $each_project) {
                        // validate if the project['project']['phaseName'] is "Archived"
                        if (isset($each_project['project']['phaseName'], $config_details->is_show_archieved_phase) && $each_project['project']['phaseName'] == 'Archived' && $config_details->is_show_archieved_phase == 0) {
                            continue;
                        }

                        $params['projectId'] = $each_project['projectId']['native'];
                        if (in_array($params['projectId'], $project_ids) || in_array($params['projectId'], $blacklistProjectIds)) {
                            continue;
                        }

                        // check the client role of the project.
                        $contact_role = $each_project['role'] ?? "";
                        if (strtolower($contact_role) !== 'client') {
                            continue;
                        }

                        $all_projects_data[] = $each_project;
                    }
                }
                $offset += $limit;
            } while ($next_link);
            if (!empty($all_projects_data)) {
                $lookup_data = [];
                $selected_client_native_id = null;
                $redirect = false;
                foreach ($all_projects_data as $data) {

                    if (in_array($data['projectId']['native'], $blacklistProjectIds)) {
                        if (($lookup_project_id == $data['projectId']['native'])) {
                            foreach ($all_projects_data as $d) {
                                if (!in_array($d['projectId']['native'], $blacklistProjectIds)) {
                                    return redirect()->route('lookup', ['subdomain' => session()->get('subdomain'), 'lookup_project_id' => $d['projectId']['native']]);
                                }
                            }
                        }
                        continue;
                    }
                    if ($data == "" || !isset($data['projectId'])) {
                        return redirect()->route('client', ['subdomain' => $subdomain_name]);
                    } else {
                        if (isset($data['project']['clientId']['native'], $data['project']['projectTypeId']['native'], $data['projectId']['native']) && $data['projectId']['native'] == $lookup_project_id) {
                            $description = "";
                            $phase_category_id = "";
                            $project_type_id = "";
                            $project_id = "";
                            $project_type_name = "";
                            $mapped_template_name = "";
                            $phase_categories = [];
                            $phase_category = [];
                            $last_login = "";
                            $mapped_template_id = 0;

                            // get the projectTypeId
                            $project_type_id = $data['project']['projectTypeId']['native'];
                            $project_id = $data['projectId']['native'];
                            $selected_client_native_id = $data['project']['clientId']['native'];
                            $tenant_phase_mapping_override_title = DB::table('tenant_phase_mapping_override_titles')
                                ->where('tenant_id', $this->cur_tenant_id)
                                ->where('fv_project_type_id', $project_type_id)
                                ->first();

                            // check for phase mapping
                            $is_not_phase_mapping = true;
                            if (isset($data['project']['phaseId']['native'])) {
                                $type_phase_id = $data['project']['phaseId']['native'];
                                $mappings  = DB::table('phase_mappings')
                                    ->where('tenant_id', $tenant_id)
                                    ->where('Type_Phase_Id', $type_phase_id)
                                    ->get();

                                /* if (count($mappings) <= 0 && isset($config_details->default_phase_mapping) && $config_details->default_phase_mapping) {
                                    $mappings  = DB::table('phase_mappings')
                                        ->where('tenant_id', $tenant_id)
                                        ->where('is_default', true)
                                        ->get();
                                } */

                                if (count($mappings) <= 0) {
                                    $mapping_min = DB::select("SELECT MAX(type_phase_id) AS type_phase_id FROM `phase_mappings` WHERE tenant_id = $tenant_id AND type_phase_id < $type_phase_id");
                                    if (count($mapping_min)) {
                                        $temp_phase_id = $mapping_min[0]->type_phase_id;
                                        $mappings  = DB::table('phase_mappings')
                                            ->where('tenant_id', $tenant_id)
                                            ->where('Type_Phase_Id', $temp_phase_id)
                                            ->get();
                                    }
                                }

                                if (count($mappings) > 0) {
                                    $is_not_phase_mapping = false;
                                    if (isset($mappings[0]->phase_category_id)) {
                                        $phase_category_id = $mappings[0]->phase_category_id;
                                    }

                                    if (isset($mappings[0]->phase_description)) {
                                        $description  = $mappings[0]->phase_description;
                                        $data['project']['phaseName'] = $mappings[0]->type_phase_name;
                                        $description  = $this->parsePhaseDescription($description, $tenant_name, $data, 'phase_mapping');
                                    }
                                }
                            }

                            $project_type_id = empty($project_type_id) ? "0" : $project_type_id;

                            // get project type name
                            $project_type = DB::select(DB::raw("
                                                select pm.project_type_name, pm.overrite_phase_name, pt.template_category_name, pt.template_id, pc.override_phase_name
                                                from phase_mappings as pm
                                                LEFT join phase_categories pc on pm.phase_category_id=pc.id
                                                LEFT join template_categories pt on pc.template_category_id=pt.id
                                                where pm.tenant_id = $tenant_id and project_type_id = " . $project_type_id . " and pm.type_phase_id = " . $type_phase_id . ""));

                            if (is_array($project_type) && count($project_type) <= 0) {
                                $project_type = DB::select(DB::raw("
                                                select pm.project_type_name, pm.overrite_phase_name, pt.template_category_name, pt.template_id, pc.override_phase_name
                                                from phase_mappings as pm
                                                LEFT join phase_categories pc on pm.phase_category_id=pc.id
                                                LEFT join template_categories pt on pc.template_category_id=pt.id
                                                where pm.tenant_id = $tenant_id and project_type_id = " . $project_type_id));
                            }
                            if (count($project_type) > 0 && isset($project_type[0])) {
                                if (!empty($project_type[0]->overrite_phase_name)) {
                                    $data['project']['phaseName'] = $project_type[0]->overrite_phase_name;
                                }
                                if (isset($project_type[0]->project_type_name)) {
                                    $project_type_name = $project_type[0]->project_type_name;
                                }
                                if (isset($project_type[0]->template_category_name)) {
                                    $mapped_template_name = $project_type[0]->template_category_name;
                                }
                                if (isset($project_type[0]->template_id)) {
                                    $mapped_template_id = $project_type[0]->template_id;
                                }
                            } else {
                                // get the projectType from FV API
                                $projectTypeObject = json_decode($filevine_api->getProjectTypeObject($project_type_id), TRUE);
                                if (isset($projectTypeObject['name'])) {
                                    $project_type_name = $projectTypeObject['name'];
                                    $projectTypeArray = explode("-", $project_type_name);
                                    if (isset($projectTypeArray) and !empty($projectTypeArray)) {
                                        $project_type_name = $projectTypeArray[0];
                                    }
                                    $mapped_template = DB::select(DB::raw("
                                                            select *
                                                            from templates
                                                            where template_name like '%$project_type_name%';
                                                            "));
                                    if (count($mapped_template) > 0 && isset($mapped_template[0]->template_name, $mapped_template[0]->id)) {
                                        $mapped_template_name = $mapped_template[0]->template_name;
                                        $mapped_template_id = $mapped_template[0]->id;
                                    }
                                }
                            }

                            // get phase categories and selected template
                            if ($mapped_template_name != "") {
                                $phase_category = DB::select(DB::raw("
                                                        select pc.id, pc.phase_category_name, pc.phase_category_description, pt.template_category_name, pt.template_id, pc.override_phase_name
                                                        from phase_categories pc
                                                        inner join template_categories pt on pc.template_category_id=pt.id
                                                        where pc.tenant_id = $tenant_id and pt.template_category_name = '$mapped_template_name';"));
                                if (isset($phase_category[0], $phase_category[0]->id)) {
                                    $phase_category_id = $phase_category[0]->id;
                                }
                            }
                            if ($selected_client_native_id) {
                                $last_login_details = Log::where('fv_client_id', $selected_client_native_id)
                                    ->where('tenant_id', $tenant_id)
                                    ->orderBy('id', 'DESC')
                                    ->limit(1)->first();
                                if ($last_login_details && isset($last_login_details->created_at)) {
                                    $last_login = date_create($last_login_details->created_at);
                                    $last_login =  date_format($last_login, "Y/m/d H:i A");
                                } else {
                                    $last_login = "";
                                }
                            }
                            $filevine_api = new FilevineService($apiurl, "");

                            $project_vital_details = json_decode($filevine_api->getProjectsVitalsById($project_id));
                            $project_vital_data = [];
                            $project_top_vitals = [];
                            $project_top_vitals_temp = [];

                            if (!empty($project_vital_details)) {

                                foreach ($project_vital_details as $single_vital_details) {
                                    if (isset($single_vital_details->friendlyName, $single_vital_details->value)) {
                                        if ($single_vital_details->friendlyName == 'SOL') {
                                            if (array_key_exists("value", (array)$single_vital_details)) {
                                                $project_vital_data['sol'] = $single_vital_details->value;
                                            } else {
                                                $project_vital_data['sol'] = "";
                                            }
                                        } elseif ($single_vital_details->fieldName == 'projectSmsNumber') {
                                            if (array_key_exists("value", (array)$single_vital_details)) {
                                                if (strpos($single_vital_details->value, "+1") !== false) {
                                                    $project_vital_data['phone'] = $single_vital_details->value;
                                                } else {
                                                    $project_vital_data['phone'] = "+1" . $single_vital_details->value;
                                                }
                                            } else {
                                                $project_vital_data['phone'] = "";
                                            }
                                        } elseif ($single_vital_details->fieldName == 'projectEmail') {
                                            if (array_key_exists("value", (array)$single_vital_details)) {
                                                $project_vital_data['email'] = $single_vital_details->value;
                                            } else {
                                                $project_vital_data['email'] = "";
                                            }
                                        } elseif ($single_vital_details->friendlyName == 'Case Type') {
                                            if (array_key_exists("value", (array)$single_vital_details)) {
                                                $project_vital_data['case'] = $single_vital_details->value;
                                            } else {
                                                $project_vital_data['case'] = "";
                                            }
                                        }

                                        // Capture all vitals
                                        if (!empty($single_vital_details->value)) {
                                            if ($single_vital_details->fieldType == 'DateOnly' || $single_vital_details->fieldType == 'DateUtc') {
                                                $project_top_vitals_temp[$single_vital_details->fieldName] = [
                                                    'key' => $single_vital_details->friendlyName,
                                                    'value' => (new \DateTime($single_vital_details->value))->format('m/d/Y')
                                                ];
                                            } elseif ($single_vital_details->fieldType == 'Currency') {
                                                $project_top_vitals_temp[$single_vital_details->fieldName] = [
                                                    'key' => $single_vital_details->friendlyName,
                                                    'value' => '$' . number_format($single_vital_details->value, 2)
                                                ];
                                            } elseif ($single_vital_details->fieldType == 'Percent') {
                                                $project_top_vitals_temp[$single_vital_details->fieldName] = [
                                                    'key' => $single_vital_details->friendlyName,
                                                    'value' => number_format($single_vital_details->value, 2) . '%'
                                                ];
                                            } elseif ($single_vital_details->fieldType == 'E164PhoneNumber' && isset($tenant_custom_vital->is_show_project_sms_number) && !empty($tenant_custom_vital->is_show_project_sms_number)) {
                                                $project_top_vitals_temp[$single_vital_details->fieldName] = [
                                                    'key' => $single_vital_details->friendlyName,
                                                    'value' => substr($single_vital_details->value, 0, 2) . "(" . substr($single_vital_details->value, 2, 3) . ")" . " " . substr($single_vital_details->value, 5, 3) . "-" . substr($single_vital_details->value, 8),
                                                    'is_sms_number' => 1
                                                ];
                                            } elseif ($single_vital_details->fieldType == 'E164PhoneNumber') {
                                                $project_top_vitals_temp[$single_vital_details->fieldName] = [
                                                    'key' => $single_vital_details->friendlyName,
                                                    'value' => substr($single_vital_details->value, 0, 2) . "(" . substr($single_vital_details->value, 2, 3) . ")" . " " . substr($single_vital_details->value, 5, 3) . "-" . substr($single_vital_details->value, 8),
                                                    'is_sms_number' => 1
                                                ];
                                            } elseif ($single_vital_details->fieldType == 'Boolean') {
                                                //$value = explode("/", $single_vital_details->value);
                                                $project_top_vitals_temp[$single_vital_details->fieldName] = [
                                                    'key' => $single_vital_details->friendlyName,
                                                    'value' => $single_vital_details->value
                                                ];
                                            } elseif ($single_vital_details->fieldType == 'Url') {
                                                $project_top_vitals_temp[$single_vital_details->fieldName] = [
                                                    'key' => $single_vital_details->friendlyName,
                                                    'value' => $single_vital_details->value,
                                                    'is_url' => 1
                                                ];
                                            } elseif ($single_vital_details->fieldType == 'MailTo' && isset($tenant_custom_vital->is_show_project_email) && !empty($tenant_custom_vital->is_show_project_email)) {
                                                $project_top_vitals_temp[$single_vital_details->fieldName] = [
                                                    'key' => $single_vital_details->friendlyName,
                                                    'value' => $single_vital_details->value,
                                                    'is_mail' => 1
                                                ];
                                            } elseif ($single_vital_details->fieldType == 'StringList') {
                                                $project_top_vitals_temp[$single_vital_details->fieldName] = [
                                                    'key' => $single_vital_details->friendlyName,
                                                    'value' => substr($single_vital_details->value, 1, -1)
                                                ];
                                            } else {
                                                $project_top_vitals_temp[$single_vital_details->fieldName] = [
                                                    'key' => $single_vital_details->friendlyName,
                                                    'value' => $single_vital_details->value
                                                ];
                                            }
                                        }
                                    }
                                }


                                // Finalize vital based on configuration and order
                                $config_project_vitals = ConfigProjectVital::where('tenant_id', $tenant_id)->where('fv_project_type_id', $project_type_id)->get();

                                foreach ($config_project_vitals as $record) {
                                    if (array_key_exists($record->vital_name, $project_top_vitals_temp)) {
                                        $project_top_vitals_temp[$record->vital_name]['key'] = empty($record->override_title) ? $record->friendly_name : $record->override_title;
                                        if (isset($project_top_vitals_temp[$record->vital_name]['value']) && !empty($project_top_vitals_temp[$record->vital_name]['value'])) {
                                            $project_top_vitals[] = $project_top_vitals_temp[$record->vital_name];
                                        }
                                    }
                                }
                            } else {
                                $project_vital_data['sol'] = "";

                                $project_vital_data['case'] = "";
                            }

                            if (!isset($project_vital_data['phone']) || $project_vital_data['phone'] === "") {
                                // If phone is empty, get phone number from tenant custom vitals configuration
                                $project_vital_data['phone'] = isset($tenant_custom_vital->display_phone_number) ? $tenant_custom_vital->display_phone_number : "";
                            }

                            if (!isset($project_vital_data['email']) || $project_vital_data['email'] === "") {
                                // If email is empty, then get email from project details
                                $project_vital_data['email'] = isset($data['project']['projectEmailAddress']) ? $data['project']['projectEmailAddress'] : "";
                            }

                            $tabs = "";
                            $pannels = "";
                            $mobileNav = "";
                            $mobileTimeline = "";
                            $phase_categories = DB::select(DB::raw("
                                                        select pc.id, pc.phase_category_name, pc.is_default, pc.override_phase_name, pc.phase_category_description, pc.template_category_id, pt.template_category_name
                                                        from phase_categories pc
                                                        inner join template_categories pt on pc.template_category_id=pt.id
                                                        where pc.tenant_id = $tenant_id and pt.template_id = $mapped_template_id order by sort_order asc;"));
                            if (count($phase_categories) <= 0) {
                                $phase_categories = DB::select(DB::raw("
                                                        select pc.id, pc.phase_category_name, pc.is_default, pc.override_phase_name, pc.phase_category_description, pc.template_category_id, pt.template_category_name
                                                        from phase_categories pc
                                                        inner join template_categories pt on pc.template_category_id=pt.id
                                                        where pc.tenant_id = $tenant_id and pc.is_default = 1 order by sort_order asc;"));
                            }
                            if (count($phase_categories) > 0) {
                                $mobileNav .= '<div class="timeline-menu-container">';
                                foreach ($phase_categories as $pc) {
                                    if (isset($config_details->is_show_archieved_phase, $pc->phase_category_name)) {
                                        $cat_id = $pc->id;
                                        $cat_name = ucfirst(($pc->override_phase_name ? $pc->override_phase_name : $pc->phase_category_name));
                                        $id_name = $cat_name . '-tab';
                                        $tab_active_class = ($cat_id == $phase_category_id) ? 'active' : '';
                                        $panel_active_class = ($cat_id == $phase_category_id) ? 'show active' : '';
                                        $cat_description = $pc->phase_category_description;
                                        $cat_description = $this->parsePhaseDescription($cat_description, $tenant_name, $data);
                                        $mobileNav .= '<div class="timeline-menu ' . $tab_active_class . '"onclick="showtimelinecontent(this,' . $cat_id . ')">' . $tr->translate($cat_name) . '</div>';
                                        $mobileTimeline .= '<li class="nav-item timeline-menu ' . $tab_active_class . '"onclick="showtimelinecontent(this,' . $cat_id . ')">' . $tr->translate($cat_name) . '</li>';
                                    }
                                }
                                $mobileNav .= '</div>';
                                $pannels .= '<div class="timeline-content">';

                                foreach ($phase_categories as $key => $pc) {
                                    if (isset($config_details->is_show_archieved_phase, $pc->phase_category_name)) {
                                        $cat_id = $pc->id;
                                        $cat_name = strtolower($pc->override_phase_name ? $pc->override_phase_name : $pc->phase_category_name);
                                        $tab_active_class = ($cat_id == $phase_category_id) ? 'active' : '';
                                        $panel_active_class = ($cat_id == $phase_category_id) ? 'show active' : '';

                                        $cat_desc_count = str_word_count($pc->phase_category_description);
                                        if ($cat_desc_count < config('app.max_count_description')) {
                                            $cat_description = $pc->phase_category_description;
                                            $cat_description = $this->parsePhaseDescription($cat_description, $tenant_name, $data);
                                        } else {
                                            $cat_description_explode = explode(" ", $pc->phase_category_description);
                                            $cat_description_slice = array_slice($cat_description_explode, 0, config('app.max_count_description'));
                                            $cat_description =  implode(" ", $cat_description_slice);
                                            $cat_description = $this->parsePhaseDescription($cat_description, $tenant_name, $data);
                                        }

                                        $pannels .= '<div class="timeline-content-item ' . $panel_active_class . '" id="' . $cat_id . '">
                                        <span>' . preg_replace('/\/\s/', '/', $tr->translate($cat_description)) . '</span>
                                    </div>';
                                    }
                                }
                                $pannels .= "</div>";
                            }

                            // get notes of project
                            $note_details = [];
                            $hash_note_details = [];
                            $notes = json_decode($filevine_api->getNotesByProjectId($project_id), TRUE);
                            if ($notes && isset($notes['items']) && count($notes['items']) > 0) {
                                foreach ($notes['items'] as $note) {
                                    $note_tmp = [];
                                    if (isset($note['authorId']) && $note['authorId'] != null) {
                                        $dt = new \DateTime($note['createdAt']);
                                        $note_tmp = [
                                            'note_id' => $note['noteId']['native'],
                                            'posted_at' => new \DateTime($note['createdAt']),
                                            'author_id' => $note['authorId']['native'],
                                            'body' => $note['body'],
                                            'allow_editing' => $note['allowEditing'],
                                            'is_pinned' => $note['isPinnedToProject']
                                        ];
                                    } else {
                                        continue;
                                    }

                                    // check for hash note
                                    if (strpos($note['body'], config('services.fv.legal_team_note_prefix')) !== false) {
                                        $note_tmp['body'] = trim(str_replace(config('services.fv.legal_team_note_prefix'), '', $note_tmp['body']));
                                        if (count($hash_note_details) == 0) {
                                            $hash_note_details = $note_tmp;
                                        } elseif (count($hash_note_details) > 0 && $dt > $hash_note_details['posted_at']) {
                                            $hash_note_details = $note_tmp;
                                        }
                                    }

                                    // other logic
                                    // if ($note['isPinnedToProject']) {
                                    //     $note_details = $note_tmp;
                                    // }
                                }
                                if (count($hash_note_details) > 0) {
                                    $note_details = $hash_note_details;
                                }
                            }
                            // check for author of note
                            if (count($note_details) > 0 && isset($note_details['author_id'])) {
                                $author = json_decode($filevine_api->getUserById($note_details['author_id']), TRUE);
                                if ($author && isset($author['user'])) {
                                    $fname = isset($author['user']['firstName']) ? $author['user']['firstName'] : '';
                                    $lname = isset($author['user']['lastName']) ? $author['user']['lastName'] : '';
                                    $note_details['author'] = $fname . ' ' . $lname;
                                } else {
                                    $note_details['author'] = '';
                                }
                            }


                            $notification = ClientNotification::where('tenant_id', $this->cur_tenant_id)
                                ->where('start_date', '<=', Carbon::today()->toDateString())
                                ->where('end_date', '>=', Carbon::today()->toDateString())
                                ->where('is_active', 1)
                                ->get();

                            $variable_service = new VariableService();

                            $clientName = isset($data['project']['clientName']) ? $data['project']['clientName'] : '';
                            list($client_firstname) = explode(' ', $clientName);

                            $additional_data = [
                                'client_firstname' => $client_firstname,
                                'client_fullname' => isset($data['project']['clientName']) ? $data['project']['clientName'] : '',
                                'law_firm_name' => $tenant_name,
                                'client_portal_url' => route('client', ['subdomain' => $subdomain]),
                                'project_name' => isset($data['project']['projectName']) ? $data['project']['projectName'] : '',
                                'project_phase' => isset($data['project']['phaseName']) ? $data['project']['phaseName'] : '',
                                'tenant_id' => $this->cur_tenant_id,
                                'fv_project_id' => isset($data['projectId']['native']) ? $data['projectId']['native'] : 0,
                            ];

                            foreach ($notification as $record) {
                                $notice_body = $variable_service->updateVariables($record->notice_body, 'is_client_banner_message', $additional_data);
                                $record->notice_body = $tr->translate($notice_body);
                            }

                            $legal_teams = [];
                            $personFieldsTeam = [];
                            if ($config_details->is_legal_team_by_roles) {
                                $legal_teams = $this->_getLegalteamConfigInfo($tenant_id, $project_id);
                            } else {
                                $personFieldsTeam = $this->_getLegalteamPersonConfigInfo($project_type_id, $project_id);
                            }

                            $tenant_phase_category_override_title = DB::table('tenant_phase_category_override_titles')
                                ->where('tenant_id', $this->cur_tenant_id)
                                ->where('template_id',  $mapped_template_id)->first();
                            $tenant_override_titles = DB::table('tenant_override_titles')
                                ->where('tenant_id', $this->cur_tenant_id)->first();
                            $data['role'] = isset($data['role']) && $data['role'] ? $tr->translate($data['role']) : "";


                            // Get phase data based on default phase mapping config
                            /*  if (isset($config_details->default_phase_mapping) && !$config_details->default_phase_mapping && $is_not_phase_mapping) {
                                $phase_name = isset($data['project']['phaseName']) ? $data['project']['phaseName'] : "";
                                $timeline_row = DB::select("SELECT * FROM `phase_categories` WHERE `phase_category_name` != '$phase_name' AND tenant_id = $tenant_id AND id < (SELECT id FROM `phase_categories` WHERE `phase_category_name` = '$phase_name' AND tenant_id = $tenant_id LIMIT 1) ORDER BY id DESC LIMIT 1;");
                                if (count($timeline_row)) {
                                    $data['project']['phaseName'] = $timeline_row[0]->phase_category_name;
                                    $description = $this->parsePhaseDescription($timeline_row[0]->phase_category_description, $tenant_name, $data, 'phase_mapping');
                                }
                            } */

                            $data['project']['phaseName'] = isset($data['project']['phaseName']) ? $tr->translate($data['project']['phaseName']) : "";

                            foreach ($legal_teams as $key => $legal_team_member) {
                                if (isset($legal_team_member['roles']) && count($legal_team_member['roles']) > 0) {
                                    foreach ($legal_team_member['roles'] as $role_key => $legal_team_member_role) {
                                        $legal_teams[$key]['roles'][$role_key]['name'] = $tr->translate($legal_team_member_role['name']);
                                    }
                                } else {
                                    $legal_teams[$key]['role_title'] = $tr->translate($legal_team_member['role_title']);
                                }
                            }
                            if (count($personFieldsTeam) > 0) {
                                foreach ($personFieldsTeam as $key => $legal_team_member_by_person) {
                                    if ($legal_team_member_by_person['config']->fv_person_field_name) {
                                        $personFieldsTeam[$key]['config']->fv_person_field_name = $tr->translate($legal_team_member_by_person['config']->fv_person_field_name);
                                    }
                                }
                            }
                            $customize_message = $this->customizeLegalTeamMessage($note_details);
                            if (isset($customize_message['body'])) {
                                $customize_message['body'] = $this->convertTeamMessageLinks($customize_message['body']);
                                $customize_message['body'] = $tr->translate($customize_message['body']);
                            }
                            foreach ($project_top_vitals as $key => $value) {
                                $project_top_vitals[$key]['key'] = $tr->translate($value['key']);
                            }

                            // override project title
                            $project_override_name = $this->customizeProjectName($filevine_api, $config_details, $config_details_custom, $project_id, $data['project']['clientName']);


                            // Calendar Apppointment List Last 3
                            $appointment_items = [];
                            if ($calendar_visibility) {
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
                                            'location' => $appointment['location'],
                                            'all_day' => $appointment['allDay']
                                        ];
                                    }
                                }
                            }


                            $lookup_data[] = array(
                                'last_login' => $tr->translate($last_login),
                                'results'    => $data,
                                'description' => $tr->translate($description),
                                'project_vital_data' => $project_vital_data,
                                'project_top_vitals' => $project_top_vitals,
                                'project_type_name'  => $tr->translate($project_type_name),
                                'phase_category'     => $phase_category,
                                'tabs'               => $tabs,
                                'pannels'            => $pannels,
                                'mobileNav'          => $mobileNav,
                                'mobileTimeline'     => $mobileTimeline,
                                'config_details'     => $config_details,
                                'note'               => $customize_message,
                                'project_id'         => $project_id,
                                'notification'       => $notification,
                                'active_project_id'  => $lookup_project_id,
                                'legal_teams'        => $legal_teams,
                                'tenant_phase_category_override_title' => $tr->translate($tenant_phase_category_override_title ? Str::replaceArray('[projectTypeName]', [$project_type_name], $tenant_phase_category_override_title->title) : ""),
                                'tenant_phase_mapping_override_title' => $tr->translate($tenant_phase_mapping_override_title ? Str::replaceArray('[phaseName]', [$data['project']['phaseName']], $tenant_phase_mapping_override_title->title) : ""),
                                'tenant_override_title' => $tr->translate($tenant_override_titles->title ?? ""),
                                'personFieldsTeam'   => $personFieldsTeam,
                                'project_override_name' => $project_override_name,
                                'appointment_items' => $appointment_items
                            );
                        } else {
                            $lookup_data[] = array(
                                'results'    => $data,
                                'project_id' => $data['projectId']['native'],
                                'active_project_id' => $lookup_project_id,
                                'project_override_name' => $this->customizeProjectName($filevine_api, $config_details, $config_details_custom, $data['projectId']['native'], $data['project']['clientName'])
                            );
                        }

                        $project_list_info[] = array(
                            'results'    => $data,
                            'project_id' => $data['projectId']['native'],
                            'active_project_id' => $lookup_project_id,
                            'project_override_name' => $this->customizeProjectName($filevine_api, $config_details, $config_details_custom, $data['projectId']['native'], $data['project']['clientName'])
                        );
                    }
                }

                // Save project list info into session
                Session::put('project_list_info', $project_list_info);

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
                    'client.pages.lookup',
                    [
                        'lookup_data' => $lookup_data,
                        'selected_client_native_id' => $selected_client_native_id,
                        'tenant_custom_vital' => $tenant_custom_vital,
                        'lookup_project_id' => $lookup_project_id,
                        'calendar_visibility' => $calendar_visibility
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
     *  [GET] Look up team message page for client
     */
    public function myTeamMessages(Request $request, $subdomain, $lookup_project_id)
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

            $calendar_setting = CalendarSetting::where('tenant_id', $tenant_id)->first();
            $calendar_visibility = isset($calendar_setting->calendar_visibility) ? $calendar_setting->calendar_visibility : false;

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
                        } else {
                            $last_login = "";
                        }
                    }
                    $note_details = [];
                    $hash_note_details = [];
                    $notes = json_decode($filevine_api->getNotesByProjectId($project_id), TRUE);
                    if ($notes && isset($notes['items']) && count($notes['items']) > 0) {
                        foreach ($notes['items'] as $note) {
                            $note_tmp = array();
                            if (isset($note['authorId']) && $note['authorId'] != null) {
                                $dt = new \DateTime($note['createdAt']);
                                $note_tmp = [
                                    'note_id' => $note['noteId']['native'],
                                    'posted_at' => new \DateTime($note['createdAt']),
                                    'author_id' => $note['authorId']['native'],
                                    'body' => $note['body'],
                                    'allow_editing' => $note['allowEditing'],
                                    'is_pinned' => $note['isPinnedToProject']
                                ];
                                if (isset($note['authorId']['native'])) {
                                    $author = json_decode($filevine_api->getUserById($note['authorId']['native']), TRUE);
                                    if ($author && isset($author['user'])) {
                                        $fname = isset($author['user']['firstName']) ? $author['user']['firstName'] : '';
                                        $lname = isset($author['user']['lastName']) ? $author['user']['lastName'] : '';
                                        $note_tmp['author'] = $fname . ' ' . $lname;
                                    } else {
                                        $note_tmp['author'] = '';
                                    }
                                }
                                $note_tmp = $this->customizeLegalTeamMessage($note_tmp);
                            } else {
                                continue;
                            }

                            if (strpos($note['body'], config('services.fv.legal_team_note_prefix')) !== false) {
                                $note_tmp['body'] = trim(str_replace(config('services.fv.legal_team_note_prefix'), '', $note_tmp['body']));

                                // update the URL with a tag
                                $note_tmp['body'] = $this->convertTeamMessageLinks($note_tmp['body']);

                                $hash_note_details[] = $note_tmp;
                            }
                        }
                        if (count($hash_note_details) > 0) {
                            $note_details = $hash_note_details;
                        }
                    }

                    $lookup_data[] = array(
                        'last_login' => $tr->translate($last_login),
                        'results'    => $project_details,
                        'config_details'     => $config_details,
                        'note'               => array_reverse($note_details),
                        'active_project_id'  => $lookup_project_id,
                        'project_id'  => $project_id,
                        'project_override_name' => $this->customizeProjectName($filevine_api, $config_details, $config_details_custom, $project_details['projectId']['native'], $project_details['clientName'])
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
                    'client.pages.my_team_messages',
                    [
                        'lookup_data' => $lookup_data,
                        'selected_client_native_id' => $selected_client_native_id,
                        'lookup_project_id' => $lookup_project_id,
                        'calendar_visibility' => $calendar_visibility
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
     *  [GET] Look up active forms for client
     */
    public function forms(Request $request, $subdomain, $lookup_project_id)
    {
        try {
            $tr = new GoogleTranslate();
            $tr->setSource();
            $tr->setTarget(!is_null(session()->get('lang')) ? session()->get('lang') : 'en');
            $tenant_id = $this->cur_tenant_id;
            $tenant_data = Tenant::find($tenant_id);
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

            $calendar_setting = CalendarSetting::where('tenant_id', $tenant_id)->first();
            $calendar_visibility = isset($calendar_setting->calendar_visibility) ? $calendar_setting->calendar_visibility : false;

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
                        } else {
                            $last_login = "";
                        }
                    }
                    $active_forms = TenantForm::where(['tenant_id' => $tenant_id, 'is_active' => 1, 'deleted_at' => null])->latest()->get();
                    foreach ($active_forms as $form) {
                        $form->form_name = !empty($form->form_name) ? $tr->translate($form->form_name) : $form->form_name;
                        $form->form_description = !empty($form->form_description) ? $tr->translate($form->form_description) : $form->form_description;
                    }

                    $lookup_data = array(
                        'last_login' => $tr->translate($last_login),
                        'results'    => $project_details,
                        'config_details'     => $config_details,
                        'active_project_id'  => $lookup_project_id,
                        'project_id'  => $project_id,
                        'active_forms' => $active_forms,
                        'project_override_name' => $this->customizeProjectName($filevine_api, $config_details, $config_details_custom, $project_details['projectId']['native'], $project_details['clientName'])
                    );
                } else {
                    $lookup_data = array(
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
                    'client.pages.forms',
                    [
                        'lookup_data' => $lookup_data,
                        'selected_client_native_id' => $selected_client_native_id,
                        'lookup_project_id' => $lookup_project_id,
                        'calendar_visibility' => $calendar_visibility
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
     * show form for submiting response
     */
    public function showForm(Request $request, $subdomain)
    {
        try {
            if (request()->tenant_id == $this->cur_tenant_id && !is_null(request()->name) && !is_null(request()->lookup_project_id)) {
                $lookup_project_id = request()->lookup_project_id;
                $tr = new GoogleTranslate();
                $tr->setSource();
                $tr->setTarget(!is_null(session()->get('lang')) ? session()->get('lang') : 'en');
                $tenant_id = $this->cur_tenant_id;
                $tenant_data = Tenant::find($tenant_id);
                $selected_client_native_id = null;

                $config_details = DB::table('config')->where('tenant_id', $tenant_id)->first();
                $config_details_custom = ConfigCustomProjectName::where('tenant_id', $tenant_id)->first();
                $blacklists = Blacklist::where('tenant_id', $tenant_id)->where('is_allow_client_potal', 0)->get();
                $blacklistProjectIds = $blacklists->pluck("fv_project_id")->toArray();

                $calendar_setting = CalendarSetting::where('tenant_id', $tenant_id)->first();
                $calendar_visibility = isset($calendar_setting->calendar_visibility) ? $calendar_setting->calendar_visibility : false;


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
                            } else {
                                $last_login = "";
                            }
                        }
                        $form = TenantForm::where(['tenant_id' => $tenant_id, 'form_name' => request()->name, 'is_active' => 1, 'deleted_at' => null])->latest()->first();
                        if (!is_null($form)) {
                            $lookup_data = array(
                                'last_login' => $tr->translate($last_login),
                                'results'    => $project_details,
                                'config_details'     => $config_details,
                                'active_project_id'  => $lookup_project_id,
                                'project_id'  => $project_id,
                                'form' => $form,
                                'project_override_name' => $this->customizeProjectName($filevine_api, $config_details, $config_details_custom, $project_details['projectId']['native'], $project_details['clientName'])
                            );
                        } else {
                            return redirect()->route('client_active_forms', ['subdomain' => $subdomain_name, 'lookup_project_id' => $lookup_project_id]);
                        }
                    } else {
                        $lookup_data = array(
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
                        'client.pages.form',
                        [
                            'lookup_data' => $lookup_data,
                            'tenant_id' => $tenant_id,
                            'selected_client_native_id' => $selected_client_native_id,
                            'lookup_project_id' => $lookup_project_id,
                            'calendar_visibility' => $calendar_visibility,
                        ]
                    );
                } else {
                    return redirect()->route('client', ['subdomain' => $subdomain_name]);
                }
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
     * get a form data
     */
    public function getFormData(Request $request, $subdomain, $form_id)
    {
        try {
            if (!is_null($form_id)) {

                $tr = new GoogleTranslate();
                $tr->setSource();
                $tr->setTarget(!is_null(session()->get('lang')) ? session()->get('lang') : 'en');

                $form = TenantForm::where(['id' => $form_id, 'tenant_id' => $this->cur_tenant_id, 'is_active' => 1, 'deleted_at' => null])->first();
                $mutliselectformname = TenantFormMapping::where('fv_field_name', 'like', '%MultiSelectList%')->pluck('form_item_name')->toArray();

                if (!is_null($form)) {

                    $form_fields_json = json_decode($form->form_fields_json);
                    foreach ($form_fields_json as $form_field) {
                        if (isset($form_field->label)) {
                            $form_field->label = $tr->translate($form_field->label);
                        }
                        if (isset($form_field->placeholder)) {
                            $form_field->placeholder = $tr->translate($form_field->placeholder);
                        }
                    }

                    $form->form_fields_json = json_encode($form_fields_json);

                    return response()->json([
                        'success' => true,
                        'data' => $form,
                        'mutliselectformname' => json_encode($mutliselectformname)
                    ], 200);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'No form data found.'
                    ], 400);
                }
            }
        } catch (\Throwable $th) {
            $exception_json = json_encode($th);
            Logging::warning($th->getMessage());
            Logging::warning($exception_json);
            return $th->getMessage();
        }
    }

    /**
     * Get the Dynamic Legal Team Person Config info.
     */
    public function handleFormResponse(Request $request)
    {
        try {
            $response_id = 0;

            $main_data = $request->all();
            // $data = json_decode($request->getContent());
            $data = json_decode($main_data['content']);
            $documents = (isset($main_data['documents']) && count($main_data['documents']) > 0) ? $main_data['documents'] : [];

            if ($data->form_id && $data->tenant_id == $this->cur_tenant_id && $data->client_id && $data->project_id) {
                $form_details = TenantForm::find($data->form_id);
                $Tenant = Tenant::find($this->cur_tenant_id);
                $apiurl = config('services.fv.default_api_base_url');
                if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                    $apiurl = $Tenant->fv_api_base_url;
                }
                $filevine_api = new FilevineService($apiurl, "");
                $fv_client = FvClients::where('fv_client_id', $data->client_id)->first();
                if (empty($fv_client)) {
                    $contact = json_decode($filevine_api->getContactByContactId($data->client_id));
                    FvClients::create([
                        'tenant_id' => $this->cur_tenant_id,
                        'fv_client_id' => $contact->personId->native,
                        'fv_client_name' => $contact->fullName,
                        'fv_client_address' => isset($contact->addresses[0]->fullAddress) ? $contact->addresses[0]->fullAddress : '',
                        'fv_client_zip' => isset($contact->addresses[0]->postalCode) ? $contact->addresses[0]->postalCode : ''
                    ]);
                    $fv_client = FvClients::where('fv_client_id', $data->client_id)->first();
                }
                $project = json_decode($filevine_api->getProjectsById($data->project_id));

                $res = TenantFormResponse::create([
                    'tenant_form_id' => $data->form_id,
                    'fv_client_id' => $data->client_id,
                    'fv_client_name' => isset($fv_client->fv_client_name) ? $fv_client->fv_client_name : '',
                    'fv_project_name' => isset($project->projectName) ? $project->projectName : '',
                    'fv_project_id' => $data->project_id,
                    'form_response_values_json' => json_encode($data->response)
                ]);
                $response_id = $res->id;

                // Send Admin Notification (Email and Post to Filevine)
                $notification_config = TenantNotificationConfig::where('tenant_id', $this->cur_tenant_id)->where('event_short_code', TenantNotificationConfig::FormSubmission)->first();
                if ($notification_config) {
                    $params = [
                        'project_id' => $data->project_id,
                        'tenant_id' => $this->cur_tenant_id,
                        'client_id' => $fv_client->fv_client_id,
                        'client_name' => $fv_client->fv_client_name,
                        'note_body' => 'A form with name ' . $form_details->form_name . ' submitted.',
                        'action_name' => 'Form Submission',
                        'project_name' => $project->projectName,
                        'tenant_form_response_id' => $response_id,
                    ];
                    NotificationHandlerService::callActionService($notification_config, $params);
                }

                $form_response = $data->response;
                $fv_section_id = null;
                $dataObject = [];
                $count_form_item_collections = [];
                $document_array_indexes = [];

                $count_form_item_collections_data = TenantFormMapping::select('collection_item_index', DB::raw('count(*) as total'))->where('form_id', $data->form_id)->where('section_type', 'collection')->where('form_item_type', '!=', 'file')->groupBy('collection_item_index')->get();
                foreach ($count_form_item_collections_data as $item) {
                    $count_form_item_collections[$item->collection_item_index] = $item->total;
                }
                $count_total_form_item_collection = 0;
                $prev_mapping_collection_item_index = 0;

                foreach ($form_response as $fresponse) {
                    $fname = trim($fresponse->name);
                    $mapping = TenantFormMapping::where('form_id', $data->form_id)->where('form_mapping_enable', true)->where('form_item_name', $fname)->first();
                    if (isset($mapping->section_type) && $mapping->section_type == 'collection') {
                        if (count($count_form_item_collections)) {
                            if ($count_total_form_item_collection == $count_form_item_collections[$prev_mapping_collection_item_index]) {
                                if (count($dataObject)) {
                                    // check if there is any document for the object
                                    if(count($documents) > 0) {
                                        $document_mappings = TenantFormMapping::where('form_id', $data->form_id)->where('form_mapping_enable', true)
                                        ->where('collection_item_index', $prev_mapping_collection_item_index)
                                        ->where('form_item_type', 'file')
                                        ->get();
                                        if(count($document_mappings) > 0) {
                                            foreach ($document_mappings as $doc_map) {
                                                // get collection item index
                                                if(isset($document_array_indexes[$doc_map->form_item_name])) {
                                                    $document_array_indexes[$doc_map->form_item_name][] = end($document_array_indexes[$doc_map->form_item_name]) + 1;
                                                }
                                                else {
                                                    $document_array_indexes[$doc_map->form_item_name][] = 0;
                                                }

                                                // get index to check for docs
                                                $document_array_index = end($document_array_indexes[$doc_map->form_item_name]);

                                                // get documents
                                                if(isset($documents[$doc_map->form_item_name]) && isset($documents[$doc_map->form_item_name][$document_array_index])) {
                                                    $dataObject[$doc_map->fv_field_id] = $this->uploadDocumentsToFV($filevine_api, $data->project_id, $documents[$doc_map->form_item_name][$document_array_index]);
                                                }
                                            }
                                        }
                                    }
                                    $field_params["dataObject"] = $dataObject;
                                    $filevine_api->createCollectionItem($data->project_id, $fv_section_id, $field_params);
                                }
                                $dataObject = [];
                                $count_total_form_item_collection = 0;
                            }

                            if (!empty($mapping->fv_section_id)) {
                                $fv_section_id = $mapping->fv_section_id;
                            }

                            if (!empty($fresponse->value) && !empty($mapping->fv_field_id)) {
                                // check if its a person link
                                if(strpos($mapping->fv_field_name, "PersonLink") !== false) {
                                    $dataObject[$mapping->fv_field_id] = $this->filterFieldValue($this->getMatchFirstContactId($filevine_api, $fresponse->value));
                                }
                                elseif(strpos($mapping->fv_field_name, "Deadline") !== false) {
                                    $dataObject[$mapping->fv_field_id] = ['dateValue' => (new \DateTime($fresponse->value))->format('Y-m-d') . 'T00:00:00Z'];
                                }
                                elseif(strpos($mapping->fv_field_name, "MultiSelectList") !== false) {
                                    $dataObject[$mapping->fv_field_id] = array_map('trim', $fresponse->value);
                                }
                                else {
                                    $dataObject[$mapping->fv_field_id] = $this->filterFieldValue($fresponse->value);
                                }
                            }
                            $count_total_form_item_collection++;
                            $prev_mapping_collection_item_index = $mapping->collection_item_index;
                        }
                    } else if ($mapping) {
                        $target_section_id = $mapping->fv_section_id;
                        // check if its a person link
                        if(strpos($mapping->fv_field_name, "PersonLink") !== false) {
                            $params[$mapping->fv_field_id] = $this->filterFieldValue($this->getMatchFirstContactId($filevine_api, $fresponse->value));
                        }
                        elseif(strpos($mapping->fv_field_name, "Deadline") !== false) {
                            $params[$mapping->fv_field_id] = ['dateValue' => (new \DateTime($fresponse->value))->format('Y-m-d') . 'T00:00:00Z'];
                        }
                        elseif(strpos($mapping->fv_field_name, "MultiSelectList") !== false) {
                            $params[$mapping->fv_field_id] = array_map('trim', $fresponse->value);
                        }
                        else {
                            $params[$mapping->fv_field_id] = $this->filterFieldValue($fresponse->value);
                        }
                        $filevine_api->updateStaticForm($data->project_id, $target_section_id, $params);
                        $params = [];
                    }
                }

                if (count($dataObject)) {
                    // check if there is any document for the object
                    if(count($documents) > 0) {
                        $document_mappings = TenantFormMapping::where('form_id', $data->form_id)->where('form_mapping_enable', true)
                        ->where('collection_item_index', $prev_mapping_collection_item_index)
                        ->where('form_item_type', 'file')
                        ->get();
                        if(count($document_mappings) > 0) {
                            foreach ($document_mappings as $doc_map) {
                                // get collection item index
                                if(isset($document_array_indexes[$doc_map->form_item_name])) {
                                    $document_array_indexes[$doc_map->form_item_name][] = end($document_array_indexes[$doc_map->form_item_name]) + 1;
                                }
                                else {
                                    $document_array_indexes[$doc_map->form_item_name][] = 0;
                                }

                                // get index to check for docs
                                $document_array_index = end($document_array_indexes[$doc_map->form_item_name]);

                                // get documents
                                if(isset($documents[$doc_map->form_item_name]) && isset($documents[$doc_map->form_item_name][$document_array_index])) {
                                    $dataObject[$doc_map->fv_field_id] = $this->uploadDocumentsToFV($filevine_api, $data->project_id, $documents[$doc_map->form_item_name][$document_array_index]);
                                }
                            }
                        }
                    }

                    $field_params["dataObject"] = $dataObject;
                    $filevine_api->createCollectionItem($data->project_id, $fv_section_id, $field_params);
                }

                // check if there is any document for the object
                if(count($documents) > 0) {
                    foreach ($documents as $field_name => $docs) {
                        $document_mapping = TenantFormMapping::where('form_id', $data->form_id)->where('form_mapping_enable', true)
                        ->where('form_item_name', $field_name)
                        ->where('form_item_type', 'file')
                        ->orderBy('collection_item_index', 'asc')
                        ->first();
                        if(isset($document_mapping)) {
                            if($document_mapping->section_type == 'collection') {
                                $fv_section_id = $document_mapping->fv_section_id;
                                foreach ($docs as $key => $value) {
                                    $dataObject = [];
                                    $field_params = [];
                                    if(!isset($document_array_indexes[$document_mapping->form_item_name]) ||
                                    (isset($document_array_indexes[$document_mapping->form_item_name]) &&
                                    !in_array($key, $document_array_indexes[$document_mapping->form_item_name]))) {
                                        $dataObject[$document_mapping->fv_field_id] = $this->uploadDocumentsToFV($filevine_api, $data->project_id, $value);
                                        if (!empty($fv_section_id)) {
                                            $field_params["dataObject"] = $dataObject;
                                            $filevine_api->createCollectionItem($data->project_id, $fv_section_id, $field_params);
                                        }
                                    }
                                }
                            }
                            elseif($document_mapping->section_type == 'static') {
                                $target_section_id = $document_mapping->fv_section_id;
                                $params = [];
                                // get documents
                                if(isset($documents[$document_mapping->form_item_name]) && isset($documents[$document_mapping->form_item_name][0])) {
                                    $params[$document_mapping->fv_field_id] = $this->uploadDocumentsToFV($filevine_api, $data->project_id, $documents[$document_mapping->form_item_name][0]);
                                    if (!empty($target_section_id)) {
                                        $filevine_api->updateStaticForm($data->project_id, $target_section_id, $params);
                                    }
                                }
                            }
                        }
                    }
                }

                // Call automated workflow webhook trigger function
                app('App\Http\Controllers\API\AutomatedWorkflowWebhookController')->formSubmitted(['Object' => 'FormSubmitted', 'Event' => '', 'ProjectId' => $data->project_id, 'fv_client_id' => $data->client_id, 'tenant_id' => $this->cur_tenant_id, 'form_id' => $data->form_id]);

                return response()->json([
                    'success' => true,
                    'message' => !empty($form_details->success_message) ? $form_details->success_message : '<div class="alert alert-success" role="alert">Form submission received. Thank you.</div>'
                ], 200);
            }
        } catch (\Throwable $th) {
            $exception_json = json_encode($th);
            Logging::warning($exception_json);
            if ($response_id) {
                TenantFormResponse::where('id', $response_id)->update([
                    'error_log' => $th->getMessage()
                ]);
            }
            return $th->getMessage();
        }
    }


    /**
     *  [GET] Upload files page for client
     */
    public function uploadFiles(Request $request, $subdomain, $lookup_project_id)
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

            // get available upload choices
            $upload_schemes = ClientFileUploadConfiguration::where(['tenant_id' => $tenant_data->id])->whereNotNull('handle_files_action')->select('id', 'choice', 'target_field_type')->get();

            $apiurl = config('services.fv.default_api_base_url');
            if (isset($tenant_data->fv_api_base_url) and !empty($tenant_data->fv_api_base_url)) {
                $apiurl = $tenant_data->fv_api_base_url;
            }

            $filevine_api = new FilevineService($apiurl, "");

            $calendar_setting = CalendarSetting::where('tenant_id', $tenant_id)->first();
            $calendar_visibility = isset($calendar_setting->calendar_visibility) ? $calendar_setting->calendar_visibility : false;

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
                        } else {
                            $last_login = "";
                        }
                    }

                    $lookup_data[] = array(
                        'last_login' => $tr->translate($last_login),
                        'results'    => $project_details,
                        'config_details'     => $config_details,
                        'active_project_id'  => $lookup_project_id,
                        'project_id'  => $project_id,
                        'project_override_name' => $this->customizeProjectName($filevine_api, $config_details, $config_details_custom, $project_details['projectId']['native'], $project_details['clientName'])
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


                $fv_shared_documents = FvSharedDocument::where('tenant_id', $tenant_id)->where('fv_project_id', $lookup_project_id)->get();
                $fv_client_upload_documents = FvClientUploadDocument::join('client_file_upload_configurations', 'fv_client_upload_documents.scheme_id', '=', 'client_file_upload_configurations.id')
                    ->where('client_file_upload_configurations.tenant_id', $tenant_id)
                    ->where('fv_client_upload_documents.tenant_id', $tenant_id)
                    ->where('fv_project_id', $lookup_project_id)
                    ->select('fv_client_upload_documents.*', 'client_file_upload_configurations.choice as choice')
                    ->get();


                return $this->_loadContent(
                    'client.pages.upload_files',
                    [
                        'files_allowed' => $config_details->is_enable_file_uploads,
                        'lookup_data' => $lookup_data,
                        'selected_client_native_id' => $selected_client_native_id,
                        'lookup_project_id' => $lookup_project_id,
                        'upload_schemes' => $upload_schemes,
                        'calendar_visibility' => $calendar_visibility,
                        'fv_shared_documents' => $fv_shared_documents,
                        'fv_client_upload_documents' => $fv_client_upload_documents,
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
     *  [POST] Upload files to project root folder/selected mapping
     */
    public function uploadProjectFiles(Request $request, $subdomain, $lookup_project_id)
    {
        $response = [
            'status' => false,
            'message' => 'Unable to upload requested file at the moment',
        ];

        try {
            $data = $request->except('_token');
            // check if there is any file
            if (!isset($data['files']) || (isset($data['files']) && count($data['files']) <= 0)) {
                Logging::warning(json_encode($response));
                return response()->json($response);
            }

            // now create upload url on filevine
            $tenant_id = $this->cur_tenant_id;
            $tenant_data = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($tenant_data->fv_api_base_url) and !empty($tenant_data->fv_api_base_url)) {
                $apiurl = $tenant_data->fv_api_base_url;
            }
            $filevine_api = new FilevineService($apiurl, "");

            // check for folders first if needed
            $section_folder = [];
            $child_folder = [];
            $files_handle = 1;
            $hashtags = [];
            $scheme_id = 0;
            if (isset($data['scheme_id']) && !empty($data['scheme_id'])) {
                $scheme_id = $data['scheme_id'];
                $upload_scheme = ClientFileUploadConfiguration::where('id', $scheme_id)->whereNotNull('handle_files_action')->first();
                if (isset($upload_scheme->id)) {
                    if (!empty($upload_scheme->hashtag)) {
                        $hashtags = explode(',', $upload_scheme->hashtag);
                    }
                    if ($upload_scheme->handle_files_action > 1) {
                        $files_handle = $upload_scheme->handle_files_action;
                        // check if section folder is created already or not
                        $section_name = $upload_scheme->target_section_name;
                        $field_name = $upload_scheme->target_field_name;
                        $section_folder = $this->_getDocumentFolderInfo($filevine_api, $lookup_project_id, $section_name);
                        // create folder if not exist
                        if (!isset($section_folder['folderId'])) {
                            $section_folder_params = [
                                'projectId' => [
                                    'native' => $lookup_project_id
                                ],
                                'name' => $section_name
                            ];
                            $section_folder = json_decode($filevine_api->createFolderInProject($section_folder_params), TRUE);
                        }

                        // go on for child folder if parent exist
                        if (isset($section_folder['folderId'])) {
                            $child_folder = $this->_getDocumentFolderInfo($filevine_api, $lookup_project_id, $field_name, $section_folder['folderId']['native']);
                            // create folder if not exist
                            if (!isset($child_folder['folderId'])) {
                                $child_folder_params = [
                                    'projectId' => [
                                        'native' => $lookup_project_id
                                    ],
                                    'parentId' => [
                                        'native' => $section_folder['folderId']['native']
                                    ],
                                    'name' => $field_name
                                ];
                                $child_folder = json_decode($filevine_api->createFolderInProject($child_folder_params), TRUE);
                            }
                        }

                        // check if folders exist or not
                        if (!isset($section_folder['folderId']) || !isset($child_folder['folderId'])) {
                            //return response()->json($response);
                        }
                    }
                }
            }


            // Get existing section details data
            $field_details = [];
            if (isset($upload_scheme->handle_files_action) && $upload_scheme->handle_files_action == 2) {
                $target_section_id = $upload_scheme->target_section_id;
                if ($upload_scheme->target_field_type == 'DocList') {
                    $target_field_id = $upload_scheme->target_field_id;
                    $section_details = json_decode($filevine_api->getProjectFormsTeamInfo([
                        'projectId' => $lookup_project_id,
                        'section' => $target_section_id,
                        'fields' => $target_field_id
                    ]));
                    if (!empty($section_details)) {
                        $field_details = $section_details->$target_field_id;
                    }
                }
            }

            // process file
            $files = $data['files'];
            foreach ($files as $file) {
                $file_size = $file->getSize();
                $file_name = $file->getClientOriginalName();
                $file_binary = file_get_contents($file->getRealPath());

                $upload_params = [
                    'filename' => $file_name,
                    'size' => $file_size
                ];
                $upload_details = json_decode($filevine_api->createDocumentUploadUrl($upload_params), true);
                if (isset($upload_details['documentId']) && isset($upload_details['url'])) {
                    $url = $upload_details['url'];
                    $content_type = $upload_details['contentType'];
                    $document_id = $upload_details['documentId'];
                    // upload file to returned url
                    $upload_binary = $filevine_api->uploadDocumentToUploadUrl($url, $file_binary, $content_type);
                    if (empty($upload_binary)) {
                        $document_params = [
                            'documentId' => $document_id,
                            'filename' => $file_name,
                            'size' => $file_size,
                            'projectId' => [
                                'native' => $lookup_project_id
                            ]
                        ];
                        // add folder params
                        if ($files_handle > 1) {
                            $document_params['folderId'] = [
                                'native' => $child_folder['folderId']['native']
                            ];
                        }
                        // add hashtag to document if any
                        if (count($hashtags) > 0) {
                            $hashtags_param['hashtags'] = $hashtags;
                            $add_hashtags_to_document = json_decode($filevine_api->addHashTagsToDocument($document_id['native'], $hashtags_param), true);
                        }
                        $add_to_project = json_decode($filevine_api->addDocumentToProject($lookup_project_id, $document_id['native'], $document_params), true);

                        // Add file into sub folder/section field
                        if ($files_handle > 1) {
                            $sub_folder_params['folderId'] = [
                                'native' => $child_folder['folderId']['native']
                            ];
                            $add_to_subfolder = json_decode($filevine_api->addHashTagsToDocument($document_id['native'], $sub_folder_params), true);
                        }

                        $field_details[] = [
                            "id" => $document_id['native']
                        ];

                        // Store file upload information into fv_client_upload_documents
                        FvClientUploadDocument::insert([
                            'tenant_id' => $tenant_id,
                            'fv_client_id' => !is_null(session()->get('contact_id')) ? session()->get('contact_id') : 0,
                            'fv_project_id' => $lookup_project_id,
                            'fv_document_id' => $document_id['native'],
                            'fv_filename' => $file_name,
                            'doc_size' => $file_size,
                            'fv_upload_date' => date('Y-m-d H:i:s'),
                            'scheme_id' => $scheme_id,
                            'fv_download_url' => $url,
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }


            $target_section_id = $upload_scheme->target_section_id;

            // Update static section data
            if (isset($add_to_project['documentId']) && isset($upload_scheme->handle_files_action) && $upload_scheme->handle_files_action == 2) {
                if ($upload_scheme->target_field_type == 'DocList') {
                    $params[$target_field_id] = $field_details;
                } else {
                    $params[$upload_scheme->target_field_id] = ['id' => $document_id['native']];
                }
                $filevine_api->updateStaticForm($lookup_project_id, $target_section_id, $params);
            }

            // Update collection section data
            if (isset($add_to_project['documentId']) && isset($upload_scheme->handle_files_action) && $upload_scheme->handle_files_action == 3) {
                $params['dataObject'][$upload_scheme->target_field_id] = $field_details;
                $filevine_api->createCollectionItem($lookup_project_id, $target_section_id, $params);
            }


            if (isset($add_to_project['documentId']) && isset($add_to_project['projectId'])) {
                $response['status'] = true;
                $response['message'] = 'Document uplloaded successfully';
            }

            // Send Admin Notification (Email and Post to Filevine)
            $notification_config = TenantNotificationConfig::where('tenant_id', $this->cur_tenant_id)->where('event_short_code', TenantNotificationConfig::DocumentUploaded)->first();
            if ($notification_config) {
                $params = [
                    'project_id' => $lookup_project_id,
                    'tenant_id' => $this->cur_tenant_id,
                    'client_id' => !is_null(session()->get('contact_id')) ? session()->get('contact_id') : 0,
                    'note_body' => 'A client has uploaded a document from VineConnect Client Portal',
                    'action_name' => 'Document Uploaded',
                ];
                NotificationHandlerService::callActionService($notification_config, $params);
            }

            // Call automated workflow webhook trigger function
            app('App\Http\Controllers\API\AutomatedWorkflowWebhookController')->documentUploaded(['Object' => 'DocumentUploaded', 'Event' => '', 'ProjectId' => $lookup_project_id, 'fv_client_id' => (!is_null(session()->get('contact_id')) ? session()->get('contact_id') : 0), 'tenant_id' => $this->cur_tenant_id, 'scheme_id' => $scheme_id]);

            return response()->json($response);
        } catch (\Exception $ex) {
            $response['status'] = false;
            $response['message'] = $ex->getMessage();
            Logging::warning(json_encode($response));
            return response()->json($ex->getMessage());
        }
    }

    /**
     * Get Document Folder Details.
     */
    public function _getDocumentFolderInfo($fvobject, $projectId, $folderName, $parentFolder = "")
    {
        $offset = 0;
        $limit = 1000;
        $return = [];
        do {
            $folders = json_decode($fvobject->getProjectFoldersList($projectId, $limit, $offset, $parentFolder), TRUE);
            $next_link = trim($folders['links']['next']);

            if (isset($folders['items'])) {
                foreach ($folders['items'] as $folder) {
                    if (isset($folder['folderId']) && strtolower($folder['name']) == strtolower($folderName)) {
                        $return = $folder;
                        $next_link = false;
                        break;
                    }
                }
            }
            $offset += $limit;
        } while ($next_link);

        return $return;
    }

    /**
     * Get the Dynamic Legal Team Person Config info.
     */
    public function _getLegalteamPersonConfigInfo($project_type_id, $project_id)
    {
        $persons = [];
        $tenant_id = $this->cur_tenant_id;
        $Tenant = Tenant::find($tenant_id);
        $apiurl = config('services.fv.default_api_base_url');
        if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
            $apiurl = $Tenant->fv_api_base_url;
        }
        $filevine_api = new FilevineService($apiurl, "");
        $personConfig = LegalteamPersonConfig::where('tenant_id', $tenant_id)->orderBy('sort_order', 'asc')->get();
        if (isset($personConfig) and !empty($personConfig)) {
            $sectionFields = $personConfig->groupBy('fv_section_id');
            foreach ($sectionFields as $sectionkey => $section) {
                $fetchOrStatic = $section->groupBy('type');
                foreach ($fetchOrStatic as $typeKey => $type) {
                    if ($typeKey == "static") {
                        foreach ($type as $key => $personType) {
                            $person = [
                                'email' => $personType->override_email,
                                'phone' => $personType->override_phone,
                                'fullname' => $personType->override_name,
                                'config' => $personType,
                            ];
                            $persons[$personType->sort_order] = $person;
                        }
                    } else {
                        $dataFields = $type->pluck("fv_person_field_id")->toArray();
                        $personFields = json_decode($filevine_api->getProjectFormsTeamInfo(["projectId" => $project_id, "section" => $sectionkey, 'fields' => implode(",", $dataFields)]), TRUE);
                        foreach ($personConfig as $personc) {
                            if (isset($personFields[$personc->fv_person_field_id]) and !empty($personFields[$personc->fv_person_field_id])) {
                                $fields = $personFields[$personc->fv_person_field_id];
                                if ($personc->is_static_name) {
                                    $fields['fullname'] = $personc->override_name;
                                }
                                $fields['phone'] = "";
                                $fields['email'] = "";
                                if (isset($fields['phones']) and !empty($fields['phones'])) {
                                    if (isset($fields['phones'][0]['number'])) {
                                        $fields['phone'] = $fields['phones'][0]['number'];
                                    }
                                }
                                if (isset($fields['emails']) and !empty($fields['emails'])) {
                                    if (isset($fields['emails'][0]['address'])) {
                                        $fields['email'] = $fields['emails'][0]['address'];
                                    }
                                }
                                if ($personc->is_override_phone) {
                                    $fields['phone'] = $personc->override_phone;
                                }
                                if ($personc->is_override_email) {
                                    $fields['email'] = $personc->override_email;
                                }
                                if ($personc->is_enable_feedback) {
                                    $fields['is_enable_feedback'] = $personc->is_enable_feedback;
                                }
                                $fields['config'] = $personc;


                                $pictureUrl = isset($fields['pictureUrl']) ? $fields['pictureUrl'] : '';
                                if (!empty($pictureUrl)) {
                                    // $file_headers = @get_headers($pictureUrl);
                                    // if (!$file_headers || $file_headers[0] != 'HTTP/1.0 200 OK') {
                                    //     $pictureUrl = "";
                                    // } else if (empty($file_headers)) {
                                    //     $pictureUrl = "";
                                    // }
                                }

                                $person = [
                                    'email' => $fields['email'],
                                    'phone' => $fields['phone'],
                                    'fullname' => $fields['fullname'],
                                    'picture_url' => $pictureUrl,
                                    'config' => $personc,
                                ];
                                // get member image
                                if (!empty($person['picture_url'])) {
                                    $picParts = explode("/", $person['picture_url']);
                                    $person['picture_url'] = $this->updateTeamMemberData($filevine_api, $Tenant, $fields, $picParts, "notroles");
                                }
                                $persons[$personc->sort_order] = $person;
                            }
                        }
                    }
                }
            }
        }
        ksort($persons);
        return $persons;
    }

    /**
     * Get the Dynamic Legal Team Config info.
     */
    public function _getLegalteamConfigInfo($tenant_id, $project_id)
    {
        try {
            $legal_teams = [];

            // get the legal team config info from the tennant id.
            $legal_configs = LegalteamConfig::where('tenant_id', $tenant_id)
                ->orderBy('role_order')
                ->get()->toArray();

            // get the fetch role ids
            $fetch_fv_all_role_ids = [];
            $fetch_fv_all_role_parsed = [];
            $fetch_legal_team_members = [];
            $static_legal_team_members = [];
            foreach ($legal_configs as $key => $value) {
                if (isset($value['type'], $value['fv_role_id']) && $value['type'] == LegalteamConfig::TYPE_FETCH) {
                    $fetch_fv_all_role_ids[] = $value['fv_role_id'];
                    $fetch_fv_all_role_parsed[$value['fv_role_id']] = $value;
                } else {
                    $static_legal_team_members[] = $value;
                }
            }

            // get the all legal team members from the FV project id.
            $Tenant = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $filevine_api = new FilevineService($apiurl, "");

            $offset = 0;
            $limit = 1000;
            do {
                $fv_team_members = $filevine_api->getProjectsTeamById($project_id, $offset, $limit);
                $fv_team_members = json_decode($fv_team_members, true);
                $has_more = isset($fv_team_members['hasMore']) ? $fv_team_members['hasMore'] : false;

                if (isset($fv_team_members['items']) && count($fv_team_members['items']) > 0) {
                    foreach ($fv_team_members['items'] as $key => $value) {
                        if (count($value['teamOrgRoles']) == 0) {
                            continue;
                        }

                        $pictureUrl = isset($value['pictureUrl']) ? $value['pictureUrl'] : '';
                        if (!empty($pictureUrl)) {
                            // $file_headers = @get_headers($pictureUrl);
                            // if (!$file_headers || $file_headers[0] != 'HTTP/1.0 200 OK') {
                            //     $pictureUrl = "";
                            // } else if (empty($file_headers)) {
                            //     $pictureUrl = "";
                            // }
                        }


                        $fetch_legal_team_member = [
                            'type' => LegalteamConfig::TYPE_FETCH,
                            'userId' => $value['userId']['native'],
                            'name' => $value['fullname'],
                            'email' => $value['email'],
                            'picture_url' => $pictureUrl,
                            'roles' => $value['teamOrgRoles'],
                            'role_order' => 0,
                            'is_enable_email' => 1,
                            'is_enable_feedback' => 1,
                        ];

                        $roles = $value['teamOrgRoles'];
                        $team_member_level = $value['level'] ?? "";

                        $fv_role_ids = [];
                        $is_in_legal_team_config = 0;
                        $legal_team_avg_role_order = 0;
                        $avg_role_cnt = 0;
                        $is_follow_valid = 1;
                        $is_enable_email = 1;
                        $is_enable_feedback = 1;
                        if (count($roles) > 0) {
                            foreach ($roles as $k => $role_value) {
                                if (isset($role_value['orgRoleId']['native'])) {
                                    $fv_role_ids[] = $role_value['orgRoleId']['native'];
                                    if (in_array($role_value['orgRoleId']['native'], $fetch_fv_all_role_ids)) {
                                        $is_in_legal_team_config = 1;
                                        if (isset($fetch_fv_all_role_parsed[$role_value['orgRoleId']['native']]['role_order'])) {
                                            $legal_team_avg_role_order += $fetch_fv_all_role_parsed[$role_value['orgRoleId']['native']]['role_order'];
                                            $avg_role_cnt++;

                                            // is_follow_required check
                                            if (isset($fetch_fv_all_role_parsed[$role_value['orgRoleId']['native']]['is_follower_required']) && $fetch_fv_all_role_parsed[$role_value['orgRoleId']['native']]['is_follower_required'] == 1) {
                                                if ($team_member_level != "Follower") {
                                                    $is_follow_valid = 0;
                                                }
                                            }

                                            if (isset($fetch_fv_all_role_parsed[$role_value['orgRoleId']['native']]['is_enable_email']) && $fetch_fv_all_role_parsed[$role_value['orgRoleId']['native']]['is_enable_email'] == 0) {
                                                $is_enable_email = 0;
                                            }

                                            if (isset($fetch_fv_all_role_parsed[$role_value['orgRoleId']['native']]['is_enable_feedback']) && $fetch_fv_all_role_parsed[$role_value['orgRoleId']['native']]['is_enable_feedback'] == 0) {
                                                $is_enable_feedback = 0;
                                            }
                                        }
                                    }
                                }
                            }

                            if ($avg_role_cnt > 0) {
                                $legal_team_avg_role_order = $legal_team_avg_role_order / $avg_role_cnt;
                            }
                        }

                        $fetch_legal_team_member['role_order'] = $legal_team_avg_role_order;
                        $fetch_legal_team_member['is_enable_email'] = $is_enable_email;
                        $fetch_legal_team_member['is_enable_feedback'] = $is_enable_feedback;

                        if ($is_in_legal_team_config == 1 && $is_follow_valid == 1) {
                            // get member image
                            if (!empty($fetch_legal_team_member['picture_url'])) {
                                $picParts = explode("/", $fetch_legal_team_member['picture_url']);
                                $fetch_legal_team_member['picture_url'] = $this->updateTeamMemberData($filevine_api, $Tenant, $value, $picParts, "roles");
                            }
                            // it is need to show the info.
                            $fetch_legal_team_members[] = $fetch_legal_team_member;
                        }
                    }
                }
                $offset += $limit;
            } while ($has_more);

            // sort by role_order
            $legal_teams = array_merge($fetch_legal_team_members, $static_legal_team_members);
            $role_order = array_column($legal_teams, 'role_order');
            array_multisort($role_order, SORT_ASC, $legal_teams);
        } catch (\Exception $ex) {
        }
        return $legal_teams;
    }

    /**
     *  [POST] Send Client Feedback
     */
    public function sendClientFeedback(Request $request)
    {
        try {
            $tenant_id = $this->cur_tenant_id;
            if ($tenant_id) {
                $current_date = date('Y-m-d H:i:s');
                $Tenant = Tenant::find($tenant_id);
                $feedback_body_str = "
                    How satisfied are you with the legal service " . $request['legal_team_name'] . " has provided? => " . $request['fd_mark_legal_service'] . " stars
                    How likely are you to recommend our firm to others? => " . $request['fd_mark_recommend'] . " stars
                    How useful have you found this Client Portal to be? => " . $request['fd_mark_useful'] . " stars
                    Feedback: " . $request['fd_content'] .  "
                ";
                $values = array(
                    'tenant_id' => $tenant_id,
                    'project_id' => $request->input('project_id'),
                    'project_name' => $request->input('project_name'),
                    'project_phase' => $request->input('project_phase'),
                    'legal_team_email' => $request->input('legal_team_email'),
                    'legal_team_phone' => $request->input('legal_team_phone'),
                    'legal_team_name' => $request->input('legal_team_name'),
                    'client_name' => $request->input('client_name'),
                    'client_phone' => $request->input('client_phone'),
                    'fd_mark_legal_service' => $request->input('fd_mark_legal_service'),
                    'fd_mark_recommend' => $request->input('fd_mark_recommend'),
                    'fd_mark_useful' => $request->input('fd_mark_useful'),
                    'fd_content' => $request->input('fd_content'),
                    'created_at' => $current_date
                );

                $feedbacks = Feedbacks::create($values);
                $apiurl = config('services.fv.default_api_base_url');
                if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                    $apiurl = $Tenant->fv_api_base_url;
                }
                $filevine_api = new FilevineService($apiurl, "");


                // get the author ID from the FV author id.
                $clientNativeId = null;
                $auth_user_obj = json_decode($filevine_api->getAuthUser(), TRUE);
                if ($auth_user_obj && isset($auth_user_obj['user'])) {
                    if (isset($auth_user_obj['user']['userId']) && isset($auth_user_obj['user']['userId']['native'])) {
                        $clientNativeId = $auth_user_obj['user']['userId']['native'];
                    }
                }

                // Send Admin Notification Email & Post to Filevine
                $is_post_to_filevine = false;
                $is_email_notification = false;
                $config = TenantNotificationConfig::where('event_short_code', TenantNotificationConfig::FeedbackReceived)->where('tenant_id', $tenant_id)->first();
                if ($config) {
                    $is_post_to_filevine = $config->is_post_to_filevine;
                    $is_email_notification = $config->is_email_notification;
                }

                // Insert Notification Log
                $log_data = [
                    'tenant_id' => $config->tenant_id,
                    'event_name' => $config->event_name,
                    'fv_project_id' => $request->input('project_id'),
                    'fv_project_name' => $request->input('project_name'),
                    'fv_client_name' => $request->input('client_name'),
                    'notification_body' => $feedback_body_str,
                    'created_at' => Carbon::now(),
                ];
                $log_id = TenantNotificationLog::insertGetId($log_data);

                if (isset($clientNativeId) && $clientNativeId != null && $is_post_to_filevine) {
                    $params = [
                        'projectId' => ['native' => $request->input('project_id'), 'partner' => null],
                        'body' => $feedback_body_str,
                        'authorId' => ['native' => $clientNativeId, 'partner' => null]
                    ];
                    $filevine_api->createNote($params);
                    // Update Notification Log
                    TenantNotificationLog::where('id', $log_id)->update([
                        'sent_post_to_filevine_at' => Carbon::now()
                    ]);
                }
                $this->sendGridServices->sendClientFeedbackSuccessMail($values, $feedback_body_str, $this->cur_tenant_id);

                if ($is_email_notification) {
                    $emailrecords = FeedbackNoteManager::where('tenant_id', $tenant_id)->get();
                    foreach ($emailrecords as $record) {
                        $values['legal_team_email'] = $record->email;
                        $values['legal_team_name'] = $record->email;
                        $this->sendGridServices->sendClientFeedbackSuccessMail($values, $feedback_body_str, $this->cur_tenant_id);
                    }
                    // Update Notification Log
                    TenantNotificationLog::where('id', $log_id)->update([
                        'sent_email_notification_at' => Carbon::now()
                    ]);
                }

                if ($feedbacks) {
                    return redirect()->back()->with('success', 'Successfully submitted feedback');
                }
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     *  [POST] Allow Note Editing
     */
    public function allowNoteEditing(Request $request)
    {
        $response = ['success' => false, 'message' => ''];
        $data = $request->all();
        try {
            $tenant_id = $this->cur_tenant_id;
            if ($tenant_id && !empty($data['note_id'])) {
                $Tenant = Tenant::find($tenant_id);
                $apiurl = config('services.fv.default_api_base_url');
                if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                    $apiurl = $Tenant->fv_api_base_url;
                }
                $filevine_api = new FilevineService($apiurl, "");

                // get api user id.
                $fvauth_user_obj = json_decode($filevine_api->getAuthUser(), TRUE);
                $api_vine_comment_user_id = null;
                if ($fvauth_user_obj && isset($fvauth_user_obj['user'])) {
                    if (isset($fvauth_user_obj['user']['userId']) && isset($fvauth_user_obj['user']['userId']['native'])) {
                        $api_vine_comment_user_id = $fvauth_user_obj['user']['userId']['native'];
                    }
                }
                if ($api_vine_comment_user_id != null) {
                    $response['success'] = true;
                } else {
                    $response['message'] = 'Not allowed to reply now!';
                }

                // $payload = array('allowEditing' => "Yes");
                // $note = json_decode($filevine_api->updateNoteById($data['note_id'], $payload));
                // if ($note && isset($note['noteId']) && isset($note['allowEditing']) && $note['allowEditing']) {
                //     $response['success'] = true;
                // } else {
                //     $response['message'] = 'Not allowed to reply now!';
                // }

            } else {
                $response['message'] = 'Invalid Filevine Note!';
            }
        } catch (\Exception $e) {
            $response['message'] = 'Not allowed to reply now!';
        }
        return json_encode($response);
    }

    /**
     *  [POST] Send Note Reply
     */
    public function sendNoteReply(Request $request)
    {
        $response = ['success' => false, 'message' => ''];
        $data = $request->all();
        try {
            $tenant_id = $this->cur_tenant_id;
            if ($tenant_id && !empty($data['note_id']) && !empty($data['note_body']) && !empty($data['project_id'])) {

                $api_vine_comment_user_id = null;
                $Tenant = Tenant::find($tenant_id);
                $apiurl = config('services.fv.default_api_base_url');
                if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                    $apiurl = $Tenant->fv_api_base_url;
                }
                $filevine_api = new FilevineService($apiurl, "");

                // get api user id.
                $auth_user_obj = json_decode($filevine_api->getAuthUser(), TRUE);
                if ($auth_user_obj && isset($auth_user_obj['user'])) {
                    if (isset($auth_user_obj['user']['userId']) && isset($auth_user_obj['user']['userId']['native'])) {
                        $api_vine_comment_user_id = $auth_user_obj['user']['userId']['native'];
                    }
                }

                if ($api_vine_comment_user_id != null) {
                    $payload = array(
                        'authorId' => array(
                            'native' => $api_vine_comment_user_id
                        ),
                        'body' => $data['note_body']
                    );

                    $comment = json_decode($filevine_api->createNoteComment($data['note_id'], $payload), TRUE);

                    if ($comment && isset($comment['commentId'])) {
                        // create record in database
                        //FvNoteComment::create(['fv_project_id' => $data['project_id'], 'fv_note_id' => $data['note_id'], 'fv_comment_id' => $comment['commentId']['native'], 'fv_comment_body' => $data['note_body'], 'client_name' => $data['client_name'], 'client_email' => $data['client_email']]);

                        // Send Admin Notification (Email and Post to Filevine)
                        $notification_config = TenantNotificationConfig::where('tenant_id', $this->cur_tenant_id)->where('event_short_code', TenantNotificationConfig::TeamMessageResponse)->first();
                        if ($notification_config) {
                            $params = [
                                'project_id' => $data['project_id'],
                                'tenant_id' => $this->cur_tenant_id,
                                'client_id' => !is_null(session()->get('contact_id')) ? session()->get('contact_id') : 0,
                                'note_body' => 'A response has been sent from team message!',
                                'action_name' => 'Team Message Response',
                            ];
                            NotificationHandlerService::callActionService($notification_config, $params);
                        }

                        $response['success'] = true;
                        $response['message'] = 'Your reply was sent!';

                        // Call automated workflow webhook trigger function
                        app('App\Http\Controllers\API\AutomatedWorkflowWebhookController')->teamMessageReply(['ProjectId' => $data['project_id'], 'fv_client_id' => (!is_null(session()->get('contact_id')) ? session()->get('contact_id') : 0), 'tenant_id' => $this->cur_tenant_id]);
                    } else {
                        $response['message'] = 'Unable to send reply at the moment.';
                    }
                } else {
                    $response['message'] = 'Invalid Filevine comment.';
                }
            } else {
                $response['message'] = 'Unable to send reply at the moment.';
            }
        } catch (\Exception $e) {
            $response['message'] = 'Unable to send reply at the moment.';
        }
        return json_encode($response);
    }

    /**
     *  [GET] Regenerate the description from the variables with the real info
     */
    public function parsePhaseDescription($description, $tenant_name, $data, $type = null)
    {
        try {

            $variable_service = new VariableService();

            $clientName = isset($data['project']['clientName']) ? $data['project']['clientName'] : '';
            list($client_firstname) = explode(' ', $clientName);

            $additional_data = [
                'client_fullname' => isset($data['project']['clientName']) ? $data['project']['clientName'] : '',
                'client_firstname' => $client_firstname,
                'client_portal_url' => route('client', ['subdomain' => session()->get('subdomain')]),
                'project_name' => isset($data['project']['projectName']) ? $data['project']['projectName'] : '',
                'project_phase' => isset($data['project']['phaseName']) ? $data['project']['phaseName'] : '',
                'law_firm_name' => $tenant_name,
                'tenant_id' => $this->cur_tenant_id,
                'fv_project_id' => isset($data['projectId']['native']) ? $data['projectId']['native'] : 0,
            ];

            if ($type == 'phase_mapping') {

                $description = $variable_service->updateVariables($description, 'is_timeline_mapping', $additional_data);

                if (!empty($data) && !empty($description)) {

                    if (isset($data['attorney']['name']) && strpos($description, '[legal_team_attorney]') !== false) {
                        $description = str_replace("[legal_team_attorney]", $data['attorney']['name'], $description);
                    }
                    if (isset($data['paralegal']['name']) && strpos($description, '[legal_team_paralegal]') !== false) {
                        $description = str_replace("[legal_team_paralegal]", $data['paralegal']['name'], $description);
                    }
                }
            } else {

                $description = $variable_service->updateVariables($description, 'is_project_timeline', $additional_data);

                if (!empty($data) && !empty($description)) {
                    if (isset($data['attorney']['name']) && strpos($description, '[legal_team_attorney]') !== false) {
                        $description = str_replace("[legal_team_attorney]", $data['attorney']['name'], $description);
                    }
                    if (isset($data['paralegal']['name']) && strpos($description, '[legal_team_paralegal]') !== false) {
                        $description = str_replace("[legal_team_paralegal]", $data['paralegal']['name'], $description);
                    }
                }
            }

            return $description;
        } catch (\Exception $e) {
            \Log::debug("Lookup page timeline : (Error: " . $e->getMessage() . ")");
        }
    }

    /**
     * Get Contact info from filevine
     */
    public function getContactInfo(Request $request)
    {
        $project_id = $_POST['project_id'];
        $client_native_id = $_POST['client_native_id'] ?? null;
        $contact_project_email_address = isset($_POST['contact_project_email_address']) ? $_POST['contact_project_email_address'] : '';
        $project_name = isset($_POST['project_name']) ? $_POST['project_name'] : '';
        try {
            $tenant_id = $this->cur_tenant_id;
            $Tenant = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $filevine_api = new FilevineService($apiurl, "");
            if ($client_native_id == null) {
                $project_obj = json_decode($filevine_api->getProjectsById($project_id), TRUE);
                if (isset($project_obj['clientId']['native'])) {
                    $client_native_id = $project_obj['clientId']['native'];
                }
                if (isset($project_obj['projectEmailAddress'])) {
                    $contact_project_email_address = $project_obj['projectEmailAddress'];
                }
            }

            if (isset($client_native_id) && $client_native_id != null) {
                $clientId = $client_native_id;
                $client_obj = json_decode($filevine_api->getContactByContactId($clientId), true);
                $states = $this->_getStates();
                return $this->_loadContent('client.pages.contact_info_modal', ['project_id' => $project_id, 'project_name' => $project_name, 'contact_project_email_address' => $contact_project_email_address, 'contact_info' => $client_obj, 'client_id' => $clientId, 'states' => $states]);
            }
        } catch (\Exception $e) {
            $output = ['success' => 0];
        }
        return response()->json($output);
    }

    /**
     * Update Contact info from filevine
     */
    function updateContactInfo()
    {
        try {
            $client_id = $_POST['client_id'];
            $project_id = $_POST['project_id'];
            $contact_project_email_address = $_POST['contact_project_email_address'];
            $project_name = $_POST['project_name'];
            $fv_note_body = "Client Contact Information Updated.";
            $original_contact_info = "";
            $updated_contact_info = "";
            $all_info = "";
            if (!empty($client_id)) {
                $tenant_id = $this->cur_tenant_id;
                $Tenant = Tenant::find($tenant_id);
                $apiurl = config('services.fv.default_api_base_url');
                if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                    $apiurl = $Tenant->fv_api_base_url;
                }
                $filevine_api = new FilevineService($apiurl, "");
                $contact = json_decode($filevine_api->getContactByContactId($client_id), TRUE);
                if (isset($contact['personId']['native'])) {
                    foreach ($contact['emails'] as $key => $single_email) {

                        $all_info .= ", Email=" . $_POST['email'][$key];
                        if ($contact['emails'][$key]['address'] != $_POST['email'][$key]) {
                            $fv_note_body .= " Original Info: Email=" . $contact['emails'][$key]['address'];
                            $fv_note_body .= " New Info: Email=" . $_POST['email'][$key];
                            $original_contact_info .= ", Email=" . $contact['emails'][$key]['address'];
                            $updated_contact_info .= ", Email=" . $_POST['email'][$key];
                        }

                        $contact['emails'][$key]['address'] = $_POST['email'][$key];
                    }

                    foreach ($contact['addresses'] as $key => $single_address) {

                        $all_info .= ", Address=" . $_POST['line1'][$key];
                        if ($contact['addresses'][$key]['line1'] != $_POST['line1'][$key]) {
                            $fv_note_body .= " Original Info: Address=" . $contact['addresses'][$key]['line1'];
                            $fv_note_body .= " New Info: Address=" . $_POST['line1'][$key];
                            $original_contact_info .= ", Address=" . $contact['addresses'][$key]['line1'];
                            $updated_contact_info .= ", Address=" . $_POST['line1'][$key];
                        }

                        $all_info .= ", City=" . $_POST['city'][$key];
                        if ($contact['addresses'][$key]['city'] != $_POST['city'][$key]) {
                            $fv_note_body .= " Original Info: City=" . $contact['addresses'][$key]['city'];
                            $fv_note_body .= " New Info: City=" . $_POST['city'][$key];
                            $original_contact_info .= ", City=" . $contact['addresses'][$key]['city'];
                            $updated_contact_info .= ", City=" . $_POST['city'][$key];
                        }

                        $all_info .= ", State=" . $_POST['state'][$key];
                        if ($contact['addresses'][$key]['state'] != $_POST['state'][$key]) {
                            $fv_note_body .= " Original Info: State=" . $contact['addresses'][$key]['state'];
                            $fv_note_body .= " New Info: State=" . $_POST['state'][$key];
                            $original_contact_info .= ", State=" . $contact['addresses'][$key]['state'];
                            $updated_contact_info .= ", State=" . $_POST['state'][$key];
                        }

                        $all_info .= ", Postal Code=" . $_POST['postalCode'][$key];
                        if ($contact['addresses'][$key]['postalCode'] != $_POST['postalCode'][$key]) {
                            $fv_note_body .= " Original Info: Postal Code=" . $contact['addresses'][$key]['postalCode'];
                            $fv_note_body .= " New Info: Postal Code=" . $_POST['postalCode'][$key];
                            $original_contact_info .= ", Postal Code=" . $contact['addresses'][$key]['postalCode'];
                            $updated_contact_info .= ", Postal Code=" . $_POST['postalCode'][$key];
                        }

                        $contact['addresses'][$key]['line1'] = $_POST['line1'][$key];
                        $contact['addresses'][$key]['city'] = $_POST['city'][$key];
                        $contact['addresses'][$key]['state'] = $_POST['state'][$key];
                        $contact['addresses'][$key]['postalCode'] = $_POST['postalCode'][$key];
                    }

                    foreach ($contact['phones'] as $key => $single_phone) {

                        $all_info .= ", Phone=" . $_POST['phone'][$key];
                        if ($contact['phones'][$key]['number'] != $_POST['phone'][$key]) {
                            $fv_note_body .= " Original Info: Phone=" . $contact['phones'][$key]['number'];
                            $fv_note_body .= " New Info: Phone=" . $_POST['phone'][$key];
                            $original_contact_info .= ", Phone=" . $contact['phones'][$key]['number'];
                            $updated_contact_info .= ", Phone=" . $_POST['phone'][$key];
                        }

                        $contact['phones'][$key]['rawNumber'] = $_POST['phone'][$key];
                        $contact['phones'][$key]['number'] = $_POST['phone'][$key];
                    }

                    $contactupdate = json_decode($filevine_api->updateContact($client_id, $contact));

                    // Send Admin Notification Email & Post to Filevine
                    $is_post_to_filevine = false;
                    $is_email_notification = false;
                    $config = TenantNotificationConfig::where('event_short_code', TenantNotificationConfig::ContactInfoUpdated)->where('tenant_id', $tenant_id)->first();

                    // Insert Notification Log
                    $log_data = [
                        'tenant_id' => $config->tenant_id,
                        'event_name' => $config->event_name,
                        'fv_project_id' => $project_id,
                        'fv_project_name' => $project_name,
                        'fv_client_id' => $client_id,
                        'created_at' => Carbon::now(),
                    ];
                    $log_id = TenantNotificationLog::insertGetId($log_data);

                    if ($config) {
                        $is_post_to_filevine = $config->is_post_to_filevine;
                        $is_email_notification = $config->is_email_notification;
                    }
                    if ($is_post_to_filevine) {
                        $notiService = new NotificationFileVineNoteAction();
                        $notiService->createNote($tenant_id, $project_id, $client_id, $fv_note_body);

                        // Update Notification Log
                        TenantNotificationLog::where('id', $log_id)->update([
                            'sent_post_to_filevine_at' => Carbon::now()
                        ]);
                    }

                    if (empty($original_contact_info) && empty($updated_contact_info)) {
                        $original_contact_info = $updated_contact_info = $all_info;
                    }

                    $sg_data = [
                        'original_contact_info' => ltrim($original_contact_info, ', '),
                        'updated_contact_info' => ltrim($updated_contact_info, ', '),
                        'client_name' => isset($Tenant->tenant_name) ? $Tenant->tenant_name : '',
                        'fv_project_id' => $project_id,
                        'fv_project_name' => $project_name,
                        'fv_contact_id' => $client_id
                    ];
                    if (!empty($contact_project_email_address)) {
                        $this->sendGridServices->sendContactUpdateEmail($contact_project_email_address, $sg_data);
                    }
                    if ($is_email_notification) {
                        $emailrecords = FeedbackNoteManager::where('tenant_id', $tenant_id)->get();
                        foreach ($emailrecords as $record) {
                            $this->sendGridServices->sendContactUpdateEmail(trim($record->email), $sg_data);
                        }

                        // Update Notification Log
                        TenantNotificationLog::where('id', $log_id)->update([
                            'sent_email_notification_at' => Carbon::now()
                        ]);
                    }



                    return redirect()->back();
                }
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function customizeLegalTeamMessage($note_details = NULL)
    {
        try {
            if (!empty($note_details['body'])) {
                $note_details['body'] = str_replace(array('~', '_', '__', '___', '==', '--', '[', ']', '>', '`', '* '), '', $note_details['body']);
            }

            return $note_details;
        } catch (\Exception $ex) {
            $error = [
                __FILE__,
                __LINE__,
                $ex->getMessage()
            ];
            Logging::warning(json_encode($error));
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

    /**
     * update URL link with the a tag in the text message
     */
    public function convertTeamMessageLinks($message)
    {
        try {
            $pattern_https = '@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.%-=#]*(\?\S+)?)?)?)@';
            $pattern_http = '@(http?://([-\w\.]+)+(:\d+)?(/([\w/_\.%-=#]*(\?\S+)?)?)?)@';
            $message = preg_replace($pattern_http, '<a href="$0" target="_blank" title="$0">$0</a>', $message);
            $message = preg_replace($pattern_https, '<a href="$0" target="_blank" title="$0">$0</a>', $message);

            return $message;
        } catch (\Exception $ex) {
            $error = [
                __FILE__,
                __LINE__,
                $ex->getMessage()
            ];
            Logging::warning(json_encode($error));
            return $message;
        }
    }

    /**
     * [POST] Update or create team member
     */
    public function updateTeamMemberData($filevine_api, $Tenant, $value, $picParts, $type)
    {
        $pictureUrl = "";
        try {
            // check if team member exists
            if ($type == 'roles') {
                $teamMember = FvTeamMembers::where(['tenant_id' => $Tenant->id, 'fv_user_id' => $value['userId']['native']])->first();
                if (!isset($teamMember->id)) {
                    $teamMember = FvTeamMembers::create([
                        'tenant_id' => $Tenant->id,
                        'fv_user_id' => $value['userId']['native'],
                        'user_name' => $value['username'],
                        'first_name' => $value['firstName'],
                        'last_name' => $value['lastName'],
                        'email' => $value['email'],
                        'full_name' => $value['fullname'],
                        'level' => $value['level'],
                        'team_org_roles' => json_encode($value['teamOrgRoles']),
                        'picture_url' => $picParts[count($picParts) - 1],
                        's3_image_url' => '',
                        'is_primary' => $value['isPrimary'],
                        'is_admin' => $value['isAdmin'],
                        'is_first_primary' => $value['isFirstPrimary'],
                        'is_only_primary' => $value['isOnlyPrimary'],
                    ]);
                }
            } else if ($type == 'notroles') {
                $teamMember = FvTeamMembers::where(['tenant_id' => $Tenant->id, 'fv_user_id' => $value['id']])->first();
                if (!isset($teamMember->id)) {
                    $teamMember = FvTeamMembers::create([
                        'tenant_id' => $Tenant->id,
                        'fv_user_id' => $value['id'],
                        'user_name' => $value['emails'][0]['address'],
                        'first_name' => $value['firstName'],
                        'last_name' => isset($value['lastName']) ? $value['lastName'] : '',
                        'email' => $value['emails'][0]['address'],
                        'full_name' => $value['fullname'],
                        'picture_url' => $picParts[count($picParts) - 1],
                        's3_image_url' => '',
                    ]);
                }
            }
            // check if image needs to be updated
            if (!empty($teamMember->picture_url) && (empty($teamMember->s3_image_url) || (!empty($teamMember->s3_image_url) && $teamMember->picture_url != $picParts[count($picParts) - 1]))) {
                $teamMember->picture_url = $picParts[count($picParts) - 1];
                $teamMember->save();
                $memberPicture = json_decode($filevine_api->getTeamMemberPicture($teamMember->picture_url), true);
                if (isset($memberPicture['ContentType']) && isset($memberPicture['Image'])) {
                    $picExtension = explode('.', $teamMember->picture_url);
                    $dateNow = Carbon::now();
                    $filePath = $Tenant->tenant_name . '/client_photos/fv_client_img_' . $teamMember->fv_user_id . '_' . $teamMember->id . '_' . $dateNow->format('Y_m_d_H_i_s') . '.' . $picExtension[count($picExtension) - 1];
                    $pictureUrl = $this->uploadTeamMemberImage($memberPicture['Image'], $filePath);
                    $teamMember->s3_image_url = $pictureUrl;
                    $teamMember->save();
                } else {
                    $pictureUrl = "";
                }
            } else {
                $pictureUrl = $teamMember->s3_image_url;
            }
        } catch (\Exception $e) {
        }
        return $pictureUrl;
    }

    /**
     * [POST] Upload team member image
     */
    public function uploadTeamMemberImage($file, $filePath)
    {
        try {
            \Storage::disk('s3')->put($filePath, base64_decode($file), 'public');
            $publicUrl = \Storage::disk('s3')->url($filePath);
            return $publicUrl;
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            die();
            return "";
        }
    }

    public function filterFieldValue($field_value)
    {
        if (strtolower($field_value) == 'true' || strtolower($field_value) == 'yes') {
            $field_value = true;
        } else if (strtolower($field_value) == 'false' || strtolower($field_value) == 'no') {
            $field_value = false;
        }
        return $field_value;
    }
    /**
     * [GET] Get Text By Session Language
     */
    public function getTextFromLanguage()
    {
        try {
            $tr = new GoogleTranslate();
            $tr->setSource();
            $tr->setTarget(!is_null(session()->get('lang')) ? session()->get('lang') : 'en');

            $data['Drop_files_here_or'] = $tr->translate('Drop files here or');
            $data['choose_files'] = $tr->translate('choose files');
            $data['success'] = true;

            return response()->json($data);
        } catch (\Exception $ex) {
            $data['success'] = false;
            return response()->json($data);
        }
    }

    /**
     * [GET] Download uploaded file from new link
     */
    public function downloadFvFile(Request $request)
    {
        $response = [
            'status'  => false,
            'message' => "Unable to process your request at the moment!",
        ];
        try {
            $tenant_id = $this->cur_tenant_id;
            $Tenant = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $fv_service = new FilevineService($apiurl, "");

            $id = $request->get('id');
            $type = $request->get('type');
            if ($type == 'shared') {
                $fv_client_upload_document = FvSharedDocument::find($id);
            } else {
                $fv_client_upload_document = FvClientUploadDocument::find($id);
            }
            $fv_download_url = $fv_service->getDocumentDownloadUrl($fv_client_upload_document->fv_document_id);

            $fv_client_upload_document->download_count = $fv_client_upload_document->download_count + 1;
            $fv_client_upload_document->save();

            return response()->streamDownload(function () use ($fv_download_url) {
                echo file_get_contents($fv_download_url);
            }, $fv_client_upload_document->fv_filename);
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
            return response()->json($response);
        }
    }

    public function getMatchFirstContactId($fv_service, $full_name)
    {
        try {
            $contact_id = 0;
            if(!empty($full_name)) {
                $contact = json_decode($fv_service->getContactByFullName(trim($full_name)), TRUE);
                if (isset($contact['count']) && $contact['count'] != 0) {
                    $contact_id = isset($contact['items'][0]['personId']['native']) ? $contact['items'][0]['personId']['native'] : 0;
                }
            }
            return $contact_id;
        } catch (\Exception $ex) {}
    }

    /**
     *  [POST] Upload files to project root folder
     */
    private function uploadDocumentsToFV($filevine_api, $project_id, $files)
    {
        $field_details = [];
        try {
            if(count($files) > 0) {
                foreach ($files as $file) {
                    $file_size = $file->getSize();
                    $file_name = $file->getClientOriginalName();
                    $file_binary = file_get_contents($file->getRealPath());

                    $upload_params = [
                        'filename' => $file_name,
                        'size' => $file_size
                    ];
                    $upload_details = json_decode($filevine_api->createDocumentUploadUrl($upload_params), true);
                    if (isset($upload_details['documentId']) && isset($upload_details['url'])) {
                        $url = $upload_details['url'];
                        $content_type = $upload_details['contentType'];
                        $document_id = $upload_details['documentId'];
                        // upload file to returned url
                        $upload_binary = $filevine_api->uploadDocumentToUploadUrl($url, $file_binary, $content_type);
                        if (empty($upload_binary)) {
                            $document_params = [
                                'documentId' => $document_id,
                                'filename' => $file_name,
                                'size' => $file_size,
                                'projectId' => [
                                    'native' => $project_id
                                ]
                            ];
                            $add_to_project = json_decode($filevine_api->addDocumentToProject($project_id, $document_id['native'], $document_params), true);

                            $field_details[] = [
                                "id" => $document_id['native']
                            ];
                        }
                    }
                }
            }
        } catch (\Exception $ex) {}
        return $field_details;
    }
}
