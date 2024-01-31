<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;
use App\Models\MediaLocker;
use Illuminate\Support\Facades\Auth;
use Exception;
use Hash;
use Illuminate\Support\Facades\Log as Logging;

class MedialockerController extends Controller
{
    public $cur_tenant_id;
    public function __construct()
    {
        Controller::setSubDomainName();
        $this->cur_tenant_id = session()->get('tenant_id');
    }

    /*
    * [GET] List of tenant media file
    */
    public function media_locker(Request $request)
    {
        try {
            $tenant_id = $this->cur_tenant_id;
            $tenant_details = Tenant::where('id', $tenant_id)->first();
            $media = MediaLocker::where('tenant_id', $tenant_id)->orderBy('created_at', 'desc')->get();
            return $this->_loadContent('admin.pages.media_locker', compact('tenant_details', 'media'));
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

    /*
    * [POST] Save Media Object
    */
    public function save($subdomain, Request $request){
        $edit_id = "";
        if($request->has('edit_id') && !empty($request->input('edit_id'))) {
            $edit_id = $request->input('edit_id');
            $rules = [
                'media_code' => 'required|unique:media_locker,media_code,' . $request->input('edit_id'),
                'file' => 'nullable|mimetypes:video/*,audio/*,image/*'
            ];
        }
        else {
            $rules = [
                'media_code' => 'required|unique:media_locker,media_code',
                'file' => 'required|mimetypes:video/*,audio/*,image/*'
            ];
        }
        $request->validate($rules);
        try {
            $tenant_id = $this->cur_tenant_id;
            $Tenant = Tenant::find($tenant_id);

            $values = array(
                'tenant_id' => $tenant_id,
                'media_code' => $request->input('media_code')
            );
            // check for template file
            if($request->hasFile('file')) {
                $file = $request->file('file');
                $file_name = $file->getClientOriginalName();
                $file_ext = $file->getClientOriginalExtension();
                $file_new_name = $Tenant->tenant_name . '/media_locker_files/' . time() . '_' . $file_name;
                //Move Uploaded File
                \Storage::disk('s3')->put($file_new_name, file_get_contents($file), 'public');
                $public_url = \Storage::disk('s3')->url($file_new_name);
                $values['media_url'] = $public_url;
            }
            else {
                if(empty($edit_id)) {
                    return redirect()->back()->with('error', 'Please upload a media file');
                }
            }

            // check if edit id there
            if(!empty($edit_id)) {
                $media = MediaLocker::where('id', $edit_id)->update($values);
                if($media){
                    return redirect()->route('media_locker', ['subdomain' => $subdomain])
                    ->with('success', 'Media object updated successfully');
                }
                else {
                    return redirect()->route('media_locker', ['subdomain' => $subdomain])
                    ->with('error', 'Unable to update requested media object at the moment!');
                }
            }
            else {
                $media = MediaLocker::create($values);
                if(isset($media->id) && $media->id > 0){
                    return redirect()->route('media_locker', ['subdomain' => $subdomain])
                    ->with('success', 'Media object created successfully');
                }
                else {
                    return redirect()->route('media_locker', ['subdomain' => $subdomain])
                    ->with('error', 'Unable to create requested media object at the moment!');
                }
            }

        } catch (Exception $e) {
            return redirect()->route('media_locker', ['subdomain' => $subdomain])
                ->with('error', $e->getMessage());
        }
    }

    /*
    * [GET] Delete Media Object
    */
    public function delete(Request $request){
        $response = ['success' => false, 'message' => ''];
        try {
            $tenant_id = $this->cur_tenant_id;
            $media = MediaLocker::where('tenant_id', $tenant_id)->where('id', $request->get('media_id'))->first();
            if (isset($media->id) && !empty($media->id)) {
                $media->delete();
                $response = ['success' => true, 'message' => 'Media object deleted successfully'];
            }
            else {
                $response = ['success' => false, 'message' => 'Unable to delete requested media object'];
            }
        } catch (Exception $e) {
            $response = ['success' => false, 'message' => $e->getMessage()];
        }
        return response()->json($response);
    }
}

