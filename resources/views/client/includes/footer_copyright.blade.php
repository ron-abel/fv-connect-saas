<footer class="lc-md bg-accent">
    <div class="footer-r">{{ __('Powered by') }} <img src="{{ asset('img/client/vinetegrate-emblem.png') }}"
            class="footer-logo"><span><a href="http://vinetegrate.com/" target="_blank">{{ __('Vinetegrate') }}</a>
            - {{ __('VineConnect Client Portal. Copyright © 2021 | All Rights Reserved') }} |
            @version('footer-version')</span>
    </div>
</footer>

<!-- Intercom Snippet -->
<script>
  window.intercomSettings = {
    app_id: "yskhl95g",
    name: "", 							
    email: "", 								
	user_id: "",		 						
	tenant_name: "",						
	tenant_law_firm_name: "", 		
    user_hash: "{{hash_hmac( 'sha256', rand() , config('services.intercom.identiy_secret_key')) }}"  // when identity verification, used.
  };
</script>
<script>
(function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',w.intercomSettings);}else{var d=document;var i=function(){i.c(arguments);};i.q=[];i.c=function(args){i.q.push(args);};w.Intercom=i;var l=function(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/yskhl95g';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);};if(document.readyState==='complete'){l();}else if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();
</script>
