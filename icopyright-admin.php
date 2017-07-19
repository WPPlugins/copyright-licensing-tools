<?php

include (ICOPYRIGHT_PLUGIN_DIR . '/icopyright-admin-functions.php');
include (ICOPYRIGHT_PLUGIN_DIR . '/settings-fields-callback.php');
include (ICOPYRIGHT_PLUGIN_DIR . '/icopyright-republish-page.php');

//
// Add the iCopyright options page
//
add_action('admin_menu', 'icopyright_admin_menu');
function icopyright_admin_menu() {
  add_options_page('iCopyright', 'iCopyright', 'manage_options', 'copyright-licensing-tools', 'icopyright_options_page');
}

function icopyright_options_page() {
	
  //
  // Process the TOU Form
  //
  $touResult = icopyright_process_tou();
  if ($touResult != NULL && $touResult == 'SUCCESS') {
    icopyright_display_publication_welcome();
  }

  $registrationResult = icopyright_post_registration_form();
  if ($registrationResult != NULL && $registrationResult == 'SUCCESS') {
    icopyright_display_publication_welcome();
  }

  //
  // Check connectivity
  //
  if ($_GET['page'] != null && $_GET['page'] != 'repubhub-republish')  {
  	icopyright_check_connectivity();
  }

  //
  // Add JS and CSS
  //

  wp_enqueue_media();
  wp_enqueue_style('icopyright-admin-css', plugins_url('css/style.css', __FILE__), array(), ICOPYRIGHT_VERSION);  // Update the version when the style changes.  Refreshes cache.
  wp_enqueue_style('icopyright-admin-css-2', "http://cdnjs.cloudflare.com/ajax/libs/jquery.colorbox/1.4.33/example1/colorbox.css", array());
  wp_enqueue_script('icopyright-admin-js', plugins_url('js/main.js', __FILE__), array(), ICOPYRIGHT_VERSION);
  wp_enqueue_script("icopyright-admin-js-2", "http://cdnjs.cloudflare.com/ajax/libs/jquery.colorbox/1.4.33/jquery.colorbox-min.js");
  
  wp_localize_script( 'icopyright-admin-js', 'icx_plugin_url', array('url' => ICOPYRIGHT_PLUGIN_URL));

  $tou = get_option('icopyright_tou');
  if (($touResult != NULL && $touResult == 'FAILURE') || ($registrationResult != NULL && $registrationResult == 'FAILURE') || !empty($_GET['show-registration-form'])) {
    //
    // Show register form
    //
    icopyright_create_register_form();
  } else {
    if (empty($tou)) {
      //
      // Show TOU on first view
      //
      icopyright_create_tou_form();
    } else {
    icopyright_admin_check_price_optimizer();
      //
      // Show options
      //
      ?>
    <div class="wrap">
      <h2>iCopyright Settings</h2>
      <div style="width: 1150px;">
	      <div class="intro-video" >
	        <a href="https://www.youtube.com/embed/V7g6E4OZjXQ?autoplay=1&vq=hd720" target="_blank" id="icopyright_wp_republishing_video" title="iCopyright Republishing Articles">
	          <img src="/wp-content/plugins/copyright-licensing-tools/images/Tutorial_1.png" style="border: 1px solid black"/>
	          <img src="/wp-content/plugins/copyright-licensing-tools/images/btn.play.png" style="position:absolute;left:157px;top:76px;opacity:.5;width:45px"/>
	        </a>
	      </div>
	      
	      <div class="intro-video">
	        <a href="https://www.youtube.com/embed/ZedjWrtVJ58?autoplay=1&vq=hd720" target="_blank" id="icopyright_wp_syndicating_video" title="iCopyright Syndicating Articles">
	          <img src="/wp-content/plugins/copyright-licensing-tools/images/Tutorial_2.png" style="border: 1px solid black"/>
	          <img src="/wp-content/plugins/copyright-licensing-tools/images/btn.play.png" style="position:absolute;left:157px;top:76px;opacity:.5;width:45px"/>
	        </a>
	      </div>
	      
	      <div class="intro-video">
	        <a href="https://www.youtube.com/embed/MWSuvjkM1NI?autoplay=1&vq=hd720" target="_blank" id="icopyright_wp_revenue_video" title="iCopyright Revenue Allocation">
	          <img src="/wp-content/plugins/copyright-licensing-tools/images/Tutorial_3.png" style="border: 1px solid black; width:352px; height:194px;"/>
	          <img src="/wp-content/plugins/copyright-licensing-tools/images/btn.play.png" style="position:absolute;left:157px;top:76px;opacity:.5;width:45px"/>
	        </a>
	      </div>  
	    </div>      
      <div style="clear: both;"></div>            
      <form action="options.php" method="POST">
        <?php
        settings_fields('icopyright-settings-group');
        do_settings_sections('copyright-licensing-tools');
        submit_button();
        ?>
      </form>
    </div>
    <?php
      $pubId = get_option('icopyright_pub_id');
      ?>
    <?php if (!empty($pubId)) { ?>
      <table class="form-table">
        <tbody>
        <tr valign="top">
          <th scope="row">
            <h3>Enter My<br/> Conductor Console</h3>
          </th>
          <td valign="top">
            <div id="enter-conductor-console">
              <?php print icopyright_graphical_link_to_conductor('freePostReport.act', 'view-reports.jpg', 'icx-view-reports'); ?>
              <?php print icopyright_graphical_link_to_conductor('serviceGroups.act', 'modify-services-and-prices.jpg', 'icx-modify-services-prices'); ?>
              <?php print icopyright_graphical_link_to_conductor('pricingOptimizer.act', 'enter-price-optimizer.jpg', 'icx-enter-price-optimizer'); ?>
            </div>
            <div style="clear:both;"></div>
          </td>
        </tr>
        </tbody>
      </table>
      <?php } ?>
    <?php

      //
      // Add various id's used by JavaScript
      //
      $siteName = get_option('icopyright_publication');
      if ($siteName == NULL || empty($siteName)) {
      	$siteName = get_option('icopyright_site_name'); //legacy
      }
      ?>
    <div id="pub_id" style="display:none;"><?php print get_option('icopyright_pub_id') ?></div>
    <div id="site_name" style="display:none;"><?php echo(empty($siteName) ? get_bloginfo() : $siteName); ?></div>
    <div id="icopyright_server" style="display:none;"><?php print icopyright_get_server() ?></div>
    <?php
    }
  }
}

//
// Section callbacks
//
function account_settings_section_callback() {
  $address = get_option('icopyright_address_line1');
  if (!empty($address)) {
?>
    <input type="button" id="toggle_account_setting" value="Show Address" style="cursor:pointer; margin-top: 1em;">
<?php
  } else {
?>
    <h3>Send Revenue Checks To:</h3>
    <div style="float:left;max-width: 900px;">
<?php
  }
}

function deployment_mechanism_section_callback() {
  $address = get_option('icopyright_address_line1');
  if (!empty($address)) {
  ?>
<div style="float:left;max-width: 900px;">
  <?php
  }
  ?>
<p>
    For assistance, please email <a
    href="mailto:wordpress@icopyright.com">wordpress@icopyright.com</a> or get <a
    href="http://www.icopyright.com/wordpress" target="_blank">help</a>.
</p>
  <?php
  if (empty($address)) {
  ?>
</div>
  <?php
  }
}

function toolbar_appearance_section_callback() {
  $address = get_option('icopyright_address_line1');
  if (!empty($address)) {
    ?>
    </div>
    <div style="clear: both;"></div>
  <?php
  }
}

function display_section_callback() {
}

function service_section_callback() {
}

function icopyright_ad_network_section_callback() {
	echo '<p style="font-size: 9pt;">
When your content is licensed for republication or reuse for free with ads inserted, iCopyright will use the information you provide below for your share of the ad inventory.&nbsp;&nbsp;
(This does not affect how advertising is placed on your site.)&nbsp;&nbsp;If you prefer to use a different ad network, contact 
<a href="mailto:publishers@icopyright.com?Subject=Request%20for%20a%20different%20ad%20network" target="_top">publishers@icopyright.com</a>. 
  If you currently do not have an ad system or are not subscribing to an ad network,
 <a target="_blank" href="https://www.google.com/adsense/login/"> click here</a> for instructions on Google AdSense or <a href="https://www.adblade.com/registration/advertiser" target="_blank">here</a> for Adblade.
</p>';
	echo '<select id="icopyright_ad_network_selector" name="icopyright_ad_network_selector" style="font-size:12px; height: 25px; margin-bottom: 10px;">';
	echo '<option value="none">Please select ad network</option>';
	echo '<option value="ADBLADE">Adblade</option>';
	echo '<option value="GOOGLE">Google AdSense</option>';
	echo '</select>';
}



function advanced_section_callback() {
  ?>
<input type="button" id="toggle_advance_setting" value="Show Advanced Settings" style="cursor:pointer; display: block; margin-top: 1em;">
<?php
}

//
// Add the settings.
//
function icopyright_admin_check_price_optimizer() {
$icopyright_pricing_optimizer_opt_in = get_option('icopyright_pricing_optimizer_opt_in');
  if ($icopyright_pricing_optimizer_opt_in != FALSE) {
    add_settings_field('icopyright_pricing_optimizer_opt_in', 'Price Optimizer', 'pricing_optimizer_opt_in_field_callback', 'copyright-licensing-tools', 'service-settings');
    register_setting('icopyright-settings-group', 'icopyright_pricing_optimizer_opt_in');

    add_settings_field('icopyright_pricing_optimizer_apply_automatically', '', 'pricing_optimizer_apply_automatically_field_callback', 'copyright-licensing-tools', 'service-settings');
    register_setting('icopyright-settings-group', 'icopyright_pricing_optimizer_apply_automatically');
  }
}

add_action('admin_init', 'icopyright_admin_init');
function icopyright_admin_init() {

  //
  // Update settings if we're in iCopyright settings page
  //
  $pageParam = $_GET['page'];
  if ($pageParam != null && $pageParam == 'copyright-licensing-tools') {
  	icopyright_update_settings();
  }

  $address = get_option('icopyright_address_line1');
  if (empty($address)) {
    add_account_settings_section();
  }

  add_settings_section('deployment-mechanism', 'The Basics:', 'deployment_mechanism_section_callback', 'copyright-licensing-tools');

  add_settings_field('icopyright_display', 'Toolbar Placement', 'display_field_callback', 'copyright-licensing-tools', 'deployment-mechanism');
  register_setting('icopyright-settings-group', 'icopyright_display');
  
  add_settings_field('icopyright_searchable', 'Searchable', 'searchable_field_callback', 'copyright-licensing-tools', 'deployment-mechanism');
  register_setting('icopyright-settings-group', 'icopyright_searchable');
  
  add_settings_field('icopyright_use_category_filter', 'Excludes', 'use_category_filter_field_callback', 'copyright-licensing-tools', 'deployment-mechanism');
  //register_setting('icopyright-settings-group', 'icopyright_use_category_filter');
  
  add_settings_field('icopyright_site_description', 'Site Description', 'site_description_field_callback', 'copyright-licensing-tools', 'deployment-mechanism');
  register_setting('icopyright-settings-group', 'icopyright_site_description');

  add_settings_field('icopyright_site_logo', 'Site Logo', 'site_logo_field_callback', 'copyright-licensing-tools', 'deployment-mechanism');
  register_setting('icopyright-settings-group', 'icopyright_site_logo');  
  
  add_settings_section('toolbar-appearance', 'Toolbar Appearance:', 'toolbar_appearance_section_callback', 'copyright-licensing-tools');

  add_settings_field('icopyright_tools', 'Format', 'tools_field_callback', 'copyright-licensing-tools', 'toolbar-appearance');
  register_setting('icopyright-settings-group', 'icopyright_tools');

  add_settings_field('icopyright_theme', 'Theme', 'theme_field_callback', 'copyright-licensing-tools', 'toolbar-appearance');
  register_setting('icopyright-settings-group', 'icopyright_theme');

  add_settings_field('icopyright_background', 'Background', 'background_field_callback', 'copyright-licensing-tools', 'toolbar-appearance');
  register_setting('icopyright-settings-group', 'icopyright_background');

  add_settings_field('icopyright_align', 'Align', 'align_field_callback', 'copyright-licensing-tools', 'toolbar-appearance');
  register_setting('icopyright-settings-group', 'icopyright_align');

  add_settings_field('copyright_notice_preview', 'Preview of Interactive Copyright Notice (displayed below articles)', 'copyright_notice_preview_callback', 'copyright-licensing-tools', 'toolbar-appearance');
  register_setting('icopyright-settings-group', 'copyright_notice_preview');

  //add_settings_section('toolbar-display', 'Tools Displayed on Pages With:', 'display_section_callback', 'copyright-licensing-tools');

  add_settings_field('icopyright_show', 'Display style', 'show_preview_callback', 'copyright-licensing-tools', 'toolbar-appearance');
  register_setting('icopyright-settings-group', 'icopyright_show');

  add_settings_field('icopyright_display_on_pages', 'Pages', 'display_on_pages_field_callback', 'copyright-licensing-tools', 'toolbar-appearance');
  register_setting('icopyright-settings-group', 'icopyright_display_on_pages');


  add_settings_section('service-settings', 'Service Settings:', 'service_section_callback', 'copyright-licensing-tools');

  add_settings_field('icopyright_ez_excerpt', 'EZ Excerpt', 'ez_excerpt_field_callback', 'copyright-licensing-tools', 'service-settings');
  register_setting('icopyright-settings-group', 'icopyright_ez_excerpt');

  $icopyright_pricing_optimizer_opt_in = get_option('icopyright_pricing_optimizer_opt_in');
  if ($icopyright_pricing_optimizer_opt_in != FALSE) {
    add_settings_field('icopyright_pricing_optimizer_opt_in', 'Price Optimizer', 'pricing_optimizer_opt_in_field_callback', 'copyright-licensing-tools', 'service-settings');
    register_setting('icopyright-settings-group', 'icopyright_pricing_optimizer_opt_in');

    add_settings_field('icopyright_pricing_optimizer_apply_automatically', '', 'pricing_optimizer_apply_automatically_field_callback', 'copyright-licensing-tools', 'service-settings');
    register_setting('icopyright-settings-group', 'icopyright_pricing_optimizer_apply_automatically');
  }

  add_settings_field('icopyright_share', 'Share services', 'share_field_callback', 'copyright-licensing-tools', 'service-settings');
  register_setting('icopyright-settings-group', 'icopyright_share');

  if (!empty($address)) {
    add_account_settings_section();
  }
  
  // Begin ad settings
  
  add_settings_section('icopyright_ad_network', 'Ad Network', 'icopyright_ad_network_section_callback', 'copyright-licensing-tools');
  
  add_settings_field('icopyright_adblade_explanation', '', 'icopyright_ad_unit_callback', 'copyright-licensing-tools', 'icopyright_ad_network', array('id' => 'icopyright_adblade_explanation', 'explanation' => 'For each ad unit, enter the value for <i>data-cid</i> from your Adblade ad code.  Example: 12345-6789054321. No quotation marks.'));
  add_settings_field('icopyright_google_explanation', '', 'icopyright_ad_unit_callback', 'copyright-licensing-tools', 'icopyright_ad_network', array('id' => 'icopyright_google_explanation', 'explanation' => 'In the ad client field, input the value for <i>data-ad-client</i> or <i>google-ad-client</i> from your Google AdSense code.  Example: ca-pub-123456789.<br/><br/>Then for each ad unit field, enter the value for <i>data-ad-slot</i> or <i>google-ad-slot</i> from your Google AdSense code for that unit.  Example: 5678934.  No quotation marks.'));
  
  add_settings_field('icopyright_adblade_leaderboard', 'Leaderboard (728x90)', 'icopyright_ad_unit_callback', 'copyright-licensing-tools', 'icopyright_ad_network', array('id' => 'icopyright_adblade_leaderboard', 'type' => 'adblade', 'size' => 'LEADERBOARD'));
  register_setting('icopyright-settings-group', 'icopyright_adblade_leaderboard');
  
  add_settings_field('icopyright_adblade_wideskyscraper', 'Wide Skyscraper (160x600)', 'icopyright_ad_unit_callback', 'copyright-licensing-tools', 'icopyright_ad_network', array('id' => 'icopyright_adblade_wideskyscraper', 'type' => 'adblade', 'size' => 'WIDE_SKYSCRAPER'));
  register_setting('icopyright-settings-group', 'icopyright_adblade_wideskyscraper');
  
  add_settings_field('icopyright_adblade_fullbanner', 'Below article Dynazone', 'icopyright_ad_unit_callback', 'copyright-licensing-tools', 'icopyright_ad_network', array('id' => 'icopyright_adblade_fullbanner', 'type' => 'adblade', 'size' => 'FULL_BANNER'));
  register_setting('icopyright-settings-group', 'icopyright_adblade_fullbanner');
  
  add_settings_field('icopyright_adblade_mediumrectangle1', 'Medium Rectangle 1 (300x250)', 'icopyright_ad_unit_callback', 'copyright-licensing-tools', 'icopyright_ad_network', array('id' => 'icopyright_adblade_mediumrectangle1', 'type' => 'adblade', 'size' => 'MEDIUM_RECTANGLE_1'));
  register_setting('icopyright-settings-group', 'icopyright_adblade_mediumrectangle1');
  
  add_settings_field('icopyright_adblade_mediumrectangle2', 'Medium Rectangle 2 (300x250)', 'icopyright_ad_unit_callback', 'copyright-licensing-tools', 'icopyright_ad_network', array('id' => 'icopyright_adblade_mediumrectangle2', 'type' => 'adblade', 'size' => 'MEDIUM_RECTANGLE_2'));
  register_setting('icopyright-settings-group', 'icopyright_adblade_mediumrectangle2');
  

  add_settings_field('icopyright_google_client', 'Ad Client', 'icopyright_ad_unit_callback', 'copyright-licensing-tools', 'icopyright_ad_network', array('id' => 'icopyright_google_client'));
  register_setting('icopyright-settings-group', 'icopyright_google_client');
    
  add_settings_field('icopyright_google_leaderboard', 'Leaderboard (728x90)', 'icopyright_ad_unit_callback', 'copyright-licensing-tools', 'icopyright_ad_network', array('id' => 'icopyright_google_leaderboard', 'type' => 'google', 'size' => 'LEADERBOARD'));
  register_setting('icopyright-settings-group', 'icopyright_google_leaderboard');
  
  add_settings_field('icopyright_google_wideskyscraper', 'Wide Skyscraper (160x600)', 'icopyright_ad_unit_callback', 'copyright-licensing-tools', 'icopyright_ad_network', array('id' => 'icopyright_google_wideskyscraper', 'type' => 'google', 'size' => 'WIDE_SKYSCRAPER'));
  register_setting('icopyright-settings-group', 'icopyright_google_wideskyscraper');
  
  add_settings_field('icopyright_google_fullbanner', 'Full Banner (468 x 60) or bottom ad (468 x 200 max height)', 'icopyright_ad_unit_callback', 'copyright-licensing-tools', 'icopyright_ad_network', array('id' => 'icopyright_google_fullbanner', 'type' => 'google', 'size' => 'FULL_BANNER'));
  register_setting('icopyright-settings-group', 'icopyright_google_fullbanner');
  
  add_settings_field('icopyright_google_mediumrectangle1', 'Medium Rectangle 1 (300x250)', 'icopyright_ad_unit_callback', 'copyright-licensing-tools', 'icopyright_ad_network', array('id' => 'icopyright_google_mediumrectangle1', 'type' => 'google', 'size' => 'MEDIUM_RECTANGLE_1'));
  register_setting('icopyright-settings-group', 'icopyright_google_mediumrectangle1');
  
  add_settings_field('icopyright_google_mediumrectangle2', 'Medium Rectangle 2 (300x250)', 'icopyright_ad_unit_callback', 'copyright-licensing-tools', 'icopyright_ad_network', array('id' => 'icopyright_google_mediumrectangle2', 'type' => 'google', 'size' => 'MEDIUM_RECTANGLE_2'));
  register_setting('icopyright-settings-group', 'icopyright_google_mediumrectangle2');

  // End ad settings
  
  add_settings_section('advanced-settings', '', 'advanced_section_callback', 'copyright-licensing-tools');

  add_settings_field('icopyright_pub_id', 'Publication ID', 'pub_id_field_callback', 'copyright-licensing-tools', 'advanced-settings');
  register_setting('icopyright-settings-group', 'icopyright_pub_id');

  add_settings_field('icopyright_conductor_email', 'Email Address', 'conductor_email_field_callback', 'copyright-licensing-tools', 'advanced-settings');
  register_setting('icopyright-settings-group', 'icopyright_conductor_email');

  add_settings_field('icopyright_conductor_password', 'Password', 'conductor_password_field_callback', 'copyright-licensing-tools', 'advanced-settings');
  register_setting('icopyright-settings-group', 'icopyright_conductor_password');

  add_settings_field('icopyright_feed_url', 'Feed URL', 'feed_url_field_callback', 'copyright-licensing-tools', 'advanced-settings');
  register_setting('icopyright-settings-group', 'icopyright_feed_url', 'icopyright_post_settings');

  add_settings_field('icopyright_show_multiple', '', 'show_multiple_callback', 'copyright-licensing-tools', '');
  register_setting('icopyright-settings-group', 'icopyright_show_multiple');
}

function add_account_settings_section() {
  add_settings_section('account-settings', '', 'account_settings_section_callback', 'copyright-licensing-tools');

  add_settings_field('icopyright_fname', 'First Name', 'first_name_field_callback', 'copyright-licensing-tools', 'account-settings');
  register_setting('icopyright-settings-group', 'icopyright_fname');

  add_settings_field('icopyright_lname', 'Last Name', 'last_name_field_callback', 'copyright-licensing-tools', 'account-settings');
  register_setting('icopyright-settings-group', 'icopyright_lname');

  add_settings_field('icopyright_publication', 'Publication', 'site_name_field_callback', 'copyright-licensing-tools', 'account-settings');
  register_setting('icopyright-settings-group', 'icopyright_publication');

  add_settings_field('icopyright_site_url', 'Site URL', 'site_url_field_callback', 'copyright-licensing-tools', 'account-settings');
  register_setting('icopyright-settings-group', 'icopyright_site_url');

  add_settings_field('icopyright_address_line1', 'Address', 'address_line1_field_callback', 'copyright-licensing-tools', 'account-settings');
  register_setting('icopyright-settings-group', 'icopyright_address_line1');

  add_settings_field('icopyright_address_line2', '', 'address_line2_field_callback', 'copyright-licensing-tools', 'account-settings');
  register_setting('icopyright-settings-group', 'icopyright_address_line2');

  add_settings_field('icopyright_address_line3', '', 'address_line3_field_callback', 'copyright-licensing-tools', 'account-settings');
  register_setting('icopyright-settings-group', 'icopyright_address_line3');

  add_settings_field('icopyright_address_city', 'City', 'address_city_field_callback', 'copyright-licensing-tools', 'account-settings');
  register_setting('icopyright-settings-group', 'icopyright_address_city');

  add_settings_field('icopyright_address_state', 'State', 'address_state_field_callback', 'copyright-licensing-tools', 'account-settings');
  register_setting('icopyright-settings-group', 'icopyright_address_state');

  add_settings_field('icopyright_address_country', 'Country', 'address_country_field_callback', 'copyright-licensing-tools', 'account-settings');
  register_setting('icopyright-settings-group', 'icopyright_address_country');

  add_settings_field('icopyright_address_postal', 'Postal Code', 'address_postal_field_callback', 'copyright-licensing-tools', 'account-settings');
  register_setting('icopyright-settings-group', 'icopyright_address_postal');

  add_settings_field('icopyright_address_phone', 'Phone', 'address_phone_field_callback', 'copyright-licensing-tools', 'account-settings');
  register_setting('icopyright-settings-group', 'icopyright_address_phone');
}

//
// Display validation errors
//
function icopyright_admin_notices() {

  $wp_settings_errors = get_settings_errors('icopyright');
  if (sizeof($wp_settings_errors) > 0) {
  	$settingsMessages = FALSE;
    echo("<div class=\"updated settings-error\">");
    foreach ($wp_settings_errors as $error) {
    	if ($settingsMessage == FALSE) {
      	echo("<p>" . $error['message'] . "</p>");
    	}
      
      if ($settingsMessage == FALSE && $error['code'] && $error['code'] == 'settings-15') {
      	$settingsMessage = TRUE;
      }      
    }
    echo("</div>");
  }
}

add_action('admin_notices', 'icopyright_admin_notices');

?>