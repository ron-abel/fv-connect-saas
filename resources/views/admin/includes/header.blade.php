<?php
$config_details = DB::table('config')
    ->where('tenant_id', $cur_tenant_id)
    ->first();
$user = Auth::user();
$permissions = DB::table('user_role_permissions')
    ->where('user_role_id', $user->user_role_id)
    ->where('is_allowed', 1)
    ->get()
    ->pluck('tenant_admin_page')
    ->toArray();
?>
<div class="aside aside-left aside-fixed d-flex flex-column flex-row-auto" id="kt_aside" style="overflow-y:auto;">
    <!--begin::Brand-->
    <div class="brand flex-column-auto justify-content-between" id="kt_brand">
        <p>&nbsp;</p>
        <!--begin::Logo-->
        <a href="#" class="brand-logo">
            @if (isset($config_details->logo))
                <img src="{{ asset('uploads/client_logo/' . $config_details->logo) }}"
                    style="width:100%; max-height:65px;" alt="Logo">
            @else
                <img src="{{ asset('img/client/vineconnect_logo.png') }}" style="width:100%; max-height:65px;"
                    alt="VineConnect Logo">
            @endif
        </a>
        <!--end::Logo-->
        <!--begin::Toggle-->
        <button class="brand-toggle btn btn-sm px-0" id="kt_aside_toggle">
            <span class="text-white">
                <i class="fa fa-angle-double-left fa-2x"></i>
            </span>
        </button>
        <!--end::Toolbar-->
    </div>
    <!--end::Brand-->
    <!--begin::Aside Menu-->
    <div class="aside-menu-wrapper flex-column-fluid" id="kt_aside_menu_wrapper">
        <!--begin::Menu Container-->
        <div id="kt_aside_menu" class="aside-menu my-4" data-menu-vertical="1" data-menu-scroll="1"
            data-menu-dropdown-timeout="500">
            <!--begin::Menu Nav-->
            <ul class="menu-nav">
                @if (in_array('tenant_dashboard', $permissions) || empty($permissions))
                    <li class="menu-item {{ request()->is('admin/dashboard') ? 'menu-item-active' : '' }} "
                        aria-haspopup="true">
                        <a href="{{ route('dashboard', ['subdomain' => $subdomain]) }}" class="menu-link">
                            <span class="menu-icon">
                                <i class="fa-icon far fa-clock"></i>
                            </span>
                            <span class="menu-text">Usage Dashboard</span>
                        </a>
                    </li>
                @endif
                @if (in_array('tenant_webhooks', $permissions) || empty($permissions))
                    <li class="menu-item {{ request()->is('admin/client_portal_launch') ? 'menu-item-active' : '' }}"
                        aria-haspopup="true" data-menu-toggle="hover">
                        <a href="{{ route('client_portal_launch', ['subdomain' => $subdomain]) }}" class="menu-link">
                            <span class="menu-icon">
                                <i class="fa-icon fas fa-rocket"></i>
                            </span>
                            <span class="menu-text">Launchpad</span>
                        </a>
                    </li>
                @endif

                @if (in_array('tenant_billing', $permissions) ||
                        in_array('tenant_profile', $permissions) ||
                        in_array('tenant_managers', $permissions) ||
                        in_array('tenant_client_config', $permissions) ||
                        empty($permissions))
                    <li class="menu-section">
                        <h4 class="menu-text">VineConnect Configuration</h4>
                        <i class="menu-icon ki ki-bold-more-hor icon-md"></i>
                    </li>
                    @if (in_array('tenant_billing', $permissions) || empty($permissions))
                        <li class="menu-item {{ request()->is('admin/billing') ? 'menu-item-active' : '' }}"
                            aria-haspopup="true" data-menu-toggle="hover">
                            <a href="{{ route('billing', ['subdomain' => $subdomain]) }}" class="menu-link">
                                <span class="menu-icon">
                                    <i class="fa-icon fas fa-money-check-alt"></i>
                                </span>
                                <span class="menu-text">Profile &amp; Billing</span>
                            </a>
                        </li>
                    @endif
                    @if (in_array('tenant_managers', $permissions) || empty($permissions))
                        <li class="menu-item {{ request()->is('admin/users') ? 'menu-item-active' : '' }}"
                            aria-haspopup="true" data-menu-toggle="hover">
                            <a href="{{ route('users', ['subdomain' => $subdomain]) }}" class="menu-link">
                                <span class="menu-icon">
                                    <i class="fa-icon fas fa-users"></i>
                                </span>
                                <span class="menu-text">Manage Users</span>
                            </a>
                        </li>
                    @endif
                    @if (in_array('tenant_client_config', $permissions) || empty($permissions))
                        <li class="menu-item {{ request()->is('admin/settings') ? 'menu-item-active' : '' }}"
                            aria-haspopup="true" data-menu-toggle="hover">
                            <a href="{{ route('settings', ['subdomain' => $subdomain]) }}" class="menu-link">
                                <span class="menu-icon">
                                    <i class="fa-icon fas fa-key"></i>
                                </span>
                                <span class="menu-text">Admin Settings</span>
                            </a>
                        </li>
                    @endif
                @endif

                @if (in_array('tenant_webhooks', $permissions) ||
                        in_array('tenant_legal_team', $permissions) ||
                        in_array('tenant_phase_categories', $permissions) ||
                        in_array('tenant_phase_mapping', $permissions) ||
                        in_array('tenant_phase_change_sms', $permissions) ||
                        in_array('tenant_review_requests', $permissions) ||
                        empty($permissions))
                    <li class="menu-section">
                        <h4 class="menu-text">Client Portal Configuration</h4>
                        <i class="menu-icon ki ki-bold-more-hor icon-md"></i>
                    </li>
                    @if (in_array('portal_display_settings', $permissions) || empty($permissions))
                        <li class="menu-item {{ request()->is('admin/portal_display_settings') ? 'menu-item-active' : '' }}"
                            aria-haspopup="true" data-menu-toggle="hover">
                            <a href="{{ route('portal_display_settings', ['subdomain' => $subdomain]) }}"
                                class="menu-link">
                                <span class="menu-icon">
                                    <i class="la la-bar-chart-o"></i>
                                </span>
                                <span class="menu-text">Portal Display Settings</span>
                            </a>
                        </li>
                    @endif
                    {{-- @if (in_array('tenant_legal_team', $permissions) || empty($permissions)) --}}
                    {{-- <li class="menu-item {{ request()->is('admin/legal_team') ? 'menu-item-active' : '' }}" aria-haspopup="true" data-menu-toggle="hover"> --}}
                    {{-- <a href="{{ route('legal_team', ['subdomain' => $subdomain]) }}" class="menu-link"> --}}
                    {{-- <span class="menu-icon"> --}}
                    {{-- <i class="fa-icon fas fa-users"></i> --}}
                    {{-- </span> --}}
                    {{-- <span class="menu-text">Team Setup</span> --}}
                    {{-- </a> --}}
                    {{-- </li> --}}
                    {{-- @endif --}}
                    @if (in_array('tenant_phase_categories', $permissions) || empty($permissions))
                        <li class="menu-item {{ request()->is('admin/phase_categories') ? 'menu-item-active' : '' }}"
                            aria-haspopup="true" data-menu-toggle="hover">
                            <a href="{{ route('phase_categories', ['subdomain' => $subdomain]) }}" class="menu-link">
                                <span class="menu-icon">
                                    <i class="fa-icon fas fa-bars"></i>
                                </span>
                                <span class="menu-text">Timeline Setup</span>
                            </a>
                        </li>
                    @endif
                    @if (in_array('tenant_phase_mapping', $permissions) || empty($permissions))
                        <li class="menu-item {{ request()->is('admin/phase_mapping') ? 'menu-item-active' : '' }}"
                            aria-haspopup="true" data-menu-toggle="hover">
                            <a href="{{ route('phase_mapping', ['subdomain' => $subdomain]) }}" class="menu-link">
                                <span class="menu-icon">
                                    <i class="fa-icon fas fa-table"></i>
                                </span>
                                <span class="menu-text">Phase Mapping</span>
                            </a>
                        </li>
                    @endif
                    @if (in_array('tenant_phase_change_sms', $permissions) || empty($permissions))
                        <li class="menu-item {{ request()->is('admin/phase_change_automated_communications') ? 'menu-item-active' : '' }}"
                            aria-haspopup="true" data-menu-toggle="hover">
                            <a href="{{ route('phase_change_automated_communications', ['subdomain' => $subdomain]) }}"
                                class="menu-link">
                                <span class="menu-icon">
                                    <i class="fab la-rocketchat"></i>
                                </span>
                                <span class="menu-text">Phase Change SMS</span>
                            </a>
                        </li>
                    @endif
                    @if (in_array('tenant_review_requests', $permissions) || empty($permissions))
                        <li class="menu-item {{ request()->is('admin/google_review_automated_communications') ? 'menu-item-active' : '' }}"
                            aria-haspopup="true" data-menu-toggle="hover">
                            <a href="{{ route('google_review_automated_communications', ['subdomain' => $subdomain]) }}"
                                class="menu-link">
                                <span class="menu-icon">
                                    <i class="fab fa-google"></i>
                                </span>
                                <span class="menu-text">Review Requests</span>
                            </a>
                        </li>
                    @endif
                    @if (in_array('client_file_upload_config', $permissions) || empty($permissions))
                        <li class="menu-item {{ request()->is('admin/client_file_upload_config') ? 'menu-item-active' : '' }}"
                            aria-haspopup="true" data-menu-toggle="hover">
                            <a href="{{ route('client_file_upload_config', ['subdomain' => $subdomain]) }}"
                                class="menu-link">
                                <span class="menu-icon">
                                    <i class="far fa-file"></i>
                                </span>
                                <span class="menu-text">Document Uploads</span>
                            </a>
                        </li>
                    @endif

                    @if (in_array('forms', $permissions) || empty($permissions))
                        <li class="menu-item {{ request()->is('admin/forms') ? 'menu-item-active' : '' }}"
                            aria-haspopup="true" data-menu-toggle="hover">
                            <a href="{{ route('forms', ['subdomain' => $subdomain]) }}" class="menu-link">
                                <span class="menu-icon">
                                    <i class="fab fa-wpforms"></i>
                                </span>
                                <span class="menu-text">Filevine Forms</span>
                            </a>
                        </li>
                    @endif

                    @if (in_array('banner_messages', $permissions) || empty($permissions))
                        <li class="menu-item {{ request()->is('admin/banner_messages') ? 'menu-item-active' : '' }}"
                            aria-haspopup="true" data-menu-toggle="hover">
                            <a href="{{ route('banner_messages', ['subdomain' => $subdomain]) }}" class="menu-link">
                                <span class="menu-icon">
                                    <i class="far fa-flag"></i>
                                </span>
                                <span class="menu-text">Banner Messages</span>
                            </a>
                        </li>
                    @endif
                    @if (in_array('calendar', $permissions) || empty($permissions))
                        <li class="menu-item {{ request()->is('admin/calendar') ? 'menu-item-active' : '' }}"
                            aria-haspopup="true" data-menu-toggle="hover">
                            <a href="{{ route('calendar', ['subdomain' => $subdomain]) }}" class="menu-link">
                                <span class="menu-icon">
                                    <i class="far fa-calendar"></i>
                                </span>
                                <span class="menu-text">Filevine Calendar</span>
                            </a>
                        </li>
                    @endif
                @endif


                @if (in_array('tenant_client_config', $permissions) ||
                        in_array('tenant_webhooks', $permissions) ||
                        in_array('tenant_contacts', $permissions) ||
                        empty($permissions))
                    <li class="menu-section">
                        <h4 class="menu-text">Admin Tools</h4>
                        <i class="menu-icon ki ki-bold-more-hor icon-md"></i>
                    </li>
                    <li class="menu-item {{ request()->is('admin/automated_workflow') ? 'menu-item-active' : '' }}"
                        aria-haspopup="true" data-menu-toggle="hover">
                        <a href="{{ route('automated_workflow', ['subdomain' => $subdomain]) }}" class="menu-link">
                            <span class="menu-icon">
                                <i class="fa-icon fas fa-water"></i>
                            </span>
                            <span class="menu-text">Automated Workflows</span>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->is('admin/mass_messages') ? 'menu-item-active' : '' }}"
                        aria-haspopup="true" data-menu-toggle="hover">
                        <a href="{{ route('mass_messages', ['subdomain' => $subdomain]) }}" class="menu-link">
                            <span class="menu-icon">
                                <i class="fab la-rocketchat"></i>
                            </span>
                            <span class="menu-text">Mass Text Messages</span>
                        </a>
                    </li>

                    <li class="menu-item {{ request()->is('admin/mass_emails') ? 'menu-item-active' : '' }}"
                        aria-haspopup="true" data-menu-toggle="hover">
                        <a href="{{ route('mass_emails', ['subdomain' => $subdomain]) }}" class="menu-link">
                            <span class="menu-icon">
                                <i class="fa la-envelope"></i>
                            </span>
                            <span class="menu-text">Mass Email Messages</span>
                        </a>
                    </li>

                    @if (false && (in_array('tenant_webhooks', $permissions) || empty($permissions)))
                        <li class="menu-item {{ request()->is('admin/webhooks') ? 'menu-item-active' : '' }}"
                            aria-haspopup="true" data-menu-toggle="hover">
                            <a href="{{ route('webhooks', ['subdomain' => $subdomain]) }}" class="menu-link">
                                <span class="menu-icon">
                                    <i class="fa-icon fas fa-recycle"></i>
                                </span>
                                <span class="menu-text">Webhooks</span>
                            </a>
                        </li>
                    @endif
                    @if (in_array('tenant_contacts', $permissions) || empty($permissions))
                        {{-- <li class="menu-item {{ request()->is('admin/mass_updates') ? 'menu-item-active' : '' }}" aria-haspopup="true" data-menu-toggle="hover">
                        <a href="{{ route('mass_updates', ['subdomain' => $subdomain]) }}" class="menu-link">
                            <span class="menu-icon">
                                <i class="fa-icon fas fa-upload"></i>
                            </span>
                            <span class="menu-text">Upload Contacts</span>
                        </a>
                    </li> --}}
                    @endif
                    @if (in_array('media_locker', $permissions) || empty($permissions))
                        <li class="menu-item {{ request()->is('admin/media_locker') ? 'menu-item-active' : '' }}"
                            aria-haspopup="true" data-menu-toggle="hover">
                            <a href="{{ route('media_locker', ['subdomain' => $subdomain]) }}" class="menu-link">
                                <span class="menu-icon">
                                    <i class="fa-icon fas fa-camera"></i>
                                </span>
                                <span class="menu-text">Media Locker</span>
                            </a>
                        </li>
                    @endif
                    @if (in_array('variables', $permissions) || empty($permissions))
                        <li class="menu-item {{ request()->is('admin/variables') ? 'menu-item-active' : '' }}"
                            aria-haspopup="true" data-menu-toggle="hover">
                            <a href="{{ route('variables', ['subdomain' => $subdomain]) }}" class="menu-link">
                                <span class="menu-icon">
                                    <i class="fa-icon fas fa-recycle"></i>
                                </span>
                                <span class="menu-text">Custom Variables</span>
                            </a>
                        </li>
                    @endif
                    @if (in_array('tenant_client_config', $permissions) || empty($permissions))
                        <li class="menu-item {{ request()->is('admin/client_blacklist') ? 'menu-item-active' : '' }}"
                            aria-haspopup="true" data-menu-toggle="hover">
                            <a href="{{ route('client_blacklist', ['subdomain' => $subdomain]) }}" class="menu-link">
                                <span class="menu-icon">
                                    <i class="fa-icon fas fa-ban"></i>
                                </span>
                                <span class="menu-text">Blacklist Tool</span>
                            </a>
                        </li>
                    @endif
                @endif
            </ul>
            <!--end::Menu Nav-->
        </div>
        <!--end::Menu Container-->
    </div>
    <!--end::Aside Menu-->
</div>
