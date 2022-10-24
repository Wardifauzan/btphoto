<?php

use \DrewM\MailChimp\MailChimp;

// Register the Shortcodes
add_shortcode('sdm-squeeze-form', 'sdm_squeeze_form_shortcode');

// Use to Check form submissions
add_action('init', 'sdm_sf_check_submission');

// Create shortcode function
function sdm_squeeze_form_shortcode($atts)
{

    // If shortcode is on the page then enqueue necessary scripts and styles
    wp_enqueue_style('sdm_sf_shortcode_styles', SDM_SF_URL . '/css/sdm_squeeze_form_styles.css', array(), SDM_SF_VERSION);
    wp_enqueue_script('sdm_sf_shortcode_js', SDM_SF_URL . '/js/sdm_squeeze_form_script.js', array(), SDM_SF_VERSION);
    wp_localize_script('sdm_sf_shortcode_js', 'sdm_sf_msg', array(
        'name_required' => '* ' . 'Name is required.',
        'email_required' => '* ' . 'Email is required.',
        'wrong_email' => '* ' . 'A valid email address is required.',
    ));

    // Get shortcode attributes (and set defaults)
    $a = shortcode_atts(array(
        'id' => '',
        'fancy' => '0',
        'button_text' => 'Download Now',
        'delay' => '1000'
    ), $atts);

    $content = '';
    if ($a['fancy'] == '0') {
        include_once('templates/sdm-sf-template-0.php');
        $content .= sdm_sf_render_fancy0_form($a);
    } else if ($a['fancy'] == '1') {
        include_once('templates/sdm-sf-template-1.php');
        $content .= sdm_sf_render_fancy1_form($a);
    } else if ($a['fancy'] == '2') {
        include_once('templates/sdm-sf-template-2.php');
        $content .= sdm_sf_render_fancy2_form($a);
    } else if ($a['fancy'] == '3') {
        include_once('templates/sdm-sf-template-3.php');
        $content .= sdm_sf_render_fancy3_form($a);
    } else if ($a['fancy'] == '4') {
        include_once('templates/sdm-sf-template-4.php');
        $content .= sdm_sf_render_fancy4_form($a);
    } else if ($a['fancy'] == '5') {
        include_once('templates/sdm-sf-template-5.php');
        $content .= sdm_sf_render_fancy5_form($a);
    } else {
        //Default is fancy 0
        include_once('templates/sdm-sf-template-0.php');
        $content .= sdm_sf_render_fancy0_form($a);
    }

    return $content;
}

function sdm_sf_check_submission()
{

    if (isset($_POST['sdm_squeeze_submit'])) {

        SDM_Debug::log('Sqeeze form submitted. Processing submission.');

        // reCaptcha validation.
        sdm_sf_recaptcha_verify();

        $name = strip_tags($_POST['sdm_squeeze_name']);
        $email = strip_tags($_POST['sdm_squeeze_email']);
        $postid = strip_tags($_POST['sdm_squeeze_postid']);
        $download_id = $postid;
        $download_title = get_the_title($download_id);
        $date = date('Y-m-d H:i:s');

        $name = explode(' ', $name, 2);  // Limit to two array items
        $fname = $name[0];
        $lname = (isset($name[1]) ? $name[1] : ''); // Sometimes user isn't providing last name
        // Update database with user download info
        global $wpdb;
        $insert_row = $wpdb->insert($wpdb->prefix . 'sdm_squeeze_form', array('post_id' => $postid, 'fname' => $fname, 'lname' => $lname, 'email' => $email, 'date' => $date), array('%s', '%s', '%s', '%s', '%s'));
        SDM_Debug::log('Sqeeze form submission recorded in DB.');

        // If MailChimp is enabled... add user to list
        $get_opt = get_option('sdm_squeeze_form');
        if (isset($get_opt['enable_mailchimp']) && $get_opt['enable_mailchimp'] === 'on') {

            SDM_Debug::log('Mailchimp signup is enabled.');

            // Get MailChimp plugin options
            $mc_apikey = $get_opt['mailchimp_api_key'];
            $mc_listname = $get_opt['mailchimp_list_name'];

            // Require MailChimp lib
            require_once SDM_SF_PATH . '/lib/MailChimp.php';

            $api_error = false;

            try {
                $mc_api = new MailChimp($mc_apikey);
            } catch (Exception $e) {
                // some error occured
                //echo $e->getMessage();
                $api_error = true;
                SDM_Debug::log('Failed to initialize MailChimp object.', false);
            }
            if (!$api_error) {
                // Take list name and convert to list id
                $mc_lists = $mc_api->get('lists');
                $mc_listid = '';
                foreach ($mc_lists['lists'] as $list) {
                    if (strtolower($list['name']) == strtolower($mc_listname)) {
                        $mc_listid = $list['id'];
                        break;
                    }
                }
                if (!empty($mc_listid)) {
                    SDM_Debug::log('Making API call to subscribe user to list ID: ' . $mc_listid);
                    //list found, let's subscribe user
                    $merge_vars = array('FNAME' => $fname, 'LNAME' => $lname, 'INTERESTS' => '');

                    $status = 'subscribed';
                    $api_arr = array('email_address' => $email, 'status_if_new' => $status, 'status' => $status, 'merge_fields' => $merge_vars);
                    //Interest grps not used atm.
                    //if (isset($interests)) {
                    //    $api_arr['interests'] = $interests;
                    //}

                    //$retval = $mc_api->post("lists/" . $mc_listid . "/members", $api_arr);

                    $member_hash = md5(strtolower($email)); //The MD5 hash of the lowercase version of the list member's email address.
                    $retval = $mc_api->put("lists/" . $mc_listid . "/members/" . $member_hash, $api_arr);

                    if (!$mc_api->success()) {
                        //Some error occured
                        SDM_Debug::log('API error occured during mailchimp signup.', false);
                        SDM_Debug::log('Error details: ' . $mc_api->getLastError(), false);
                    } else {
                        SDM_Debug::log('Mailchimp sign-up API query successful.');
                    }

                } else {
                    SDM_Debug::log('Could not find a list for the given list name: ' . $mc_listname, false);
                }
            }
        }

        //Check if we have "Deliver via Email" option enabled. Then we will email the download link and it will be handled by the core plugin.
        if (isset($get_opt['email_download']) && $get_opt['email_download'] == 'on') {
            $email_subj = $get_opt['email_subject'];
            $email_body = $get_opt['email_body'];
            $email_from = $get_opt['email_from'];
            //The homepage URL.
            $homepage = get_bloginfo('url');
            //The download URL that is handled by the core plugin (with logging).
            $download_url = $homepage . '/?smd_process_download=1&download_id=' . $postid;

            $keysArr = array('{first_name}', '{last_name}', '{product_link}', '{email}', '{download_id}', '{download_title}');
            $valsArr = array($fname, $lname, $download_url, $email, $download_id, $download_title);
            $email_body = str_replace($keysArr, $valsArr, $email_body);
            $headers = array('From: ' . $email_from);
            wp_mail($email, $email_subj, $email_body, $headers);
            define("SDM_SF_DELIVERED_VIA_EMAIL", true);

            // Checks if a redirection url exists.
            $get_opts = get_option('sdm_squeeze_form');
            $has_redirect_url = isset($get_opts['sf_redirect_url']) && $get_opts['sf_redirect_url'];
            if ($has_redirect_url) {
                if (wp_redirect($get_opts['sf_redirect_url'], 302, 'sdm-squeeze-form')) {
                    exit;
                }
            }

            return;
        }

        // Get Download File URL.
        $file = get_post_meta($postid, 'sdm_upload', true);
        $download_link = $file;

        if (empty($file)) {  // This download has no downloadable file
            echo '<p class="sdm_sf_error">Configuration Error! This download has no downloadable file. Please specify a downloadable file for this download item.</p>';
            exit;
        }

        // Allow plugin extensions to hook into download request.
        do_action('sdm_sf_process_download_request', $download_id, $download_link);

        //Record the download to the logs table (for the logs menu of the core plugin)
        if (function_exists('sdm_insert_download_to_logs_table')) {
            sdm_insert_download_to_logs_table($download_id);
        }

        // Only local file can be dispatched (downloaded using PHP)
        if (stripos($download_link, WP_CONTENT_URL) === 0) {
            //This is a local file. Get the file path.
            $file_path = path_join(WP_CONTENT_DIR, ltrim(substr($download_link, strlen(WP_CONTENT_URL)), '/'));

            //sdm_dispatch_file( $file_path );//Could try to dispatch the file using the core plugin's dispatch function (terminates script execution on success)

            $fsize = filesize($file_path);

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream'); // http://stackoverflow.com/a/20509354
            header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . $fsize);

            ob_end_clean();
            readfile($file_path);
            exit;

        }//End of PHP file dispatching method.

        /* Fallback condition */
        // Could not dispatch the file. As a fallback redirect to the file URL (and terminate script execution).
        sdm_redirect_to_url($download_link);

    }
}
