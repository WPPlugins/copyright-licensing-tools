<?php
/**
 * Legacy feed template; simply do a permanent redirect to the new best location. New installs (v1.8 and on) will not need this.
 */
$root = dirname(dirname(dirname(dirname(__FILE__))));
if (file_exists($root . '/wp-load.php')) {
  require_once($root . '/wp-load.php');
} else {
  require_once($root . '/wp-config.php');
}

require_once (ICOPYRIGHT_PLUGIN_DIR . '/icopyright-feed.php');

get_feed_xml(true);

?>