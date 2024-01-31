@extends('superadmin.layouts.default')

@section('title', 'Billing Plans')

@section('content')

	<div class="main-content container">
		<!--begin::Card-->
		<div class="card card-custom mt-6">
			<div class="card-header flex-wrap border-0 pt-6 pb-0">
				<div class="card-title">
					<h3 class="card-label">Billing Plans Mangement</h3>
				</div>
				<div class="card-toolbar">
					<!--begin::Dropdown-->
					{{-- <div class="dropdown dropdown-inline mr-2">
						<button type="button" class="btn btn-light-primary font-weight-bolder dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<span class="svg-icon svg-icon-md">
							<!--begin::Svg Icon | path:assets/media/svg/icons/Design/PenAndRuller.svg-->
							<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
								<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
									<rect x="0" y="0" width="24" height="24" />
									<path d="M3,16 L5,16 C5.55228475,16 6,15.5522847 6,15 C6,14.4477153 5.55228475,14 5,14 L3,14 L3,12 L5,12 C5.55228475,12 6,11.5522847 6,11 C6,10.4477153 5.55228475,10 5,10 L3,10 L3,8 L5,8 C5.55228475,8 6,7.55228475 6,7 C6,6.44771525 5.55228475,6 5,6 L3,6 L3,4 C3,3.44771525 3.44771525,3 4,3 L10,3 C10.5522847,3 11,3.44771525 11,4 L11,19 C11,19.5522847 10.5522847,20 10,20 L4,20 C3.44771525,20 3,19.5522847 3,19 L3,16 Z" fill="#000000" opacity="0.3" />
									<path d="M16,3 L19,3 C20.1045695,3 21,3.8954305 21,5 L21,15.2485298 C21,15.7329761 20.8241635,16.200956 20.5051534,16.565539 L17.8762883,19.5699562 C17.6944473,19.7777745 17.378566,19.7988332 17.1707477,19.6169922 C17.1540423,19.602375 17.1383289,19.5866616 17.1237117,19.5699562 L14.4948466,16.565539 C14.1758365,16.200956 14,15.7329761 14,15.2485298 L14,5 C14,3.8954305 14.8954305,3 16,3 Z" fill="#000000" />
								</g>
							</svg>
							<!--end::Svg Icon-->
						</span>Export</button>
						<!--begin::Dropdown Menu-->
						<div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
							<!--begin::Navigation-->
							<ul class="navi flex-column navi-hover py-2">
								<li class="navi-header font-weight-bolder text-uppercase font-size-sm text-primary pb-2">Choose an option:</li>
								<li class="navi-item">
									<a href="#" class="navi-link">
										<span class="navi-icon">
											<i class="la la-print"></i>
										</span>
										<span class="navi-text">Print</span>
									</a>
								</li>
								<li class="navi-item">
									<a href="#" class="navi-link">
										<span class="navi-icon">
											<i class="la la-copy"></i>
										</span>
										<span class="navi-text">Copy</span>
									</a>
								</li>
								<li class="navi-item">
									<a href="#" class="navi-link">
										<span class="navi-icon">
											<i class="la la-file-excel-o"></i>
										</span>
										<span class="navi-text">Excel</span>
									</a>
								</li>
								<li class="navi-item">
									<a href="#" class="navi-link">
										<span class="navi-icon">
											<i class="la la-file-text-o"></i>
										</span>
										<span class="navi-text">CSV</span>
									</a>
								</li>
								<li class="navi-item">
									<a href="#" class="navi-link">
										<span class="navi-icon">
											<i class="la la-file-pdf-o"></i>
										</span>
										<span class="navi-text">PDF</span>
									</a>
								</li>
							</ul>
							<!--end::Navigation-->
						</div>
						<!--end::Dropdown Menu-->
					</div> --}}
					<!--end::Dropdown-->
					<!--begin::Button-->
					<a href="{{ route('add_billing_plan') }}" class="btn btn-primary font-weight-bolder">
					<span class="svg-icon svg-icon-md">
						<!--begin::Svg Icon | path:assets/media/svg/icons/Design/Flatten.svg-->
						<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
							<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
								<rect x="0" y="0" width="24" height="24" />
								<circle fill="#000000" cx="9" cy="15" r="6" />
								<path d="M8.8012943,7.00241953 C9.83837775,5.20768121 11.7781543,4 14,4 C17.3137085,4 20,6.6862915 20,10 C20,12.2218457 18.7923188,14.1616223 16.9975805,15.1987057 C16.9991904,15.1326658 17,15.0664274 17,15 C17,10.581722 13.418278,7 9,7 C8.93357256,7 8.86733422,7.00080962 8.8012943,7.00241953 Z" fill="#000000" opacity="0.3" />
							</g>
						</svg>
						<!--end::Svg Icon-->
					</span>Add Billing Plan</a>
					<!--end::Button-->
				</div>
			</div>
			<div class="card-body">
				<!--begin: Search Form-->
				<!--begin::Search Form-->
				<div class="mb-7">
					<div class="row align-items-center">
						<div class="col-lg-9 col-xl-8">
							<div class="row align-items-center">
								<div class="col-md-4 my-2 my-md-0">
									<div class="input-icon">
										<input type="text" class="form-control" placeholder="Search..." id="kt_datatable_search_query" />
										<span>
											<i class="flaticon2-search-1 text-muted"></i>
										</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!--end::Search Form-->
				<!--end: Search Form-->
				<!--begin: Datatable-->
				<table class="datatable datatable-bordered datatable-head-custom" id="kt_datatable">
					<thead>
						<tr>
							<th title="Field #1">Plan ID</th>
							<th title="Field #2">Plan Name</th>
							<th title="Field #3">Plan Price</th>
                            <th title="Field #4">Active</th>
                            <th title="Field #4">Custom Plan</th>
                            <th title="Field #5">Plan Trial Days</th>
							<th title="Field #6">Actions</th>
						</tr>
					</thead>
					<tbody>
						@foreach($billing_plans_data as $single_billing_plan)
						<tr>
							<td>{{ $single_billing_plan->stripe_plan_id }}</td>
							<td>{{ $single_billing_plan->plan_name }}</td>
							<td>$ {{ $single_billing_plan->plan_price }} / {{ $single_billing_plan->plan_interval }}</td>
                            <td>
                                @if($single_billing_plan->plan_is_active == 1)
                                <i class="fa fa-check" style="color:#1bc5bd;"></i>
                                @else
                                <i class="fa fa-check"></i>
                                @endif
                            </td>
                            <td>
                                @if($single_billing_plan->plan_is_default == 1)
                                <i class="fa fa-check"></i>
                                @else
                                <i class="fa fa-check" style="color:#1bc5bd;"></i>
                                @endif
                            </td>
                            <td>{{ $single_billing_plan->plan_trial_days }}</td>
							<td>
								<span style="overflow: visible; position: relative; width: 125px;">
									<a href="{{ route('edit_billing_plan', ['billing_plan_id' => $single_billing_plan->id]) }}" class="btn btn-sm btn-clean btn-icon mr-2" title="Edit details">
										<i class="icon-xl la la-eye"></i>
									</a>
								</span>
							</td>
						</tr>
						@endforeach
					</tbody>
				</table>
				<!--end: Datatable-->
			</div>
		</div>
		<!--end::Card-->
	</div>

@stop
