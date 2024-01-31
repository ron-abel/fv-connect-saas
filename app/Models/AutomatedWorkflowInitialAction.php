<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AutomatedWorkflowInitialAction extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    public $timestamps = true;


    public static function getStaticActionList()
    {
        return [
            [
                'action_short_code' => '1',
                'action_name' => 'Send SMS Message',
                'action_description' => 'Send SMS message to client',
                'is_active' => 1,
                'order_by' => 1
            ],
            [
                'action_short_code' => '2',
                'action_name' => 'Add Client to Blacklist',
                'action_description' => 'Add client to Blacklist',
                'is_active' => 1,
                'order_by' => 14
            ],
            [
                'action_short_code' => '3',
                'action_name' => 'Create a Note on Project',
                'action_description' => 'Create a note on project',
                'is_active' => 1,
                'order_by' => 4
            ],
            [
                'action_short_code' => '4',
                'action_name' => 'Create a Task on Project',
                'action_description' => 'Create a task on project',
                'is_active' => 1,
                'order_by' => 5
            ],
            [
                'action_short_code' => '5',
                'action_name' => 'Send Admin Notification Email',
                'action_description' => 'Send admin notification email',
                'is_active' => 1,
                'order_by' => 3
            ],
            [
                'action_short_code' => '6',
                'action_name' => 'Add Project Hashtag',
                'action_description' => 'Add project hashtag',
                'is_active' => 1,
                'order_by' => 7
            ],
            [
                'action_short_code' => '7',
                'action_name' => 'Add Client Hashtag',
                'action_description' => 'Add Client Hashtag',
                'is_active' => 1,
                'order_by' => 8
            ],
            [
                'action_short_code' => '8',
                'action_name' => 'Toggle Section Visibility',
                'action_description' => 'Toggle Section Visibility',
                'is_active' => 1,
                'order_by' => 9
            ],
            [
                'action_short_code' => '9',
                'action_name' => 'Kill All Tasks',
                'action_description' => 'Kill All Tasks',
                'is_active' => 1,
                'order_by' => 6
            ],
            [
                'action_short_code' => '10',
                'action_name' => 'Change Project Phase',
                'action_description' => 'Change Project Phase',
                'is_active' => 1,
                'order_by' => 10
            ],
            [
                'action_short_code' => '11',
                'action_name' => 'Send to Webhook',
                'action_description' => 'Send to Webhook',
                'is_active' => 1,
                'order_by' => 12
            ],
            [
                'action_short_code' => '12',
                'action_name' => 'Send Email',
                'action_description' => 'Send Email',
                'is_active' => 1,
                'order_by' => 2
            ],
            [
                'action_short_code' => '13',
                'action_name' => 'Mirror a Field',
                'action_description' => 'Mirror a Field',
                'is_active' => 1,
                'order_by' => 13
            ],
            [
                'action_short_code' => '14',
                'action_name' => 'Update Project Team',
                'action_description' => 'Update Project Team',
                'is_active' => 1,
                'order_by' => 11
            ],
        ];
    }
}
