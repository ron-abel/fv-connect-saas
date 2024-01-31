<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Facades\Log as Logging;

use App\Models\Version;


class VersionManagementController extends Controller
{

    /**
     * [GET] Version Management List page for Super Admin
     */
    public function index()
    {
        try {
            $data['versions'] = Version::get();
            return $this->_loadContent('superadmin.pages.version_management', $data);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [GET] Version Management Add page for Super Admin
     */
    public function add_version()
    {
        try {
            return $this->_loadContent('superadmin.pages.add_version', []);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Create Version page for Super Admin
     */
    public function add_post_version(Request $request)
    {

        $request->validate(
            [
                'version_name' => 'required',
                'description' => 'required',
                'major' => 'required',
                'minor' => 'required',
                'patch' => 'required',
            ],
            [
                'version_name.required' => 'Please enter version name',
                'description.required' => 'Please enter version description',
                'major.required' => 'Please enter version major',
                'minor.required' => 'Please enter version minor',
                'patch.required' => 'Please enter version patch',
            ]
        );

        try {
            $values = array(
                'version_name' => $request->input('version_name'),
                'description' => $request->input('description'),
                'major' => $request->input('major'),
                'minor' => $request->input('minor'),
                'patch' => $request->input('patch'),
                'full' => 'Version ' . $request->input('major') . '.' . $request->input('minor') . '.' . $request->input('patch'),
                'created_at' => Carbon::now()
            );
            $version_obj = Version::create($values);

            $this->updateVersionYaml($request->all());

            if ($version_obj->id) {
                return redirect()->route('version_management')->with('success', 'Version added successfully');
            } else {
                return redirect()->back()->with('error', 'Unable to add version at the moment, please try again later');
            }
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * [GET] Edit Version page for Super Admin
     */
    public function edit_version($version_id)
    {

        try {
            $version =  Version::where('id', $version_id)->first();

            if ($version) {
                return $this->_loadContent('superadmin.pages.edit_version', compact('version'));
            } else {
                return abort(404);
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Update Version page for Super Admin
     */
    public function edit_post_version($version_id, Request $request)
    {
        try {
            $version =  Version::where('id', $version_id)->first();
            if ($version) {
                $request->validate(
                    [
                        'version_name' => 'required',
                        'description' => 'required',
                        'major' => 'required',
                        'minor' => 'required',
                        'patch' => 'required',
                    ],
                    [
                        'version_name.required' => 'Please enter version name',
                        'description.required' => 'Please enter version description',
                        'major.required' => 'Please enter version major',
                        'minor.required' => 'Please enter version minor',
                        'patch.required' => 'Please enter version patch',
                    ]
                );

                $values = array(
                    'version_name' => $request->input('version_name'),
                    'description' => $request->input('description'),
                    'major' => $request->input('major'),
                    'minor' => $request->input('minor'),
                    'patch' => $request->input('patch'),
                    'full' => 'Version ' . $request->input('major') . '.' . $request->input('minor') . '.' . $request->input('patch'),
                    'updated_at' => Carbon::now()
                );
                $version_obj = Version::where('id', $version_id)->update($values);

                $latest_version = Version::latest()->first();
                if ($latest_version != null && $latest_version->id == $version_id) {
                    $this->updateVersionYaml($request->all());
                }

                if ($version_obj) {
                    return redirect()->route('version_management')->with('success', 'Version updated successfully');
                } else {
                    return redirect()->back()->with('error', 'Unable to update version at the moment, please try again later');
                }
            } else {
                return redirect()->route('version_management')->with('error', 'Requested version not found');
            }
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * [POST] Delete Version for Super Admin
     */
    public function delete_version($version_id)
    {
        try {
            $version =  Version::where('id', $version_id)->first();
            if ($version) {
                $version_delete = Version::find($version_id)->delete();
                return \Response::json(array('success' => true, 'message' => 'Version deleted successfully'));
            } else {
                return \Response::json(array('success' => false, 'message' => 'Unable to delete version at the moment'));
            }
        } catch (Exception $e) {
            return \Response::json(array('success' => false, 'message' => $e->getMessage()));
        }
    }


    /**
     * Update Version YML File
     */
    public function updateVersionYaml($params)
    {
        try {
            $version_yml = base_path() . '/config/version.yml';
            $yamlContents = Yaml::parse(file_get_contents($version_yml));

            $yamlContents['current']['major'] = intval($params['major']);
            $yamlContents['current']['minor'] = intval($params['minor']);
            $yamlContents['current']['patch'] = intval($params['patch']);

            $yaml = Yaml::dump($yamlContents, 5);
            file_put_contents($version_yml, $yaml);
        } catch (\Exception $ex) {
            Logging::warning($ex->getMessage());
        }
    }
}
