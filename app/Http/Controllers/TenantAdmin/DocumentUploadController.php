<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as Logging;
use App\Services\FilevineService;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;
use App\Models\ClientFileUploadConfiguration;
use App\Models\FvDocumentConfig;
use App\Models\FvClientUploadDocument;

class DocumentUploadController extends Controller
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
            $fv_service = new FilevineService($apiurl, "");

            // get project types
            $fv_project_type_list = $fv_service->getProjectTypeList();
            if ($fv_project_type_list != null) {
                $fv_project_type_list = json_decode($fv_project_type_list, true);
            } else {
                $fv_project_type_list = [];
            }

            $config_details = DB::table('config')->where('tenant_id', $tenant_id)->first();


            // Create/Update fv document config & subscription
            $eventId = "Document.Updated";
            $webhook_url = url('api/v1/webhook/document_updated');
            $subscription_exist = false;
            $all_subscriptions = json_decode($fv_service->getSubscriptionsList());
            foreach ($all_subscriptions as $single_subscription) {
                if (isset($single_subscription->eventIds) && $single_subscription->eventIds == [$eventId] && isset($single_subscription->endpoint) && $single_subscription->endpoint == $webhook_url) {
                    $subscription_exist = true;
                }
            }
            if (!$subscription_exist) {
                $subscription = json_decode($fv_service->createSubscription("Document Updated - Client Portal Document Uploads Configuration", $webhook_url, $eventId));
                $subscriptionId = isset($subscription->subscriptionId) ? $subscription->subscriptionId : '';
                $fv_document_config = FvDocumentConfig::where('tenant_id', $tenant_id)->first();
                if ($fv_document_config == null) {
                    FvDocumentConfig::create([
                        'tenant_id' => $tenant_id,
                        'fv_subscription_link' => $webhook_url,
                        'fv_subscription_event' => $eventId,
                        'fv_subscription_id' => $subscriptionId,
                        'hashtag' => 'clientportal'
                    ]);
                } else {
                    FvDocumentConfig::where('tenant_id', $tenant_id)->update([
                        'fv_subscription_link' => $webhook_url,
                        'fv_subscription_event' => $eventId,
                        'fv_subscription_id' => $subscriptionId
                    ]);
                }
            }
            $fv_document_config = FvDocumentConfig::where('tenant_id', $tenant_id)->first();
            $fv_client_upload_documents = FvClientUploadDocument::join('client_file_upload_configurations', 'fv_client_upload_documents.scheme_id', '=', 'client_file_upload_configurations.id')
                    ->where('client_file_upload_configurations.tenant_id', $tenant_id)
                    ->where('fv_client_upload_documents.tenant_id', $tenant_id)
                    ->select('fv_client_upload_documents.*', 'client_file_upload_configurations.choice as choice')
                    ->get();

            return $this->_loadContent('admin.pages.client_file_upload_config', [
                'project_types' => $fv_project_type_list,
                'tenant_id' => $this->cur_tenant_id,
                'tenant' => $Tenant,
                'config_actions' => ClientFileUploadConfiguration::$config_options,
                'config_details' => $config_details,
                'fv_document_config' => $fv_document_config,
                'fv_client_upload_documents' => $fv_client_upload_documents
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
     * [POST] Update client file uploads config
     */
    public function update_client_file_upload_config(Request $request)
    {
        $response = [
            'status'  => false,
            'message' => "Unable to update file upload settings at the moment!",
        ];
        try {
            $tenant_id = $this->cur_tenant_id;
            $data = $request->all();
            if ($request->type == "update-file-uploads") {
                $upload_config = DB::table('config')->where('tenant_id', $tenant_id)->update(['is_enable_file_uploads' => $data['is_enable_file_uploads']]);
                if ($upload_config) {
                    $response['status'] = true;
                    $response['message'] = "File upload settings updated successfully!";
                }
            } elseif ($request->type == "update-file-scheme") {
                $upload_config = \DB::table('config')->where('tenant_id', $tenant_id)->update(['is_defined_organization_scheme' => $data['is_defined_organization_scheme']]);
                if ($upload_config) {
                    $response['status'] = true;
                    $response['message'] = "File upload scheme settings updated successfully!";
                }
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json($response);
        }
    }

    /**
     * [POST] Add client file upload scheme choice
     */
    public function add_choice_client_file_upload_config(Request $request)
    {
        $response = [
            'status'  => false,
            'message' => "Unable to add requested choice at the moment!",
        ];
        try {
            $tenant_id = $this->cur_tenant_id;
            $data = $request->all();
            if (!isset($data['choice']) || (isset($data['choice']) && empty($data['choice']))) {
                $response['message'] = "Please enter choice name to add!";
            } else {
                $tenant = \DB::table('config')->where('tenant_id', $tenant_id)->first();
                $choice = ClientFileUploadConfiguration::create([
                    'tenant_id' => $tenant_id,
                    'choice' => $data['choice'],
                    'is_enable_file_uploads' => $tenant->is_enable_file_uploads,
                    'is_defined_organization_scheme' => $tenant->is_defined_organization_scheme
                ]);
                if (isset($choice->id)) {
                    $response['status'] = true;
                    $response['message'] = "Requested choice added successfully!";
                }
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json($response);
        }
    }

    /**
     * [POST] Get client file upload scheme choices
     */
    public function get_choices_client_file_upload_config(Request $request)
    {
        $response = [
            'options' => '',
            'choices' => ''
        ];
        $options_html = '<option value="">Choose Option</option>';
        $choices_html = '';
        try {
            $tenant_id = $this->cur_tenant_id;
            $data = $request->all();
            $choices = ClientFileUploadConfiguration::where(['tenant_id' => $tenant_id])->get();
            if (count($choices) > 0) {
                foreach ($choices as $choice) {
                    $choices_html .= '<p class="font-size-lg col-md-12">' . $choice->choice . ' <a href="javascript:;" class="delete-choice fa fa-times text-danger" data-target="' . $choice->id . '"></a></p>';
                    $options_html .= '<option value="' . $choice->id . '">' . $choice->choice . '</option>';
                }
            }
            $response['options'] = $options_html;
            $response['choices'] = $choices_html;
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json($response);
        }
    }

    /**
     * [POST] Delete client file upload scheme choices
     */
    public function delete_choice_client_file_upload_config(Request $request)
    {
        $response = [
            'status' => false,
            'message' => 'Unable to delete requested choice at the moment'
        ];
        try {
            $tenant_id = $this->cur_tenant_id;
            $data = $request->all();
            $choice = ClientFileUploadConfiguration::where(['tenant_id' => $tenant_id, 'id' => $data['choice']])->delete();
            if ($choice) {
                $response['status'] = true;
                $response['message'] = "Requested choice deleted successfully!";
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json($response);
        }
    }

    /**
     * [GET] Get Project Section
     */
    public function get_project_section_client_file_upload_config($subdomain, $type_id, $handle_id)
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
                $sections = $sections->where("isCollection", ($handle_id == 2 ? false : true));
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
    public function get_project_section_field_client_file_upload_config($subdomain, $type_id, $section_id)
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
                $customFields = $customFields->whereIn("customFieldType", ["Doc", "DocList"]);
                foreach ($customFields as $key => $field) {
                    $html .= '<option value="' . $field['fieldSelector'] . '*' . $field['customFieldType'] . '">' . $field['name'] . ' (' . $field['customFieldType'] . ')' . '</option>';
                }
            }
            $response['html'] = $html;
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json($response);
        }
    }

    /**
     * [POST] Add file upload scheme with choice
     */
    public function add_scheme_client_file_upload_config(Request $request)
    {
        $response = [
            'status'  => false,
            'message' => "Unable to save requested scheme at the moment!",
        ];
        try {
            $tenant_id = $this->cur_tenant_id;
            $data = $request->except("_token");

            $validator = \Validator::make(
                $data,
                ['choice_id' => 'required', 'handle_files_action' => 'required'],
                ['choice_id.required' => 'Please select a choice', 'handle_files_action.required' => 'Please select a file action']
            );
            // check if action is different than root folder
            if ($data['handle_files_action'] > 1) {
                $validator = \Validator::make(
                    $data,
                    [
                        'choice_id' => 'required',
                        'project_type_id' => 'required',
                        'handle_files_action' => 'required',
                        'target_section_id' => 'required',
                        'target_field_id' => 'required'
                    ],
                    [
                        'choice_id.required' => 'Please select a choice',
                        'project_type_id.required' => 'Please select a project type',
                        'handle_files_action.required' => 'Please select a file action',
                        'target_section_id.required' => 'Please select a section',
                        'target_field_id.required' => 'Please select a field'
                    ]
                );
            }

            if ($validator->passes()) {
                $tenant = Tenant::find($tenant_id);
                // data to update
                $scheme_data = [
                    'handle_files_action' => $data['handle_files_action'],
                    'project_type_id' => $data['handle_files_action'] > 1 ? $data['project_type_id'] : null,
                    'project_type_name' => $data['handle_files_action'] > 1 ? $data['project_type_name'] : null,
                    'target_section_id' => $data['handle_files_action'] > 1 ? $data['target_section_id'] : null,
                    'target_section_name' => $data['handle_files_action'] > 1 ? $data['target_section_name'] : null,
                    'target_field_id' => $data['handle_files_action'] > 1 ? explode("*", $data['target_field_id'])[0] : null,
                    'target_field_type' => $data['handle_files_action'] > 1 ? explode("*", $data['target_field_id'])[1] : null,
                    'target_field_name' => $data['handle_files_action'] > 1 ? $data['target_field_name'] : null,
                    'hashtag' => $data['hashtag'],
                    'is_enable_file_uploads' => $tenant->is_enable_file_uploads,
                    'is_defined_organization_scheme' => $tenant->is_defined_organization_scheme
                ];
                $choice = ClientFileUploadConfiguration::where(['tenant_id' => $tenant_id, 'id' => $data['choice_id']])->update($scheme_data);

                if ($choice) {
                    $response['status'] = true;
                    $response['message'] = "Requested scheme saved successfully!";
                }
            } else {
                $errors = $validator->errors()->getMessages();
                if (count($errors) > 0) {
                    $error_string = "";
                    foreach ($errors as $key => $value) {
                        foreach ($value as $key1 => $value1) {
                            $error_string .= "<p>" . $value1 . "</p>";
                        }
                    }
                    $response['message'] = $error_string;
                }
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json($response);
        }
    }

    /**
     * [POST] Get client mapped upload scheme choices
     */
    public function get_mapped_choices_client_file_upload_config(Request $request)
    {
        $response = '<table class="w-100 table table-bordered">';
        $response .= '<tr><th>Dropdown Choice</th><th>Handler</th><th>Scheme</th><th>Actions</th></tr>';
        try {
            $config_types = ClientFileUploadConfiguration::$config_options;
            $tenant_id = $this->cur_tenant_id;
            $schemes = ClientFileUploadConfiguration::where(['tenant_id' => $tenant_id])->whereNotNull('handle_files_action')->get();
            if (count($schemes) > 0) {
                foreach ($schemes as $scheme) {
                    $hashtag = $scheme->hashtag;
                    /* if(!empty($scheme->hashtag)) {
                        $number_sign = substr($scheme->hashtag, 0, 1);
                        if($number_sign == '#') {
                            $hashtag = $scheme->hashtag;
                            $hashtag = str_replace('#', '', $hashtag);
                        }
                    } */
                    $response .= '<tr>';
                    $response .= '<td>' . $scheme->choice . '</td>';
                    $response .= '<td>' . $config_types[$scheme->handle_files_action] . '</td>';
                    $response .= '<td>' . ($scheme->handle_files_action > 1 ? $scheme->project_type_name . ' - ' . $scheme->target_section_name . ' - ' . $scheme->target_field_name . ' - <span class="text-danger">' . $hashtag . '<span>' : '<span class="text-danger">' . $hashtag . '</span>') . '</td>';
                    $response .= '<td><button class="btn btn-danger btn-icon delete-scheme" data-target="' . $scheme->id . '"><i class="fa fa-trash"></i></button></td>';
                    $response .= '</tr>';
                }
            } else {
                $response .= '<tr><td colspan="4" class="text-center">No mapped schemes found</td></tr>';
            }
            $response .= '</table>';
            echo $response;
            return;
        } catch (\Throwable $th) {
            echo $response;
            return;
        }
    }

    /**
     * [POST] Delete client file upload mapped scheme
     */
    public function delete_mapped_choice_client_file_upload_config(Request $request)
    {
        $response = [
            'status' => false,
            'message' => 'Unable to delete requested mapped scheme at the moment'
        ];
        try {
            $tenant_id = $this->cur_tenant_id;
            $data = $request->all();
            $scheme_data = [
                'handle_files_action' => null,
                'project_type_id' => null,
                'project_type_name' => null,
                'target_section_id' => null,
                'target_section_name' => null,
                'target_field_id' => null,
                'target_field_name' => null,
                'hashtag' => null
            ];
            $scheme = ClientFileUploadConfiguration::where(['tenant_id' => $tenant_id, 'id' => $data['scheme']])->update($scheme_data);
            if ($scheme) {
                $response['status'] = true;
                $response['message'] = "Requested mapped scheme deleted successfully!";
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json($response);
        }
    }

    /**
     * [POST] Get client file upload scheme choices
     */
    public function get_choice_detail_client_file_upload_config(Request $request)
    {
        $response = [
            'status' => false,
            'sections' => '',
            'fields' => '',
            'scheme' => ''
        ];
        try {
            $tenant_id = $this->cur_tenant_id;
            $data = $request->all();
            $scheme = ClientFileUploadConfiguration::where(['tenant_id' => $tenant_id, 'id' => $data['scheme']])->whereNotNull('handle_files_action')->select('id', 'project_type_id', 'project_type_name', 'target_section_id', 'target_section_name', 'target_field_id', 'target_field_name', 'target_field_type', 'handle_files_action', 'hashtag')->first();
            if (isset($scheme->id)) {
                $response['status'] = true;
                $response['scheme'] = $scheme;
                // if filevine engaged
                if ($scheme->handle_files_action > 1) {
                    $response['sections'] = json_decode($this->get_project_section_client_file_upload_config("", $scheme->project_type_id, $scheme->handle_files_action)->getContent(), true)['html'];
                    $response['fields'] = json_decode($this->get_project_section_field_client_file_upload_config("", $scheme->project_type_id, $scheme->target_section_id)->getContent(), true)['html'];
                }
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json($response);
        }
    }


    /**
     * [POST] Update FV document config hashtag
     */
    public function updateConfigHashtag(Request $request)
    {
        $response = [
            'status'  => false,
            'message' => "Unable to process your request at the moment!",
        ];
        try {
            $tenant_id = $this->cur_tenant_id;
            $hashtag = $request->hashtag;

            if (empty($hashtag)) {
                $response['message'] = "Hashtag can't be empty!";
                return response()->json($response);
            }

            FvDocumentConfig::where('tenant_id', $tenant_id)->update([
                'hashtag' => $hashtag,
            ]);
            $response['status'] = true;
            $response['message'] = "Setting saved successfully!";
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json($response);
        }
    }


    /**
     * [GET] Download uploaded file from new link
     */
    public function downloadUploadedFile(Request $request)
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
            $fv_client_upload_document = FvClientUploadDocument::find($id);
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
}
