<?php

return [

    'twilio' => [

        'default' => 'twilio',

        'connections' => [

            'twilio' => [

                /*
                |--------------------------------------------------------------------------
                | Account SID
                |--------------------------------------------------------------------------
                |
                | Your Twilio Account SID #
                |
                */

                'account_sid' => env('TWILIO_ACCOUNT_SID', ''),
                
                /*
                |--------------------------------------------------------------------------
                | API SID
                |--------------------------------------------------------------------------
                |
                | Your Twilio Account SID #
                |
                */

                'sid' => env('TWILIO_API_SID', ''),

                /*
                |--------------------------------------------------------------------------
                | API Access Token
                |--------------------------------------------------------------------------
                |
                | Access token that can be found in your Twilio dashboard
                |
                */

                'token' => env('TWILIO_API_TOKEN', ''),

                /*
                |--------------------------------------------------------------------------
                | From Number
                |--------------------------------------------------------------------------
                |
                | The Phone number registered with Twilio that your SMS & Calls will come from
                |
                */

                'from' => env('TWILIO_FROM', ''),
                'phase_change' => env('TWILIO_FROM_PHASE_CHANGE', ''),
                'review_request' => env('TWILIO_FROM_REVIEW_REQUEST', ''),
                'mass_message' => env('TWILIO_FROM_MASS_MESSAGE', ''),
            ],
        ],

        'cost_per_sms' => 0.0075
    ],
];
