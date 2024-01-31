<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log as Logging;

use Carbon\Carbon;
use Image;
use Exception;

use App\Services\FVInitializerService;
use App\Services\CampaignMonitorService;
use App\Services\FilevineService;
use App\Services\SubscriptionService;

use App\Models\SmsLineConfig;
use App\Models\SubscriptionPlans;
use App\Models\ClientNotification;
use App\Models\FeedbackNoteManager;
use App\Models\LegalteamConfig;
use App\Models\ConfigCustomProjectName;
use App\Models\TenantCustomVital;
use App\Models\ConfigProjectVital;
use App\Models\TenantNotificationConfig;
use App\Models\TenantLive;
use App\Models\Tenant;
use App\Models\User;


class ClientPortalSetupController extends Controller
{

    public $cur_tenant_id;
    public $cmServices;

    public function __construct(CampaignMonitorService $cmServices)
    {
        $this->cmServices = $cmServices;
        Controller::setSubDomainName();
        $this->cur_tenant_id = session()->get('tenant_id');
    }

    /**
     * [GET] Client Portal Setup Page for Tenanat Admin
     */
    public function index()
    {
        try {
            $tenantLive = TenantLive::where('tenant_id', $this->cur_tenant_id)->first();
            $notices = ClientNotification::where("tenant_id", $this->cur_tenant_id)->orderBy('id', 'DESC')->get();
            $notificationEmails = FeedbackNoteManager::where("tenant_id", $this->cur_tenant_id)->get();

            if ($notificationEmails->count() == 0) {
                $manager_legal_team_config = LegalteamConfig::where('tenant_id', $this->cur_tenant_id)
                    ->where('type', LegalteamConfig::TYPE_STATIC)
                    ->where('role_title', (LegalteamConfig::$legalteam_config_types)['ClientRelationsManager'])
                    ->first();

                if ($manager_legal_team_config instanceof LegalteamConfig && !empty($manager_legal_team_config->email)) {
                    FeedbackNoteManager::create([
                        'tenant_id' => $this->cur_tenant_id,
                        'email' => $manager_legal_team_config->email,
                    ]);

                    $notificationEmails = FeedbackNoteManager::where("tenant_id", $this->cur_tenant_id)->get();
                }
            }

            // Disabling the notice once expired
            foreach ($notices as $key => $notice) {
                if ($notices[$key] instanceof ClientNotification && $notices[$key]->is_active == 1 && Carbon::now()->startOfDay()->timestamp > strtotime($notices[$key]->end_date)) {
                    $notices[$key]->is_active = 0;
                    $notices[$key]->save();
                }
            }


            $tenant_id = $this->cur_tenant_id;
            $Tenant = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $filevine_api = new FilevineService($apiurl, "");

            $fv_project_type_list = $filevine_api->getProjectTypeList();
            $fv_project_type_list = json_decode($fv_project_type_list);
            if (isset($fv_project_type_list->items) and !empty($fv_project_type_list->items)) {
                $project_type_lists = $fv_project_type_list->items;
            } else {
                $project_type_lists = [];
            }

            // Add notification events for a tenant if not exist
            $events   = TenantNotificationConfig::getStaticEventList();
            foreach ($events as $key => $value) {
                $is_exist = TenantNotificationConfig::where('tenant_id', $tenant_id)->where('event_short_code', $value['event_short_code'])->exists();
                if (!$is_exist) {
                    $value['tenant_id'] = $tenant_id;
                    $value['created_at'] = date('Y-m-d H:i:s');
                    TenantNotificationConfig::insert($value);
                }
            }
            $tenantNotificationConfigs = TenantNotificationConfig::where('tenant_id', $tenant_id)->get();

            $tenantCustomVital = TenantCustomVital::where('tenant_id', $tenant_id)->first();

            // Default contact dropdown item
            $config = DB::table('config')->where('tenant_id', $tenant_id)->first();
            $contact_type_html = "";
            if (isset($config->default_sms_way) && $config->default_sms_way == 'broadcast_number') {
                $default_sms_custom_contact_labels = explode(',', $config->default_sms_custom_contact_label);
                $contact_metas = json_decode($filevine_api->getContactMetadata());
                $options = '<option value="">Phone Label</option>';
                if (isset($contact_metas) && !empty($contact_metas)) {
                    foreach ($contact_metas as $contact_meta) {
                        if ($contact_meta->selector == 'phones') {
                            if (isset($contact_meta->allowedValues)) {
                                $allowedValues = $contact_meta->allowedValues;
                                foreach ($allowedValues as $allowedValue) {
                                    if (in_array($allowedValue->name, $default_sms_custom_contact_labels)) {
                                        $contact_type_html .= '<option value="' . $allowedValue->name . '" selected>' . $allowedValue->name  . '</option>';
                                    } else {
                                        $contact_type_html .= '<option value="' . $allowedValue->name . '">' . $allowedValue->name  . '</option>';
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // SMS line config
            $sms_line_config = SmsLineConfig::where('tenant_id', $tenant_id)->first();
            if ($sms_line_config == null) {
                $sms_line_config = SmsLineConfig::create(
                    [
                        'tenant_id' => $tenant_id
                    ]
                );
                $sms_line_config = SmsLineConfig::where('tenant_id', $tenant_id)->first();
            }

            $sms_line_config_post_orders[$sms_line_config->project_sms_number_order] = ['project_sms_number_order', 'Send to Project SMS Number (requires Project SMS Number as a Project Vital)'];
            $sms_line_config_post_orders[$sms_line_config->mailroom_order] = ['mailroom_order', 'Send to the Mailroom'];
            $sms_line_config_post_orders[$sms_line_config->project_feed_note_order] = ['project_feed_note_order', 'Post to Filevine Project Feed as a Note'];
            ksort($sms_line_config_post_orders);

            // Load Billing Page Modal Based on this data
            $custom_plan_by_tenant_id = SubscriptionPlans::where('plan_is_default', 0)
                ->where('plan_tenant_id', $tenant_id)
                ->where('plan_is_active', 1)
                ->first();

            $fv_org_id = "";
            $fv_total_project = 0;

            if ($filevine_api->verify_api_and_session() && SubscriptionService::checkIfTenantFirstPayment($this->cur_tenant_id)) {
                if ($custom_plan_by_tenant_id == null) {
                    $fv_org_id = $filevine_api->getOrgId();
                    $fv_total_project = (new FVInitializerService())->getTenantReport($tenant_id);
                    Tenant::where('id', $tenant_id)->update([
                        'fv_project_count' => $fv_total_project
                    ]);

                    // $offset = 0;
                    // $limit = 1000;
                    // do {
                    //     $projects_object = json_decode($filevine_api->getProjectsList($limit, $offset));
                    //     if (isset($projects_object->items)) {
                    //         $project_items = collect($projects_object->items);
                    //         $project_active_count = count($project_items->where('isArchived', false));
                    //         $fv_total_project += $project_active_count;
                    //     }
                    //     $hasMore = isset($projects_object->hasMore) ? $projects_object->hasMore : false;
                    //     $offset += $limit;
                    // } while ($hasMore);
                } else {
                    return redirect(url("/admin/billing/0"));
                }
            }

            return $this->_loadContent('admin.pages.settings', compact('fv_org_id', 'fv_total_project', 'notices', 'notificationEmails', 'project_type_lists', 'tenantCustomVital', 'tenantNotificationConfigs', 'contact_type_html', 'sms_line_config', 'sms_line_config_post_orders'));
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
     * [POST] Updates Settings Page for Admin
     */
    public function settings_post(Request $request, $subdomain)
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
                if ($request->input('fv_api_key') && $request->input('fv_key_secret')) {
                    $tenant_id = $this->cur_tenant_id;
                    $Tenant = Tenant::find($tenant_id);
                    $apiurl = $this->_getFVAPIBaseUrl($fv_tenant_base_url);
                    if (empty($apiurl) && isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                        $apiurl = $Tenant->fv_api_base_url;
                    }

                    $filevine_api = new FilevineService($apiurl, $request);

                    if (!$filevine_api->verify_api_and_session()) {
                        $msg_err = true;
                    }
                }

                if ($msg_err) {
                    return redirect()->route('settings', ['subdomain' => $subdomain])
                        ->with('msg_err', $msg_err);
                }


                // Update subscription when change FV API KEY & Secret
                $config = DB::table('config')->where('tenant_id', $request->tenant_details->id)->first();
                if ($config != null && $config->fv_api_key != $request->input('fv_api_key') && $config->fv_key_secret != $request->input('fv_key_secret')) {
                    $Tenant = Tenant::find($this->cur_tenant_id);
                    $apiurl = config('services.fv.default_api_base_url');
                    if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                        $apiurl = $Tenant->fv_api_base_url;
                    }
                    $fv_service_old = new FilevineService($apiurl, "", $Tenant->id);
                    if ($fv_service_old->verify_api_and_session()) {
                        $get_all_subscriptions = json_decode($fv_service_old->getSubscriptionsList());
                        if (config('app.env') == 'local') {
                            $http = "http://";
                        } else {
                            $http = "https://";
                        }
                        $domainName = config('app.domain');
                        $tenant_url = $http . $Tenant->tenant_name . '.' . $domainName;
                        foreach ($get_all_subscriptions as $single_subscription) {
                            if (isset($single_subscription->endpoint)) {
                                if (strpos(strtolower($single_subscription->endpoint), strtolower($tenant_url)) !== false) {
                                    $filevine_api->createSubscription($single_subscription->name, $single_subscription->endpoint, $single_subscription->eventIds[0]);
                                    $fv_service_old->deleteSubscription($single_subscription->subscriptionId);
                                }
                            }
                        }
                    }
                }


                if ($request->settings_details) {
                    if ($request->hasFile('image')) {
                        $imageName = time() . '-' . $request->image->getClientOriginalName();
                        $request->image->move(public_path('/assets/uploads/client_logo/'), $imageName);

                        $values = array('product_license' => $request->input('product_license'), 'fv_api_key' => $request->input('fv_api_key'), 'fv_key_secret' => $request->input('fv_key_secret'), 'logo' => $imageName, 'updated_at' => $current_date);
                    } else {
                        $values = array('product_license' => $request->input('product_license'), 'fv_api_key' => $request->input('fv_api_key'), 'fv_key_secret' => $request->input('fv_key_secret'),  'updated_at' => $current_date);
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
                        $values = array('product_license' => $request->input('product_license') ?? "product_license", 'fv_api_key' => $request->input('fv_api_key'), 'fv_key_secret' => $request->input('fv_key_secret'), 'tenant_id' => $request->tenant_details->id, 'logo' => $imageName, 'sms_buffer_time' => env('SMS_NOTE_BUFFER_DEFAULT_TIME', '300'), 'created_at' => $current_date);
                    } else {
                        $values = array('product_license' => $request->input('product_license') ?? "product_license", 'fv_api_key' => $request->input('fv_api_key'), 'fv_key_secret' => $request->input('fv_key_secret'), 'tenant_id' => $request->tenant_details->id, 'sms_buffer_time' => env('SMS_NOTE_BUFFER_DEFAULT_TIME', '300'), 'created_at' => $current_date);
                    }

                    $configs = DB::table('config')->where('tenant_id', $request->tenant_details->id)->first();
                    if ($configs == null) {
                        DB::table('config')->insert($values);
                    } else {
                        DB::table('config')->where('tenant_id', $request->tenant_details->id)->update($values);
                    }
                }

                // call initializer endpoint to create report if not created
                (new FVInitializerService())->getTenantReport($this->cur_tenant_id);

                return redirect()->route('settings', ['subdomain' => $subdomain])
                    ->with('success', 'Setting saved successfully!')
                    ->with('msg_err', $msg_err);
            }
        } catch (\Exception $ex) {
            Logging::warning($ex->getMessage());
        }
    }

    /**
     * [POST] Update show_archieved_phase
     */
    public function update_show_archieved_phase(Request $request, $subdomain)
    {
        DB::table('config')->where('tenant_id', $request->tenant_details->id)->update([
            'is_show_archieved_phase' => $request['show_archieved_phase'] ? 1 : 0
        ]);

        return response()->json([
            'message' => 'Setting saved successfully!'
        ]);
    }

    /**
     * [POST] Save notification email
     */
    public function save_notification_email(Request $request, $subdomain)
    {
        $table = (new FeedbackNoteManager())->getTable();
        $data = $this->validate($request, [
            'email' => ["required", "email", Rule::unique($table)->where(function ($query) {
                return $query->where('tenant_id', $this->cur_tenant_id);
            })],
        ]);

        FeedbackNoteManager::create([
            'tenant_id' => $this->cur_tenant_id,
            'email' => $data['email'],
        ]);

        return back()->with('feedback_email_success', 'Setting saved successfully!');
    }

    /**
     * [POST] Delete notification email
     */
    public function delete_notification_email(Request $request, $subdomain, $id)
    {
        FeedbackNoteManager::where("tenant_id", $this->cur_tenant_id)->where('id', $id)->delete();
        return back()->with('feedback_email_success', 'Setting saved successfully!');
    }

    /**
     * [POST] Update law firm display name
     */
    public function update_law_firm_display_name(Request $request, $subdomain)
    {
        if ($request->isMethod('post')) {
            $validatedData = $request->validate([
                'lf_display_name' => "required",
            ]);

            if ($request->settings_details) {
                Tenant::where('id', $request->tenant_details->id)->update(['tenant_law_firm_name' => $request->input('lf_display_name')]);
                $user = User::where('tenant_id', $request->tenant_details->id)->first();
                $tenant = Tenant::find($request->tenant_details->id);
                $this->cmServices->updateTenant([
                    'tenant_id' => $tenant->id,
                    'tenant_name' => $tenant->tenant_name,
                    'owner_email' => $user->email,
                    'owner_first_name' => $user->full_name,
                    'tenant_law_firm_name' => $request->input('lf_display_name')
                ]);
            }

            return response()->json([
                'message' => 'Setting saved successfully!'
            ]);
        }
    }

    /**
     * [POST] Upload firms logo
     */
    public function upload_firms_logo(Request $request, $subdomain)
    {
        if ($request->isMethod('post')) {
            $validatedData = $request->validate([
                'image' => "file",
            ]);
            $current_date = date('Y-m-d H:i:s');

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $originalImage = Image::make($image);

                $imageName = time() . '-' . $request->image->getClientOriginalName();
                $filepath = public_path('/assets/uploads/client_logo');
                $image->move($filepath, $imageName);
                $filename = $filepath . '/' . $imageName;

                $thumb_width = env('LOGO_WIDTH', 719);
                $thumb_height = env('LOGO_HEIGHT', 237);
                $thumbnail = $originalImage->resize(null, $thumb_height, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $thumbnail->save($filename);

                $values = array('logo' => $imageName, 'updated_at' => $current_date);
            }
            DB::table('config')->where('tenant_id', $request->tenant_details->id)->update($values);

            return response()->json([
                'message' => 'Setting saved successfully!'
            ]);
        }
    }

    /**
     * [POST] Update display color settings
     */
    public function update_display_color_settings(Request $request, $subdomain)
    {
        if ($request->isMethod('post')) {
            $current_date = date('Y-m-d H:i:s');

            if (!empty($request->input('color_logo'))) {
                $values = array('color_logo' => $request->input('color_logo'), 'updated_at' => $current_date);
                DB::table('config')->where('tenant_id', $request->tenant_details->id)->update($values);
            }

            if (!empty($request->input('color_main'))) {
                $values = array('color_main' => $request->input('color_main'), 'updated_at' => $current_date);
                DB::table('config')->where('tenant_id', $request->tenant_details->id)->update($values);
            }

            if (!empty($request->input('color_text'))) {
                $values = array('color_text' => $request->input('color_text'), 'updated_at' => $current_date);
                DB::table('config')->where('tenant_id', $request->tenant_details->id)->update($values);
            }

            return response()->json([
                'message' => 'Setting saved successfully!'
            ]);
        }
    }

    /**
     * [POST] Upload background
     */
    public function upload_background(Request $request, $subdomain)
    {
        if ($request->isMethod('post')) {
            $validatedData = $request->validate([
                'image' => "required|file|max:10240",
            ]);
            $current_date = date('Y-m-d H:i:s');
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $originalImage = Image::make($image);

                $imageName = time() . '-' . $request->image->getClientOriginalName();
                $filepath = public_path('/assets/uploads/client_background');
                $image->move($filepath, $imageName);
                $filename = $filepath . '/' . $imageName;

                $originalImage->save($filename);

                $values = array('background' => $imageName, 'updated_at' => $current_date);
            }
            DB::table('config')->where('tenant_id', $request->tenant_details->id)->update($values);

            return response()->json([
                'message' => 'Setting saved successfully!'
            ]);
        }
    }

    /**
     * [POST] Update notification configurations
     */
    public function updateNotificationConfig(Request $request, $subdomain)
    {
        try {
            $config_id = $request->input('config_id');
            $notification_type = $request->input('notification_type');
            $config = TenantNotificationConfig::where('id', $config_id)->where('tenant_id', $this->cur_tenant_id)->first();
            if ($config) {
                if ($notification_type == 'email') {
                    $config->is_email_notification = $config->is_email_notification ? false : true;
                } else {
                    $config->is_post_to_filevine = $config->is_post_to_filevine ? false : true;
                }
                $config->save();
            }
            return response()->json([
                'status'  => true,
                'message' => "Setting saved successfully!"
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * [GET] Get Project Section
     */
    public function get_project_sections_cutom_project($subdomain, $type_id)
    {
        $response = [
            'status' => true,
            'html' => ''
        ];
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
            $html = '<option value="">Choose Option</option>';
            if (isset($fv_project_type_section_list['items']) and !empty($fv_project_type_section_list['items'])) {
                $sections = collect($fv_project_type_section_list['items']);
                $sections = $sections->where("isCollection", false);
                foreach ($sections as $key => $section) {
                    $html .= '<option value="' . $section['sectionSelector'] . '">' . $section['name'] . '</option>';
                }
            }
            $response['html'] = $html;
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json($response);
        }
    }

    /**
     * [GET] Get Project Section Fields
     */
    public function get_project_section_fields_cutom_project($subdomain, $type_id, $section_id)
    {
        $response = [
            'status' => true,
            'html' => ''
        ];
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
            $fv_project_type_section_field_list = $obj->getProjectTypeSectionFieldList($type_id, $section_id);
            if ($fv_project_type_section_field_list != null) {
                $fv_project_type_section_field_list = json_decode($fv_project_type_section_field_list, true);
            }
            $html = '<option value="">Choose Option</option>';
            if (isset($fv_project_type_section_field_list['customFields']) and !empty($fv_project_type_section_field_list['customFields'])) {
                $customFields = collect($fv_project_type_section_field_list['customFields']);
                $customFields = $customFields->whereIn("customFieldType", ["String", "Date", "Integer", "Dropdown", "Person", "IncidentDate"]);
                foreach ($customFields as $key => $field) {
                    $html .= '<option value="' . $field['fieldSelector'] . '">' . $field['name'] . '</option>';
                }
            }
            $response['html'] = $html;
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json($response);
        }
    }

    /**
     * [POST] Update notification configurations
     */
    public function save_settings_cutom_project(Request $request, $subdomain)
    {
        try {
            $data = $request->all();
            $data_array = [
                'selected_option' => $data['display_project_as'],
                'fv_project_type_id' => $data['is_custom'] == 'true' ? $data['client_custom_project_selector'] : NULL,
                'fv_project_type_name' => $data['is_custom'] == 'true' ? $data['client_custom_project_type_name'] : NULL,
                'fv_section_id' => $data['is_custom'] == 'true' ? $data['client_custom_section_selector'] : NULL,
                'fv_section_name' => $data['is_custom'] == 'true' ? $data['client_custom_section_name'] : NULL,
                'fv_field_id' => $data['is_custom'] == 'true' ? $data['client_custom_field_selector'] : NULL,
                'fv_field_name' => $data['is_custom'] == 'true' ? $data['client_custom_field_name'] : NULL,
                'sec_fv_project_type_id' => $data['is_custom_append'] == 'true' ? $data['client_custom_project_selector_optional'] : NULL,
                'sec_fv_project_type_name' => $data['is_custom_append'] == 'true' ? $data['client_custom_project_type_name_optional'] : NULL,
                'sec_fv_section_id' => $data['is_custom_append'] == 'true' ? $data['client_custom_section_selector_optional'] : NULL,
                'sec_fv_section_name' => $data['is_custom_append'] == 'true' ? $data['client_custom_section_name_optional'] : NULL,
                'sec_fv_field_id' => $data['is_custom_append'] == 'true' ? $data['client_custom_field_selector_optional'] : NULL,
                'sec_fv_field_name' => $data['is_custom_append'] == 'true' ? $data['client_custom_field_name_optional'] : NULL
            ];
            // update config table
            DB::table('config')->where('tenant_id', $this->cur_tenant_id)->update([
                'is_client_custom_project_name' => $data['is_custom'] == 'true' ? 1 : 0,
                'is_client_custom_project_name_append_another_field' => $data['is_custom_append'] == 'true' && $data['is_custom'] == 'true' ? 1 : 0
            ]);
            // update config table
            $config = DB::table('config')->where('tenant_id', $this->cur_tenant_id)->first();
            if ($config) {
                // get config custom names details
                $custom_config = ConfigCustomProjectName::where('tenant_id', $this->cur_tenant_id)->first();
                if ($custom_config) {
                    ConfigCustomProjectName::where('tenant_id', $this->cur_tenant_id)->update($data_array);
                } else {
                    $data_array['tenant_id'] = $this->cur_tenant_id;
                    ConfigCustomProjectName::create($data_array);
                }
            }
            return response()->json([
                'status'  => true,
                'message' => "Setting saved successfully!"
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => "Unable to save settings!"
            ]);
        }
    }

    /**
     * [POST] Update notification configurations
     */
    public function get_settings_cutom_project(Request $request, $subdomain)
    {
        try {
            $data = [
                'is_custom' => 0,
                'is_custom_append' => 0,
                'mappings' => null
            ];
            // update config table
            $config = DB::table('config')->where('tenant_id', $this->cur_tenant_id)->first();
            if ($config) {
                // get config custom names details
                $data['is_custom'] = $config->is_client_custom_project_name;
                $data['is_custom_append'] = $config->is_client_custom_project_name_append_another_field;

                $mappings = null;
                $custom_config = ConfigCustomProjectName::where('tenant_id', $this->cur_tenant_id)
                    ->select('selected_option', 'fv_project_type_id', 'fv_project_type_name', 'fv_section_id', 'fv_section_name', 'fv_field_id', 'fv_field_name', 'sec_fv_project_type_id', 'sec_fv_project_type_name', 'sec_fv_section_id', 'sec_fv_section_name', 'sec_fv_field_id', 'sec_fv_field_name')
                    ->first();
                if ($custom_config && $data['is_custom'] == 1) {
                    $mappings['object'] = $custom_config;
                    // get main values
                    if ($custom_config->selected_option == 'field_value') {
                        $mappings['main']['sections'] = json_decode($this->get_project_sections_cutom_project($subdomain, $custom_config->fv_project_type_id)->getContent(), true)['html'];
                        $mappings['main']['fields'] = json_decode($this->get_project_section_fields_cutom_project($subdomain, $custom_config->fv_project_type_id, $custom_config->fv_section_id)->getContent(), true)['html'];
                    }

                    // get append values
                    if ($data['is_custom_append'] == 1) {
                        $mappings['append']['sections'] = json_decode($this->get_project_sections_cutom_project($subdomain, $custom_config->sec_fv_project_type_id)->getContent(), true)['html'];
                        $mappings['append']['fields'] = json_decode($this->get_project_section_fields_cutom_project($subdomain, $custom_config->sec_fv_project_type_id, $custom_config->sec_fv_section_id)->getContent(), true)['html'];
                    }
                    $data['mappings'] = $mappings;
                }
            }
            return response()->json([
                'status'  => true,
                'message' => "Here are settings details",
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => "Unable to get settings details",
                'data' => $data
            ]);
        }
    }


    /**
     *  [GET] Get Contact Meta Data
     */
    public function getContactMetadata()
    {
        try {
            $Tenant = Tenant::find($this->cur_tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $fv_service = new FilevineService($apiurl, "");

            $contact_metas = json_decode($fv_service->getContactMetadata());
            $options = '<option value="">Phone Label</option>';
            if (isset($contact_metas) && !empty($contact_metas)) {
                foreach ($contact_metas as $contact_meta) {
                    if ($contact_meta->selector == 'phones') {
                        if (isset($contact_meta->allowedValues)) {
                            $allowedValues = $contact_meta->allowedValues;
                            foreach ($allowedValues as $allowedValue) {
                                $options .= '<option value="' . $allowedValue->name . '">' . $allowedValue->name  . '</option>';
                            }
                        }
                    }
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
     * [POST] Save default contact
     */
    public function saveDefaultContact(Request $request, $subdomain)
    {
        try {
            $default_sms_custom_contact_labels = "";
            if (isset($request->default_sms_custom_contact_label) && !empty($request->default_sms_custom_contact_label)) {
                foreach ($request->default_sms_custom_contact_label as $contact_label) {
                    $default_sms_custom_contact_labels .= $contact_label . ",";
                }
                if (strlen($default_sms_custom_contact_labels)) {
                    $default_sms_custom_contact_labels = rtrim($default_sms_custom_contact_labels, ',');
                }
            }

            $values = [
                'default_sms_way_status' => $request->input('default_sms_way_status') == 'on' ? 1 : 0,
                'number_submitted_by_user' => $request->input('number_submitted_by_user') == 'on' ? 1 : 0,
                'default_sms_way' => $request->default_sms_way,
                'default_sms_custom_contact_label' => $default_sms_custom_contact_labels,
            ];

            DB::table('config')->where('tenant_id', $request->tenant_details->id)->update($values);

            return redirect()->route('settings', ['subdomain' => $subdomain])
                ->with('success', 'Setting saved successfully!');
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * [POST] Add/Update Reply To Org Email
     */
    public function addReplyToOrgEmail(Request $request, $subdomain)
    {
        DB::table('config')->where('tenant_id', $request->tenant_details->id)->update([
            'reply_to_org_email' => $request['reply_to_org_email']
        ]);

        return response()->json([
            'message' => 'Setting saved successfully!'
        ]);
    }

    /**
     * [POST] Update SMS Line Configuration Toggle
     */
    public function updateSmsLineToggle(Request $request, $subdomain)
    {
        try {
            $field_name = $request->input('field_name');
            $config = SmsLineConfig::where('tenant_id', $this->cur_tenant_id)->first();
            if ($config) {
                $config->$field_name = $config->$field_name ? false : true;
                $config->save();
            }
            return response()->json([
                'status'  => true,
                'message' => "Setting saved successfully!"
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * [POST] Update SMS Line Configuration Toggle
     */
    public function saveSmsLineConfig(Request $request, $subdomain)
    {
        try {
            $phase_change_response = $request->input('phase_change_response');
            $phase_change_response_text = $request->input('phase_change_response_text');
            $review_request_response = $request->input('review_request_response');
            $review_request_response_text = $request->input('review_request_response_text');
            $mass_text_response = $request->input('mass_text_response');
            $mass_text_response_text = $request->input('mass_text_response_text');

            $config = SmsLineConfig::where('tenant_id', $this->cur_tenant_id)->first();
            if ($config) {
                $config->phase_change_response = ($phase_change_response == 'on' ? 1 : 0);
                $config->phase_change_response_text = $phase_change_response_text;
                $config->review_request_response = ($review_request_response == 'on' ? 1 : 0);
                $config->review_request_response_text = $review_request_response_text;
                $config->mass_text_response = ($mass_text_response == 'on' ? 1 : 0);
                $config->mass_text_response_text = $mass_text_response_text;
                $config->project_sms_number_order = $request->input('project_sms_number_order');
                $config->mailroom_order = $request->input('mailroom_order');
                $config->project_feed_note_order = $request->input('project_feed_note_order');
                $config->default_org_mailroom_number = $request->input('default_org_mailroom_number');
                $config->save();
            }

            return redirect()->route('settings', ['subdomain' => $subdomain])
                ->with('success', 'Setting saved successfully!');
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
