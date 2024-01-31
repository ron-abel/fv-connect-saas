<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log as Logging;

use App\Models\ClientNotification;
use App\Models\MediaLocker;
use App\Models\Tenant;


class BannerMessagesController extends Controller
{

    public $cur_tenant_id;

    public function __construct()
    {
        Controller::setSubDomainName();
        $this->cur_tenant_id = session()->get('tenant_id');
    }

    /**
     * [GET] Client Portal Setup Page for Tenanat Admin
     */
    public function index()
    {
        try {

            $notices = ClientNotification::where("tenant_id", $this->cur_tenant_id)->orderBy('id', 'DESC')->get();
            // Disabling the notice once expired
            foreach ($notices as $key => $notice) {
                if ($notices[$key] instanceof ClientNotification && $notices[$key]->is_active == 1 && Carbon::now()->startOfDay()->timestamp > strtotime($notices[$key]->end_date)) {
                    $notices[$key]->is_active = 0;
                    $notices[$key]->save();
                }
            }

            return $this->_loadContent('admin.pages.banner_messages', compact('notices'));
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
     * [POST] Notice Settings Page for Admin
     */
    public function notice_post(Request $request, $subdomain)
    {
        $request->validate([
            'notice_body' => "required",
            'start_date' => "required",
            'end_date' => "required"
        ]);

        try {

            $is_active = isset($request['is_active']) && $request['is_active'] == 'on' ? 1 : 0;

            if ($request['start_date'] > $request['end_date']) {
                $err_message = "Start date should be less than or equal end date!";
                return redirect()->back()->with('notice_error', $err_message);
            }

          /*  if ($is_active) {
                ClientNotification::where('tenant_id', $this->cur_tenant_id)->update([
                    'is_active' => 0
                ]);
            } */

            $notice_body = $request['notice_body'];

            if (strlen($notice_body) > 10) {
                $checkstr = substr($notice_body, strpos($notice_body, 'href="') + 6);
                $checkstr = substr($checkstr, 0, 4);
                if ($checkstr != 'http') {
                    $notice_body = str_replace('href="', 'href="https://', $notice_body);
                }
            }

            if (isset($request['id']) && $request['id']) {

                $notice = ClientNotification::where('id', $request['id'])->first();

                $notice->update([
                    'tenant_id' => $this->cur_tenant_id,
                    'notice_body' => $notice_body,
                    'banner_color' => $request['banner_color'],
                    'is_active' => $is_active,
                    'start_date' => $request['start_date'],
                    'end_date' => $request['end_date'],
                ]);
                return redirect()->back()->with('notice_success', 'Setting saved successfully!');
            } else {

                ClientNotification::create([
                    'tenant_id' => $this->cur_tenant_id,
                    'notice_body' => $notice_body,
                    'banner_color' => $request['banner_color'],
                    'is_active' => $is_active,
                    'start_date' => $request['start_date'],
                    'end_date' => $request['end_date'],
                ]);
                return redirect()->back()->with('notice_success', 'Setting saved successfully!');
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [Post] Client Banner Message Delete
     */
    public function notice_delete(Request $request)
    {
        try {
            if (!is_null($request->post('id'))) {
                $notice = ClientNotification::where('id', $request->post('id'))->first();
                if (!is_null($notice) && $notice->count() > 0) {
                    $notice->forceDelete();
                    return json_encode(['success' => true, 'message' => 'Notice deleted successfully']);
                } else {
                    return json_encode(['success' => false, 'message' => 'Notice not found']);
                }
            } else {
                return json_encode(['success' => false, 'message' => 'Invalid request']);
            }
        } catch (\Throwable $th) {
            return json_encode(['success' => false, 'message' => 'Notice not found']);
        }
    }

    /**
     * [POST] Update client notification configurations banner message
     */
    public function updateClientNotificationStatus(Request $request, $subdomain)
    {
        try {
            $id = $request->input('id');
            $config = ClientNotification::where('id', $id)->where('tenant_id', $this->cur_tenant_id)->first();
            if ($config) {
                $config->is_active = $config->is_active ? false : true;
                $config->save();
            }

         /*   if ($config->is_active) {
                ClientNotification::where('tenant_id', $this->cur_tenant_id)->where('id', '!=', $id)->update([
                    'is_active' => 0
                ]);
            } */

            return response()->json([
                'status'  => true,
                'message' => "Setting saved successfully!"
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * [POST] Upload banner message image
     */
    public function uploadImage(Request $request)
    {
        try {
            $tenant_id = $this->cur_tenant_id;
            $Tenant = Tenant::find($tenant_id);
            $file = $request->file;
            $name = time() . $file->getClientOriginalName();
            $filePath = $Tenant->tenant_name . '/' . $name;
            \Storage::disk('s3')->put($filePath, file_get_contents($file), 'public');
            $publicUrl = \Storage::disk('s3')->url($filePath);
            $values = array(
                'tenant_id' => $tenant_id,
                'media_code' => $request->input('media_code') ?? ""
            );
            $values['media_url'] = $publicUrl;
            $media = MediaLocker::create($values);
            return response()->json(['location' => "$publicUrl"]);
        } catch (\Exception $e) {
            return response()->json(['error_message' => $e->getMessage()]);
        }
    }
}
