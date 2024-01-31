@extends('admin.layouts.default')

@section('title', 'VineConnect Admin - Support &amp; Documentation')

@section('content')

<!--begin::Subheader-->
<div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
    <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
        <!--begin::Info-->
        <div class="d-flex align-items-center flex-wrap mr-2">
            <!--begin::Page Title-->
            <h4 class="text-dark font-weight-bold mt-2 mb-2 mr-5"><i class="icon-xl la la-support"></i> VineConnect Support &amp; Documentation</h4>
            <!--end::Page Title-->
        </div>
        <!--end::Info-->
    </div>
</div>
<!--end::Subheader-->

<!--begin::Entry-->
<div class="d-flex flex-column-fluid">
    <!--begin::Container-->
    <div class="container">
        <!--begin::Dashboard-->
        <!--begin::Row-->
        <div class="row">
            <div class="col-md-12">
                <!--begin::Card-->
                <div class="card card-custom gutter-b example example-compact">
                    <div class="notice" style="padding: 25px 50px;text-align: center;font-size: 18px;background-color: #000;color: #fff;">Our New Support Portal Is Here: <a href="https://intercom.help/vinetegrate" target="_blank" >VineConnect Support</a></div>
                    <div class="pg_container wmenu">
						<div class="inner">
							<div class="pg_menu">
								<ul>
									<li><a href="#aboutVineConnect">About VineConnect</a></li>
									<li><a href="#howclientportalworks">How Client Portal Works</a></li>
                                    <li><a href="#webhooksconfig">Configuring Webhooks</a></li>
									<li><a href="#portalmessaging">Portal Messaging & Feedback</a></li>
									<li><a href="#preptogolive">Prep Your Client Portal</a></li>
									<li><a href="#launchingclientportal">Launch Your New Client Portal</a>
								</ul>
							</div>
						</div><!-- inner -->
					<div class="pg_content">
						<i class="fas fa-link"></i> <h5 id="aboutVineConnect">About VineConnect</h5>
						<p>We at Vinetegrate are pleased to present <b>VineConnect Client Portal!</b> This web application is in a constant state of improvement, and we sincerely hope VineConnect serves your and your clients with our unique set of tools for many years to come. We welcome your feedback, bug reports, and ideas for new features in future releases! For now, use this document to get your app fully configured and operating. For any help, contact us here.</p>
						<div class="callout_subtle lightgrey"><i class="far fa-lightbulb" style="color:#383838;padding-right:5px;"></i> Feedback, Bug Reports, and Ideas: <a href="mailto:support@vinetegrate.com">support@vinetegrate.com</a></div>
						<div class="clear"></div>
						<i class="fas fa-link"></i> <h5 id="howclientportalworks">How Client Portal Works</h5>
						<p>Client Portal works by using your Filevine Org's configured API keys to "look up" clients by their first and last name, and their personal mobile number.</p>
						<p>Client Portal is locked by 2-Factor Authorization ("2FA"). The cell phone number the client provides to log in <b>MUST</b> exactly match a number listed in their Contact Record. If the information matches, a one-time code will be sent, and the client can access their portal.</p>
                        <a href="/assets/img/client/support_client_contact_record_example.png" data-lightbox="guide-images">
						    <img src="/assets/img/client/support_client_contact_record_example.png" class="guide-images" title"Image of Client Contact Card" alt="Screenshot image of the Client Contact Card in Filevine">
                        </a>
						<div class="img_caption">Screenshot image of the Client Contact Card in Filevine. The name and at least one SMS-capable phone number labeled as "Personal Mobile" must match the login attempt. The client name must also match the project name as "Last Name, First Name" including the comma.</div>
						<p>When the client accesses Client Portal, they are able to toggle between the various cases/projects they may have with your firm. You can control whether or not <strong>Archived Cases</strong> should be accessible by cleints in the <a href="/settings" />Client Portal Settings</a> page.</p>
						<p>There's only a few ways that a "look up" attempt fails. Consider this your <b>3-Step Troubleshoot Process for any clients who may be struggling to log in:</b></p>
						<ul>
							<li><span class="text-danger">Bad Credentials: </span>Your Filevine API Credentials are incorrectly configured or inactive.</li>
							<li><span class="text-danger">Name Doesn't Exactly Match: </span>The first and last name the client enters must EXACTLY match the first and last name of their Contact Record in Filevine.</li>
							<li><span class="text-danger">Phone Number Doesn't Match: </span>The cell phone number attempted at login doesn't match or isn't listed on the Client's Contact Record. Only one number listed in the Contact Record needs to match in order to release a 2FA code.</li>
							<li><span class="text-danger">Phone Number Isn't Labeled: </span>The cell phone number attempted at login must be labeled as "Personal Mobile" in the Client's Contact Record.</li>
						</ul>
                        <p>You can monitor the <a href="/admin/dashboard" />Client Usage Dashbaord</a> for attempts at Client Portal logins. The dashboard displays all successful and failed attempts. If you notice a particular client struggling to login, use the 3-step troubleshooting guide above to help them make a proper request and gain access.</p>

                        <i class="fas fa-link"></i> <h5 id="webhooksconfig">How to Config Webhooks and Why They Matter</h5>
						<p><b>Webhooks</b> are payloads of data delivered to a third-party application triggered by some action taken inside of Filevine. With VineConnect, you can deliver meaningful data from Filevine for several useful pre-defined trigger actions like phase change, contact created, and project created.</p>
						<p>Why does this matter? Because visual automation platforms like Zapier and Workado are priced based on number of tasks running. If you're using Zapier to filter out actions then you're going to run your monthly bill up quickly, especially as you grow and more tasks are run.</p>
						<p>With VineConnect, you can filter actions for phase change, and deliver the data to different Zapier or Workado endpoints without running your tasks up! If you're new to Zapier webhooks, here's a support article to get you started <a href="https://zapier.com/page/webhooks/" />using Webhooks in Zapier</a>.</p>
						<p><b>Payload Data</b> delivered from this feature is contextually meaningful with project name and details, project vitals, and client's name and contact information, allowing you to do vitually anything once the data hits your dedicated Zapier or Workado endpoint.</p>
						<p>Some tips for using webhooks:
						<ul>
							<li>Create a new project phase in your Filevine project type template called "Test" and place it at the bottom of your list of phases. This allows you to move test cases to this phase without triggering tasks from a live project phase.</li>
							<li>Use the <b>Phase Change</b> filter on our webhooks tool instead of filtering on Zapier to save tasks, money, and time. Create individual webhooks for each of your phases you want to perform an automated action for.</li>
							<li>Some ideas to get you started: Send a welcome email when a project changes to your "approved sign up" phase. Alert a partner to review a file when it hits a "pending" or review phase. Ping your partners in an email when a settlement is reached indicated by a phase change</li>
						</ul>
						</p>

						<i class="fas fa-link"></i> <h5 id="portalmessaging">Portal Messaging & Client Feedback</h5>
						<p>One of the two most unique features of Client Portal is messaging and the collection of feedback from your client about how individual member of your legal team is performing. On the front end of Client Portal, the "Leave Feedback" button appears below each Legal Team Member name. When submitted, the feedback is logged in the Client Feedback table on the Client Usage Dashboard.</p>
						<p>You can send a single message to display in the front end of the Client Portal using a note on the Project Feed. Our system will look for the most recent <b>note that is hashtagged with #clientportal</b>. You can have as many of these notes as you would like, but only the most recent one displays. If there are no hashtagged notes, the system returns simply: <em>"There are no messages at this time. Please check back later."</em></p>
						<i class="fas fa-link"></i> <h5 id="preptogolive">Prep To Go Live with Client Portal</h5>
						<p>It's almost time to launch Client Portal! But before you do, there's few things you should prepare for. First, because Client Portal is completely web based, we recommend creating a button somewhere on your website that launches the application. Your Client Portal front-end login URL is:<br>
						<pre>https://[your_tenant_name].vinetegrate.com</pre><br>
						<p>So drive your clients there! A simple button in the header of your website like this would do the trick (consult with your web design company for propoer installation):</p>
						<p><button id="cp_button" class="cp-button" style="display: block;font-size: 14px;text-transform: uppercase;border: 2px solid #26A9E0;background: #fff;padding: 5px 15px;border-radius: 5px;-moz-border-radius: 5px;-webkit-border-radius: 5px;"><a href="#" />Client Portal</button></a></p>
						<p>Here are some more tips for prepping to launch your new Client Portal:</p>
						<ul>
							<li>Thoroughly test all scenarios you've configured using a test project in your live Filevine environment. Test the front of the client portal by setting your test case client name to yourself and your cell phone number to bypass 2FA.</li>
							<li>Ensure you've configured all your project type template phase categories, and you've mapped and described each of your project phases.</li>
							<li>If you've configured webhooks, test them by sending fake events from your test project.</li>
							<li>Invite a list of trusted clients to test the Portal and provide feedback.</li>
							<li>Make a plan to not only announce your Portal, but to ensure your clients are regularly reminded to use the Portal.</li>
						</ul>
						<i class="fas fa-link"></i> <h5 id="launchingclientportal">Announce and Release Client Portal To Your Clients!</h5>
						<p>You've configured, you've tested, and now it's time to launch! The 3 Keys for Successful Adoption by your clients is today and going forward is <b>Promotion, Reminders, and Assistance.</b></p>
						<p>First and foremore, you have to promote the heck out of it. Here's some ideas:
						<ul>
							<li>Announce Client Portal in a blast email</li>
							<li>Announce on Social Media</li>
							<li>Place a link in your email footer with a message</li>
						</ul>
						</p>
						</div><!-- pg_content -->
					</div><!-- pg_container -->
                </div>
                <!--end::Card-->
            </div><!-- col-md-12 -->
        </div><!-- row -->
    </div><!-- container -->
</div><!-- d-flex flex-column fluid -->
@endsection
