<?php
//
// Field callbacks
//
function first_name_field_callback() {
  $fname = get_option('icopyright_fname');
  icopyright_make_account_row(150, 'icopyright_fname', NULL, ($fname == 'Anonymous' ? '' : $fname));
}

function last_name_field_callback() {
  $lname = get_option('icopyright_lname');
  icopyright_make_account_row(150, 'icopyright_lname', NULL, ($lname == 'User' ? '' : $lname));
}

function site_name_field_callback() {
	$site_name = get_option('icopyright_publication');
	if ($site_name == NULL || empty($site_name)) {
		$site_name = get_option('icopyright_site_name');
	}
  icopyright_make_account_row(350, 'icopyright_publication', NULL, $site_name);
}

function site_url_field_callback() {
  icopyright_make_account_row(350, 'icopyright_site_url');
}

function address_line1_field_callback() {
  icopyright_make_account_row(200, 'icopyright_address_line1');
}

function address_line2_field_callback() {
  icopyright_make_account_row(200, 'icopyright_address_line2');
}

function address_line3_field_callback() {
  icopyright_make_account_row(200, 'icopyright_address_line3');
}

function address_city_field_callback() {
  icopyright_make_account_row(200, 'icopyright_address_city');
}

function address_state_field_callback() {
  icopyright_make_account_row(50, 'icopyright_address_state');
}

function address_country_field_callback() {
  $field = 'icopyright_address_country';
  $current_value = sanitize_text_field(stripslashes(get_option($field)));
  if(empty($current_value)) $current_value = 'US';
  $countries = array(
    "AF" => "Afghanistan",
    "AX" => "��land Islands",
    "AL" => "Albania",
    "DZ" => "Algeria",
    "AS" => "American Samoa",
    "AD" => "Andorra",
    "AO" => "Angola",
    "AI" => "Anguilla",
    "AQ" => "Antarctica",
    "AG" => "Antigua and Barbuda",
    "AR" => "Argentina",
    "AM" => "Armenia",
    "AW" => "Aruba",
    "AU" => "Australia",
    "AT" => "Austria",
    "AZ" => "Azerbaijan",
    "BS" => "Bahamas",
    "BH" => "Bahrain",
    "BD" => "Bangladesh",
    "BB" => "Barbados",
    "BY" => "Belarus",
    "BE" => "Belgium",
    "BZ" => "Belize",
    "BJ" => "Benin",
    "BM" => "Bermuda",
    "BT" => "Bhutan",
    "BO" => "Bolivia",
    "BA" => "Bosnia and Herzegovina",
    "BW" => "Botswana",
    "BV" => "Bouvet Island",
    "BR" => "Brazil",
    "IO" => "British Indian Ocean Territory",
    "BN" => "Brunei Darussalam",
    "BG" => "Bulgaria",
    "BF" => "Burkina Faso",
    "BI" => "Burundi",
    "KH" => "Cambodia",
    "CM" => "Cameroon",
    "CA" => "Canada",
    "CV" => "Cape Verde",
    "KY" => "Cayman Islands",
    "CF" => "Central African Republic",
    "TD" => "Chad",
    "CL" => "Chile",
    "CN" => "China",
    "CX" => "Christmas Island",
    "CC" => "Cocos (Keeling) Islands",
    "CO" => "Colombia",
    "KM" => "Comoros",
    "CG" => "Congo",
    "CD" => "Congo, The Democratic Republic of The",
    "CK" => "Cook Islands",
    "CR" => "Costa Rica",
    "CI" => "Cote D'ivoire",
    "HR" => "Croatia",
    "CU" => "Cuba",
    "CY" => "Cyprus",
    "CZ" => "Czech Republic",
    "DK" => "Denmark",
    "DJ" => "Djibouti",
    "DM" => "Dominica",
    "DO" => "Dominican Republic",
    "EC" => "Ecuador",
    "EG" => "Egypt",
    "SV" => "El Salvador",
    "GQ" => "Equatorial Guinea",
    "ER" => "Eritrea",
    "EE" => "Estonia",
    "ET" => "Ethiopia",
    "FK" => "Falkland Islands (Malvinas)",
    "FO" => "Faroe Islands",
    "FJ" => "Fiji",
    "FI" => "Finland",
    "FR" => "France",
    "GF" => "French Guiana",
    "PF" => "French Polynesia",
    "TF" => "French Southern Territories",
    "GA" => "Gabon",
    "GM" => "Gambia",
    "GE" => "Georgia",
    "DE" => "Germany",
    "GH" => "Ghana",
    "GI" => "Gibraltar",
    "GR" => "Greece",
    "GL" => "Greenland",
    "GD" => "Grenada",
    "GP" => "Guadeloupe",
    "GU" => "Guam",
    "GT" => "Guatemala",
    "GG" => "Guernsey",
    "GN" => "Guinea",
    "GW" => "Guinea-bissau",
    "GY" => "Guyana",
    "HT" => "Haiti",
    "HM" => "Heard Island and Mcdonald Islands",
    "VA" => "Holy See (Vatican City State)",
    "HN" => "Honduras",
    "HK" => "Hong Kong",
    "HU" => "Hungary",
    "IS" => "Iceland",
    "IN" => "India",
    "ID" => "Indonesia",
    "IR" => "Iran, Islamic Republic of",
    "IQ" => "Iraq",
    "IE" => "Ireland",
    "IM" => "Isle of Man",
    "IL" => "Israel",
    "IT" => "Italy",
    "JM" => "Jamaica",
    "JP" => "Japan",
    "JE" => "Jersey",
    "JO" => "Jordan",
    "KZ" => "Kazakhstan",
    "KE" => "Kenya",
    "KI" => "Kiribati",
    "KP" => "Korea, Democratic People's Republic of",
    "KR" => "Korea, Republic of",
    "KW" => "Kuwait",
    "KG" => "Kyrgyzstan",
    "LA" => "Lao People's Democratic Republic",
    "LV" => "Latvia",
    "LB" => "Lebanon",
    "LS" => "Lesotho",
    "LR" => "Liberia",
    "LY" => "Libyan Arab Jamahiriya",
    "LI" => "Liechtenstein",
    "LT" => "Lithuania",
    "LU" => "Luxembourg",
    "MO" => "Macao",
    "MK" => "Macedonia, The Former Yugoslav Republic of",
    "MG" => "Madagascar",
    "MW" => "Malawi",
    "MY" => "Malaysia",
    "MV" => "Maldives",
    "ML" => "Mali",
    "MT" => "Malta",
    "MH" => "Marshall Islands",
    "MQ" => "Martinique",
    "MR" => "Mauritania",
    "MU" => "Mauritius",
    "YT" => "Mayotte",
    "MX" => "Mexico",
    "FM" => "Micronesia, Federated States of",
    "MD" => "Moldova, Republic of",
    "MC" => "Monaco",
    "MN" => "Mongolia",
    "ME" => "Montenegro",
    "MS" => "Montserrat",
    "MA" => "Morocco",
    "MZ" => "Mozambique",
    "MM" => "Myanmar",
    "NA" => "Namibia",
    "NR" => "Nauru",
    "NP" => "Nepal",
    "NL" => "Netherlands",
    "AN" => "Netherlands Antilles",
    "NC" => "New Caledonia",
    "NZ" => "New Zealand",
    "NI" => "Nicaragua",
    "NE" => "Niger",
    "NG" => "Nigeria",
    "NU" => "Niue",
    "NF" => "Norfolk Island",
    "MP" => "Northern Mariana Islands",
    "NO" => "Norway",
    "OM" => "Oman",
    "PK" => "Pakistan",
    "PW" => "Palau",
    "PS" => "Palestinian Territory, Occupied",
    "PA" => "Panama",
    "PG" => "Papua New Guinea",
    "PY" => "Paraguay",
    "PE" => "Peru",
    "PH" => "Philippines",
    "PN" => "Pitcairn",
    "PL" => "Poland",
    "PT" => "Portugal",
    "PR" => "Puerto Rico",
    "QA" => "Qatar",
    "RE" => "Reunion",
    "RO" => "Romania",
    "RU" => "Russian Federation",
    "RW" => "Rwanda",
    "SH" => "Saint Helena",
    "KN" => "Saint Kitts and Nevis",
    "LC" => "Saint Lucia",
    "PM" => "Saint Pierre and Miquelon",
    "VC" => "Saint Vincent and The Grenadines",
    "WS" => "Samoa",
    "SM" => "San Marino",
    "ST" => "Sao Tome and Principe",
    "SA" => "Saudi Arabia",
    "SN" => "Senegal",
    "RS" => "Serbia",
    "SC" => "Seychelles",
    "SL" => "Sierra Leone",
    "SG" => "Singapore",
    "SK" => "Slovakia",
    "SI" => "Slovenia",
    "SB" => "Solomon Islands",
    "SO" => "Somalia",
    "ZA" => "South Africa",
    "GS" => "South Georgia and The South Sandwich Islands",
    "ES" => "Spain",
    "LK" => "Sri Lanka",
    "SD" => "Sudan",
    "SR" => "Suriname",
    "SJ" => "Svalbard and Jan Mayen",
    "SZ" => "Swaziland",
    "SE" => "Sweden",
    "CH" => "Switzerland",
    "SY" => "Syrian Arab Republic",
    "TW" => "Taiwan, Province of China",
    "TJ" => "Tajikistan",
    "TZ" => "Tanzania, United Republic of",
    "TH" => "Thailand",
    "TL" => "Timor-leste",
    "TG" => "Togo",
    "TK" => "Tokelau",
    "TO" => "Tonga",
    "TT" => "Trinidad and Tobago",
    "TN" => "Tunisia",
    "TR" => "Turkey",
    "TM" => "Turkmenistan",
    "TC" => "Turks and Caicos Islands",
    "TV" => "Tuvalu",
    "UG" => "Uganda",
    "UA" => "Ukraine",
    "AE" => "United Arab Emirates",
    "GB" => "United Kingdom",
    "US" => "United States",
    "UM" => "United States Minor Outlying Islands",
    "UY" => "Uruguay",
    "UZ" => "Uzbekistan",
    "VU" => "Vanuatu",
    "VE" => "Venezuela",
    "VN" => "Viet Nam",
    "VG" => "Virgin Islands, British",
    "VI" => "Virgin Islands, U.S.",
    "WF" => "Wallis and Futuna",
    "EH" => "Western Sahara",
    "YE" => "Yemen",
    "ZM" => "Zambia",
    "ZW" => "Zimbabwe");
  ?>
  <select name="<?php echo($field);?>">
    <?php
      foreach ($countries as $code => $country) {
        ?>
          <option value="<?php echo($code); ?>"<?php echo(strcasecmp($code, $current_value) == 0 ? " selected=\"selected\"" : ""); ?>><?php echo($country); ?></option>
        <?php
      }
    ?>
  </select>
  <?php
}

function address_postal_field_callback() {
  icopyright_make_account_row(100, 'icopyright_address_postal');
}

function address_phone_field_callback() {
  icopyright_make_account_row(100, 'icopyright_address_phone');
}

function display_field_callback() {
  ?>
<input id="icopyright_auto_option" name="icopyright_display" type="radio" value="auto"
        <?php $icopyright_display = get_option('icopyright_display'); if (empty($icopyright_display) || $icopyright_display == "auto") {
  echo "checked";
}?> />
  <?php _e('Automatic ')?><br/>
<span class="description">
		    <?php _e('iCopyright Toolbar and Interactive Copyright Notice will be automatically added into content of post')?>
		</span>

<br/>

<input id="icopyright_manual_option" name="icopyright_display" type="radio" value="manual"
        <?php $icopyright_display2 = get_option('icopyright_display'); if ($icopyright_display2 == "manual") {
  echo "checked";
}?>/>
  <?php _e('Manual ')?><br/>
<span class="description">
		    <?php _e('Deploy iCopyright Toolbar and Interactive Copyright Notice into content of post using WordPress shortcode')?>
		</span>

<div id="M3"
     style="float:left;margin:0 50px 0 0;display:none;<?php $display5 = get_option('icopyright_display'); if ($display5 == "manual") {
       echo "display:block;";
     }?>">
  <p>
    <strong><?php _e('Available WordPress Shortcodes: ')?></strong>
  </p>
  <ul>
    <li>[icopyright_one_button_toolbar]</li>
    <li>[icopyright_horizontal_toolbar]</li>
    <li>[icopyright_vertical_toolbar]</li>
    <li>[interactive_copyright_notice]</li>
  </ul>
  <p>
    <strong><?php _e('Available WordPress Shortcode Attributes: ')?></strong>
  </p>
  <table>
    <thead>
    <tr>
      <th>Purpose</th>
      <th>Attribute</th>
      <th>Variations</th>
      <th>Example Usage</th>
    </tr>
    </thead>
    <tbody>
    <tr>
      <td>Default</td>
      <td>--</td>
      <td>--</td>
      <td>[icopyright_one_button_toolbar]</td>
    </tr>
    <tr>
      <td>For alignment</td>
      <td>float="right"</td>
      <td>float="left"<br/>float="right"</td>
      <td>[icopyright_one_button_toolbar float="right"]</td>
    </tr>
    </tbody>
  </table>
</div>

<br style="display: block; clear: both;"/>

<input id="icopyright_none_option" name="icopyright_display" type="radio" value="none"
        <?php $icopyright_displayNone = get_option('icopyright_display'); if ($icopyright_displayNone == "none") {
  echo "checked";
}?>/>
  <?php _e('Do not display')?><br/>
  
<?php
}

function tools_field_callback() {
  ?>
<fieldset id="toolbar-format">
  <input name="icopyright_tools" type="radio"
         value="horizontal" <?php $icopyright_tools = get_option('icopyright_tools'); if (empty($icopyright_tools) || $icopyright_tools == "horizontal") {
    echo "checked";
  }?> />
  <iframe id="horizontal-article-tools-preview" style="border: 0;" scrolling="no" height="53" width="300"></iframe>
  <input name="icopyright_tools" type="radio" value="vertical" <?php if ($icopyright_tools == "vertical") {
    echo "checked";
  }?> />
  <iframe id="vertical-article-tools-preview" style="border: 0;" scrolling="no" height="130" width="100"></iframe>
  <input name="icopyright_tools" type="radio" value="onebutton" <?php if ($icopyright_tools == "onebutton") {
    echo "checked";
  }?> />
  <iframe id="onebutton-article-tools-preview" style="border: 0;" scrolling="no" height="250" width="200"></iframe>
</fieldset>
<?php
}

function theme_field_callback() {
  ?>
<fieldset>
  <select name="icopyright_theme" class="form-select" id="icopyright_article_tools_theme">
    <?php
    $themes = icopyright_theme_options();
    $icopyright_theme = get_option('icopyright_theme'); if (empty($icopyright_theme)) {
    $icopyright_theme = 'CLASSIC';
  }
    foreach ($themes as $option => $name) {
      print "<option value=\"$option\"";
      if ($option == $icopyright_theme) {
        print ' selected="selected"';
      }
      print ">$name</option>";
    }
    ?>
  </select>
</fieldset>
<?php
}

function background_field_callback() {
  ?>
<fieldset>
  <input name="icopyright_background" type="radio"
         value="OPAQUE" <?php $icopyright_background = get_option('icopyright_background'); if (empty($icopyright_background) || $icopyright_background == "OPAQUE") {
    echo "checked";
  }?> /> <?php _e('Opaque')?>
  <br/>
  <input name="icopyright_background" type="radio"
         value="TRANSPARENT" <?php if ($icopyright_background == "TRANSPARENT") {
    echo "checked";
  }?> /> <?php _e('Transparent')?>
</fieldset>
<?php
}

function align_field_callback() {
  ?>
<fieldset>
  <input name="icopyright_align" type="radio"
         value="left" <?php $icopyright_align = get_option('icopyright_align'); if (empty($icopyright_align) || $icopyright_align == "left") {
    echo "checked";
  }?> /> <?php _e('Left')?>
  <br/>
  <input name="icopyright_align" type="radio"
         value="right" <?php $icopyright_align = get_option('icopyright_align');if ($icopyright_align == "right") {
    echo "checked";
  }?> /> <?php _e('Right')?>
</fieldset>

<?php
}

function copyright_notice_preview_callback() {
  ?>
<fieldset>
  <iframe id="copyright-notice-preview" style="border: 0;" height="50" scrolling="no"></iframe>
</fieldset>
<?php
}

function show_preview_callback() {
  ?>
<fieldset>
  <table id="icopyright-show-when">
    <thead>
    <tr>
      <td width="15%" align="center">Single&nbsp;Post</td>
      <td width="20%" align="center">Multiple&nbsp;Posts</td>
      <td></td>
    </tr>
    </thead>
    <tbody>
    <tr class="show-both">
      <td style="text-align: center;">
        <input name="icopyright_show" type="radio"
               value="both" <?php $icopyright_show = get_option('icopyright_show'); if (empty($icopyright_show) || $icopyright_show == "both") {
          echo "checked";
        }?> />
      </td>
      <td style="text-align: center;">
        <input name="icopyright_show_multiple" type="radio"
               value="both" <?php $icopyright_show_multiple = get_option('icopyright_show_multiple'); if (empty($icopyright_show_multiple) || $icopyright_show_multiple == "both") {
          echo "checked";
        }?> />
      </td>
      <td>
        Show both iCopyright Toolbar and Interactive Copyright Notice
      </td>
    </tr>
    <tr class="show-toolbar">
      <td style="text-align: center;">
        <input name="icopyright_show" type="radio"
               value="tools" <?php $icopyright_show = get_option('icopyright_show');if ($icopyright_show == "tools") {
          echo "checked";
        }?> />
      </td>
      <td style="text-align: center;">
        <input name="icopyright_show_multiple" type="radio"
               value="tools" <?php $icopyright_show_multiple = get_option('icopyright_show_multiple');if ($icopyright_show_multiple == "tools") {
          echo "checked";
        }?> />
      </td>
      <td>
        Show only iCopyright Toolbar
      </td>
    </tr>
    <tr class="show-icn">
      <td style="text-align: center;">
        <input name="icopyright_show" type="radio"
               value="notice" <?php $icopyright_show = get_option('icopyright_show');if ($icopyright_show == "notice") {
          echo "checked";
        }?> />
      </td>
      <td style="text-align: center;">
        <input name="icopyright_show_multiple" type="radio"
               value="notice" <?php $icopyright_show_multiple = get_option('icopyright_show_multiple');if ($icopyright_show_multiple == "notice") {
          echo "checked";
        }?> />
      </td>
      <td>
        Show only Interactive Copyright Notice
      </td>
    </tr>
    <tr class="show-nothing">
      <td>
        &nbsp;
      </td>
      <td style="text-align: center;">
        <input name="icopyright_show_multiple" type="radio"
               value="nothing" <?php $icopyright_show_multiple = get_option('icopyright_show_multiple');if ($icopyright_show_multiple == "nothing") {
          echo "checked";
        }?> />
      </td>
      <td>
        Show nothing
      </td>
    </tr>
    </tbody>
  </table>
</fieldset>
<?php
}

function show_multiple_callback() {}

function display_on_pages_field_callback() {
  ?>
<fieldset>
  <input id="display_on_pages" name="icopyright_display_on_pages"
         type="checkbox" <?php if (get_option('icopyright_display_on_pages') == 'yes') {
    print 'checked="checked"';
  } ?>
         value="yes">
  <label for="display_on_pages">Display tools on pages as well as posts</label>
</fieldset>
<?php
}



function empty_callback() {
	echo '<div class="empty_callback"></div>';
}
function categories_field_callback() {

}

function exclude_author_field_callback() {}
function authors_field_callback() {}

function share_field_callback() {
  $icopyright_share = get_option('icopyright_share');
  $check_email = get_option('icopyright_conductor_email');
  $check_password = get_option('icopyright_conductor_password');
  ?>
<fieldset>
  <input name="icopyright_share" type="radio" value="yes" <?php if ($icopyright_share == "yes") {
    echo "checked";
  }?> <?php if (empty($check_email) || empty($check_password)) {
    echo 'disabled';
  }?>/> <?php _e('On ')?>
  <br/>
  <input name="icopyright_share" type="radio"
         value="no" <?php if (empty($icopyright_share) || $icopyright_share == "no") {
    echo "checked";
  }?><?php if (empty($check_email) || empty($check_password)) {
    echo ' disabled';
  }?>/> <?php _e('Off ')?>
</fieldset>
<span class="description">Share services make it easy for readers to share links to your articles using
                  Facebook, LinkedIn, Twitter, and Google+. Displayable in the four-button versions of the Toolbar only.</span>
<?php
}

function ez_excerpt_field_callback() {
  $check_email = get_option('icopyright_conductor_email');
  $check_password = get_option('icopyright_conductor_password');
  ?>
<fieldset>
  <input name="icopyright_ez_excerpt" type="radio"
         value="yes" <?php $icopyright_ez_excerpt = get_option('icopyright_ez_excerpt'); if (empty($icopyright_ez_excerpt) || $icopyright_ez_excerpt == "yes") {
    echo "checked";
  }?> <?php if (empty($check_email) || empty($check_password)) {
    echo 'disabled';
  }?>/> <?php _e('On ')?>
  <br/>
  <input name="icopyright_ez_excerpt" type="radio"
         value="no" <?php $icopyright_ez_excerpt2 = get_option('icopyright_ez_excerpt'); if ($icopyright_ez_excerpt2 == "no") {
    echo "checked";
  }?> <?php if (empty($check_email) || empty($check_password)) {
    echo 'disabled';
  }?>/> <?php _e('Off ')?>
</fieldset>
<span class="description">When EZ Excerpt is activated, any reader who tries to copy/paste
                  a portion of your article will be presented with a box asking "Obtain a License?". If reader
                  selects "yes" he or she will be offered the opportunity to license the excerpt for purposes of posting
                  on the reader's own website.</span>
<?php
}

function pricing_optimizer_apply_automatically_field_callback() {
  ?>
  <div id="icx_remove_parent_tr"></div>
  <script type="text/javascript">
    jQuery(document).ready(function() {
      jQuery("#icx_remove_parent_tr").parent().parent().remove();
    });
  </script>
  <?php
}

function searchable_field_callback() {
  $check_email = get_option('icopyright_conductor_email');
  $check_password = get_option('icopyright_conductor_password');
  $icopyright_searchable = get_option('icopyright_searchable');
  ?>
  <fieldset>
    <input name="icopyright_searchable" type="checkbox"
          value="true" <?php if ($icopyright_searchable == "true") echo('checked="checked"'); ?>/>
    <?php _e('Allow the articles on my site to be searchable by tools such as the Republish module in this plugin and iCopyright <a href="http://www.repubhub.com" target="_blank">repubHub</a>&trade;')?>
  </fieldset>
<?php
}

function use_category_filter_field_callback() {
  ?>
  <a href="javascript:authorPopup()">Click here to exclude authors and categories</a>
  <br/>
  <span style="font-size: 7.5pt;">
  <?php _e('Indicate any groups of articles for which you don\'t have the right to sell licenses.  (Individual articles may be excluded on their Edit Post template.)')?>
  </span>
<?php
}

function site_description_field_callback() {
	echo '<textarea rows="3" cols="50" name="icopyright_site_description" maxlength="1024" placeholder="Describe what your content is about...">' . get_option('icopyright_site_description') . '</textarea>';
	echo '<br/><span style="font-size: 7.5pt;">Maximum of 1024 characters</span>';
}

function site_logo_field_callback() {
	echo '<div>
    <input type="text" name="icopyright_site_logo" id="icopyright_site_logo" class="regular-text" value="' . get_option("icopyright_site_logo") . '">
    <input type="button" name="upload-btn" id="upload-btn" class="button-secondary" value="Upload Logo">
		</div>';
	echo '<p style="line-height: 1;"><span style="font-size: 7.5pt;">Click Upload Logo to upload the publication logo, or to change the logo you are now using. The image file must be a .gif, .jpeg or .png and should be optimized for 234 pixels wide by 60 pixels high. This logo will be displayed at the top of the iCopyright Licensing Window that users see when they click on the tags affixed to your content. It will also appear at the top or bottom of the licensed content, depending upon the license type.</span></p>';
	echo '<div style="margin-top: 10px;" id="icopyright_site_logo_image" ' . ((!get_option("icopyright_site_logo")) ? 'style="display: none;"' : '') . '><img src="' . get_option("icopyright_site_logo") . '" width="234px" height="60px"/></div>';
}

function pricing_optimizer_opt_in_field_callback() {
  $check_email = get_option('icopyright_conductor_email');
  $check_password = get_option('icopyright_conductor_password');

  $icopyright_pricing_optimizer_opt_in = get_option('icopyright_pricing_optimizer_opt_in');
  $icopyright_pricing_optimizer_apply_automatically = get_option('icopyright_pricing_optimizer_apply_automatically');
  $icopyright_created_date = get_option('icopyright_created_date')+(3*24*60*60);
  $autoPriceOptimizerDate = date("m/d/Y", $icopyright_created_date)
  ?>
  <input type="hidden" name="icopyright_pricing_optimizer_showing" value="true"/>
  <fieldset>
    <input name="icopyright_pricing_optimizer_opt_in" type="checkbox"
          value="true" <?php if ($icopyright_pricing_optimizer_opt_in == "true") echo('checked="checked"'); ?> <?php if (empty($check_email) || empty($check_password)) echo(' disabled="disabled"');?>/> <?php _e('Start Price Optimizer on '.$autoPriceOptimizerDate.' and')?>
    <br/>
    <input class="price_optimizer_radio" name="icopyright_pricing_optimizer_apply_automatically" value="false" type="radio" <?php if ($icopyright_pricing_optimizer_apply_automatically != "true") echo('checked="checked"'); ?> <?php if (empty($check_email) || empty($check_password) || $icopyright_pricing_optimizer_opt_in == "false") echo(' disabled="disabled"');?>/> <?php _e('Show me the results so I can decide what prices to implement'); ?>
    <br/>
    <input class="price_optimizer_radio" name="icopyright_pricing_optimizer_apply_automatically" value="true" type="radio" <?php if ($icopyright_pricing_optimizer_apply_automatically == "true") echo('checked="checked"'); ?> <?php if (empty($check_email) || empty($check_password) || $icopyright_pricing_optimizer_opt_in == "false") echo(' disabled="disabled"');?>/> <?php _e('Automatically implement the pricing found to be the most profitable by Pricing Optimizer'); ?>
  </fieldset>
  <input type="hidden" name="icopyright_pricing_optimizer_apply_automatically2" value="<?php echo(($icopyright_pricing_optimizer_apply_automatically == "true") ? 'true' : 'false'); ?>"/>
  <span class="description">
    Price Optimizer runs a 10 week live test of different Instant License prices to determine which prices generate the most revenue.
  </span>
  <script type="text/javascript">
    jQuery(document).ready(function() {
      jQuery("input[name='icopyright_pricing_optimizer_opt_in']").change(function() {
        if (jQuery("input[name='icopyright_pricing_optimizer_opt_in']").is(":checked")) {
          jQuery(".price_optimizer_radio").removeAttr("disabled");
        } else {
          jQuery(".price_optimizer_radio").attr("disabled", "disabled");
        }
      });
      jQuery("input[name='icopyright_pricing_optimizer_apply_automatically']").change(function() {
        jQuery("input[name='icopyright_pricing_optimizer_apply_automatically2']").val(jQuery("input[name='icopyright_pricing_optimizer_apply_automatically']:checked").val());
      });
    });
  </script>
<?php
}

function icopyright_ad_unit_callback($args) {
	if ($args['explanation']) {
		echo '<span id="' . $args['id'] . '" style="font-size:10pt;">' . $args['explanation'] . '</span>';
	} else {
		echo '<input type="text" name="' . $args['id'] . '" style="width:200px;" value="' . sanitize_text_field(stripslashes(get_option($args['id']))) . '"/>';
		
		if ($args['id'] != "icopyright_google_client") {
			$url = 'previewAd.act?size=&type=&adSlot=';
			$display = !get_option($args['id']) ? "display: none;" : "";
			echo '<a data-type="' . $args['type'] . '" data-size="' . $args['size'] . '" style="' . $display . ' font-size: 7.5pt;" href="' . icopyright_get_server() . '/publisher/previewAd.act" class="icopyright_ad_preview">Preview</a>';
		}	
		
	}
}

function pub_id_field_callback() {
  ?>
<input type="text" name="icopyright_pub_id" style="width:200px"
       value="<?php $icopyright_pubid = sanitize_text_field(stripslashes(get_option('icopyright_pub_id'))); echo $icopyright_pubid; ?>"/>
<span class="description" id="no_pub_id_message">Click <a
    href="<?php echo admin_url('options-general.php?page=copyright-licensing-tools&show-registration-form=1') ?>">here</a>
  to register your publication</span>
<?php
}

function conductor_email_field_callback() {
  ?>
<input type="text" name="icopyright_conductor_email" style="width:200px;"
       value="<?php echo sanitize_text_field(stripslashes(get_option('icopyright_conductor_email'))); ?>"/>
<?php
}

function conductor_password_field_callback() {
  ?>
<input type="password" name="icopyright_conductor_password" style="width:200px;"
       value="<?php echo sanitize_text_field(stripslashes(get_option('icopyright_conductor_password'))); ?>"/>
       <p style="font-size: 8pt; width: 200px;">
         (same as at <a style="text-decoration: none;" target="_blank" href="//repubhub.com">www.repubhub.com</a>, if you have one)
			   <a href="//<?php echo ICOPYRIGHT_SERVER . '/user/forgotPassword.act?email=';?>" target="_blank" style="text-decoration: none; margin-left: 10px;">Forgot Password?</a>       
       </p>
       
<?php
}

function feed_url_field_callback() {
  $feedUrl = sanitize_text_field(stripslashes(get_option('icopyright_feed_url')));
  ?>
<input type="text" name="icopyright_feed_url" style="width:500px;"
       value="<?php echo (!empty($feedUrl) ? $feedUrl : icopyright_get_default_feed_url()); ?>"/>
<?php
}

?>