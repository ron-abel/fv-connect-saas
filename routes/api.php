<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::domain(config('app.superadmin') . '.' . config('app.domain'))->group(function () {

    Route::group(['prefix' => 'v1/twilio/'], function () {
        Route::post('send2faAuthCode', 'API\TwilioAuthController@send2faAuthCode');

        Route::post('validate2faAuthCode', 'API\TwilioAuthController@validate2faAuthCode');
    });

    Route::group(['prefix' => 'v1/webhook/'], function () {
        Route::post('handle_inbound_message', 'API\WebhookController@handle_inbound_message');

        Route::post('handle_google_review_check_inbound_message', 'API\WebhookController@handle_google_review_check_inbound_message');
    });
});

Route::domain('{subdomain}.' . config('app.domain'))->group(function () {
    Route::get('/validate/coupon/{coupon}', 'TenantAdmin\BillingController@validateCoupon');
    Route::group(['prefix' => 'v1/webhook/'], function () {
        Route::post('contact_created', 'API\WebhookController@contact_created');

        Route::post('project_created', 'API\WebhookController@project_created');

        Route::post('phase_changed', 'API\WebhookController@phase_changed');

        Route::post('collectionitem_created', 'API\WebhookController@collectionItemCreated');

        Route::post('task_created', 'API\WebhookController@taskCreated');

        Route::post('send_notification_phase_changed', 'API\WebhookController@send_notification_phase_changed');

        Route::post('automated_workflow_project_created', 'API\AutomatedWorkflowWebhookController@projectCreated');
        Route::post('automated_workflow_project_updated', 'API\AutomatedWorkflowWebhookController@projectUpdated');
        Route::post('automated_workflow_project_phase_changed', 'API\AutomatedWorkflowWebhookController@projectPhaseChanged');
        Route::post('automated_workflow_project_related', 'API\AutomatedWorkflowWebhookController@projectRelated');
        Route::post('automated_workflow_project_unrelated', 'API\AutomatedWorkflowWebhookController@projectUnrelated');

        Route::post('automated_workflow_contact_created', 'API\AutomatedWorkflowWebhookController@contactCreated');
        Route::post('automated_workflow_contact_updated', 'API\AutomatedWorkflowWebhookController@contactUpdated');

        Route::post('automated_workflow_note_created', 'API\AutomatedWorkflowWebhookController@noteCreated');
        Route::post('automated_workflow_note_completed', 'API\AutomatedWorkflowWebhookController@noteCompleted');

        Route::post('automated_workflow_collection_item_created', 'API\AutomatedWorkflowWebhookController@collectionItemCreated');
        Route::post('automated_workflow_collection_item_deleted', 'API\AutomatedWorkflowWebhookController@collectionItemDeleted');

        Route::post('automated_workflow_appointment_created', 'API\AutomatedWorkflowWebhookController@appointmentCreated');
        Route::post('automated_workflow_appointment_updated', 'API\AutomatedWorkflowWebhookController@appointmentUpdated');
        Route::post('automated_workflow_appointment_deleted', 'API\AutomatedWorkflowWebhookController@appointmentDeleted');

        Route::post('automated_workflow_section_toggle', 'API\AutomatedWorkflowWebhookController@sectionToggle');

        Route::post('automated_workflow_taskflow_executed', 'API\AutomatedWorkflowWebhookController@taskflowExecuted');
        Route::post('automated_workflow_taskflow_reset', 'API\AutomatedWorkflowWebhookController@taskflowReset');

        Route::post('document_updated', 'API\DocumentWebhookController@documentUpdated');
    });
});
