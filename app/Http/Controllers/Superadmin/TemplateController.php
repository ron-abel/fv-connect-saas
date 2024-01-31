<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Templates;
use App\Models\TemplateCategory;
use App\Models\Tenant;
use Auth;
use Response;
use App\Models\PhaseCategorie;


class TemplateController extends Controller
{	

	/**
     * [GET] Template List page for Super Admin
    */
    public function index()
	{
		$templates_data = Templates::get();
		return $this->_loadContent('superadmin.pages.template', ['templates_data' => $templates_data]);
	}

	/**
     * [GET] Create Template page for Super Admin
    */
    public function add_template()
	{
		return $this->_loadContent('superadmin.pages.add_template');
	}

	/**
     * [POST] Create Template for Super Admin
    */
    public function add_template_post(Request $request)
	{
		$request->validate([
			'template_name' => 'required|string|min:5',
			'template_description' => 'required|string',
		]);

		$current_date = date('Y-m-d H:i:s');

		$values = array(
			'template_name' => $request->input('template_name'), 
			'template_description' => $request->input('template_description'), 
			'created_at' => $current_date);

		$template_id = Templates::create($values);

		return redirect()->route('templates');
	}

	/**
     * [GET] Edit Template for Super Admin
    */
    public function edit_template($template_id)
	{

		$template_details =   Templates::where('id', $template_id)->first();

		if ($template_details) {

            $template_category_details =  TemplateCategory::where('template_id', $template_id)->get();

			return $this->_loadContent('superadmin.pages.edit_template', compact('template_details', 'template_category_details'));
		} else {
			return abort(404);
		}
	}

	/**
     * [POST] Edit Template for Super Admin
    */
    public function edit_template_post($template_id, Request $request)
	{
		$request->validate([
			'template_name' => 'required|string|min:5',
			'template_description' => 'required|string',
		]);

		$current_date = date('Y-m-d H:i:s');

		$template_details =  Templates::where('id', $template_id)->first();

		if ($template_details) {

			$values = array('template_name' => $request->input('template_name'), 'template_description' => $request->input('template_description'), 'updated_at' => $current_date);

			$template_details_update = Templates::where('id', $template_id)->update($values);

			if ($template_details_update) {
				return redirect()->route('edit_template', ['template_id' => $template_id])->with('success', 'Template Successfully Updated');
			} else {
				return redirect()->route('edit_template', ['template_id' => $template_id])->with('error', 'No Records Updated');
			}
		} else {
			return abort(404);
		}
	}

	/**
     * [POST] Delete Template for Super Admin
    */
    public function delete_template($template_id)
	{

		$template_details =  Templates::where('id', $template_id)->first();

		if ($template_details) {

			$template_delete = Templates::where('id', $template_id)->delete();

			return Response::json(array('success' => true, 'template_url' => route('templates')));
		} else {
			return abort(404);
		}
	}

	/**
     * [GET] Create Template Category for Super Admin
    */
    public function add_template_category($template_id)
	{
        $template_details =  Templates::where('id', $template_id)->first();

		if ($template_details) {
		    return $this->_loadContent('superadmin.pages.add_template_category', compact('template_details'));
        } else {
            return abort(404);
        }
    }

	/**
     * [POST] Create Template Category for Super Admin
    */
    public function add_template_category_post($template_id, Request $request)
	{
		$request->validate([
			'template_category_name' => 'required|string|min:5',
			'template_category_description' => 'required|string',
		]);

        $template_details =  Templates::where('id', $template_id)->first();

		if ($template_details) {

            $current_date = date('Y-m-d H:i:s');

            $values = array('template_id' => $template_id, 'template_category_name' => $request->input('template_category_name'), 'template_category_description' => $request->input('template_category_description'), 'created_at' => $current_date);

            // check if template linked to tenant
            if(!empty($template_details->tenant_id)) {
                $values['tenant_id'] = $template_details->tenant_id;
            }

            $tenant_id = TemplateCategory::create($values);

            return redirect()->route('edit_template', ['template_id' => $template_id])->with('success', 'Template Category Successfully Created');
        }
	}

	/**
     * [GET] Edit Template Category for Super Admin
    */
    public function edit_template_category($template_category_id)
	{

		$template_category_details =   TemplateCategory::where('id', $template_category_id)->first();
		$template = [];
		if(isset($template_category_details->template_id)){
			$template = Templates::where('id', $template_category_details->template_id)->first();
		}
		
		if ($template_category_details) {
			return $this->_loadContent('superadmin.pages.edit_template_category', compact('template_category_details', 'template'));
		} else {
			return abort(404);
		}
	}

	/**
     * [POST] Edit Template Category for Super Admin
    */
    public function edit_template_category_post($template_category_id, Request $request)
	{
		$request->validate([
			'template_category_name' => 'required|string|min:5',
			'template_category_description' => 'required|string',
		]);

		$current_date = date('Y-m-d H:i:s');

		$template_category_details =  TemplateCategory::where('id', $template_category_id)->first();

		if ($template_category_details) {

			$values = array(
					'template_category_name' => $request->input('template_category_name'), 
					'template_category_description' => $request->input('template_category_description'), 
					'updated_at' => $current_date);
			$template_id = $template_category_details->template_id;

			$template_category_details_update = TemplateCategory::where('id', $template_category_id)->update($values);

			if ($template_category_details_update) {
                // update phase categories if its a tenant template category
                $template_details = Templates::where('id', $template_category_details->template_id)->first();
                if(!empty($template_details->tenant_id)) {
                    PhaseCategorie::where(['tenant_id' => $template_details->tenant_id, 'template_category_id' => $template_category_details->id])
                    ->update(['phase_category_name' => $request->input('template_category_name')]);
                }
				return redirect()->route('edit_template', ['template_id' => $template_id])->with('success', 'Template Category Successfully Updated!');
			} else {
				return redirect()->route('edit_template_category', ['template_category_id' => $template_category_id])->with('error', 'No Records Updated');
			}
		} else {
			return abort(404);
		}
	}

	/**
     * [POST] Delete Template Category for Super Admin
    */
    public function delete_template_category($template_category_id)
	{

		$template_category_details =  TemplateCategory::where('id', $template_category_id)->first();

		if ($template_category_details) {

			$template_category_delete = TemplateCategory::where('id', $template_category_id)->delete();

			return Response::json(array('success' => true, 'template_category_url' => route('edit_template', ['template_id' => $template_category_details->template_id])));
		} else {
			return abort(404);
		}
	}

}