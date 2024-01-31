@extends('admin.layouts.default')

@section('title', 'Payment History')

@section('content')

<div class="container">
    <!--begin::Card-->
    <div class="card card-custom mt-6">
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">Payment History</h3>
            </div>
        </div>
        <div class="card-body">
            <!--begin: Search Form-->
            <!--begin::Search Form-->
            {{-- <div class="mb-7">
                <div class="row align-items-center">
                    <div class="col-lg-9 col-xl-8">
                        <div class="row align-items-center">
                            <div class="col-md-4 my-2 my-md-0">
                                <div class="input-icon">
                                    <input
                                        type="text"
                                        class="form-control"
                                        placeholder="Search..."
                                        id="kt_datatable_search_query"
                                    />
                                    <span>
                                        <i class="flaticon2-search-1 text-muted"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}
            <!--end::Search Form-->
            <!--end: Search Form-->
            <!--begin: Datatable-->
            <table
                class="datatable datatable-bordered table  datatable-head-custom"
                id=""
            >
                <thead>
                    <tr>
                        <th title="Field #1">Date</th>
                        <th title="Field #2">Invoice</th>
                        <th title="Field #3">Products</th>
                        <th title="Field #4">Payment System</th>
                        <th title="Field #5">Amount</th>
                        <th title="Field #6">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $d)
                    @foreach($d['products'] as $product)
                    <tr>
                        <td>{{ \Carbon\Carbon::createFromTimeStamp($product->created )->format('Y-m-d h:i'); }}</td>
                        <td>{{ $d['stripe_customer']->invoice_prefix }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $d['pm_type'] }}</td>
                        <td>$ {{ $stripe->prices->all(['product' => $product->id])->data[0]->unit_amount/100 }}</td>
                        <td><i
                                class="fa {{ $product->active ? 'fa-check text-success' : 'fa-times text-danger'  }}"></i>
                        </td>
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>

            </table>
            <!--end: Datatable-->
        </div>
    </div>
    <!--end::Card-->
</div>

@stop