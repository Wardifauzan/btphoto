<?php

function theme_enqueue_styles() {
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'avada-stylesheet' ) );
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );

function my_custom_upload_mime_types( $mimes ) {
 
    // Add new allowed MIME types here.
    $mimes['ttf'] = 'font/ttf';
 
    // Return the array back to the function with our added MIME type.
    return $mimes;
}
add_filter( 'upload_mimes', 'my_custom_upload_mime_types' );

function my_correct_filetypes( $data, $file, $filename, $mimes, $real_mime ) {

    if ( ! empty( $data['ext'] ) && ! empty( $data['type'] ) ) {
      return $data;
    }

    $wp_file_type = wp_check_filetype( $filename, $mimes );

  	// Check for the file type you want to enable, e.g. 'svg'.
    if ( 'ttf' === $wp_file_type['ext'] ) {
      $data['ext']  = 'ttf';
      $data['type'] = 'font/ttf';
    }

    return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'my_correct_filetypes', 10, 5 );

add_action( 'wp_enqueue_scripts', 'enqueue_load_fa' );
function enqueue_load_fa() { 
    wp_enqueue_style( 'load-fa', 'https://use.fontawesome.com/releases/v5.0.13/css/all.css' );
}

function my_scripts() {
    wp_enqueue_style('bootstrap4', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css');
    wp_enqueue_script( 'boot1','https://code.jquery.com/jquery-3.3.1.slim.min.js', array( 'jquery' ),'',true );
    wp_enqueue_script( 'boot2','https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js', array( 'jquery' ),'',true );
    wp_enqueue_script( 'boot3','https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js', array( 'jquery' ),'',true );
}
add_action( 'wp_enqueue_scripts', 'my_scripts' );

add_filter('sdm_download_shortcode_output', 'custom_download_output', 10, 2);
function custom_download_output($output, $args){
    $download_id = $args['id'];
    $button_text = $args['button_text'];
    $homepage = get_bloginfo( 'url' );
    $download_url = $homepage . '/?smd_process_download=1&download_id=' . $download_id ;

    //Just as a test lets show the download URL of the item.
    $output = '<a class="btn btn-primary" target="_blank" href="'.$download_url.'">'.$button_text.' ('.get_post_meta( $download_id, 'sdm_item_file_size', true ).')</a>';
    return $output;
}

add_filter('sdm_download_count_output', 'custom_count_output', 10, 2);
function custom_count_output($output, $args)
{
    $id=$args['id'];
    $count = sdm_get_download_count_for_post( $id );
    $downloadstring = ( $count == '1' ) ? 'Download' : 'Downloads';
    return '<i class="fas fa-download"></i> '.$count.' '.$downloadstring;
}



add_filter('sdm_category_download_items_shortcode_output', 'custom_category_output', 10, 3);
function custom_category_output($output, $args, $dl_posts) {
    // echo "<pre>";
    // print_r($dl_posts);
    // echo "</pre>";

    $output = '<div class="row">';
    foreach($dl_posts as $p) {
        $output .= '<div class="col-md-4">';
        $download_thumb_url = get_post_meta($p->ID, 'sdm_upload_thumbnail', true);
        $thumb_output = '<img src="' . $download_thumb_url . '"/>';

        $output .= '
        <div class="card">'
        .$thumb_output.
        '<div class="card-body">
            <div class="d-flex justify-content-between">
                <h5 class="card-title">'.$p->post_title.'</h5>'
                .do_shortcode("[sdm_download id=".$p->ID." button_text='Download Now' ]").'
            </div>'
            .do_shortcode("[sdm_download_counter id=".$p->ID." ]").
        '</div></div>';

        $output .= '</div>';
    }
    $output .='</div>';
    return $output;
}