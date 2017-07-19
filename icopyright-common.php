<?php
/**
 * @file
 * Common functions for communicating with the iCopyright servers
 */

// Which iCopyright server should we talk to via REST? The standard is license.icopyright.net, port 80,
// but you can target alternate infrastructures (normally for debugging purposes) by changing these variables.

define('LOCAL', false);
define('STAGING', false);


if (LOCAL) {
	define('ICOPYRIGHT_SERVER', 'localhost');
	define('ICOPYRIGHT_PORT', 8080);	
} else if (STAGING) {
	define('ICOPYRIGHT_SERVER', 'staging.icopyright.net');
	define('ICOPYRIGHT_PORT', 80);	
} else {
	define('ICOPYRIGHT_SERVER', 'license.icopyright.net');
	define('ICOPYRIGHT_PORT', 80);	
}


/**
 * Return the iCopyright server and port that is handling the various services
 *
 * @param bool $secure
 *      should we go over https?
 * @return the full server specification
 */
function icopyright_get_server($secure = TRUE, $includeHttp = TRUE) {
  if (LOCAL || STAGING || ICOPYRIGHT_PORT == 8080) {
		$secure = false;
  }
  
$server = "";

if ($includeHttp) {
  $server = ($secure ? 'https:' : 'http:');
}

$server .= '//' . ICOPYRIGHT_SERVER;

  if (ICOPYRIGHT_PORT != 80) {
    $server .= ':' . ICOPYRIGHT_PORT;
  }
  return $server;
}

function icopyright_static_server() {
	if (LOCAL) {
		return '//localhost:8080';
	} else if (STAGING) {
		return '//static.staging.icopyright.net';
	}
	
  return '//static.icopyright.net';
}

function getPortal() {
	if (STAGING) {
		return 'https://portal.icopyright.net';
	} else {
		return 'https://repubhub.icopyright.net';
	}
}

/**
 * Asks the iCopyright server to ping us, to see if there's a successful link between the iCopyright servers
 * and us. If there isn't, then the quality of service will be somewhat degraded
 * @param $useragent
 *      string the user agent string
 * @param $pid
 *      integer the publication ID
 * @param $email
 *      string the email address of a registrar
 * @param $password
 *      string the password of that registrar
 * @return boolean true if the link is established; false if not
 */
function icopyright_ping($useragent, $pid, $email, $password) {
  $res = icopyright_post("/api/xml/publication/ping/$pid", NULL, $useragent, icopyright_make_header($email, $password), 'GET');
  return icopyright_check_response($res);
}

function icopyright_is_featured_publication($useragent, $pid, $email, $password) {
	$res = icopyright_post("/api/xml/repubhub/is-featured/$pid", NULL, $useragent, icopyright_make_header($email, $password), 'GET');
	return $res;
}

/**
 * Fetch a Publication's settings:
 *
 * fname   member's first name
 * lname   member's last name
 * pname   publication's name
 * purl    publication's URL
 * furl    publication's feed URL pattern.  Must contain at least one asterisk denoting the article ID, i.e. <em>http://foo.com/article?id=*</em>
 * line1   publisher's address line 1
 * line2   publisher's address line 2
 * line3   publisher's address line 3
 * city    publisher's city
 * state   publisher's state
 * postal  publisher's postal code
 * country publisher's country code
 * phone   publisher's phone number
 *
 * @param $useragent
 *      string the user agent string
 * @param $pid
 *      integer the publication ID
 * @param $email
 *      string the email address of a registrar
 * @param $password
 *      string the password of that registrar
 * @return an array of settings.
 */
function icopyright_get_publication_settings($useragent, $pid, $email, $password) {
  $res = icopyright_post("/api/xml/publication/settings/$pid", NULL, $useragent, icopyright_make_header($email, $password), 'GET');
  $output = array();
  if (icopyright_check_response($res)) {
    $output['success'] = true;
    $xml = @simplexml_load_string($res->response);
    $output['fname'] = (string)$xml->fname;
    $output['lname'] = (string)$xml->lname;
    $output['pname'] = (string)$xml->pname;
    $output['purl'] = (string)$xml->purl;
    $output['furl'] = (string)$xml->furl;
    $output['line1'] = (string)$xml->line1;
    $output['line2'] = (string)$xml->line2;
    $output['line3'] = (string)$xml->line3;
    $output['city'] = (string)$xml->city;
    $output['state'] = (string)$xml->state;
    $output['postal'] = (string)$xml->postal;
    $output['country'] = (string)$xml->country;
    $output['phone'] = (string)$xml->phone;
    $output['createdDate'] = (string)$xml->createdDate;
    $output['pricingOptimizerOptIn'] = (string)$xml->pricingOptimizerOptIn;
    $output['pricingOptimizerApplyAutomatically'] = (string)$xml->pricingOptimizerApplyAutomatically;
    $output['searchable'] = (string)$xml->searchable;
    $output['ezExcerpt'] =(string)$xml->ezExcerpt;
    $output['background'] = (string)$xml->background;
    $output['theme'] = (string)$xml->theme;
    $output['shareService'] = (string)$xml->shareService;
    $output['syndication'] = (string)$xml->syndication;
    
    $output['icopyright_site_description'] = (string)$xml->siteDescription;
    $output['icopyright_site_logo'] = (string)$xml->logoUrl;
    
    $output['icopyright_adblade_leaderboard'] = (string)$xml->adBladeLeaderboard;
    $output['icopyright_adblade_wideskyscraper'] = (string)$xml->adBladeSkyscraper;
    $output['icopyright_adblade_fullbanner'] = (string)$xml->adBladeFullBanner;
    $output['icopyright_adblade_mediumrectangle1'] = (string)$xml->adBladeMed1;
    $output['icopyright_adblade_mediumrectangle2'] = (string)$xml->adBladeMed2;
    	
    $output['icopyright_google_client'] = (string)$xml->googleAdClient;
    $output['icopyright_google_leaderboard'] = (string)$xml->googleLeaderboard;
    $output['icopyright_google_wideskyscraper'] = (string)$xml->googleSkyscraper;
    $output['icopyright_google_fullbanner'] = (string)$xml->googleFullBanner;
    $output['icopyright_google_mediumrectangle1'] = (string)$xml->googleMed1;
    $output['icopyright_google_mediumrectangle2'] = (string)$xml->googleMed2;    

  } else {
    $output['success'] = false;
    $xml = @simplexml_load_string($res->response);
    if ($xml) {
      $output['error'] = $res->response;
    } else {
      $output['error'] = $res->http_expl;
    }
  }

  return $output;
}

/**
 * Given a RESTful request to add a publisher, post it to iCopyright's servers
 *
 * @param  $postdata
 *      the package of settings to post
 * @param  $useragent
 *      the type and version of the plugin doing the posting, for example
 *      "iCopyright Drupal Plugin v6.x-1.0"
 * @param  $email
 *      the email address of the user
 * @param  $password
 *      the user's iCopyright password
 * @return the response from iCopyright's servers
 */
function icopyright_post_new_publisher($postdata, $useragent, $email, $password) {
  // Create the new publisher
  $res = icopyright_post('/api/xml/publisher/addbrief', $postdata, $useragent);
  if(icopyright_check_response($res)) {
    $xml = @simplexml_load_string($res->response);
    $pid = (string)$xml->publication_id;
    icopyright_post_syndication_service($pid, TRUE, $useragent, $email, $password);
  }
  return $res;
}

function icopyright_register_content($tag, $useragent, $email, $password) {
  $url = "/api/xml/content/tag/". urlencode($tag);
  $res = icopyright_post_blocking($url, NULL, $useragent, icopyright_make_header($email, $password), "PUT", false);
  return $res;
}

function icopyright_delete_content($tag, $useragent, $email, $password) {
  $url = "/api/xml/content/tag/". urlencode($tag);
  $res = icopyright_post_blocking($url, NULL, $useragent, icopyright_make_header($email, $password), "DELETE", false);
  return $res;
}

function icopyright_update_searchable($tag, $searchable, $searchable_override, $useragent, $email, $password) {
  $post = array(
  		'searchable' => ($searchable == true ? 'true' : 'false'), 
  		'searchable_override' => ($searchable_override == true ? 'true' : 'false')
  		); 
  $postdata = http_build_query($post);
  
  $url = "/api/xml/content/tag/update-searchable/". urlencode($tag);
  $res = icopyright_post_blocking($url, $postdata, $useragent, icopyright_make_header($email, $password), "POST", false);
  return $res;
}

function icopyright_create_member($useragent, $email, $password, $publicationId, $newEmail, $fName, $lName) {
	$post = array(
			'emailAddress' => $newEmail,
			'publicationId' => $publicationId,
			'fName' => $fName,
			'lName' => $lName
	);
	$postdata = http_build_query($post);
		
	$url = "/api/xml/repubhub/create-member";
	$res = icopyright_post($url, $postdata, $useragent, icopyright_make_header($email, $password), "PUT");
	return $res;
}

function icopyright_add_topic($postdata, $useragent, $email, $password) {
  $url = "/api/xml/repubhub/topics-create";
  $res = icopyright_post($url, $postdata, $useragent, icopyright_make_header($email, $password), "PUT");
  return $res;
}

function icopyright_search($postdata, $useragent, $email, $password) {
  $url = "/api/xml/repubhub/search";
  $res = icopyright_post($url, $postdata, $useragent, icopyright_make_header($email, $password), "POST");
  return $res;
}

function icopyright_edit_topic($topicId, $postdata, $useragent, $email, $password) {
  $url = "/api/xml/repubhub/topics-update/".$topicId;
  $res = icopyright_post($url, $postdata, $useragent, icopyright_make_header($email, $password), "POST");
  return $res;
}

function icopyright_get_embed($tag, $allowScript, $useragent, $email, $password) {
	$endPoint = $allowScript ? "embed" : "oembed";
  $url = "/api/xml/repubhub/".$endPoint."?tag=".$tag;
  $res = icopyright_post($url, NULL, $useragent, icopyright_make_header($email, $password), "GET");
  return $res;
}

function icopyright_get_topics($useragent, $currentUser, $email, $password) {
  $url = "/api/xml/repubhub/topics?memberEmail=".$currentUser;
  $res = icopyright_post($url, NULL, $useragent, icopyright_make_header($email, $password), "GET");
  return $res;
}

function icopyright_get_topic($useragent, $currentUser, $email, $password, $topicId) {
	$url = "/api/xml/repubhub/topics/".$topicId."?memberEmail=".$currentUser;
	$res = icopyright_post($url, NULL, $useragent, icopyright_make_header($email, $password), "GET");
	return $res;
}

function icopyright_get_search_filters($useragent, $email, $password) {
	$url = "/api/xml/repubhub/search-filters";
	$res = icopyright_post($url, NULL, $useragent, icopyright_make_header($email, $password), "GET");
	return $res;
}

function icopyright_get_ad_filters($useragent, $email, $password) {
	$url = "/api/xml/publication/ad-networks";
	$res = icopyright_post($url, NULL, $useragent, icopyright_make_header($email, $password), "GET");
	return $res;
}

function icopyright_get_global_settings($useragent, $currentUser, $email, $password) {
	$url = "/api/xml/repubhub/global-settings?memberEmail=".$currentUser;
	$res = icopyright_post($url, NULL, $useragent, icopyright_make_header($email, $password), "GET");
	return $res;
}

function icopyright_post_global_settings($useragent, $postdata, $email, $password) {
	$url = "/api/xml/repubhub/global-settings";
	$res = icopyright_post($url, $postdata, $useragent, icopyright_make_header($email, $password), "POST", false);
	return $res;
}

function icopyright_get_recent_headlines($useragent, $email, $password, $page) {
  $url = "/api/xml/repubhub/recent-headlines/" . $page;
  $res = icopyright_post($url, NULL, $useragent, icopyright_make_header($email, $password), "GET");
  return $res;
}

function icopyright_search_topic($useragent, $currentUser, $email, $password, $topic_id, $page) {
	$url = "/api/xml/repubhub/search-topic/" . $topic_id . "/" . $page . "?memberEmail=".$currentUser;
  $res = icopyright_post($url, NULL, $useragent, icopyright_make_header($email, $password), "GET");
  return $res;
}

function icopyright_read_topic($useragent, $currentUser, $email, $password, $topic_id) {
	$url = "/api/xml/repubhub/read-topic/" . $topic_id . "?memberEmail=".$currentUser;
	$res = icopyright_post($url, NULL, $useragent, icopyright_make_header($email, $password), "GET", false);
	return $res;
}

function icopyright_delete_topic($topicId, $currentUser, $useragent, $email, $password) {
  $url = "/api/xml/repubhub/topics/".$topicId . "?memberEmail=" . $currentUser;
  $res = icopyright_post($url, NULL, $useragent, icopyright_make_header($email, $password), "DELETE");
  return $res;
}

function deactivate_account($pid, $playerStatus) {
 /* $user_agent = ICOPYRIGHT_USERAGENT;
  $email = get_option('icopyright_conductor_email');
  $password = get_option('icopyright_conductor_password');
  
  $post = array(
  		'playerStatus' => $playerStatus
  		); 
  $postdata = http_build_query($post);

  $url = "/api/xml/publication/deactivate/". $pid;
  $res = icopyright_post($url, $postdata, $useragent, icopyright_make_header($email, $password), "POST");
  return $res;*/
}

function reactivate_account($pid, $icopyright_searchable) {
  /*$user_agent = ICOPYRIGHT_USERAGENT;
  $email = get_option('icopyright_conductor_email');
  $password = get_option('icopyright_conductor_password');
  
  $post = array(
  		'searchable' => $icopyright_searchable
  		); 
  $postdata = http_build_query($post);

  $url = "/api/xml/publication/reactivate/". $pid;
  $res = icopyright_post($url, $postdata, $useragent, icopyright_make_header($email, $password), "POST");
  return $res;*/
}

/**
 * Update the publication info as necessary
 *
 * @param $pid
 * @param $fname
 * @param $lname
 * @param $name
 * @param $pub_url
 * @param $feed_url
 * @param $line1
 * @param $line2
 * @param $line3
 * @param $city
 * @param $state
 * @param $postal
 * @param $country
 * @param $phone
 * @param $useragent
 * @param $email
 * @param $password
 * @return object
 */
function icopyright_post_publication_info($pid, $fname, $lname, $name, $pub_url, $feed_url, $line1, $line2, $line3, $city, $state, $postal, $country,
                                          $phone, $useragent, $email, $password, $icopyright_pricing_optimizer_opt_in, 
																					$icopyright_pricing_optimizer_apply_automatically, $icopyright_searchable, $icopyright_ad_units, 
																					$icopyright_site_description, $icopyright_site_logo) {
  $post = array(
    'fname' => $fname,
    'lname' => $lname,
    'pname' => $name,
    'pub_url' => $pub_url,
    'feed_url' => $feed_url,
    'line1' => $line1,
    'line2' => $line2,
    'line3' => $line3,
    'city' => $city,
    'state' => $state,
    'postal' => $postal,
    'country' => $country,
    'phone' => $phone,
    'pricingOptimizerOptIn' => is_null($icopyright_pricing_optimizer_opt_in) ? 'N/A' : ''.$icopyright_pricing_optimizer_opt_in,
    'pricingOptimizerApplyAutomatically' => is_null($icopyright_pricing_optimizer_apply_automatically) ? 'N/A' : ''.$icopyright_pricing_optimizer_apply_automatically,
    'searchable' => is_null($icopyright_searchable) ? 'N/A' : ''.$icopyright_searchable,
  	'icopyright_ad_units' => $icopyright_ad_units,
  	'icopyright_site_description' => $icopyright_site_description,
  	'icopyright_site_logo' => $icopyright_site_logo
  );
  $postdata = http_build_query($post);
  $url = "/api/xml/publication/update/$pid";
  $res = icopyright_post($url, $postdata, $useragent, icopyright_make_header($email, $password));
  return $res;
}

function icopyright_post_publication_categories($pid, $excluded_categories, $excluded_authors, $useragent, $email, $password) {
  $cat_string = is_array($excluded_categories) ? implode("|", $excluded_categories) : $excluded_categories;
  $author_string = is_array($excluded_authors) ? implode("|", $excluded_authors) : $excluded_authors;
   
  $post = array('excluded_categories' => $cat_string, 'excluded_authors' => $author_string);
  
  $postdata = http_build_query($post);
  $url = "/api/xml/publication/update-categories/$pid";
  $res = icopyright_post($url, $postdata, $useragent, icopyright_make_header($email, $password));
  return $res;  
}

/**
 * Checks the response object for success code. Returns true if all is OK.
 *
 * @param  $res
 *      The response from a post
 * @return TRUE if all is OK
 */
function icopyright_check_response($res) {
  return ($res->http_code == '200');
}

/**
 * Updates a publication's feed URL. The URL must have one and only one asterisk.
 *
 * @param $pid
 *      the publication ID
 * @param $value
 *      the feed URL
 * @param $useragent
 *      a user agent string
 * @param  $email
 *      the email address of the user
 * @param  $password
 *      the user's iCopyright password
 * @return the response from iCopyright's servers
 */
function icopyright_post_update_feed_url($pid, $value, $useragent, $email, $password) {
  $url = "/api/xml/publication/update/$pid";
  $postdata = 'feed_url=' . urlencode($value);
  $res = icopyright_post($url, $postdata, $useragent, icopyright_make_header($email, $password));
  return $res;
}

/**
 * Sets EZ-Excerpt on or off for the publication
 *
 * @param  $pid
 *      the publication ID
 * @param  $value
 *      whether EZ Excerpt should be on (true) or off (false)
 * @param  $useragent
 *      a user agent string
 * @param  $email
 *      the email address of the user
 * @param  $password
 *      the user's iCopyright password
 * @return the response from iCopyright's servers in XML format
 */
function icopyright_post_ez_excerpt($pid, $value, $useragent, $email, $password) {
  $url = "/api/xml/publication/toolbar/$pid";
  $postdata = 'ez_excerpt_enabled=' . ($value == 0 ? 'false' : 'true');
  $res = icopyright_post($url, $postdata, $useragent, icopyright_make_header($email, $password));
  return $res;
}

/**
 * Changes the toolbar theme.
 *
 * @param $pid the publication ID
 * @param $theme the new theme: default, green, etc.
 * @param $background the new background theme
 * @param $useragent a user agent string identifying the plugin version
 * @param $email the email address of the user
 * @param $password the user's iCopyright password
 * @return the response from iCopyright's servers in XML format
 */
function icopyright_post_toolbar_theme($pid, $theme, $background, $useragent, $email, $password) {
  $url = "/api/xml/publication/toolbar/$pid";
  $postdata = "theme=$theme&background=$background";
  $res = icopyright_post($url, $postdata, $useragent, icopyright_make_header($email, $password));
  return $res;
}

/**
 * Returns an associative array of valid themes
 * @return an array of themes
 */
function icopyright_theme_options() {
  return array(
    'CLASSIC' => 'Classic',
    'CADET' => 'Cadet',
    'EARTH' => 'Earth',
    'OCEAN' => 'Ocean',
    'FOREST' => 'Forest',
    'AMARANTH' => 'Amaranth',
    'BLIZZARD' => 'Blizzard'
  );
}

/**
 * Returns an associative array of valid theme backgrounds
 * @return an array of backgrounds
 */
function icopyright_theme_backgrounds() {
  return array(
    'OPAQUE' => 'Opaque',
    'TRANSPARENT' => 'Transparent'
  );
}

/**
 * Enables or disables the syndication service for the publisher
 *
 * @param  $pid
 *      the publication ID
 * @param  $value
 *      whether syndication should be on (true) or off (false)
 * @param  $useragent
 *      the type and version of the plugin doing the posting, for example
 *      "iCopyright Drupal Plugin v6.x-1.0"
 * @param  $email
 *      the email address of the user
 * @param  $password
 *      the user's iCopyright password
 * @return the response from iCopyright's servers in XML format
 */
function icopyright_post_syndication_service($pid, $value, $useragent, $email, $password) {
  return icopyright_post_service(101, $pid, $value, $useragent, $email, $password);
}

/**
 * Enables or disables the share service for the publisher
 *
 * @param  $pid
 *      the publication ID
 * @param  $value
 *      whether share should be on (true) or off (false)
 * @param  $useragent
 *      the type and version of the plugin doing the posting, for example
 *      "iCopyright Drupal Plugin v6.x-1.0"
 * @param  $email
 *      the email address of the user
 * @param  $password
 *      the user's iCopyright password
 * @return the response from iCopyright's servers in XML format
 */
function icopyright_post_share_service($pid, $value, $useragent, $email, $password) {
  return icopyright_post_service(200, $pid, $value, $useragent, $email, $password);
}

/**
 * Enables or disables a service by ID for the publisher
 *
 * @param  $sid
 *      the service ID
 * @param  $pid
 *      the publication ID
 * @param  $value
 *      whether share should be on (true) or off (false)
 * @param  $useragent
 *      the type and version of the plugin doing the posting, for example
 *      "iCopyright Drupal Plugin v6.x-1.0"
 * @param  $email
 *      the email address of the user
 * @param  $password
 *      the user's iCopyright password
 * @return the response from iCopyright's servers in XML format
 */
function icopyright_post_service($sid, $pid, $value, $useragent, $email, $password) {
  $url = "/api/xml/service/offer/$sid";
  $queryargs = array();
  array_push($queryargs, "publication=$pid");
  array_push($queryargs, 'enable=' . ($value == 0 ? 'false' : 'true'));
  $res = icopyright_post($url, join('&', $queryargs), $useragent, icopyright_make_header($email, $password));
  return $res;
}

/**
 * Given an email address and a password, create the appropriate headers for authentication to change
 * Conductor settings
 *
 * @param  $email
 *      the email address of the user
 * @param  $password
 *      the user's iCopyright password
 * @return headers to use
 */
function icopyright_make_header($email, $password) {
  $header_encode = base64_encode("$email:$password");
  return array('Authorization' => 'Basic '.$header_encode);
}

/**
 * General helper function to post RESTfully to iCopyright. Returns an object with the following
 * fields: response (the text back from the server); http_code (the code, like 200 or 404); http_expl
 * (the http string corresponding to that code); curl_code (the curl error code)
 *
 * @param $url
 *      the URL to post to
 * @param $postdata
 *      the data that we're sending up
 * @param $useragent
 *      the user agent doing the requesting -- should be the plugin and version number
 * @param $headers
 *      headers to include for authentication, if any
 * @param $method
 *      the HTTP method -- defaults to post of course
 * @return object results of the post as specified
 */
function icopyright_post($url, $postdata, $useragent = NULL, $headers = NULL, $method = 'POST') {
  return icopyright_post_blocking($url, $postdata, $useragent, $headers, $method, true);
}

function icopyright_post_blocking($url, $postdata, $useragent = NULL, $headers = NULL, $method = 'POST', $blocking) {

    //Default: timeout: 5, redirection: 5, httpversion: 1.0, blocking: true, headers: array(), body: null, cookies: array()
    $args = array();
    $args['method'] = $method;
    $args['timeout'] = 60;
    $args['redirection'] = 5;
    $args['httpversion'] = '1.0';
    $args['blocking'] = $blocking;
    $args['sslverify'] = false;

    if ($headers == NULL)
        $headers = array();

    if($postdata != NULL) {
        $args['body'] = $postdata;
    } else {
        $args['body'] = NULL;
    }

    // Very unlikely we will need to follow, but set if we can
    if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) {
        $args['redirection'] = 1;
    }

    if ($useragent != NULL) {
        $args['user-agent'] = $useragent;
    }

    $args['headers'] = $headers;
    $args['cookies'] = array();

    // Fetch the respopnse
    $rv = new stdClass();

    $response = wp_remote_post( icopyright_get_server(TRUE) . $url, $args);
    
    if( is_wp_error( $response ) ) {
        $rv->http_expl = $response->get_error_message();
    } else {

        $rv->response = $response['body'];
        $rv->http_code = $response['response']['code'];

        // A 200 code can carry an error message in the payload
        if($rv->http_code == 200) {
            $xml = @simplexml_load_string($rv->response);
            $status = $xml->status;
            $rv->http_code = (string)$status['code'];
        } else if ($rv->http_code == 401) {
        	// Unauthorized
        	$rv->response = '<h3>HTTP Status 401 — Bad credentials</h3><p>Please ask your administrator to verify that his/her email address and password are valid and updated for the iCopyright plugin.  The administrator can verify this by visiting the iCopyright plugin settings page and clicking Show Advanced Settings.   If he/she set a password for www.repubhub.com, he/she must use the same password.</p>';
        }

        $responses = array(
            100 => 'Continue', 101 => 'Switching Protocols',
            200 => 'OK', 201 => 'Created', 202 => 'Accepted', 203 => 'Non-Authoritative Information', 204 => 'No Content', 205 => 'Reset Content', 206 => 'Partial Content',
            300 => 'Multiple Choices', 301 => 'Moved Permanently', 302 => 'Found', 303 => 'See Other', 304 => 'Not Modified', 305 => 'Use Proxy', 307 => 'Temporary Redirect',
            400 => 'Bad Request', 401 => 'Unauthorized', 402 => 'Payment Required', 403 => 'Forbidden', 404 => 'Not Found', 405 => 'Method Not Allowed', 406 => 'Not Acceptable', 407 => 'Proxy Authentication Required', 408 => 'Request Time-out', 409 => 'Conflict', 410 => 'Gone', 411 => 'Length Required', 412 => 'Precondition Failed', 413 => 'Request Entity Too Large', 414 => 'Request-URI Too Large', 415 => 'Unsupported Media Type', 416 => 'Requested range not satisfiable', 417 => 'Expectation Failed',
            500 => 'Internal Server Error', 501 => 'Not Implemented', 502 => 'Bad Gateway', 503 => 'Service Unavailable', 504 => 'Gateway Time-out', 505 => 'HTTP Version not supported'
        );

        if ($rv->http_code) {
        	$rv->http_expl = $responses[$rv->http_code];
        }
    }

    return $rv;
}
