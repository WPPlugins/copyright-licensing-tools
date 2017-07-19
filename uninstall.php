<?php
define("ICOPYRIGHT_PLUGIN_NAME", "copyright-licensing-tools");
define("ICOPYRIGHT_PLUGIN_DIR", WP_PLUGIN_DIR . "/" . ICOPYRIGHT_PLUGIN_NAME);
define("ICOPYRIGHT_USERAGENT", "iCopyright WordPress Plugin v3.0.2");

require (ICOPYRIGHT_PLUGIN_DIR . '/icopyright-common.php');

//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();

$pid = get_option('icopyright_pub_id');
$playerStatus = 'DEAD';

deactivate_account($pid, $playerStatus);      

delete_option("icopyright_fname");
delete_option("icopyright_lname");
delete_option("icopyright_site_name");
delete_option("icopyright_publication");
delete_option("icopyright_site_url");
delete_option("icopyright_address_line1");
delete_option("icopyright_address_line2");
delete_option("icopyright_address_line3");
delete_option("icopyright_address_city");
delete_option("icopyright_address_state");
delete_option("icopyright_address_country");
delete_option("icopyright_address_postal");
delete_option("icopyright_address_phone");
delete_option("icopyright_display");
delete_option("icopyright_tools");
delete_option("icopyright_theme");
delete_option("icopyright_background");
delete_option("icopyright_align");
delete_option("icopyright_show");
delete_option("icopyright_show_multiple");
delete_option("icopyright_display_on_pages");
delete_option("icopyright_use_category_filter");
delete_option("icopyright_categories");
delete_option("icopyright_exclude_categories");
delete_option("icopyright_exclude_author_filter");
delete_option("icopyright_authors");  
delete_option("icopyright_ez_excerpt");
delete_option("icopyright_share");
delete_option("icopyright_syndication");
delete_option("icopyright_feed_url");
delete_option('icopyright_tou');
delete_option('icopyright_conductor_password');
delete_option('icopyright_conductor_email');
delete_option('icopyright_redirect_on_first_activation');
delete_option('icopyright_pricing_optimizer_opt_in');
delete_option('repubhub_dismiss_post_new_info_box');
delete_option('icopyright_searchable');
delete_option("icopyright_unread_republish_clips_$pid");
delete_option("icopyright_unread_republish_markers_$pid");
delete_option('icopyright_pub_id');
delete_option('repubhub_dismiss_save_search_info_box');
delete_option('icopyright_featured_pub_check');
delete_option('icopyright_is_featured');
delete_option('icopyright_adblade_leaderboard');
delete_option('icopyright_adblade_fullbanner');
delete_option('icopyright_adblade_wideskyscraper');
delete_option('icopyright_adblade_mediumrectangle1');
delete_option('icopyright_adblade_mediumrectangle2');
delete_option('icopyright_google_client');
delete_option('icopyright_google_leaderboard');
delete_option('icopyright_google_fullbanner');
delete_option('icopyright_google_wideskyscraper');
delete_option('icopyright_google_mediumrectangle1');
delete_option('icopyright_google_mediumrectangle2');
delete_option('icopyright_site_description');
delete_option('icopyright_site_logo');

?>