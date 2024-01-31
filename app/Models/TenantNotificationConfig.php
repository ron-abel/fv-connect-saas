<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantNotificationConfig extends Model
{
    use HasFactory;

    public const FeedbackReceived = 1;
    public const ContactInfoUpdated = 2;
    public const SMSResponse = 3;
    public const TeamMessageResponse = 4;
    public const WorkflowExecuted = 5;
    public const UnsuccessfulLogin = 6;
    public const GoogleReviewThreshold = 7;
    public const DocumentUploaded = 8;
    public const FormSubmission = 9;

    public static function getStaticEventList()
    {
        return [
            [
                'event_short_code' => TenantNotificationConfig::FeedbackReceived,
                'event_name' => 'Feedback Received'
            ],
            [
                'event_short_code' => TenantNotificationConfig::ContactInfoUpdated,
                'event_name' => 'Contact Info Updated'
            ],
            [
                'event_short_code' => TenantNotificationConfig::SMSResponse,
                'event_name' => 'SMS Response'
            ],
            [
                'event_short_code' => TenantNotificationConfig::TeamMessageResponse,
                'event_name' => 'Team Message Response'
            ],
            [
                'event_short_code' => TenantNotificationConfig::WorkflowExecuted,
                'event_name' => 'Workflow Executed Successfully'
            ],
            [
                'event_short_code' => TenantNotificationConfig::UnsuccessfulLogin,
                'event_name' => '2+ Unsuccessful Client Portal Login Attempts'
            ],
            [
                'event_short_code' => TenantNotificationConfig::GoogleReviewThreshold,
                'event_name' => 'Google Review Threshold Met'
            ],
            [
                'event_short_code' => TenantNotificationConfig::DocumentUploaded,
                'event_name' => 'Document Uploaded'
            ],
            [
                'event_short_code' => TenantNotificationConfig::FormSubmission,
                'event_name' => 'Form Submission'
            ]
        ];
    }
}
