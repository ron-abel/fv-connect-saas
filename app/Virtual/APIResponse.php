<?php

namespace App\Virtual;

class APIResponse{
    public function validation($errors){
        return response()->json([
            'success'       => false,
            'status'        => 'error',
            'message' => 'Validation Error',
            'httpCode'      => '422',
            'response'      => $errors
        ], 422);
    }

    // for send 2fa function
    public function response_success($response){
        return response()->json([
            'success'        => true,
            'status'         => 'success',
            'message'  => 'Sent successfully',
            'service_sid'   => $response
        ], 200);
    }

    // for validation 2fa code.
    public function validate_response_success($verified_contact_number){
        return response()->json([
            'success'                   => true,
            'status'                    => 'success',
            'message'             => 'Sent successfully',
            'verified_contact_number'   => $verified_contact_number
        ], 200);
    }

    public function invalid(){
        return response()->json([
            'success'       => false,
            'status'        => 'error',
            'message' => 'Invalid API Token',
            'httpCode'      => '422',
        ], 422);
    } 
    public function response_error($message){
        return response()->json([
            'success'       => false,
            'status'        => 'Error',
            'message' =>  $message 
        ], 422);
    }   
    
}