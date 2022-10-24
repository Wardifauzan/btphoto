<?php

function sdm_sf_show_squeeze_form_submission_details() {
    echo '<div id="poststuff"><div id="post-body">';//This need to be AFTER the nav menu.

    if (isset($_REQUEST['sdmsf_export_submission_data_to_csv'])) {
        $file_url = sdmsf_process_export_submission_data_to_csv();
        $export_message = 'Data exported to <a href="' . $file_url . '" target="_blank">Submissions CSV File (Right click on this link and choose "Save As" to save the file to your computer)</a>';
        echo '<div id="message" class="updated fade">';
        echo '<p>' . $export_message . '</p>';
        echo '<p>Note: You should use the reset export file option from this page to reset the CSV file after you download it.</p>';
        echo '</div>';
    }

    if (isset($_REQUEST['sdmsf_reset_export_files'])) {
	sdmsf_reset_export_files();
        echo '<div id="message" class="updated fade"><p>';
        echo 'The CSV Export file has been reset successfully.';
        echo '</p></div>';
    }

    ?>

    <div class="sdm_blue_box">
        <p>The table below lists the names and email addresses of all the squeeze form submissions.</p>
        <p>Items may be deleted, or exported to a .csv file, via the "Bulk Actions". You can also export all the submissions to a CSV file from the export option at the bottom of this page.</p>
        <p>Set additional options using the "Screen Options" tab on the upper-right of the page.</p>
    </div>

    <form id="sdm_download_log_form" method="post">
        <?php $sdm_list_table = new sdm_squeeze_list_table(); ?>
    </form>

    <br /><br />
    <div class="postbox">
        <h3 class="hndle"><label for="title">Export Data to CSV File</label></h3>
        <div class="inside">

            <br />
            <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
                <input type="submit" class="button" name="sdmsf_export_submission_data_to_csv" value="<?php _e('Export All Submissions Data to a CSV File', 'simple-download-monitor'); ?>" />
                <p class="description">You can use this to export all the squeeze form submissions data to a CSV file (comma separated). When you use this option, it will create a CSV file with the data and show a download link at the top of this page.</p>
            </form>

            <br />
            <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
                <input type="submit" class="button" name="sdmsf_reset_export_files" value="<?php _e('Reset the Export File'); ?>" />
                <p class="description">Use this to reset the export file. It is a good idea to reset the exported CSV files after you export data and download the file.</p>
            </form>

        </div>
    </div>

    <?php
    echo '</div></div>';//End post-stuff and postbody
}