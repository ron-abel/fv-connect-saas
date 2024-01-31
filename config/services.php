<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'stripe' => [
        'model' => App\Models\Tenant::class,
        'key' => env('STRIPE_KEY', ""),
        'secret' => env('STRIPE_SECRET', ""),
    ],
    'sendgrid' => [
        'key' => env('MAIL_PASSWORD', ""),
        'from_name' => env('MAIL_FROM_NAME', 'Vinetegrate'),
        'from_mail' => env('MAIL_FROM_ADDRESS', "support@vinetegrate.com"),
        'manager_mails' => env('VINECONNECT_MANAGER_NOTE_EMAIL', ""),
        'register_template' => env('SENDGRID_REGISTER_EMAIL_TEMPLATE_ID', ''),
        'confirmed_register_template' => env('SENDGRID_REGISTER_CONFIRMED_EMAIL_TEMPLATE_ID', ''),
        'change_subscription_plan_template' => env('SENDGRID_CHANGE_SUBSCRIPTION_PLAN_EMAIL_TEMPLATE_ID', ''),
        'admin_login_note_template' => env('SENDGRID_ADMIN_LOGIN_NOTE_TEMPLATE_ID', ''),
        'test-target-mail' => env("TEST_TARGET_MAIL", ''),
        'client_feedback_template' => env('SENDGRID_FEEDBACK_TO_LEGALTEAM_TEMPLATE_ID', ''),
        'reset_password' => env('SENDGRID_ADMIN_RESET_PASSWORD_LINK', ''),
        'invite_email' => env('SENDGRID_TENANT_ADMIN_INVITE_TEMPLATE_ID', ''),
        'schedule_email' => env('SENDGRID_TENANT_SCHEDULED_EMAIL_TEMPLATE_ID', ''),
        'trigger_action_admin_notification' => env('SENDGRID_TRIGGER_ACTION_ADMIN_NOTIFICATION_EMAIL_TEMPLATE_ID', ''),
        'trigger_action_client_notification' => env('SENDGRID_TRIGGER_ACTION_CLIENT_NOTIFICATION_EMAIL_TEMPLATE_ID', ''),
        'contact_update_email' => env('SENDGRID_CLIENT_CONTACT_UPDATE_EMAIL_TEMPLATE_ID', ''),
        'form_response_template' => env('SENDGRID_TENANT_FORM_RESPONSE_ADMIN_TEMPLATE_ID', ''),
        'tfa_code' => env('SENDGRID_2FA_CODE_TEMPLATE_ID', ''),
        'admin_handler_note_for_client_failed_login' => env('SENDGRID_ADMIN_HANDLER_NOTE_FOR_CLIENT_FAILED_LOGIN_TEMPLATE_ID', ''),
        'tenant_admin_notification_email' => env('SENDGRID_ADMIN_NOTIFICATION_EMAIL_TEMPLATE_ID', ''),
        'test_template' => env('SENDGRID_TEST_TEMPLATE_ID', ''),
        'tenant_admin_mass_email' => env('SENDGRID_ADMIN_MASS_EMAIL_TEMPLATE_ID', '')
    ],

    'sms' => [
        'test-target-number' => env('SMS_TEST_NUMBER'),
    ],

    'slack' => env('SLACK_WEBHOOK_URL', ""),

    'fv' => [
        'legal_team_note_prefix' => ('#' . env('FV_LEGALTEAM_NOTE_PREFIX', 'clientportal')),
        'default_api_base_url' => env('FV_DEFAULT_API_BASE_URL', 'https://api.filevine.io'),
        'default_app_base_url' => env('FV_DEFAULT_APP_BASE_URL', 'https://app.filevine.com'),
        'default_api_key' => env('FV_DEFAULT_API_KEY', ''),
        'default_api_key_secret' => env('FV_DEFAULT_API_KEY_SECRET', ''),
        'default_api_project_type_id' => env('FV_DEFAULT_API_PROJECT_TYPE_ID', ''),
        'default_api_base_url_ca' => env('FV_DEFAULT_API_BASE_URL_CA', 'https://api.filevine.ca'),
        'default_app_base_url_ca' => env('FV_DEFAULT_APP_BASE_URL_CA', 'https://app.filevine.ca'),
        'default_report_id' => env('FV_DEFAULT_REPORT_ID', ''),
        'default_report_api_url' => env('FV_DEFAULT_REPORT_API_URL', 'https://api.filevine.io'),
        'default_report_api_key' => env('FV_DEFAULT_REPORT_API_KEY', ''),
        'default_report_api_secret' => env('FV_DEFAULT_REPORT_API_KEY_SECRET', '')
    ],

    'intercom' => [
        'identiy_secret_key' => env('INTERCOM_IDENTITY_SECRET_KEY', '')
    ],

    'email' => [
        'vineconnect_sales_email' => env('VINECONNECT_SALES_EMAIL', 'sales@vinetegrate.com')
    ],
];
