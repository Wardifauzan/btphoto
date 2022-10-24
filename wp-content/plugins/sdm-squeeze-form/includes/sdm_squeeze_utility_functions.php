<?php

/*
 * Shows a terms and condtions checkbox if it is enabled in the core plugin.
 */
function sdmsf_get_checkbox_for_termsncond( $id, $args = array(), $class = '' ) {
    $data = "";
    $main_advanced_opts = get_option( 'sdm_advanced_options' );
    $termscond_enable = isset( $main_advanced_opts[ 'termscond_enable' ] ) ? true : false;
    if ( $termscond_enable ) {
	$data .= '<div class="sdm-termscond-checkbox">';
	$data .= '<input type="checkbox" class="agree_termscond" value="1"/> ' . __( 'I agree to the ', 'simple-download-monitor' ) . '<a href="' . $main_advanced_opts[ 'termscond_url' ] . '" target="_blank">' . __( 'terms and conditions', 'simple-download-monitor' ) . '</a>';
	$data .= '</div>';
    }
    return $data;
}

function sdmsf_process_export_submission_data_to_csv() {
    global $wpdb;
    $sf_table_name = $wpdb->prefix . 'sdm_squeeze_form';;

    $submissions_csv_file_path = SDM_SF_PATH . "sdm_squeeze_form_submissions.csv";

    $fp = fopen($submissions_csv_file_path, 'w');

    $header_names = array('Download Title', 'Download ID', 'First Name', 'Last Name', 'Email', 'Date');
    fputcsv($fp, $header_names);

    $db_result = $wpdb->get_results("SELECT * FROM $sf_table_name ORDER BY id DESC", OBJECT);

    foreach ($db_result as $row) {
        $post_id = $row->post_id;

        if(empty($post_id)){
            //The Post ID for this record is missing. Go to the next record.
            continue;
        }

        $item_title = get_the_title($post_id);

	$fields = array(
	    $item_title,
	    $post_id,
	    stripslashes($row->fname),
	    stripslashes($row->lname),
	    $row->email,
	    $row->date,
	);

	$fields = apply_filters('sdmsf_export_customers_csv_row', $fields, $post_id);
	fputcsv($fp, $fields);
    }
    fclose($fp);

    $file_url = SDM_SF_URL . '/sdm_squeeze_form_submissions.csv';
    return $file_url;
}

function sdmsf_reset_export_files(){
    $files_list = array(
	SDM_SF_PATH . 'sdm_squeeze_form_submissions.csv',
    );

    foreach($files_list as $file_to_empty){
	$f = @fopen($file_to_empty, "r+");
	if ($f !== false) {
	    ftruncate($f, 0);
	    fclose($f);
	}
    }
}

/**
 * If reCAPTCHA Enabled verify answer, send it to google API
 *
 * @return boolean
 */
function sdm_sf_recaptcha_verify() {
    $main_advanced_opts = get_option( 'sdm_advanced_options' );
    $get_sf_opts = get_option('sdm_squeeze_form');
    $recaptcha_enable   = isset($get_sf_opts['enable_captcha']) && $get_sf_opts['enable_captcha'] === 'on';
    if ( $recaptcha_enable ) {
        if ( $_SERVER['REQUEST_METHOD'] == 'POST' && isset( $_POST['g-recaptcha-response'] ) ) {
            $recaptcha_secret_key = $main_advanced_opts['recaptcha_secret_key'];
            $recaptcha_response   = filter_input( INPUT_POST, 'g-recaptcha-response', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $response             = wp_remote_get( "https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret_key}&response={$recaptcha_response}" );
            $response             = json_decode( $response['body'], 1 );

            if ( $response['success'] ) {
                return true;
            } else {
                wp_die( '<p><strong>' . __( 'ERROR:', 'simple-download-monitor' ) . '</strong> ' . __( 'Google reCAPTCHA verification failed.', 'simple-download-monitor' ) . "</p>\n\n<p><a href=" . wp_get_referer() . '>&laquo; ' . __( 'Back', 'simple-download-monitor' ) . '</a>', '', 403 );
                return false;
            }
        } else {
            wp_die( '<p><strong>' . __( 'ERROR:', 'simple-download-monitor' ) . '</strong> ' . __( 'Google reCAPTCHA verification failed.', 'simple-download-monitor' ) . ' ' . __( 'Do you have JavaScript enabled?', 'simple-download-monitor' ) . "</p>\n\n<p><a href=" . wp_get_referer() . '>&laquo; ' . __( 'Back', 'simple-download-monitor' ) . '</a>', '', 403 );
            return false;
        }
    }
    return true;
}

function sdm_sf_handle_email_delivary_msg( $download_id, $template_no ) {
	$get_opts         = get_option( 'sdm_squeeze_form' );
	$email_delivery   = isset( $get_opts['email_download'] ) && $get_opts['email_download'] === 'on';
	$dl_message       = isset( $get_opts['sf_dl_message'] ) ? $get_opts['sf_dl_message'] : '';
	$has_redirect_url = isset( $get_opts['sf_redirect_url'] ) && $get_opts['sf_redirect_url'];

	$anchor = null;
	$action = null;
	if ( $email_delivery && ! $has_redirect_url ) {
		$anchor = 'sdm-sf-submitted-' . $template_no . $download_id;
		$action    = '#' . $anchor;
	}

	return array(
		'dl_message' => $dl_message,
		'action'     => $action,
		'anchor'     => $anchor,
	);
}