<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\LegalteamConfig;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as Logging;

class SendGridServices
{

    protected $_reply_to_email = "support@vinetegrate.com";
    protected $_reply_to_name = "VineConnect";
    protected $_logo_image = "";
    protected $_tenant_name = "";
    protected $_law_firm_name = "";

    public function __construct($tenant_id = null)
    {
        if (!empty($tenant_id)) {

            $tenant = Tenant::find($tenant_id);

            $config_details = DB::table('config')
                ->where('tenant_id', $tenant_id)
                ->first();

            $user = User::where('user_role_id', USER::TENANT_OWNER)->where('tenant_id', $tenant_id)->first();

            if (isset($tenant->tenant_name)) {
                if (isset($config_details->reply_to_org_email) && !empty($config_details->reply_to_org_email)) {
                    $this->_reply_to_email = $config_details->reply_to_org_email;
                    $this->_reply_to_name = !empty($tenant->tenant_law_firm_name) ? $tenant->tenant_law_firm_name : $tenant->tenant_name;
                } else if (isset($user->email) && !empty($user->email)) {
                    $this->_reply_to_email = $user->email;
                    $this->_reply_to_name = !empty($tenant->tenant_law_firm_name) ? $tenant->tenant_law_firm_name : $tenant->tenant_name;
                }
            }

            if (!empty($config_details->logo)) {
                $this->_logo_image = url('assets/uploads/client_logo/' . $config_details->logo);
            } else {
                $this->_logo_image = url('assets/img/client/vineconnect_logo.png');
            }

            if (isset($tenant->tenant_name)) {
                $this->_tenant_name = $tenant->tenant_name;
                $this->_law_firm_name = !empty($tenant->tenant_law_firm_name) ? $tenant->tenant_law_firm_name : $tenant->tenant_name;
            }
        }
    }

    /**
     * Send Confirm Registration Email by Sendgrid template.
     */
    public function sendConfirmRegistration($tenant_owner, $tenant_obj, $create_by_super_admin = 1)
    {
        try {
            $subdomain = isset($tenant_obj['tenant_name']) ? $tenant_obj['tenant_name'] : config('app.superadmin');
            $link =  'https://' . $subdomain . '.' . config('app.domain') . '/verify/email/' . $tenant_owner->remember_token;
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom(config('services.sendgrid.from_mail'), config('services.sendgrid.from_name'));

            $to_mail = $tenant_owner->email ?? "";
            $to_name = $tenant_owner->full_name ?? "";

            if (env('APP_ENV') == 'local') {
                $to_mail = config('services.sendgrid.test-target-mail');
            }

            if ($to_mail == '' || $to_mail == null) {
                // logging
                $msg_obj = [
                    'SendGrid Mail Invalid=>  ',
                    $tenant_owner,
                    $tenant_obj,
                    __FILE__, __LINE__
                ];
                Logging::warning(json_encode($msg_obj));

                return $msg_obj;
            }

            $email->addTo($to_mail, $to_name);


            $email->setTemplateId(
                new \SendGrid\Mail\TemplateId(config('services.sendgrid.register_template'))
            );

            $dynamic_data = [
                'user_name'     => $tenant_owner->full_name,
                'user_email'    => $tenant_owner->email,
                'tenant_name' => $tenant_obj['tenant_name'],
                'law_firm_name' => $tenant_obj['tenant_name'],
                'client_fullname' => '',
                'client_firstname' => '',
                'logo_image' => '',
                'verify_link' => $link,
            ];

            if ($create_by_super_admin == 1) {
                // only when thenant is created by super admin, we will send password info.
                $admin_password = $tenant_obj['tenant_name'] . 'password';
                $dynamic_data['password'] = $admin_password;
            }

            $email->addDynamicTemplateDatas($dynamic_data);

            // logging
            $msg_obj = ['SendGrid =>  ' . json_encode($dynamic_data), 'to_mail => ' . $to_mail];
            Logging::warning(json_encode($msg_obj));

            $sendgrid = new \SendGrid(config('services.sendgrid.key'));
            $response = $sendgrid->send($email);
        } catch (Exception $e) {
            $msg_obj = [
                'SendGrid Failed =>  ' . $e->getMessage(),
                $tenant_owner, $tenant_obj,
                __FILE__, __LINE__
            ];
            Logging::warning(json_encode($msg_obj));

            return $e->getMessage();
        }
    }

    /**
     * Resend verification to unverifies tenants.
     */
    public function resendVerification($tenant, $token, $create_by_super_admin = 1)
    {
        try {
            $subdomain = $tenant->tenant_name ? $tenant->tenant_name : config('app.superadmin');
            $link =  'https://' . $subdomain . '.' . config('app.domain') . '/verify/email/' . $token;
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom(config('services.sendgrid.from_mail'), config('services.sendgrid.from_name'));

            $to_mail = $tenant->email ?? "";
            $to_name = $tenant->full_name ?? "";

            if (env('APP_ENV') == 'local') {
                $to_mail = config('services.sendgrid.test-target-mail');
            }

            if ($to_mail == '' || $to_mail == null) {
                // logging
                $msg_obj = [
                    'SendGrid Mail Invalid=>  ',
                    $tenant,
                    __FILE__, __LINE__
                ];
                Logging::warning(json_encode($msg_obj));
                return $msg_obj;
            }

            $email->addTo($to_mail, $to_name);


            $email->setTemplateId(
                new \SendGrid\Mail\TemplateId(config('services.sendgrid.register_template'))
            );

            $dynamic_data = [
                'user_name'     => $tenant->full_name,
                'user_email'    => $tenant->email,
                'tenant_name'   => $tenant->tenant_name,
                'law_firm_name' => $tenant->tenant_name,
                'client_fullname' => '',
                'client_firstname' => '',
                'logo_image' => '',
                'verify_link'   => $link,
            ];
            if ($create_by_super_admin == 1) {
                // only when thenant is created by super admin, we will send password info.
                $admin_password = $tenant->tenant_name . 'password';
                $dynamic_data['password'] = $admin_password;
            }

            $email->addDynamicTemplateDatas($dynamic_data);

            // logging
            $msg_obj = ['SendGrid =>  ' . json_encode($dynamic_data), 'to_mail => ' . $to_mail];
            Logging::warning(json_encode($msg_obj));

            $sendgrid = new \SendGrid(config('services.sendgrid.key'));
            $response = $sendgrid->send($email);
        } catch (Exception $e) {
            $msg_obj = [
                'SendGrid Failed =>  ' . $e->getMessage(),
                $tenant,
                __FILE__, __LINE__
            ];
            Logging::warning(json_encode($msg_obj));

            return $e->getMessage();
        }
    }

    /**
     * Send Mail after confirmed registration
     */
    public function sendReigstrationSuccessMail($tenant_owner)
    {
        try {
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom(config('services.sendgrid.from_mail'), config('services.sendgrid.from_name'));

            $to_mail = $tenant_owner->email ?? "";
            $to_name = $tenant_owner->full_name ?? "";

            if (env('APP_ENV') == 'local') {
                $to_mail = config('services.sendgrid.test-target-mail');
            }

            if ($to_mail == '' || $to_mail == null) {
                // logging
                $msg_obj = [
                    'SendGrid Mail Invalid=>  ',
                    $tenant_owner,
                    __FILE__, __LINE__
                ];
                Logging::warning(json_encode($msg_obj));
                return $msg_obj;
            }

            $email->addTo($to_mail, $to_name);

            $dynamic_data = [
                'user_name'     => $tenant_owner->full_name,
                'user_email'    => $tenant_owner->email,
                'tenant_name' => $tenant_owner->tenant->tenant_name,
                'law_firm_name' => $tenant_owner->tenant->tenant_name,
                'client_fullname' => '',
                'client_firstname' => '',
                'logo_image' => '',
            ];

            $email->addDynamicTemplateDatas($dynamic_data);
            $email->setTemplateId(
                new \SendGrid\Mail\TemplateId(config('services.sendgrid.confirmed_register_template'))
            );

            // logging
            $msg_obj = ['SendGrid =>  ' . json_encode($dynamic_data), 'to_mail => ' . $to_mail];
            Logging::warning(json_encode($msg_obj));

            $sendgrid = new \SendGrid(config('services.sendgrid.key'));
            $response = $sendgrid->send($email);
        } catch (Exception $e) {
            $msg_obj = [
                'SendGrid Failed =>  ' . $e->getMessage(),
                $tenant_owner,
                __FILE__, __LINE__
            ];
            Logging::warning(json_encode($msg_obj));

            return $e->getMessage();
        }
    }

    /**
     * Send Mail after Tenant plan upgrade
     */
    public function sendTenantUpgradePlanMail($user_name, $user_email, $tenant_name, $new_plan_name)
    {
        try {
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom(config('services.sendgrid.from_mail'), config('services.sendgrid.from_name'));

            $to_mail = $user_email ?? "";

            if (env('APP_ENV') == 'local') {
                $to_mail = config('services.sendgrid.test-target-mail');
            }

            if ($to_mail == '' || $to_mail == null) {
                // logging
                $msg_obj = [
                    'SendGrid Mail Invalid=>  ',
                    $user_email, $tenant_name,
                    __FILE__, __LINE__
                ];
                Logging::warning(json_encode($msg_obj));
                return $msg_obj;
            }

            $email->addTo($to_mail, $user_name);

            $dynamic_data = [
                'user_name'     => $user_name,
                'user_email'    => $user_email,
                'tenant_name' => $tenant_name,
                'new_plan_name' => $new_plan_name,
                'logo_image' => $this->_logo_image,
                'law_firm_name' => $this->_law_firm_name,
                'client_fullname' => '',
                'client_firstname' => '',
            ];

            if (!empty($this->_reply_to_email)) {
                $email->setReplyTo($this->_reply_to_email, $this->_reply_to_name);
            }

            $email->addDynamicTemplateDatas($dynamic_data);
            $email->setTemplateId(
                new \SendGrid\Mail\TemplateId(config('services.sendgrid.change_subscription_plan_template'))
            );

            // logging
            $msg_obj = ['SendGrid =>  ' . json_encode($dynamic_data), 'to_mail => ' . $to_mail];
            Logging::warning(json_encode($msg_obj));

            $sendgrid = new \SendGrid(config('services.sendgrid.key'));
            $response = $sendgrid->send($email);
        } catch (Exception $e) {
            $msg_obj = [
                'SendGrid Failed =>  ' . $e->getMessage(),
                $user_name, $user_email, $tenant_name,
                __FILE__, __LINE__
            ];
            Logging::warning(json_encode($msg_obj));

            return $e->getMessage();
        }
    }

    /**
     * Send Mail after client feedback
     */
    public function sendClientFeedbackSuccessMail($feedback_data, $feedback_body_str = "", $tenant_id = null)
    {
        try {
            if ($tenant_id == null) return null;
            $to_mail = $feedback_data['legal_team_email'] ?? "";
            $to_name = $feedback_data['legal_team_name'] ?? "";
            if (env('APP_ENV') == 'local') {
                $to_mail = config('services.sendgrid.test-target-mail');
            }

            if ($to_mail == '' || $to_mail == null) {
                // logging
                $msg_obj = [
                    'SendGrid Mail Invalid=>  ',
                    $feedback_data,
                    __FILE__, __LINE__
                ];
                Logging::warning(json_encode($msg_obj));
                return $msg_obj;
            }

            $manager_legal_team_config = LegalteamConfig::where('tenant_id', $tenant_id)
                ->where('type', LegalteamConfig::TYPE_STATIC)
                ->where('role_title', (LegalteamConfig::$legalteam_config_types)['ClientRelationsManager'])
                ->first();

            $dynamic_data = [
                'project_id'            => $feedback_data['project_id'],
                'project_name'          => $feedback_data['project_name'],
                'project_phase'         => $feedback_data['project_phase'],

                'legal_team_email'      => $feedback_data['legal_team_email'],
                'legal_team_phone'      => $feedback_data['legal_team_phone'],
                'legal_team_name'       => $feedback_data['legal_team_name'],

                'client_name'           => $feedback_data['client_name'],
                'client_phone'          => $feedback_data['client_phone'],

                'fd_mark_legal_service' => $feedback_data['fd_mark_legal_service'],
                'fd_mark_recommend'     => $feedback_data['fd_mark_recommend'],
                'fd_mark_useful'        => $feedback_data['fd_mark_useful'],
                'fd_content'            => $feedback_data['fd_content'],
                'feedback_body_str'     => $feedback_body_str,
                'logo_image' => $this->_logo_image,
                'law_firm_name' => $this->_law_firm_name,
                'tenant_name' => $this->_tenant_name,
                'client_fullname' => $feedback_data['client_name'],
                'client_firstname' => $feedback_data['client_name'],
            ];

            // send mail to legal team member
            return $this->sendEmailOfFeedback($to_mail, $to_name, $dynamic_data);
        } catch (Exception $e) {
            $msg_obj = [
                'SendGrid Failed =>  ' . $e->getMessage(),
                $feedback_data, $tenant_id,
                __FILE__, __LINE__
            ];
            Logging::warning(json_encode($msg_obj));

            return $e->getMessage();
        }
    }


    /**
     * Send Mail function after client feedback
     */
    public function sendEmailOfFeedback($to_mail, $to_name, $dynamic_data)
    {
        try {
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom(config('services.sendgrid.from_mail'), config('services.sendgrid.from_name'));
            if (env('APP_ENV') == 'local') {
                $to_mail = config('services.sendgrid.test-target-mail');
            }

            if ($to_mail == '' || $to_mail == null) {
                // logging
                $msg_obj = [
                    'SendGrid Mail Invalid=>  ',
                    $to_mail, $dynamic_data,
                    __FILE__, __LINE__
                ];
                Logging::warning(json_encode($msg_obj));
                return $msg_obj;
            }

            if (!empty($this->_reply_to_email)) {
                $email->setReplyTo($this->_reply_to_email, $this->_reply_to_name);
            }

            $email->addTo($to_mail, $to_name);
            $email->addDynamicTemplateDatas($dynamic_data);
            $email->setTemplateId(
                new \SendGrid\Mail\TemplateId(config('services.sendgrid.client_feedback_template'))
            );

            // logging
            $msg_obj = ['SendGrid =>  ' . json_encode($dynamic_data), 'to_mail => ' . $to_mail];
            Logging::warning(json_encode($msg_obj));

            $sendgrid = new \SendGrid(config('services.sendgrid.key'));
            $response = $sendgrid->send($email);
            return $response;
        } catch (Exception $e) {
            $msg_obj = [
                'SendGrid Failed =>  ' . $e->getMessage(),
                $to_mail, $to_name, $dynamic_data,
                __FILE__, __LINE__
            ];
            Logging::warning(json_encode($msg_obj));

            return $e->getMessage();
        }
    }

    /**
     * Send Admin Login Note by Sendgrid template.
     */
    public function sendAdminLoginNote($user, $ip, $device, $tenant_name = "", $tenant_id = null)
    {
        try {
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom(config('services.sendgrid.from_mail'), config('services.sendgrid.from_name'));

            $to_mail = $user->email ?? "";
            $to_name = $user->full_name ?? "";

            if (env('APP_ENV') == 'local') {
                $to_mail = config('services.sendgrid.test-target-mail');
            }

            if ($to_mail == '' || $to_mail == null) {
                // logging
                $msg_obj = [
                    'SendGrid Mail Invalid=>  ',
                    $user,
                    $tenant_name,
                    __FILE__, __LINE__
                ];
                Logging::warning(json_encode($msg_obj));

                return $msg_obj;
            }

            $email->addTo($to_mail, $to_name);


            $email->setTemplateId(
                new \SendGrid\Mail\TemplateId(config('services.sendgrid.admin_login_note_template'))
            );

            $token = Str::random(40);
            $user->admin_token = $token;
            $user->save();

            if ($tenant_id) {
                $link = route('admin.login', ['subdomain' => $tenant_name, 'token' => $token]);
            } else {
                $link = route('super.login', ['token' => $token]);
            }

            $dynamic_data = [
                'user_name'     => $user->full_name,
                'user_email'    => $user->email,
                'ip'            => $ip,
                'device'        => $device,
                'tenant_name' => $tenant_name,
                'verify_link' => $link,
                'logo_image' => $this->_logo_image,
                'law_firm_name' => $this->_law_firm_name,
                'client_fullname' => '',
                'client_firstname' => '',
            ];

            if (!empty($this->_reply_to_email)) {
                $email->setReplyTo($this->_reply_to_email, $this->_reply_to_name);
            }

            $email->addDynamicTemplateDatas($dynamic_data);

            // logging
            $msg_obj = ['SendGrid =>  ' . json_encode($dynamic_data), 'to_mail => ' . $to_mail];
            Logging::warning(json_encode($msg_obj));

            $sendgrid = new \SendGrid(config('services.sendgrid.key'));
            $response = $sendgrid->send($email);
        } catch (Exception $e) {
            $msg_obj = [
                'SendGrid Failed =>  ' . $e->getMessage(),
                $user, $tenant_name,
                __FILE__, __LINE__
            ];
            Logging::warning(json_encode($msg_obj));

            return $e->getMessage();
        }
    }

    /*
     * Send Mail function after client feedback
     */
    public function sendResetPassword($to_mail, $sg_data)
    {
        try {

            $full_name = $sg_data['user_name'];
            $link = $sg_data['verify_link'];
            $tenant_name = $sg_data['tenant_name'];

            if ($to_mail == '' || $to_mail == null) {
                // logging
                $msg_obj = [
                    'SendGrid Mail Invalid=>  ',
                    $to_mail,
                    $sg_data,
                    __FILE__, __LINE__
                ];
                Logging::warning(json_encode($msg_obj));

                return $msg_obj;
            }

            $email = new \SendGrid\Mail\Mail();
            $email->setFrom(config('services.sendgrid.from_mail'), config('services.sendgrid.from_name'));
            $email->addTo($to_mail);
            $dynamic_data = [
                'user_name' => $full_name,
                'tenant_name' => $tenant_name,
                'verify_link' => $link,
                'logo_image' => $this->_logo_image,
                'law_firm_name' => $this->_law_firm_name,
                'client_fullname' => '',
                'client_firstname' => '',
            ];
            $email->addDynamicTemplateDatas($dynamic_data);
            $email->setTemplateId(
                new \SendGrid\Mail\TemplateId(config('services.sendgrid.reset_password'))
            );

            // logging
            $msg_obj = ['SendGrid =>  ' . json_encode($dynamic_data), 'to_mail => ' . $to_mail];
            Logging::warning(json_encode($msg_obj));

            $sendgrid = new \SendGrid(config('services.sendgrid.key'));
            $response = $sendgrid->send($email);
            return $response;
        } catch (Exception $e) {
            $msg_obj = [
                'SendGrid Failed =>  ' . $e->getMessage(),
                $to_mail, $sg_data,
                __FILE__, __LINE__
            ];
            Logging::warning(json_encode($msg_obj));

            return $e->getMessage();
        }
    }

    /**
     * Send Mail function on user invite
     */
    public function sendUserInvite($to_mail, $sg_data)
    {
        try {

            $user_email = $sg_data['user_email'];
            $signup_link = $sg_data['signup_link'];
            $tenant_name = $sg_data['tenant_name'];

            if ($to_mail == '' || $to_mail == null) {
                // logging
                $msg_obj = [
                    'SendGrid Mail Invalid=>  ',
                    $to_mail,
                    $sg_data,
                    __FILE__, __LINE__
                ];
                Logging::warning(json_encode($msg_obj));

                return $msg_obj;
            }

            $email = new \SendGrid\Mail\Mail();
            $email->setFrom(config('services.sendgrid.from_mail'), config('services.sendgrid.from_name'));
            $email->addTo($to_mail);
            $dynamic_data = [
                'user_email' => $user_email,
                'tenant_name' => $tenant_name,
                'signup_link' => $signup_link,
                'logo_image' => $this->_logo_image,
                'law_firm_name' => $this->_law_firm_name,
                'client_fullname' => '',
                'client_firstname' => '',
            ];

            if (!empty($this->_reply_to_email)) {
                $email->setReplyTo($this->_reply_to_email, $this->_reply_to_name);
            }

            $email->addDynamicTemplateDatas($dynamic_data);
            $email->setTemplateId(
                new \SendGrid\Mail\TemplateId(config('services.sendgrid.invite_email'))
            );

            // logging
            $msg_obj = ['SendGrid =>  ' . json_encode($dynamic_data), 'to_mail => ' . $to_mail];
            Logging::warning(json_encode($msg_obj));

            $sendgrid = new \SendGrid(config('services.sendgrid.key'));
            $response = $sendgrid->send($email);
            return $response;
        } catch (Exception $e) {
            $msg_obj = [
                'SendGrid Failed =>  ' . $e->getMessage(),
                $to_mail, $sg_data,
                __FILE__, __LINE__
            ];
            Logging::warning(json_encode($msg_obj));

            return $e->getMessage();
        }
    }

    /**
     * Send Mail function on Schedule Go Live
     */
    public function sendScheduleEmail($to_mail, $sg_data)
    {
        try {

            if ($to_mail == '' || $to_mail == null) {
                // logging
                $msg_obj = [
                    'SendGrid Mail Invalid=>  ',
                    $to_mail,
                    $sg_data,
                    __FILE__, __LINE__
                ];
                Logging::warning(json_encode($msg_obj));

                return $msg_obj;
            }

            $email = new \SendGrid\Mail\Mail();
            $email->setFrom(config('services.sendgrid.from_mail'), config('services.sendgrid.from_name'));
            $email->addTo($to_mail);

            $managerEmails = config('services.sendgrid.manager_mails');
            $managerEmailsArray = explode(",", $managerEmails);
            foreach ($managerEmailsArray as $managerEmail) {
                if ($managerEmail != "") {
                    $email->addTo($managerEmail);
                }
            }

            $dynamic_data = $sg_data;

            if (!empty($this->_reply_to_email)) {
                $email->setReplyTo($this->_reply_to_email, $this->_reply_to_name);
            }
            $dynamic_data['logo_image'] = $this->_logo_image;
            $dynamic_data['law_firm_name'] = $this->_law_firm_name;
            $dynamic_data['tenant_name'] = $this->_tenant_name;
            $dynamic_data['client_fullname'] = '';
            $dynamic_data['client_firstname'] = '';

            $email->addDynamicTemplateDatas($dynamic_data);
            $email->setTemplateId(
                new \SendGrid\Mail\TemplateId(config('services.sendgrid.schedule_email'))
            );

            // logging
            $msg_obj = ['SendGrid =>  ' . json_encode($dynamic_data), 'to_mail => ' . $to_mail];
            Logging::warning(json_encode($msg_obj));

            $sendgrid = new \SendGrid(config('services.sendgrid.key'));
            $response = $sendgrid->send($email);
            return $response;
        } catch (Exception $e) {
            $msg_obj = [
                'SendGrid Failed =>  ' . $e->getMessage(),
                $to_mail, $sg_data,
                __FILE__, __LINE__
            ];
            Logging::warning(json_encode($msg_obj));

            return $e->getMessage();
        }
    }

    /**
     * Send Mail function for admin notification service action
     * this is the admin notification email for admin settings.
     */
    public function sendTenantAdminNotificationEmail($to_mail, $dynamic_data)
    {
        try {

            if ($to_mail == '' || $to_mail == null) {
                // logging
                $msg_obj = [
                    'SendGrid Mail Invalid=>  ',
                    $to_mail,
                    $dynamic_data,
                    __FILE__, __LINE__
                ];
                Logging::warning(json_encode($msg_obj));

                return $msg_obj;
            }

            $email = new \SendGrid\Mail\Mail();
            $email->setFrom(config('services.sendgrid.from_mail'), config('services.sendgrid.from_name'));
            $email->addTo($to_mail);

            $managerEmails = config('services.sendgrid.manager_mails');
            $managerEmailsArray = explode(",", $managerEmails);
            foreach ($managerEmailsArray as $managerEmail) {
                if ($managerEmail != "") {
                    $email->addTo($managerEmail);
                }
            }

            if (!empty($this->_reply_to_email)) {
                $email->setReplyTo($this->_reply_to_email, $this->_reply_to_name);
            }

            if (isset($dynamic_data['full_path']) && !empty($dynamic_data['full_path'])) {
                $email->addAttachment(base64_encode(file_get_contents($dynamic_data['full_path'])), 'csv', basename($dynamic_data['full_path']));
            }

            $dynamic_data['logo_image'] = $this->_logo_image;
            $dynamic_data['law_firm_name'] = $this->_law_firm_name;
            $dynamic_data['tenant_name'] = $this->_tenant_name;

            $email->addDynamicTemplateDatas($dynamic_data);
            $email->setTemplateId(
                new \SendGrid\Mail\TemplateId(config('services.sendgrid.tenant_admin_notification_email'))
            );

            // logging
            $msg_obj = ['SendGrid =>  ' . json_encode($dynamic_data), 'to_mail => ' . $to_mail];
            Logging::warning(json_encode($msg_obj));

            $sendgrid = new \SendGrid(config('services.sendgrid.key'));
            $response = $sendgrid->send($email);
            return $response;
        } catch (Exception $e) {
            $msg_obj = [
                'SendGrid Failed =>  ' . $e->getMessage(),
                __FILE__, __LINE__,
                $to_mail, $dynamic_data
            ];
            Logging::warning(json_encode($msg_obj));

            return $e->getMessage();
        }
    }

    /**
     * Send Mail function for admin notification service action
     */
    public function sendTriggerAdminNotificationEmail($to_mail, $dynamic_data)
    {
        try {

            if ($to_mail == '' || $to_mail == null) {
                // logging
                $msg_obj = [
                    'SendGrid Mail Invalid=>  ',
                    $to_mail,
                    $dynamic_data,
                    __FILE__, __LINE__
                ];
                Logging::warning(json_encode($msg_obj));

                return $msg_obj;
            }

            $email = new \SendGrid\Mail\Mail();
            $email->setFrom(config('services.sendgrid.from_mail'), config('services.sendgrid.from_name'));
            $email->addTo($to_mail);

            $managerEmails = config('services.sendgrid.manager_mails');
            $managerEmailsArray = explode(",", $managerEmails);
            foreach ($managerEmailsArray as $managerEmail) {
                if ($managerEmail != "") {
                    $email->addTo($managerEmail);
                }
            }

            if (!empty($this->_reply_to_email)) {
                $email->setReplyTo($this->_reply_to_email, $this->_reply_to_name);
            }

            $dynamic_data['logo_image'] = $this->_logo_image;
            $dynamic_data['law_firm_name'] = $this->_law_firm_name;
            $dynamic_data['tenant_name'] = $this->_tenant_name;
            $dynamic_data['client_fullname'] = '';
            $dynamic_data['client_firstname'] = '';

            $email->addDynamicTemplateDatas($dynamic_data);
            $email->setTemplateId(
                new \SendGrid\Mail\TemplateId(config('services.sendgrid.trigger_action_admin_notification'))
            );

            // logging
            $msg_obj = ['SendGrid =>  ' . json_encode($dynamic_data), 'to_mail => ' . $to_mail];
            Logging::warning(json_encode($msg_obj));

            $sendgrid = new \SendGrid(config('services.sendgrid.key'));
            $response = $sendgrid->send($email);
            return $response;
        } catch (Exception $e) {
            $msg_obj = [
                'SendGrid Failed =>  ' . $e->getMessage(),
                __FILE__, __LINE__,
                $to_mail, $dynamic_data
            ];
            Logging::warning(json_encode($msg_obj));

            return $e->getMessage();
        }
    }


    /**
     * Send Mail function for client notification service action
     */
    public function sendTriggerClientNotificationEmail($to_mail, $dynamic_data)
    {
        try {

            if ($to_mail == '' || $to_mail == null) {
                // logging
                $msg_obj = [
                    'SendGrid Mail Invalid=>  ',
                    $to_mail,
                    $dynamic_data,
                    __FILE__, __LINE__
                ];
                Logging::warning(json_encode($msg_obj));

                return $msg_obj;
            }

            $email = new \SendGrid\Mail\Mail();
            $email->setFrom(config('services.sendgrid.from_mail'), config('services.sendgrid.from_name'));
            $email->addTo($to_mail);

            $managerEmails = config('services.sendgrid.manager_mails');
            $managerEmailsArray = explode(",", $managerEmails);
            foreach ($managerEmailsArray as $managerEmail) {
                if ($managerEmail != "") {
                    $email->addTo($managerEmail);
                }
            }

            if (!empty($this->_reply_to_email)) {
                $email->setReplyTo($this->_reply_to_email, $this->_reply_to_name);
            }

            $dynamic_data['logo_image'] = $this->_logo_image;
            $dynamic_data['law_firm_name'] = $this->_law_firm_name;
            $dynamic_data['tenant_name'] = $this->_tenant_name;
            $dynamic_data['client_fullname'] = '';
            $dynamic_data['client_firstname'] = '';

            $email->addDynamicTemplateDatas($dynamic_data);
            $email->setTemplateId(
                new \SendGrid\Mail\TemplateId(config('services.sendgrid.trigger_action_client_notification'))
            );

            // logging
            $msg_obj = ['SendGrid =>  ' . json_encode($dynamic_data), 'to_mail => ' . $to_mail];
            Logging::warning(json_encode($msg_obj));

            $sendgrid = new \SendGrid(config('services.sendgrid.key'));
            $response = $sendgrid->send($email);
            return $response;
        } catch (Exception $e) {
            $msg_obj = [
                'SendGrid Failed =>  ' . $e->getMessage(),
                __FILE__, __LINE__,
                $to_mail, $dynamic_data
            ];
            Logging::warning(json_encode($msg_obj));

            return $e->getMessage();
        }
    }

    /**
     * Send Mail function on Schedule Go Live
     */
    public function sendContactUpdateEmail($to_mail, $dynamic_data)
    {
        try {
            if ($to_mail == '' || $to_mail == null) {
                // logging
                $msg_obj = [
                    'SendGrid Mail Invalid=>  ',
                    $to_mail,
                    $dynamic_data,
                    __FILE__, __LINE__
                ];
                Logging::warning(json_encode($msg_obj));

                return $msg_obj;
            }

            $email = new \SendGrid\Mail\Mail();
            $email->setFrom(config('services.sendgrid.from_mail'), config('services.sendgrid.from_name'));
            $email->addTo($to_mail);

            // for manual managers.
            $managerEmails = config('services.sendgrid.manager_mails');
            $managerEmailsArray = explode(",", $managerEmails);
            foreach ($managerEmailsArray as $managerEmail) {
                if ($managerEmail != "") {
                    $email->addTo($managerEmail);
                }
            }

            if (!empty($this->_reply_to_email)) {
                $email->setReplyTo($this->_reply_to_email, $this->_reply_to_name);
            }

            $dynamic_data['logo_image'] = $this->_logo_image;
            $dynamic_data['law_firm_name'] = $this->_law_firm_name;
            $dynamic_data['tenant_name'] = $this->_tenant_name;
            $dynamic_data['client_fullname'] = '';
            $dynamic_data['client_firstname'] = '';

            $email->addDynamicTemplateDatas($dynamic_data);
            $email->setTemplateId(
                new \SendGrid\Mail\TemplateId(config('services.sendgrid.contact_update_email'))
            );

            // logging
            $msg_obj = ['SendGrid =>  ' . json_encode($dynamic_data), 'to_mail => ' . $to_mail];
            Logging::warning(json_encode($msg_obj));

            $sendgrid = new \SendGrid(config('services.sendgrid.key'));
            $response = $sendgrid->send($email);
            return $response;
        } catch (Exception $e) {
            $msg_obj = [
                'SendGrid Failed =>  ' . $e->getMessage(),
                $to_mail, $dynamic_data,
                __FILE__, __LINE__
            ];
            Logging::warning(json_encode($msg_obj));

            return $e->getMessage();
        }
    }

    /**
     * Send Mail to admin for submiting form by an end user.
     */
    public function sendFormSubmitResponseMail($to_mail, $dynamic_data)
    {
        try {
            if ($to_mail == '' || $to_mail == null) {
                // logging
                $msg_obj = [
                    'SendGrid Mail Invalid=>  ',
                    $to_mail,
                    $dynamic_data,
                    __FILE__, __LINE__
                ];
                Logging::warning(json_encode($msg_obj));

                return $msg_obj;
            }

            $email = new \SendGrid\Mail\Mail();
            $email->setFrom(config('services.sendgrid.from_mail'), config('services.sendgrid.from_name'));
            $email->addTo($to_mail);
            $email->setTemplateId(
                new \SendGrid\Mail\TemplateId(config('services.sendgrid.form_response_template'))
            );

            if (!empty($this->_reply_to_email)) {
                $email->setReplyTo($this->_reply_to_email, $this->_reply_to_name);
            }

            $dynamic_data['logo_image'] = $this->_logo_image;
            $dynamic_data['law_firm_name'] = $this->_law_firm_name;
            $dynamic_data['tenant_name'] = $this->_tenant_name;
            $dynamic_data['client_fullname'] = '';
            $dynamic_data['client_firstname'] = '';

            $email->addDynamicTemplateDatas($dynamic_data);

            // logging
            $msg_obj = ['SendGrid =>  ' . json_encode($dynamic_data), 'to_mail => ' . $to_mail];
            Logging::warning(json_encode($msg_obj));

            $sendgrid = new \SendGrid(config('services.sendgrid.key'));
            $response = $sendgrid->send($email);
            return $response;
        } catch (Exception $e) {
            $msg_obj = [
                'SendGrid Failed =>  ' . $e->getMessage(),
                $to_mail, $dynamic_data,
                __FILE__, __LINE__
            ];
            Logging::warning(json_encode($msg_obj));

            return $e->getMessage();
        }
    }

    /**
     * Send Mail to User for 2Fa Verification Code.
     */
    public function send2faCodeEmail($to_mail, $dynamic_data)
    {
        try {
            if ($to_mail == '' || $to_mail == null) {
                // logging
                $msg_obj = [
                    'SendGrid Mail Invalid=>  ',
                    $to_mail,
                    $dynamic_data,
                    __FILE__, __LINE__
                ];
                Logging::warning(json_encode($msg_obj));

                return $msg_obj;
            }

            $email = new \SendGrid\Mail\Mail();
            $email->setFrom(config('services.sendgrid.from_mail'), config('services.sendgrid.from_name'));
            $email->addTo($to_mail);
            $email->setTemplateId(
                new \SendGrid\Mail\TemplateId(config('services.sendgrid.tfa_code'))
            );

            if (!empty($this->_reply_to_email)) {
                $email->setReplyTo($this->_reply_to_email, $this->_reply_to_name);
            }

            $dynamic_data['logo_image'] = $this->_logo_image;
            $dynamic_data['tenant_name'] = $this->_tenant_name;
            $dynamic_data['client_fullname'] = '';
            $dynamic_data['client_firstname'] = '';

            $email->addDynamicTemplateDatas($dynamic_data);

            // logging
            $msg_obj = ['SendGrid =>  ' . json_encode($dynamic_data), 'to_mail => ' . $to_mail];
            Logging::warning(json_encode($msg_obj));

            $sendgrid = new \SendGrid(config('services.sendgrid.key'));
            $response = $sendgrid->send($email);
            return $response;
        } catch (Exception $e) {
            $msg_obj = [
                'SendGrid Failed =>  ' . $e->getMessage(),
                $to_mail, $dynamic_data,
                __FILE__, __LINE__
            ];
            Logging::warning(json_encode($msg_obj));

            return $e->getMessage();
        }
    }

    /**
     * Send Mail to User for 2Fa Verification Code.
     */
    public function sendAdminHandlerNoteEmail($to_mail, $dynamic_data)
    {
        try {
            if ($to_mail == '' || $to_mail == null) {
                // logging
                $msg_obj = [
                    'SendGrid Mail Invalid=>  ',
                    $to_mail,
                    $dynamic_data,
                    __FILE__, __LINE__
                ];
                Logging::warning(json_encode($msg_obj));

                return $msg_obj;
            }

            $email = new \SendGrid\Mail\Mail();
            $email->setFrom(config('services.sendgrid.from_mail'), config('services.sendgrid.from_name'));
            $email->addTo($to_mail);
            $email->setTemplateId(
                new \SendGrid\Mail\TemplateId(config('services.sendgrid.admin_handler_note_for_client_failed_login'))
            );

            if (!empty($this->_reply_to_email)) {
                $email->setReplyTo($this->_reply_to_email, $this->_reply_to_name);
            }

            $dynamic_data['logo_image'] = $this->_logo_image;
            $dynamic_data['law_firm_name'] = $this->_law_firm_name;
            $dynamic_data['tenant_name'] = $this->_tenant_name;
            $dynamic_data['client_fullname'] = '';
            $dynamic_data['client_firstname'] = '';

            $email->addDynamicTemplateDatas($dynamic_data);

            // logging
            $msg_obj = ['SendGrid =>  ' . json_encode($dynamic_data), 'to_mail => ' . $to_mail];
            Logging::warning(json_encode($msg_obj));

            $sendgrid = new \SendGrid(config('services.sendgrid.key'));
            $response = $sendgrid->send($email);
            return $response;
        } catch (Exception $e) {
            $msg_obj = [
                'SendGrid Failed =>  ' . $e->getMessage(),
                $to_mail, $dynamic_data,
                __FILE__, __LINE__
            ];
            Logging::warning(json_encode($msg_obj));

            return $e->getMessage();
        }
    }

    /**
     * Send Test Email to Test Template
     */
    public function sendTestEmail($to_mail, $dynamic_data)
    {
        try {
            if ($to_mail == '' || $to_mail == null) {
                // logging
                $msg_obj = [
                    'SendGrid Mail Invalid=>  ',
                    $to_mail,
                    $dynamic_data,
                    __FILE__, __LINE__
                ];
                Logging::warning(json_encode($msg_obj));

                return $msg_obj;
            }

            $email = new \SendGrid\Mail\Mail();
            $email->setFrom(config('services.sendgrid.from_mail'), config('services.sendgrid.from_name'));
            $email->addTo($to_mail);
            if (!empty($this->_reply_to_email)) {
                $email->setReplyTo($this->_reply_to_email, $this->_reply_to_name);
            }

            if (isset($dynamic_data['full_path']) && !empty($dynamic_data['full_path'])) {
                $email->addAttachment(base64_encode(file_get_contents($dynamic_data['full_path'])), 'csv', basename($dynamic_data['full_path']));
            }

            $dynamic_data['logo_image'] = $this->_logo_image;
            $dynamic_data['law_firm_name'] = $this->_law_firm_name;
            $dynamic_data['tenant_name'] = $this->_tenant_name;
            $dynamic_data['client_fullname'] = '';
            $dynamic_data['client_firstname'] = '';

            $email->setTemplateId(
                new \SendGrid\Mail\TemplateId(config('services.sendgrid.test_template'))
            );
            $email->addDynamicTemplateDatas($dynamic_data);

            // logging
            $msg_obj = ['SendGrid =>  ' . json_encode($dynamic_data), 'to_mail => ' . $to_mail];
            Logging::warning(json_encode($msg_obj));

            $sendgrid = new \SendGrid(config('services.sendgrid.key'));
            $response = $sendgrid->send($email);
            return $response;
        } catch (Exception $e) {
            $msg_obj = [
                'SendGrid Failed =>  ' . $e->getMessage(),
                $to_mail, $dynamic_data,
                __FILE__, __LINE__
            ];
            Logging::warning(json_encode($msg_obj));

            return $e->getMessage();
        }
    }

    /**
     * Send Mass Email
     */
    public function sendMassEmail($to_mail, $email_subject, $message, $cc_email = '')
    {
        try {
            if ($to_mail == '' || $to_mail == null) {
                // logging
                $msg_obj = [
                    'SendGrid Mail Invalid=>  ',
                    $to_mail,
                    __FILE__, __LINE__
                ];
                Logging::warning(json_encode($msg_obj));

                return $msg_obj;
            }

            $email = new \SendGrid\Mail\Mail();
            $email->setFrom(config('services.sendgrid.from_mail'), config('services.sendgrid.from_name'));
            $email->addTo($to_mail);

            if (!empty($cc_email)) {
                $email->addCc($cc_email);
            }

            if (!empty($this->_reply_to_email)) {
                $email->setReplyTo($this->_reply_to_email, $this->_reply_to_name);
            }

            $email->addContent(new \SendGrid\Mail\Content("text/html", $message['body']));


            $dynamic_data = [
                "logo_image" => $this->_logo_image,
                "law_firm_name" => $this->_law_firm_name,
                "email_body" => $message['body'],
                "email_subject" => $email_subject
            ];


            // check if there are any attachments
            if (count($message['media']) > 0) {
                foreach ($message['media'] as $key => $med) {
                    $attachment = new \SendGrid\Mail\Attachment();
                    $attachment->setContent($med['content']);
                    $attachment->setFilename($med['name']);
                    $attachment->setType($med['mime']);
                    $attachment->setDisposition("attachment");
                    $email->addAttachment($attachment);
                }
            }

            $email->setTemplateId(
                new \SendGrid\Mail\TemplateId(config('services.sendgrid.tenant_admin_mass_email'))
            );
            $email->addDynamicTemplateDatas($dynamic_data);

            // logging
            $msg_obj = ['SendGrid =>  got request for mass email', 'to_mail => ' . $to_mail];
            Logging::warning(json_encode($msg_obj));

            $sendgrid = new \SendGrid(config('services.sendgrid.key'));
            $response = $sendgrid->send($email);
            return $response;
        } catch (Exception $e) {
            $msg_obj = [
                'SendGrid Failed =>  ' . $e->getMessage(),
                $to_mail, $dynamic_data,
                __FILE__, __LINE__
            ];
            Logging::warning(json_encode($msg_obj));

            return $e->getMessage();
        }
    }
}
