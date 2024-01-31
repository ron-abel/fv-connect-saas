<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;
use App\Models\UserInvite;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\Auth;
use Exception;
use Hash;
use App\Services\SendGridServices;
use App\Services\SlackServices;
use Illuminate\Support\Facades\Log as Logging;

use App\Services\FilevineService;

use App\Models\Variable;

class TestController extends Controller
{

    private $sendGridServices;
    private $slackServices;
    public $cur_tenant_id;
    public function __construct(SendGridServices $sendGridServices, SlackServices $slackServices)
    {
        $this->sendGridServices = $sendGridServices;
        $this->slackServices = $slackServices;
        Controller::setSubDomainName();
        $this->cur_tenant_id = session()->get('tenant_id');
    }

    /**
     * Testing Controller
     */
    public function test(Request $request){

        $tenant_id = $this->cur_tenant_id;
        $Tenant = Tenant::where('id', $tenant_id)->first();
        $apiurl = config('services.fv.default_api_base_url');
        if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
            $apiurl = $Tenant->fv_api_base_url;
        }

        $obj = new FilevineService($apiurl, "");

        if(false){
            // users list
            $res = $obj->getUsersList();
            dd(json_decode($res));
        }


        if(false){
            // create a new test user. 
            $params = [
                "firstName"=> "Vernon",
                "lastName"=> "Grant",
                "email"=> "solutionarchitect00@gmail.com",
                "permissions"=> [
                    "accessLevel"=> "Guest",
                    "autoAddToNewProject"=> false,
                    "autoFollowNewProject"=> false,
                    "accessOrgInBox"=> false,
                    "autoAccessLevelOnNewProject"=> "Collaborator-Unsubscribed"
                ]
            ];

            $new_user = $obj->addNewUser($params);

            dd(json_decode($new_user));

        }

        if(false){
            // delete user.
            $userid = 31031;
            $res = $obj->deleteNewUser($userid);
            dd(json_decode($res));
        }

        if(false){
            // products
            $res = $obj->getProjectsList();
            dd(json_decode($res));
        }

        if(false){
            // get project detail
            $res = $obj->getProjectsById(2088779);
            dd(json_decode($res));
        }

        if(false){
            if($request->input('type') == 'subscriptions'){
                // check subscriptions list. 
                $subscriptions = $obj->getSubscriptionsList();
    
                dd(json_decode($subscriptions));
            }
        }

        if(false){
            // check team members of project. 
            $project_id = '11200564';
            $teams = json_decode($obj->getProjectsTeamById($project_id, 1000), true);
            $team_list = [];
            dd($teams);
            foreach ($teams['items'] as $key => $team) {
                
                # code...
                if( count($team['teamRoles']) > 0 || count($team['teamOrgRoles']) > 0 ){
                    $team_list[] = $team;
                }
                
            }
            dd($team_list);

            if(isset($teams['links']['next'])){
                $teams = json_decode($obj->getProjectsTeamById($project_id, 100), true);
            }

            dd($team_list);
        }



        // project contact. 
        if(false){
            // $project_id = 1886691;

            $project_id = 3199185;
            $contacts = $obj->getProjectContactList($project_id);

            dd(json_decode($contacts));


            // $contact_Id = 12593137; //5349495
            // $obj->deleteProjectContact($project_id, $contact_Id);


            // delete project. 
            $obj->archiveProject($project_id);


            
        }

        return 'Welcome!';
    }


    /**
     * Testing Controller
     */
    public function test_api(Request $request){

        $tenant_id = $this->cur_tenant_id;
        $Tenant = Tenant::where('id', $tenant_id)->first();
        $apiurl = config('services.fv.default_api_base_url');
        if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
            $apiurl = $Tenant->fv_api_base_url;
        }

        $obj = new FilevineService($apiurl, "");

        // get fv projects. 
        if(true){
            // products
            $res = $obj->getProjectsList();
            dd(json_decode($res));
        }
    }

}