<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log as Logging;
use Illuminate\Http\Request;

use App\Models\TenantLiveCheckList;
use App\Models\TenantLive;
use App\Models\User;
use App\Services\SendGridServices;
use App\Services\SlackServices;

class ClientPortalLaunchController extends Controller
{
    private $sendGridServices;
    private $slackServices;
    public $cur_tenant_id;

    public function __construct(SlackServices $slackServices)
    {
        $this->slackServices = $slackServices;
        Controller::setSubDomainName();
        $this->cur_tenant_id = session()->get('tenant_id');
        $this->sendGridServices = new SendGridServices($this->cur_tenant_id);
    }

    /**
     * [GET] Client Launch Pad Page for Admin
     */
    public function client_portal_launch(Request $request, $subdomain)
    {
        try {
            $tenant_id = $this->cur_tenant_id;
            $tenantLive = TenantLive::where('tenant_id', $tenant_id)->first();
            $checkList = TenantLiveCheckList::where('tenant_id', $tenant_id)->first();
            return $this->_loadContent('admin.pages.client_portal_launch', compact('tenantLive', 'checkList'));
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
     * POST - Update Client Launch Pad
     */
    public function update_live_checklist(Request $request)
    {
        $tenant_id = $this->cur_tenant_id;
        $tenantCheckList = TenantLiveCheckList::where('tenant_id', $tenant_id)->first();
        if (!isset($tenantCheckList->id)) {
            $tenantCheckList = TenantLiveCheckList::create([
                'tenant_id' => $tenant_id
            ]);
        }

        $tenantCheckList->{$request->type} = intval($request->value);
        $tenantCheckList->save();

        return response()->json([
            'status' => true,
            'message' => 'NICE JOB!'
        ]);
    }

    /**
     * POST - Update Client Launch Pad into Live
     */
    public function go_live(Request $request, $subdomain)
    {
        try {

            $tenant_id = $this->cur_tenant_id;
            $data = $request->all();
            $saveData = [];
            $saveData['tenant_id'] = $tenant_id;
            $saveData['status'] = $data['status'];
            $scheduled_at = "";
            if (isset($data['scheduled_date']) and !empty($data['scheduled_date'])) {
                $saveData['scheduled_date'] = $data['scheduled_date'];
                $scheduled_at = $data['scheduled_date'];
            }
            $tenantLive = TenantLive::where('tenant_id', $tenant_id)->first();
            if (isset($tenantLive) and !empty($tenantLive)) {
                $tenantLive->update($saveData);
                $scheduled_at = $tenantLive->scheduled_date;
            } else {
                TenantLive::create($saveData);
            }
            if (isset($data['status']) and !empty($data['status']) and $data['status'] == "scheduled") {
                $tenant_owner = User::where('tenant_id', $tenant_id)->where('user_role_id', User::TENANT_OWNER)->first();
                if (isset($tenant_owner) and !empty($tenant_owner)) {
                    $email = $tenant_owner->email;
                    $tenant_name = $tenant_owner->tenant->tenant_name;
                    $sg_data = [
                        'client_name' => $tenant_name,
                        'scheduled_at' => $scheduled_at,
                        'user_name' => $tenant_owner->full_name
                    ];
                    $this->sendGridServices->sendScheduleEmail($email, $sg_data);
                    $this->slackServices->sendScheduleMessage($tenant_owner, $scheduled_at);
                }
            }

            $statuses = [
                'setup' => 'Setup saved',
                'scheduled' => 'Schedule is set',
                'live' => 'Your Portal is now live'
            ];
            $message = "Setting saved successfully!";
            if (array_key_exists($data['status'], $statuses)) {
                $message = $statuses[$data['status']];
            }

            return back()->with('go_live', $message);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
