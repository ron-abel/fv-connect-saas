<?php

namespace App\Services;

use Illuminate\Support\Facades\Log as Logging;
use App\Services\FilevineService;
use App\Models\Tenant;
use Illuminate\Http\Request;
use DB;

class FVInitializerService
{
    private $default_api_key;
    private $default_api_secret;
    private $default_api_base_url;
    private $default_report_id;

    public function __construct() {
        $this->default_api_key = config('services.fv.default_report_api_key');
        $this->default_api_secret = config('services.fv.default_report_api_secret');
        $this->default_api_base_url = config('services.fv.default_report_api_url');
        $this->default_report_id = config('services.fv.default_report_id');
    }

    /**
    * create report for tenant if not created
    */
    public function createTenantReport($tenant_id)
    {
        $created = false;
        try {
            $tenant = Tenant::where('id', $tenant_id)->first();
            if (isset($tenant->id) && empty($tenant->fv_report_id)) {

                // check if keys are setup as well
                $config = DB::table('config')->where('tenant_id', $tenant_id)->first();

                if(isset($config->id) && isset($config->fv_api_key) && !empty($config->fv_api_key) 
                && isset($config->fv_key_secret) && !empty($config->fv_key_secret)) {
                    $apiurl = $this->default_api_base_url;
                    if (isset($tenant->fv_api_base_url) and !empty($tenant->fv_api_base_url)) {
                        $apiurl = $tenant->fv_api_base_url;
                    }
                    // get default tenant report
                    $request = new Request();
                    $request->merge(array('fv_api_key' => $this->default_api_key, 'fv_key_secret' => $this->default_api_secret));
                    
                    $fv_service = new FilevineService($this->default_api_base_url, $request);
                    
                    $default_report = json_decode($fv_service->getInitializerReportTenant($this->default_report_id), TRUE);
                    if(is_array($default_report) 
                    && isset($default_report['success']) 
                    && $default_report['success'] == true 
                    && isset($default_report['encryptedData'])) {

                        $fv_service1 = new FilevineService($apiurl, "");
                        $tenant_report = json_decode($fv_service1->addInitializerReportToTenant($default_report['encryptedData']), TRUE);
                        if(is_array($tenant_report) 
                        && isset($tenant_report['id']) 
                        && isset($tenant_report['importSuccess']) 
                        && $tenant_report['importSuccess'] == true) {
                            // update tenant for report
                            $tenant->fv_report_id = $tenant_report['id'];
                            $tenant->save();
                        }
                        else {
                            $error = [
                                __FILE__,
                                __LINE__,
                                'Unable to created report for tenant => ' . $tenant_id,
                                $tenant_report['errors']
                            ];
                            Logging::error(json_encode($error)); 
                        }
                    }
                    else {
                        $error = [
                            __FILE__,
                            __LINE__,
                            'Unable to get default report for default tenant => ' . $default_report['error']
                        ];
                        Logging::error(json_encode($error));        
                    }
                }
            }

        } catch (\Exception $ex) {
            $error = [
                __FILE__,
                __LINE__,
                $ex->getMessage()
            ];
            Logging::warning(json_encode($error));
        }

        return $created;
    }

    /**
    * create report for tenant if not created
    */
    public function getTenantReport($tenant_id)
    {
        // call function to check if report not created yet
        $this->createTenantReport($tenant_id);

        // start function here
        $count = 0;
        try {
            $tenant = Tenant::where('id', $tenant_id)->first();
            if (isset($tenant->id) && !empty($tenant->fv_report_id)) {
                $apiurl = $this->default_api_base_url;
                if (isset($tenant->fv_api_base_url) and !empty($tenant->fv_api_base_url)) {
                    $apiurl = $tenant->fv_api_base_url;
                }
                $fv_service = new FilevineService($apiurl, "");

                $tenant_report = json_decode($fv_service->getRunReportTenant($tenant->fv_report_id), TRUE);
                if(is_array($tenant_report) 
                && isset($tenant_report[0]) 
                && isset($tenant_report[0]['orgName']) 
                && isset($tenant_report[0]['projectsCount'])) {
                    $count = array_sum(array_column($tenant_report, 'projectsCount'));
                }
                else {
                    $error = [
                        __FILE__,
                        __LINE__,
                        'Unable to get report for tenant => ' . $tenant_id,
                        $tenant_report
                    ];
                    Logging::error(json_encode($error)); 
                }
            }

        } catch (\Exception $ex) {
            $error = [
                __FILE__,
                __LINE__,
                $ex->getMessage()
            ];
            Logging::warning(json_encode($error));
        }

        return $count;
    }

}
