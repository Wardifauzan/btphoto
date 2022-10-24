<?php

function sdm_sf_render_fancy0_form($a) {

    // Get SDM download item info
    $post_title = get_the_title($a['id']);
    //$post_content = get_post_meta($a['id'], 'sdm_description', true);
    //$post_image = get_post_meta($a['id'], 'sdm_upload_thumbnail', true);

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
    $email_delivary_msg_handler = sdm_sf_handle_email_delivary_msg( $a['id'], 0 );
    //The squeeze form
    $content = '';
    $content .= '<div class="sdm_sf_tpl_container sdm_sf_tpl_0_container">';

    $content .= '<div class="sdm_sf_tpl_0_title">';
    $content .= '<h3>' . $post_title . '</h3>';
    $content .= '</div>';

    $dl_delivered_by_email = false;
    if (defined("SDM_SF_DELIVERED_VIA_EMAIL")) {
        $submitted_postid = strip_tags($_POST['sdm_squeeze_postid']);
        if ($submitted_postid == $a['id']) {
            //This download's squeeze form was submitted
            $dl_delivered_by_email = true;
        }
    }

    $content .= '<div class="sdm_sf_tpl_0_form">';
    if ($dl_delivered_by_email) {
        $dl_message = $email_delivary_msg_handler['dl_message'];
        if (empty($dl_message)) {
            $dl_message .= __('The download has been sent to your email. Please check your inbox.', 'simple-download-monitor');
        }
        $content .= '<div class="sdm_sf_tpl_0_msg" id="' . $email_delivary_msg_handler['anchor']  . '">' . $dl_message . '</div>';
    } else {
        $content .= '<form method="post" action="' . $email_delivary_msg_handler['action']  . '">';
        if (!$hide_name_field) {
            $content .= '<div class="sdm_sf_tpl_0_name_input">';
            $content .= '<input type="text" name="sdm_squeeze_name" class="sdm_squeeze_name" placeholder="' . __('Name', 'simple-download-monitor') . '" value="" />';
            $content .= '</div>'; //end sdm_sf_tpl_0_name
        }
        $content .= '<div class="sdm_sf_tpl_0_email_input">';
        $content .= '<input type="text" name="sdm_squeeze_email" class="sdm_squeeze_email" placeholder="' . __('Email', 'simple-download-monitor') . '" value="" required />';
        $content .= '</div>'; //end sdm_sf_tpl_0_email

        // Check if reCaptcha is enabled.
        if ($reCaptcha){
            $content .= '<div class="sdm-sf-recaptcha-box sdm-sf-tpl0-recaptcha-box">';
            $content .= '<div class="g-recaptcha sdm-g-recaptcha"></div>';
            $content .= '</div>';
        }

        //Check if Terms & Condition enabled then show checkbox
        $content .= sdmsf_get_checkbox_for_termsncond($a['id']);
        $content .= '<input type="hidden" name="sdm_squeeze_postid" class="sdm_squeeze_postid" value="' . $a['id'] . '" />';
        $content .= '<input type="submit" name="sdm_squeeze_submit" class="sdm_squeeze_submit" value="' . $a['button_text'] . '" />';
        $content .= '</form>';
    }
    $content .= '</div>'; //end sdm_sf_tpl_0_form

    $content .= '</div>'; //end sdm_sf_tpl_0_container
    return $content;
}
