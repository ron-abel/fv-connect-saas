<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\API_LOG;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $logs = API_LOG::all();
        $all_logs = [];
        foreach ($all_logs as $log) {
        }
        return $this->_loadContent('superadmin.pages.customers', compact('all_logs'));
    }

    public function customerDetails($domain)
    {
        $all_logs = API_LOG::where('request_domain', $domain)->get();
        return $this->_loadContent('superadmin.pages.customer_details', compact('all_logs'));
    }
}
