<?php
/*
Plugin Name: Zoho ZeptoMail
Version: 3.2.7
Plugin URI: https://zeptomail.zoho.com/
Author: Zoho Mail
Author URI: https://www.zoho.com/zeptomail/
Description: Configure your Zoho ZeptoMail account to send email from your WordPress site.
Text Domain: ZeptoMail
Domain Path: /languages
 */
  /*
    Copyright (c) 2015, ZOHO CORPORATION
    All rights reserved.

    Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

    1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.

    2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

    THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/
    define('TRANSMAIL_PLUGIN_VERSION', '3.2.0');

    function transmail_plugin_update_check() {
        if ( ( ! get_option( 'transmail_plugin_version' ) || get_option( 'transmail_plugin_version' ) < '3.2.0' ) ) {
            ztm_plugin_migration_logic();
            ztm_plugin_activate();
            update_option('transmail_plugin_version', TRANSMAIL_PLUGIN_VERSION);
        }
    }
    add_action('plugins_loaded', 'transmail_plugin_update_check');

    require_once plugin_dir_path( __FILE__ ) . 'includes/class-transmail-helper.php';

    function ztm_zmplugin_script() {
        wp_enqueue_style( 'zm_zohoTransMail_style', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', false, '1.0.0' );
        wp_enqueue_script('jquery');
        wp_enqueue_script('my-plugin-script', plugin_dir_url(__FILE__) . 'index.js', array('jquery'), '1.0', true);
        wp_localize_script('my-plugin-script', 'myAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'), 
            'nonce' => wp_create_nonce('transmail_failed_email_nonce') 
        ));
        wp_localize_script('my-plugin-script', 'transmailPluginData', array(
            'plugin_url' => plugins_url('', __FILE__),
        ));
    }

    add_action( 'admin_enqueue_scripts', 'ztm_zmplugin_script');

    function zohoTransMail_deactivate() {
    //--------------Clear the credentials once deactivated-------------------	
    global $wpdb;
        delete_option('transmail_max_log_limit');
        delete_option('transmail_additional_mail_agents');
        delete_option('transmail_test_mail_case');
        delete_option('transmail_connection_status');
        delete_option('transmail_content_type');
        delete_option('transmail_domain_name');
        delete_option('transmail_mail_agents_count');

        $table_name = $wpdb->prefix . 'transmail_failed_emails';
        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
        
    }

    register_deactivation_hook( __FILE__, 'zohoTransMail_deactivate');

    

    function transmail_integ_settings() {
     add_menu_page ( 
        'Welcome to ZeptoMail by Zoho Mail',
        'Zoho ZeptoMail',
        'manage_options',
        'transmail-settings',
        'transmail_settings_callback' ,
        'dashicons-email'
    );
     add_submenu_page ( 
        'transmail-settings',
        'ZeptoMail by Zoho Mail',
        'Configure Account',
        'manage_options',
        'transmail-settings',
        'transmail_settings_callback'
    );
     add_submenu_page (
        'transmail-settings', 
        'Send Mail - ZeptoMail by Zoho Mail', 
        'Send test email', 
        'manage_options', 
        'transmail-send-mail',
        'transmail_send_mail_callback'
    );
    add_submenu_page (
        'transmail-settings', 
        'Send Mail - ZeptoMail by Zoho Mail', 
        'Failed logs', 
        'manage_options', 
        'transmail-failed-logs',
        'transmail_faild_mail_callback'
    );
 }

 function ztm_plugin_activate() {
    try {
        global $wpdb;

        $table_name = $wpdb->prefix . 'transmail_failed_emails';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            email_address VARCHAR(255) NOT NULL,
            email_subject VARCHAR(255) NOT NULL,
            email_body LONGTEXT NOT NULL,
            headers TEXT NOT NULL,
            failed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            retry_count INT(11) NOT NULL DEFAULT 0,
            error_code VARCHAR(255) DEFAULT NULL,
            error_description VARCHAR(255) DEFAULT NULL,
            attachment_files TEXT DEFAULT NULL,
            attachments TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
    catch(Exception $e) {
        error_log("error occured while activate plugin");
    }
}
register_activation_hook(__FILE__, 'ztm_plugin_activate');

 
function ztm_plugin_migration_logic() {
    $old_from_name = get_option('transmail_from_name');
    $old_from_email_id = get_option('transmail_from_email_id');
    $old_send_mail_token = base64_decode(get_option('transmail_send_mail_token'));

    if ($old_from_name || $old_from_email_id || $old_send_mail_token) {
        $mail_agents = array(
            $old_from_email_id => array(
                array(
                    "fromName" => $old_from_name,
                    "Token" => $old_send_mail_token,
                    "isDefault" => true
                )
            )
        );
        
        $json_string = json_encode($mail_agents);
        update_option('transmail_additional_mail_agents', base64_encode($json_string), false);

        delete_option('transmail_from_name');
        delete_option('transmail_from_email_id');
        delete_option('transmail_send_mail_token');
    }
}

function transmail_admin_notice__success() {
     
    if (! get_option( 'zmail_plugin_installed' )  ) {
  ?>
  
  <div class="notice notice-info is-dismissible" style="display: flex; align-items: center;">
  <img src="<?php echo esc_url(plugins_url('assets/images/zeptomail.svg',__FILE__)) ?>" title="Zoho" alt="Zoho" width="140" style="margin-right: 10px;">
  
  <p style="margin: 0;">
    <?php _e('No more worrying about failed emails. Our latest update allows you to easily retry failed deliveries. Check it out in our plugin settings!', 'my-plugin-textdomain'); ?>
  </p>
</div>
  <?php
   update_option( 'zmail_plugin_installed', true );
  }
  
  }
  add_action( 'admin_notices', 'transmail_admin_notice__success' );

function transmail_faild_mail_callback() {
    $json_string = get_option('transmail_additional_mail_agents');

    $array = json_decode(base64_decode($json_string), true);

    $connection_details = get_option('transmail_connection_status');
    $connection_status = json_decode($connection_details, true);
    $connected_emails = [];

    $connected = false;
    if (is_array($array) && count($array) > 0) {
        $keys = array_keys($array);
        if ($connection_status) {
            foreach ($keys as $email) {
                $isConnected = true;
                foreach ($connection_status as $connection) {
                    if (isset($connection['email']) && $connection['email'] === $email) {
                        $isConnected = false;
                        break;
                    }
                }

                if ($isConnected) {
                    $connected_emails[] = $email;
                }
            }
        }
        else {
            $connected = true;
        }
    }
            
    // if((empty($connected_emails) && !$connected)){
    //     echo '<div class="error"><p><strong>'.esc_html__('Please configure your account to retry failed email.').'</strong></p></div>'."\n";
    // }



    $length = 0;
    if (is_array($array)) {
        $length = count($array);
    }

    if($length > 0){
        if((empty($connected_emails) && !$connected)){
            echo '<div class="error"><p><strong>'.esc_html__('Please configure your account to retry failed email.').'</strong></p></div>'."\n";
        }
        if(is_admin() || current_user_can('administrator')) 
        {     
    global $wpdb;
    $table_name = $wpdb->prefix . 'transmail_failed_emails';
    
    $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
    
    ?>
    <head>
       <meta charset="UTF-8">
       <title>Zoho Mail</title>
   </head>
<form method="post" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"], ENT_QUOTES, 'UTF-8'); ?>">
               <?php wp_nonce_field('transmail_send_mail_nonce'); ?>
               <body>
                  <div class="zm-page">
                      <div class="zm-page-header">
                        <img src=<?php echo esc_url(plugins_url('assets/images/zeptomail.svg',__FILE__))?> title="Zoho" alt="Zoho" width="162">
                      </div>
                    <div class="zm-page-content">
                        <div class="zm-page-content-title-wrapper">
                            <h3 class="zm-page-content-title">Email logs - Failed emails</h3>
                        </div>
                        <div>
                            <p class="zm-page-content-text">View the logs for emails that could not be delivered using the Zoho ZeptoMail plugin. You can retry delivery of these emails from here.</p>
        </br></div>
                        <div class="zm-page-content-table-wrapper">
                            <table class="zm-page-content-table">
                                <thead class="zm-page-content-table-header">
                                    <tr>
                                        <th></th>
                                        <th>Time</th>
                                        <th>To address</th>
                                        <th>Subject</th>
                                        <th>Message</th>
                                        <th>Attachments</th>
                                        <th>Error code</th>
                                        <th>Error description</th>
                                        <th></th>
                                        <th>
                                            <div class="zm-page-content-failed-log-filter">
                                                <svg id="Layer_2" data-name="Layer 2" width="14" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><defs><style>.cls-1{fill:none;}</style></defs><path id="_07" data-name=" 07" d="M14.05,1.06H2a1,1,0,0,0-.8,1.58L5.82,7.8a.82.82,0,0,1,.2.5V14a1,1,0,0,0,1,1,.7.7,0,0,0,.49-.2l2-1.58a1,1,0,0,0,.49-.9v-4c0-.2,0-.4.2-.5l4.66-5.16a1,1,0,0,0-.79-1.58ZM9.39,7.11h0A2,2,0,0,0,9,8.3v4H9L7,14V8.3a2,2,0,0,0-.4-1.19h0L2.05,2.05H14Z"/><rect class="cls-1" x="1" y="1" width="14" height="14" width="16px;"/></svg>
                                                <div class="zm-page-content-failed-log-filter-list-wrapper">
                                                    <ul class="zm-page-content-failed-log-filter-list">
                                                        <li class="zm-page-content-failed-log-filter-list-item" data-filter="ALL">ALL</li>
                                                        <li class="zm-page-content-failed-log-filter-list-item" data-filter="SERR_157">SERR_157</li>
                                                        <li class="zm-page-content-failed-log-filter-list-item" data-filter="LE_102">LE_102</li>
                                                        <li class="zm-page-content-failed-log-filter-list-item" data-filter="SM_111">SM_111</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="zm-page-content-table-body" id="data-table">
                                <?php
                        if ($results) {
                            foreach ($results as $row) {
                        ?>  
				<tr id="row-<?php echo esc_attr($row['id']); ?>">
                                        <td>
                                            <div class="zm-page-content-table-row-checkbox">
                                                <input type="checkbox" class="row-checkbox" data-id="<?php echo esc_attr($row['id']); ?>">
                                            </div>
                                        </td>
                                        <td><div class="zm-page-content-table-msg"><?php echo esc_html($row['failed_at']); ?></div></td>
                                        <td><div class="zm-page-content-table-msg"><?php echo esc_html(transmail_validate_email($row['email_address'])); ?></div></td>
                                        <td><div class="zm-page-content-table-msgs"><?php echo esc_html($row['email_subject']); ?></div></td>
                                        <td class="zm-page-content-table-tdd">
    					    <div class="zm-page-content-table-msgs"><?php echo wp_kses_post($row['email_body']); ?></div>
					</td>
                                        <td>
                                            <div class="zm-page-content-table-msg">
                                                <?php
                                                if ($row['attachments'] == 0) {
                                                    echo 'No files';
                                                } else {
                                                    $attachment_files_raw = $row['attachment_files'];
                                                    $attachment_paths = json_decode($attachment_files_raw, true);

                                                    $att_count = 0;
                                                    if (!empty($attachment_paths) && is_array($attachment_paths)) {
                                                        foreach ($attachment_paths as $attachment) {
                                                            $att_count++;
                                                        }
                                                    } 
                                                    //else {
                                                      //  error_log("list No attachments available.");
                                                    //}

                                                    if($att_count > 0) {
                                                        ?><span><?php echo intval($att_count); ?> Attachments</span>
                                                        <i title="File does not exist. The attachment may be unavailable or missing during retry." style="color:red;">&#9432;</i>
                                                    <?php
                                                    }
                                                    else {
                                                        echo "No files";
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </td>
                                        <td><div class="zm-page-content-table-msg"><?php echo esc_html($row['error_code']); ?></div></td>
                                        <td><div class="zm-page-content-table-msg"><?php echo wp_kses_post($row['error_description']); ?></div></td>
                                        <td>
                                            <button class="retry-button no-border no-background" title="Resend email" data-id="<?php echo intval($row['id']); ?>">
                                            <?php if(empty($connected_emails) && !$connected): ?>
                                                disabled
                                            <?php endif; ?>>
                                            <svg id="Layer_2" data-name="Layer 2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" style="width:16px;" fill="#264DED">
                                            <path d="M2,8a.5.5,0,1,1-1,0A7,7,0,0,1,13,3.12V1.55a.5.5,0,0,1,1,0v3a.5.5,0,0,1-.5.49h-3a.5.5,0,0,1,0-1h2A6,6,0,0,0,2,8Zm12.41-.5A.5.5,0,0,0,14,8,6,6,0,0,1,3.54,12h2a.5.5,0,0,0,0-1h-3a.5.5,0,0,0-.5.49v3a.5.5,0,0,0,1,0V12.88A7,7,0,0,0,15,8a.5.5,0,0,0-.5-.5Z"/></svg>
                                            </button>
                                        </td>
                                        <td>
                                            <button class="delete-button no-border no-background" title="Delete log" data-id="<?php echo intval($row['id']); ?>">
                                            <div class="zm-page-content-trash-icon">
                                            <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                                viewBox="0 0 14 14" style="width:16px;" fill="#777777" style="enable-background:new 0 0 14 14;" xml:space="preserve" fill="#777777">
                                            <path d="M12.5,2H10V1.6C10,0.7,9.3,0,8.5,0H5.5C4.7,0,4,0.7,4,1.6V2H1.5C1.2,2,1,2.2,1,2.5S1.2,3,1.5,3H2v8.8C2,13.1,3.3,14,4.4,14
                                            h5.1c1.1,0,2.4-0.9,2.4-2.2V3h0.5C12.8,3,13,2.8,13,2.5S12.8,2,12.5,2z M5,1.6C5,1.3,5.2,1,5.5,1h2.9C8.8,1,9,1.3,9,1.6V2H5V1.6z
                                                M11,11.8c0,0.7-0.8,1.2-1.4,1.2H4.4C3.8,13,3,12.4,3,11.8V3h8V11.8z M5,9.5v-4C5,5.2,5.2,5,5.5,5S6,5.2,6,5.5v4
                                            C6,9.8,5.8,10,5.5,10S5,9.8,5,9.5z M8,9.5v-4C8,5.2,8.2,5,8.5,5S9,5.2,9,5.5v4C9,9.8,8.8,10,8.5,10S8,9.8,8,9.5z"/>
                                            </svg>
                                            </div>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php } }
                                    else {
                                        ?>
                                        <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>No failed logs found</td>                                        
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr><?php
                                    }?>
                                </tbody>
                            </table>
                        </div>
                     </div>
                 </div>
             </body>
         </form>
 <?php
        }
}
else {
    echo '<div class="error"><p><strong>'.__('Configure Your Account').'</strong></p></div>'."\n";
}

}

function send_test_email($fromEmail, $fromName, $token) {
    $to = $fromEmail; 
    $subject = 'ZeptoMail plugin for WordPress - Test Email';
    $message = '<html><body><p>Hello,</p><br><br><p>We\'re glad you\'re using our ZeptoMail plugin. This is a test email to verify your configuration details. 
    Thank you for choosing ZeptoMail for your transactional email needs.<p><br><br>Team ZeptoMail</body></html>';

    $headers = array(
        'From: ' . $fromName . ' <' . $fromEmail . '>',
        'Authorization' => $token,
        'User-Agent' => 'Zepto_WordPress',
        'Content-Type: text/html; charset=UTF-8'
    );
    $sent = wp_mail($to, $subject, $message, $headers, null);
    return $sent;
}

function update_log_limit($transmail_max_log_limit) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'transmail_failed_emails';
    
    $row_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

    $row_count = (int) $row_count;

    $max_log_count = $transmail_max_log_limit;
    if ($row_count > $max_log_count) {
            $limit  = $row_count - $max_log_count + 1;

            $ids_to_delete = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT id FROM $table_name ORDER BY id ASC LIMIT %d",
                    $limit
                )
            );

            if (!empty($ids_to_delete)) {
                $ids_placeholder = implode(',', array_fill(0, count($ids_to_delete), '%d'));
                $deleted = $wpdb->query(
                    $wpdb->prepare(
                        "DELETE FROM $table_name WHERE id IN ($ids_placeholder)",
                        ...$ids_to_delete
                    )
                );
		/*
                if ($deleted !== false) {
                    error_log("Successfully deleted " . $limit . " old records.");
                } else {
                    error_log("Failed to delete old records.");
                }*/
            }
    }
}

     
	function transmail_validate_email($email) {
	    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
	}

	function transmail_validate_domain($domain) {
	    return filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) ? $domain : false;
	}

	function transmail_validate_url($url) {
	    return filter_var($url, FILTER_VALIDATE_URL) ? $url : '';
	}

	function transmail_validate_content_type($input) {
	    $allowed_values = ['html', 'plaintext'];
	    return in_array(strtolower($input), $allowed_values, true) ? strtolower($input) : false;
	}
	
	function transmail_validate_from_name($input) {
	    // Allow letters, numbers, spaces, and basic special chars (.-_)
	    $input = trim($input);
	    if (preg_match('/^[a-zA-Z0-9 ._-]{1,50}$/', $input)) {
		return $input;
	    }
	    return false;
	}


function transmail_settings_callback() {
  
  if (isset($_POST['transmail_submit']) && !empty($_POST)) {
    $nonce = sanitize_text_field($_REQUEST['_wpnonce']);
    if (!wp_verify_nonce($nonce, 'transmail_settings_nonce')) {
        echo '<div class="error"><p><strong>'.esc_html__('Reload the page again').'</strong></p></div>'."\n";
    } else {
        $transmail_domain_name = transmail_validate_domain(sanitize_text_field($_POST['transmail_domain_name']));
        $transmail_content_type = transmail_validate_content_type(sanitize_text_field($_POST['transmail_content_type']));
        $transmail_max_log_limit = absint($_POST['transmail_max_log_limit']);
        if ( $transmail_max_log_limit < 1 ) {
            $transmail_max_log_limit = 1;
        } elseif ( $transmail_max_log_limit > 100 ) {
            $transmail_max_log_limit = 100;
        }
    }
    update_option('transmail_content_type',$transmail_content_type, false);
    update_option('transmail_domain_name',$transmail_domain_name, false);
    update_option( 'transmail_max_log_limit', $transmail_max_log_limit );

    update_log_limit($transmail_max_log_limit);
    

    $tempDataCount = isset($_POST['tempDataCount']) ? intval($_POST['tempDataCount']) : 0;
    $defaultData = isset($_POST['defaultData']) ? intval($_POST['defaultData']) : 1;

    $temp_data = isset($_POST['tempData']) ? $_POST['tempData'] : array();
    //error_log("tempda: " . print_r($temp_data, true));

    $temp_data = json_decode($temp_data);

    //error_log("tempda: " . print_r($temp_data, true));
    //error_log("defaultData: " . $defaultData);

    $json_data = array();
    $errors = array();
    for ($i = 1; $i <= $tempDataCount; $i++) {
        $fromName = isset($_POST["transmail_from_name_$i"]) ? transmail_validate_from_name(sanitize_text_field($_POST["transmail_from_name_$i"])) : '';
        $fromEmail = isset($_POST["transmail_from_email_id_$i"]) ? transmail_validate_email(sanitize_email($_POST["transmail_from_email_id_$i"])) : '';
        $token = isset($_POST["transmail_send_mail_token_$i"]) ? sanitize_text_field($_POST["transmail_send_mail_token_$i"]) : '';

        if ($fromEmail) { 
            $json_data[$fromEmail][] = array(
                "fromName" => $fromName,
                "Token" => $token,
                "isDefault" => $defaultData == $i ? true : false
            );
            
            if (!send_test_email($fromEmail, $fromName, $token)) {
                $data = json_decode(get_option('transmail_test_mail_case'));
                if($data != ''){
                    $message= ''.$data->error->details[0]->message;
                    $reason = '';
                    if(!empty($data->error)) {
                        if(!empty($data->error->details[0]->message) && strcmp($data->error->details[0]->message,"Invalid API Token found") == 0 ) {
                            $reason = "Invalid API Token found";
                        }
                        if(!empty($data->error->details[0]->target) && strcmp($data->error->details[0]->target,"from") == 0 ) {
                            $reason = "Invalid From address";
                        }
                        $errors[] = array(
                            'email' => $fromEmail,
                            'field' => "transmail_from_email_id_$i",
                            'message' => 'Configuration Failed for :' . esc_html($fromEmail),
                            'reason' => $reason
                        );
                    }
                } else {
                    $errors[] = array(
                        'email' => $fromEmail,
                        'field' => "transmail_from_email_id_$i",
                        'message' => 'Configuration Failed for :' . esc_html($fromEmail),
                        'reason' => 'Internal Server Error'
                    );
                }
           }
        }
    }   
    $json_string = json_encode($json_data); 

    echo '<div class="updated"><p><strong>'.esc_html__('Plugin Configuration Settings has been saved successfully!').'</strong></p></div>'."\n";

    $json_error_data = json_encode($errors);
    update_option('transmail_connection_status', $json_error_data, false);
    update_option('transmail_additional_mail_agents',base64_encode($json_string), false);
}

global $wpdb;
$option_name = 'transmail_additional_mail_agents';
$table_name = $wpdb->prefix . 'options';
$result = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM $table_name WHERE option_name = %s", $option_name));
if ($result !== null && $result !== '') {
    $mail_agents = json_decode(base64_decode($result), true);
    $length = count($mail_agents);

    update_option('transmail_mail_agents_count', $length, false);
}else {
    $mail_agents = false;
}
$connection_details = get_option('transmail_connection_status');
$connection_status = json_decode($connection_details, true);
?>
<head>
    <meta charset="UTF-8">
    <title>ZeptoMail by Zoho Mail</title>
</head>
<body>
<?php
    $temp_data = [];

    if ($mail_agents) {
        $mail_agents_keys = array_keys($mail_agents);
        $mail_agents_values = array_values($mail_agents);

        for ($i = 0; $i < count($mail_agents_keys); $i++) {
            $email = $mail_agents_keys[$i];
            $agents = $mail_agents_values[$i];

            foreach ($agents as $index => $agent) {
                $isConnected = true;
                $reason = '';
                if ($connection_status) {
                    foreach ($connection_status as $connection) {
                        if (isset($connection['email']) && $connection['email'] === $email) {
                            $isConnected = false;
                            $reason = $connection['reason'];
                            break;
                        }
                    }
                }

                $temp_data[] = [
                    'fromName' => $agent['fromName'],
                    'fromEmail' => $email,
                    'token' => $agent['Token'],
                    "isDefault" => isset($agent['isDefault']) ? $agent['isDefault'] : false,
                    'isConnected' => $isConnected,
                    'reason' => $reason,
                ];
            }
        }
    }

    echo '<script type="text/javascript">';
    echo 'var tempData = ' . json_encode($temp_data) . ';';
    echo '</script>';
?>
    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>" onsubmit="return validateForm()">
        <?php wp_nonce_field('transmail_settings_nonce'); ?>
        <div class="zm-page">
            <div class="zm-page-header">
                <img src=<?php echo esc_url(plugins_url('assets/images/zeptomail.svg',__FILE__))?> title="Zoho" alt="Zoho" width="162" style="margin-right: 15px;">
            </div>
            <div class="zm-page-content">
                <div class="zm-page-content-title-wrapper">
                    <h3 class="zm-page-content-title">Welcome to Zoho ZeptoMail!</h3>
                    <p class="zm-page-content-text">Thank you for choosing Zoho ZeptoMail as your transactional email sending service. Read our <a class="zm_a" href=<?php echo esc_url("https://www.zoho.com/zeptomail/help/wordpress-plugin.html")?> target="_blank">help documentation</a> to know about our plugin in detail.</p>
                </div>
                <div class="form-row-wrapper">
                    <div class="form-row">
                        <label class="form--label">Where is your account hosted?</label>
                        <select class="form--input form--input--select" name="transmail_domain_name">
                            <option value="zoho.com" <?php if(get_option('transmail_domain_name') == "zoho.com") {?> selected="true"<?php } ?>>zeptomail.zoho.com</option>
                            <option value="zoho.eu" <?php if(get_option('transmail_domain_name') == "zoho.eu") {?> selected="true"<?php } ?>>zeptomail.zoho.eu</option>
                            <option value="zoho.in" <?php if(get_option('transmail_domain_name') == "zoho.in") {?> selected="true"<?php }?>>zeptomail.zoho.in</option>
                            <option value="zoho.com.cn" <?php if(get_option('transmail_domain_name') == "zoho.com.cn") {?>selected="true"<?php }?>>zeptomail.zoho.com.cn</option>
                            <option value="zoho.com.au" <?php if(get_option('transmail_domain_name') == "zoho.com.au"){?>selected="true"<?php }?>>zeptomail.zoho.com.au</option>
                            <option value="zohocloud.ca" <?php if(get_option('transmail_domain_name') == "zohocloud.ca"){?>selected="true"<?php }?>>zeptomail.zohocloud.ca</option>
                            <option value="zoho.sa" <?php if(get_option('transmail_domain_name') == "zoho.sa"){?>selected="true"<?php }?>>zeptomail.zoho.sa</option>
                            <option value="zoho.jp" <?php if(get_option('transmail_domain_name') == "zoho.jp"){?>selected="true"<?php }?>>zeptomail.zoho.jp</option>
                        </select><br>
                        <small class="form-text">The region where your ZeptoMail account is hosted. The URL displayed on logging in.</small>
                    </div>
                    <div class="form-row">
                        <label class="form--label">Email format   </label>
                        <select class="form--input form--input--select" name="transmail_content_type">
                            <option value="plaintext" <?php if(get_option('transmail_content_type') == "plaintext") {?> selected="true"<?php } ?>>Plaintext</option>
                            <option value="html" <?php if(get_option('transmail_content_type') == "html") {?> selected="true"<?php } ?>>HTML</option>
                        </select><br>
                        <small class="form-text">The preferred format for the body of your email.</small>
                    </div>
                    <div id="mail-agents">
                        <br>
                        <div class="form-row-group">
                                    <div class="form-row-group-title">
                                    <label class="form--label" style="width:158px" title="The sender name displayed on the emails sent from the plugin."> From Name</label></div>
                                    <div class="form-row-group-title">
                                    <label class="form--label" style="width:158px" title="The email address that will be used to send emails.">  From address</label></div>
                                    <div class="form-row-group-title">
                                    <label class="form--label" style="width:158px" title="Send mail token generated in the relevant Mail Agent in ZeptoMail."> Send mail token</label></div>
                        </div>
                        <div id="form-container"></div>
                    </div>
                    <div class="form-row">
                            <h3 style="
                            margin-block: 0 12px;
                            font-size: 14px;
                        ">Logs limit</h3>
                                                <label style="font-size: 14px;">Only keep &nbsp;<input type="number" name="transmail_max_log_limit" value="<?php echo esc_attr( get_option('transmail_max_log_limit', 50) ); ?>"  min="1" font-size="14px" max="100" maxlength="3"  required spellcheck="false" style="
                            padding-inline-end: 0;
                            border: 1px solid #E2E2E2;
                            outline: none;
                            width: 60px;
                            font-size: 14px;
                        ">&nbsp; recent logs
                                            </label></div>
                    <input type="hidden" name="tempDataCount" id="tempDataCount" value="0">
                    <input type="hidden" name="tempData" id="tempData" value=''>
                    <input type="hidden" name="defaultData" id="defaultData" value="1"> 
                    <div class="form-row form-row-btn">
                        <input type="submit" name="transmail_submit" id="transmail_submit" class="btn" value="Save and test configuration"/> 
                    </div>
                </div>
            </div>
        </div>
    </form>
              </body>
              <?php
              

          }
    add_action('admin_menu','transmail_integ_settings');


    function transmail_send_mail_callback() {
            $json_string = get_option('transmail_additional_mail_agents');

            $array = json_decode(base64_decode($json_string), true);

            $connection_details = get_option('transmail_connection_status');
            $connection_status = json_decode($connection_details, true);
            $connected_emails = [];

            $connected = false;
            if(is_array($array) && count($array) > 0){
                $keys = array_keys($array);

                if ($connection_status) {
                    foreach ($keys as $email) {
                        $isConnected = true;
                        foreach ($connection_status as $connection) {
                            if (isset($connection['email']) && $connection['email'] === $email) {
                                $isConnected = false;
                                break;
                            }
                        }
                        if ($isConnected) {
                            $connected_emails[] = $email;
                        }
                    }
                } else {
                    $connected = true;
                    foreach ($keys as $email) {
                            $connected_emails[] = $email;
                    }
                }
            }

            if ((!empty($connected_emails) || $connected) && is_array($array)) {
                $length = count($array);
                if($length > 0) {
                    if(is_admin() || current_user_can('administrator')) { 
                        if(isset($_POST['transmail_send_mail_submit']) && !empty($_POST)){
                            $nonce = sanitize_text_field($_REQUEST['_wpnonce']);
                            if (!wp_verify_nonce($nonce, 'transmail_send_mail_nonce')) {
                              echo '<div class="error"><p><strong>'.esc_html__('Reload the page again').'</strong></p></div>'."\n";
                            } else {
                            if($length < 1){
                                echo '<div class="error"><p><strong>'.esc_html__('Account not Configured').'</strong></p></div>'."\n";
                            }
                            $from_address = transmail_validate_email(sanitize_email($_POST['transmail_test_from_address']));
                            $toAddressTest = transmail_validate_email(sanitize_email($_POST['transmail_to_address']));
    
    			    $subjectTest = isset($_POST['transmail_subject']) ? wp_kses_post($_POST['transmail_subject']) : '';
		            $contentTest = isset($_POST['transmail_content']) ? wp_kses_post(($_POST['transmail_content'])) : '';

			    if (!$from_address || !$toAddressTest || !$subjectTest || !$contentTest) {
				 die('Invalid input detected.');
			    }
			    
                            $json_string = get_option('transmail_additional_mail_agents');
    
                            $json_data = json_decode(base64_decode($json_string), true);
    
                            $keys = array_keys($json_data);
    
                            if (isset($keys) && isset($keys[$from_address])) {
                                $result['fromName'] = $keys[$from_address]['fromName'];
                                $result['Token'] = $keys[$from_address]['Token'];
                            }
    
                            $headers = array('From: ' . $from_address);
                            
                            if(wp_mail($toAddressTest,$subjectTest,$contentTest, $headers, null)) {
                                echo '<div class="updated"><p><strong>'.esc_html__('Mail Sent Successfully').'</strong></p></div>'."\n";
                            } else {
                                $data = json_decode(get_option('transmail_test_mail_case'));
                                $message= ''.$data->error->details[0]->message;
                                $reason = '';
                                $errors = array();
                                if(!empty($data->error)) {
                                    if(!empty($data->error->details[0]->message) && strcmp($data->error->details[0]->message,"Invalid API Token found") == 0 ) {
                                        $reason = "Invalid API Token found";
                                    }
                                    if(!empty($data->error->details[0]->target) && strcmp($data->error->details[0]->target,"from") == 0 ) {
                                        $reason = "Invalid From address";
                                    } else if(!empty($data->error->details[0]->message)){
                                        $reason = $data->error->details[0]->message;
                                    }
                                    $errors[] = array(
                                        'field' => $from_address,
                                        'message' => 'Configuration Failed for :' . esc_html($from_address),
                                        'reason' => $reason
                                    );
                                }
                                echo '<div class="error"><p><strong>'.esc_html__('Mail Sending Failed').'</strong></p></div>'."\n";
                                foreach ($errors as $error) {
                                    echo '<div class="error"><p><strong>Error: ' . esc_html(isset($error['reason']) ? $error['reason'] : 'Unknown error') . '</strong></p></div>' . "\n";
                                }
                            }        
                        }
                    }
                }
            } else {
                echo "The decoded value is not an array.";
            }
            
            ?>
            <head>
               <meta charset="UTF-8">
               <title>Zoho Mail</title>
           </head>

           <form method="post" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"], ENT_QUOTES, 'UTF-8'); ?>">
               <?php wp_nonce_field('transmail_send_mail_nonce'); ?>
               <body>
                  <div class="zm-page">
                      <div class="zm-page-header">
                        <img src=<?php echo esc_url(plugins_url('assets/images/zeptomail.svg',__FILE__))?> title="Zoho" alt="Zoho" width="162">
                      </div>
                    <div class="zm-page-content">
                        <div class="zm-page-content-title-wrapper">
                            <h3 class="zm-page-content-title">Send test email</h3>
                            <p class="zm-page-content-text">Test email sending from the Zoho ZeptoMail plugin by sending a test email to the recipient of your choice.</p>
                        </div>

                      <div class="form-row-wrapper">
                        <div class="form-row">
                            <label class="form--label">From address</label>
                            <select class="form--input" name="transmail_test_from_address" required="required">
                                <?php                           
                                if (!empty($connected_emails)) {
                                    foreach ($connected_emails as $email) {
                                        $email_sanitized = isset($email) && is_string($email) ? trim($email) : 'Unknown';
					echo '<option value="' . esc_attr($email_sanitized) . '">' . esc_html($email_sanitized) . '</option>';
                                    }
                                } else {
                                    echo '<option value="">No account configured</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-row">
                            <label class="form--label">To address</label>
                            <input type="text" class="form--input" name="transmail_to_address" required = "required" />
                        </div>
                        <div class="form-row">
                            <label class="form--label">Subject</label>
                            <textarea type="text" class="form--input" id="input-subject" name="transmail_subject" placeholder="Enter the subject"></textarea>
                        </div>
                        <div class="form-row">
                             <label class="form--label">Content</label>
                            <textarea type="text" class="form--input" id="input-content" name="transmail_content" placeholder="Enter/paste the content"></textarea>
                        </div>
                             <div class="form-row form-row-btn"> <input type="submit" class = "btn" name="transmail_send_mail_submit" id="transmail_send_mail_submit" value="<?php _e('Send test email');?>">
                             </div>
                         </div>
                     </div>
                 </div>
             </body>
         </form>
         <?php
     }
     else {
       echo '<div class="error"><p><strong>'.__('Configure Your Account').'</strong></p></div>'."\n";
     }    
    }


function insert_failed_email($from_address, $email_address, $email_subject, $email_body, $responseArray, $attachments = array()) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'transmail_failed_emails';
    

    $errorCode = $responseArray[0]['code'];
    $errorMessage = $responseArray[0]['message'];

    $attachment_paths = !empty($attachments) ? json_encode($attachments) : '';
    
    $wpdb->insert(
        $table_name,
        array(
            'headers'  => $from_address,
            'email_address' => $email_address,
            'email_subject' => $email_subject,
            'email_body'    => $email_body,
            'attachments' => !empty($attachments)? 1: 0,
            'error_code' => $errorCode,
            'error_description' => $errorMessage,
            'attachment_files' => $attachment_paths
        ),
        array(
            '%s', 
            '%s', 
            '%s',  
            '%s',
            '%d',
            '%s',
            '%s',
            '%s'
        )
    );

    $row_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

    $row_count = (int) $row_count;

    $max_log_count = intval(get_option('transmail_max_log_limit', 100));
    if ($row_count > $max_log_count) {
        $limit  = $row_count - $max_log_count;

        $ids_to_delete = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT id FROM $table_name ORDER BY id ASC LIMIT %d",
                $limit
            )
        );

        if (!empty($ids_to_delete)) {
            $ids_placeholder = implode(',', array_fill(0, count($ids_to_delete), '%d'));
            $deleted = $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM $table_name WHERE id IN ($ids_placeholder)",
                    ...$ids_to_delete
                )
            );
/*
            if ($deleted !== false) {
                error_log("Successfully deleted " . $limit . " old records.");
            } else {
                error_log("Failed to delete old records.");
            }*/
        }
    }
}


if(!function_exists('wp_mail')) {
    function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() ) { 
      
      $atts = apply_filters( 'wp_mail', compact( 'to', 'subject', 'message', 'headers', 'attachments' ) );

      if ( isset( $atts['to'] ) ) {
          $to = $atts['to'];
      }
      if ( !is_array( $to ) ) {
          $to = explode( ',', $to );
      }
      if ( isset( $atts['subject'] ) ) {
          $subject = $atts['subject'];
      }
      if ( isset( $atts['message'] ) ) {
          $message = $atts['message'];
      }
      if ( isset( $atts['headers'] ) ) {
          $headers = $atts['headers'];
      } else {
              $headers = '';
      }
      if ( isset( $atts['attachments'] ) ) {
          $attachments = $atts['attachments'];
      }
      if (!is_array($attachments)) {
          $attachments = $attachments ? array($attachments) : array();
      }
      foreach ($attachments as &$attachment) {
          $attachment = str_replace("\r\n", "\n", $attachment);
      }
  
      $attachments = implode("\n", $attachments);
      
    $allowed_upload_dir = wp_upload_dir();
    $allowed_dir = realpath($allowed_upload_dir['basedir']);
  
    $content_type = null;
    $cc = $bcc = $reply_to = array();
    $dynamicFrom = array();
    $from_email = '';
    if ( empty( $headers ) ) {
      $headers = array('');
    } else {
        
      if ( !is_array( $headers ) ) {
          $tempheaders = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
          $headers = array('');
      } else {
          $tempheaders = $headers;
      }
  
      if (is_array($tempheaders)) {
          $headerString = implode("\n", $tempheaders);
      } else {
          $headerString = (string) $tempheaders;
      }
      
      //error_log("Header value:\n" . $headerString);
  
      
              // Iterate through the raw headers
      foreach ( (array) $tempheaders as $header ) {
          if ( strpos($header, ':') === false ) {
              if ( false !== stripos( $header, 'boundary=' ) ) {
                  $parts = preg_split('/boundary=/i', trim( $header ) );
                  $boundary = trim( str_replace( array( "'", '"' ), '', $parts[1] ) );
              }
              continue;
          }
                  // Explode them out
          list( $name, $content ) = explode( ':', trim( $header ), 2 );
    
                  // Cleanup crew
          $name    = trim( $name    );
          $content = trim( $content );
          $content_type = null;
          $from = array();
          //$from_email = '';    
          if (stripos($name, 'content-type') !== false) {
              $name = 'content-type';
          }
          switch ( strtolower($name) ) {
              case 'content-type':
                  if ( strpos( $content, ';' ) !== false ) {
                      list( $type, $charset_content ) = explode( ';', $content );
                      $content_type = trim( $type );
                      if ( false !== stripos( $charset_content, 'charset=' ) ) {
                          $charset = trim( str_replace( array( 'charset=', '"' ), '', $charset_content ) );
                      } elseif ( false !== stripos( $charset_content, 'boundary=' ) ) {
                          $boundary = trim( str_replace( array( 'BOUNDARY=', 'boundary=', '"' ), '', $charset_content ) );
                          $charset = '';
                      }
                      // Avoid setting an empty $content_type.
                  } elseif ( '' !== trim( $content ) ) {
                      $content_type = trim( $content );
                  }
                  break;
              case 'cc':
                  $cc = array_merge( (array) $cc, explode( ',', $content ) );
                  break;
              case 'bcc':
                  $bcc = array_merge( (array) $bcc, explode( ',', $content ) );
                  break;
              case 'reply-to':
                  $reply_to = array_merge( (array) $reply_to, explode( ',', $content ) );
                  break;
              case 'from':
                  $dynamicFrom = array_merge( (array) $from, explode( ',', $content ) );
  
                  $bracket_pos = strpos( $content, '<' );
                  if ( false !== $bracket_pos ) {
                      // Text before the bracketed email is the "From" name.
                      if ( $bracket_pos > 0 ) {
                          $from_name = substr( $content, 0, $bracket_pos );
                          $from_name = str_replace( '"', '', $from_name );
                          $from_name = trim( $from_name );
                      }
  
                      $from_email = substr( $content, $bracket_pos + 1 );
                      $from_email = str_replace( '>', '', $from_email );
                      $from_email = trim( $from_email );
  
                      // Avoid setting an empty $from_email.
                  } elseif ( '' !== trim( $content ) ) {
                      $from_email = trim( $content );
                      //echo '<div class="error"><p><strong> '.esc_html__('there is no lessthan char').''.$content.'</strong></p></div>'."\n";
                      //echo '<div class="error"><p><strong> '.$from_email.'</strong></p></div>'."\n";
                  }
                  //echo '<div class="error"><p><strong> from name is '.esc_html__($from_name).'</strong></p></div>'."\n";
                  break;
              default:
                  $headers[trim( $name )] = trim( $content );
                  break;
          }
      }
  }
  
  //echo '<div class="error"><p><strong>'.esc_html__('inside wp-mail function()').'</strong></p></div>'."\n";
  $content_type = apply_filters( 'wp_mail_content_type', $content_type );    
  $data = array();
  $token = '';
  $fromAddress = array();
  if (!empty($from_name)) {
     $fromAddress['name'] = $from_name;
  } else {
    //echo "inside else part of from address:";
    $json_string = get_option('transmail_additional_mail_agents');
    
    // Decode JSON string back into array
    $json_data = json_decode(base64_decode($json_string), true);
    
                                
    // Use the data as needed
    //print_r($json_data);
    if (is_array($json_data)) {
    
    $keys = array_keys($json_data);

    //echo "json_Data: " .$json_string;


     // Check if the key exists and extract the details
    if (isset($keys) && isset($keys[$from_email])) {
        //echo " inside the from email";
        $fromAddress['name'] = $keys[$from_email]['fromName'];
    }
    }
    //$fromAddress['name'] = get_option('transmail_from_name');
  }
  //error_log('keys form:'.$fromAddress['name']);
  
  if (!empty($dynamicFrom)) 
  {
      $dynpos = false;
      $dynpos = strpos($dynamicFrom[0], '<');
      if($dynpos !== false) {
        $dynad = substr($dynamicFrom[0], $dynpos+1, strlen($dynamicFrom[0])-$dynpos-2);
        $dynfrom['address'] = transmail_validate_email(sanitize_email($dynad));
        //echo '<div class="error"><p><strong>dynform'.esc_html__($dynfrom['address']).'</strong></p></div>'."\n";
        if($dynpos >0) {
          $dynfrom['name'] = substr($dynamicFrom[0],0,$dynpos);
          $fromAddress['name'] = $dynfrom['name'];
        }
        $fromAddress['address'] = $dynfrom['address'];
       }
        else if(!empty($dynamicFrom[0])) {
          $fromAddress['address'] = $dynamicFrom[0];
       }
  
       //echo '<div class="error"><p><strong>'.esc_html__('dynamicForm is not empty').'</strong></p></div>'."\n";
       //echo '<div class="error"><p><strong>'.esc_html__($dynamicFrom[0]).'</strong></p></div>'."\n";
  }
//   else {
//       $fromAddress['address'] = get_option('transmail_from_email_id');
//       echo '<div class="error"><p><strong>'.esc_html__('dynamicForm is empty').'</strong></p></div>'."\n";
//       echo '<div class="error"><p><strong>dynam 0 fromaddreess'.esc_html__($fromAddress['address']).'</strong></p></div>'."\n";
//   }
  //echo '<div class="error"><p><strong>from address' . esc_html($fromAddress['address']) . '</strong></p></div>' . "\n";
  
  // Retrieve the JSON string from the 'transmail_additional_mail_agents' option
  $json_string = get_option('transmail_additional_mail_agents');
  
  // Decode the JSON string into a PHP associative array
  $email_agents = json_decode(base64_decode($json_string), true);
  if (isset($headers['Authorization'])) {
      $token = $headers['Authorization'];
  }
  else if (is_array($email_agents)) {	
     if(!isset($fromAddress['address'])) {
        //echo "is not set";
        foreach ($email_agents as $email => $details) {
            foreach ($details as $agent) {
                // Check if the mail agent is marked as the default
                if (isset($agent['isDefault']) && $agent['isDefault'] === true) {
                    $fromAddress['address'] = $email;    // Set the default email address
                    $fromAddress['name'] = $agent['fromName'];  // Set the corresponding name
                    $token = $agent['Token'];
                    break; // Exit both loops after finding the default
                }
            }
        }
     }
     else {
// Define the email ID you want to get the token for
      $target_email = $fromAddress['address'];
      
      // Check if the target email exists in the array
      if (isset($email_agents[$target_email])) {
          // Iterate over the details array to get the token
          foreach ($email_agents[$target_email] as $detail) {
              
              //echo 'From Name: ' . esc_html($detail['fromName']) . '<br>';
              //echo 'Token: ' . esc_html($detail['Token']) . '<br>';
              if ( !isset( $fromAddress['name'] ) || empty( $fromAddress['name'] ) ) {
                $fromAddress['name'] = $detail['fromName'];
              }
              $token = $detail['Token'];
          }
      } else {
          //echo 'Email not found in the data.';
  
            foreach ($email_agents as $email => $details) {
                foreach ($details as $agent) {
                    // Check if the mail agent is marked as the default
                    if (isset($agent['isDefault']) && $agent['isDefault'] === true) {
                        $fromAddress['address'] = $email;    // Set the default email address
                        $fromAddress['name'] = $agent['fromName'];  // Set the corresponding name
                        $token = $agent['Token'];
                        break; // Exit both loops after finding the default
                    }
                }
            }
  
          if ($token) {
              //echo 'Email not found in the data. Using the first available token:<br>';
              //echo 'Token: ' . esc_html($token) . '<br>';
          } else {
              echo 'No tokens available in the data.';
          }
      }
     }
      
  }
  //error_log('from name:'. $fromAddress['name']);
  /*
  if($fromAddress['address'] !== get_option('transmail_from_email_id')){
      
      } else {
          echo 'Failed to decode JSON or no data found.';
      }      
  
  } else {
      $token = base64_decode(get_option('transmail_send_mail_token'));
      echo '<div class="error"><p><strong>other than default' . esc_html($token) . '</strong></p></div>' . "\n";
  
  }*/
  $data['from'] =  $fromAddress;

  if (!empty($data['from']['address'])) {
      //echo '<div class="error"><p><strong>' . esc_html($data['from']['address']) . '</strong></p></div>' . "\n";
  } else {
      //echo '<div class="error"><p><strong>from address empty</strong></p></div>' . "\n";
  }
  
  $zmbccs = array();
  $zmbcc = array();
  $zmbce = array();
  if (!empty($bcc)) {
    $count = 0;
    foreach($bcc as $bc) {
      $zmbcc['address'] = $bc;
      $zmbce['email_address'] = $zmbcc;
      $zmbccs[$count] = $zmbce;
      $count = $count + 1;
  }
  $data['bcc'] = $zmbccs;
  }
  
  if(!empty($reply_to)) {
    $replyTos = array();
    $replyTo = array();
    $rte = array();
    $count = 0;
    foreach($reply_to as $reply) {
      $pos = strpos($reply, '<');
      if($pos !== false) {
        $ad = substr($reply, $pos+1, strlen($reply)-$pos-2);
        $replyTo['address'] = $ad;
        $replyTo['name'] = substr($reply,0,$pos-1);
    } else {
        $replyTo['address'] = $reply;
    }
    $replyTos[$count] = $replyTo;
    $count = $count + 1;
  }
  $data['reply_to'] = $replyTos;
  }
  $data['subject'] = $subject;
  
  if(!empty($to) && is_array($to)) {
    $tos = array();
    $count = 0;
    foreach($to as $t) {
      $toa = array();
      $toe = array();
      $pos = strpos($t, '<');
      if($pos !== false) {
        $ad = substr($t, $pos+1, strlen($t)-$pos-2);
        $toa['address'] = transmail_validate_email(sanitize_email($ad));
        $toa['name'] = substr($t,0,$pos-1);
    } else {
        $toa['address'] = transmail_validate_email(sanitize_email($t));
    }
    $toe['email_address'] = $toa;
    $tos[$count] = $toe;
    $count = $count + 1;
  }
  $data['to'] = $tos;
  } else {
    $toa = array();
    $tos = array();
    $toa['address'] = $to;
    $tos[0] = $toa;
    $data['to'] = $to;
  }
  $attachmentJSONArr = array();
  $attachment_paths = array();
  if (!empty($attachments)) {
      if (!is_array($attachments)) {
          $attachments = explode("\n", $attachments);
      }
      $count = 0;
  
      foreach ($attachments as $attfile) {
          if (file_exists($attfile)) {        
           $real_path = realpath($attfile); 
           if ($real_path && strpos($real_path, $allowed_dir) === 0 && file_exists($real_path) && is_readable($real_path)) {
              $attachmentupload = array(
                  'name' => basename($attfile),
                  'mime_type' => mime_content_type($attfile),
                  'content' => base64_encode(file_get_contents($attfile))
              );
              $attachmentJSONArr[$count] = $attachmentupload;
              $relative_path = str_replace(ABSPATH, '', $attfile);
              $attachment_paths[] = $relative_path;
              $count = $count + 1;
            }
          } else {
              error_log("Attachment file does not exist: " . $attfile);
          }
      }
      
      
      //error_log("attachments: " . json_encode($attachmentJSONArr, JSON_PRETTY_PRINT));
      $data['attachments'] = $attachmentJSONArr;
  }
  $files = isset($data['attachments']) ? $data['attachments'] : array();
  $attachedFiles = array();
  
  // Iterate over the attachment data
  foreach ($files as $fileData) {
      // Assuming 'name' is the file path stored in the attachment data
      $attachedFiles[] = $fileData['name'];
  }
  
  if( $content_type == 'text/html' || get_option('transmail_content_type') == 'html') {
    $data['htmlbody'] = $message;
  } else {
    $data['textbody'] = $message;
  } 
  
  
  
  //echo '<div class="error"><p><strong> token is ' . esc_html($token) . '</strong></p></div>' . "\n";
  $headers1 = array(
     'Authorization' => $token,
     'User-Agent' => 'Zepto_WordPress'
  );

  $data_string = json_encode($headers1);

  $data_string = json_encode($data);
  $args = array(
     'body' => $data_string,
     'headers' => $headers1,
     'method' => 'POST'
  );
  $domainName = get_option('transmail_domain_name');
  if (strpos($domainName, 'zoho') === false) {
      $domainName = 'zoho.'.$domainName;
  }
  $urlToSend = Transmail_Helper::getZeptoMailUrlForDomain($domainName).'/v1.1/email';
  $responseSending = wp_remote_post( $urlToSend, $args );
  $http_code = wp_remote_retrieve_response_code($responseSending);
  $responseBody = wp_remote_retrieve_body( $responseSending );

//  echo "respbpody: " . $responseBody;


//   if ( is_wp_error( $responseSending ) ) {
//     echo 'Error: ' . $responseSending->get_error_message();
// } else {
//     echo '<pre>';
//     print_r( $responseSending );
//     echo '</pre>';
// }

  if(!is_wp_error( $responseSending )) {
    update_option('transmail_test_mail_case', $responseSending['body'], false);
  }
    

    //error_log("responsesending body data: ". $responseSending['body']);
    //echo  "responsesending body data: ". $responseSending['body'];


    $responseBody = wp_remote_retrieve_body($responseSending);
      $responseData = json_decode($responseBody);
      
      //error_log("response data: ". $responseBody);
      $mail_data = array(
          'to' => $to,
          'subject' => $subject,
          'message' => $message,
          'headers' => $headers1,
          'attachments' => $attachments
        );
  
  if($http_code == '200' || $http_code == '201') {
      //echo "http codE:  " .$http_code;
      //do_action( 'wp_mail_succeeded', $mail_data );
      //wp_send_json_success(array('status' => 'mail_sent', 'message' => 'Email sent successfully.'));
    return true;  
  } else {
    	if(!is_wp_error( $responseSending )) {
    		update_option('transmail_test_mail_case', $responseSending['body'], false);
    	}
    //echo "http codE:  " .$http_code;
      // Decode the JSON string into an associative array
      $responseArray = json_decode($responseBody, true);
  
      if($responseSending['body'] != '') {
                //echo "resp array: " . $responseArray;
            // Check if the response data was decoded successfully and contains the expected structure
            if (isset($responseArray['error']['details'][0]['code']) && isset($responseArray['error']['details'][0]['message'])) {
                $errorCode = $responseArray['error']['details'][0]['code'];
                $errorMessage = $responseArray['error']['details'][0]['message'];
                //echo "to address: " . $to[0] . PHP_EOL;
                $attachment_paths_json = !empty($attachment_paths) ? $attachment_paths : '';
                insert_failed_email($from_email,$to[0], $subject, $message, $responseArray['error']['details'], $attachment_paths_json);
            }  
       }
        return false;
  }
  
  
  
  
  if (is_object($responseData) && isset($responseData->error)) {
      $details = $responseData->error->details;
      if (is_array($details) && isset($details[0]->message)) {
          $message = $details[0]->message;
      } else {
          $message = "Error details are not available.";
      }
  } else {
      $message = "Error property is not present in the response.";
  }
  
  
  
  
  do_action( 'wp_mail_failed', new WP_Error( 'wp_mail_failed', $message, $mail_data ) );
  return false;
  
  }
  }
  
add_action('wp_ajax_retry_failed_email', 'retry_failed_email');

function retry_failed_email() {
    global $wpdb;

    check_ajax_referer('transmail_failed_email_nonce', 'nonce');

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id > 0) {
        $table_name = $wpdb->prefix . 'transmail_failed_emails'; 
        $record = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));

        if ($record) {
            $headers = array(
                'From' => $record->headers,
                'User-Agent' => 'Zepto_WordPress',
                'Content-Type: text/html; charset=UTF-8'
            );
            $attachments = array();
            $attachment_files_raw = $record->attachment_files;
            if($record->attachment_files){
                $attachment_paths = json_decode($attachment_files_raw, true);

                if (!empty($attachment_paths) && is_array($attachment_paths)) {
                    foreach ($attachment_paths as $attachment) {
                        if (file_exists($attachment)) {
                            $attachments[] = $attachment;
                        } 
                        else {
                            error_log("Attachment file does not exist: " . $attachment);
                        }
                    }
                } 
            }
                $headers = array('From: ' . $record->headers);
                $email_sent = wp_mail($record->email_address, $record->email_subject, $record->email_body, $headers, $attachments);

                if ($email_sent) {
                    wp_send_json_success();

                } else {
                    wp_send_json_error('Failed to retry email.');
                }
    } else {
        wp_send_json_error('Invalid ID.');
    }
    wp_die();
    }
}


add_action('wp_ajax_delete_failed_email', 'delete_failed_email');

function delete_failed_email() {
    check_ajax_referer('transmail_failed_email_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . 'transmail_failed_emails';

    $id = intval($_POST['id']);

    $deleted = $wpdb->delete($table_name, array('id' => $id), array('%d'));

    if ($deleted !== false) {
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }

    wp_die();
}

add_action('wp_ajax_delete_selected_logs', 'handle_delete_selected_logs');

function handle_delete_selected_logs() {
    check_ajax_referer('transmail_failed_email_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . 'transmail_failed_emails';

    $ids = isset($_POST['ids']) ? json_decode(stripslashes($_POST['ids'])) : array();

    $ids = array_map('intval', $ids);

    $deleted = $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM $table_name WHERE id IN (" . implode(',', array_fill(0, count($ids), '%d')) . ")",
            ...$ids
        )
    );

    if ($deleted !== false) {
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }

    wp_die();
}
