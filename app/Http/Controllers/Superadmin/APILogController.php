<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Response;
use App\Models\LegalteamConfig;
use App\Models\Tenant;
use App\Models\API_LOG;
use App\Services\ExportService;
use Carbon\Carbon;

class APILogController extends Controller
{

    /**
     * [GET] API Logs page for Super Admin
     */
    public function index()
    {
        try {

            $startDate = request()->has('log_start_date') ? date("Y-m-d", strtotime(request()->get('log_start_date'))) : Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = request()->has('log_end_date') ? date("Y-m-d", strtotime(request()->get('log_end_date'))) : Carbon::now()->format('Y-m-d');
            $tenant_id = request()->has('tenant_id') ? request()->get('tenant_id') : '';

            $all_log_query = DB::table('api_logs')
            ->select('api_logs.*', 'tenants.tenant_name')
            ->leftJoin('tenants', 'api_logs.tenant_id', '=', 'tenants.id')
            ->whereDate('api_logs.created_at', '>=', $startDate)
                ->whereDate('api_logs.created_at', '<=', $endDate . ' 23:59:59');

            if (!empty($tenant_id)) {
                $all_log_query->where('api_logs.tenant_id', $tenant_id);
                $all_log_query->where('tenants.id', $tenant_id);
            }
            $all_log_query->orderby('api_logs.created_at', 'DESC');
            $all_logs = $all_log_query->get();

            $tenants_data = Tenant::where('tenant_name', '!=', config('app.superadmin'))->get();

            return $this->_loadContent('superadmin.pages.api_logs', [
                'all_logs' => $all_logs,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'tenants_data' => $tenants_data,
                'tenant_id' => $tenant_id
            ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [GET] Export APILOG CSV
     */
    public function exportAPILogCsv(Request $request)
    {
        $service = new ExportService();
        return $service->exportAPILogCsv($request);
    }
}
