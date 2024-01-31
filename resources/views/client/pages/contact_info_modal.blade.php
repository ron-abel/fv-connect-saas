<form method="post" name="feedback_form"
    action="{{ route('update_contact_info', ['subdomain' => session()->get('subdomain')]) }}">
    @csrf
    <div class="modal-body contact-info-update">
        <h5 class="modal-title">{{ __('Please let us know if anything has changed.') }}</h5>
        <input type="hidden" value="{{ $client_id }}" name="client_id">
        <input type="hidden" value="{{ $project_id }}" name="project_id">
        <input type="hidden" value="{{ $contact_project_email_address }}" name="contact_project_email_address">
        <input type="hidden" value="{{ $project_name }}" name="project_name">
        <div class="accordion-started accordion-bral row contact-accordian">
            @if (isset($contact_info['emails']))
                @foreach ($contact_info['emails'] as $key => $single_email)
                    <div class="w-100">
                        <input class="ac-input" id="ac-email-{{ $key }}" name="accordion-2" type="radio">
                        <label class="ac-label" for="ac-email-{{ $key }}"><span>{{ __('Email:') }}
                                {{ $single_email['address'] }}</span><i></i></label>
                        <div class="article ac-content">
                            <div class="form-group">
                                <i class="fa fa-pencil-square-o" onclick="edit_field(this)" aria-hidden="true"></i>
                                <i class="fa fa-times-circle" onclick="close_field(this)" aria-hidden="true"></i>
                                <h5>{{ __('Email Address:') }} </h5>
                                <span>{{ $single_email['address'] }}</span>
                                <input name="email[]" type="text" value="{{ $single_email['address'] }}"
                                    class="form-control fivelineContactName" autocomplete="off">
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            @if (isset($contact_info['addresses']))
                @foreach ($contact_info['addresses'] as $key => $single_address)
                    <div class="w-100">
                        <input class="ac-input" id="ac-address-{{ $key }}" name="accordion-3" type="radio">
                        <label class="ac-label" for="ac-address-{{ $key }}"><span>{{ __('Address:') }}
                                {{ $single_address['fullAddress'] }}</span><i></i></label>
                        <div class="article ac-content">
                            <div class="form-group">
                                <i class="fa fa-pencil-square-o" onclick="edit_field(this)" aria-hidden="true"></i>
                                <i class="fa fa-times-circle" onclick="close_field(this)" aria-hidden="true"></i>
                                <h5>{{ __('Street Address:') }} </h5>
                                <span>{{ isset($single_address['line1']) ? $single_address['line1'] : '' }}</span>
                                <input name="line1[]" type="text" value="{{ $single_address['line1'] }}"
                                    class="form-control fivelineContactName" autocomplete="off">
                            </div>
                            <div class="form-group">
                                <i class="fa fa-pencil-square-o" onclick="edit_field(this)" aria-hidden="true"></i>
                                <i class="fa fa-times-circle" onclick="close_field(this)" aria-hidden="true"></i>
                                <h5>{{ __('City:') }} </h5>
                                <span>{{ isset($single_address['city']) ? $single_address['city'] : '' }}</span>
                                <input name="city[]" type="text" value="{{ isset($single_address['city']) ? $single_address['city'] : '' }}"
                                    class="form-control fivelineContactName" autocomplete="off">
                            </div>
                            <div class="form-group">
                                <i class="fa fa-pencil-square-o" onclick="edit_field(this)" aria-hidden="true"></i>
                                <i class="fa fa-times-circle" onclick="close_field(this)" aria-hidden="true"></i>
                                <h5>{{ __('State:') }} </h5>
                                <span>{{ isset($single_address['state']) ? $single_address['state'] : '' }}</span>
                                <select name="state[]" class="form-control fivelineContactName" autocomplete="off">
                                    @foreach ($states as $key => $single_state)
                                        <option {{ $key == (isset($single_address['state']) ? $single_address['state'] : '') ? 'selected' : '' }}
                                            value="{{ $key }}">{{ $single_state }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <i class="fa fa-pencil-square-o" onclick="edit_field(this)" aria-hidden="true"></i>
                                <i class="fa fa-times-circle" onclick="close_field(this)" aria-hidden="true"></i>
                                <h5>{{ __('Zip:') }} </h5>
                                <span>{{ isset($single_address['postalCode']) ? $single_address['postalCode'] : '' }}</span>
                                <input name="postalCode[]" type="text" value="{{ isset($single_address['postalCode']) ? $single_address['postalCode'] : '' }}"
                                    class="form-control fivelineContactName" autocomplete="off">
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            @if (isset($contact_info['phones']))
                @foreach ($contact_info['phones'] as $key => $single_phone)
                    <div class="w-100">
                        <input class="ac-input" id="ac-phone-{{ $key }}" name="accordion-4" type="radio">
                        <label class="ac-label" for="ac-phone-{{ $key }}"><span>{{ __('Phone:') }}
                                {{ $single_phone['rawNumber'] }}</span><i></i></label>
                        <div class="article ac-content">
                            <div class="form-group">
                                <i class="fa fa-pencil-square-o" onclick="edit_field(this)" aria-hidden="true"></i>
                                <i class="fa fa-times-circle" onclick="close_field(this)" aria-hidden="true"></i>
                                <h5>{{ __('Phone:') }} </h5>
                                <span>{{ $single_phone['rawNumber'] }}</span>
                                <input name="phone[]" type="text" value="{{ $single_phone['rawNumber'] }}"
                                    class="form-control fivelineContactName" autocomplete="off">
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif


        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
    </div>
</form>

<script>
    function edit_field(params) {
        var parent = $(params).parent();
        $(parent).find('span').hide();
        $(parent).find('.form-control').show();
        $(parent).find('.fa-pencil-square-o').hide();
        $(parent).find('.fa-times-circle').show();
    }

    function close_field(params) {
        var parent = $(params).parent();
        $(parent).find('span').show();
        $(parent).find('.form-control').hide();
        $(parent).find('.fa-pencil-square-o').show();
        $(parent).find('.fa-times-circle').hide();
    }
</script>
