<?php
/*
Plugin Name: ZeptoMail
Version: 3.1.4
Plugin URI: https://zeptomail.zoho.com/
Author: Zoho Mail
Author URI: https://www.zoho.com/zeptomail/
Description: Configure your ZeptoMail account to send email from your WordPress site.
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
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-transmail-helper.php';

    function ztm_zmplugin_script() {
        wp_enqueue_style( 'zm_zohoTransMail_style', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', false, '1.0.0' );
    }

    add_action( 'admin_enqueue_scripts', 'ztm_zmplugin_script');

    function zohoTransMail_deactivate() {
    //--------------Clear the credentials once deactivated-------------------	
        delete_option('transmail_send_mail_token');
        delete_option('transmail_bounce_email_address');
        delete_option('transmail_from_email_id');
        delete_option('transmail_from_name');
        delete_option('transmail_content_type');
        delete_option('transmail_domain_name');
        
    }

    register_deactivation_hook( __FILE__, 'zohoTransMail_deactivate');

    

    function transmail_integ_settings() {
     add_menu_page ( 
        'Welcome to ZeptoMail by Zoho Mail',
        'ZeptoMail',
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
        'Test Mail', 
        'manage_options', 
        'transmail-send-mail',
        'transmail_send_mail_callback'
    );
 }




 function transmail_settings_callback() {
  
  if (isset($_POST['transmail_submit']) && !empty($_POST)) {
    $nonce = sanitize_text_field($_REQUEST['_wpnonce']);
    if (!wp_verify_nonce($nonce, 'transmail_settings_nonce')) {
        echo '<div class="error"><p><strong>'.esc_html__('Reload the page again').'</strong></p></div>'."\n";
    } else {
        $need_to_test = 0;
        $transmail_send_mail_token = sanitize_text_field($_POST['transmail_send_mail_token']);
        $transmail_from_email_id = sanitize_email($_POST['transmail_from_email_id']);
        $transmail_domain_name = sanitize_text_field($_POST['transmail_domain_name']);
        $transmail_from_name = sanitize_text_field($_POST['transmail_from_name']);
        $transmail_content_type = sanitize_text_field($_POST['transmail_content_type']);
        if(strcmp($transmail_from_email_id, get_option('transmail_from_email_id')) != 0
          || strcmp($transmail_send_mail_token, base64_decode(get_option('transmail_send_mail_token'))) != 0) {
            
            $need_to_test = 1;

    }
    if(get_option('transmail_send_mail_token') == null || strcmp(get_option("last_test_result"), "failed") == 0) {
        $need_to_test = 1;
    }
    update_option('transmail_send_mail_token',base64_encode($transmail_send_mail_token), false);
    update_option('transmail_from_email_id',$transmail_from_email_id, false);
    update_option('transmail_from_name',stripslashes($transmail_from_name), false);
    update_option('transmail_content_type',$transmail_content_type, false);
    update_option('transmail_domain_name',$transmail_domain_name, false);
    if ( $need_to_test == 1) {
      $headers = array('Content-Type: text/html; charset=UTF-8');
      wp_mail($transmail_from_email_id, "ZeptoMail plugin for WordPress - Test Email", "<html><body><p>Hello,</p><br><br><p>We're glad you're using our ZeptoMail plugin. This is a test email to verify your configuration details. 
          Thank you for choosing ZeptoMail for your transactional email needs.<p><br><br>Team ZeptoMail", $headers, null);
      $data = json_decode(get_option('transmail_test_mail_case'));
      if (!empty($data->data)) {
        echo '<div class="updated"><p><strong>'.esc_html__('Plugin has been configured successfully!').'</strong></p></div>'."\n";
        update_option("last_test_result","sucess",false); 
    } else {
      
      $message= ''.$data->error->details[0]->message;
      if(!empty($data->error)) {
        if(!empty($data->error->details[0]->message) && strcmp($data->error->details[0]->message,"Invalid API Token found") == 0 ) {
          $message = "Configuration failed. Enter a valid Send Mail token and try again.";
      }
      if(!empty($data->error->details[0]->target) && strcmp($data->error->details[0]->target,"from") == 0 ) {
          $message = "Configuration failed. Enter a valid From address and try again.";
      }
      update_option("last_test_result","failed",false); 
  }
  else{
     $message= 'Configuration failed. Verify details and try again. For further assistance, contact <a href="mailto:support@zeptomail.com">support.</a>';
  }
  echo '<div class="error"><p><strong>'.($message).'</strong></p></div>'."\n";
}
} else {
    echo '<div class="updated"><p><strong>'.esc_html__('Configuration saved successfully.').'</strong></p></div>'."\n";
}
}
}
?>
<head>
    <meta charset="UTF-8">
    <title>ZeptoMail by Zoho Mail</title>
</head>
<body>
    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
        <?php wp_nonce_field('transmail_settings_nonce'); ?>
        <div class="page"><div class="page__content">
            <div class="page__header">
                <h2 style="display: flex; align-items: center;" ><img src=<?php echo esc_url(plugins_url('assets/images/icon.png',__FILE__))?> title="Zoho" alt="Zoho" width="60" style="margin-right: 15px;">   Welcome to ZeptoMail by Zoho Mail</h2>
                <p>Visit <a class="zm_a" href=<?php echo esc_url("https://zeptomail.zoho.com/#dashboard/setupDetail")?> target="_blank">here</a> to generate your Send Mail token.</p>
            </div>
            <div class="form">
                <div class="form__row">
                    <label class="form--label">Where is your account hosted?</label>
                    <select class="form--input form--input--select" name="transmail_domain_name">
                        <option value="zoho.com" <?php if(get_option('transmail_domain_name') == "zoho.com") {?> selected="true"<?php } ?>>zeptomail.zoho.com</option>
                        <option value="zoho.eu" <?php if(get_option('transmail_domain_name') == "zoho.eu") {?> selected="true"<?php } ?>>zeptomail.zoho.eu</option>
                        <option value="zoho.in" <?php if(get_option('transmail_domain_name') == "zoho.in") {?> selected="true"<?php }?>>zeptomail.zoho.in</option>
                        <option value="zoho.com.cn" <?php if(get_option('transmail_domain_name') == "zoho.com.cn") {?>selected="true"<?php }?>>zeptomail.zoho.com.cn</option>
                        <option value="zoho.com.au" <?php if(get_option('transmail_domain_name') == "zoho.com.au"){?>selected="true"<?php }?>>zeptomail.zoho.com.au</option>
                        <option value="zohocloud.ca" <?php if(get_option('transmail_domain_name') == "zohocloud.ca"){?>selected="true"<?php }?>>zeptomail.zohocloud.ca</option>
                        <option value="zoho.sa" <?php if(get_option('transmail_domain_name') == "zoho.sa"){?>selected="true"<?php }?>>zeptomail.zoho.sa</option>
                    </select> <br><i class="form__row-info">The region where your ZeptoMail account is hosted. This is the URL displayed on login to your account.</i> </div>
                    <div class="form__row">
                        <label class="form--label">Send Mail Token</label>
                        <input type="password" value="<?php echo base64_decode(get_option('transmail_send_mail_token')); ?>" name="transmail_send_mail_token" class="form--input" id="transmail_send_mail_token" required/> 
                        <i class="form__row-info">Send Mail token of the relevant Mail Agent generated in your ZeptoMail account.</i> 
                    </div>
                    <div class="form__row">
                            <label class="form--label">From Email Address</label>
                            <input type="text" name="transmail_from_email_id" value="<?php echo get_option('transmail_from_email_id') ?>" class="form--input" id="transmail_from_email_id" required/> <i class="form__row-info">The email address that will be used as the From address to send emails
                            .</i> </div>
                            <div class="form__row">
                                <label class="form--label">From Name</label>
                                <input type="text" name="transmail_from_name" value="<?php echo get_option('transmail_from_name') ?>" class="form--input" id="transmail_from_name" required/> <i class="form__row-info">The sender name displayed on the emails sent from the plugin.</i> </div>
                                <div class="form__row">
                                    <label class="form--label">Mail Format</label>
                                    <select class="form--input form--input--select" name="transmail_content_type">
                                        <option value="plaintext" <?php if(get_option('transmail_content_type') == "plaintext") {?> selected="true"<?php } ?>>Plaintext</option>
                                        <option value="html" <?php if(get_option('transmail_content_type') == "html") {?> selected="true"<?php } ?>>HTML</option>
                                    </select> <br><i class="form__row-info">The preferred email format for the body of your email.</i> </div>
                                    <div class="form__row form__row-btn">
                                      <input type="submit" name="transmail_submit" id="transmail_submit" class="btn" value="Save"/> 
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

            
            

            $option = get_option('transmail_send_mail_token'); 
            if(!empty($option)){
               
                if(is_admin() || current_user_can('administrator')) { 
                    if(isset($_POST['transmail_send_mail_submit']) && !empty($_POST)){
                        $nonce = sanitize_text_field($_REQUEST['_wpnonce']);
                        if (!wp_verify_nonce($nonce, 'transmail_send_mail_nonce')) {
                          echo '<div class="error"><p><strong>'.esc_html__('Reload the page again').'</strong></p></div>'."\n";
                      } else {
                        if(empty($option)){          
                            echo '<div class="error"><p><strong>'.esc_html__('Account not Configured').'</strong></p></div>'."\n";
                        }
                        $toAddressTest = sanitize_email($_POST['transmail_to_address']);
                        $subjectTest = sanitize_text_field($_POST['transmail_subject']);
                        $contentTest = sanitize_text_field($_POST['transmail_content']);
                        if(wp_mail($toAddressTest,$subjectTest,$contentTest,'', null)) {
                            echo '<div class="updated"><p><strong>'.esc_html__('Mail Sent Successfully').'</strong></p></div>'."\n";
                        } else {
                            echo '<div class="error"><p><strong>'.esc_html__('Mail Sending Failed').'</strong></p></div>'."\n";
                        }        
                    }
                }
            }
            ?>
            <head>
               <meta charset="UTF-8">
               <title>Zoho Mail</title>
           </head>

           <form method="post" enctype="multipart/form-data" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
               <?php wp_nonce_field('transmail_send_mail_nonce'); ?>
               <body>
                  <div class="page"><div class="page__content">
                      <div class="page__header">
                          <h1>Send Mail <span class="ico-send"></span></h1>
                      </div>
                      <div class="form">
                       <div class="form__row">
                        <label class="form--label">To</label>
                        <input type="text" class="form--input" name="transmail_to_address" required = "required"/> </div>
                        <div class="form__row">
                            <label class="form--label">Subject</label>
                            <input type="text" class="form--input" name="transmail_subject" required = "required"/> </div>
                            <div class="form__row">
                             <label class="form--label">Content</label>
                             <input type="text" class="form--input" name="transmail_content"/> </div>
                             <div class="form__row form__row-btn"> <input type="submit" class = "btn" name="transmail_send_mail_submit" id="transmail_send_mail_submit" value="<?php _e('Send Mail');?>">
                              
                             </div>
                         </div>
                     </div>
                 </div>
             </body>
         </form>
         <?php
         
     }
     else {
       echo '<div class="error"><p><strong>'.__('Configure Your Account.').'</strong></p></div>'."\n";
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

  $content_type = null;
    // Headers
  $cc = $bcc = $reply_to = array();
  $dynamicFrom = array();
  if ( empty( $headers ) ) {
    $headers = array('');
} else {
   if ( !is_array( $headers ) ) {
            // Explode the headers out, so this function can take both
            // string headers and an array of headers.
    $tempheaders = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
} else {
    $tempheaders = $headers;
}
$headers = array();
        // If it's actually got contents
if ( !empty( $tempheaders ) ) {
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
        switch ( strtolower( $name ) ) {
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
          break;
          default:
          $headers[trim( $name )] = trim( $content );
          break;
      }
  }
}
}
$content_type = apply_filters( 'wp_mail_content_type', $content_type );    
$data = array();
$token = base64_decode(get_option('transmail_send_mail_token'));
$fromAddress = array();
if (!empty($from_name)) {
   $fromAddress['name'] = $from_name;
} else {
  $fromAddress['name'] = get_option('transmail_from_name');
}
if (!empty($dynamicFrom)) 
{
    $dynpos = false;
    $dynpos = strpos($dynamicFrom[0], '<');
    if($dynpos !== false) {
      $dynad = substr($dynamicFrom[0], $dynpos+1, strlen($dynamicFrom[0])-$dynpos-2);
      $dynfrom['address'] = sanitize_email($dynad);
      if($dynpos >0) {
        $dynfrom['name'] = substr($dynamicFrom[0],0,$dynpos-1);
        $fromAddress['name'] = $dynfrom['name'];
      }
      $fromAddress['address'] = $dynfrom['address'];
     }
 
}
else {
    $fromAddress['address'] = get_option('transmail_from_email_id');
   

}

$data['from'] =  $fromAddress;
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
      $toa['address'] = sanitize_email($ad);
      $toa['name'] = substr($t,0,$pos-1);
  } else {
      $toa['address'] = sanitize_email($t);
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
if (!empty($attachments)) {
    if (!is_array($attachments)) {
        $attachments = explode("\n", $attachments);
    }

    $attachmentJSONArr = array();
    $count = 0;

    foreach ($attachments as $attfile) {
        if (file_exists($attfile)) {
            $attachmentupload = array(
                'name' => basename($attfile),
                'mime_type' => mime_content_type($attfile),
                'content' => base64_encode(file_get_contents($attfile))
            );
            $attachmentJSONArr[$count] = $attachmentupload;
            $count = $count + 1;
        } else {
            error_log("Attachment file does not exist: " . $attfile);
        }
    }
    
    $data['attachments'] = $attachmentJSONArr;
}
if( $content_type == 'text/html' || get_option('transmail_content_type') == 'html') {
  $data['htmlbody'] = $message;
} else {
  $data['textbody'] = $message;

} 


$headers1 = array(
   'Authorization' => $token,
   'User-Agent' => 'Zepto_WordPress'
);
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
if(!is_wp_error( $responseSending ))
  update_option('transmail_test_mail_case', $responseSending['body'], false);
  
  $responseBody = wp_remote_retrieve_body($responseSending);
    $responseData = json_decode($responseBody);
    
if($http_code == '200' || $http_code == '201') {
  return true;
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


$mail_data = array(
  'to' => $to,
  'subject' => $subject,
  'message' => $message,
  'headers' => $headers1,
  'attachments' => $attachments
);

do_action( 'wp_mail_failed', new WP_Error( 'wp_mail_failed', $message, $mail_data ) );
return false;

}
}
