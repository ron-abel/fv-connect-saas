<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log as Logging;
use Exception;
use App\Services\FilevineService;

use App\Models\Tenant;
use App\Models\FvDocumentConfig;
use App\Models\FvSharedDocument;

class DocumentWebhookController extends Controller
{
    public function __construct()
    {
        Controller::setSubDomainName();
    }

    /**
     * [POST] Document.Updated Webhook
     */
    public function documentUpdated($domain, Request $request)
    {
        Logging::warning(json_encode($request->all()));
        try {
            $tenant_details = Tenant::where('tenant_name', $domain)->first();
            if ($tenant_details == null) {
                return response()->json(['status'  => true, 'code' => 200, 'message' => 'Tenant details not found!']);
            }
            $tenant_id = $tenant_details->id;
            $fv_document_config = FvDocumentConfig::where('tenant_id', $tenant_id)->first();
            $config_hashtag = isset($fv_document_config->hashtag) ? $fv_document_config->hashtag : "";

            if (!empty($config_hashtag)) {
                $request_action = isset($request->Other['Action']) ? $request->Other['Action'] : "";
                if ($request_action == "ReplacedHashtags") {
                    $doc_id = isset($request->ObjectId['DocId']) ? $request->ObjectId['DocId'] : 0;
                    if ($doc_id) {
                        $Tenant = Tenant::find($tenant_id);
                        $apiurl = config('services.fv.default_api_base_url');
                        if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                            $apiurl = $Tenant->fv_api_base_url;
                        }
                        $fv_service = new FilevineService($apiurl, "");
                        $document_details = json_decode($fv_service->getDocument($doc_id));
                        $hashtags = isset($document_details->hashtags) ? $document_details->hashtags : [];
                        $save_document = false;
                        if (count($hashtags)) {
                            $config_hashtag = str_replace("#", "", trim($config_hashtag));
                            foreach ($hashtags as $hashtag) {
                                $hashtag = str_replace("#", "", trim($hashtag));
                                if ($hashtag == $config_hashtag) {
                                    $save_document = true;
                                }
                            }
                            if ($save_document) {
                                $fv_uploader_id = isset($document_details->uploaderId->native) ? $document_details->uploaderId->native : 0;
                                $user = json_decode($fv_service->getUserById($fv_uploader_id));
                                $fv_uploader_name = "";
                                if ($user && isset($user->user)) {
                                    $fv_uploader_name = $user->user->firstName . " " . $user->user->lastName;
                                }
                                $project_id = isset($document_details->projectId->native) ? $document_details->projectId->native : 0;
                                FvSharedDocument::insert([
                                    'tenant_id' => $tenant_id,
                                    'fv_document_id' => $doc_id,
                                    'fv_filename' => isset($document_details->filename) ? $document_details->filename : "",
                                    'doc_size' => isset($document_details->size) ? $document_details->size : "",
                                    'fv_folder_id' => isset($document_details->folderId->native) ? $document_details->folderId->native : 0,
                                    'fv_folder_name' => isset($document_details->folderName) ? $document_details->folderName : null,
                                    'fv_project_id' => $project_id,
                                    'fv_uploader_id' => $fv_uploader_id,
                                    'fv_uploader_name' => $fv_uploader_name,
                                    'fv_upload_date' => isset($document_details->uploadDate) ? $document_details->uploadDate : null,
                                    'hash_tag' => implode(",", $hashtags),
                                    'created_at' => date('Y-m-d H:i:s')
                                ]);

                                // Call automated workflow webhook trigger function
                                app('App\Http\Controllers\API\AutomatedWorkflowWebhookController')->documentShared(['Object' => 'DocumentShared', 'Event' => '', 'ProjectId' => $project_id, 'tenant_id' => $tenant_id]);
                            }
                        }
                    }
                }
            }

            return response()->json(['status'  => true, 'code' => 200, 'message' => 'Request Successfully Completed!']);
        } catch (Exception $e) {
            Logging::warning(json_encode($e));
            return response()->json(['status'  => true, 'code' => 500, 'message' => $e->getMessage()]);
        }
    }
}
