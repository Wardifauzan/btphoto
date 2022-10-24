<?php

function sdm_sf_render_fancy2_form($a)
{

    wp_enqueue_style('sdm_sf_shortcode_styles_2', SDM_SF_URL . '/css/sdm_squeeze_form_tpl_2_styles.css', array(), SDM_SF_VERSION);
    wp_enqueue_script('sdm_sf_shortcode_js_tpl_2', SDM_SF_URL . '/js/sdm_squeeze_form_script_tpl_2.js', array(), SDM_SF_VERSION);

    // Get SDM download item info
    $post_title = get_the_title($a['id']);
    $post_content = get_post_meta($a['id'], 'sdm_description', true);
    $post_image = get_post_meta($a['id'], 'sdm_upload_thumbnail', true);

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
    $email_delivary_msg_handler = sdm_sf_handle_email_delivary_msg( $a['id'], 2 );
    //The squeeze form
    $content = '';

    $content .= '<div class="sdm_sf_tpl_2_wrap sdm_sf_tpl_container">';

    if (!empty($post_image)) {
        $content .= '<div class="sdm_sf_tpl_2_thumb">';
        $content .= '<img src="' . $post_image . '">';
        $content .= '</div>';
    }

    $content .= '<div class="sdm_sf_tpl_2_form"> <!-- for the form -->';
    $content .= '<form method="post" action="' . $email_delivary_msg_handler['action'] . '">';
    //<!-- for heading -->
    $content .= '<div class="sdm_sf_tpl_2_title">';
    $content .= $post_title;
    $content .= '</div>';

    //<!-- for the content -->
    $content .= '<div class="sdm_sf_tpl_2_description">';
    $content .= '<p>' . $post_content . '</p>';
    $content .= '</div>';

    $dl_delivered_by_email = false;
    if (defined("SDM_SF_DELIVERED_VIA_EMAIL")) {
        $submitted_postid = strip_tags($_POST['sdm_squeeze_postid']);
        if ($submitted_postid == $a['id']) {
            //This download's squeeze form was submitted
            $dl_delivered_by_email = true;
        }
    }
    if ($dl_delivered_by_email) {
        $dl_message = $email_delivary_msg_handler['dl_message'];
        if (empty($dl_message)) {
            $dl_message .= __('The download has been sent to your email. Please check your inbox.', 'simple-download-monitor');
        }
        $content .= '<div class="sdm_sf_tpl_2_msg" id="' . $email_delivary_msg_handler['anchor'] . '" ><div class="sdm_sf_tpl_2_msg_text">' . wpautop($dl_message) . '</div></div>';
    } else {

        $content .= '<div class="sdm_sf_tpl_2_input_field">'; // <!-- input fields -->

        if (!$hide_name_field) {
            $content .= '<div class="sdm_sf_tpl_2_input">'; //<!-- for input 1 -->
            $content .= '<input class="sdm_sf_tpl_2_input_info sdm_sf_tpl_2_input_name" type="text" name="sdm_squeeze_name" placeholder="' . __('Name', 'simple-download-monitor') . '" value="">';
            $content .= '</div>';
        }

        $content .= '<div class="sdm_sf_tpl_2_input">'; // <!-- for input 2 -->
        $content .= '<input class="sdm_sf_tpl_2_input_info sdm_sf_tpl_2_input_email" type="text" name="sdm_squeeze_email" placeholder="' . __('Email', 'simple-download-monitor') . '" value="" required>';
        $content .= '</div>';
        // Check if reCaptcha is enabled.
        if ($reCaptcha) {
            $content .= '<div class="sdm-sf-recaptcha-box sdm-sf-tpl2-recaptcha-box">';
            $content .= '<div class="g-recaptcha sdm-g-recaptcha"></div>';
            $content .= '</div>';
        }
        //Check if Terms & Condition enabled then show checkbox
        $content .= sdmsf_get_checkbox_for_termsncond($a['id']);
        $content .= '<input type="hidden" name="sdm_squeeze_postid" class="sdm_squeeze_postid" value="' . $a['id'] . '" />';

        $content .= '<div class="sdm_sf_tpl_2_input">'; //<!-- for input submit -->
        $content .= '<input class="sdm_sf_tpl_2_input_submit" type="submit" name="sdm_squeeze_submit" value="' . $a['button_text'] . '">';
        $content .= '</div>';

        $content .= '</div>';
    }

    $content .= '</form>';

    $content .= '</div>'; // <!-- end of the form -->

    $content .= '</div>'; //<!-- end of sdm_sf_tpl_2_wrap -->

    return $content;
}
