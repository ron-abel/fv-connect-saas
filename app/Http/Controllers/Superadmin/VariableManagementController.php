<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;

use App\Models\Variable;
use App\Models\VariablePermission;

class VariableManagementController extends Controller
{
    /**
     * [GET] Variable Management List page for Super Admin
     */
    public function index()
    {
        try {

            $data['variables'] = DB::table('variables')->select('variable_permissions.*', 'variables.id as master_id', 'variables.variable_key', 'variables.variable_name', 'variables.variable_description', 'variables.is_active')
                ->leftJoin('variable_permissions', 'variables.id', '=', 'variable_permissions.variable_id')
                ->where('variables.is_custom_variable', false)
                ->get();

            return $this->_loadContent('superadmin.pages.variable_management', $data);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [GET] Variable Management Add page for Super Admin
     */
    public function addVariable()
    {
        try {
            return $this->_loadContent('superadmin.pages.variable_management_add');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * [POST] Create Variable
     */
    public function addVariablePost(Request $request)
    {
        $request->validate(
            [
                'variable_name' => 'required',
                'variable_key' => 'required',
                'variable_description' => 'required',
            ],
            [
                'variable_name.required' => 'Please enter variable name',
                'variable_key.required' => 'Please enter variable key',
                'variable_description.required' => 'Please enter variable description',
            ]
        );

        try {

            $obj = Variable::create([
                'variable_name' => $request->input('variable_name'),
                'variable_key' => $request->input('variable_key'),
                'variable_description' => $request->input('variable_description'),
            ]);

            if ($obj->id) {
                return redirect()->route('variable_management')->with('success', 'Variable added successfully');
            } else {
                return redirect()->back()->with('error', 'Unable to add variable at the moment, please try again later');
            }
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * [POST] Add Variable Permission
     */
    public function addVariablePermissionPost(Request $request)
    {
        try {

            $variable_id = $request->input('variable_id');

            $permissions = [
                'is_project_timeline' => $request->input('is_project_timeline'),
                'is_timeline_mapping' => $request->input('is_timeline_mapping'),
                'is_phase_change_sms' => $request->input('is_phase_change_sms'),
                'is_review_request_sms' => $request->input('is_review_request_sms'),
                'is_client_banner_message' => $request->input('is_client_banner_message'),
                'is_automated_workflow_action' => $request->input('is_automated_workflow_action'),
                'is_mass_text' => $request->input('is_mass_text'),
                'is_email_template' => $request->input('is_email_template'),
            ];

            $permission = VariablePermission::where('variable_id', $variable_id)->exists();
            if ($permission) {
                VariablePermission::where('variable_id', $variable_id)->update($permissions);
            } else {
                $permissions['variable_id'] = $variable_id;
                VariablePermission::create($permissions);
            }

            return \Response::json(array('success' => true, 'message' => "Setting Saved Successfully!"));
        } catch (Exception $e) {
            return \Response::json(array('success' => false, 'message' => $e->getMessage()));
        }
    }


    /**
     * [POST] Delete Variable for Super Admin
     */
    public function deleteVariable($variable_id)
    {
        try {
            $variable =  Variable::where('id', $variable_id)->first();
            if ($variable) {
                VariablePermission::where('variable_id', $variable_id)->delete();
                Variable::find($variable_id)->delete();
                return \Response::json(array('success' => true, 'message' => 'Variable Deleted Successfully!'));
            } else {
                return \Response::json(array('success' => false, 'message' => 'Unable to Delete Variable at this Moment!'));
            }
        } catch (Exception $e) {
            return \Response::json(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    /**
     * [POST] Update Variable Active
     */
    public function updateActive(Request $request)
    {
        try {
            $variable_id = $request->input('variable_id');
            $variable =  Variable::where('id', $variable_id)->first();
            if ($variable) {
                $variable->is_active = $variable->is_active ? false : true;
                $variable->save();
                return \Response::json(array('success' => true, 'message' => 'Setting Saved Successfully!'));
            } else {
                return \Response::json(array('success' => false, 'message' => 'Unable to Toggle Variable at this Moment!'));
            }
        } catch (Exception $e) {
            return \Response::json(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    /**
     * [POST] Update Variable Information
     */
    public function udateVariablePost(Request $request)
    {
        $request->validate(
            [
                'edit_variable_name' => 'required',
                'edit_variable_description' => 'required',
            ],
            [
                'edit_variable_name.required' => 'Please enter variable name',
                'edit_variable_description.required' => 'Please enter variable description',
            ]
        );

        try {
            $variable_id = $request->input('edit_variable_id');

            Variable::where('id', $variable_id)->update([
                'variable_name' => $request->input('edit_variable_name'),
                'variable_description' => $request->input('edit_variable_description'),
            ]);

            $permissions = [
                'is_project_timeline' => $request->input('edit_is_project_timeline') ? true : false,
                'is_timeline_mapping' => $request->input('edit_is_timeline_mapping') ? true : false,
                'is_phase_change_sms' => $request->input('edit_is_phase_change_sms') ? true : false,
                'is_review_request_sms' => $request->input('edit_is_review_request_sms') ? true : false,
                'is_client_banner_message' => $request->input('edit_is_client_banner_message') ? true : false,
                'is_automated_workflow_action' => $request->input('edit_is_automated_workflow_action') ? true : false,
                'is_mass_text' => $request->input('edit_is_mass_text') ? true : false,
                'is_email_template' => $request->input('edit_is_email_template') ? true : false,
            ];

            VariablePermission::where('variable_id', $variable_id)->update($permissions);

            return redirect()->route('variable_management')->with('success', 'Variable updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
