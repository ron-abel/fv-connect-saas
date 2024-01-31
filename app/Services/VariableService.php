<?php

namespace App\Services;

use Illuminate\Support\Facades\Log as Logging;
use App\Services\FilevineService;

use App\Models\Variable;
use App\Models\AutoNoteGoogleReviewLinks;
use App\Models\Tenant;

class VariableService
{

    /**
     * Update content text variable with text
     */
    public function updateVariables($content_text, $page_key, $additional_data)
    {
        try {
            $tenant_id = null;
            if (array_key_exists('tenant_id', $additional_data)) {
                $tenant_id = $additional_data['tenant_id'];
            }

            if ($tenant_id && !isset($additional_data['review_link'])) {
                $google_review_link = AutoNoteGoogleReviewLinks::where(['tenant_id' => $tenant_id, 'is_default' => 1])->first();
                if ($google_review_link && isset($google_review_link->review_link)) {
                    $additional_data['review_link'] = $google_review_link->review_link;
                }
            }

            if ($tenant_id && isset($additional_data['fv_project_id']) && !empty($additional_data['fv_project_id'])) {
                if (!isset($additional_data['project_name'], $additional_data['project_phase'])) {
                    $tenant_details = Tenant::where('id', $tenant_id)->first();
                    $apiurl = config('services.fv.default_api_base_url');
                    if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                        $apiurl = $tenant_details->fv_api_base_url;
                    }
                    $filevine_api = new FilevineService($apiurl, "", $tenant_id);
                    $project_id = $additional_data['fv_project_id'];
                    $project_details = json_decode($filevine_api->getProjectsById($project_id));

                    $additional_data['project_name'] = isset($project_details->projectName) ? $project_details->projectName : '';
                    $additional_data['project_phase'] = isset($project_details->phaseName) ? $project_details->phaseName : '';
                    $client_id = isset($project_details->clientId->native) ? $project_details->clientId->native : 0;

                    if (!isset($additional_data['fv_client_id']) || empty($additional_data['fv_client_id'])) {
                        $additional_data['fv_client_id'] = $client_id;
                    }
                }
            }

            if ($tenant_id && isset($additional_data['fv_client_id']) && !empty($additional_data['fv_client_id'])) {
                if (!isset($additional_data['client_fullname'], $additional_data['client_firstname'])) {
                    $tenant_details = Tenant::where('id', $tenant_id)->first();
                    $apiurl = config('services.fv.default_api_base_url');
                    if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                        $apiurl = $tenant_details->fv_api_base_url;
                    }
                    $filevine_api = new FilevineService($apiurl, "", $tenant_id);
                    $fv_client_id = $additional_data['fv_client_id'];
                    $contact_details = json_decode($filevine_api->getContactByContactId($fv_client_id));
                    $additional_data['client_fullname'] = isset($contact_details->fullName) ? $contact_details->fullName : '';
                    $additional_data['client_firstname'] = isset($contact_details->firstName) ? $contact_details->firstName : '';
                }
            }

            $tenant_details = Tenant::where('id', $tenant_id)->first();

            if ($tenant_id && !isset($additional_data['law_firm_name'])) {
                $additional_data['law_firm_name'] = $tenant_details->tenant_law_firm_name;
            }

            if ($tenant_id && !isset($additional_data['client_portal_url'])) {
                $additional_data['client_portal_url'] = 'https://' . $tenant_details->tenant_name . '.vinetegrate.com';
            }

            // update standard variables with real values
            $variables = Variable::getVariableByPageKey($page_key);
            foreach ($variables as $variable) {
                $variable_key = $variable->variable_key;
                $variable_refine = trim(str_replace(array('[', ']'), '', $variable_key));
                if (array_key_exists($variable_refine, $additional_data)) {
                    $content_text = str_replace($variable_key, $additional_data[$variable_refine], $content_text);
                } else {
                    $content_text = str_replace($variable_key, '', $content_text);
                }
            }


            // update custom variables with real value from project
            if ($tenant_id && isset($additional_data['fv_project_id']) && !empty($additional_data['fv_project_id'])) {
                $variables = Variable::getVariableByPageKey($page_key, true);
                if (count($variables)) {
                    $tenant_details = Tenant::where('id', $tenant_id)->first();
                    $apiurl = config('services.fv.default_api_base_url');
                    if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                        $apiurl = $tenant_details->fv_api_base_url;
                    }
                    $filevine_api = new FilevineService($apiurl, "", $tenant_id);
                    $project_id = $additional_data['fv_project_id'];
                    foreach ($variables as $variable) {
                        $variable_key = $variable->variable_key;
                        if (strpos(strtolower($content_text), strtolower($variable_key)) !== false) {
                            $fv_field_selector = $variable->fv_field_selector;
                            $fv_field_selector_type = $variable->fv_field_selector_type;
                            $section_details = json_decode($filevine_api->getProjectFormsTeamInfo([
                                'projectId' => $project_id,
                                'section' => $variable->fv_section_selector,
                                'fields' => $fv_field_selector,
                            ]));

                            $fv_field_selector_value = "";
                            if ($fv_field_selector_type == 'PersonLink') {
                                $fv_field_selector_value = isset($section_details->$fv_field_selector->fullname) ? $section_details->$fv_field_selector->fullname : '';
                            } else if ($fv_field_selector_type == 'PersonList') {
                                $personList = isset($section_details->$fv_field_selector) ? $section_details->$fv_field_selector : [];
                                foreach ($personList as $person) {
                                    $fv_field_selector_value .= $person->fullname . ", ";
                                }
                                $fv_field_selector_value = rtrim($fv_field_selector_value, ', ');
                            } else if ($fv_field_selector_type == 'MultiSelectList') {
                                $fv_field_selector_value = isset($section_details->$fv_field_selector) ? implode(",", $section_details->$fv_field_selector) : '';
                            } else if ($fv_field_selector_type == 'Date') {
                                $fv_field_selector_value = (isset($section_details->$fv_field_selector) && !empty($section_details->$fv_field_selector)) ? date('d-m-Y', strtotime($section_details->$fv_field_selector)) : '';
                            } else {
                                $fv_field_selector_value = isset($section_details->$fv_field_selector) ? $section_details->$fv_field_selector : '';
                            }
                            if (empty($fv_field_selector_value)) {
                                $fv_field_selector_value = $variable->placeholder;
                            }
                            $content_text = str_replace($variable_key, $fv_field_selector_value, $content_text);
                        }
                    }
                }
            }

            return $content_text;
        } catch (\Exception $ex) {
            $error = [
                __FILE__,
                __LINE__,
                $ex->getMessage()
            ];
            Logging::warning(json_encode($error));
            return $content_text;
        }
    }

    /**
     * Update content text variable with text
     */
    public function replaceVariable($content_text, $variable_key)
    {
    }
}
