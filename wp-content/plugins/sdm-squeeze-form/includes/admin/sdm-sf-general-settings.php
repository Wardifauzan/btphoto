<?php

function sdm_general_settings_page()
{
    if (isset($_POST['sdm_sf_settings_submit'])) {
        $post = $_POST;
        $get_opt = get_option('sdm_squeeze_form');

        // --- Email Settings ---

        // Save 'email_download' checkbox
        $get_opt['email_download'] = (isset($post['email_download']) && $post['email_download'] === 'on') ? 'on' : '';

        // Save 'email_from' text field
        $get_opt['email_from'] = (isset($post['email_from']) && $post['email_from'] != '') ? stripslashes($post['email_from']) : '';

        // Save 'email_subject' text field
        $get_opt['email_subject'] = (isset($post['email_subject']) && $post['email_subject'] != '') ? sanitize_text_field($post['email_subject']) : '';

        // Save 'Download message' text field
        $get_opt['sf_dl_message'] = (isset($post['sf_dl_message']) && $post['sf_dl_message'] != '') ? sanitize_text_field($post['sf_dl_message']) : '';

        // Save 'Redirection url' text field
        $get_opt['sf_redirect_url'] = (isset($post['sf_redirect_url']) && $post['sf_redirect_url'] != '') ? sanitize_url($post['sf_redirect_url']) : '';

        // Save 'email_body' text field
        $get_opt['email_body'] = (isset($post['email_body']) && $post['email_body'] != '') ? stripslashes((string)$post['email_body']) : '';

        //--- Misc Settings ---

        // Save 'hide_sf' checkbox
        $get_opt['hide_sf'] = (isset($post['hide_sf']) && $post['hide_sf'] === 'on') ? 'on' : '';

        // Save 'name_field_not_required' checkbox
        $get_opt['name_field_not_required'] = (isset($post['name_field_not_required']) && $post['name_field_not_required'] === 'on') ? 'on' : '';

        // Save 'hide_name_field' checkbox
        $get_opt['hide_name_field'] = (isset($post['hide_name_field']) && $post['hide_name_field'] === 'on') ? 'on' : '';

        // Save 'squeeze_form_in_single_page' checkbox
        $get_opt['squeeze_form_in_single_page'] = (isset($post['squeeze_form_in_single_page']) && $post['squeeze_form_in_single_page'] === 'on') ? 'on' : '';

        // Enable reCaptcha in the squeeze form.
        $get_opt['enable_captcha'] = (isset($post['enable_captcha']) && $post['enable_captcha'] === 'on') ? 'on' : '';

        // Update plugin options
        update_option('sdm_squeeze_form', $get_opt);

        echo '<div id="message" class="updated fade"><p>';
        _e('Settings successfully saved.', 'sdm_lang');
        echo '</p></div>';
    }

    //Retrieve the saved settings values
    $get_opts = get_option('sdm_squeeze_form');

    $email_download = isset($get_opts['email_download']) && $get_opts['email_download'] === 'on' ? 'checked="checked"' : '';
    $email_from = isset($get_opts['email_from']) ? $get_opts['email_from'] : get_option('blogname') . ' <' . get_option('admin_email') . '>';
    $email_subject = isset($get_opts['email_subject']) ? $get_opts['email_subject'] : 'Your Downloadable Item';
    $sf_dl_message = isset($get_opts['sf_dl_message']) ? $get_opts['sf_dl_message'] : 'The download has been sent to your email. Please check your inbox.';
    $email_body = isset($get_opts['email_body']) ? $get_opts['email_body'] : "Dear {first_name} {last_name}\r\n\r\nBelow is your download link:\r\n{product_link}\r\n\r\nThank You";
    $sf_redirect_url = isset($get_opts['sf_redirect_url']) ? $get_opts['sf_redirect_url'] : "";

    $hide_sf = isset($get_opts['hide_sf']) && $get_opts['hide_sf'] === 'on' ? 'checked="checked"' : '';
    $name_field_not_required = isset($get_opts['name_field_not_required']) && $get_opts['name_field_not_required'] === 'on' ? 'checked="checked"' : '';
    $hide_name_field = isset($get_opts['hide_name_field']) && $get_opts['hide_name_field'] === 'on' ? 'checked="checked"' : '';
    $squeeze_form_in_single_page = isset($get_opts['squeeze_form_in_single_page']) && $get_opts['squeeze_form_in_single_page'] === 'on' ? 'checked="checked"' : '';

    // if reCAPTCHA api key is provided is the main plugin
    $reCaptcha_key = (bool)(get_option('sdm_advanced_options')['recaptcha_secret_key'] && get_option('sdm_advanced_options')['recaptcha_site_key']);
    $reCaptcha = isset($get_opts['enable_captcha']) && $get_opts['enable_captcha'] === 'on' ? 'checked="checked"' : '';
    ?>
    <style>
        .wp-editor-tools:after {
            display: none !important;
        }
    </style>
    <div id="poststuff">
        <div id="post-body">

            <form method="post" action="">

                <div class="postbox">
                    <h3 class="hndle"><label for="title">Email Related Settings</label></h3>
                    <div class="inside">

                        <table cellspacing="20">
                            <tbody>
                            <tr valign="top">
                                <td width="25%" align="left">Deliver the Download via Email</td>
                                <td><input type="checkbox" name="email_download" <?php echo $email_download; ?> />
                                    <p class="description">Check this if you want an email with the download link to be
                                        sent after form submission (instead of starting the download right after).</p>
                                </td>
                            </tr>
                            <tr valign="top">
                                <td width="25%" align="left">From Email Address</td>
                                <td><input type="text" name="email_from" value="<?php echo $email_from; ?>" size="80"/>
                                    <p class="description">This is the email address that will be used to send the email
                                        to the buyer. This name and email address will appear in the from field of the
                                        email. You should use an email address that is on this domain.<br/>Example: Your
                                        Name &lt;support@your-domain.com&gt;</p></td>
                            </tr>
                            <tr valign="top">
                                <td width="25%" align="left">Squeeze Form Email Subject</td>
                                <td><input type="text" name="email_subject" value="<?php echo $email_subject; ?>"
                                           size="80"/>
                                    <p class="description">Specify the subject of the email that will be sent to the
                                        user when a squeeze form is submitted.</p></td>
                            </tr>
                            <tr valign="top">
                                <td width="25%" align="left">Squeeze Form Email Body</td>
                                <td>
                                    <?php
                                    $email_body_settings = array('textarea_name' => 'email_body');
                                    wp_editor($email_body, "email_body", $email_body_settings);
                                    ?>
                                    <p class="description">Specify the email body that will be sent to the user when a
                                        squeeze form is submitted. Do not change the text within the braces {}. You can
                                        use the following email merge tags in this email:
                                        <br/>{first_name} – First name of the user
                                        <br/>{last_name} – Last name of the user
                                        <br/>{email} – Email address of the user
                                        <br/>{product_link} – Download link of the product the squeeze form is giving.
                                        <br/>{download_id} – Download ID of the item.
                                        <br/>{download_title} – Download title of the item.
                                    </p></td>
                            </tr>
                            <tr valign="top">
                                <td width="25%" align="left">Successful Submission Message</td>
                                <td><input type="text" name="sf_dl_message" value="<?php echo $sf_dl_message; ?>"
                                           size="80"/>
                                    <p class="description">This message will be shown after the squeeze form submission
                                        is successful.</p></td>
                            </tr>
                            <tr valign="top">
                                <td width="25%" align="left">After Submission Redirection URL</td>
                                <td><input type="text" name="sf_redirect_url" value="<?php echo esc_url($sf_redirect_url); ?>"
                                           size="80"/>
                                    <p class="description">Optionally, you can redirect the users to a URL after the squeeze form is submitted. Enter a URL where the users will be redirected to after the email is sent.</p></td>
                            </tr>
                            </tbody>
                        </table>

                    </div>
                </div><!-- end of postbox and inside -->

                <div class="postbox">
                    <h3 class="hndle"><label for="title">Miscellaneous Settings</label></h3>
                    <div class="inside">

                        <table cellspacing="20">
                            <tbody>
                            <tr valign="top">
                                <td width="25%" align="left">Hide from Logged-in Users</td>
                                <td><input type="checkbox" name="hide_sf" <?php echo $hide_sf; ?> />
                                    <p class="description">Check this if you want to hide the squeeze form from
                                        logged-in users. So the squeeze form will only be shown to anonymous visitors of
                                        your site.</p>
                                </td>
                            </tr>

                            <tr valign="top">
                                <td width="25%" align="left">Make Name Field Not-Required</td>
                                <td><input type="checkbox"
                                           name="name_field_not_required" <?php echo $name_field_not_required; ?> />
                                    <p class="description">Check this if you do not want the "Name" field to be a
                                        required field (on the squeeze form).</p>
                                </td>
                            </tr>

                            <tr valign="top">
                                <td width="25%" align="left">Hide Name Field</td>
                                <td><input type="checkbox" name="hide_name_field" <?php echo $hide_name_field; ?> />
                                    <p class="description">Check this to remove the "Name" field from the squeeze
                                        form.</p>
                                </td>
                            </tr>

                            <tr valign="top">
                                <td width="25%" align="left">Show Squeeze Form on Single Download Page</td>
                                <td><input type="checkbox"
                                           name="squeeze_form_in_single_page" <?php echo $squeeze_form_in_single_page; ?> />
                                    <p class="description">Enable this if you want to show the squeeze form instead of
                                        the standard download now button on the single download page.</p>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                    </div>
                </div><!-- end of postbox and inside -->

                <div class="postbox">
                    <h3 class="hndle"><label for="title">Captcha Related Settings</label></h3>
                    <div class="inside">
                        <table cellspacing="20">
                            <tbody>
                            <tr valign="top">
                                <td width="25%" align="left">Enable Captcha on the Squeeze Forms</td>
                                <td><input type="checkbox" <?php echo !$reCaptcha_key ? 'disabled' : ''; ?>
                                           name="enable_captcha" <?php echo $reCaptcha; ?> />
                                    <p class="description">
                                        When it is enabled, the squeeze form will show the Google captcha. Enter the
                                        Google Captcha API keys in the core plugin's <a
                                                href="edit.php?post_type=sdm_downloads&page=sdm-settings&action=advanced-settings"
                                                target="_blank">advanced settings</a> menu.
                                    </p>
                                    <?php if (!$reCaptcha_key) { ?>
                                        <?php if ($reCaptcha) { ?>
                                            <p style="color: red;">
                                                Google Captcha API key is missing from the settings. Please configure
                                                the Captcha API keys in the settings menu of the core plugin.
                                            </p>
                                        <?php } else { ?>
                                            <p style="color: red">
                                                You need to enter the captcha API details in the core plugin's settings
                                                before you can use this option.
                                            </p>
                                        <?php } ?>
                                    <?php } ?>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div><!-- end of captcha settings -->

                <input id="sdm_sf_settings_submit" name="sdm_sf_settings_submit" type="submit" class="button-primary"
                       value="Save Settings"/>
            </form>

        </div><!-- end of poststuff and post-body -->
    </div><!-- end of poststuff and post-body -->
    <?php
}
