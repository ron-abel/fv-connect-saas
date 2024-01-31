 //Coupon hide and show feature
 $(document).on('click', 'input#coupon', function() {
    $('#coupon-input').toggle();
})

//Coupon hide and show feature
$(document).on('click', 'input#coupon-1', function() {
    $('#coupon-input-1').toggle();
})

const billingUrl = 'billing/submit'
var elements = stripe.elements();
var style = {
    base: {
        color: '#32325d',
        fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
        fontSmoothing: 'antialiased',
        fontSize: '16px',
        '::placeholder': {
            color: '#aab7c4'
        }
    },
    invalid: {
        color: '#fa755a',
        iconColor: '#fa755a'
    }
};
var card = elements.create('card', {
    hidePostalCode: true,
    style: style
});
card.mount('#card-element');
card.addEventListener('change', function(event) {
    console.log(event);
    var displayError = document.getElementById('card-errors');
    if (event.error) {
        displayError.textContent = event.error.message;
    } else {
        displayError.textContent = '';
    }
});
const cardHolderName = document.getElementById('card-holder-name');
const cardButton = document.getElementById('card-button');
const clientSecret = cardButton.dataset.secret;
 let noError = true;
cardButton.addEventListener('click',function(e){
     noError = true;
     $('#coupon-error').text(' ')
      if ($('input#coupon-input-field').val() !== '') {
        axios.get('/api/validate/coupon/' + $("#coupon-input-field").val()).then(res => {
            if (!res.data.success) {
                $('#coupon-error').text(res.data.error)
                noError = false;
            }
            else{
                setPaymentMethod()
            }
        })
    }
    else{
        setPaymentMethod()
    }
})

async function setPaymentMethod(){
       const {
        setupIntent,
        error
    } = await stripe.confirmCardSetup(
        clientSecret, {
            payment_method: {
                card: card,
                billing_details: {
                    name: cardHolderName.value
                }
            }
        }
    );
    if (error) {
        var errorElement = document.getElementById('card-errors');
        errorElement.textContent = error.message;
    }
    else {
         paymentMethodHandler(setupIntent.payment_method, 'kt_form');

        }
    }

var updatecard_elements = stripe.elements();
var update_card = updatecard_elements.create('card', {
    hidePostalCode: true,
    style: style
});

update_card.mount('#updatecard-element');
update_card.addEventListener('change', function(event) {
    var displayError = document.getElementById('updatecard-errors');
    if (event.error) {
        displayError.textContent = event.error.message;
    } else {
        displayError.textContent = '';
    }
});

const updatecardHolderName = document.getElementById('updatecard-holder-name');
const updatecardButton = document.getElementById('updatecard-button');
const updateclientSecret = updatecardButton.dataset.secret;
updatecardButton.addEventListener('click', async (e) => {

    const {
        setupIntent,
        error
    } = await stripe.confirmCardSetup(
        updateclientSecret, {
            payment_method: {
                card: update_card,
                billing_details: {
                    name: updatecardHolderName.value
                }
            }
        }
    );
    if (error) {
        var errorElement = document.getElementById('updatecard-errors');
        errorElement.textContent = error.message;
    } else {
        paymentMethodHandler(setupIntent.payment_method, 'update_card_form');
    }
});

var addsubscription_elements = stripe.elements();
var addsubscription_card = addsubscription_elements.create('card', {
    hidePostalCode: true,
    style: style
});
addsubscription_card.mount('#addsubscription-element');
addsubscription_card.addEventListener('change', function(event) {
    var displayError = document.getElementById('addsubscription-errors');
    if (event.error) {
        displayError.textContent = event.error.message;
    } else {
        displayError.textContent = '';
    }
});
const addsubscriptionHolderName = document.getElementById('addsubscription-holder-name');
const addsubscriptionButton = document.getElementById('addsubscription-button');
const addsubscriptionSecret = addsubscriptionButton.dataset.secret;
addsubscriptionButton.addEventListener('click', async (e) => {
    if ($('input#coupon-1').is(':checked')) {
        axios.get('/api/validate/coupon/' + $("#coupon-input-field-1").val()).then(res => {
            if (!res.data.success) {
                $('#coupon-error-1').text(res.data.error)
                return false
            }
            else{
                submitValue();    
             }
        })
    }
    else{
        submitValue()
    }
    async function submitValue(){

    var default_value = $('#default_payment_method').is(':checked');

    if (default_value) {
        $('#addsubscription_form').submit();
    }

    const {
        setupIntent,
        error
    } = await stripe.confirmCardSetup(
        addsubscriptionSecret, {
            payment_method: {
                card: addsubscription_card,
                billing_details: {
                    name: addsubscriptionHolderName.value
                }
            }
        }
    );
    if (error) {
        var errorElement = document.getElementById('addsubscription-errors');
        errorElement.textContent = error.message;
    } else {
        paymentMethodHandler(setupIntent.payment_method, 'addsubscription_form');
    }
}
});


function paymentMethodHandler(payment_method, form_name) {
    var form = document.getElementById(form_name);
    var hiddenInput = document.createElement('input');
    hiddenInput.setAttribute('type', 'hidden');
    hiddenInput.setAttribute('name', 'payment_method');
    hiddenInput.setAttribute('value', payment_method);
    form.appendChild(hiddenInput);
    form.submit();

    // axios.post(billingUrl,{
    //     payment_method:  payment_method,
    //     plan: plan,
    //     ccname: cardHolderName.value
    // }).then(res=>{
    //     if(res.status==200){
    //         wizard.goTo(wizard.getNewStep())
    //     }
    //     else {
    //         Swal.fire({
    //             text: "Sorry, looks like there are some errors detected, please try again.",
    //             icon: "error",
    //             buttonsStyling: false,
    //             confirmButtonText: "Ok, got it!",
    //             customClass: {
    //                 confirmButton: "btn font-weight-bold btn-light"
    //             }
    //         }).then(function () {
    //             KTUtil.scrollTop();
    //         });
    //     }
    // })
}

$("#default_payment_method").click(function() {
    // assumes element with id='button'
    $(".hide_form").toggle('hide');
});