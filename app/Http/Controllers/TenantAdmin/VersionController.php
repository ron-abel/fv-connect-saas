<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Models\Version;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class VersionController extends Controller
{
    public function __construct()
    {
        Controller::setSubDomainName();
    }

    /**
     * [GET] Support Page for Admin
     */
    public function index()
    {
        $data['versions'] = Version::orderBy('created_at', 'desc')
            ->paginate(10);
        return $this->_loadContent('admin.pages.versions', $data);
    }

    public function filterVersions(Request $request)
    {
        $versions = Version::select('*');

        if($request->formInput)
        {
            $versions = $versions->where('version_name','like','%'.$request->formInput.'%');
        }

        $versions = $versions->get();

        return response()->json(['status_code' => 200, 'data' => $versions], 200);
    }
}
