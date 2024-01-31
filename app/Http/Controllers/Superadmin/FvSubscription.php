<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;

use App\Services\FilevineService;

use App\Models\Tenant;


class FvSubscription extends Controller
{

    public function __construct()
    {
        try {
            $Tenant = Tenant::find(20);
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                $apiurl = $Tenant->fv_api_base_url;
            }
            $this->fv_service = new FilevineService($apiurl, "", $Tenant->id);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }


    /**
     * [GET] API Logs page for Super Admin
     */
    public function index()
    {
        try {

            $old_tenant_url = "https://vinetegratesales.vinetegrate.com";
            $new_tenant_url = "https://portal.vinetegrate.com";

            $get_all_subscriptions = json_decode($this->fv_service->getSubscriptionsList());
            echo "<pre>";
            //print_r($this->fv_service);
            print_r($get_all_subscriptions);

            // just do it for first item for testing purpose
            foreach ($get_all_subscriptions as $single_subscription) {

                if (strpos(strtolower($single_subscription->endpoint), strtolower($old_tenant_url)) !== false) {
                   // $single_subscription->endpoint = str_replace($old_tenant_url, $new_tenant_url, $single_subscription->endpoint);
                   // $res = $this->fv_service->updateSubscription($single_subscription->subscriptionId, $single_subscription);
                }

                if (strpos($single_subscription->endpoint, $old_tenant_url) == false && strpos($single_subscription->endpoint, $new_tenant_url) == false) {
                   // $this->fv_service->deleteSubscription($single_subscription->subscriptionId);
                }



            }

            //  $get_all_subscriptions = json_decode($this->fv_service->getSubscriptionsList());
            //  print_r($get_all_subscriptions);

            echo "Updated Sucessfully!";
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
