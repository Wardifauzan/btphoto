<?php
/**
 * Plugin Name: Simple Download Monitor Squeeze Form
 * Plugin URI: http://simple-download-monitor.com/
 * Description: Use squeeze forms and capture emails for Simple Download Monitor items.
 * Version: 2.0.6
 * Author: Tips and Tricks HQ, Alexander
 * Author URI: http://simple-download-monitor.com/
 * License: GPL2
 */

//slug - sdmsf

if ( ! defined( 'ABSPATH' ) ) {
    exit; //Exit if accessed directly
}

define( 'SDM_SF_VERSION', "2.0.6" );
define( 'SDM_SF_URL', plugins_url( '', __FILE__ ) );
define( 'SDM_SF_PATH', plugin_dir_path( __FILE__ ) );
define( 'SDM_SF_FOLDER', dirname( plugin_basename( __FILE__ ) ) );
define( 'SDM_SF_SITE_HOME_URL', home_url() );

// Register plugin (create db table)
register_activation_hook( __FILE__, array( 'SDM_Squeeze_Forms', 'sdm_squeeze_form_db_activation' ) );

// Begin the magic!
class SDM_Squeeze_Forms {

    // DB version
    static $db_version = '1.2';

    // Initialize plugin
    static function init() {

	//Include files
	self::includes();

	// Register and enqueue the general scripts and styles
	add_action( 'init', array( __CLASS__, 'sdm_register_script' ) );

	add_action( 'admin_init', array( __CLASS__, 'sdm_admin_init' ) );

	// Plugins loaded tasks
	add_action( 'plugins_loaded', array( __CLASS__, 'sdm_squeeze_form_plugins_loaded' ) );

	// Load tinymce plugin
	add_action( 'init', array( __CLASS__, 'sdm_register_tinymce' ) );

	// Add submenu page for this addon
	add_action( 'admin_menu', 'sdm_sf_admin_menu', 999 );

	// Add plugin settings link
	add_filter( 'plugin_action_links', array( __CLASS__, 'sdm_action_links' ), 10, 2 );

    // replaces single page download button with squeeze form
        if (isset(get_option('sdm_squeeze_form')['squeeze_form_in_single_page']) && get_option('sdm_squeeze_form')['squeeze_form_in_single_page'] === 'on'){
            add_filter('sdm_single_page_dl_link',array( __CLASS__, 'render_squeeze_form_in_single_page' ), 10 , 2);
        }
    }

    static function sdm_admin_init() {
	foreach ( array( 'post.php', 'post-new.php' ) as $hook ) {
	    add_action( "admin_head-$hook", array( __CLASS__, 'sdm_localize_tinymce' ) );
	}
    }

    static function includes() {
	//Common files
        include_once('includes/sdm_squeeze_utility_functions.php');

	//Admin side only files
	if ( is_admin() ) {
	    include_once('sdm-sf-admin-menu.php');
	} else {
	    //Front-end only files
	    include_once('sdm-sf-shortcodes.php');
	}
    }

    // Activation hook
    static function sdm_squeeze_form_db_activation() {

	// Create DB Table
	global $wpdb;
	$table_name = $wpdb->prefix . 'sdm_squeeze_form';

	$sql = 'CREATE TABLE ' . $table_name . ' (
				  id mediumint(9) NOT NULL AUTO_INCREMENT,
				  post_id mediumint(9) NOT NULL,
				  fname mediumtext NOT NULL,
				  lname mediumtext NOT NULL,
				  email mediumtext NOT NULL,
				  date datetime NOT NULL,
				  UNIQUE KEY id (id)
			);';

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	// Update option
	$get_opt		 = get_option( 'sdm_squeeze_form' );
	$get_opt[ 'db_version' ] = self::$db_version;
	update_option( 'sdm_squeeze_form', $get_opt );
    }

    // Register and localize script
    static function sdm_register_script() {
        //Read the settings menu related to the name field being required or not
        $get_opts = get_option('sdm_squeeze_form');
        //Note that it is a little tricky since it is reading the "not required" value then putting it in the "required" variable.
        $name_field_equired = isset($get_opts['name_field_not_required']) && $get_opts['name_field_not_required'] == 'on' ? 'no' : 'yes';

	// Register script only on pages where shortcode is used
	wp_enqueue_script( 'sdm_squeeze_form_js', SDM_SF_URL . '/js/sdm_sf_global_script.js', array(), SDM_SF_VERSION );
	wp_localize_script( 'sdm_squeeze_form_js', 'sdm_js_object', array(
	    'ajax_url'	 => admin_url( 'admin-ajax.php' ),
	    'sdm_sf_version' => SDM_SF_VERSION,
            'sdm_sf_name_required' => $name_field_equired,
	) );

        // if reCAPTCHA enabled.
        $get_sf_opts = get_option('sdm_squeeze_form');
        $main_advanced_opts = get_option( 'sdm_advanced_options' );
        $recaptcha_enable = isset($get_sf_opts['enable_captcha']) && $get_sf_opts['enable_captcha'] === 'on';
        if ( $recaptcha_enable ) {
            wp_register_script( 'sdm-recaptcha-scripts-js', WP_SIMPLE_DL_MONITOR_URL . '/js/sdm_g_recaptcha.js', array(), true );
            wp_localize_script( 'sdm-recaptcha-scripts-js', 'sdm_recaptcha_opt', array( 'site_key' => $main_advanced_opts['recaptcha_site_key'] ) );
            wp_register_script( 'sdm-recaptcha-scripts-lib', '//www.google.com/recaptcha/api.js?hl=' . get_locale() . '&onload=sdm_reCaptcha&render=explicit', array(), false );
            wp_enqueue_script( 'sdm-recaptcha-scripts-js' );
            wp_enqueue_script( 'sdm-recaptcha-scripts-lib' );
        }

    }

    // Plugins loaded tasks
    static function sdm_squeeze_form_plugins_loaded() {

	// Handle db upgrade
	self::sdm_squeeze_form_db_update_check();
    }

    // Plugin update check
    static function sdm_squeeze_form_db_update_check() {

	//Check if database needs to be upgraded
	if ( is_admin() ) {

	    // Get this db version
	    $this_db_version = self::$db_version;

	    // Get user db version
	    $get_opt	 = get_option( 'sdm_squeeze_form' );
	    $user_db_version = $get_opt[ 'db_version' ];

	    if ( $this_db_version != $user_db_version ) {

		// Update db table
		self::sdm_squeeze_form_db_activation();
	    }
	}
    }

    // Load tinymce plugin
    static function sdm_register_tinymce() {

	add_filter( 'mce_buttons', 'sdm_register_mce_button' );
	add_filter( 'mce_external_plugins', 'sdm_registeer_mce_plugin' );

	function sdm_register_mce_button( $buttons ) {

	    array_push( $buttons, 'sdmSqueezeMCE' );
	    return $buttons;
	}

	function sdm_registeer_mce_plugin( $plugins ) {

	    $plugins[ 'sdmSqueezeMCE' ] = plugins_url( '/js/sdm_tinymce_editor_plugin.js', __FILE__ );
	    return $plugins;
	}

    }

    // Load tinymce script
    static function sdm_localize_tinymce() {

	// Get all sdm download item ids
	$query_args	 = array(
	    'post_type'	 => 'sdm_downloads',
	    'posts_per_page' => -1
	);
	$query		 = new WP_Query( $query_args );

	$sdm_items = '[{text: "Select...", value: ""},';
	foreach ( $query->posts as $wp_post ) {

	    //$sdm_items[$wp_post->ID] = $wp_post->post_title;
	    $sdm_items .= '{text: "' . $wp_post->ID . ' - ' . $wp_post->post_title . '", value: "' . $wp_post->ID . '"},';
	}
	$sdm_items	 = rtrim( $sdm_items, ',' );
	$sdm_items	 .= ']';

	// Pass download items to javascript for tinymce selection
	?>
	<script type="text/javascript">
	    var sdm_squeeze_item_ids = <?php echo $sdm_items; ?>;
	</script>
	<?php
    }

    /* Added the settings menu link in the plugins listing page */

    static function sdm_action_links( $links, $file ) {
	if ( $file == plugin_basename( __FILE__ ) ) {
	    $settings_link = '<a href="edit.php?post_type=sdm_downloads&page=sdm_downloads_squeeze" title="Squeeze Form Settings Page">' . __( "Settings", 'simple-download-monitor' ) . '</a>';
	    array_unshift( $links, $settings_link );
	}
	return $links;
    }

    static function render_squeeze_form_in_single_page($content, $args)
    {
        include_once ('templates/sdm-sf-template-single.php');
        return sdm_sf_render_fancy_single_form($args);
    }
}

SDM_Squeeze_Forms::init();
