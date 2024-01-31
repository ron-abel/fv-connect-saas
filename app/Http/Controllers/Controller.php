<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller as BaseController;
use App\Models\LegalteamConfig;


/**
 * @OA\Info(
 *    title="Filevine",
 *    version="1.0.0",
 * )
 */


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    var $data;
    var $fv_cur_tenant_id;

    public function __construct()
    {

        $this->data = array();
    }

    public function setSubDomainName()
    {
        //Check Subdomain
        $domain_name = explode('.', URL::current());
        $subdomain = substr($domain_name[0], strrpos($domain_name[0], '/') + 1);

        $tenant = DB::table('tenants')->where('tenant_name', $subdomain)->first();

        //To used in other files as well
        session()->put('subdomain', $subdomain);
        if (isset($tenant->id)) {
            session()->put('tenant_id',  $tenant->id);
        }

        $this->fv_cur_tenant_id = session()->get('tenant_id');
    }

    /**
     * Load of the html contents.
     */
    public function _loadContent($url, $data = null)
    {
        if ($data != null) {
            $this->data = $data;
        }

        $this->data['cur_tenant_id'] = $this->fv_cur_tenant_id;
        return view($url)->with($this->data);
    }





    /**
     * Convert the phone number format
     * ex:  "+15017122661"
     */
    public function _normilizePhone($phone)
    {
        $phoneNorm = str_replace(array(' ', '-', '(', ')', '+',), '', $phone);
        if (strlen($phoneNorm) == 10) {
            $phoneNorm = '+1' . $phoneNorm;
        }
        if (strlen($phoneNorm) == 11 && substr($phoneNorm, 0, 1) == 1) {
            $phoneNorm = '+' . $phoneNorm;
        }
        return $phoneNorm;
    }

    /**
     * Convert the phone number format for FV
     * ex:  "+15017122661" =>  "5017122661"
     */
    public function _normilizeToFVPhone($phone)
    {
        $phoneNorm = str_replace(array(' ', '-', '(', ')', '+',), '', $phone);
        if (strlen($phoneNorm) == 12 && substr($phoneNorm, 0, 2) == '+1') {
            $phoneNorm = substr($phoneNorm, 2, 10);
        }
        if (strlen($phoneNorm) == 11 && substr($phoneNorm, 0, 1) == 1) {
            $phoneNorm = substr($phoneNorm, 1, 10);
        }
        return $phoneNorm;
    }

    /**
     * get FV API url from app base url.
     */
    public function _getFVAPIBaseUrl($fv_tenant_base_url)
    {
        $fv_api_base_url = $fv_tenant_base_url;

        if (strpos($fv_tenant_base_url, ".filevineapp.com") !== false || strpos($fv_tenant_base_url, "app.filevine.com") !== false) {
            $fv_api_base_url = config('services.fv.default_api_base_url');
            $fv_tenant = "";
            if ($fv_tenant_base_url !== config('services.fv.default_app_base_url')) {
                // should get the fv tenant name. : ex: https://test.filevineapp.com
                $a = explode("//", $fv_tenant_base_url);
                if (count($a) > 1) {
                    $a_domain = $a[1];
                    $fv_tenant = (explode(".", $a_domain))[0];
                } else {
                    $a_domain = $a[0];
                    $fv_tenant = (explode(".", $a_domain))[0];
                }
            }
            if ($fv_tenant != "") {
                $fv_api_base_url = "https://" . $fv_tenant . ".api.filevineapp.com";
            }
        } else if (strpos($fv_tenant_base_url, ".filevineapp.ca") !== false || strpos($fv_tenant_base_url, "app.filevine.ca") !== false) {
            $fv_api_base_url = config('services.fv.default_api_base_url_ca');
            $fv_tenant = "";
            if ($fv_tenant_base_url !== config('services.fv.default_app_base_url_ca')) {
                $a = explode("//", $fv_tenant_base_url);
                if (count($a) > 1) {
                    $a_domain = $a[1];
                    $fv_tenant = (explode(".", $a_domain))[0];
                } else {
                    $a_domain = $a[0];
                    $fv_tenant = (explode(".", $a_domain))[0];
                }
            }
            if ($fv_tenant != "") {
                $fv_api_base_url = "https://" . $fv_tenant . ".api.filevineapp.com";
            }
        }

        return $fv_api_base_url;
    }

    public function _getStates()
    {
        $state_list = array(
            'AL' => "Alabama",
            'AK' => "Alaska",
            'AZ' => "Arizona",
            'AR' => "Arkansas",
            'CA' => "California",
            'CO' => "Colorado",
            'CT' => "Connecticut",
            'DE' => "Delaware",
            'DC' => "District Of Columbia",
            'FL' => "Florida",
            'GA' => "Georgia",
            'HI' => "Hawaii",
            'ID' => "Idaho",
            'IL' => "Illinois",
            'IN' => "Indiana",
            'IA' => "Iowa",
            'KS' => "Kansas",
            'KY' => "Kentucky",
            'LA' => "Louisiana",
            'ME' => "Maine",
            'MD' => "Maryland",
            'MA' => "Massachusetts",
            'MI' => "Michigan",
            'MN' => "Minnesota",
            'MS' => "Mississippi",
            'MO' => "Missouri",
            'MT' => "Montana",
            'NE' => "Nebraska",
            'NV' => "Nevada",
            'NH' => "New Hampshire",
            'NJ' => "New Jersey",
            'NM' => "New Mexico",
            'NY' => "New York",
            'NC' => "North Carolina",
            'ND' => "North Dakota",
            'OH' => "Ohio",
            'OK' => "Oklahoma",
            'OR' => "Oregon",
            'PA' => "Pennsylvania",
            'RI' => "Rhode Island",
            'SC' => "South Carolina",
            'SD' => "South Dakota",
            'TN' => "Tennessee",
            'TX' => "Texas",
            'UT' => "Utah",
            'VT' => "Vermont",
            'VA' => "Virginia",
            'WA' => "Washington",
            'WV' => "West Virginia",
            'WI' => "Wisconsin",
            'WY' => "Wyoming"
        );

        return $state_list;
    }
}
