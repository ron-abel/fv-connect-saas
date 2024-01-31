<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Models\Blacklist;
use Illuminate\Http\Request;
use App\Services\FilevineService;
use App\Models\Tenant;

class BlackListController extends Controller
{
    public $cur_tenant_id;

    public function __construct()
    {
        Controller::setSubDomainName();
        $this->cur_tenant_id = session()->get('tenant_id');
    }

    /**
     * [GET] Client blacklisting Page for Admin
     */
    public function client_blacklisting()
    {
        $tenant_id = $this->cur_tenant_id;
        $blacklists = Blacklist::where('tenant_id', $tenant_id)->get();
        return $this->_loadContent('admin.pages.client_blacklist', compact('blacklists'));
    }

    /**
     * [POST] Get client and project data
     */
    public function client_contacts()
    {
        try {

            $data = $this->validate(request(), [
                'name' => 'required',
                'search_filter' => '',
                'offset' => ''
            ]);

            $search_filter = $data['search_filter'];
            $search_value = $data['name'];
            $tenant_id = $this->cur_tenant_id;
            $Tenant = Tenant::find($tenant_id);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $filevine_api = new FilevineService($apiurl, "");

            $clients = [];
            $offset = (isset($data['offset']) && !empty($data['offset'])) ? $data['offset'] : 0;
            $limit = 1000;
            $is_more = false;


            // Check Search Filter Type
            if ($search_filter == "Client") {
                // do {
                $projects_object = json_decode($filevine_api->getProjectsList($limit, $offset), TRUE);
                $next_link = trim($projects_object['links']['next']);
                if (isset($projects_object['items'])) {
                    foreach ($projects_object['items'] as $project) {
                        if (stripos($project['clientName'], $search_value) !== false) {
                            $id = $project['clientId']['native'];
                            $clients[] = [
                                'client_info' => [
                                    'id' => $id,
                                    'full_name' => $project['clientName'],
                                ],
                                'projects' => []
                            ];
                        }
                    }
                }
                $offset += $limit;
                if ($next_link) {
                    $is_more = true;
                }
                // } while ($next_link);
            } else if ($search_filter == "Project") {
                $project_list = [];
                // do {
                $projects_object = json_decode($filevine_api->getProjectsList($limit, $offset), TRUE);
                $next_link = trim($projects_object['links']['next']);
                if (isset($projects_object['items'])) {
                    foreach ($projects_object['items'] as $project) {
                        if (stripos($project['projectOrClientName'], $search_value) !== false) {
                            $project_list[] = [
                                'id' => $project['projectId']['native'],
                                'client_id' => $project['clientId']['native'],
                                'full_name' => $project['projectName'] . " (" . $project['clientName'] . ")"
                            ];
                        }
                    }
                }
                $offset += $limit;
                if ($next_link) {
                    $is_more = true;
                }
                // } while ($next_link);

                $clients[] = [
                    'client_info' => [],
                    'projects' => $project_list
                ];
            } else {
                $projects_by_client = [];
                // do {
                $projects_object = json_decode($filevine_api->getProjectsList($limit, $offset), TRUE);
                $next_link = trim($projects_object['links']['next']);
                if (isset($projects_object['items'])) {
                    foreach ($projects_object['items'] as $project) {
                        if (stripos($project['clientName'], $search_value) !== false || stripos($project['projectOrClientName'], $search_value) !== false) {
                            $projects_by_client[$project['clientId']['native']][] = [
                                'id' => $project['projectId']['native'],
                                'client_id' => $project['clientId']['native'],
                                'full_name' => $project['projectName']
                            ];
                            $client_info[] = [
                                'id' => $project['clientId']['native'],
                                'full_name' => $project['clientName'],
                            ];
                        }
                    }
                }
                $offset += $limit;
                if ($next_link) {
                    $is_more = true;
                }
                // } while ($next_link);

                foreach ($client_info as $client) {
                    $id = $client['id'];
                    $clients[] = [
                        'client_info' => $client,
                        'projects' => array_key_exists($id, $projects_by_client) ? $projects_by_client[$id] : [],
                    ];
                }
            }

            return response()->json([
                'status' => true,
                'data' => $clients,
                'offset' => $offset,
                'is_more' => $is_more
            ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Delete client black listing
     */
    public function delete_client_blacklisting(Request $request, $subdomain, $id)
    {
        $tenant_id = $this->cur_tenant_id;
        Blacklist::where('tenant_id', $tenant_id)->where('id', $id)->delete();

        return back()
            ->with('success', 'Client successfully removed from blacklist!');
    }

    /**
     * [POST] Save client black listing
     */
    public function client_blacklisting_post(Request $request, $subdomain)
    {
        if ($request->isMethod('post')) {
            $tenant_id = $this->cur_tenant_id;

            $validatedData = $request->validate([
                'type' => "required|in:client,project",
                'data' => 'required',
            ]);

            $data = $validatedData['data'];

            Blacklist::create([
                'tenant_id' => $tenant_id,
                'fv_full_name' => $data['full_name'],
                'fv_client_id' => $validatedData['type'] == 'client' ? $data['id'] : $data['client_id'],
                'fv_project_id' => $validatedData['type'] == 'client' ? null : $data['id'],
            ]);

            return response()->json([
                'status' => true,
            ]);
        }
    }

    /**
     * [POST] Update client black listing
     */
    public function client_blacklisting_update(Request $request, $subdomain)
    {
        if ($request->isMethod('post')) {
            $tenant_id = $this->cur_tenant_id;

            $ids = $request->get('ids');
            $allow_notifications = $request->get('allow_notification');
            $allow_portals = $request->get('allow_portal');

            foreach ($ids as $id) {
                $allow_notifications[$id] = @$allow_notifications[$id] == 'true' ? 1 : 0;
                $allow_portals[$id] = @$allow_portals[$id] == 'true' ? 1 : 0;

                Blacklist::where('tenant_id', $tenant_id)->where('id', $id)->update([
                    'is_allow_notification' => $allow_notifications[$id],
                    'is_allow_client_potal' => $allow_portals[$id],
                ]);
            }

            return back()->with('success', 'Setting saved successfully!');
        }
    }
}
