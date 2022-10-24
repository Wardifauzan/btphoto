<?php

function sdm_sf_render_fancy4_form($a) {

    wp_enqueue_style('sdm_sf_shortcode_styles_4', SDM_SF_URL . '/css/sdm_squeeze_form_tpl_4_styles.css', array(), SDM_SF_VERSION);

    // unique id generator for
    $sdm_sf_tpl4_uniqeid = uniqid("sf_popup_window_tpl4");
    $sdm_sf_tpl4_btn_uniqeid = uniqid("sf_tpl4_btn");
    $sdm_sf_tpl4_close_uniqeid = uniqid("sf_tpl4_close");

    // Get SDM download item info
    $post_title = get_the_title($a['id']);
    $post_content = get_post_meta($a['id'], 'sdm_description', true);
    $post_image = get_post_meta($a['id'], 'sdm_upload_thumbnail', true);

    $content = '';
    
    $get_opts = get_option('sdm_squeeze_form');
    $hide_sf = isset($get_opts['hide_sf']) && $get_opts['hide_sf'] === 'on' ? true : false;
    $hide_name_field = isset($get_opts['hide_name_field']) && $get_opts['hide_name_field'] === 'on' ? true : false;
    $visitor_name = sdm_get_logged_in_user();
    if ($hide_sf && $visitor_name !== false) {
        // Hide from logged-in users is enabled. User is logged-in. Hide the form.
        $content = '';
        return $content;
    }
    $reCaptcha = isset($get_opts['enable_captcha']) && $get_opts['enable_captcha'] === 'on';
    $email_delivary_msg_handler = sdm_sf_handle_email_delivary_msg( $a['id'], 4 );
    $content .= '';

    //Check if delivered by email is enabled
    $dl_delivered_by_email = false;
    if (defined("SDM_SF_DELIVERED_VIA_EMAIL")) {
        $submitted_postid = strip_tags($_REQUEST['sdm_squeeze_postid']);
        if ($submitted_postid == $a['id']) {
            //This download's squeeze form was submitted
            $dl_delivered_by_email = true;
        }
    }

    if ($dl_delivered_by_email) {
        //Show the "success" message (where the shortcode output goes)
        $dl_message = $email_delivary_msg_handler['dl_message'];
        if (empty($dl_message)) {
            $dl_message .= __('The download has been sent to your email. Please check your inbox.', 'simple-download-monitor');
        }

        $content .= '<a name="'.$email_delivary_msg_handler['anchor'].'"></a>';
        $content .= '<div class="sdm_sf_tpl_4_msg"><div class="sdm_sf_tpl_4_msg_text">' . $dl_message . '</div></div>';

    }
    else {
        //Show the popup trigger button (where the shortcode output goes)
        $content .= '<div class="sdm_sf_tpl4_btn">';
        $content .= '<button id="' . $sdm_sf_tpl4_btn_uniqeid . '" class="sdm_sf_tpl4_button_element">' . $a['button_text'] . '</button>';
        $content .= '</div>'; // end of sdm_sf_tpl4_btn
    }

    //The popup window markup
    $content .= '<div id ="' . $sdm_sf_tpl4_uniqeid . '" class="sdm_sf_tpl4">';
    $content .= '<div class="sdm_sf_tpl4_wrap">';
    $content .= '<div class="sdm_sf_tpl4_head">';

    //Long length title limit
    $max_title_length = 60;
    if(strlen($post_title)>$max_title_length){
        $post_title = substr($post_title, 0, $max_title_length) . '...';
    }

    $content .= '<span class="sdm_sf_tpl4_title">';
    $content .= $post_title;
    $content .= '</span>';

    $content .= '<span id="' . $sdm_sf_tpl4_close_uniqeid . '" class = "sdm_sf_tpl4_close">&times;</span>';
    $content .= '</div>'; //end of sdm_sf_tpl4_head, which is for the title and the close button.

    if (!empty($post_image)) {
        $content .= '<div class="sdm_sf_tpl4_img">';
        $content .= '<img src="' . $post_image . '" alt="Image">';
        $content .= '</div>';   //end of the image section. It is an optional element.
    }
    if (!empty($post_content)) {
        $content .= '<div class="sdm_sf_tpl4_description">';
        $content .= $post_content;
        $content .= '</div>';   //end of the description section. It is an optional element.
    }

    $content .= '<form method="post" action="'.$email_delivary_msg_handler['action'].'" class="sdm_sf_tpl4_form">';

    //The input fields section (in the poup winodw)
    if (!$hide_name_field) {
        $content .= '<div class="sdm_sf_tpl4_user_input_div">';
        $content .= '<input type="text" class="sdm_sf_tpl4_input sdm_sf_tpl4_input_name" name="sdm_squeeze_name" placeholder="' . __('Name', 'simple-download-monitor') . '" value="">';
        $content .= '</div>';
    }
    $content .= '<div class="sdm_sf_tpl4_email_input_div">';
    $content .= '<input type="email" class="sdm_sf_tpl4_input sdm_sf_tpl4_input_email" name="sdm_squeeze_email" placeholder="' . __('Email', 'simple-download-monitor') . '" value="" required>';
    $content .= '</div>';
    // Check if reCaptcha is enabled.
    if ($reCaptcha){
        $content .= '<div class="sdm-sf-recaptcha-box sdm-sf-tpl4-recaptcha-box" style="margin-top: 10px">';
        $content .= '<div class="g-recaptcha sdm-g-recaptcha"></div>';
        $content .= '</div>';
    }
    $content .= sdmsf_get_checkbox_for_termsncond($a['id']);
    $content .= '<input type="hidden" name="sdm_squeeze_postid" class="sdm_squeeze_postid" value="' . $a['id'] . '" />';

    $content .= '<button type="submit" class="sdm_sf_tpl4_input sdm_sf_tpl4_submit" name="sdm_squeeze_submit">' . $a['button_text'] . '</button>';

    $content .= '</form>';  //end of the form

    $content .= '</div>'; //end of sdm_sf_tpl4_wrap

    $content .= '</div>';  //end of the sdm_sf_tpl4

    //This section is for the needed js codes
    $content .= <<<EOT
            <script>
jQuery(document).ready(function ($) {
    //var sdm_sf_tpl4_target= "'." + sdm_sf_tpl4_js_object.tpl4_target_class +"'";
    //var sdm_sf_tpl4_target_string = String(sdm_sf_tpl4_target);
    //var test_var_tpl4 = '.sdm_sf_tpl4';
    $('#{$sdm_sf_tpl4_btn_uniqeid}').click(function () {
       // alert(sdm_sf_tpl4_target_string);
        $('#{$sdm_sf_tpl4_uniqeid}').css("display", "flex");
    });
    $('#{$sdm_sf_tpl4_close_uniqeid}').click(function () {
        $('#{$sdm_sf_tpl4_uniqeid}').hide();
    });
//    window.click(function(e){
//      if(e.target == var_sdm_sf_tpl4){
//      var_sdm_sf_tpl4.style.display = 'none';
//      }
//    });
   $(window).click(function(e) {
       console.dir(e.target);
       // var sdm_sf_modaltest = document.getElementById('{$sdm_sf_tpl4_uniqeid}');
     //var sdm_sf_modaltest = $('#{$sdm_sf_tpl4_uniqeid}');
   if(e.target == $('#{$sdm_sf_tpl4_uniqeid}')[0]){
     $('#{$sdm_sf_tpl4_uniqeid}').css("display", "none");
    }
   });

    var err_cont = $('<div class="sdm_sf_tpl4_error_container"></div>');

    $('input.sdm_sf_tpl4_input_name,input.sdm_sf_tpl4_input_email').keypress(function () {
        $(this).siblings('.sdm_sf_tpl4_error_container').remove();
    });

    // SDM squeeze form download button
    $('.sdm_sf_tpl4_submit').click(function (e) {

        // Get name, email and validate it
        var curr_form_div = $(this).closest('.sdm_sf_tpl4_wrap');
        var name_input = curr_form_div.find('.sdm_sf_tpl4_input_name');
        var email_input = curr_form_div.find('.sdm_sf_tpl4_input_email');
        get_name = name_input.val();
        get_email = email_input.val();

        // If name not present
        var name_required = sdm_js_object.sdm_sf_name_required;
        if (name_required == 'yes') {
            if (get_name === '') {
                if (name_input.siblings('.sdm_sf_tpl4_error_container').length === 0) {
                    var name_err_msg = err_cont;
                    name_err_msg.html(sdm_sf_msg.name_required);
                    name_err_msg.insertAfter(name_input);
                }
                name_input.addClass("sdm_sf_tpl4_error_foc").focus();

                return false;
            } else {
                if (name_input.siblings('.sdm_sf_tpl4_error_container').length !== 0) {
                    name_input.siblings('.sdm_sf_tpl4_error_container').remove();
                }
            }
        }

        // If email not present
        if (get_email === '') {
            if (email_input.siblings('.sdm_sf_tpl4_error_container').length === 0) {
                var email_err_msg = err_cont;
                email_err_msg.html(sdm_sf_msg.email_required);
                email_err_msg.insertAfter(email_input);
            }
            email_input.addClass("sdm_sf_tpl4_error_foc").focus();
            return false;
        } else {
            if (email_input.siblings('.sdm_sf_tpl4_error_container').length !== 0) {
                email_input.siblings('.sdm_sf_tpl4_error_container').remove();
            }
        }

        // Validate email
        regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        test_email = regex.test(get_email);

        if (test_email === false) {
            if (email_input.siblings('.sdm_sf_tpl4_error_container').length === 0) {
                var wrong_email_err_msg = err_cont;
                wrong_email_err_msg.html(sdm_sf_msg.wrong_email);
                wrong_email_err_msg.insertAfter(email_input);
            }
            email_input.addClass("sdm_sf_tpl4_error_foc").focus();
            return false;
        } else {
            if (email_input.siblings('.sdm_sf_tpl4_error_container').length !== 0) {
                email_input.siblings('.sdm_sf_tpl4_error_container').remove();
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
</script>
EOT;

    return $content;
}
