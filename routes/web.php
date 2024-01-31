<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::domain(config('app.superadmin') . '.' . config('app.domain'))->group(function () {
    Route::get('/', 'Superadmin\ConnectController@index')->name('super.welcome');
    Route::post('login', 'Auth\AuthController@authenticate_superadmin')->name('login_post');

    //toggle ip_verification
    Route::post('tenant/toggle_ipverificationenable', 'Superadmin\TenantsController@toggle_ip_verification_enable')->name('toggle_ip_verification_enable');

    Route::get('register_tenant', 'Superadmin\TenantsController@selfRegistration')->name('tenant_registration');
    Route::post('register_tenant', 'Superadmin\TenantsController@postSelfRegistration')->name('submit_tenant_registration');

    /* Route::get('forgot_password', 'Auth\AuthController@showForgotPasswordForm');
    Route::post('reset_password_without_token', 'Auth\AuthController@validatePasswordRequest');
    Route::get('password/reset/{token}', 'Auth\AuthController@showResetForm');
    Route::post('reset_password_with_token', 'Auth\AuthController@resetPassword');
    Route::get('verify/email/{token}', 'Superadmin\TenantsController@confirmVerification')->name('confirm-verification'); */

    Route::group(['prefix' => 'admin'], function () {

        Route::get('login', 'Auth\AuthController@login')->name('super.login');
        Route::get('logout', 'Auth\AuthController@logout')->name('super.logout');


        Route::group(['middleware' => 'superadminauth'], function () {
            Route::get('/', 'Auth\AuthController@login')->name('super.admin');
            Route::get('home', 'Superadmin\TenantsController@index')->name('home');

            // tenants management
            Route::get('tenants', 'Superadmin\TenantsController@index')->name('tenants');

            Route::get('tenant/create', 'Superadmin\TenantsController@add_tenant')->name('add_tenant');

            Route::post('tenant/create', 'Superadmin\TenantsController@add_tenant_post')->name('add_tenant_post');

            // Munish Start
            Route::post('tenant/reverify/{tenant_id}', 'Superadmin\TenantsController@reverify_tenant')->name('reverify_tenant');
            // Munish END


            Route::get('tenant/edit/{tenant_id}', 'Superadmin\TenantsController@edit_tenant')->name('edit_tenant');

            Route::post('tenant/edit/{tenant_id}', 'Superadmin\TenantsController@edit_tenant_post')->name('edit_tenant_post');

            Route::post('tenant/delete/{tenant_id}', 'Superadmin\TenantsController@delete_tenant')->name('delete_tenant');

            Route::get('tenant/view/{tenant_id}', 'Superadmin\TenantsController@view_tenant')->name('view_tenant');

            Route::get('tenants/exportcsv', 'Superadmin\TenantsController@exportTenantsCsv')->name('tenants_export_csv');

            Route::get('api_logs/exportcsv', 'Superadmin\APILogController@exportAPILogCsv')->name('api_log_export_csv');

            Route::post('tenant/edit-status/{tenant_id}', 'Superadmin\TenantsController@editTenantActiveStatus')->name('edit_tenant_active_status');

            Route::post('tenant/upgrade-plan/{tenant_id}', 'Superadmin\TenantsController@upgradeTenantPlan')->name('upgrade_tenant_plan');

            Route::get('tenants/usage-dashboard', 'Superadmin\TenantsController@usageDashboard')->name('usage_dashboard');

            Route::get('tenants/usageexportcsv', 'Superadmin\TenantsController@exportTenantsUsageCsv')->name('tenants_usage_export_csv');

            //User-managements
            Route::post('user/reset/{id}', 'Superadmin\TenantsController@resetUser')->name('reset-user');

            // template managements
            Route::get('templates', 'Superadmin\TemplateController@index')->name('templates');

            Route::get('template/create', 'Superadmin\TemplateController@add_template')->name('add_template');

            Route::post('template/create', 'Superadmin\TemplateController@add_template_post')->name('add_template_post');

            Route::get('template/edit/{template_id}', 'Superadmin\TemplateController@edit_template')->name('edit_template');

            Route::post('template/edit/{template_id}', 'Superadmin\TemplateController@edit_template_post')->name('edit_template_post');

            Route::post('template/delete/{template_id}', 'Superadmin\TemplateController@delete_template')->name('delete_template_done');

            Route::get('template_category/{template_id}/create', 'Superadmin\TemplateController@add_template_category')->name('add_template_category');

            Route::post('template_category/{template_id}/create', 'Superadmin\TemplateController@add_template_category_post')->name('add_template_category_post');

            Route::get('template_category/edit/{template_category_id}', 'Superadmin\TemplateController@edit_template_category')->name('edit_template_category');

            Route::post('template_category/edit/{template_category_id}', 'Superadmin\TemplateController@edit_template_category_post')->name('edit_template_category_post');

            Route::post('template_category/delete/{template_category_id}', 'Superadmin\TemplateController@delete_template_category')->name('delete_template_category');


            // API logs management
            Route::get('api_logs', 'Superadmin\APILogController@index')->name('api_logs');

            Route::get('db_backup', 'Superadmin\DbBackupController@index')->name('db_backup');
            Route::post('db_backup_delete', 'Superadmin\DbBackupController@deleteDbBackup')->name('db_backup_delete');
            Route::post('db_backup_create', 'Superadmin\DbBackupController@createNewDbBackup')->name('db_backup_create');

            // billing plans management
            Route::get('billing_plans', 'Superadmin\BillingPlansController@index')->name('billing_plans');

            Route::get('billing_plans/create', 'Superadmin\BillingPlansController@add_billing_plan')->name('add_billing_plan');

            Route::post('billing_plans/create', 'Superadmin\BillingPlansController@add_billing_plan_post')->name('add_billing_plan_post');

            Route::get('billing_plans/edit/{billing_plan_id}', 'Superadmin\BillingPlansController@edit_billing_plan')->name('edit_billing_plan');

            Route::post('billing_plans/edit/{billing_plan_id}', 'Superadmin\BillingPlansController@edit_billing_plan_post')->name('edit_billing_plan_post');

            Route::get('automation_workflow_mapping', 'Superadmin\AutomationWorkflowMappingController@index')->name('automation_workflow_mapping');
            Route::post('automation_workflow_mapping_post', 'Superadmin\AutomationWorkflowMappingController@store')->name('automation_workflow_mapping_post');
            Route::post('automation_workflow_mapping_delete', 'Superadmin\AutomationWorkflowMappingController@delete')->name('automation_workflow_mapping_delete');

            Route::get('filevine/subscription', 'Superadmin\FvSubscription@index')->name('filevine_subscription');

            Route::post('tenant/update_name', 'Superadmin\TenantsController@updateTenantName')->name('update_tenant_name');

            Route::get('version_management', 'Superadmin\VersionManagementController@index')->name('version_management');
            Route::get('version_management/add', 'Superadmin\VersionManagementController@add_version')->name('version_management.add');
            Route::post('version_management/add_post', 'Superadmin\VersionManagementController@add_post_version')->name('version_management.add_post');
            Route::get('version_management/edit/{id}', 'Superadmin\VersionManagementController@edit_version')->name('version_management.edit');
            Route::post('version_management/edit_post/{id}', 'Superadmin\VersionManagementController@edit_post_version')->name('version_management.edit_post');
            Route::post('version_management/delete/{id}', 'Superadmin\VersionManagementController@delete_version')->name('version_management.delete');

            Route::get('variable_management', 'Superadmin\VariableManagementController@index')->name('variable_management');
            Route::get('variable_management/add', 'Superadmin\VariableManagementController@addVariable')->name('variable_management_add');
            Route::post('variable_management/add_post', 'Superadmin\VariableManagementController@addVariablePost')->name('variable_management_add_post');
            Route::post('variable_management/add_permission_post', 'Superadmin\VariableManagementController@addVariablePermissionPost')->name('variable_management_add_permission_post');
            Route::post('variable_management/delete/{id}', 'Superadmin\VariableManagementController@deleteVariable')->name('variable_management_delete');
            Route::post('variable_management/update_active', 'Superadmin\VariableManagementController@updateActive')->name('variable_management_update_active');
            Route::post('variable_management/update', 'Superadmin\VariableManagementController@udateVariablePost')->name('variable_management_update');

            Route::post('tenant/get_billing_plan', 'Superadmin\TenantsController@getBillingPlan')->name('get_billing_plan');

            Route::get('subscription_plan_mapping', 'Superadmin\SubscriptionPlanMappingController@index')->name('subscription_plan_mapping');
            Route::post('subscription_plan_mapping_post', 'Superadmin\SubscriptionPlanMappingController@store')->name('subscription_plan_mapping_post');
            Route::post('subscription_plan_mapping_delete', 'Superadmin\SubscriptionPlanMappingController@delete')->name('subscription_plan_mapping_delete');
        });
    });
});

Route::domain('{subdomain}.' . config('app.domain'))->group(function () {
    Route::get('/invalid_tenant', function () {
        return view('404');
    })->name('invalid');

    // custom auth with tenant validation.
    Route::group(['middleware' => 'checktenant'], function () {
        Route::group(['middleware' => 'multilang'], function () {
            Route::get('/', 'Client\LoginController@index')->name('client');

            Route::post('/client', 'Client\LoginController@login')->name('client_login');

            Route::get('/client/logout', 'Client\LoginController@logout')->name('client_logout');

            Route::get('/2fa_verify/{projectId}/{phoneNo}', 'Client\VerifyClientController@send2faAuthCode')->name('verify');

            Route::post('/2fa_verify', 'Client\VerifyClientController@validate2faAuthCode')->name('verifyCheck');
            Route::post('/re_send_2fa', 'Client\VerifyClientController@reSend2fa')->name('re_send_2fa');

            Route::post('/send_2fa_email', 'Client\VerifyClientController@send2faEmail')->name('send2faEmail');
            Route::post('/verify_2fa_email', 'Client\VerifyClientController@verify2faEmail')->name('verify2faEmail');
            Route::post('/submit_information', 'Client\LoginController@submitInformation')->name('submit_information');

            Route::post('/client_feedback', 'Client\LookupController@sendClientFeedback')->name('client_feedback');

            Route::post('/get_contact_info', 'Client\LookupController@getContactInfo')->name('get_contact_info');
            Route::post('/update_contact_info', 'Client\LookupController@updateContactInfo')->name('update_contact_info');

            Route::get('/lookup/{lookup_project_id}', 'Client\LookupController@index')->name('lookup');

            Route::get('/lookup/my_team_messages/{lookup_project_id}', 'Client\LookupController@myTeamMessages')->name('myteam_messages');

            Route::get('/lookup/forms/{lookup_project_id}', 'Client\LookupController@forms')->name('client_active_forms');

            Route::get('/form', 'Client\LookupController@showForm')->name('show_form');

            Route::get('/form/{id}', 'Client\LookupController@getFormData')->name('show_form');

            Route::post('/handle_form_response', 'Client\LookupController@handleFormResponse')->name('handle_form_response');

            Route::get('/share/views/open/form/{id}', 'Client\PublicFormControlle@showForm')->name('show_form_public');
            Route::post('/share/views/open/form/save', 'Client\PublicFormControlle@saveForm')->name('save_form_public');

            Route::get('/lookup/upload_files/{lookup_project_id}', 'Client\LookupController@uploadFiles')->name('upload_files');
            Route::post('/lookup/upload_project_files/{lookup_project_id}', 'Client\LookupController@uploadProjectFiles')->name('upload_project_files');

            Route::post('login', 'Auth\AuthController@authenticate')->name('login_post');

            Route::post('/allow_note_editing', 'Client\LookupController@allowNoteEditing')->name('allow_note_editing');

            Route::post('/send_note_reply', 'Client\LookupController@sendNoteReply')->name('send_note_reply');

            Route::get('/lookup/calendar/{lookup_project_id}', 'Client\CalendarController@index')->name('lookup_calendar');
            Route::post('/lookup/calendar/update', 'Client\CalendarController@update')->name('lookup_calendar_update');

            Route::get('/get_text_from_language', 'Client\LookupController@getTextFromLanguage')->name('get_text_from_language');

            Route::get('/download_fv_document', 'Client\LookupController@downloadFvFile')->name('download_fv_document');
        });
        //************************* ADMIN ROUTES ******************************** /

        Route::get('forgot_password', 'Auth\AuthController@showForgotPasswordForm');

        Route::post('reset_password_without_token', 'Auth\AuthController@validatePasswordRequest');

        Route::get('password/reset/{token}', 'Auth\AuthController@showResetForm');

        Route::post('reset_password_with_token', 'Auth\AuthController@resetPassword');
        Route::get('verify/email/{token}', 'Superadmin\TenantsController@confirmVerification')->name('confirm-verification');

        Route::get('register_user', 'TenantAdmin\UserController@register_user')->name('users.register');
        Route::post('register_user', 'TenantAdmin\UserController@post_register_user')->name('user.register');

        Route::group(['prefix' => 'admin', 'middleware' => 'customauth'], function () {

            Route::get('/', 'Auth\AuthController@login')->name('login_redirect');

            Route::get('login', 'Auth\AuthController@login')->name('admin.login');

            Route::get('logout', 'Auth\AuthController@logout')->name('logout');

            Route::get('billing/{is_update?}', 'TenantAdmin\BillingController@index')->name('billing');

            Route::post('/billing/submit', 'TenantAdmin\BillingController@submitSubscription')->name('billing.submit');

            Route::get('/payment_history', 'TenantAdmin\PaymentController@showPaymentHistory')->name('payment_history');


            Route::get('cancel/subscription/{id}', 'TenantAdmin\BillingController@cancelSubscription')->name('subscription_cancel');

            Route::post('update/subscription/{id}', "TenantAdmin\BillingController@updateCard")->name('update_card');

            Route::post('update/plan/{id}', "TenantAdmin\BillingController@updatePlan")->name('update_plan');

            Route::post('add_subscription/{id}', "TenantAdmin\BillingController@addSubscription")->name('add_subscription');

            Route::post('accept_terms', "TenantAdmin\DashboardController@acceptTerms")->name('accept_terms');

            Route::group(['middleware' => 'subscribed'], function () {

                Route::get('dashboard', 'TenantAdmin\DashboardController@index')->name('dashboard');
                Route::post('searchfeedBack', 'TenantAdmin\DashboardController@searchFeedBack')->name('searchfeedBack');
                Route::get('dashboard_search_client', 'TenantAdmin\DashboardController@getClientList')->name('dashboard_search_client');
                Route::get('dashboard_send_client_info', 'TenantAdmin\DashboardController@sendClientInfo')->name('dashboard_send_client_info');
                Route::get('dashboard_block_client', 'TenantAdmin\DashboardController@addClientIntoBlock')->name('dashboard_block_client');
                Route::post('dashboard_update_client', 'TenantAdmin\DashboardController@updateClientInfo')->name('dashboard_update_client');

                Route::get('feedbacks/exportcsv', 'TenantAdmin\DashboardController@exportFeedbacksCsv')->name('feedbacks_export_csv');
                Route::get('usagelog/exportcsv', 'TenantAdmin\DashboardController@exportUsageLogCsv')->name('usage_log_export_csv');
                Route::get('messagelog/exportcsv', 'TenantAdmin\DashboardController@exportMessageLogCsv')->name('message_log_export_csv');
                Route::get('submittedlog/exportcsv', 'TenantAdmin\DashboardController@exportSubmittedLogCsv')->name('submitted_log_exportcsv');

                Route::get('get/notificationlog', 'TenantAdmin\DashboardController@getNotificationLog')->name('get_notificationlog');
                Route::get('export/notificationlog', 'TenantAdmin\DashboardController@exportNotificationLog')->name('export_notificationlog');


                Route::get('legal_team', 'TenantAdmin\LegalTeamController@index')->name('legal_team');

                Route::post('legal_team', 'TenantAdmin\LegalTeamController@store')->name('legal_team_store');
                Route::post('legal_team_all_store', 'TenantAdmin\LegalTeamController@storeAll')->name('legal_team_all_store');


                Route::post('legal_team/destroy', 'TenantAdmin\LegalTeamController@destroy')->name('legal_team_destroy');

                Route::post('legal_team/sort', 'TenantAdmin\LegalTeamController@sortable')->name('legal_team_sort');
                Route::post('legal_team_person/sort', 'TenantAdmin\LegalTeamController@person_sortable')->name('legal_team_person_sort');

                Route::get('get_project_section/{type_id}', 'TenantAdmin\LegalTeamController@get_project_section')->name('get_project_section');
                Route::get('get_project_section_field/{type_id}/{selection_filter}', 'TenantAdmin\LegalTeamController@get_project_section_field')->name('get_project_section_field');
                #Route::post('legalteam_person', 'TenantAdmin\LegalTeamController@legalteam_person')->name('legalteam_person');
                Route::post('update_legalteam_config', 'TenantAdmin\LegalTeamController@update_legalteam_config')->name('update_legalteam_config');
                Route::post('update_all_legalteam_config', 'TenantAdmin\LegalTeamController@update_all_legalteam_config')->name('update_all_legalteam_config');

                Route::get('phase_categories', 'TenantAdmin\PhaseCategoriesController@index')->name('phase_categories');

                Route::post('phase_categories/upload', 'TenantAdmin\PhaseCategoriesController@uploadImage');

                Route::post('phase_categories', 'TenantAdmin\PhaseCategoriesController@store')->name('add_phase_categories');

                Route::post('phase_categories/sort', 'TenantAdmin\PhaseCategoriesController@category_sortable')->name('phase_categories_sort');

                // routes for custom templates
                Route::post('phase_categories/custom_template', 'TenantAdmin\PhaseCategoriesController@custom_template_save')->name('phase_categories_custom_template_save');
                Route::post('phase_categories/get_custom_template', 'TenantAdmin\PhaseCategoriesController@get_custom_template')->name('phase_categories_get_custom_template');

                //  rout for get image list
                Route::get('/get-image-list', 'TenantAdmin\PhaseMappingController@getImageList');
                //  rout for get image list

                Route::get('phase_mapping', 'TenantAdmin\PhaseMappingController@index')->name('phase_mapping');

                Route::post('phase_mapping', 'TenantAdmin\PhaseMappingController@store')->name('add_phase_mappings');

                Route::get('phase_categories_info', 'TenantAdmin\PhaseCategoriesController@get_category_info')->name('phase_categories_info');

                Route::get('phase_category_title', 'TenantAdmin\PhaseCategoriesController@phase_category_title')->name('phase_category_title');
                Route::get('get_template_category_description_by_id', 'TenantAdmin\PhaseCategoriesController@get_template_category_description_by_id')->name('get_template_category_description_by_id');

                Route::get('get_phase_categories_info', 'TenantAdmin\PhaseMappingController@get_phase_category_info')->name('get_phase_category_info');

                Route::get('get_phase_mapping_override_title_by_id', 'TenantAdmin\PhaseMappingController@get_phase_mapping_override_title_by_id')->name('get_phase_mapping_override_title_by_id');

                Route::get('get_phase_category_description_by_id', 'TenantAdmin\PhaseMappingController@get_phase_category_description_by_id')->name('get_phase_category_description_by_id');

                Route::delete('delete_mapped_timeline/{projectTypeId}', 'TenantAdmin\PhaseMappingController@delete_mapped_timeline')->name('delete_mapped_timeline');

                Route::get('settings', 'TenantAdmin\ClientPortalSetupController@index')->name('settings');

                Route::post('settings', 'TenantAdmin\ClientPortalSetupController@settings_post')->name('settings_post');

                Route::post('save_notification_email', 'TenantAdmin\ClientPortalSetupController@save_notification_email')->name('save_notification_email');

                Route::post('go_live', 'TenantAdmin\ClientPortalLaunchController@go_live')->name('go_live');

                Route::get('client_portal_launch', 'TenantAdmin\ClientPortalLaunchController@client_portal_launch')->name('client_portal_launch');

                Route::post('client_portal/update_tenant_live_checklist', 'TenantAdmin\ClientPortalLaunchController@update_live_checklist')->name('client_update_tenant_live_checklist');

                Route::delete('delete_notification_email/{id}', 'TenantAdmin\ClientPortalSetupController@delete_notification_email')->name('delete_notification_email');

                Route::get('client_blacklist', 'TenantAdmin\BlackListController@client_blacklisting')->name('client_blacklist');

                Route::post('get_clients_contacts', 'TenantAdmin\BlackListController@client_contacts')->name('get_clients_contacts');

                Route::post('client_blacklist', 'TenantAdmin\BlackListController@client_blacklisting_post')->name('client_blacklist_post');

                Route::post('update_client_blacklist', 'TenantAdmin\BlackListController@client_blacklisting_update')->name('update_client_blacklist');

                Route::delete('client_blacklist/{id}', 'TenantAdmin\BlackListController@delete_client_blacklisting')->name('client_blacklist.delete');

                Route::post('update_law_firm_display_name', 'TenantAdmin\ClientPortalSetupController@update_law_firm_display_name')->name('settings_law_firm_display_name');

                Route::post('upload_firms_logo', 'TenantAdmin\ClientPortalSetupController@upload_firms_logo')->name('settings_firms_logo');
                Route::post('upload_background', 'TenantAdmin\ClientPortalSetupController@upload_background')->name('settings_background');
                Route::post('update_display_color_settings', 'TenantAdmin\ClientPortalSetupController@update_display_color_settings')->name('update_display_color_settings');

                Route::post('notice', 'TenantAdmin\BannerMessagesController@notice_post')->name('notice_post');

                Route::post('notice_delete', 'TenantAdmin\BannerMessagesController@notice_delete')->name('notice_delete');

                Route::get('get_graph_data', 'TenantAdmin\DashboardController@get_graph_data')->name('get_graph_data');

                Route::get('get_table_data/{value}', 'TenantAdmin\DashboardController@get_table_data')->name('get_table_data');

                Route::get('webhooks', 'TenantAdmin\WebhookController@index')->name('webhooks');
                Route::get('webhooks/fetch-data/{trigger_action}', 'TenantAdmin\WebhookController@fetchData');

                Route::post('process_webhook', 'TenantAdmin\WebhookController@process_webhook')->name('process_webhook');

                Route::get('mass_updates', 'TenantAdmin\MassUpdatesController@index')->name('mass_updates');

                Route::post('mass_updates/upload_csv', 'TenantAdmin\MassUpdatesController@upload_csv')->name('upload_csv');

                Route::post('mass_updates/add_csv_data', 'TenantAdmin\MassUpdatesController@add_csv_data')->name('add_csv_data');

                Route::get('profile', 'TenantAdmin\ProfileController@index')->name('profile');

                Route::post('profile', 'TenantAdmin\ProfileController@profile_update')->name('profile_update');

                Route::post('update_password', 'TenantAdmin\ProfileController@update_password')->name('update_password');

                Route::get('support', 'TenantAdmin\SupportController@index')->name('support');

                Route::get('/phase_change_automated_communications', 'TenantAdmin\AutomatedCommunicationsController@index')->name('phase_change_automated_communications');

                Route::get('/mass_messages', 'TenantAdmin\MassMessagesController@index')->name('mass_messages');
                Route::get('/mass_messages_fetch_contacts ', 'TenantAdmin\MassMessagesController@fetch_contacts')->name('mass_messages_fetch_contacts');
                Route::get('/mass_messages_logs/{id} ', 'TenantAdmin\MassMessagesController@mass_messages_logs')->name('mass_messages_logs');
                Route::get('/mass_messages_exportcsv/{id} ', 'TenantAdmin\MassMessagesController@massMessagesExportcsv')->name('mass_messages_exportcsv');
                Route::get('mass_messages_custom_logs', 'TenantAdmin\MassMessagesController@massMessagesCustomLog')->name('mass_messages_custom_logs');
                Route::get('mass_messages_custom_logs_csv', 'TenantAdmin\MassMessagesController@massMessagesCustomLogCSV')->name('mass_messages_custom_logs_csv');
                Route::post('/mass_messages_send_messages ', 'TenantAdmin\MassMessagesController@send_messages')->name('mass_messages_send_messages');
                Route::post('mass_messages_upload_csv', 'TenantAdmin\MassMessagesController@upload_csv')->name('mass_messages_upload_csv');
                Route::post('mass_messages_delete', 'TenantAdmin\MassMessagesController@massMessagesDelete')->name('mass_messages_delete');
                Route::post('mass_messages_recreate_job', 'TenantAdmin\MassMessagesController@reCreateJob')->name('mass_messages_recreate_job');
                Route::post('update_mass_messages', 'TenantAdmin\MassMessagesController@updateMassMessages')->name('update_mass_messages');

                Route::get('forms', 'TenantAdmin\FormController@index')->name('forms');

                Route::get('get_tenant_forms', 'TenantAdmin\FormController@get_forms')->name('get_tenant_forms');

                Route::get('form/{id?}', 'TenantAdmin\FormController@form')->name('form_settings');

                Route::get('form_view/{id?}', 'TenantAdmin\FormController@view_form')->name('form_view');

                Route::post('save_form_data', 'TenantAdmin\FormController@save_form_data')->name('save_form_data');

                Route::post('toggle_form_eligibility', 'TenantAdmin\FormController@toggle_form_eligibility')->name('toggle_form_eligibility');

                Route::get('form/responses/{id}', 'TenantAdmin\FormController@form_responses')->name('form_responses');

                Route::post('delete_form', 'TenantAdmin\FormController@delete_form')->name('delete_form');

                Route::post('/save_sms_time_buffer', 'TenantAdmin\AutomatedCommunicationsController@saveSmsTimeBuffer')->name('save_sms_time_buffer');

                Route::get('/google_review_automated_communications', 'TenantAdmin\AutomatedCommunicationsController@googleReviewAutomatedCommunications')->name('google_review_automated_communications');

                Route::post('/save_auto_note_google_review', 'TenantAdmin\AutomatedCommunicationsController@saveAutoNoteGoogleReview')->name('save_auto_note_google_review');

                Route::post('/google_review_automated_communications_upload', 'TenantAdmin\AutomatedCommunicationsController@googleReviewAutomatedCommunicationsUploadCsv')->name('google_review_automated_communications_upload');

                Route::post('/update_auto_notes_occurence', 'TenantAdmin\AutomatedCommunicationsController@updateAutoNotesOccurenceStatus')->name('update_auto_notes_occurence');

                Route::post('/get_project_type_phaseList', 'TenantAdmin\AutomatedCommunicationsController@getProjectTypePhaseLists')->name('get_project_type_phaseList');

                Route::post('/process_auto_notes_phase_settings', 'TenantAdmin\AutomatedCommunicationsController@processAutoNotePhaseSettings')->name('process_auto_notes_phase_settings');

                Route::post('/process_auto_notes_phase_settings_save_all', 'TenantAdmin\AutomatedCommunicationsController@processAutoNotePhaseSettingsSaveAll')->name('process_auto_notes_phase_settings_save_all');

                Route::post('/phase_settings_add_all_phase_changes', 'TenantAdmin\AutomatedCommunicationsController@phaseSettingsAddPhaseChangesAll')->name('phase_settings_add_all_phase_changes');

                Route::post('/process_auto_notes_google_review_settings', 'TenantAdmin\AutomatedCommunicationsController@processAutoNoteGoogleReviewSettings')->name('process_auto_notes_google_review_settings');

                Route::post('/process_auto_notes_google_review_cities', 'TenantAdmin\AutomatedCommunicationsController@processAutoNoteGoogleReviewCities')->name('process_auto_notes_google_review_cities');

                Route::post('/phase_mapping_single_save', 'TenantAdmin\PhaseMappingController@phase_mapping_single_save')->name('phase_mapping_single_save');

                Route::get('users', 'TenantAdmin\UserController@users')->name('users');
                Route::post('users/invite', 'TenantAdmin\UserController@user_invite')->name('users.invite');
                Route::get('change_invite_role', 'TenantAdmin\UserController@change_invite_role')->name('change_invite_role');
                Route::get('delete_invite_role', 'TenantAdmin\UserController@delete_invite_role')->name('delete_invite_role');

                Route::post('get_project_vitals', 'TenantAdmin\PortalDisplaySettingController@project_vitals_by_project_type_id')->name('get_project_vitals');
                Route::post('project_vitals_save', 'TenantAdmin\PortalDisplaySettingController@project_vitals_post')->name('project_vitals_save');
                Route::post('save_tenant_portal_display_settings', 'TenantAdmin\PortalDisplaySettingController@save_tenant_portal_display_settings')->name('save_tenant_portal_display_settings');
                Route::post('get_current_project_vitals', 'TenantAdmin\PortalDisplaySettingController@current_project_vitals_by_project_type_id')->name('get_current_project_vitals');

                Route::post('delete_template', 'TenantAdmin\PhaseCategoriesController@deleteTemplate')->name('delete_template');

                Route::get('automated_workflow', 'TenantAdmin\AutomatedWorkflowController@index')->name('automated_workflow');
                Route::get('automated_workflow/get_project_type_list', 'TenantAdmin\AutomatedWorkflowController@getProjectTypeList')->name('automated_workflow_get_project_type_list');
                Route::get('automated_workflow/get_phase_list', 'TenantAdmin\AutomatedWorkflowController@getProjectTypePhaseList')->name('automated_workflow_get_phase_list');
                Route::get('automated_workflow/get_contact_metadata', 'TenantAdmin\AutomatedWorkflowController@getContactMetadata')->name('automated_workflow_get_contact_metadata');
                Route::get('automated_workflow/get_user_list', 'TenantAdmin\AutomatedWorkflowController@getUserList')->name('automated_workflow_get_user_list');
                Route::get('automated_workflow/get_project_type_section_list', 'TenantAdmin\AutomatedWorkflowController@getSectionListByProjectType')->name('automated_workflow_get_project_type_section_list');
                Route::get('automated_workflow/get_project_section_field', 'TenantAdmin\AutomatedWorkflowController@getProjectSectionField')->name('automated_workflow_get_project_section_field');
                Route::get('automated_workflow/get_project_type_collection_list', 'TenantAdmin\AutomatedWorkflowController@getCollectionListByProjectType')->name('automated_workflow_get_project_type_collection_list');
                Route::post('automated_workflow/save', 'TenantAdmin\AutomatedWorkflowController@save')->name('automated_workflow_save');
                Route::post('automated_workflow/delete', 'TenantAdmin\AutomatedWorkflowController@delete')->name('automated_workflow_delete');
                Route::get('automated_workflow/logs', 'TenantAdmin\AutomatedWorkflowController@webhookLog')->name('automated_workflow_logs');
                Route::post('automated_workflow/update_status', 'TenantAdmin\AutomatedWorkflowController@updateStatus')->name('automated_workflow_update_status');
                Route::post('automated_workflow/action/update_status', 'TenantAdmin\AutomatedWorkflowController@updateActionStatus')->name('automated_workflow_action_update_status');
                Route::post('automated_workflow/action/update_data', 'TenantAdmin\AutomatedWorkflowController@updateActionData')->name('automated_workflow_action_update_data');
                Route::post('automated_workflow/map/add', 'TenantAdmin\AutomatedWorkflowController@addActionMap')->name('automated_workflow_map_add');
                Route::post('automated_workflow/map/delete', 'TenantAdmin\AutomatedWorkflowController@deleteActionMap')->name('automated_workflow_map_delete');
                Route::post('automated_workflow/map/update', 'TenantAdmin\AutomatedWorkflowController@updateActionMap')->name('automated_workflow_map_update');
                Route::post('automated_workflow/action/update', 'TenantAdmin\AutomatedWorkflowController@updateActionInfo')->name('automated_workflow_action_update');
                Route::post('automated_workflow/trigger/updateactive', 'TenantAdmin\AutomatedWorkflowController@updateTriggerActive')->name('automated_workflow_trigger_updateactive');
                Route::post('automated_workflow/action/add', 'TenantAdmin\AutomatedWorkflowController@addAction')->name('automated_workflow_add_action');
                Route::get('automated_workflow/get_trigger_action', 'TenantAdmin\AutomatedWorkflowController@getTriggerActionList')->name('automated_workflow_get_trigger_action');
                Route::get('automated_workflow/get_role_list', 'TenantAdmin\AutomatedWorkflowController@getRoleList')->name('automated_workflow_get_role_list');
                Route::get('automated_workflow/get_phase_list', 'TenantAdmin\AutomatedWorkflowController@getPhaseList')->name('automated_workflow_get_phase_list');

                Route::get('/review_request_message_log', 'TenantAdmin\AutomatedCommunicationsController@getMessageLog')->name('review_request_message_log');

                Route::post('setting_update_notification_config', 'TenantAdmin\ClientPortalSetupController@updateNotificationConfig')->name('setting_update_notification_config');
                Route::post('update_show_archieved_phase', 'TenantAdmin\ClientPortalSetupController@update_show_archieved_phase')->name('update_show_archieved_phase');

                // adding routes for custom project naming
                Route::get('get_project_sections_cutom_project/{type_id}', 'TenantAdmin\ClientPortalSetupController@get_project_sections_cutom_project')->name('get_project_sections_cutom_project');
                Route::get('get_project_section_fields_cutom_project/{type_id}/{section_id}', 'TenantAdmin\ClientPortalSetupController@get_project_section_fields_cutom_project')->name('get_project_section_fields_cutom_project');
                Route::post('save_settings_cutom_project', 'TenantAdmin\ClientPortalSetupController@save_settings_cutom_project')->name('save_settings_cutom_project');
                Route::post('get_settings_cutom_project', 'TenantAdmin\ClientPortalSetupController@get_settings_cutom_project')->name('get_settings_cutom_project');

                Route::post('update_client_notification_status', 'TenantAdmin\BannerMessagesController@updateClientNotificationStatus')->name('update_client_notification_status');
                Route::get('banner_messages', 'TenantAdmin\BannerMessagesController@index')->name('banner_messages');

                Route::get('portal_display_settings', 'TenantAdmin\PortalDisplaySettingController@index')->name('portal_display_settings');

                Route::get('form_response_logs', 'TenantAdmin\FormController@formResponseLog')->name('form_response_logs');
                Route::get('form_response_logs_csv', 'TenantAdmin\FormController@formResponseLogCSV')->name('form_response_logs_csv');
                Route::get('forms/get_project_type_section_list', 'TenantAdmin\FormController@getSectionListByProjectType')->name('forms_get_project_type_section_list');
                Route::get('forms/get_project_section_field', 'TenantAdmin\FormController@getProjectSectionField')->name('forms_get_project_section_field');

                Route::get('client_file_upload_config', 'TenantAdmin\DocumentUploadController@index')->name('client_file_upload_config');
                Route::post('update_client_file_upload_config', 'TenantAdmin\DocumentUploadController@update_client_file_upload_config')->name('update_client_file_upload_config');
                Route::post('add_choice_client_file_upload_config', 'TenantAdmin\DocumentUploadController@add_choice_client_file_upload_config')->name('add_choice_client_file_upload_config');
                Route::post('get_choices_client_file_upload_config', 'TenantAdmin\DocumentUploadController@get_choices_client_file_upload_config')->name('get_choices_client_file_upload_config');
                Route::post('delete_choice_client_file_upload_config', 'TenantAdmin\DocumentUploadController@delete_choice_client_file_upload_config')->name('delete_choice_client_file_upload_config');
                Route::get('get_project_section_client_file_upload_config/{type_id}/{handle_id}', 'TenantAdmin\DocumentUploadController@get_project_section_client_file_upload_config')->name('get_project_section_client_file_upload_config');
                Route::get('get_project_section_field_client_file_upload_config/{type_id}/{section_id}', 'TenantAdmin\DocumentUploadController@get_project_section_field_client_file_upload_config')->name('get_project_section_field_client_file_upload_config');
                Route::post('add_scheme_client_file_upload_config', 'TenantAdmin\DocumentUploadController@add_scheme_client_file_upload_config')->name('add_scheme_client_file_upload_config');
                Route::post('get_mapped_choices_client_file_upload_config', 'TenantAdmin\DocumentUploadController@get_mapped_choices_client_file_upload_config')->name('get_mapped_choices_client_file_upload_config');
                Route::post('delete_mapped_choice_client_file_upload_config', 'TenantAdmin\DocumentUploadController@delete_mapped_choice_client_file_upload_config')->name('delete_mapped_choice_client_file_upload_config');
                Route::post('get_choice_detail_client_file_upload_config', 'TenantAdmin\DocumentUploadController@get_choice_detail_client_file_upload_config')->name('get_choice_detail_client_file_upload_config');

                Route::get('default_contact/get_contact_metadata', 'TenantAdmin\ClientPortalSetupController@getContactMetadata')->name('default_contact_get_contact_metadata');
                Route::post('save_default_contact', 'TenantAdmin\ClientPortalSetupController@saveDefaultContact')->name('save_default_contact');

                Route::get('versions', 'TenantAdmin\VersionController@index')->name('versions');
                Route::get('filter_versions', 'TenantAdmin\VersionController@filterVersions')->name('filterVersions');
                Route::post('banner_messages/upload', 'TenantAdmin\BannerMessagesController@uploadImage')->name('banner_messages_upload');

                Route::get('variables', 'TenantAdmin\VariableController@index')->name('variables');


                Route::get('calendar', 'TenantAdmin\CalendarController@index')->name('calendar');
                Route::get('calendar/get_project_type_section_list', 'TenantAdmin\CalendarController@getSectionListByProjectType')->name('calendar_get_project_type_section_list');
                Route::get('calendar/get_project_type_section_field_list', 'TenantAdmin\CalendarController@getProjectSectionField')->name('calendar_get_project_type_section_field_list');
                Route::post('calendar/save_setting', 'TenantAdmin\CalendarController@save')->name('calendar_save_setting');

                Route::post('setting_update_sms_line_toggle', 'TenantAdmin\ClientPortalSetupController@updateSmsLineToggle')->name('setting_update_sms_line_toggle');
                Route::post('save_sms_line_config', 'TenantAdmin\ClientPortalSetupController@saveSmsLineConfig')->name('save_sms_line_config');

                Route::post('add_reply_to_org_email', 'TenantAdmin\ClientPortalSetupController@addReplyToOrgEmail')->name('add_reply_to_org_email');

                Route::post('update_default_phase_mapping', 'TenantAdmin\PhaseMappingController@updateDefaultPhaseMapping')->name('update_default_phase_mapping');

                Route::post('save_tenant_test_number_settings', 'TenantAdmin\PortalDisplaySettingController@save_tenant_test_number_settings')->name('save_tenant_test_number_settings');

                // media locker routes
                Route::get('media_locker', 'TenantAdmin\MedialockerController@media_locker')->name('media_locker');
                Route::post('media_locker/save', 'TenantAdmin\MedialockerController@save')->name('media_locker.save');
                Route::get('media_locker/delete', 'TenantAdmin\MedialockerController@delete')->name('media_locker.delete');


                Route::post('update_timeline_mapping_config', 'TenantAdmin\PhaseCategoriesController@updateTimelineMappingConfig')->name('update_timeline_mapping_config');

                Route::post('add_all_phase_mapping', 'TenantAdmin\PhaseMappingController@addAllPhaseMapping')->name('add_all_phase_mapping');

                Route::post('legalteam_person', 'TenantAdmin\PortalDisplaySettingController@legalteam_person')->name('legalteam_person');

                Route::post('update_config_hashtag', 'TenantAdmin\DocumentUploadController@updateConfigHashtag')->name('update_config_hashtag');
                Route::get('document/download_uploaded_file', 'TenantAdmin\DocumentUploadController@downloadUploadedFile')->name('document_download_uploaded_file');

                Route::post('dashboard/delete/failed_submit_log', 'TenantAdmin\DashboardController@deleteFailedSubmitLog')->name('dashboard_delete_failed_submit_log');

                Route::get('forms/get_project_list_by_project_type', 'TenantAdmin\FormController@getProjectListByProjectType')->name('forms_get_project_list_by_project_type');

                Route::get('variable/get_project_type', 'TenantAdmin\VariableController@getProjectType')->name('variable_get_project_type');
                Route::get('variable/get_section', 'TenantAdmin\VariableController@getSection')->name('variable_get_section');
                Route::get('variable/get_field', 'TenantAdmin\VariableController@getField')->name('variable_get_field');
                Route::post('variable/add', 'TenantAdmin\VariableController@addVariable')->name('variable_add');
                Route::post('variable/update_permission', 'TenantAdmin\VariableController@updateVariablePermission')->name('variable_update_permission');
                Route::post('variable/delete/{id}', 'TenantAdmin\VariableController@deleteVariable')->name('variable_delete');
                Route::post('variable/update_active', 'TenantAdmin\VariableController@updateActive')->name('variable_update_active');

                // mass emails routes
                Route::get('/mass_emails', 'TenantAdmin\MassEmailsController@index')->name('mass_emails');
                Route::get('/mass_emails_fetch_contacts ', 'TenantAdmin\MassEmailsController@fetch_contacts')->name('mass_emails_fetch_contacts');
                Route::get('/mass_emails_logs/{id} ', 'TenantAdmin\MassEmailsController@mass_emails_logs')->name('mass_emails_logs');
                Route::get('/mass_emails_exportcsv/{id} ', 'TenantAdmin\MassEmailsController@massEmailsExportcsv')->name('mass_emails_exportcsv');
                Route::get('mass_emails_custom_logs', 'TenantAdmin\MassEmailsController@massEmailsCustomLog')->name('mass_emails_custom_logs');
                Route::get('mass_emails_custom_logs_csv', 'TenantAdmin\MassEmailsController@massEmailsCustomLogCSV')->name('mass_emails_custom_logs_csv');
                Route::post('/mass_emails_send_messages ', 'TenantAdmin\MassEmailsController@send_email_messages')->name('mass_emails_send_messages');
                Route::post('mass_emails_upload_csv', 'TenantAdmin\MassEmailsController@upload_csv')->name('mass_emails_upload_csv');
                Route::post('mass_emails_delete', 'TenantAdmin\MassEmailsController@massEmailsDelete')->name('mass_emails_delete');
                Route::post('mass_emails_recreate_job', 'TenantAdmin\MassEmailsController@reCreateJob')->name('mass_emails_recreate_job');
            });

            // testing route
            Route::get('/test', 'TenantAdmin\TestController@test')->name('test');
            Route::get('/test/api_token', 'TenantAdmin\TestController@test_api')->name('test_api');
        });
    });
});
