<?php

function sdm_sf_render_fancy_single_form($a)
{
    $get_opts = get_option('sdm_squeeze_form');
    $hide_sf = isset($get_opts['hide_sf']) && $get_opts['hide_sf'] === 'on' ? true : false;
    $hide_name_field = isset($get_opts['hide_name_field']) && $get_opts['hide_name_field'] === 'on' ? true : false;
    $visitor_name = sdm_get_logged_in_user();
    if ($hide_sf && $visitor_name !== false) {
        // Hide from logged-in users is enabled. User is logged-in. Hide the form.
        $content = '';
        return $content;
    }
?>
    <style>
        /*** Template 0 styles ***/
        .sdm_sf_tpl_0_container {
            display: block;
            /*padding: 20px;*/
            /*margin: 10px 0;*/
            max-width: 300px;
            /*background-color: #CCCCCC;*/
            color: #000000;
            /*border: 1px solid #bababa;*/
            font-family: "Open Sans", Helvetica, Arial, sans-serif;
            line-height: 1.7;
        }
        .sdm_sf_tpl_0_name_input .sdm_squeeze_name {
            text-align: center;
            width: 100%;
            border: 1px solid #DDDDDD;
            border-radius: 3px 3px 3px 3px;
            padding: 10px 0;
            margin: 5px 0;
            color: #333;
        }
        .sdm_sf_tpl_0_email_input .sdm_squeeze_email {
            text-align: center;
            width: 100%;
            border: 1px solid #DDDDDD;
            border-radius: 3px 3px 3px 3px;
            padding: 10px 0;
            margin: 5px 0;
            color: #333;
        }
        .sdm_squeeze_submit {
            text-align: center;
            width: 100%;
            padding: 10px 0;
            margin: 5px 0;
            /*color: #FFFFFF;*/
            font-size: 14px;
            font-weight: bold;
            /*background-color: #14B2CF;*/
            background-image: none;
            /*box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2), 0 -3px 0 rgba(0, 0, 0, 0.1) inset;*/
            /*text-shadow: 1px 1px 1px rgba(151, 71, 0, 0.35);*/
            border: medium none !important;
            border-radius: 3px 3px 3px 3px;
            cursor: pointer;
        }
        .sdm_sf_tpl_0_msg {
            color: green;
            text-align: justify;
        }
        .sdm_sf_error_container {
            display: block;
            background: #f5f3b0;
            color: red;
            border-radius: 3px;
            padding: 3px 10px;
        }
    </style>
<?php
    //The squeeze form
    wp_enqueue_script('sdm_sf_shortcode_js', SDM_SF_URL. '/js/sdm_squeeze_form_script.js', array(), SDM_SF_VERSION);
    wp_localize_script('sdm_sf_shortcode_js','sdm_sf_msg',array(
        'name_required' => '* '.'Name is required.',
        'email_required' => '* '.'Email is required.',
        'wrong_email' => '* '.'A valid email address is required.',
    ));
    $content = '';
    $content .= '<div class="sdm_sf_tpl_container sdm_sf_tpl_0_container">';

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
        $dl_message = isset($get_opts['sf_dl_message']) ? $get_opts['sf_dl_message'] : '';
        if (empty($dl_message)) {
            $dl_message .= __('The download has been sent to your email. Please check your inbox.', 'simple-download-monitor');
        }
        $content .= '<div class="sdm_sf_tpl_0_msg">' . $dl_message . '</div>';
    } else {
        $content .= '<form method="post" action="">';
        if (!$hide_name_field) {
            $content .= '<div class="sdm_sf_tpl_0_name_input">';
            $content .= '<input type="text" name="sdm_squeeze_name" class="sdm_squeeze_name" placeholder="' . __('Name', 'simple-download-monitor') . '" value="" />';
            $content .= '</div>'; //end sdm_sf_tpl_0_name
        }
        $content .= '<div class="sdm_sf_tpl_0_email_input">';
        $content .= '<input type="text" name="sdm_squeeze_email" class="sdm_squeeze_email" placeholder="' . __('Email', 'simple-download-monitor') . '" value="" required />';
        $content .= '</div>'; //end sdm_sf_tpl_0_email
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
