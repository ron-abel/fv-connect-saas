@extends('admin.layouts.default')

@section('title', 'VineConnect Admin - Mass Updates Tool')

@section('content')

<!--begin::Subheader-->
<div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
    <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
        <!--begin::Info-->
        <div class="d-flex align-items-center flex-wrap mr-2">
            <!--begin::Page Title-->
            <h4 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Mass Updates to Filevine Tools</h4>
            <!--end::Page Title-->

        </div>
        <!--end::Info-->
    </div>
</div>
<!--end::Subheader-->
<div class="overlay loading"></div>
<div class="spinner-border text-primary loading" role="status">
    <span class="sr-only">Loading...</span>
</div>

<div class="d-flex flex-column-fluid">
    <!--begin::Container-->
    <div class="container">
        <!--begin::Row-->
        <div class="row">
            <div class="col-md-12">
                <!--begin::Card-->
                <div class="card card-custom gutter-b example example-compact">
                    <div class="card-header">
                        <h5 class="card-title mt-7">Mass Create/Update Contacts Tool</h5>
					</div>
					<div class="pg_container">
						<div class="pg_content">
							<p>This tool allows you the ability to <b>Mass Upload Contacts</b> and/or <b>Mass Update Contact Labels</b> into your Filevine Org. This is a feature not currently supported by Filevine's advanced permissions tools.</p>
							<h6>Mass Create New Contacts</h6>
							<p>Download the template provided below and add each contact to be created in your Filevine Org as an individual row. The only required column is name; the rest of the columns are optional. We suggest giving the file a unique name. Upload and click "Add Contacts". Give the browser some time to do its magic, and in a few minutes, you'll notice your contacts are now in Filevine. </p>
                            <div class="callout_subtle lightgrey">
								Download the <a href="{{ asset('sample_templates/Multi-Create%20Contacts.csv') }}" download>Multi-Create Contacts Template</a>
							</div>

                            </br></br>

							<h6>Mass Update Contact Type Label Tool</h6>
							<p>You may need to add new Contact Type labels to a list of contacts and this tool allows you to upload a CSV template to mass add Contact Type labels at once. For each contact you update with a new label, add the full name (first and last) and the exact contact label you want to change it to.</p>
							<p><b>Use Case Examples:</b>
								<ul>
									<li>You have all your attorney contact records as contact type "Attorney", but maybe you want to split out "Defense Attorneys" and "Referring Attorneys" in your Filevine database.</li>
									<li>You have a list of contacts from Filevine without a contact type label, and you want to add a label to all of them at once instead of doing it manually one-by-one.</li>
									<li>You want to separate "Treatment Providers" into sub groups.</li>
								</ul>
							</p>
							<p>These are all instances for which this Mass Update Contact Type Label tool can be quite handy.</p>
                            <p><b>Note:</b> Give both tools plenty of time to work - refreshing or closing the page before you receive success messages will end the process. For each new run, give your file a unique name, otherwise you will be prompted to check the box "Replace the Original File" before you can proceed.
							</p>
							<div class="callout_subtle lightgrey">
								Download the <a href="{{ asset('sample_templates/Add%20Person%20Types.csv') }}" download>Multi-Update Contact Types Template</a>
							</div>
						</div>
					</div>
						<div class="card-body">
                        <!-- Container fluid  -->
                        <div class="container-fluid">
                            <div class="row card card-body">
                                <form id="fileToUploadForm" method="post" enctype="multipart/form-data">
                                    <input type="file" name="fileToUpload" id="fileToUpload" accept=".csv">
                                    <input style="margin-right:5px;" type="checkbox" name="removeOriginalFile" value="yes">Replace the Original File
                                    <input style="margin-left:5px;" type="submit" value="Upload" name="submit">
                                </form>

                                <div class="col-md-12 row" style="margin-top:30px;">
                                    <input type="hidden" id="uploaded_file" data-val="" style="display:none;">
                                    <button id="addContactsBtn" class="btn ml-1 btn-success btn-sm text-white" style="margin-right:50px;">Add Contacts</button>
                                    <button class="btn ml-1 btn-success btn-sm text-white" id="addPersonTypesBtn">Add PersonTypes</button>
                                </div>

                                <hr style="margin-bottom: 50px;">
                                <div id="preview"></div><br>
                                <div id="err"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<style>
    .overlay {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 100;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, .7);
        transition: .3s linear;
        z-index: 1000;
    }
    .loading {
        display: none;
    }
    .spinner-border.loading {
        position: fixed;
        top: 48%;
        left: 48%;
        z-index: 1001;
        width: 5rem;
        height: 5rem;
    }
  </style>
@stop
