<div class="aside aside-left d-flex aside-fixed d-flex flex-column flex-row-auto" id="kt_aside" style="overflow-y:auto;">
    <!--begin::Brand-->
    <div class="brand flex-column-auto" id="kt_brand">
        <!--begin::Logo-->
        <a href="/">
            <img alt="Logo" src="{{ asset('img/client/vineconnect_logo.png') }}" style="max-height:100px;width:auto;" class="max-h-80px max-w-250px" />
        </a>
        <!--end::Logo-->
    </div>
    <!--end::Brand-->
    <!--begin::Nav Wrapper-->
    <div class="aside-menu-wrapper flex-column-fluid" id="kt_aside_menu_wrapper">
        <div id="kt_aside_menu" class="aside-menu my-4" data-menu-vertical="1" data-menu-scroll="1" data-menu-dropdown-timeout="500">
            <!--begin::Nav-->
            <ul class="menu-nav" role="tablist">

                <!--begin::Item-->
                <li class="menu-item {{ request()->route()->getName()=='usage_dashboard' ? 'active': '' }}" data-toggle="tooltip" title="Dashboard">
                    <a href="{{ route('usage_dashboard') }}" class="menu-link">
                        <span class="svg-icon menu-icon"><i class="icon-xl la la-dashboard"></i></span>
                        <span class="menu-text"> Dashboard </span>
                    </a>
                </li>

                <!--begin::Item-->
                <li class="menu-item {{ request()->route()->getName()=='tenants' ? 'active': '' }}" data-toggle="tooltip" title="Tenants">
                    <a href="{{ route('tenants') }}" class="menu-link">
                        <span class="svg-icon menu-icon"><i class="icon-xl la la-user-shield"></i></span>
                        <span class="menu-text"> Tenants </span>
                    </a>
                </li>

                <li class="menu-item {{ request()->route()->getName()=='templates' ? 'active': '' }}" title="Templates">
					<a href="{{ route('templates') }}" class="menu-link">
						<span class="svg-icon menu-icon"><i class="icon-xl la la-th-large"></i></span>
						<span class="menu-text"> Templates </span>
					</a>
				</li>

                <li class="menu-item {{ request()->route()->getName()=='api_logs' ? 'active': '' }}" data-toggle="tooltip" title="API Logs">
                    <a href="{{ route('api_logs') }}" class="menu-link">
                        <span class="svg-icon menu-icon"><i class="icon-xl la la-list"></i></span>
                        <span class="menu-text">API Logs</span>
                    </a>
                </li>

                <li class="menu-item {{ request()->route()->getName()=='db_backup' ? 'active': '' }}" data-toggle="tooltip" title="DB Backup">
                    <a href="{{ route('db_backup') }}" class="menu-link">
                        <span class="svg-icon menu-icon"><i class="icon-xl la la-database"></i></span>
                        <span class="menu-text">DB Backup</span>
                    </a>
                </li>

                <li class="menu-item {{ request()->route()->getName()=='billing_plans' ? 'active': '' }}" data-toggle="tooltip" title="Billing Plans">
                    <a href="{{ route('billing_plans') }}" class="menu-link">
                        <span class="svg-icon menu-icon"><i class="icon-xl la la-money-bill"></i></span>
                        <span class="menu-text">Billing Plans</span>
                    </a>
                </li>

                <li class="menu-item {{ request()->route()->getName()=='automation_workflow_mapping' ? 'active': '' }}" data-toggle="tooltip" title="Trigger Action Rule">
                    <a href="{{ route('automation_workflow_mapping') }}" class="menu-link">
                        <span class="svg-icon menu-icon"><i class="icon-xl las la-water"></i></span>
                        <span class="menu-text">Trigger Action Rule</span>
                    </a>
                </li>

                <li class="menu-item {{ request()->route()->getName()=='version_management' ? 'active': '' }}" data-toggle="tooltip" title="Version Management">
                    <a href="{{ route('version_management') }}" class="menu-link">
                        <span class="svg-icon menu-icon"><i class="icon-xl las la-building"></i></span>
                        <span class="menu-text">Version Management</span>
                    </a>
                </li>

                <li class="menu-item {{ request()->route()->getName()=='variable_management' ? 'active': '' }}" data-toggle="tooltip" title="Variable Management">
                    <a href="{{ route('variable_management') }}" class="menu-link">
                        <span class="svg-icon menu-icon"><i class="icon-xl las la-cubes"></i></span>
                        <span class="menu-text">Variable Management</span>
                    </a>
                </li>

                <li class="menu-item {{ request()->route()->getName()=='subscription_plan_mapping' ? 'active': '' }}" data-toggle="tooltip" title="Billing Plan Mapping">
                    <a href="{{ route('subscription_plan_mapping') }}" class="menu-link">
                        <span class="svg-icon menu-icon"><i class="icon-xl la la-money-bill"></i></span>
                        <span class="menu-text">Billing Plan Mapping</span>
                    </a>
                </li>

            </ul>
            <!--end::Nav-->
        </div>
    </div>
    <!--end::Nav Wrapper-->
    <!--begin::Footer-->
    <div class="aside-footer aside-menu">
        <!--begin::User-->

        <ul class="menu-nav w-100" role="tablist">
            <li class="menu-item w-100" title="Logout" style="list-style: none;">
                <a href="{{ route('super.logout')}}" class="menu-link" data-toggle="tooltip" title="Logout">
                    <span class="svg-icon menu-icon"><i class="icon-xl la la-sign-out"></i></span>
                    <span class="menu-text">Logout</span>
                </a>
            </li>
        </ul>

        <!--end::User-->
    </div>
    <!--end::Footer-->

    <!--end::Primary-->
</div>
