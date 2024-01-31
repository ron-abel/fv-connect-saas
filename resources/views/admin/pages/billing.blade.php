@extends('admin.layouts.default')

@section('title', 'VineConnect Admin - Profile &amp; Billing Settings')
<link href="{{ asset('css/billing.scss') }}" rel="stylesheet">
@section('content')

    <!--begin::Subheader-->
    @php
        $coupons = (new App\Services\SubscriptionService())->getAllCoupons(true);
        $is_first_payment = App\Services\SubscriptionService::checkIfTenantFirstPayment($cur_tenant_id);
    @endphp
    <div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex align-items-center flex-wrap mr-2">
                <!--begin::Page Title-->
                <h4 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Profile &amp; Billing Settings</h4>
                <!--end::Page Title-->
            </div>
            <!--end::Info-->
        </div>
    </div>
    <!--end::Subheader-->
    <!--begin::Entry-->
    <div class="d-flex flex-column-fluid py-0">
        <div class="container py-0" style="margin-top: -10px;">
            <div class="flex-row-fluid py-0 my-0">
                <!--begin::Section-->
                <!-- COMBINE THE PROFILE BLADE WITH BILLING BLADE -->
                <div class="row">
                    <div class="col-md-6">
                        <!--begin::Card-->
                        <div class="card card-custom gutter-b example example-compact" style="min-height: 94.7%;">
                            <div class="card-header">
                                <h3 class="card-title">My Profile</h3>
                            </div>
                            <!--begin::Form-->
                            <form action="{{ route('profile_update', ['subdomain' => $subdomain]) }}"
                                enctype="multipart/form-data" method="post">
                                @csrf
                                <div class="card-body mb-20">
                                    <div class="form-group">
                                        <label>Display Name
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input name="full_name" type="text" class="form-control"
                                            value="@if (isset($user_details->full_name)) {{ $user_details->full_name }} @endif">
                                        @error('full_name')
                                            <span class="form-text text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label>Email Address
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input name="email" type="text" class="form-control"
                                            value="@if (isset($user_details->email)) {{ $user_details->email }} @endif">
                                        @error('email')
                                            <span class="form-text text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button name="settings_save" class="btn btn-primary mr-2">Update Profile</button>
                                </div>
                            </form>
                            <!--end::Form-->
                        </div>
                        <!--end::Card-->
                    </div>

                    <div class="col-md-6">
                        <!--begin::Card-->
                        <div class="card card-custom gutter-b example example-compact">
                            <div class="card-header">
                                <h3 class="card-title">Change Password</h3>
                            </div>
                            <!--begin::Form-->
                            <form id="changePass" action="{{ route('update_password', ['subdomain' => $subdomain]) }}"
                                enctype="multipart/form-data" method="post">
                                @csrf
                                <div class="card-body">
                                    @if (session()->has('success_pass'))
                                        <div class="alert alert-success" role="alert">
                                            {{ session()->get('success_pass') }} </div>
                                    @elseif(session()->has('error_pass'))
                                        <div class="alert alert-danger" role="alert"> {{ session()->get('error_pass') }}
                                        </div>
                                    @endif

                                    <div class="form-group">
                                        <label>Current Password
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input name="current_password" type="password" class="form-control" value="">
                                        @error('current_password')
                                            <span class="form-text text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label>New Password
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input name="new_password" type="password" class="form-control" value="">
                                        @error('new_password')
                                            <span class="form-text text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label>Confirm Password
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input name="confirm_password" type="password" class="form-control" value="">
                                        @error('confirm_password')
                                            <span class="form-text text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button name="change_password" type="submit" class="btn btn-primary mr-2">Update
                                        Password</button>
                                </div>
                            </form>
                            <!--end::Form-->
                        </div>
                        <!--end::Card-->
                    </div>

                </div>
                <div class="card card-custom card-transparent py-0 my-0">
                    <div class="card-body p-0">

                        <div class="stripe-errors"></div>
                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    {{ $error }}<br>
                                @endforeach
                            </div>
                        @endif

                        @if (isset($error) && $error)
                            <div class="alert alert-danger">
                                {{ $errorMessage ?? '' }}

                            </div>
                        @endif

                        @if (session()->get('success'))
                            <div class="bg-success p-3">
                                {{ session()->get('success') }}
                            </div>
                        @endif
                        @if (session()->get('info'))
                            <div class="bg-info text-white p-3">
                                {{ session()->get('info') }}
                            </div>
                        @endif


                        <!--begin: Wizard-->
                        <div class="wizard wizard-4" id="kt_wizard" data-wizard-state="step-first"
                            data-wizard-clickable="false">
                            <!--begin: Wizard Nav-->
                            <div class="wizard-nav">
                                <div class="wizard-steps" data-total-steps="3">
                                    <!--begin::Wizard Step 1 Nav-->
                                    <div class="wizard-step" data-wizard-type="step" data-wizard-state="current">
                                        <input id="current-step" hidden
                                            value="{{ session()->get('coupon-error') ? 2 : (!$is_update && ($user != [] && isset($userProd->name) || !empty($user->subscription('default')->needs_cancelled_at)) ? 3 : 1) }}">
                                        <div class="wizard-wrapper">
                                            <div class="wizard-number">1</div>
                                            <div class="wizard-label">
                                                <div class="wizard-title">Subscriptions</div>
                                                <div class="wizard-desc">Select Your Plan</div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Wizard Step 1 Nav-->
                                    <!--begin::Wizard Step 2 Nav-->
                                    <div class="wizard-step" data-wizard-type="step">
                                        <div class="wizard-wrapper">
                                            <div class="wizard-number">2</div>
                                            <div class="wizard-label">
                                                <div class="wizard-title">Billing</div>
                                                <div class="wizard-desc">Set Up Payments</div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Wizard Step 2 Nav-->
                                    <!--begin::Wizard Step 3 Nav-->
                                    <div class="wizard-step" data-wizard-type="step">
                                        <div class="wizard-wrapper">
                                            <div class="wizard-number">3</div>
                                            <div class="wizard-label">
                                                <div class="wizard-title">Complete</div>
                                                <div class="wizard-desc">You're Done!</div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Wizard Step 3 Nav-->
                                </div>
                            </div>
                            <!--end: Wizard Nav-->

                            <!--begin: Wizard Body-->
                            <div class="card card-custom card-shadowless rounded-top-0">
                                <div class="card-body p-0">
                                    <div class="row justify-content-center">
                                        <div class="col-md-12">
                                            <!--begin: Wizard Form-->
                                            <form class="form mt-0 mt-lg-10" id="kt_form"
                                                action="{{ route('billing.submit', ['subdomain' => $subdomain]) }}"
                                                method="POST">
                                                @csrf
                                                <!--begin: Wizard Step 1-->
                                                <input name="is_update" value="{{ $is_update }}" hidden>
                                                <div class="pb-5" data-wizard-type="step-content"
                                                    data-wizard-state="current">
                                                    <div class="row justify-content-center text-center my-0"
                                                        style="display: flex">
                                                        @if (count($plans) > 0)
                                                            @foreach ($plans as $key => $plan)
                                                                <div
                                                                    class="col-md-4 col-xxl-3 bg-white rounded-left ml-10 shadow-sm">
                                                                    <label for="selected-item-{{ $plan->id }}"
                                                                        class="selected-label first-tab">
                                                                        <input type="radio" name="plan"
                                                                            {{ $key == 0 ? 'checked' : '' }}
                                                                            id="selected-item-{{ $plan->id }}"
                                                                            value="{{ $plan->id }}">
                                                                        <span class="icon"></span>
                                                                        <div class="pt-25 pb-25 pb-md-10 px-4">
                                                                            <h4 class="mb-15">{{ $plan->product->name }}
                                                                            </h4>
                                                                            <span
                                                                                class="px-7 py-3 d-inline-flex flex-center rounded-lg mb-15 bg-primary-o-10">
                                                                                <span class="pr-2 opacity-70">$</span>
                                                                                <span
                                                                                    class="pr-2 font-size-h1 font-weight-bold">{{ $plan->usd_amount }}</span>
                                                                                <span
                                                                                    class="opacity-70">/&nbsp;&nbsp;{{ $plan->interval }}</span>
                                                                            </span>
                                                                            <br>
                                                                            <p
                                                                                class="mb-10 d-flex flex-column text-dark-50">
                                                                                <span>{{ $plan->product->description }}</span>
                                                                            </p>
                                                                            <span
                                                                                class="btn btn-primary text-uppercase font-weight-bolder px-15 py-3 order_now_first">Order
                                                                                Now</span>
                                                                        </div>
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <div class="col-md-11 alert alert-custom alert-notice alert-light-info fade show mb-5"
                                                                role="alert">
                                                                <div class="alert-icon">
                                                                    <i class="flaticon2-mail-1"></i>
                                                                </div>
                                                                <div class="alert-text">Your active projects count exceeds
                                                                    our standard billing plans. Let’s create a custom plan
                                                                    together! <a
                                                                        href="mailto:{{ $vineconnect_sales_email }}?cc={{ $reply_to_email }}&&subject=Custom%20Plan%20Request%20from%20%7B%7B{{ $tenant->tenant_name }}%7D%7D&body=Vinetegrate%20Sales%20Team%3A%20%7B%7B{{ $tenant->tenant_name }}%7D%7D%20needs%20a%20custom%20billing%20plan.%20Total%20project%20count%20is%20%7B%7B{{ $fv_total_project }}%7D%7D.%20Let's%20schedule%20a%20time%20to%20consult!"
                                                                        class="btn btn-success ml-12">
                                                                        <i class="flaticon2-new-email"></i> Contact Us
                                                                    </a> </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <!--end: Wizard Step 1-->
                                                <!--begin: Wizard Step 2-->
                                                <div class="pb-5 col-xxl-7 mx-auto" data-wizard-type="step-content">
                                                    <h4 class="mb-10 font-weight-bold text-dark">Enter your Payment Details
                                                    </h4>
                                                    <div class="row">
                                                        <div class="col-xl-6">
                                                            <!--begin::Input-->
                                                            <div class="form-group">
                                                                <label for="card-holder-name">Name on Card</label>
                                                                <input id="card-holder-name" type="text"
                                                                    value="{{ old('ccname') }}"
                                                                    class="form-control form-control-solid form-control-lg"
                                                                    name="ccname" placeholder="Eg: John Wick" />
                                                                <span class="form-text text-muted">Please enter the name on
                                                                    your card.</span>
                                                            </div>
                                                            <!--end::Input-->
                                                        </div>
                                                        <div class="col-xl-6">
                                                            <!--begin::Input-->
                                                            <div class="form-group">
                                                                <label for="card-element">Card Number</label>
                                                                <div id="card-element" class="form-control">
                                                                    <span class="form-text text-muted">Please enter your
                                                                        address.</span>
                                                                    <div id="card-errors" role="alert"></div>
                                                                </div>
                                                                <!--end::Input-->
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-xl-6">
                                                            <!--begin::Input-->
                                                            <div class="form-group">
                                                                <label for="card-holder-address">Email</label>
                                                                <input id="card-holder-address" type="text"
                                                                    class="form-control form-control-solid form-control-lg"
                                                                    name="cemail" value="{{ $user_details->email }}"
                                                                    required />
                                                                <span class="form-text text-muted">Please enter your
                                                                    email.</span>
                                                            </div>
                                                            <!--end::Input-->
                                                        </div>

                                                        <div class="col-xl-6">
                                                            <!--begin::Input-->
                                                            <div class="form-group">
                                                                <label for="card-holder-address">Phone</label>
                                                                <input id="card-holder-phone" value="{{ old('cphone') }}"
                                                                    type="text"
                                                                    class="form-control form-control-solid form-control-lg"
                                                                    name="cphone" placeholder="" required />
                                                                <span class="form-text text-muted">Please enter your
                                                                    phone.</span>
                                                            </div>
                                                            <!--end::Input-->
                                                        </div>

                                                    </div>
                                                    <div class="row">
                                                        <div class="col-xl-6">
                                                            <!--begin::Input-->
                                                            <div class="form-group">
                                                                <label for="card-holder-address">Address</label>
                                                                <input id="card-holder-address" type="text"
                                                                    class="form-control form-control-solid form-control-lg"
                                                                    value="{{ old('caddress') }}" name="caddress"
                                                                    placeholder="" required />
                                                                <span class="form-text text-muted">Please enter your
                                                                    address.</span>
                                                            </div>
                                                            <!--end::Input-->
                                                        </div>

                                                        {{-- <div class="col-xl-6">
                                                        <!--begin::Input-->
                                                        <div class="form-group">
                                                            <label for="card-holder-email">Description</label>
                                                            <input id="card-holder-description" type="text" value="{{ old('cdescription') }}" class="form-control form-control-solid form-control-lg" name="cdescription" placeholder="" required />
                                                            <span class="form-text text-muted">Please enter your description.</span>
                                                        </div>
                                                        <!--end::Input-->
                                                    </div> --}}
                                                        <div class="col-xl-6 {{ session()->get('coupon-error') ? '' : '' }}"
                                                            id="coupon-input">
                                                            <div class="form-group">
                                                                <label for="coupon-input">Coupon</label>
                                                                <input type="text" value="{{ old('ccoupon') }}"
                                                                    id="coupon-input-field"
                                                                    class="form-control form-control-solid form-control-lg"
                                                                    name="ccoupon" placeholder="" required />
                                                                <span class="form-text text-muted">Please enter
                                                                    Coupon.</span>
                                                            </div>
                                                            <span class="text-danger ml-2" id="coupon-error">
                                                            </span>
                                                        </div>

                                                    </div>
                                                    {{-- @if ($is_first_payment)
                                                <div class="row">
                                                    <div class="col-md-12 d-flex">
                                                        <input type="checkbox" class="mt-1" name="coupon" id="coupon" {{ session()->get('coupon-error') ? 'checked': '' }}>
                                                        <h6 class="ml-2 mt-3">Enter your Coupon Code?</h6>
                                                    </div>
                                                    <div class="col-xl-6 {{ session()->get('coupon-error') ? '' : 'hidden-input' }}" id="coupon-input">
                                                        <div class="form-group">
                                                            <label for="coupon-input">Coupon</label>
                                                            <input type="text" value="{{ old('ccoupon') }}" id="coupon-input-field" class="form-control form-control-solid form-control-lg" name="ccoupon" placeholder="" required />
                                                            <span class="form-text text-muted">Please enter
                                                                Coupon.</span>
                                                        </div>
                                                        <span class="text-danger ml-2" id="coupon-error">
                                                        </span>
                                                    </div>
                                                </div>
                                                @endif --}}
                                            </form>


                                            <!--begin: Wizard Actions-->
                                            <div class="d-flex justify-content-between border-top mt-5 pt-10 p-3">
                                                <div class="mr-2">
                                                    <button type="button"
                                                        class="btn prev-button btn-light-primary font-weight-bolder text-uppercase px-9 py-4"
                                                        data-wizard-type="action-prev">Previous</button>
                                                </div>
                                                <div>
                                                    <button type="button"
                                                        class="btn next-button btn-primary font-weight-bolder text-uppercase px-9 py-4 click_next"
                                                        data-wizard-type="action-next">Next</button>
                                                    <button type="button"
                                                        class="btn btn-success submit-button font-weight-bolder text-uppercase px-9 py-4"
                                                        data-secret="{{ $intent->client_secret ?? '' }}" id="card-button"
                                                        style="display: none;">Submit</button>
                                                </div>
                                            </div>
                                            <!--end: Wizard Actions-->

                                        </div>

                                        <!--end: Wizard Step 2-->
                                        <!--begin: Wizard Step 3-->
                                        <div class="pb-5 col-xxl-9 mx-auto" data-wizard-type="step-content">
                                            @if (!$is_update && $user != [] && (isset($userProd->name) || !empty($user->subscription('default')->needs_cancelled_at)))
                                                <div class="card-body">
                                                    <div class="row">
                                                        <!--subscription activity -->
                                                        @if(isset($userProd->name))
                                                        <div class="col-md-12">
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <h5 class="text-success subscription-completion">
                                                                        Subscription Successful!</h5>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <span><b>Plan: </b>{{ $userProd->name }}</span>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <span><b>Price:
                                                                        </b>{{ $stripe->prices->all(['product' => $userProd->id])->data[0]->currency }}
                                                                        {{ $stripe->prices->all(['product' => $userProd->id])->data[0]->unit_amount / 100 }}/
                                                                        {{ $stripe->prices->all(['product' => $userProd->id])->data[0]->recurring['interval'] }}</span>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <span><b>Payment Type: </b>{{ $user->pm_type }}</span>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <span><b>Card : </b> ************
                                                                        {{ $user->pm_last_four }}</span>
                                                                    <!-- <span class="ml-3"><b>Exp Date: </b></span> -->
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <span><b>End Date:
                                                                        </b>
                                                                        {{ \Carbon\Carbon::createFromTimeStamp($user->subscription('default')->asStripeSubscription()->current_period_end)->format('m-d-Y') }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endif
                                                        <!-- subscription status -->
                                                        <div class="col-md-12" style="margin-top: 50px;">
                                                            <h4>My Subscriptions</h4>
                                                            <div class="row ml-2 mb-5">

                                                                <span class="mb-3">
                                                                    <b>Subscribed Date:</b>
                                                                    {{ $user->subscription('default')->created_at ? date('Y-m-d', strtotime($user->subscription('default')->created_at)) : '' }}<br>
                                                                    <b>Invoice Number:</b>
                                                                    #{{ $user->asStripeCustomer()->invoice_prefix }}<br>
                                                                    <b>Card Type:</b> {{ $user->pm_type }}<br>
                                                                    {{-- <b>Plan:</b> {{ $userProd->name }}<br>
                                                                <b>Cost:</b> ${{ $stripe->prices->all(['product' => $userProd->id])->data[0]->unit_amount/100 }}/{{ $stripe->prices->all(['product' => $userProd->id])->data[0]->recurring['interval'] }}<br> --}}
                                                                    <b>Next Payment:</b>&nbsp;
                                                                    @if ($user->is_active)
                                                                        @if(!empty($user->subscription('default')->needs_cancelled_at) && !empty($user->subscription('default')->ends_at) && \Carbon\Carbon::parse($user->subscription('default')->needs_cancelled_at)->format('Y-m-d') <= \Carbon\Carbon::parse($user->subscription('default')->ends_at)->format('Y-m-d'))
                                                                        @else
                                                                        {{ \Carbon\Carbon::createFromTimeStamp($user->subscription('default')->asStripeSubscription()->current_period_end)->format('m-d-Y') }}
                                                                        @endif
                                                                    @endif
                                                                    <!-- </span> -->
                                                                    <p>&nbsp;</p>
                                                                    @if(!empty($user->subscription('default')->needs_cancelled_at))
                                                                    <span class="text-danger">Subscription
                                                                        Canceled</span>
                                                                    <h6 class="text-info mt-3 mb-3">
                                                                        Your subscription plan <plan name> will be
                                                                            finished at
                                                                            {{ date('Y-m-d', strtotime($user->subscription('default')->needs_cancelled_at)) }}.
                                                                            </br>
                                                                            You can resubscribe at the end of the
                                                                            current plan.
                                                                    </h6>
                                                                    @endif

                                                                    <p>&nbsp;</p>
                                                                    @if ($user->is_active)

                                                                        @if(empty($user->subscription('default')->needs_cancelled_at))
                                                                        <button class="btn btn-danger" data-toggle="modal"
                                                                            data-target="#subscriptionModal">Cancel
                                                                            Subscription</button>
                                                                        @endif
                                                                        <!-- <button class="btn btn-success ml-2" data-toggle="modal" data-target="#updatePlanModal">Update Plan</button> -->
                                                                        <button class="btn btn-primary ml-2"
                                                                            data-toggle="modal"
                                                                            data-target="#updateCardModal">Update
                                                                            Card</button>
                                                                        <a href="https://billing.stripe.com/p/login/bIY8xIdv5cUi728fYY"
                                                                            target="_blank" class="btn btn-success ml-2">
                                                                            Invoices & Payments History</a>
                                                                    @else
                                                                        <!-- </br>
                                                                        <span class="text-danger">Subscription
                                                                            Canceled</span>
                                                                        <h6 class="text-info mt-3 mb-3">
                                                                            Your subscription plan <plan name> will be
                                                                                finished at
                                                                                {{ date('Y-m-d', strtotime($user->subscription('default')->ends_at)) }}.
                                                                                </br>
                                                                                You can resubscribe at the end of the
                                                                                current plan.
                                                                                </br>
                                                                                If you want, you can re-subscribe now.
                                                                        </h6> -->
                                                                        <button class="btn btn-primary"
                                                                            data-toggle="modal"
                                                                            data-target="#addSubscriptionModal">Resubscribe</button>
                                                                    @endif
                                                                </span>
                                                                <!-- Subscription cancel modal. -->
                                                                <div class="modal fade" id="subscriptionModal"
                                                                    tabindex="-1" role="dialog"
                                                                    aria-labelledby="subscriptionModalLabel"
                                                                    aria-hidden="true">
                                                                    <div class="modal-dialog" role="document">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title"
                                                                                    id="subscriptionModalLabel">Cancel
                                                                                    Subscription</h5>
                                                                                <button type="button" class="close"
                                                                                    data-dismiss="modal"
                                                                                    aria-label="Close">
                                                                                    <span aria-hidden="true">&times;</span>
                                                                                </button>
                                                                            </div>
                                                                            <div class="modal-body">
                                                                                <h4>Are you sure?
                                                                                </h4>
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button type="button"
                                                                                    class="btn btn-secondary"
                                                                                    data-dismiss="modal">Close</button>
                                                                                <a href="cancel/subscription/{{ $user->id }}"
                                                                                    type="button"
                                                                                    class="btn btn-primary">Confirm</a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal fade" id="updateCardModal"
                                                                    tabindex="-1" role="dialog"
                                                                    aria-labelledby="updateCardModalLabel"
                                                                    aria-hidden="true">
                                                                    <div class="modal-dialog" role="document">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title"
                                                                                    id="subscriptionModalLabel">Update Card
                                                                                </h5>
                                                                                <button type="button" class="close"
                                                                                    data-dismiss="modal"
                                                                                    aria-label="Close">
                                                                                    <span aria-hidden="true">&times;</span>
                                                                                </button>
                                                                            </div>
                                                                            <div class="modal-body">
                                                                                <form id="update_card_form"
                                                                                    action="{{ route('update_card', ['subdomain' => $subdomain, 'id' => $user->id]) }}"
                                                                                    method="POST">
                                                                                    @csrf
                                                                                    <div class="row">
                                                                                        <div class="col-xl-12">
                                                                                            <!--begin::Input-->
                                                                                            <div class="form-group">
                                                                                                <label
                                                                                                    for="card-holder-name">Name
                                                                                                    on Card</label>
                                                                                                <input
                                                                                                    id="updatecard-holder-name"
                                                                                                    type="text"
                                                                                                    class="form-control form-control-solid form-control-lg"
                                                                                                    name="ccname"
                                                                                                    placeholder="Eg: John Wick" />
                                                                                                <span
                                                                                                    class="form-text text-muted">Please
                                                                                                    enter your Card
                                                                                                    Name.</span>
                                                                                            </div>
                                                                                            <!--end::Input-->
                                                                                        </div>
                                                                                        <div class="col-xl-12">
                                                                                            <!--begin::Input-->
                                                                                            <div class="form-group">
                                                                                                <label
                                                                                                    for="updatecard-element">Card
                                                                                                    Number</label>
                                                                                                <div id="updatecard-element"
                                                                                                    class="form-control">
                                                                                                    <span
                                                                                                        class="form-text text-muted">Please
                                                                                                        enter your
                                                                                                        Address.</span>
                                                                                                    <div id="updatecard-errors"
                                                                                                        role="alert">
                                                                                                    </div>
                                                                                                </div>
                                                                                                <!--end::Input-->
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </form>
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button type="button"
                                                                                    class="btn btn-secondary"
                                                                                    data-dismiss="modal">Close</button>
                                                                                <button type="button"
                                                                                    class="btn btn-primary"
                                                                                    data-secret="{{ $intent->client_secret ?? '' }}"
                                                                                    id="updatecard-button">Update
                                                                                    Card</button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                @if($user->subscription('default') && empty($user->subscription('default')->needs_cancelled_at))
                                                                <div class="modal fade" id="updatePlanModal"
                                                                    tabindex="-1" role="dialog"
                                                                    aria-labelledby="updatePlanModal" aria-hidden="true">
                                                                    <div class="modal-dialog modal-lg" role="document">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title"
                                                                                    id="updatePlanModal">Update Plan</h5>
                                                                                <button type="button" class="close"
                                                                                    data-dismiss="modal"
                                                                                    aria-label="Close">
                                                                                    <span aria-hidden="true">&times;</span>
                                                                                </button>
                                                                            </div>
                                                                            <form
                                                                                action="{{ route('update_plan', ['subdomain' => $subdomain, 'id' => $user->id]) }}"
                                                                                method="POST">
                                                                                @csrf
                                                                                <div class="modal-body">
                                                                                    <div
                                                                                        class="row justify-content-center text-center my-0">
                                                                                        @foreach ($plans as $key => $plan)
                                                                                            <div
                                                                                                class="col-md-4 col-xxl-4 bg-white rounded-left ml-10 shadow-sm">
                                                                                                <label
                                                                                                    for="changeplan-item-{{ $plan->id }}"
                                                                                                    class="selected-label first-tab">
                                                                                                    <input type="radio"
                                                                                                        name="change_plan"
                                                                                                        {{ $plan->product->id == $userProd->id ? 'checked' : '' }}
                                                                                                        id="changeplan-item-{{ $plan->id }}"
                                                                                                        value="{{ $plan->id }}">
                                                                                                    <span
                                                                                                        class="icon"></span>
                                                                                                    <div
                                                                                                        class="pt-25 pb-25 pb-md-10 px-4">
                                                                                                        <h4 class="mb-15">
                                                                                                            {{ $plan->product->name }}
                                                                                                        </h4>
                                                                                                        <span
                                                                                                            class="px-7 py-3 d-inline-flex flex-center rounded-lg mb-15 bg-primary-o-10">
                                                                                                            <span
                                                                                                                class="pr-2 opacity-70">$</span>
                                                                                                            <span
                                                                                                                class="pr-2 font-size-h1 font-weight-bold">{{ $plan->usd_amount }}</span>
                                                                                                            <span
                                                                                                                class="opacity-70">/&nbsp;&nbsp;{{ $plan->interval }}</span>
                                                                                                        </span>
                                                                                                        <br>
                                                                                                        <p
                                                                                                            class="mb-10 d-flex flex-column text-dark-50">
                                                                                                            test some
                                                                                                            product desc
                                                                                                        </p>
                                                                                                        <span
                                                                                                            class="btn btn-primary text-uppercase font-weight-bolder px-15 py-3">Order
                                                                                                            Now</span>
                                                                                                    </div>
                                                                                                </label>
                                                                                            </div>
                                                                                        @endforeach
                                                                                    </div>
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <button type="button"
                                                                                        class="btn btn-secondary"
                                                                                        data-dismiss="modal">Close</button>
                                                                                    <button type="submit"
                                                                                        class="btn btn-primary">Update
                                                                                        Plan</a>
                                                                                </div>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal fade" id="addSubscriptionModal"
                                                                    tabindex="-1" role="dialog"
                                                                    aria-labelledby="addSubscriptionModal"
                                                                    aria-hidden="true">
                                                                    <div class="modal-dialog modal-lg"
                                                                        style="min-width:1000px;" role="document">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title"
                                                                                    id="addSubscriptionModal">Add
                                                                                    Subscription</h5>
                                                                                <button type="button" class="close"
                                                                                    data-dismiss="modal"
                                                                                    aria-label="Close">
                                                                                    <span aria-hidden="true">&times;</span>
                                                                                </button>
                                                                            </div>
                                                                            <form
                                                                                action="{{ route('add_subscription', ['subdomain' => $subdomain, 'id' => $user->id]) }}"
                                                                                method="POST" id="addsubscription_form">
                                                                                @csrf
                                                                                <div class="modal-body">
                                                                                    <div
                                                                                        class="row justify-content-center text-center my-0 mb-10">
                                                                                        @foreach ($plans as $key => $plan)
                                                                                            <div
                                                                                                class="col-md-4 col-xxl-4">
                                                                                                <div
                                                                                                    class="mt-2 bg-white rounded-left shadow-sm">
                                                                                                    <label
                                                                                                        for="addsubscription-item-{{ $plan->id }}"
                                                                                                        class="selected-label first-tab">
                                                                                                        <input
                                                                                                            type="radio"
                                                                                                            name="add_subscription_plan"
                                                                                                            {{ $key == 0 ? 'checked' : '' }}
                                                                                                            id="addsubscription-item-{{ $plan->id }}"
                                                                                                            value="{{ $plan->id }}">
                                                                                                        <span
                                                                                                            class="icon"></span>
                                                                                                        <div
                                                                                                            class="pt-25 pb-25 pb-md-10 px-4">
                                                                                                            <h4
                                                                                                                class="mb-15">
                                                                                                                {{ $plan->product->name }}
                                                                                                            </h4>
                                                                                                            <span
                                                                                                                class="px-7 py-3 d-inline-flex flex-center rounded-lg mb-15 bg-primary-o-10">
                                                                                                                <span
                                                                                                                    class="pr-2 opacity-70">$</span>
                                                                                                                <span
                                                                                                                    class="pr-2 font-size-h1 font-weight-bold">{{ $plan->usd_amount }}</span>
                                                                                                                <span
                                                                                                                    class="opacity-70">/&nbsp;&nbsp;{{ $plan->interval }}</span>
                                                                                                            </span>
                                                                                                            <br>
                                                                                                            <p
                                                                                                                class="mb-10 d-flex flex-column text-dark-50">
                                                                                                                <span>{{ $plan->product->description }}</span>
                                                                                                            </p>
                                                                                                            <span
                                                                                                                class="btn btn-primary text-uppercase font-weight-bolder px-15 py-3">Order
                                                                                                                Now</span>
                                                                                                        </div>
                                                                                                    </label>
                                                                                                </div>
                                                                                            </div>
                                                                                        @endforeach
                                                                                    </div>

                                                                                    <div class="row col-xxl-9 mx-auto">
                                                                                        <div class="col-xl-12 mb-10">
                                                                                            <label
                                                                                                class="checkbox checkbox-success">
                                                                                                <input
                                                                                                    id="default_payment_method"
                                                                                                    type="checkbox"
                                                                                                    name="default_payment_method"
                                                                                                    value="1">
                                                                                                <span
                                                                                                    class="mr-3"></span>
                                                                                                Use the default payment
                                                                                                method
                                                                                            </label>
                                                                                        </div>

                                                                                        <div class="col-xl-12 hide_form">
                                                                                            <!--begin::Input-->
                                                                                            <div class="form-group">
                                                                                                <label
                                                                                                    for="card-holder-name">Name
                                                                                                    on Card</label>
                                                                                                <input
                                                                                                    id="addsubscription-holder-name"
                                                                                                    type="text"
                                                                                                    class="form-control form-control-solid form-control-lg"
                                                                                                    name="ccname"
                                                                                                    placeholder="Eg: John Wick" />
                                                                                                <span
                                                                                                    class="form-text text-muted">Please
                                                                                                    enter your Card
                                                                                                    Name.</span>
                                                                                            </div>
                                                                                            <!--end::Input-->
                                                                                        </div>
                                                                                        <div class="col-xl-12 hide_form">
                                                                                            <!--begin::Input-->
                                                                                            <div class="form-group">
                                                                                                <label
                                                                                                    for="addsubscription-element">Card
                                                                                                    Number</label>
                                                                                                <div id="addsubscription-element"
                                                                                                    class="form-control">
                                                                                                    <span
                                                                                                        class="form-text text-muted">Please
                                                                                                        enter your
                                                                                                        Address.</span>
                                                                                                    <div id="addsubscription-errors"
                                                                                                        role="alert">
                                                                                                    </div>
                                                                                                </div>
                                                                                                <!--end::Input-->
                                                                                            </div>
                                                                                        </div>
                                                                                        <!-- <div class="col-md-12 d-flex">
                                                                                                                <input type="checkbox" class="mt-1" name="coupon" id="coupon-1">
                                                                                                                <h6 class="ml-2">Enter your Coupon Code?</h6>
                                                                                                            </div>
                                                                                                            <div class="col-xl-12 hidden-input" id="coupon-input-1">
                                                                                                                <div class="form-group">
                                                                                                                    <label for="coupon-input">Coupon</label>
                                                                                                                    <input type="text"id="coupon-input-field-1" class="form-control form-control-solid form-control-lg" name="ccoupon" placeholder="" required />
                                                                                                                    <span class="form-text text-muted">Please enter
                                                                                                                        Coupon.</span>
                                                                                                                </div>
                                                                                                                <span class="text-danger" id="coupon-error-1">
                                                                                                                </span>
                                                                                                            </div> -->
                                                                                    </div>


                                                                                </div>
                                                                            </form>
                                                                            <div class="modal-footer">
                                                                                <button type="button"
                                                                                    class="btn btn-secondary"
                                                                                    data-dismiss="modal">Close</button>
                                                                                <button type="submit"
                                                                                    class="btn btn-primary"
                                                                                    data-secret="{{ $intent->client_secret ?? '' }}"
                                                                                    id="addsubscription-button">Submit</a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @endif

                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            <!--end: Wizard Step 3-->
                                            <!--end: Wizard Form-->
                                        </div>


                                    </div>
                                </div>
                            </div>
                            <!--end: Wizard Bpdy-->
                        </div>
                        <!--end: Wizard-->
                    </div>
                </div>
                <!--end::Section-->
            </div>
        </div>
    </div>
    </div>
    </div>
    </div>
    <!--end::Entry-->
    @php
        $success = '';
        $error = '';
        if (session()->has('success')) {
            $success = session()->get('success');
        }
        if (session()->has('error')) {
            $error = session()->get('error');
        }
    @endphp
@stop

@section('scripts')
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.4/axios.min.js"></script>
    <script>
        var stripe = Stripe('{{ env('STRIPE_KEY') }}');
    </script>
    <script type="text/javascript" src="{{ asset('../js/admin/billing.js') }}"></script>

    <script>
        $('.order_now_first').click(function() {
            $(".click_next").trigger('click');
        });
    </script>
    <!-- BEGIN COMBINE THE PROFILE BLADE WITH BILLING BLADE -->
    <script>
        var success = "{{ $success }}";
        var error = "{{ $error }}";
        if (success != "") {
            Swal.fire({
                text: success,
                icon: "success",
            });
        }
        if (error != "") {
            Swal.fire({
                text: error,
                icon: "error",
            });
        }
        $('body').on('submit', '#changePass', function(e) {
            var current_password = $("input[name=current_password]").val();
            var new_password = $("input[name=new_password]").val();
            var confirm_password = $("input[name=confirm_password]").val();
            if (current_password == '') {
                e.preventDefault();
                Swal.fire({
                    text: 'current password field required',
                    icon: "error",
                });
            } else if (new_password == '') {
                e.preventDefault();
                Swal.fire({
                    text: 'new password field required',
                    icon: "error",
                });
            } else if (confirm_password == '') {
                e.preventDefault();
                Swal.fire({
                    text: 'confirm password field required',
                    icon: "error",
                });
            } else {
                $(this).submit();
            }
        });
    </script>
    <!-- END COMBINE THE PROFILE BLADE WITH BILLING BLADE -->
@endsection
