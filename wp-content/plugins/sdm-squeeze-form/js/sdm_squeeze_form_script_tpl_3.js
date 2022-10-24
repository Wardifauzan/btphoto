jQuery(document).ready(function ($) {
    var err_cont = $('<div class="sdm_sf_tpl_3_error_container"></div>');

    $('input.sdm_sf_tpl_3_input_name,input.sdm_sf_tpl_3_input_email').keypress(function () {
	$(this).siblings('.sdm_sf_tpl_3_error_container').remove();
    });

    // SDM squeeze form download button
    $('.sdm_sf_tpl_3_input_submit').click(function (e) {

	// Get name, email and validate it
	var curr_form_div = $(this).closest('.sdm_sf_tpl_container');
	var name_input = curr_form_div.find('.sdm_sf_tpl_3_input_name');
	var email_input = curr_form_div.find('.sdm_sf_tpl_3_input_email');
	get_name = name_input.val();
	get_email = email_input.val();

	// If name not present
        var name_required = sdm_js_object.sdm_sf_name_required;
        if (name_required == 'yes'){        
            if (get_name === '') {
                if (name_input.siblings('.sdm_sf_tpl_3_error_container').length === 0) {
                    var name_err_msg = err_cont;
                    name_err_msg.html(sdm_sf_msg.name_required);
                    name_err_msg.insertAfter(name_input);
                }
                name_input.focus();
                return false;
            } else {
                if (name_input.siblings('.sdm_sf_tpl_3_error_container').length !== 0) {
                    name_input.siblings('.sdm_sf_tpl_3_error_container').remove();
                }
            }
        }

        // If email not present
	if (get_email === '') {
	    if (email_input.siblings('.sdm_sf_tpl_3_error_container').length === 0) {
		var email_err_msg = err_cont;
		email_err_msg.html(sdm_sf_msg.email_required);
		email_err_msg.insertAfter(email_input);
	    }
	    email_input.focus();
	    return false;
	} else {
	    if (email_input.siblings('.sdm_sf_tpl_3_error_container').length !== 0) {
		email_input.siblings('.sdm_sf_tpl_3_error_container').remove();
	    }
	}

	// Validate email
	regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	test_email = regex.test(get_email);

	if (test_email === false) {
	    if (email_input.siblings('.sdm_sf_tpl_3_error_container').length === 0) {
		var wrong_email_err_msg = err_cont;
		wrong_email_err_msg.html(sdm_sf_msg.wrong_email);
		wrong_email_err_msg.insertAfter(email_input);
	    }
	    email_input.focus();
	    return false;
	} else {
	    if (email_input.siblings('.sdm_sf_tpl_3_error_container').length !== 0) {
		email_input.siblings('.sdm_sf_tpl_3_error_container').remove();
	    }
	}
        
        //Check if terms enabled then validate 
        if ($('.sdm-termscond-checkbox').length) {
            var current_form = $(this).closest("form");//Find the current form
            if ($('.agree_termscond', current_form).is(':checked')) {
                $('.sdm-termscond-checkbox', current_form).removeClass('sdm_general_error_msg');
                return true;
            } else {
                $('.sdm-termscond-checkbox', current_form).addClass('sdm_general_error_msg');
                return false;
            }
        }        
    });
    // end of SDM squeeze form download button

});