<?php

// Add submenu page
function sdm_sf_admin_menu() {

    $sdm_sf_downloads = add_submenu_page('edit.php?post_type=sdm_downloads', 'Squeeze Form', 'Squeeze Form', 'manage_options', 'sdm_downloads_squeeze', 'sdm_squeeze_submenu_page');
    if (isset($sdm_sf_downloads)) {
        add_action('load-' . $sdm_sf_downloads, 'load_sdm_squeeze_submenu_page');  // Load downloads page hook
    }
}

function sdm_squeeze_submenu_page() {
    ?>
    <div class="wrap">
        <h2>Squeeze Form Settings</h2>
        <?php
        $menu_tab = isset($_GET['tab']) ? $_GET['tab'] : '';
        ?>
        <h2 class="nav-tab-wrapper">
            <a class="nav-tab <?php echo ($menu_tab == '') ? 'nav-tab-active' : ''; ?>" href="edit.php?post_type=sdm_downloads&page=sdm_downloads_squeeze">Squeeze Form Submissions</a>
            <a class="nav-tab <?php echo ($menu_tab == 'autoresponder') ? 'nav-tab-active' : ''; ?>" href="edit.php?post_type=sdm_downloads&page=sdm_downloads_squeeze&tab=autoresponder">Autoresponder Settings</a>
            <a class="nav-tab <?php echo ($menu_tab == 'general') ? 'nav-tab-active' : ''; ?>" href="edit.php?post_type=sdm_downloads&page=sdm_downloads_squeeze&tab=general">Settings</a>
        </h2>
        <?php
        switch ($menu_tab) {
            case 'main':
                include_once(SDM_SF_PATH . 'includes/admin/sdm-sf-squeeze-form-details.php');
                sdm_sf_show_squeeze_form_submission_details();
                break;
            case 'autoresponder':
                include_once(SDM_SF_PATH . 'includes/admin/sdm-sf-autoresponder-settings.php');
                sdm_squeeze_settings_page();
                break;
            case 'general':
                include_once(SDM_SF_PATH . 'includes/admin/sdm-sf-general-settings.php');
                sdm_general_settings_page();
                break;
            default:
                include_once(SDM_SF_PATH . 'includes/admin/sdm-sf-squeeze-form-details.php');
                sdm_sf_show_squeeze_form_submission_details();
                break;
        }
        ?>

    </div><!-- end of .wrap -->
    <?php
}

function load_sdm_squeeze_submenu_page() {

    // Set screen options tab
    $option = 'per_page';
    $args = array(
        'label' => 'Items',
        'default' => 20,
        'option' => 'items_per_page'
    );
    add_screen_option($option, $args);

    // Process $_GET variable (used for individual 'delete' links)
    if (isset($_GET)) {

        $get = $_GET;
        if (isset($get['action']) && isset($get['id']) && isset($get['email']) && isset($get['date'])) {

            global $wpdb;
            $query = $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'sdm_squeeze_form WHERE post_id=%d AND email=%s AND date=%s', $get['id'], $get['email'], $get['date']));
        }
    }

    // Process $_POST variable (used for prompting export download) (used for bulk deleting items)
    if (isset($_POST)) {
        $post = $_POST;

        //Check to make sure some items have been selected (before trying to process the bulk action).
        if (isset($post['action'])) {
            if (!isset($post['squeeze_form_items'])){
                //Error condition - one or more entries need to be selected for the bulk action to be processed.
                echo '<div id="message" class="error fade">';
                echo '<p>' . __('Error: No entries selected for bulk action!', 'simple-download-monitor') . '</p>';
                echo '<p>' . __('Select one or more entries then use the bulk action operation.', 'simple-download-monitor') . '</p>';
                echo '</div>';
            } else {
                //It is good to go ahead and process
            }
        }

        // Bulk action export csv
        if (isset($post['action']) && ($post['action'] === 'export') && isset($post['squeeze_form_items'])) {

            if (is_array($post['squeeze_form_items'])) {

                $date = date("Y/m/d");

                header('Content-Type: text/csv');
                header('Content-Disposition: attachment;filename="sdm_squeeze_form-' . $date . '.csv"');
                header('Cache-Control: max-age=0');

                $out = fopen('php://output', 'w');

                fputcsv($out, array('Download Title', 'Download ID', 'First Name', 'Last Name', 'Email', 'Date'), ',');

                $output = array();
                foreach ($post['squeeze_form_items'] as $k => $v) {

                    // If we have an array of items to export
                    $parts = explode('`', $v);
                    fputcsv($out, $parts, ',');
                }

                fclose($out);
                exit();
            }
        }

        // Bulk action delete items
        if (isset($post['action']) && ($post['action'] === 'delete') && isset($post['squeeze_form_items'])) {

            if (is_array($post['squeeze_form_items'])) {

                global $wpdb;
                foreach ($post['squeeze_form_items'] as $k => $v) {

                    // If we have an array of items to delete
                    list($title, $id, $fname, $lname, $email, $date) = explode('`', $v);

                    // Delete row from database
                    $query = $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'sdm_squeeze_form WHERE post_id=%d AND email=%s AND date=%s', $id, $email, $date));
                }
            }
        }
    }
}

// Set screen options values for downloads page
function sdm_squeeze_submenu_page_screen_options($status, $option, $value) {

    return $value;
}

add_filter('set-screen-option', 'sdm_squeeze_submenu_page_screen_options', 10, 3);

// WP List Table class extension
if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class sdm_squeeze_list_table extends WP_List_Table {

    public function __construct() {

        parent::__construct(array(
            'ajax' => false
        ));
        $this->prepare_items();  // Prepare items
        $this->search_box('search', 'sdm_search');  // Display search box
        $this->display();  // Display table
    }

    function column_default($item, $column_name) {

        switch ($column_name) {
            case 'title':
            case 'post_id':
            case 'fname':
            case 'lname':
            case 'email':
                return $item[$column_name];
            case 'date':
                return $item[$column_name];
            default:
                return print_r($item, true); // Show the whole array for troubleshooting purposes
        }
    }

    function get_columns() {

        $columns = array(
            'cb' => '<input type="checkbox" />',
            'title' => 'Download Title',
            'post_id' => 'Download ID',
            'fname' => 'First Name',
            'lname' => 'Last Name',
            'email' => 'Email',
            'date' => 'Date'
        );
        return $columns;
    }

    function get_sortable_columns() {

        $sortable_columns = array(
            'title' => array('title', false),
            'post_id' => array('post_id', false),
            'fname' => array('fname', false),
            'lname' => array('lname', false),
            'email' => array('email', false),
            'date' => array('date', false)
        );
        return $sortable_columns;
    }

    function prepare_items() {

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        // Build array of database items
        global $wpdb;
        $data = array();

        $query = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "sdm_squeeze_form");

        if ($query) {
            foreach ($query as $object) {

                $item_title = get_the_title($object->post_id);
                $data[] = array('title' => $item_title, 'post_id' => $object->post_id, 'fname' => $object->fname, 'lname' => $object->lname, 'email' => $object->email, 'date' => $object->date);
            }
        }

        // Check search query
        if (isset($_POST['s'])) {

            $search = trim($_POST['s']);
            $data = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "sdm_squeeze_form WHERE `post_id` LIKE '%%%s%%' OR `fname` LIKE '%%%s%%' OR `lname` LIKE '%%%s%%' OR `email` LIKE '%%%s%%' OR `date` LIKE '%%%s%%'", $search, $search, $search, $search, $search), ARRAY_A);

            // Iterate the results array and replace the download item 'post_id' with the download item 'title'
            if ($data) {
                foreach ($data as $index => $array) {

                    $title = get_the_title($array['post_id']);
                    $data[$index]['title'] = $title;  // Add 'title' array key
                    unset($data[$index]['id']);  // Remove 'id' array key
                }
            }
        }

        // Sortable
        if ($data)
            usort($data, array($this, 'usort_reorder'));

        // Pagination
        $per_page = $this->get_items_per_page('items_per_page', 20);
        $current_page = $this->get_pagenum();
        $total_items = count($data);

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page
        ));

        if ($data)
            $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    function usort_reorder($a, $b) {

        // If no sort, default to date
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'date';
        // If no order, default to desc (so latest entries are at the top)
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'desc';
        // Determine sort order
        $result = strcmp($a[$orderby], $b[$orderby]);
        // Send final sort direction to usort
        return ($order === 'asc') ? $result : -$result;
    }

    function column_title($item) {

        // Add 'delete' option for each row
        $actions = array(
            'delete' => sprintf('<a href="?post_type=sdm_downloads&page=sdm_downloads_squeeze&action=%s&id=%s&email=%s&date=%s">Delete</a>', 'delete', $item['post_id'], $item['email'], $item['date'])
        );

        return sprintf('%1$s %2$s', $item['title'], $this->row_actions($actions));
    }

    function get_bulk_actions() {

        // Bulk actions items
        $actions = array(
            'delete' => 'Delete',
            'export' => 'Export to CSV'
        );
        return $actions;
    }

    function column_cb($item) {

        // Add checkbox column
        return sprintf('<input type="checkbox" name="squeeze_form_items[]" value="%s`%s`%s`%s`%s`%s" />', $item['title'], $item['post_id'], $item['fname'], $item['lname'], $item['email'], $item['date']);
    }

    function search_box($text, $input_id) {
        ?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
            <input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
            <?php submit_button($text, 'button', false, false, array('id' => 'search-submit')); ?>
        </p>
        <?php
    }

}
