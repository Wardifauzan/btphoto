<?php

//Define AJAX URL
function myplugin_ajaxurl() {
  echo '<script type="text/javascript">
          var ajaxurl = "' . admin_url('admin-ajax.php') . '";
		  var siteurl = "'.get_site_url().'"
        </script>';
}
add_action('wp_head', 'myplugin_ajaxurl');

//Enqueue scripts and styles
function my_scripts() {
	wp_enqueue_style('bootstrap4', 'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css');
	wp_enqueue_style('booticon', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css');
	wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array( 'avada-stylesheet' ) );
	wp_enqueue_style('bt-style', get_stylesheet_directory_uri() . '/btstyle.css');
	wp_enqueue_script('boot1','https://code.jquery.com/jquery-3.6.1.min.js', array( 'jquery' ),'',true );
	wp_enqueue_script('boot2','https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js', array( 'jquery' ),'',true );
	wp_enqueue_script('boot3','https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js', array( 'jquery' ),'',true );
	wp_enqueue_script('bt-script', get_stylesheet_directory_uri() . '/script.js', array( 'jquery' ),'',true );
}
add_action( 'wp_enqueue_scripts', 'my_scripts' );

//Shortcode to output download button of an SDM [sdm_download]
function custom_download_output($output, $args){
	$download_id = $args['id'];
	$button_text = $args['button_text'];
	$homepage = get_bloginfo( 'url' );
	$download_url = $homepage . '/?smd_process_download=1&download_id=' . $download_id ;

	$output = '<a class="btn btn-secondary sdm-download-btn" target="_blank" href="'.$download_url.'" data-downloadid="'.$download_id.'" data-toggle="tooltip" data-placement="bottom" title="'.get_post_meta( $download_id, 'sdm_item_file_size', true ).'"><i class="bi bi-download"></i></a>';
	return $output;
	
}
add_filter('sdm_download_shortcode_output', 'custom_download_output', 10, 2);

//Shortcode to output count of an SDM [sdm_download_counter] 
function custom_count_output($output, $args){
    $id=$args['id'];
    $count = sdm_get_download_count_for_post( $id );
    $downloadstring = ( $count > '1' ) ? 'Downloads' : 'Download';
    return $count.' '.$downloadstring;
}
add_filter('sdm_download_count_output', 'custom_count_output', 10, 2);

//1- Shortcode to output list of SDMs by category [sdm_show_dl_from_category]
function custom_category_output($output, $args, $dl_posts, $total_posts) {
	// echo "<pre>";
	// print_r($dl_posts);
	// echo "</pre>";
	$output = custom_display_output($dl_posts);
	if ( isset( $args['pagination'] ) ) {
		$posts_per_page      = $args['pagination'];
		$count_sdm_posts     = $total_posts;
		$published_sdm_posts = $count_sdm_posts;
		$total_pages         = ceil( $published_sdm_posts / $posts_per_page );

		$big        = 999999999; // Need an unlikely integer
		$pagination = paginate_links(
			array(
				'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
				'format'    => '',
				'add_args'  => '',
				'current'   => max( 1, get_query_var( 'paged' ) ),
				'total'     => $total_pages,
				'prev_text' => 'Previous',
				'next_text' => 'Next',
			)
		);
		$output    .= '<div class="sdm_pagination">' . $pagination . '</div>';
	}
	return $output;
}
add_filter('sdm_category_download_items_shortcode_output', 'custom_category_output', 10, 4);

//2- Shortcode to output list of popular/most downloaded SDMs [sdm_popular_downloads]
function custom_popular_output($output, $args, $dl_posts) {
	$output = custom_display_output($dl_posts);
	return $output;
}
add_filter('sdm_popular_downloads_shortcode_output', 'custom_popular_output', 10, 3);

//3- Shortcode to output list of SDMs by latest posts [sdm_latest_downloads]
function custom_latest_output($output, $args, $dl_posts) {
	$output = custom_display_output($dl_posts);
	return $output;
}
add_filter('sdm_latest_downloads_shortcode_output', 'custom_latest_output', 10, 3);

//Custom template to output listing and search result
function custom_display_output($dl_posts) {

	/*$count = '';
	if(count($dl_posts) < 5) {
		$max_w = (count($dl_posts) > 2) ? 'max-width: 100%;' : 'max-width: 800px;';
		
		$count = 'column-count:'.count($dl_posts).'; '.$max_w;
	}*/

	$output = '<div class="card-columns dl-post mx-auto" style="">';
	foreach($dl_posts as $p) {
		$download_thumb_url = get_post_meta($p->ID, 'sdm_upload_thumbnail', true);
		$thumb_output = '<img src="' . $download_thumb_url . '"/>';

		$output .= '
		<div class="card dl-post__card">'
		.$thumb_output.
		'<div class="dl-post__detail-wrapper">
			<div class="card-body dl-post__detail">
			<div class="d-flex">
				<div class="card-title flex-fill">
					<h3>'.$p->post_title.'</h3>
					<p id="download-count-id-'.$p->ID.'">'.do_shortcode("[sdm_download_counter id=".$p->ID." ]").'</p>
				</div>
				<div>'
					.do_shortcode("[sdm_download id=".$p->ID." button_text='Download Now' ]").

				'</div>
			</div>
			</div>
		</div>
		</div>';
	}
	$output .= '</div>';
	return $output;
}

//Shortcode to output search form only [sdm_search_form]
function custom_search_form_output($output, $search, $args) {
	$output  = '';
	$output .= '<form id="sdm_search_form" class="form-inline fusion-form-form-wrapper w-100 ' . sanitize_html_class( $args['class'], '' ) . '" method="GET">';
	$output .='
	<div class="fusion-form-field fusion-form-text-field fusion-form-label-above w-auto flex-fill">
		<div class="fusion-form-input-with-icon">
			<i class="fa-search fas"></i>
			<input type="text" name="sdm_search_term" value="' . $search . '" class="fusion-form-input" placeholder="' . sdm_sanitize_text( $args['placeholder'] ) . '">
		</div>
	</div>
	';
	$output .= '<div style="padding: 0 10px;"><input type="submit" style="height: 50px; border-radius: 6px;" class="sdm_search_submit fusion-button button-flat button-large button-default button-1 fusion-button-default-span  form-form-submit button-default" name="sdm_search_submit" value="SEARCH"></div>';
	$output .= '</form>';
	return $output;
}
add_filter('sdm_search_form_shortcode_output', 'custom_search_form_output', 10, 3);

//4- Shortcode to output search result [sdm_search_result]
function custom_search_result_output($output, $results, $keywords_searched, $args) {
	$output = '';
	if(isset($results) && count($results) > 0) {
		$output .= '<div class="d-flex justify-content-center mb-4"><div class="sdm_search_result_number_of_items text-center flex-fill py-5 border bg-light">' . __( 'Number of items found: ', 'simple-download-monitor' ) .'<h3>'.count( $results ) . '</h3></div>';
		$output .= '<div class="sdm_search_result_keywords text-center flex-fill py-5 border bg-light">' . __( 'Keywords searched: ', 'simple-download-monitor' ) .'<h3>'. implode( ', ', $keywords_searched ) . '</h3></div></div>';
		$output .= custom_display_output($results);
	} else {
		$output .= '<div class="text-center pt-5">' . __( 'No result. Please search again.', 'simple-download-monitor' ) .'</div>';
	}

	return $output;
}
add_filter('sdm_search_result_shortcode_output', 'custom_search_result_output', 10, 5);

//from Ajax request: When the District category button is clicked
function district_category_request() {
	if (isset($_REQUEST)) { 
		$district = $_REQUEST['district'];
		if ($district != 'latest') {
			echo do_shortcode('[sdm_show_dl_from_category category_slug="'.$district.'" number="8"]');
		} else {
			echo do_shortcode('[sdm_latest_downloads number="8"]');
		}
	}
	die();
}
add_action('wp_ajax_district_category_ajax_request', 'district_category_request');
add_action('wp_ajax_nopriv_district_category_ajax_request', 'district_category_request');

//from Ajax request: when the Download button (.sdm-download-btn) is clicked
function count_download_request() {
	$download_id = $_REQUEST['download_id'];
	echo do_shortcode('[sdm_download_counter id="'.$download_id.'"]');
	die();
}
add_action('wp_ajax_count_download_ajax_request', 'count_download_request');
add_action('wp_ajax_nopriv_count_download_ajax_request', 'count_download_request');