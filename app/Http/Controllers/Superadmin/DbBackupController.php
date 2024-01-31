<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\DbBackup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class DbBackupController extends Controller
{

    /**
     * [GET] DB Backup File List page for Super Admin
     */
    public function index()
    {
        try {
            $db_backups = DbBackup::orderby('id', 'DESC')->get();
            return $this->_loadContent('superadmin.pages.db_backup', [
                'db_backups' => $db_backups,
            ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function deleteDbBackup(Request $request)
    {
        $file_id = $request->file_id;
        $db_backup = DbBackup::find($file_id);
        $file_name = $db_backup->file_name;
        $db_backup->delete();

        $file_path = public_path() . "/backup/" . $file_name;
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        $response['success'] = true;
        $response['message'] = 'File deleted successfully!';
        return response()->json($response);
    }

    public function createNewDbBackup(Request $request)
    {

        Artisan::call("db:backup");

        $response['success'] = true;
        $response['message'] = 'Backup job started successfully! Please reload this page after few minutes!';
        return response()->json($response);
    }
}
