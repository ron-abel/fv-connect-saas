<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log as Logging;


class SlackServices
{

    /**
     * Send Confirm Registration Message by Slack Webhook
     */

    public function sendMessage($tenant_owner)
    {
        try {
            $slack_route = config('services.slack');
            if (isset($tenant_owner->full_name, $tenant_owner->tenant->tenant_name) && $slack_route) {
                $msg_data = [
                    "text" => "Name: " . $tenant_owner->full_name . " , Tenant: " . $tenant_owner->tenant->tenant_name . " , Email: " . $tenant_owner->email . "\n A new tenant has been registered.",
                    "blocks" => [
                        [
                            "type" => "header",
                            "text" => [
                                "type" => "plain_text",
                                "text" => "VineConnect New Registration"
                            ]
                        ],
                        [
                            "type" => "section",
                            "text" => [
                                "type" => "mrkdwn",
                                "text" => "*Name*: " . $tenant_owner->full_name . "\n*Tenant:* " . $tenant_owner->tenant->tenant_name . "\n*Email:* " . $tenant_owner->email . "\n<https://vineconnect.vinetegrate.com/admin/tenants|Super Admin Tenant Dashboard>"
                            ]
                        ]
                    ]
                ];
                $response = Http::post($slack_route, $msg_data);
            }
        } catch (Exception $e) {
            $exception_json = json_encode($e);
            Logging::warning($exception_json);
            return $e->getMessage();
        }
    }

    /**
     * Send Schedule to Go Live Message by Slack Webhook
     */

    public function sendScheduleMessage($tenant_owner, $scheduled_at)
    {
        try {
            $slack_route = config('services.slack');

            if (isset($tenant_owner->full_name, $tenant_owner->tenant->tenant_name) && $slack_route) {
                $msg_data = [
                    "text" => "Name: " . $tenant_owner->full_name . " , Tenant: " . $tenant_owner->tenant->tenant_name . "\n Scheduled at {$scheduled_at}.",
                    "blocks" => [
                        [
                            "type" => "header",
                            "text" => [
                                "type" => "plain_text",
                                "text" => "VineConnect New Scheduled"
                            ]
                        ],
                        [
                            "type" => "section",
                            "text" => [
                                "type" => "mrkdwn",
                                "text" => "*Name*: " . $tenant_owner->full_name . "\n*Tenant:* " . $tenant_owner->tenant->tenant_name . ": Scheduled at {$scheduled_at}"
                            ]
                        ]
                    ]
                ];
                $response = Http::post($slack_route, $msg_data);
            }
        } catch (Exception $e) {
            $exception_json = json_encode($e);
            Logging::warning($exception_json);
            return $e->getMessage();
        }
    }

    /**
     * Send New Contact Creation Message by Slack Webhook
     */

    public function sendContactMessage($contact)
    {
        try {
            $slack_route = config('services.slack');
            if (isset($contact['personId'], $contact['fullname'], $contact['links']) && $slack_route) {
                $msg_data = [
                    "text" => "Name: " . $contact['fullname'] . " , Link: " . $contact['links']['self'] . "\n A new contact has been created on tenant registration.",
                    "blocks" => [
                        [
                            "type" => "header",
                            "text" => [
                                "type" => "plain_text",
                                "text" => "VineConnect New Contact Creation"
                            ]
                        ],
                        [
                            "type" => "section",
                            "text" => [
                                "type" => "mrkdwn",
                                "text" => "*Name*: " . $contact['fullname'] . "\n*Link:* " . $contact['links']['self']
                            ]
                        ]
                    ]
                ];
                $response = Http::post($slack_route, $msg_data);
            }
        } catch (Exception $e) {
            $exception_json = json_encode($e);
            Logging::warning($exception_json);
            return $e->getMessage();
        }
    }


    /**
     * Send New Project Creation Message by Slack Webhook
     */

    public function sendProjectCreationMessage($project)
    {
        try {
            $slack_route = config('services.slack');
            if (isset($project->projectName, $project->projectUrl, $project->clientName) && $slack_route) {
                $msg_data = [
                    "text" => "Project Name: " . $project->projectName . " , Client Name: " . $project->clientName . " , URL: " . $project->projectUrl . "\n A new project has been created on tenant registration.",
                    "blocks" => [
                        [
                            "type" => "header",
                            "text" => [
                                "type" => "plain_text",
                                "text" => "VineConnect New Project Creation"
                            ]
                        ],
                        [
                            "type" => "section",
                            "text" => [
                                "type" => "mrkdwn",
                                "text" => "*Project Name*: " . $project->projectName . "\n*Client Name:* " . $project->clientName . "\n*URL:* " . $project->projectUrl
                            ]
                        ]
                    ]
                ];
                $response = Http::post($slack_route, $msg_data);
            }
        } catch (Exception $e) {
            $exception_json = json_encode($e);
            Logging::warning($exception_json);
            return $e->getMessage();
        }
    }
}
