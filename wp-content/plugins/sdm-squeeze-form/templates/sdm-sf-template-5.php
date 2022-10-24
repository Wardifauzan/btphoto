<?php

function sdm_sf_render_fancy5_form($a) {
     if(isset($_REQUEST['sdm_squeeze_submit'])){   //checks if form has been submitted or not...
        $content = '';
        return $content;
    }
    wp_enqueue_style('sdm_sf_shortcode_styles_5', SDM_SF_URL . '/css/sdm_squeeze_form_tpl_5_styles.css', array(), SDM_SF_VERSION);

    $sdm_sf_tpl5_uniqeid = uniqid();
    $sdm_sf_tpl5_close_uniqeid = uniqid();
    // Get SDM download item contents
    $post_title = get_the_title($a['id']);
    $post_content = get_post_meta($a['id'], 'sdm_description', true);
    $post_image = get_post_meta($a['id'], 'sdm_upload_thumbnail', true);

    //get download item info
    $item_file_size = get_post_meta($a['id'], 'sdm_item_file_size', true);
    $item_file_size_checkbox = get_post_meta($a['id'], 'sdm_item_show_file_size_fd', true);

    $item_version = get_post_meta($a['id'], 'sdm_item_version', true);
    $item_version_checkbox = get_post_meta($a['id'], 'sdm_item_show_item_version_fd', true);

    $offset_count = get_post_meta($a['id'], 'sdm_count_offset', true);

     //Long length title limit
    $max_title_length = 60;
    if(strlen($post_title)>$max_title_length){
        $post_title = substr($post_title, 0, $max_title_length) . '...';
    }
     //Long length description limit
    $max_description_length = 600;
    if(strlen($post_content)>$max_description_length){
        $post_content = substr($post_content, 0, $max_description_length) . '...';
    }

    //get fancy5 delay value
    $delay = $a['delay'];

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
    $content = '';
    $content .= '<div id ="' . $sdm_sf_tpl5_uniqeid . '" class="sdm_sf_tpl5">';
    $content .= '<div class="' . $sdm_sf_tpl5_close_uniqeid . ' sdm_sf_tpl5_close">&times;</div>';

      $content .= '<div class="sdm_sf_tpl5_modal">';
        $content .= '<div class="sdm_sf_tpl5_left_col">';
            $content .= '<div class="sdm_sf_tpl5_img">';
                $content .= '<img class="sdm_sf_tpl5_img_img"src="' .$post_image. '"alt="Image">';
            $content .= '</div>';
            $content .= '<div class="sdm_sf_tpl5_title">'.$post_title.'</div>';

            //show get download file info
            if($item_file_size_checkbox !== null){
            $content .= '<div class="sdm_sf_tpl5_download_file_info">'.__('File Size: ', 'simple-download-monitor').''.$item_file_size.'  '.$item_file_size_checkbox.'</div>';
            }
            if($item_version_checkbox !== null){
            $content .= '<div class="sdm_sf_tpl5_download_file_info">'.__('Version: ', 'simple-download-monitor').''.$item_version.'</div>';
            }
            if(!empty($offset_count)){
            $content .= '<div class="sdm_sf_tpl5_download_file_info">'.__('Offset Count: ', 'simple-download-monitor').''.$offset_count.'</div>';
            }

        $content .= '</div>';
        $content .= '<div class="sdm_sf_tpl5_right_col">';
            $content .= '<div class="sdm_sf_tpl5_description">'.$post_content.'</div>';
            $content .= '<div class="sdm_sf_tpl5_form">';

             $content .= '<form method="post" action="" class="sdm_sf_tpl4_form">';
    if (!$hide_name_field) {
        $content .= '<div class="sdm_sf_tpl5_input">';
        $content .= '<input type="text" name="sdm_squeeze_name" class="sdm_sf_tpl5_input_name" placeholder="' . __('Name', 'simple-download-monitor') . '">';
        $content .= '</div>';
    }
                $content .= '<div class="sdm_sf_tpl5_input">';
                $content .= '<input type="email" name="sdm_squeeze_email" class="sdm_sf_tpl5_input_email" placeholder="' . __('Email', 'simple-download-monitor') . '">';
                $content .= '</div>';
    // Check if reCaptcha is enabled.
    if ($reCaptcha){
        $content .= '<div class="sdm-sf-recaptcha-box sdm-sf-tpl5-recaptcha-box">';
        $content .= '<div class="g-recaptcha sdm-g-recaptcha"></div>';
        $content .= '</div>';
    }
                $content .= sdmsf_get_checkbox_for_termsncond($a['id']);
                $content .= '<input type="hidden" name="sdm_squeeze_postid" class="sdm_squeeze_postid" value="' . $a['id'] . '" />';
                $content .= '<div class="sdm_sf_tpl5_input">';
                $content .= '<button type="submit" name="sdm_squeeze_submit" class="sdm_sf_tpl5_input_submit">' . $a['button_text'] . '</button>';
                $content .= '</div>';

             $content .= '</form>';

            //for smartphone view
                $content .= '<div class="sdm_sf_tpl5_input_cancel '.$sdm_sf_tpl5_close_uniqeid.'">';
                $content .= '<button>' . __('Cancel', 'simple-download-monitor') . '</button>';
                $content .= '</div>';

            $content .= '</div>';
        $content .= '</div>';
    $content .= '</div>';
  //$content .= '</div>';
$content .= '</div>';

 $content .= <<<EOT
            <script>

jQuery(document).ready(function ($) {

    $('.{$sdm_sf_tpl5_close_uniqeid}').click(function () {
        $('#{$sdm_sf_tpl5_uniqeid}').hide();
    });

    $(window).click(function(e) {
         if(e.target == $('#{$sdm_sf_tpl5_uniqeid}')[0]){
        $('#{$sdm_sf_tpl5_uniqeid}').hide();
         }
         });

        setTimeout(function(){
            $('.sdm_sf_tpl5').css("display", "flex");
         }, {$delay});


    var err_cont = $('<div class="sdm_sf_tpl5_error_container"></div>');

    $('input.sdm_sf_tpl5_input_name,input.sdm_sf_tpl5_input_email').keypress(function () {
        $(this).siblings('.sdm_sf_tpl5_error_container').remove();
    });

    // SDM squeeze form download button
    $('.sdm_sf_tpl5_input_submit').click(function (e) {

        // Get name, email and validate it
        var curr_form_div = $(this).closest('.sdm_sf_tpl5_form');
        var name_input = curr_form_div.find('.sdm_sf_tpl5_input_name');
        var email_input = curr_form_div.find('.sdm_sf_tpl5_input_email');
        get_name = name_input.val();
        get_email = email_input.val();

        // If name not present
        var name_required = sdm_js_object.sdm_sf_name_required;
        if (name_required == 'yes') {
            if (get_name === '') {
                if (name_input.siblings('.sdm_sf_tpl5_error_container').length === 0) {
                    var name_err_msg = err_cont;
                    name_err_msg.html(sdm_sf_msg.name_required);
                    name_err_msg.insertAfter(name_input);
                }
                name_input.addClass("sdm_sf_tpl5_error_foc").focus();

                return false;
            } else {
                if (name_input.siblings('.sdm_sf_tpl5_error_container').length !== 0) {
                    name_input.siblings('.sdm_sf_tpl5_error_container').remove();
                }
            }
        }

        // If email not present
        if (get_email === '') {
            if (email_input.siblings('.sdm_sf_tpl5_error_container').length === 0) {
                var email_err_msg = err_cont;
                email_err_msg.html(sdm_sf_msg.email_required);
                email_err_msg.insertAfter(email_input);
            }
            email_input.addClass("sdm_sf_tpl5_error_foc").focus();
            return false;
        } else {
            if (email_input.siblings('.sdm_sf_tpl5_error_container').length !== 0) {
                email_input.siblings('.sdm_sf_tpl5_error_container').remove();
            }
        }

        // Validate email
        regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        test_email = regex.test(get_email);

        if (test_email === false) {
            if (email_input.siblings('.sdm_sf_tpl5_error_container').length === 0) {
                var wrong_email_err_msg = err_cont;
                wrong_email_err_msg.html(sdm_sf_msg.wrong_email);
                wrong_email_err_msg.insertAfter(email_input);
            }
            email_input.addClass("sdm_sf_tpl5_error_foc").focus();
            return false;
        } else {
            if (email_input.siblings('.sdm_sf_tpl5_error_container').length !== 0) {
                email_input.siblings('.sdm_sf_tpl5_error_container').remove();
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
