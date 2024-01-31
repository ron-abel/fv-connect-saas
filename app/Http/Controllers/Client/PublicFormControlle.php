<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log as Logging;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;

use App\Services\SendGridServices;
use App\Services\FilevineService;
use App\Services\NotificationHandlerService;

use App\Models\TenantForm;
use App\Models\TenantFormResponse;
use App\Models\Tenant;
use App\Models\TenantFormMapping;
use App\Models\FvClients;
use App\Models\TenantNotificationConfig;
use App\Models\TenantPublicFormSubmitLog;

class PublicFormControlle extends Controller
{

    private $sendGridServices;
    public $cur_tenant_id;
    public function __construct(SendGridServices $sendGridServices)
    {
        $this->sendGridServices = $sendGridServices;
        Controller::setSubDomainName();
        $this->cur_tenant_id = session()->get('tenant_id');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * show form for submiting response
     */
    public function showForm(Request $request, $subdomain, $form_id)
    {

        try {
            if ($this->cur_tenant_id) {
                // check if reset request
                if ($request->has('refresh')) {
                    // remove ip record and cookie from browser
                    $this->removeIfFormSubmittedAlready($this->get_client_ip(), $form_id);
                    return redirect()->to($request->url());
                }
                $is_submitted = $this->checkIfFormSubmittedAlready($this->get_client_ip(), $form_id);
                // No Effect if a form is assign as per VINE-487
                //$form_assign = TenantFormResponse::where('tenant_form_id', $form_id)->where('fv_project_id', '>', 0)->first();

                $form = TenantForm::where(['tenant_id' => $this->cur_tenant_id, 'id' => $form_id, 'is_active' => 1, 'deleted_at' => null])->latest()->first();
                $config_details = DB::table('config')->where('tenant_id', $this->cur_tenant_id)->first();

                return $this->_loadContent(
                    'client.pages.form_public',
                    [
                        'form' => $form,
                        //'form_assign' => $form_assign ? true : false,
                        'form_assign' => false,
                        'config_details' => $config_details,
                        'is_submitted' => $is_submitted,
                        'current_url' => $request->url()
                    ]
                );
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
     * Function to get the client IP address
     */
    public function get_client_ip()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if (isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    /**
     * save form for submiting response
     */
    public function saveForm(Request $request)
    {
        try {
            $response_id = 0;
            $main_data = $request->all();
            // $data = json_decode($request->getContent());
            $data = json_decode($main_data['content']);
            $documents = (isset($main_data['documents']) && count($main_data['documents']) > 0) ? $main_data['documents'] : [];

            if ($data->form_id && $this->cur_tenant_id) {
                // check if already submitted the form
                $is_submitted = $this->checkIfFormSubmittedAlready($this->get_client_ip(), $data->form_id);
                if ($is_submitted['success']) {
                    return response()->json([
                        'success' => false,
                        'is_submitted' => $is_submitted,
                        'message' => 'You submitted a form response on ' . $is_submitted['timestamp'] . '.',
                        'message1' => 'You submitted a form response on ' . $is_submitted['timestamp'] . '. <a href="' . url('share/views/open/form/' . $data->form_id) . '?refresh=1">Click here to submit another.</a>'
                    ], 200);
                }

                $res = TenantFormResponse::create([
                    'tenant_form_id' => $data->form_id,
                    'fv_client_id' => 0,
                    'fv_project_id' => 0,
                    'form_response_values_json' => json_encode($data->response)
                ]);

                // save submit log and set cookie
                TenantPublicFormSubmitLog::create([
                    'form_id' => $data->form_id,
                    'user_ip' => $this->get_client_ip()
                ]);
                cookie()->queue(cookie()->forever('PUB-FM-' . $data->form_id, 'DONE'));

                $response_id = $res->id;

                $form_data = $data->response;
                $first_name = "";
                $last_name = "";
                $email = "";
                $phone = "";
                $submitted_project_name = "";

                for ($i = 0; $i < count($form_data); $i++) {
                    $label = strtolower($form_data[$i]->label);
                    $label = preg_replace('/\s+/', '', $label);
                    if (strpos($label, 'firstname') !== false) {
                        $first_name = $form_data[$i]->value;
                    } else if (strpos($label, 'lastname') !== false) {
                        $last_name = $form_data[$i]->value;
                    } else if (strpos($label, 'email') !== false) {
                        $email = $form_data[$i]->value;
                    } else if (strpos($label, 'phone') !== false) {
                        $phone = $form_data[$i]->value;
                    } else if (strpos($label, 'projectname') !== false) {
                        $submitted_project_name = $form_data[$i]->value;
                    }
                }

                $Tenant = Tenant::find($this->cur_tenant_id);
                $apiurl = config('services.fv.default_api_base_url');
                if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                    $apiurl = $Tenant->fv_api_base_url;
                }

                $project = "";
                $tenant_form = TenantForm::find($data->form_id);
                if ($tenant_form && $tenant_form->is_public_form && $tenant_form->create_fv_project && !empty($tenant_form->fv_project_type_id)) {
                    if (!empty($first_name) && !empty($last_name) && !empty($email) && !empty($phone)) {

                        $filevine_api = new FilevineService($apiurl, "");
                        $client_id = 0;

                        // Check a contact is already exist or not by full name
                        $full_name = $first_name . " " . $last_name;
                        $contact = json_decode($filevine_api->getContactByFullName($full_name), TRUE);
                        if ($contact['count'] != 0 && isset($contact['items'][0]['personId']['native'])) {
                            $client_id = $contact['items'][0]['personId']['native'];
                            TenantFormResponse::where('id', $response_id)->update([
                                'fv_client_id' => $client_id,
                                'fv_client_name' => $contact['items'][0]['fullName'],
                            ]);
                        }


                        if (empty($client_id)) {
                            $contact_params = [
                                'firstName' => $first_name,
                                'lastName'  => $last_name,
                                'personTypes' => [
                                    'Client'
                                ],
                                'phones' => [[
                                    'number' => $phone,
                                    'rawNumber' => $phone
                                ]],
                                'emails' => [[
                                    'address' => $email
                                ]],
                            ];

                            $contact = json_decode($filevine_api->createContact($contact_params));
                            if (!empty($contact)) {
                                $client_id = $contact->personId->native;
                                TenantFormResponse::where('id', $response_id)->update([
                                    'fv_client_id' => $client_id,
                                    'fv_client_name' => isset($contact->fullName) ? $contact->fullName : '',
                                ]);

                                $fv_client = FvClients::create([
                                    'tenant_id' => $this->cur_tenant_id,
                                    'fv_client_id' => $contact->personId->native,
                                    'fv_client_name' => $contact->fullName,
                                    'fv_client_address' => isset($contact->addresses[0]->fullAddress) ? $contact->addresses[0]->fullAddress : '',
                                    'fv_client_zip' => isset($contact->addresses[0]->postalCode) ? $contact->addresses[0]->postalCode : ''
                                ]);
                            }
                        }

                        if ($client_id) {
                            $project_name_prefix = $first_name . " " . $last_name;
                            if ($tenant_form->assign_project_name_as == "Map a Field Value") {
                                $project_name_prefix = !empty($submitted_project_name) ? $submitted_project_name : $project_name_prefix;
                            }

                            $fv_project_type_id = $tenant_form->fv_project_type_id;
                            $project_params = [
                                'projectName' => $project_name_prefix,
                                'projectTypeId' => [
                                    'native' => $fv_project_type_id
                                ],
                                'clientId' => [
                                    'native' => $client_id
                                ]
                            ];
                            $project = json_decode($filevine_api->createProject($project_params));
                        }
                    }
                } else if ($tenant_form && $tenant_form->is_public_form && !empty($tenant_form->fv_project_type_id) && !empty($tenant_form->fv_project_id)) {
                    $filevine_api = new FilevineService($apiurl, "");
                    $project = json_decode($filevine_api->getProjectsById($tenant_form->fv_project_id));
                } else if ($tenant_form && $tenant_form->is_public_form && $tenant_form->sync_existing_fv_project) {
                    if (!empty($first_name) && !empty($last_name)) {
                        $filevine_api = new FilevineService($apiurl, "");
                        $client_object = json_decode($filevine_api->getContactByClientInfo($first_name, $last_name));
                        if (isset($client_object->items) && count($client_object->items)) {
                            $client_object = collect($client_object->items)->first();
                            if ($client_object) {
                                $contact_id = isset($client_object->personId) ? $client_object->personId->native : null;
                                if (!empty($contact_id)) {
                                    $client_projects = json_decode($filevine_api->getProjectsByContactId($contact_id, 1, 0));
                                    if (isset($client_projects->items) && count($client_projects->items)) {
                                        $project = collect($client_projects->items)->first();
                                        $project = $project->project;
                                    }
                                }
                            }
                        }
                    }
                }

                if (!empty($project)) {
                    $project_id = $project->projectId->native;
                    TenantFormResponse::where('id', $response_id)->update([
                        'fv_project_id' => $project_id,
                        'fv_project_name' => isset($project->projectName) ? $project->projectName : '',
                        'fv_client_id' => isset($project->clientId->native) ? $project->clientId->native : 0,
                        'fv_client_name' => isset($project->clientName) ? $project->clientName : '',
                    ]);

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

                    foreach ($form_data as $fresponse) {
                        $fname = trim($fresponse->name);
                        $mapping = TenantFormMapping::where('form_id', $data->form_id)->where('form_mapping_enable', true)->where('form_item_name', $fname)->first();
                        if (isset($mapping->section_type) && $mapping->section_type == 'collection') {
                            if (count($count_form_item_collections)) {
                                if ($count_total_form_item_collection == $count_form_item_collections[$prev_mapping_collection_item_index]) {
                                    if (count($dataObject)) {
                                        // check if there is any document for the object
                                        if (count($documents) > 0) {
                                            $document_mappings = TenantFormMapping::where('form_id', $data->form_id)->where('form_mapping_enable', true)
                                                ->where('collection_item_index', $prev_mapping_collection_item_index)
                                                ->where('form_item_type', 'file')
                                                ->get();
                                            if (count($document_mappings) > 0) {
                                                foreach ($document_mappings as $doc_map) {
                                                    // get collection item index
                                                    if (isset($document_array_indexes[$doc_map->form_item_name])) {
                                                        $document_array_indexes[$doc_map->form_item_name][] = end($document_array_indexes[$doc_map->form_item_name]) + 1;
                                                    } else {
                                                        $document_array_indexes[$doc_map->form_item_name][] = 0;
                                                    }

                                                    // get index to check for docs
                                                    $document_array_index = end($document_array_indexes[$doc_map->form_item_name]);

                                                    // get documents
                                                    if (isset($documents[$doc_map->form_item_name]) && isset($documents[$doc_map->form_item_name][$document_array_index])) {
                                                        $dataObject[$doc_map->fv_field_id] = $this->uploadDocumentsToFV($filevine_api, $project_id, $documents[$doc_map->form_item_name][$document_array_index]);
                                                    }
                                                }
                                            }
                                        }

                                        $field_params["dataObject"] = $dataObject;
                                        Logging::warning(json_encode($fv_section_id));
                                        Logging::warning(json_encode($field_params));
                                        $filevine_api->createCollectionItem($project_id, $fv_section_id, $field_params);
                                    }
                                    $dataObject = [];
                                    $count_total_form_item_collection = 0;
                                }
                                if (!empty($mapping->fv_section_id)) {
                                    $fv_section_id = $mapping->fv_section_id;
                                }
                                if (!empty($fresponse->value) && !empty($mapping->fv_field_id)) {
                                    // check if its a person link
                                    if (strpos($mapping->fv_field_name, "PersonLink") !== false) {
                                        $dataObject[$mapping->fv_field_id] = $this->filterFieldValue($this->getMatchFirstContactId($filevine_api, $fresponse->value));
                                    } elseif (strpos($mapping->fv_field_name, "Deadline") !== false) {
                                        $dataObject[$mapping->fv_field_id] = ['dateValue' => (new \DateTime($fresponse->value))->format('Y-m-d') . 'T00:00:00Z'];
                                    } elseif (strpos($mapping->fv_field_name, "MultiSelectList") !== false) {
                                        $dataObject[$mapping->fv_field_id] = array_map('trim', $fresponse->value);
                                    } else {
                                        $dataObject[$mapping->fv_field_id] = $this->filterFieldValue($fresponse->value);
                                    }
                                }
                                $count_total_form_item_collection++;
                                $prev_mapping_collection_item_index = $mapping->collection_item_index;
                            }
                        } else if ($mapping) {
                            $target_section_id = $mapping->fv_section_id;
                            // check if its a person link
                            if (strpos($mapping->fv_field_name, "PersonLink") !== false) {
                                $params[$mapping->fv_field_id] = $this->filterFieldValue($this->getMatchFirstContactId($filevine_api, $fresponse->value));
                            } elseif (strpos($mapping->fv_field_name, "Deadline") !== false) {
                                $params[$mapping->fv_field_id] = ['dateValue' => (new \DateTime($fresponse->value))->format('Y-m-d') . 'T00:00:00Z'];
                            } elseif (strpos($mapping->fv_field_name, "MultiSelectList") !== false) {
                                $params[$mapping->fv_field_id] = array_map('trim', $fresponse->value);
                            } else {
                                $params[$mapping->fv_field_id] = $this->filterFieldValue($fresponse->value);
                            }
                            if (!empty($target_section_id)) {
                                $res = $filevine_api->updateStaticForm($project_id, $target_section_id, $params);
                            }
                            $params = [];
                        }
                    }

                    if (count($dataObject)) {
                        // check if there is any document for the object
                        if (count($documents) > 0) {
                            $document_mappings = TenantFormMapping::where('form_id', $data->form_id)->where('form_mapping_enable', true)
                                ->where('collection_item_index', $prev_mapping_collection_item_index)
                                ->where('form_item_type', 'file')
                                ->get();
                            if (count($document_mappings) > 0) {
                                foreach ($document_mappings as $doc_map) {
                                    // get collection item index
                                    if (isset($document_array_indexes[$doc_map->form_item_name])) {
                                        $document_array_indexes[$doc_map->form_item_name][] = end($document_array_indexes[$doc_map->form_item_name]) + 1;
                                    } else {
                                        $document_array_indexes[$doc_map->form_item_name][] = 0;
                                    }

                                    // get index to check for docs
                                    $document_array_index = end($document_array_indexes[$doc_map->form_item_name]);

                                    // get documents
                                    if (isset($documents[$doc_map->form_item_name]) && isset($documents[$doc_map->form_item_name][$document_array_index])) {
                                        $dataObject[$doc_map->fv_field_id] = $this->uploadDocumentsToFV($filevine_api, $project_id, $documents[$doc_map->form_item_name][$document_array_index]);
                                    }
                                }
                            }
                        }

                        $field_params["dataObject"] = $dataObject;
                        if (!empty($fv_section_id)) {
                            Logging::warning(json_encode($fv_section_id));
                            Logging::warning(json_encode($field_params));
                            $filevine_api->createCollectionItem($project_id, $fv_section_id, $field_params);
                        }
                    }

                    // check if there is any document for the object
                    if (count($documents) > 0) {
                        foreach ($documents as $field_name => $docs) {
                            $document_mapping = TenantFormMapping::where('form_id', $data->form_id)->where('form_mapping_enable', true)
                                ->where('form_item_name', $field_name)
                                ->where('form_item_type', 'file')
                                ->orderBy('collection_item_index', 'asc')
                                ->first();
                            if (isset($document_mapping)) {
                                if ($document_mapping->section_type == 'collection') {
                                    $fv_section_id = $document_mapping->fv_section_id;
                                    foreach ($docs as $key => $value) {
                                        $dataObject = [];
                                        $field_params = [];
                                        if (
                                            !isset($document_array_indexes[$document_mapping->form_item_name]) ||
                                            (isset($document_array_indexes[$document_mapping->form_item_name]) &&
                                                !in_array($key, $document_array_indexes[$document_mapping->form_item_name]))
                                        ) {
                                            $dataObject[$document_mapping->fv_field_id] = $this->uploadDocumentsToFV($filevine_api, $project_id, $value);
                                            if (!empty($fv_section_id)) {
                                                $field_params["dataObject"] = $dataObject;
                                                $filevine_api->createCollectionItem($project_id, $fv_section_id, $field_params);
                                            }
                                        }
                                    }
                                } elseif ($document_mapping->section_type == 'static') {
                                    $target_section_id = $document_mapping->fv_section_id;
                                    $params = [];
                                    // get documents
                                    if (isset($documents[$document_mapping->form_item_name]) && isset($documents[$document_mapping->form_item_name][0])) {
                                        $params[$document_mapping->fv_field_id] = $this->uploadDocumentsToFV($filevine_api, $project_id, $documents[$document_mapping->form_item_name][0]);
                                        if (!empty($target_section_id)) {
                                            $filevine_api->updateStaticForm($project_id, $target_section_id, $params);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // Send Admin Notification (Email and Post to Filevine)
                    $notification_config = TenantNotificationConfig::where('tenant_id', $this->cur_tenant_id)->where('event_short_code', TenantNotificationConfig::FormSubmission)->first();
                    if ($notification_config) {
                        $form_details = TenantForm::find($data->form_id);
                        $params = [
                            'project_id' => $project_id,
                            'tenant_id' => $this->cur_tenant_id,
                            'client_id' => isset($project->clientId->native) ? $project->clientId->native : 0,
                            'client_name' => isset($project->clientName) ? $project->clientName : '',
                            'note_body' => 'A public form with name ' . $form_details->form_name . ' submitted.',
                            'action_name' => 'Form Submission',
                            'project_name' => isset($project->projectName) ? $project->projectName : '',
                            'tenant_form_response_id' => $response_id,
                        ];
                        NotificationHandlerService::callActionService($notification_config, $params);
                    }

                    // Call automated workflow webhook trigger function
                    app('App\Http\Controllers\API\AutomatedWorkflowWebhookController')->formSubmitted(['Object' => 'FormSubmitted', 'Event' => '', 'ProjectId' => $project_id, 'fv_client_id' => (isset($project->clientId->native) ? $project->clientId->native : 0), 'tenant_id' => $this->cur_tenant_id, 'form_id' => $data->form_id]);
                }

                return response()->json([
                    'success' => true,
                    'message' => !empty($tenant_form->success_message) ? $tenant_form->success_message : '<div class="alert alert-success" role="alert">Form submission received. Thank you.</div>'
                ], 200);
            }
        } catch (Exception $ex) {
            Logging::warning(json_encode($ex));
            if ($response_id) {
                TenantFormResponse::where('id', $response_id)->update([
                    'error_log' => $ex->getMessage()
                ]);
            }
            return $ex->getMessage();
        }
    }

    private function checkIfFormSubmittedAlready($ip, $form_id)
    {
        $response = array('success' => false, 'timestamp' => '');
        // check in database if ip record found
        $userip_exist = TenantPublicFormSubmitLog::where(['form_id' => $form_id, 'user_ip' => $ip])->first();
        // check cookie first
        $cookie_exist = request()->cookie('PUB-FM-' . $form_id);
        if (!empty($cookie_exist)) {
            $response['success'] = true;
        }

        if (isset($userip_exist->id)) {
            $response['success'] = true;
            $response['timestamp'] = $userip_exist->created_at->format('Y/m/d H:i A');
        }
        return $response;
    }

    private function removeIfFormSubmittedAlready($ip, $form_id)
    {
        $response = array('success' => false, 'timestamp' => '');
        // check in database if ip record found
        TenantPublicFormSubmitLog::where(['form_id' => $form_id, 'user_ip' => $ip])->delete();
        // clear cookie if exists
        $cookie_exist = request()->cookie('PUB-FM-' . $form_id);
        if (!empty($cookie_exist)) {
            cookie()->queue(cookie()->forget('PUB-FM-' . $form_id));
        }
        return true;
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

    public function getMatchFirstContactId($fv_service, $full_name)
    {
        try {
            $contact_id = 0;
            if (!empty($full_name)) {
                $contact = json_decode($fv_service->getContactByFullName(trim($full_name)), TRUE);
                if (isset($contact['count']) && $contact['count'] != 0) {
                    $contact_id = isset($contact['items'][0]['personId']['native']) ? $contact['items'][0]['personId']['native'] : 0;
                }
            }
            return $contact_id;
        } catch (\Exception $ex) {
        }
    }

    /**
     *  [POST] Upload files to project root folder
     */
    private function uploadDocumentsToFV($filevine_api, $project_id, $files)
    {
        $field_details = [];
        try {
            if (count($files) > 0) {
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
        } catch (\Exception $ex) {
        }
        return $field_details;
    }
}
