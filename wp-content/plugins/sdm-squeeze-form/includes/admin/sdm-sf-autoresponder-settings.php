<?php

function sdm_squeeze_settings_page() {
    if (isset($_POST['sdm_sf_settings_submit'])) {

        $post = $_POST;
        $get_opt = get_option('sdm_squeeze_form');

        // Save 'enable_mailchimp' checkbox
        $get_opt['enable_mailchimp'] = (isset($post['enable_mailchimp']) && $post['enable_mailchimp'] === 'on') ? 'on' : '';

        // Save 'mailchimp_list_name' text field
        $get_opt['mailchimp_list_name'] = (isset($post['mailchimp_list_name']) && $post['mailchimp_list_name'] != '') ? sanitize_text_field($post['mailchimp_list_name']) : '';

        // Save 'mailchimp_api_key' text field
        $get_opt['mailchimp_api_key'] = (isset($post['mailchimp_api_key']) && $post['mailchimp_api_key'] != '') ? sanitize_text_field($post['mailchimp_api_key']) : '';

        // Update plugin options
        update_option('sdm_squeeze_form', $get_opt);

        echo '<div id="message" class="updated fade"><p>';
        _e('Settings successfully saved.', 'sdm_lang');
        echo '</p></div>';
    }

    $get_opts = get_option('sdm_squeeze_form');
    $enable_mailchimp = isset($get_opts['enable_mailchimp']) && $get_opts['enable_mailchimp'] === 'on' ? 'checked="checked"' : '';
    $mailchimp_list_name = isset($get_opts['mailchimp_list_name']) ? $get_opts['mailchimp_list_name'] : '';
    $mailchimp_api_key = isset($get_opts['mailchimp_api_key']) ? $get_opts['mailchimp_api_key'] : '';
    ?>
    <div class="wrap">
        <div id="poststuff">
            <div id="post-body">

                <form method="post" action="">

                    <div class="postbox">
                        <h3 class="hndle"><label for="title">MailChimp Integration</label></h3>
                        <div class="inside"> 

                            <table cellspacing="20">
                                <tbody>
                                    <tr>
                                        <td width="25%" align="left">Enable MailChimp Integration</td>
                                        <td><input type="checkbox" name="enable_mailchimp" <?php echo $enable_mailchimp; ?> />
                                            <p class="description">Check this if you want to signup your customers to your MailChimp list.</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="25%" align="left">MailChimp List Name</td>
                                        <td><input type="text" name="mailchimp_list_name" size="50" value="<?php echo $mailchimp_list_name; ?>" />
                                            <p class="description">The name of the MailChimp list where the customers will be signed up to when using the global signup option (example: Customer List).</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="25%" align="left">MailChimp API Key</td>
                                        <td><input type="text" name="mailchimp_api_key" size="50" value="<?php echo $mailchimp_api_key; ?>" />
                                            <p class="description">The API Key of your MailChimp account (can be found under the "Account" tab). By default the API Key is not active so make sure you activate it in your Mailchimp account.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                        </div></div><!-- end of postbox and inside -->

                    <input id="sdm_sf_settings_submit" name="sdm_sf_settings_submit" type="submit" class="button-primary" value="Save Settings" />
                </form>

            </div><!-- end of poststuff and post-body -->
        </div><!-- end of poststuff and post-body -->
    </div><!-- end of wrap -->
    <?php
}
