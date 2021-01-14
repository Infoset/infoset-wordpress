<?php
/*
Plugin Name: Infoset
Plugin URI: https://wordpress.org/plugins/infoset
Description: Official <a href="https://www.infoset.app">Infoset</a> chat widget for WordPress.
Author: Infoset
Author URI: https://www.infoset.app
Version: 0.1
 */

class IdVerificationCalculator
{
  private $raw_data = array();
  private $secret_key = "";

  public function __construct($data, $secret_key)
  {
    $this->raw_data = $data;
    $this->secret_key = $secret_key;
  }

  public function identityVerificationComponent()
  {
    $secret_key = $this->getSecretKey();
    if (empty($secret_key))
    {
      return $this->emptyIdentityVerificationHashComponent();
    }
    if (array_key_exists("user_id", $this->getRawData()))
    {
      return $this->identityVerificationHashComponent("user_id");
    }
    if (array_key_exists("email", $this->getRawData()))
    {
      return $this->identityVerificationHashComponent("email");
    }
    return $this->emptyIdentityVerificationHashComponent();
  }

  private function emptyIdentityVerificationHashComponent()
  {
    return array();
  }

  private function identityVerificationHashComponent($key)
  {
    $raw_data = $this->getRawData();
    return array("user_hash" => hash_hmac("sha256", $raw_data[$key], $this->getSecretKey()));
  }

  private function getSecretKey()
  {
    return $this->secret_key;
  }
  private function getRawData()
  {
    return $this->raw_data;
  }
}

class InfosetSettingsPage 
{
	private $settings = array();
  private $styles = array();

  public function __construct($settings)
  {
    $this->settings = $settings;
    $this->styles = $this->setStyles($settings);
  }

  public function dismissibleMessage($text)
  {
    return <<<END
  <div id="message" class="updated notice is-dismissible">
    <p>$text</p>
    <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
  </div>
END;
  }

  public function getAuthUrl() {
    return "https://localhost:5001/v1/integration/wordpress/chat?state=".get_site_url()."::".wp_create_nonce("infoset-auth");
  }

  public function htmlUnclosed()
  {
    $settings = $this->getSettings();
    $styles = $this->getStyles();
    $api_key = Escaper::escAttr($settings['api_key']);
    $auth_url = $this->getAuthUrl();
    $dismissable_message = ''; 
    if (isset($_GET['apiKey'])) {
      
      $api_key = Escaper::escAttr($_GET['apiKey']);
      $dismissable_message = $this->dismissibleMessage("We've copied your new Infoset api key below. Click to save changes and then close this window to finish signing up for Infoset.");
    }
    if (isset($_GET['saved'])) {
      $dismissable_message = $this->dismissibleMessage("Your api key has been successfully saved. You can now close this window to finish signing up for Infoset.");
    }
    if (isset($_GET['authenticated'])) {
      $dismissable_message = $this->dismissibleMessage('You\'ve successfully authenticated with Infoset');
    }

    return <<<END

    <link rel="stylesheet" property='stylesheet' href="https://marketing.intercomassets.com/assets/redesign-ead0ee66f7c89e2930e04ac1b7e423494c29e8e681382f41d0b6b8a98b4591e1.css">
    <style>
      #wpcontent {
        background-color: #ffffff;
      }
    </style>

    <div class="wrap">
      $dismissable_message

      <section id="main_content" style="padding-top: 70px;">
        <div class="container">
          <div class="cta">

            <div class="sp__2--lg sp__2--xlg"></div>
            <div id="auth_content" style="$styles[api_key_link_style]">
              <div class="t__h1 c__red">Get started with Infoset</div>

              <div class="cta__desc">
                Chat with visitors to your website in real-time, capture them as leads, and convert them to customers. Add Infoset to your WordPress site.
              </div>

              <div id="get_infoset_btn_container" style="position:relative;margin-top:30px;">
                <a href="$auth_url">
                  <img src="https://static.intercomassets.com/assets/oauth/primary-7edb2ebce84c088063f4b86049747c3a.png" srcset="https://static.intercomassets.com/assets/oauth/primary-7edb2ebce84c088063f4b86049747c3a.png 1x, https://static.intercomassets.com/assets/oauth/primary@2x-0d69ca2141dfdfa0535634610be80994.png 2x, https://static.intercomassets.com/assets/oauth/primary@3x-788ed3c44d63a6aec3927285e920f542.png 3x"/>
                </a>
              </div>
            </div>

            <div class="t__h1 c__red" style="$styles[api_key_copy_title]">Infoset setup</div>
            <div class="t__h1 c__red" style="$styles[api_key_saved_title]">Infoset chat key saved</div>
            <div id="api_key_content" style="$styles[api_key_row_style]">
              <div class="t__h1 c__red" style="$styles[api_key_copy_hidden]">Infoset has been installed</div>

              <div class="cta__desc">
                <div style="$styles[api_key_copy_hidden]">
                  Infoset is ready to go. You can now chat with your existing and potential new customers, send them targeted messages, and get feedback.
                  <br/>
                  <br/>
                  <a class="c__blue" href="https://dashboard.infoset.app/chats" target="_blank">Click here to access your Infoset Chats.</a>
                  <br/>
                  <br/>
                  Need help? <a class="c__blue" href="https://infoset.app/help/en" target="_blank">Visit our blog</a> for best practices, tips, and much more.
                  <br/>
                  <br/>
                </div>

                  <form method="post" action="" name="update_settings">
                    <table class="form-table" align="center" style="margin-top: 16px; width: inherit;">
                      <tbody>
                        <tr>
                          <th scope="row" style="text-align: center; vertical-align: middle;"><label for="infoset_api_key">Chat Api Key</label></th>
                          <td>
                            <input id="infoset_api_key" $styles[api_key_state] name="api_key" type="text" value="$api_key" class="$styles[api_key_class]">
                            <button type="submit" class="btn btn__primary cta__submit" style="$styles[button_submit_style]">Save</button>
                          </td>
                        </tr>
                      </tbody>
                    </table>

END;
  }

  public function htmlClosed()
  {
    $settings = $this->getSettings();
    $styles = $this->getStyles();
    $auth_url = $this->getAuthUrl();
    $api_key = Escaper::escAttr($settings['api_key']);
    $auth_url_identity_verification = "";
    if (!empty($api_key)) {
      $auth_url_identity_verification = $auth_url.'&enable_identity_verification=1';
    }
    return <<<END
                  </form>
                  <div style="$styles[api_key_copy_hidden]">
                    <div style="$styles[app_secret_link_style]">
                      <a class="c__blue" href="$auth_url_identity_verification">Authenticate with your Infoset application to enable Identity Verification</a>
                    </div>
                    <p style="font-size:0.86em">Identity verification helps ensure that chats between you and your users are kept private and that one person cannot impersonate another.<br/>
                    <br/>
                      <a class="c__blue" href="https://infoset.app/help/en/articles/498-enable-identity-verification-in-live-chat" target="_blank">Learn more about Identity Verification</a>
                    </p>
                    <br/>
                    <div style="font-size:0.8em">If the Infoset Chat associated with your Wordpress is incorrect, please <a class="c__blue" href="$auth_url">click here</a> to reconnect with Infoset, to choose a new application.</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
    <script src="https://code.jquery.com/jquery-2.2.0.min.js"></script>
END;
  }

  public function html()
  {
    return $this->htmlUnclosed() . $this->htmlClosed();
  }

  public function setStyles($settings) {
    $styles = array();
    $api_key = Escaper::escAttr($settings['api_key']);
    $identity_verification = Escaper::escAttr($settings['identity_verification']);


    // Use Case : Identity Verification enabled : checkbox checked and disabled
    if($identity_verification) {
      $styles['identity_verification_state'] = 'checked disabled';
    } else {
      $styles['identity_verification_state'] = '';
    }

    // Use Case : app_id here but Identity Verification disabled
    if (!empty($api_key)) {
      $styles['app_secret_row_style'] = 'display: none;';
      $styles['app_secret_link_style'] = '';
    } else {
      $styles['app_secret_row_style'] = '';
      $styles['app_secret_link_style'] = 'display: none;';
    }

    // Copying apiKey from Infoset Setup Guide for validation
    if (isset($_GET['apiKey'])) {
        $api_key = Escaper::escAttr($_GET['apiKey']);
        $styles['api_key_state'] = 'readonly';
        $styles['api_key_class'] = "cta__email";
        $styles['button_submit_style'] = '';
        $styles['api_key_copy_hidden'] = 'display: none;';
        $styles['api_key_copy_title'] = '';
        $styles['identity_verification_state'] = 'disabled'; # Prevent from sending POST data about identity_verification when using app_id form
    } else {
      $styles['api_key_class'] = "";
      $styles['button_submit_style'] = 'display: none;';
      $styles['api_key_copy_title'] = 'display: none;';
      $styles['api_key_state'] = 'disabled'; # Prevent from sending POST data about app_id when using identity_verification form
      $styles['api_key_copy_hidden'] = '';
    }

    //Use Case App_id successfully copied
    if (isset($_GET['saved'])) {
      $styles['api_key_copy_hidden'] = 'display: none;';
      $styles['api_key_saved_title'] = '';
    } else {
      $styles['api_key_saved_title'] = 'display: none;';
    }

    // Display 'connect with intercom' button if no app_id provided (copied from setup guide or from Oauth)
    if (empty($api_key)) {
      $styles['api_key_row_style'] = 'display: none;';
      $styles['api_key_link_style'] = '';
    } else {
      $styles['api_key_row_style'] = '';
      $styles['api_key_link_style'] = 'display: none;';
    }
    return $styles;
  }

  private function getSettings()
  {
    return $this->settings;
  }

  private function getStyles()
  {
    return $this->styles;
  }

}

class InfosetSnippet {
	private $snippet_settings = "";

  public function __construct($snippet_settings)
  {
    $this->snippet_settings = $snippet_settings;
  }
  public function html()
  {
    return $this->shutdown_on_logout() . $this->source();
  }

  private function shutdown_on_logout()
  {
    return <<<HTML
<script data-cfasync="false">
  document.onreadystatechange = function () {
    if (document.readyState == "complete") {
      var logout_link = document.querySelectorAll('a[href*="wp-login.php?action=logout"]');
      if (logout_link) {
        for(var i=0; i < logout_link.length; i++) {
          logout_link[i].addEventListener( "click", function() {
            InfosetChat('shutdown');
          });
        }
      }
    }
  };
</script>

HTML;
  }

  private function source()
  {
  	$snippet_json = $this->snippet_settings->json();
    $api_key = $this->snippet_settings->apiKey();
    echo $api_key;

    return <<<HTML
<!-- BEGIN INFOSET CHAT WIDGET -->
<script type='text/javascript'>!function(){var t=window;if('function'!=typeof t.InfosetChat){var n=document,e=function(){e.c(arguments)};e.q=[],e.c=function(t){e.q.push(t)},t.InfosetChat=e;function a(){var t=n.createElement('script');t.type='text/javascript',t.async=!0,t.src='https://cdn.infoset.app/chat/icw.js';var e=n.getElementsByTagName('script')[0];e.parentNode.insertBefore(t,e)}t.attachEvent?t.attachEvent('onload',a):t.addEventListener('load',a,!1)}}();
InfosetChat('boot',{widget:{apiKey: '$api_key'}});
</script>
<!-- END INFOSET CHAT WIDGET -->
HTML;
  }
}

class InfosetSnippetSettings
{
	private $raw_data = array();
  private $api_key = NULL;
  private $wordpress_user = NULL;

  public function __construct($raw_data, $api_key = NULL, $wordpress_user = NULL)
  {
    $this->raw_data = $this->validateRawData($raw_data);
    $this->wordpress_user = $wordpress_user;
  }

  public function json()
  {
    return json_encode(apply_filters("infoset_settings", $this->getRawData()));
  }

  public function apiKey()
  {
    $raw_data = $this->getRawData();
    return $raw_data["api_key"];
  }

  private function getRawData()
  {
    $user = new InfosetUser($this->wordpress_user, $this->raw_data);
    $settings = $user->buildSettings();
    // $identityVerificationCalculator = new IdVerificationCalculator($settings, $this->secret);
    $result = $settings; //array_merge($settings, $identityVerificationCalculator->identityVerificationComponent());
    return $result;
  }

  private function validateRawData($raw_data)
  {
    if (!array_key_exists("api_key", $raw_data)) {
      throw new Exception("api_key is required");
    }
    return $raw_data;
  }
}

class Escaper
{
	public static function escAttr($value)
  {
    if (function_exists('esc_attr')) {
      return esc_attr($value);
    }
  }

  public static function escJS($value)
  {
    if (function_exists('esc_js')) {
      return esc_js($value);
    }
  }
}

class InfosetUser
{
	private $wordpress_user = NULL;
  private $settings = array();

  public function __construct($wordpress_user, $settings)
  {
    $this->wordpress_user = $wordpress_user;
    $this->settings = $settings;
  }

  public function buildSettings()
  {
    if (empty($this->wordpress_user))
    {
      return $this->settings;
    }
    if (!empty($this->wordpress_user->user_email))
    {
      $this->settings["email"] = Escaper::escJS($this->wordpress_user->user_email);
    }
    if (!empty($this->wordpress_user->display_name))
    {
      $this->settings["name"] = Escaper::escJS($this->wordpress_user->display_name);
    }
    return $this->settings;
  }
}

class IValidator
{
	private $inputs = array();
  private $validation;

  public function __construct($inputs, $validation)
  {
    $this->input = $inputs;
    $this->validation = $validation;
  }

  public function validApiKey()
  {
    return $this->validate($this->input["api_key"]);
  }

  private function validate($x)
  {
    return call_user_func($this->validation, $x);
  }
}

if (!defined('ABSPATH')) exit;

function add_infoset_snippet()
{
  $options = get_option('infoset');
  $snippet_settings = new InfosetSnippetSettings(
    array("api_key" => Escaper::escJS($options['api_key'])),
    wp_get_current_user()
  );
  $snippet = new InfosetSnippet($snippet_settings);
  echo $snippet->html();
}

function add_infoset_settings_page()
{
  add_options_page(
    'Infoset Settings',
    'Infoset',
    'manage_options',
    'infoset',
    'render_infoset_options_page'
  );
}

function render_infoset_options_page()
{
  if (!current_user_can('manage_options'))
  {
    wp_die('You are not authorized to access Infoset settings');
  }
  $options = get_option('infoset');
  $settings_page = new InfosetSettingsPage(array("api_key" => $options['api_key']));
  echo $settings_page->htmlUnclosed();
  wp_nonce_field('infoset-update');
  echo $settings_page->htmlClosed();
}

function infoset_settings() {
  register_setting('infoset', 'infoset');
  if (isset($_GET['state']) && wp_verify_nonce($_GET[ 'state'], "infoset-auth") && current_user_can('manage_options') && isset($_GET['api_key']) ) {
    $validator = new IValidator($_GET, function($x) { return wp_kses(trim($x), array()); });
    update_option("infoset", array("api_key" => $validator->validApiKey()));
    $redirect_to = 'options-general.php?page=infoset&authenticated=1';
    wp_safe_redirect(admin_url($redirect_to));
  }
  if (current_user_can('manage_options') && isset($_POST['api_key']) && isset($_POST[ '_wpnonce']) && wp_verify_nonce($_POST[ '_wpnonce'], 'infoset-update')) {
      $options = array();
      $options["api_key"] = Escaper::escAttr($_POST['api_key']);
      update_option("infoset", $options);
      wp_safe_redirect(admin_url('options-general.php?page=infoset&saved=1'));
  }
}

add_action('wp_footer', 'add_infoset_snippet');
add_action('admin_menu', 'add_infoset_settings_page');
add_action('network_admin_menu', 'add_infoset_settings_page');
add_action('admin_init', 'infoset_settings');


