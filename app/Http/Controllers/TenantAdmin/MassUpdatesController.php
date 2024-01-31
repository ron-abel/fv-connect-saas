<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\FilevineService;
use Illuminate\Support\Facades\URL;
use App\Models\Tenant;
use Exception;

class MassUpdatesController extends Controller
{
    public $cur_tenant_id;
    public function __construct()
    {
        Controller::setSubDomainName();
        $this->cur_tenant_id = session()->get('tenant_id');
    }

    /**
     * [GET] Mass Updates Page for Admin
    */
    public function index(Request $request){
        return $this->_loadContent("admin.pages.mass_updates");
    }

    /**
     * [POST] Upload CSV to public folder
    */
    public function upload_csv(Request $request){
        try {
            $target_dir =  public_path('/assets/uploads/');
            $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
            if ($_FILES["fileToUpload"]["size"] == 0) {
                echo "Failed Records: Choose the CSV file";
                exit();
            }
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Check if file already exists
            if (file_exists($target_file)) {
                if (isset($request->removeOriginalFile)) {
                    unlink($target_file);
                } else {
                    echo "Failed Records: Sorry, file already exists.";
                    $uploadOk = 0;
                }
            }

            // Check file size
            if ($_FILES["fileToUpload"]["size"] > 500000) {
                echo "Failed Records: Sorry, your file is too large.";
                $uploadOk = 0;
            }
            // Allow certain file formats
            if($imageFileType != "csv") {
                echo "Failed Records: Sorry, only CSV files are allowed.";
                $uploadOk = 0;
            }
            // Check if $uploadOk is set to 0 by an error
            if ($uploadOk == 0) {
                echo "Failed Records: Sorry, your file was not uploaded.";
            // if everything is ok, try to upload file
            } else {
                if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                    echo "Success added: The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
                } else {
                    echo "Failed Records: Sorry, there was an error uploading your file.";
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
    * [POST] Uplaod CSV data to Filevine API
    */
    public function add_csv_data(Request $request){
        try {
            if(isset($request->operation) && isset($request->filename)) {
              $tenant_id = $this->cur_tenant_id;
              $Tenant = Tenant::find($tenant_id);
                $operation = $request->operation;
                if ($operation == "contact") {
                    $filename = $request->filename;
                    $csv_content_array = $this->convertCSVtoArray($filename);
                    $contacts = $this->convertCSVArraytoContactsArray($csv_content_array);

                    $apiurl = config('services.fv.default_api_base_url');
                    if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                        $apiurl = $Tenant->fv_api_base_url;
                    }
                    $filevine_api = new FilevineService($apiurl, "");

                    $results = array();

                    $contact_loop_count = 1;

                    $failed_count = 0;
                    $success_added_count = 0;
                    $success_update_count = 0;

                    foreach ($contacts as $csv_contact) {
                        if($contact_loop_count % 30 == 0){
                            $apiurl = config('services.fv.default_api_base_url');
                            if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                                $apiurl = $Tenant->fv_api_base_url;
                            }
                            $filevine_api = new FilevineService($apiurl, "");
                        }
                        $added = -1;
                        // At first check whether the contact with the Full Name exists or not
                        $contact = $filevine_api->getContactByFullName($csv_contact['fullName']);

                        $contact = json_decode($contact, TRUE);
                        if ($contact['count'] != 0) {
                            $results['failed_records'][] = "Failed Records: The ".$contact['count']." contacts with same full name '".$csv_contact['fullName']."' exist.";
                            $failed_count++;
                            continue;
                        }

                        if (empty($csv_contact['emails'][0]['address'])) {
                            unset($csv_contact['emails'][0]['address']);
                        }
                        if (empty($csv_contact['emails'][0]['label'])) {
                            unset($csv_contact['emails'][0]['label']);
                        }
                        if (empty($csv_contact['phones'][0]['number'])) {
                            unset($csv_contact['phones'][0]['number']);
                        }
                        if (empty($csv_contact['phones'][0]['label'])) {
                            unset($csv_contact['phones'][0]['label']);
                        }
                        if (empty($csv_contact['addresses'][0]['line1'])) {
                            unset($csv_contact['addresses'][0]['line1']);
                        }
                        if (empty($csv_contact['addresses'][0]['city'])) {
                            unset($csv_contact['addresses'][0]['city']);
                        }
                        if (empty($csv_contact['addresses'][0]['state'])) {
                            unset($csv_contact['addresses'][0]['state']);
                        }
                        if (empty($csv_contact['addresses'][0]['postalCode'])) {
                            unset($csv_contact['addresses'][0]['postalCode']);
                        }
                        if (empty($csv_contact['addresses'][0]['label'])) {
                            unset($csv_contact['addresses'][0]['label']);
                        }

                        // If there are no contacts with same fullname, then it creates the contact

                        $contact = $filevine_api->createContact($csv_contact);
                        $contact = json_decode($contact, TRUE);

                        // Check create contact API succeeds or not
                        if (!empty($contact)) {
                            if (array_key_exists("message", $contact)) {
                                $added = 1;
                            } else if (array_key_exists("personId", $contact) and array_key_exists("native", $contact['personId'])) {
                                $added = 2;
                            }
                        } else {
                            $added = 1;
                            $contact['message'] = " | Check csv data properly!";
                        }

                        $result = "";
                        if ($added == 1) {
                            $result .=  "Failed Records: " . $csv_contact['fullName'] . " is not added as ". $contact['message'];
                            $results['failed_records'][] = $result;
                            $failed_count++;
                        } else if ($added == 2) {
                            $result .= "Success added: " . $csv_contact['fullName'] . " is added successfully.";
                            $success_added_count++;
                        }


                        $contact_loop_count++;
                    }

                    $results['failed_count'] = 'Failed Records: ' . $failed_count;
                    $results['success_added_count'] = 'Success added: ' . $success_added_count;
                    $results['success_update_count'] = 'Success updated: ' . $success_update_count;

                    echo json_encode(array('result' => $results));

                } else if ($operation == "personType") {

                    $filename = $request->filename;
                    $csv_content_array = $this->convertCSVtoArray($filename);
                    $contacts = $this->convertCSVArraytoPersonTypesArray($csv_content_array);

                    $apiurl = config('services.fv.default_api_base_url');
                    if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                         $apiurl = $Tenant->fv_api_base_url;
                    }
                    $filevine_api = new FilevineService($apiurl, "");
                    $results = array();

                    $contact_loop_count = 1;

                    $failed_count = 0;
                    $success_added_count = 0;
                    $success_update_count = 0;

                    foreach ($contacts as $csv_contact) {

                        if($contact_loop_count % 30 == 0){
                          $apiurl = config('services.fv.default_api_base_url');
                          if (isset($Tenant->fv_api_base_url) and !empty($Tenant->fv_api_base_url)) {
                              $apiurl = $Tenant->fv_api_base_url;
                          }
                          $filevine_api = new FilevineService($apiurl, "");
                        }

                        $csv_contact['personTypes'] = $this->convertPersonTypes($csv_contact['personTypes']);

                        // Check whether the contact with fullname exist or not and only one exists or not
                        $contact = $filevine_api->getContactByFullName($csv_contact['fullName']);
                        $contact = json_decode($contact, TRUE);
                        if ($contact['count'] == 0) {
                            $results[] = "The contact with '".$csv_contact['fullName']."' does not exist.";
                            continue;
                        } else if ($contact['count'] > 1) {
                            $results[] = "The ".$contact['count']." contacts with same full name '".$csv_contact['fullName']."' exist.";
                            continue;
                        }

                        // If there is only one contact with same fullname, then it adds the new person type
                        $contactId = $contact['items'][0]['personId']['native'];
                        $personTypes = $contact['items'][0]['personTypes'];
                        $added = -1;
                        if ($contactId == null) {
                            $added = -1;
                        } else {
                            // Add PersonType
                            $new_personType = $csv_contact['personTypes'];

                            // Fix the Backend API Error of "Frim" instead of "Firm"
                            if (in_array($new_personType, $personTypes) == False) {
                                $added = 1;
                                array_push($personTypes, $new_personType);

                                // Prepare Post Fields
                                $params = array('personTypes' => $personTypes);
                                $contact = $filevine_api->updateContact($contactId, $params);
                //                 $contact = json_decode($contact, TRUE);
                //                 $personTypes = $contact['personTypes'];
                                $added = 2;
                            } else {
                                $added = 0;
                            }
                        }
                        $result = "";
                        if ($added == -1) {
                            $result = $csv_contact['fullName']." is not existing.";
                        } else if ($added == 0) {
                            $result .= "Failed Records: " . $csv_contact['fullName']." 's '".$new_personType."' personType is already in there.";
                            $failed_count++;
                            $results['failed_records'][] = $result;
                        } else if ($added == 1) {
                            $result .= "Failed Records: " . $csv_contact['fullName']." 's '".$new_personType."' personType is not added due to API connection problem.";
                            $failed_count++;
                            $results['failed_records'][] = $result;
                        } else if ($added == 2) {
                            $result .= "Success added: " . $csv_contact['fullName']." 's '".$new_personType."' personType is added successfully.";
                            $success_added_count++;
                        }

                        $contact_loop_count++;
                    }

                    $results['failed_count'] = 'Failed Records: ' . $failed_count;
                    $results['success_added_count'] = 'Success added: ' . $success_added_count;
                    $results['success_update_count'] = 'Success updated: ' . $success_update_count;

                    echo json_encode(array('result' => $results));
                }
            } else{
                echo json_encode(array('result' => 'error', 'message' => 'Invalid CSV File!'));
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
    * Get CSV data and convert to Contacts Array
    */
    public function convertCSVArraytoContactsArray($csv_content_array) {
        try {
            $result = [];

            if (count($csv_content_array) <= 1)
                return $result;

            $headere_row = $csv_content_array[0];
            for( $i = 1; $i < count($csv_content_array); $i++) {

                $contact = array('emails' => array(array()), 'phones' => array(array()), 'addresses' => array(array()));
                $contact = [];
                $row = $csv_content_array[$i];

                // Map the contacts with Header
                for ( $j = 0; $j < count($row); $j++) {
                    $key = $headere_row[$j];
                    $value = trim($row[$j]);
                    switch ( $key ) {
                        case "fullName":
                            $names = explode(" ", $value);
                            $contact["firstName"] = $names[0];
                            $contact["lastName"] = $names[1];
                            $contact["fullName"] = $value;
                            break;
                        case "emails[address]":         $contact['emails'][0]['address'] = $value;         break;
                        case "emails[label]":           $contact['emails'][0]['label'] = $value;           break;
                        case "phones[label]":           $contact['phones'][0]['label'] = $value;           break;
                        case "phones[number]":          $contact['phones'][0]['number'] = $value;          break;
                        case "addresses[line1]":        $contact['addresses'][0]['line1'] = $value;        break;
                        case "addresses[city]":         $contact['addresses'][0]['city'] = $value;         break;
                        case "addresses[state]":        $contact['addresses'][0]['state'] = $value;        break;
                        case "addresses[postalCode]":   $contact['addresses'][0]['postalCode'] = $value;   break;
                        case "addresses[label]":        $contact['addresses'][0]['label'] = $value;        break;
                        case "personTypes":             $contact["personTypes"] = [$this->convertPersonTypes($value)];             break;
                        case "notes":                   $contact["notes"] = $value;                     break;
                    }
                }

                $result[] = $contact;
            }

            return $result;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
    * Convert the CSV Content of Person Types into contactid-persontype to add array
    */
    public function convertCSVArraytoPersonTypesArray($csv_content_array) {
        try{
            $result = [];

            if (count($csv_content_array) <= 1)
                return $result;

            $headere_row = $csv_content_array[0];
            for( $i = 1; $i < count($csv_content_array); $i++) {

                $contact = [];
                $row = $csv_content_array[$i];

                // Map the contacts with Header
                for ( $j = 0; $j < count($row); $j++) {
                    $key = $headere_row[$j];
                    $value = trim($row[$j]);
                    switch ( $key ) {
                        case "fullName":    $contact["fullName"] = $value;      break;
                        case "personTypes":  $contact["personTypes"] = $value;   break;
                    }
                }

                $result[] = $contact;
            }

            return $result;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
    * Get CSV data and convert to Person Type array
    */
    public function convertPersonTypes($personType) {

        $persontypes_array = array('Firm' => 'Firm', 'Insurance Company' => 'InsuranceCompany', 'Involved Party' => 'InvolvedParty', 'Medical Provider' => 'MedicalProvider');
        if (array_key_exists($personType, $persontypes_array)) {
            $personType =  $persontypes_array[$personType];
        }

        return $personType;
    }

    /**
    * Get CSV data and convert to array format
    */
    public function convertCSVtoArray($filename) {
        try{
            // The nested array to hold all the arrays
            $result = [];
            $filename =  public_path('/assets/uploads/').$filename;

            // Open the file for reading
            if (($h = fopen("{$filename}", "r")) !== FALSE)
            {
                // Each line in the file is converted into an individual array that we call $data
                // The items of the array are comma separated
                while (($data = fgetcsv($h, 1000, ",")) !== FALSE)
                {
                    // Each individual array is being pushed into the nested array
                    $result[] = $data;
                }

                // Close the file
                fclose($h);
            }

            return $result;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

}
