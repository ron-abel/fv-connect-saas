<style>
    .selected-label {
        position: relative;
    }

    .selected-label input {
        display: none;
    }

    .selected-label .icon {
        width: 20px;
        height: 20px;
        border: solid 2px #e3e3e3;
        border-radius: 50%;
        position: absolute;
        top: 15px;
        right: 15px;
        transition: .3s ease-in-out all;
        transform: scale(1);
        z-index: 1;
    }

    .selected-label .icon:before {
        content: "\f00c";
        position: absolute;
        width: 100%;
        height: 100%;
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        font-size: 12px;
        color: #000;
        text-align: center;
        opacity: 0;
        transition: .2s ease-in-out all;
        transform: scale(2);
    }

    .selected-label input:checked+.icon {
        background: #1BC5BD;
        border-color: #1BC5BD;
        transform: scale(1.2);
    }

    .selected-label input:checked+.icon:before {
        color: #fff;
        opacity: 1;
        transform: scale(1);
        margin-left: -7px;
    }

    .selected-label input:checked~.selected-content {
        box-shadow: 0 2px 4px 0 rgba(219, 215, 215, 0.5);
        border: solid 2px #1BC5BD;
    }

    .extra-columns-hide {
        display: none;
    }
</style>

<div class="modal fade" id="updatePlanModal" tabindex="-1" role="dialog" aria-labelledby="updatePlanModal"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" style="min-width:1000px;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updatePlanModal">Select a Plan of the Tenant "<span class="font-weight-bold" id="plan-tenant-name"></span>"</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <div class="row">
                    <div class="col-sm-12">
                        <div class="alert upgrade-plan-error"></div>
                    </div>
                </div>
                <div class="container">
                    <div class="row justify-content-center text-center my-0 plan-list">
                        {{-- @foreach ($plans as $key => $plan)
                            <div class="col-md-4 col-xxl-4 ">
                                <div class="mt-2  bg-white rounded-left shadow-sm">
                                    <label for="changeplan-item-{{ $plan->id }}" class="selected-label first-tab">
                                        <input type="hidden" name="plan_tenant_id" value="" />
                                        <input type="radio" name="change_plan"
                                            id="changeplan-item-{{ $plan->id }}" value="{{ $plan->id }}">
                                        <span class="icon"></span>
                                        <div class="pt-25 pb-25 pb-md-10 px-4">
                                            <h4 class="mb-15">{{ $plan->product->name }}</h4>
                                            <span
                                                class="px-7 py-3 d-inline-flex flex-center rounded-lg mb-15 bg-primary-o-10">
                                                <span class="pr-2 opacity-70">$</span>
                                                <span
                                                    class="pr-2 font-size-h1 font-weight-bold">{{ $plan->usd_amount }}</span>
                                                <span class="opacity-70">/&nbsp;&nbsp;{{ $plan->interval }}</span>
                                            </span>
                                            <br>
                                            <p class="mb-10 d-flex flex-column text-dark-50">
                                                <span>{{ $plan->product->description }}</span>
                                            </p>
                                            <span
                                                class="btn btn-primary text-uppercase font-weight-bolder px-15 py-3">Select
                                                Plan</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        @endforeach --}}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="upgradePlan">Send Mail to Upgrade Plan</a>
            </div>
        </div>
    </div>
</div>
