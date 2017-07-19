<?php
//for logged in users
add_action('wp_ajax_repubhub_clips', 'icopyright_republish_topic_hits');
add_action('wp_ajax_repubhub_recent_headlines', 'icopyright_republish_recent_headlines');
add_action('wp_ajax_repubhub_search', 'icopyright_republish_page_search');
add_action('wp_ajax_repubhub_add_topic', 'icopyright_republish_page_post_add');
add_action('wp_ajax_repubhub_get_topic', 'icopyright_republish_get_topic');
add_action('wp_ajax_repubhub_edit_topic', 'icopyright_republish_page_post_edit' );
add_action('wp_ajax_repubhub_delete_topic', 'icopyright_republish_page_post_delete' );
add_action('wp_ajax_repubhub_update_global_settings', 'icopyright_update_global_settings' );
add_action('edit_form_after_title', 'icopyright_edit_form_after_title' );
add_action('wp_ajax_repubhub_dismiss_post_new_info_box', 'icopyright_repubhub_dismiss_post_new_info_box');
add_action('wp_ajax_repubhub_dismiss_save_search_info_box', 'icopyright_repubhub_dismiss_save_search_info_box');
add_action('wp_ajax_repubhub_clips_read', 'icopyright_republish_topic_read');
add_action('wp_ajax_repubhub_update_unread_total', 'icopyright_republish_update_unread_total');
add_action('admin_menu', 'icopyright_post_menu');
add_action( 'admin_bar_menu', 'icopyright_admin_bar', 999 );
add_filter( 'default_content', 'icopyright_republish_content', 10, 2 );
add_filter( 'default_title', 'icopyright_republish_title', 10, 2 );

//
// Add the iCopyright republish page
//

function icopyright_post_menu() {
  add_posts_page('Republish content via iCopyright\'s repubHub', icopyright_get_republish_title(true), 'edit_posts', 'repubhub-republish', 'icopyright_republish_page');
}


function icopyright_admin_bar( $wp_admin_bar ){
  $title = icopyright_get_republish_title(false);
  $url = admin_url( 'edit.php?page=repubhub-republish');
  $current_user = wp_get_current_user();
  // only show the Republish tab for users who are logged in and are higher than subscriber
  if ($current_user && !$current_user->has_cap('subscriber')) {
	  $args = array(
	    'href' => $url,
	    'title' => $title,
	    'parent' => 'new-content', // false for a root menu, pass the ID value for a submenu of that menu.
	    'id' => 'republish-1', // defaults to a sanitized title value.
	    'meta' => array() // array of any of the following options: array( 'html' => '', 'class' => '', 'onclick' => '', 'target' => '', 'title' => '' );
	  );
	  $wp_admin_bar->add_node( $args );
	
	  $args = array(
	    'href' => $url,
	    'title' => icopyright_get_republish_title(false),
	    'parent' => false, // false for a root menu, pass the ID value for a submenu of that menu.
	    'id' => 'republish-2', // defaults to a sanitized title value.
	    'meta' => array() // array of any of the following options: array( 'html' => '', 'class' => '', 'onclick' => '', 'target' => '', 'title' => '' );
	  );
	  $wp_admin_bar->add_node( $args );

	  $args = array(
	  		'href' => admin_url() . 'widgets.php',
	  		'title' => 'Automated widget',
	  		'parent' => 'republish-2', // false for a root menu, pass the ID value for a submenu of that menu.
	  		'id' => 'republish-3', // defaults to a sanitized title value.
	  		'meta' => array() // array of any of the following options: array( 'html' => '', 'class' => '', 'onclick' => '', 'target' => '', 'title' => '' );
	  );	  
	  $wp_admin_bar->add_node( $args );
	  
	  $args = array(
	  		'href' => $url,
	  		'title' => icopyright_get_republish_title(false, "Republish individual articles"),
	  		'parent' => 'republish-2', // false for a root menu, pass the ID value for a submenu of that menu.
	  		'id' => 'republish-4', // defaults to a sanitized title value.
	  		'meta' => array() // array of any of the following options: array( 'html' => '', 'class' => '', 'onclick' => '', 'target' => '', 'title' => '' );
	  );
	  $wp_admin_bar->add_node( $args );	  
  }
}


function icopyright_republish_content( $content, $post ) {
  //set content
  if (empty($_GET['rph_widget']) && !empty( $_GET['icx_tag'] )) {
    $user_agent = ICOPYRIGHT_USERAGENT;
    $email = get_option('icopyright_conductor_email');
    $password = get_option('icopyright_conductor_password');
    $allowScript = current_user_can("unfiltered_html");
    $res = icopyright_get_embed(urlencode($_GET['icx_tag']), $allowScript, $user_agent, $email, $password);

		if (!$res->response) {
			// some error must have happened like a timeout.  Try a few more times.
			$i = 0;
			do {
				$res = icopyright_get_embed(urlencode($_GET['icx_tag']), $allowScript, $user_agent, $email, $password);
				$i++;
			} while ($i < 5 && !$res->response);
			
		}
    $xml = @simplexml_load_string($res->response);
    $content = $xml->embedCode;
    
    if (!isset($post)) {
    	$post = new stdClass();
    }
    
    $post->title = $xml->title;
    
    update_post_meta($post->ID, "icopyright_republish_content", true);
    
    return($content);
  }
  return $content;
}




function icopyright_republish_title( $title, $post ) {
  //set content
  if (!empty( $_GET['icx_tag'] )) {
    return($post->title);
  }
  return $title;
}

function icopyright_republish_page() {
	// Clear the unread count
	$unreadCounts["total"] = 0;
	update_option('icopyright_unread_republish_clips_' . get_option('icopyright_pub_id'), json_encode($unreadCounts));

  icopyright_republish_page_get(array());
}


function icopyright_republish_page_get($data, $topic_id = NULL) {
	if($topic_id == NULL) {
		if(is_numeric($data['topicId']))
			$topic_id = $data['topicId'];
		else
			$topic_id = $_GET['topicId'];
	}
	if (!empty($_GET['success']))
		$data['success'] = $_GET['success'];

	initDisplay($data, $topic_id);
}

function initDisplay($data, $topic_id) {
	if (!wp_script_is( 'icopyright-admin-js', $list = 'enqueued' ))
		wp_enqueue_script('icopyright-admin-js', plugins_url('js/main.js', __FILE__), array(), ICOPYRIGHT_VERSION);
	
	if (!wp_script_is( 'icopyright-admin-css', $list = 'enqueued' ))
		wp_enqueue_style('icopyright-admin-css', plugins_url('css/style.css', __FILE__), array(), ICOPYRIGHT_VERSION);  // Update the version when the style changes.  Refreshes cache.
	
	wp_enqueue_style('icopyright-admin-css-select2', '//cdnjs.cloudflare.com/ajax/libs/select2/3.5.2/select2.min.css');
	wp_enqueue_script('icopyright-admin-js-select2', '//cdnjs.cloudflare.com/ajax/libs/select2/3.5.2/select2.min.js');
	
	wp_localize_script( 'icopyright-admin-js', 'admin_ajax_url', array('url' => admin_url('admin-ajax.php')));
	wp_localize_script( 'icopyright-admin-js', 'icx_plugin_url', array('url' => ICOPYRIGHT_PLUGIN_URL));	
	
	$email = get_option('icopyright_conductor_email');
	$current_user = wp_get_current_user();
	
	if (strtolower($email) != strtolower($current_user->user_email)) {
		// This is probably one of many editors/contributors
		//$exists = get_option('icopyright_' . $current_user->user_email);
		//if (!$exists) {
			$res = icopyright_create_member(ICOPYRIGHT_USERAGENT, get_option('icopyright_conductor_email'), get_option('icopyright_conductor_password'),
					get_option('icopyright_pub_id'), $current_user->user_email, $current_user->user_firstname, $current_user->user_lastname);
			
			if (!$res->response || $res->response == NULL) {
				echo '<h2 style="color: red;">A server error has occurred:</h2>';
				echo "<p style=\"font-size: 12pt;\">" . $res->http_expl . "</p>";
				exit();
			} else {
				$response = @simplexml_load_string($res->response);
				if((strlen($res->http_code) > 0) && ($res->http_code != '200')) {
					$msg = 'An unknown error has occurred';
					if ($response && is_object($response) && $response->status && $response->status->messages && $response->status->messages->message) {
					  $msg = $response->status->messages->message;
					} else {
						$msg = $res->response;
					}
					echo "<p style=\"padding: 25px; font-size: 12pt;\">" . $msg . "</p>";
					exit();
				}
			}
			
			//update_option('icopyright_' . $current_user->user_email, 'true');
		//}
	}
	
	echo '<div id="icx_repub_page" class="wrap">';
	icopyright_search_terms_section($data);
	icopyright_display_global_settings_inner();
	if(isset($topic_id)) {
		icopyright_republish_page_get_topics($data, $topic_id);
	} else {
		icopyright_republish_page_get_topics($data, '');
	}	
	echo '</div>';
}

function icopyright_display_global_settings() {

	?>
	   <div id="icx_global_settings_wrap" class="nav-tab">
	   Global Settings
	   </div>
	   	<?php     
}

function icopyright_display_global_settings_inner() {
	$frequencies = array(
			'IMMED' => 'As Stories Break',
			'DAILY' => 'Daily',
			'WEEKLY' => 'Weekly',
			'MONTHLY' => 'Monthly',
			'NEVER' => 'Never'
	);
	
	$user_agent = ICOPYRIGHT_USERAGENT;
	$email = get_option('icopyright_conductor_email');
	$password = get_option('icopyright_conductor_password');
	
	$currentMemberEmail = '';
	$current_user = wp_get_current_user();
	if ($email != $current_user->user_email) {
		$currentMemberEmail = $current_user->user_email;
	}
	
	$global_settings_res = icopyright_get_global_settings($user_agent, $currentMemberEmail, $email, $password);
	$global_settings_xml = @simplexml_load_string($global_settings_res->response);
	
	$excludeAllChecked = ($global_settings_xml->excludeAll == 'true') ? 'checked' : '';	
	?>
	<div id="icx_global_settings_wrap_inner" style="display: none;">
	<div id="icx_global_settings_overlay">
		
	</div>
	
	<form id="icx_global_settings_form" method="post" action="#">
	<div class="icx_settings_title">Exclude Publications:</div>
	<div class="icx_settings_desc">
	If you would like to exclude certain publications from your searches and feeds when &quot;All Publications&quot;
	is selected, please do so below. (This does not affect the Recent Headlines tab.)
	
	<br style="clear: both;"/><br/>
	<div>
		   		  <input type="checkbox" name="icx_global_settings_checkboxes" value="excludeAll" <?php echo $excludeAllChecked; ?> />
		   		  Exclude all publications that are not a Featured Publication or included in a category<br/><br/>
		   		  </div>
		   		  <h4 class="icx_global_setting_section_title">Exclude all publications in the following categories:</h4>
		   		  <div class="icx_global_setting_section_data">
		   		  <?php 
		   		  foreach ($global_settings_xml->groupExcludes as $group_exclude) {
		   		  	$checked = ($group_exclude->checked == "true") ? 'checked' : '';
		   		  	echo '<input type="checkbox" name="icx_global_settings_checkboxes" value="' . $group_exclude->code . '" ' . $checked . '/>' . $group_exclude->display . '<br/>';
		   		  }
		   		  echo '</div>';
						echo '<br/>';
						echo '<h4 class="icx_global_setting_section_title">Exclude the following Featured Publication(s):</h4>';
						
						echo '<div class="icx_global_setting_section_data">';
						foreach ($global_settings_xml->featuredPublicationExcludes as $pub_exclude) {
							$checked = ($pub_exclude->checked == "true") ? 'checked' : '';
							echo '<input type="checkbox" name="icx_global_settings_checkboxes" value="' . $pub_exclude->code . '" ' . $checked . '/>' . $pub_exclude->display . '<br/>';
						}
						echo '</div>';
						echo '<br/>';
						echo '<h4 class="icx_global_setting_section_title">Exclude my own publication(s):</h4>';
						echo '<div class="icx_global_setting_section_data">';
						foreach ($global_settings_xml->myPublicationExcludes as $my_pub_exclude) {
							$checked = ($my_pub_exclude->checked == "true") ? 'checked' : '';
							echo '<input type="checkbox" name="icx_global_settings_checkboxes" value="' . $my_pub_exclude->code . '" ' . $checked . '/>' . $my_pub_exclude->display . '<br/>';
						}
						echo '</div>';
						echo '</div>';
						echo '<p style="margin-top: 0px; clear: both;"></p>';
						echo '<div class="icx_settings_title">Topic advisories:</div><div class="icx_settings_desc">';
						echo 'Email me clips matching my topics:&nbsp;';
						$frequencies = array(
								'IMMED' => 'As Stories Break',
								'DAILY' => 'Daily',
								'WEEKLY' => 'Weekly',
								'MONTHLY' => 'Monthly',
								'NEVER' => 'Never'
						);
						echo '<select name="frequency">';
						foreach ($frequencies as $key => $name) {
							$selectedText = $global_settings_xml->frequency == $key ? 'selected' : '';
							echo '<option value="' . $key .'" ' . $selectedText . ' >' . $name . '</option>';
						}				
						echo '</select><br/>';
						$noMatchChecked = ($global_settings_xml->noMatch == "true") ? 'checked' : '';
						echo '<input type="checkbox" name="noMatch" value="true" ' . $noMatchChecked . ' />';
		        echo 'Send the email even when there are no matching clips (so I consider refining the topic)<br/>';
		        
		        echo '<input style="margin: 20px 0 0 200px; width: 50px;" type="submit" value="Save"/>';
						echo '</div>';
		   		  ?>
		   		</form>
		   	</div> 
		   	<?php 
}

function icopyright_edit_topic_section($search_xml) {
?>	
	<div id="editTopic" style="display: none;">
	<p class="editError" style="display: none; color: red;"></p>
	<div id="icx_edit_topic_overlay">
	</div>	
	<h3>Edit Search</h3>
	<div class="icx_search_wrapper">
	<form id="icx_republish_form" method="post" action="">
			<input type="hidden" name="topicId" value=""/>
			<label id="" class="icx_republish_label" for="icx_and_words">With all the words:</label><input id="icx_and_words" type="text" name="andWords" value=""/>
			<div class="icx_republish_advanced_fields" style="">
				<label class="icx_republish_label" for="icx_exact_words">With the exact phrase:</label><input id="icx_exact_words" type="text" name="exactPhrase" value=""/>
				<br/>
				<label class="icx_republish_label" for="icx_or_words">With at least one of the words:</label><input id="icx_or_words" type="text" name="orWords" value=""/>
				<br/>
				<label class="icx_republish_label" for="icx_not_words">Without the words:</label><input id="icx_not_words" type="text" name="notWords" value=""/>
				<br/>
				<label class="icx_republish_label" for="icx_author">Author:</label><input id="icx_author" type="text" name="author" value=""/>
				<br/>
				<label class="icx_republish_label" for="icx_publication_name">Publication Name:</label><input id="icx_publication_name" type="text" name="publicationName" value=""/>
				<br/>
				<label class="icx_republish_label" for="featuredPublicationString">Publication(s):</label>
				<select id="featuredPublicationSelectEdit" name="featuredPublicationStringEdit" multiple="multiple">
				<?php
			$optGroupOpen = false;
			foreach ($search_xml->featuredPublications as $featured_pub) {
				if ($featured_pub->id == 'parent') {
					if ($optGroupOpen == true) {
						echo '</optgroup>';
						$optGroupOpen = false;
					}
					echo '<optgroup label="' . $featured_pub->display . '">';
					$optGroupOpen = true;
				} else {
					$selected = '';//(!empty($selected_pubs) && in_array($featured_pub->id, $selected_pubs)) ? 'selected' : '';
					echo '<option value="' . $featured_pub->id . '" ' .  $selected .'>' . $featured_pub->display . '</option>';
				}
			}
			$frequencies = array(
					'IMMED' => 'As Stories Break',
					'DAILY' => 'Daily',
					'WEEKLY' => 'Weekly',
					'MONTHLY' => 'Monthly',
					'NEVER' => 'Never'
			);			
			
			?>
	      </select>  
	      <br/>
	      <label class="icx_republish_label" for="dateFilter">Search date range::</label>
	      <select id="icx_date_filter_edit" name="dateFilter">
	      <?php 
	        $selected_date = 'TWO_DAYS';//$data['dateFilter'];
	        //if(empty($selected_date)) {$selected_date = 'TWO_DAYS';}
		      foreach ($search_xml->dateFilters as $date_filter) {
	      		$selected = ($selected_date == $date_filter->name) ? 'selected' : '';
		      	echo '<option value="' . $date_filter->name . '" ' . $selected .'>' . $date_filter->displayName . '</option>';
		      }      
	      ?>
	      </select>
	      <br/>              
	    <label class="icx_republish_label" for="icx_frequency">Email me updated list:</label>
	    <select name="frequency" id="icx_frequency">
	      <?php foreach ($frequencies as $key => $name) { ?>
	        <option value="<?php echo $key ?>"><?php echo $name ?></option>
	      <?php } ?>
	    </select>
	    <br/>
	    <label class="icx_republish_label" for="icx_allow_rss">Create RSS Feed:</label><input id="icx_allow_rss" type="checkbox" name="allowRss" value="true"/>		    
	    </div>
	    <br/>
	    <input class="icx_save_edit_btn" type="submit" value="Save"/>
	    <input id="icx_save_cancel_btn" type="button" value="Cancel"/>
	  </form>
	</div>
	<div class="icx_clear"></div>	
	 </div>
	<?php 
}

function icopyright_search_terms_section($data) {
	$custom_css = "
    #repubhub-logo a {
    	display: inline-block;
		  width: 114px;
		  height: 50px;
    	background-image: url('" . ICOPYRIGHT_PLUGIN_URL . "/images/ReverseLogo-small.png');
		  background-repeat: no-repeat;
			vertical-align: bottom;
    }
  ";
	wp_add_inline_style( 'icopyright-admin-css', $custom_css );
	
	$user_agent = ICOPYRIGHT_USERAGENT;
	$email = get_option('icopyright_conductor_email');
	$password = get_option('icopyright_conductor_password');
	
	$search_res = icopyright_get_search_filters($user_agent, $email, $password);
	$search_xml = @simplexml_load_string($search_res->response);
	
	icopyright_edit_topic_section($search_xml);
	?>
		<div id="icx_status_message">
		</div>	  
		
		<form id="icx_search_form" method="post" action="#">
		<input type="hidden" name="page" value="1"/>
		<table style="width: 100%; margin: 15px 0;"> 	
		  <tr id="repubhub-logo">
		 		<td width="240px"><a id="rh" href="http://repubhub.icopyright.net" target="_blank"></a></td>
		 		<td style="text-align: center;"><h3>Find Republishable Articles</h3></td>
		 		<td width="125px"><?php icopyright_display_global_settings(); ?></td>
		  </tr>
		  
		  <tr class="icx_search_wrapper">
		    <td>
		      <select id="featuredPublicationSelect" name="featuredPublicationString" multiple="multiple">
		      <?php 
		        $selected_pub = $data['featuredPublicationString'];
		        if (empty($selected_pub)) {
		        	$selected_pub = '0';
		        }
		        $optGroupOpen = false;
			      foreach ($search_xml->featuredPublications as $featured_pub) {
			      	if ($featured_pub->id == 'parent') {
			      	  if ($optGroupOpen == true) {
			      	  	echo '</optgroup>';
			      	  	$optGroupOpen = false;
			      	  }
			      	  echo '<optgroup label="' . $featured_pub->display . '">';
			      	  $optGroupOpen = true;
			      	} else {
			      		$selected = ($selected_pub == $featured_pub->id) ? 'selected' : '';
				      	echo '<option value="' . $featured_pub->id . '" ' .  $selected .'>' . $featured_pub->display . '</option>';
			      	}
			      }      
		      ?>
		      </select>
	      </td>
	      <td id="search_box">
	      	<a class="icx_republish_advanced_btn" href="">More Options</a>
	      	<input id="icx_and_words" type="text" name="andWords" placeholder="Enter your search terms here" value="<?php echo $data['andWords']; ?>">
	  		</td>
	      <td>
		      <select id="icx_date_filter" name="dateFilter">
		      <?php 
		        $selected_date = $data['dateFilter'];
		        if(empty($selected_date)) {$selected_date = 'TWO_DAYS';}
			      foreach ($search_xml->dateFilters as $date_filter) {
		      		$selected = ($selected_date == $date_filter->name) ? 'selected' : '';
			      	echo '<option value="' . $date_filter->name . '" ' . $selected .'>' . $date_filter->displayName . '</option>';
			      }      
		      ?>
		      </select>
		    </td>   
	   
	      <td><input class="icx_add_btn icx_submit_btn" type="submit" value="Go"/></td>
	  </tr>
	  
		<tr class="icx_republish_advanced_fields_front_page" style="display: none;">
		  <td style="text-align: right;">
				<label class="icx_republish_label" for="icx_exact_words">With the exact phrase:</label>
			</td>
			<td>
        <input id="icx_exact_words" type="text" name="exactPhrase" value="<?php echo $data['exactPhrase']; ?>"/>
			</td>
	  </tr>
	  <tr class="icx_republish_advanced_fields_front_page" style="display: none;">
			<td style="text-align: right;">
				<label class="icx_republish_label" for="icx_or_words">With at least one of the words:</label>
			</td>
			<td>
        <input id="icx_or_words" type="text" name="orWords" value="<?php echo $data['orWords']; ?>"/>
      </td>
    </tr>
    <tr class="icx_republish_advanced_fields_front_page" style="display: none;">    
			<td style="text-align: right;">
				<label class="icx_republish_label" for="icx_not_words">Without the words:</label>
			</td>
			<td>
        <input id="icx_not_words" type="text" name="notWords" value="<?php echo $data['notWords']; ?>"/>
      </td>
    </tr>
    <tr class="icx_republish_advanced_fields_front_page" style="display: none;">    
			<td style="text-align: right;">
				<label class="icx_republish_label" for="author">Author:</label>
			</td>
			<td>
        <input id="author" type="text" name="author" value="<?php echo $data['author']; ?>"/>
      </td>
    </tr>
    <tr class="icx_republish_advanced_fields_front_page" style="display: none;">				
			<td style="text-align: right;">
				<label class="icx_republish_label" for="publicationName">Publication Name:</label>
			</td>
			<td>
        <input id="publicationName" type="text" name="publicationName" value="<?php echo $data['publicationName']; ?>"/>
      </td>			
		</tr>	   
	  </table>
	  <div class="icx_clear"></div>
	  
	  <!-- Categories -->
	  <table id="icx_article_categories">
	  	<tr>
	  	<td data-id="all" style="padding: 10px;">All</td>
		      <?php 
			      foreach ($search_xml->categories as $cat) {
			      	echo '<td data-id=' . $cat->id . '>' . $cat->displayName . '</td>';
			      }      
		      ?>	  	  
	  	</tr>
	  </table>
	  </form>
	  <?php 
}

/**
 * Main entry point and renders most visual items on screen
 * @param unknown $data
 * @param string $topic_id
 * @param string $currentSearch
 */
function icopyright_republish_page_get_topics($data, $displayTopicId = '') {
	$user_agent = ICOPYRIGHT_USERAGENT;
	$email = get_option('icopyright_conductor_email');
	$password = get_option('icopyright_conductor_password');

//   icopyright_calculate_unread_republish_clips();
//   $unreadCounts = icopyright_get_unread_counts();

	$current_user = wp_get_current_user();
  $currentMemberEmail = '';
  if ($email != $current_user->user_email) {
  	$currentMemberEmail = $current_user->user_email;
  } 
	
  $res = icopyright_get_topics($user_agent, $currentMemberEmail, $email, $password);
  $xml = @simplexml_load_string($res->response);
  
?>
	<div class="icx_wrap">
	  <?php if(!empty($data['error'])) { ?>
	    <div class="icx_error fadeout"><p><?php echo $data['error']; ?></p></div>
	  <?php } ?>
	  <?php if(!empty($data['success'])) { ?>
	    <div class="icx_success fadeout"><p><?php echo $data['success']; ?></p></div>
	  <?php } ?>	
  <div class="icon32" id="icon-page"><br></div>
  <h2 class="nav-tab-wrapper">
    <a id="icx_nav_tab_recent_headlines" class="nav-tab-recent-headlines icx_nav_tab nav-tab<?php if(empty($displayTopicId)){ ?> nav-tab-active icx-nav-tab-active<?php } ?>" href="recent_headlines">Recent Headlines</a>
    <a id="icx_nav_tab_search_results" class="nav-tab-search icx_nav_tab nav-tab" href="search_results"></a>
    
    <?php
      $index = 0;
      foreach ($xml->response as $topic) {
        ?>
          <a id="icx_nav_tab_<?php echo $topic->id; ?>" class="icx_nav_tab nav-tab<?php if(!empty($displayTopicId) &&  $displayTopicId == $topic->id){ ?> nav-tab-active icx-nav-tab-active<?php } ?>" href="<?php echo $topic->id; ?>"><?php echo icopyright_republish_topic_name($topic); ?></a>
        <?php
        $index ++;
      }
    ?>
  </h2>
  <div id="icx_topic_recent_headlines" class="icx_topic" style="display: <?php if(!empty($displayTopicId)){ ?>none<?php } ?>;">
<!--     <label for="sortBy">Sort by:&nbsp;</label><select id="icx_sortBy" name="sortBy"> -->
<!-- 	    <optgroup label="Trending (activity/hour)"> -->
<!-- 	    	<option value="TRENDING_TOTAL_GEOMETRIC">Trending FB/TW</option> -->
<!-- 	    	<option value="TRENDING_FB_GEOMETRIC">Trending FB</option> -->
<!-- 	    	<option value="TRENDING_TW_GEOMETRIC">Trending TW</option>     -->
<!-- 	    </optgroup> -->
	
<!-- 	    <optgroup label="Popular (total activity)"> -->
<!-- 	    	<option value="POPULAR_TOTAL_GEOMETRIC">Popular FB/TW</option> -->
<!-- 	    	<option value="POPULAR_FB_GEOMETRIC">Popular FB</option> -->
<!-- 	    	<option value="POPULAR_TW_GEOMETRIC">Popular TW</option>     -->
<!-- 	    </optgroup> -->
<!-- 	    <option value="PUBLICATION_DATE">Most Recent</option> -->
<!--     </select> -->
<!--     <br/> -->
    <img id="rh-spinner" src="<?php print plugin_dir_url(__FILE__) ?>images/animated-spinner.gif">
    <div class="icx_repubhub_clips" id="icx_clips_for_topic_0" data-loc="recent_headlines" data-topicid="recent_headlines">
    </div>
  </div>
  <div class="icx_clear"></div>
  
  <div id="icx_topic_search_results" class="icx_topic" style="display: none;">
		<img id="icx_search_results_spinner" src="<?php print plugin_dir_url(__FILE__) ?>images/animated-spinner.gif">
		<div id="icx_topic_search_results_inner"></div>
  </div>
  <div class="icx_clear"></div>
    
<?php
  $index = 0;
  foreach ($xml->response as $topic) {
  	$active = !empty($displayTopicId) && $displayTopicId == $topic->id;
		icopyright_search_topic_display(NULL, $topic, $active, FALSE);
?>
  <div class="icx_clear"></div>
<?php
    $index ++;
  }
  if(isset($displayTopicId)) { ?>
    <script type="application/javascript">
      jQuery("#icx_topic_<?php print $displayTopicId; ?>").trigger("click");
    </script>
  <?php
  }
?>
	</div>
<?php
}


function icopyright_republish_page_post_add() {
	$formData = $_POST['formValues'];
	$formData = explode("&",$_POST['formValues']);
	$post = array();
	foreach($formData as $val) {
		$keyVal = explode("=", $val);
		$val = urldecode(urldecode($keyVal[1])); // decode twice because Dallas Cowboys turns into Dallas+Cowboys, which turns into Dallas%2BCowboys
		
		$post[$keyVal[0]] = sanitize_text_field(stripslashes($val));
	}
			
	
	// Validate params.
	if (empty($post['andWords']) && empty($post['exactPhrase']) &&
			empty($post['orWords']) && empty($post['notWords']) && empty($post['author'])
			&& empty($post['publicationName']) && empty($post['featuredPublicationString'])) {
				echo "<p>Please provide search values.</p>";
				exit();
	}
	
	// Call WS
	$user_agent = ICOPYRIGHT_USERAGENT;
	$email = get_option('icopyright_conductor_email');
	$password = get_option('icopyright_conductor_password');
	
	$current_user = wp_get_current_user();
	if ($email != $current_user->user_email) {
		$post['memberEmail'] = $current_user->user_email;
	}	
	
	$res = icopyright_add_topic(http_build_query($post), $user_agent, $email, $password);

	$topic = @simplexml_load_string($res->response);
	$message = '<div class="icx_success fadeout"><p>Search has been saved.</p></div>';
	 if(!icopyright_check_response($res)) {
	 	$topic->error = 'true';
		 if (is_object($topic) && ($topic->status->messages)) {
		 $message = '<div class="icx_error fadeout"><p>'. (string)$topic->status->messages[0]->message . '.</p></div>';
		 } else {
		 	$message = '<div class="icx_error fadeout"><p>Sorry, we were unable to save that search.</p></div>';
		 }
	}
	
	$topic->message = $message;
	echo json_encode($topic);
	//echo$post;
//  	$_GET['page'] = 'repubhub-republish';
//  	icopyright_republish_page_get_topics($post, $post['topicId']);
	exit();
}

function icopyright_republish_get_topic() {
	$topicId = $_POST['topicId'];
	
	// Call WS
	$user_agent = ICOPYRIGHT_USERAGENT;
	$email = get_option('icopyright_conductor_email');
	$password = get_option('icopyright_conductor_password');
	
	$current_user = wp_get_current_user();
  $currentMemberEmail = '';
  if ($email != $current_user->user_email) {
  	$currentMemberEmail = $current_user->user_email;
  } 
	
	$res = icopyright_get_topic($user_agent, $currentMemberEmail, $email, $password, $topicId);
	
	$topic = @simplexml_load_string($res->response);

	exit();
}

function icopyright_republish_page_post_edit() {
  // Get values
	$formData = $_POST['formValues'];
	$formData = explode("&",$_POST['formValues']);
	$post = array();
	$featuredPublicationString = '';
	foreach($formData as $val) {
		$keyVal = explode("=", $val);
		
		if ($keyVal[0] == 'featuredPublicationStringEdit') {
			$featuredPublicationString .= $keyVal[1] . ',';
		} else { 
			$post[$keyVal[0]] = sanitize_text_field(stripslashes($keyVal[1]));
		}
	}
	$post['featuredPublicationString'] = $featuredPublicationString;
	
  // Validate params.
  $results = array();
  if (empty($post['andWords']) && empty($post['exactPhrase']) &&
    empty($post['orWords']) && empty($post['notWords']) &&
  	empty($post['featuredPublicationStringEdit']) && empty($post['author']) &&
  		empty($post['publicationName'])) {
    $results['edit_error'] = "Please provide search values.";
    echo json_encode($results);
    exit();
  }
  // Call WS
  $user_agent = ICOPYRIGHT_USERAGENT;
  $email = get_option('icopyright_conductor_email');
  $password = get_option('icopyright_conductor_password');
  
  $current_user = wp_get_current_user();
  if ($email != $current_user->user_email) {
  	$post['memberEmail'] = $current_user->user_email;
  }  
  
  $res = icopyright_edit_topic($post['topicId'], http_build_query($post), $user_agent, $email, $password);
  
  if((strlen($res->http_code) > 0) && ($res->http_code != '200')) {
  	echo "<p>" . $errorMessage . " (" . $res->http_code . ': ' . $res->http_expl . ")</p>";
  	if ($res->http_code == 401) {
  		$results['error'] = '<p>Your email address and password don\'t match a valid account. Please visit the ' .
  				'<a href="' . $adminUrl . 'options-general.php?page=copyright-licensing-tools#advanced">iCopyright settings page</a> and ' .
  				'click <em>Show Advanced Settings</em> to update your email address and password.  (If you\'ve set a password for <a style="text-decoration: none;" target="_blank" href="//repubhub.com">www.repubhub.com</a>, be sure to use the same password here.)</p>';
  	} else {
    $results['error'] = "<div class=\"icx_error fadeout\"><p>Unable to edit Search at this time.  Please try again later.</p></div>";
  	}
  	exit();
  }  
  
  $results['success'] = "<div class=\"icx_success fadeout\"><p>Search has been modified.</p></div>";
  $topic = @simplexml_load_string($res->response);
  
  $results['topic'] = $topic;
  
  echo json_encode($results);
  exit();
}

function icopyright_republish_page_post_delete() {
  $user_agent = ICOPYRIGHT_USERAGENT;
  $email = get_option('icopyright_conductor_email');
  $password = get_option('icopyright_conductor_password');
  
  $current_user = wp_get_current_user();
  $currentMemberEmail = '';
  if ($email != $current_user->user_email) {
  	$currentMemberEmail = $current_user->user_email;
  } 
  
  $res = icopyright_delete_topic($_POST['topicId'], $currentMemberEmail, $user_agent, $email, $password);
  if(!icopyright_check_response($res)) {
  	echo 'error';
    //$post['error'] = "Unable to delete topic at this time.  Please try again later.";
  } else {
  	echo 'success';
    //$post['success'] = "Topic has been deleted.";
  }
  exit();
}



function icopyright_search_topic_display($response, $obj, $active, $isSearch = FALSE) {
	$id = $isSearch ? 'search_results' : $obj->id;
	$action = $isSearch ? 'addTopic' : 'edit';
	$submitText = $isSearch ? 'Save Search' : 'Edit Search';
  $method = $isSearch ? 'post' : 'get';
  $formLocation = '';
  $formId = $isSearch ? 'icx_form_save_search' : 'icx_form_edit_topic_'.$id;
  $data = $isSearch ? '' : ' data-topicid='.$id;
  $hideBtn = ($isSearch && $obj->hideFeed == "true");
  $btnClass = $isSearch ? 'icx_save_btn' : 'icx_edit_btn';
  $btnTitle = $isSearch ? 'Save this search' : 'Edit Search';
  $btnText = $isSearch ? 'Save Search' : 'Edit Search';
  $frequencies = array(
  		'IMMED' => 'As Stories Break',
  		'DAILY' => 'Daily',
  		'WEEKLY' => 'Weekly',
  		'MONTHLY' => 'Monthly',
  		'NEVER' => 'Never'
  );
	if(!$isSearch) {
?>	
		<div id="icx_topic_<?php echo $id; ?>" class="icx_topic" style="display: <?php if($active == FALSE){ ?>none<?php } ?>;">
		
<?php }?>
	<div class="icx_topic_title">
	      <?php 
				if($isSearch && get_option("repubhub_dismiss_save_search_info_box") == null) {
				    ?>
				      <p style="float:left; background:lightblue; padding:10px; margin: 0 0 20px 0;" id="icx_save_hint_info_box">
								Tip: Click the <em>Save Search</em> button to automatically see new articles on this topic next time you visit. Saving a search also allows you to set up email alerts when new articles on this topic come in. 
				        <br/>
				        <a style="float: right;" href="#" id="icx_dismiss_save_hint_info_box">Dismiss</a>
				      </p>
				      <div style="clear: both;"></div>
				    <?php
				    }
	      
				if (!$isSearch) {
		      if (!empty($obj->andWords)) { 
		        echo "With all the words: <strong>" . $obj->andWords . "</strong><br/>";
		       } ?>
		      <?php if (!empty($obj->exactPhrase)) { ?>
		        With the exact phrase: <strong><?php echo($obj->exactPhrase); ?></strong><br/>
		      <?php } ?>
		      <?php if (!empty($obj->orWords)) { ?>
		        With at least one of the words: <strong><?php echo($obj->orWords); ?></strong><br/>
		      <?php } ?>
		      <?php if (!empty($obj->notWords)) { ?>
		        Without the words: <strong><?php echo($obj->notWords); ?></strong><br/>
		      <?php }
	
		      if (!empty($obj->publicationOrGroupName)) {
		      	echo "Publication(s): <strong>" . $obj->publicationOrGroupName . "</strong><br/>";
		      }
		      if (!empty($obj->author)) {
		      	echo "Author: <strong>" . str_replace("+", " ", $obj->author) . "</strong><br/>";
		      }
		      if (!empty($obj->friendlyDateFilter)) {
		      	echo "Date range: <strong>" . str_replace("_", " ", $obj->friendlyDateFilter) . "</strong><br/>";
		      }
		      ?>
		      Email me: <strong><?php echo($frequencies[$obj->frequency.""]); ?></strong><br/>
	<?php 
				
				if (!empty($obj->allowRss) && $obj->allowRss == 'true') {
					echo '<p>';
					echo '<a href="' . $obj->rssLocation . '" target="_blank"><img src="' . ICOPYRIGHT_PLUGIN_URL . '/images/feed-rss.gif"/></a>';
					echo '&nbsp;&nbsp;<a href="' . $obj->htmlLocation . '" target="_blank"><img src="' . ICOPYRIGHT_PLUGIN_URL . '/images/feed-html.gif"/></a>';
					echo '</p>';
				}
				}?>	      
	      
	    </div>
		  
		  <?php 
		  if(!$hideBtn) {
		  ?>
		    <div class="icx_topic_controls">
		      <?php 
		      if (!$isSearch) {
		      	// Refresh topic button
		      	?>
		      	<form style="display: inline;" class="topic_refresh" method="<?php echo $method; ?>" action="<?php echo $formLocation; ?>">
		      		<input type="hidden" name="topicId" value="<?php echo $obj->id; ?>"/>
							<button class="icx_refresh_btn icx_btn" type="submit" title="Refresh this search">
							Refresh
							</button>		      		
		      	</form>
		      	<?php 
		      }
		      ?>
		      <form style="display: inline;" id="<?php echo $formId; ?>" method="<?php echo $method; ?>" action="<?php echo $formLocation; ?>" <?php echo $data; ?>>
		        <input type="hidden" name="action" value="<?php echo $action; ?>"/>
		        <input type="hidden" name="page" value="<?php echo $_GET['page']; ?>"/>
		        <input type="hidden" name="topicId" value="<?php echo $obj->id; ?>"/>
		        <input type="hidden" name="andWords" value="<?php echo $obj->andWords; ?>"/>
		        <input type="hidden" name="exactPhrase" value="<?php echo $obj->exactPhrase; ?>"/>
		        <input type="hidden" name="orWords" value="<?php echo $obj->orWords; ?>"/>
		        <input type="hidden" name="notWords" value="<?php echo $obj->notWords; ?>"/>
		        <input type="hidden" name="frequency" value="<?php echo $obj->frequency; ?>"/>
		        <input type="hidden" name="featuredPublicationString" value="<?php if(!empty($obj->featuredPublicationString)) echo $obj->featuredPublicationString; ?>"/>
		        <input type="hidden" name="author" value="<?php if(!empty($obj->author)) echo $obj->author; ?>"/>
		        <input type="hidden" name="publicationName" value="<?php if(!empty($obj->publicationName)) echo $obj->publicationName; ?>"/>
		        <input type="hidden" name="dateFilter" value="<?php if(!empty($obj->dateFilter)) echo $obj->dateFilter; ?>"/>
		        <input type="hidden" name="allowRss" value="<?php if(!empty($obj->allowRss)) echo $obj->allowRss; ?>"/>
		        
						<button class="icx_btn <?php echo $btnClass; ?>" type="submit" title="<?php echo $btnTitle; ?>">
						<?php echo $btnText; ?>
						</button>		      			        
		      </form>
		    </div>
		  <?php 
		  }
		  ?>
	    <div class="icx_clear"></div>
	    <?php
	    if (!$isSearch) {
	    ?>
		    <div class="icx_repubhub_clips" id="icx_clips_for_topic_<?php print $obj->id ?>" data-loc="<?php print $obj->xmlLocation ?>" data-topicid="<?php print $obj->id ?>">
		      <img src="<?php print plugin_dir_url(__FILE__) ?>images/animated-spinner.gif">
		    </div>
		  <?php 
	    } else {
	    	icopyright_display_solr_content($response, $obj->content, FALSE, (int)$obj->page, (int)$obj->pageCount);
	    	exit();
	    }  
	    
	    if(!$isSearch) {
	    ?>
	  	</div>
	  	<?php }?>	
<?php 
 	  
}


/**
 * Given an XML location, returns HTML for the hits. Used in an AJAX call to fill out the page
 */
function icopyright_republish_recent_headlines() {
  $user_agent = ICOPYRIGHT_USERAGENT;
  $email = get_option('icopyright_conductor_email');
  $password = get_option('icopyright_conductor_password');
  
  $page = (empty($_GET['rhPage'])) ? '1' : $_GET['rhPage'];
  $res = icopyright_get_recent_headlines($user_agent, $email, $password, $page);
  $topicxml = @simplexml_load_string($res->response);
  
  icopyright_display_solr_content($res, $topicxml->content, TRUE, $topicxml->page);
}

/**
 * Updates global settings after a "save"
 */
function icopyright_update_global_settings() {
	// Get values
	$formData = $_POST['formValues'];
	$formData = explode("&",$_POST['formValues']);
	
	//icx_global_settings_checkboxes=g-6+&icx_global_settings_checkboxes=p-13278+&icx_global_settings_checkboxes=p-13280+
	$post = array();
	$excludes = '';
	foreach($formData as $val) {
		$keyVal = explode("=", $val);
		if($keyVal[0] == "icx_global_settings_checkboxes") {
			$excludes = $excludes . str_replace("+", "", sanitize_text_field(stripslashes($keyVal[1]))) . ',';
		}
		else {
			$post[$keyVal[0]] = sanitize_text_field(stripslashes($keyVal[1]));
		}
	}
	
	$post['excludes'] = $excludes;
	
	$user_agent = ICOPYRIGHT_USERAGENT;
	$email = get_option('icopyright_conductor_email');
	$password = get_option('icopyright_conductor_password');
	
	$current_user = wp_get_current_user();
	if ($email != $current_user->user_email) {
		$post['memberEmail'] = $current_user->user_email;
	}
		
	$res = icopyright_post_global_settings($user_agent, $post, $email, $password);

	if((strlen($res->http_code) > 0) && ($res->http_code != '200')) {
		echo "<p>" . $errorMessage . " (" . $res->http_code . ': ' . $res->http_expl . ")</p>";
		if ($res->http_code == 401) {
			echo '<p>Your email address and password don\'t match a valid account. Please visit the ' .
					'<a href="' . $adminUrl . 'options-general.php?page=copyright-licensing-tools#advanced">iCopyright settings page</a> and ' .
					'click <em>Show Advanced Settings</em> to update your email address and password.  (If you\'ve set a password for <a style="text-decoration: none;" target="_blank" href="//repubhub.com">www.repubhub.com</a>, be sure to use the same password here.)</p>';
		}
		exit();
	}
	
	$res = @simplexml_load_string($res->response);
	$attributes = $res->status->attributes();
	$code = $attributes['code'];
	$message = $res->status->messages->message;
	
	if ($code == 200 || $code == '200') {
		echo '<div class="icx_success fadeout"><p>' . $message . '</p></div>';
	} else {
		echo '<div class="icx_error fadeout"><p>' . $message . '</p></div>';
	}
		
	exit();
}

/**
 * Does an ajax search after inputting terms and hitting "go" 
 */
function icopyright_republish_page_search() {
	// Get values
	$formData = $_POST['formValues'];
	$formData = explode("&",$_POST['formValues']);
	
	$post = array();
	$featuredPublicationString = '';
	foreach($formData as $val) {
		$keyVal = explode("=", $val);
		
		if ($keyVal[0] == 'featuredPublicationString') {
			$featuredPublicationString .= $keyVal[1] . ',';
		} else { 
			$post[$keyVal[0]] = sanitize_text_field(stripslashes($keyVal[1]));
		}
	}
	$post['featuredPublicationString'] = $featuredPublicationString;

	// Validate params.
	if (empty($post['andWords']) && empty($post['exactPhrase']) &&
			empty($post['orWords']) && empty($post['notWords']) && empty($post['author'])
			&& empty($post['publicationName']) && empty($post['featuredPublicationString']) && empty($post['categoryId'])) {
			echo "<p>Please provide search values.</p>";
			exit();
	}
	
	$email = get_option('icopyright_conductor_email');
	$current_user = wp_get_current_user();
	if ($email != $current_user->user_email) {
		$post['memberEmail'] = $current_user->user_email;
	}
	
	
	// Call WS
	$user_agent = ICOPYRIGHT_USERAGENT;
	$email = get_option('icopyright_conductor_email');
	$password = get_option('icopyright_conductor_password');
	$res = icopyright_search(http_build_query($post), $user_agent, $email, $password);
	$search = @simplexml_load_string($res->response);
	
	if (is_object($search) && ($search->status->messages)) {
		echo '<p style="color: red;">' . (string)$search->status->messages[0]->message . '</p>'; 
	} else if (!empty($post['categoryId'])) {
		icopyright_display_solr_content($res, $search->content, FALSE, (int)$search->page, (int)$search->pageCount, 0, FALSE, TRUE);
	} else {
		icopyright_search_topic_display($res, $search, TRUE, TRUE);
	}
	
	exit();
}

/**
 * Used to display recent headlines and search results
 * @param unknown $res
 */
function icopyright_display_solr_content($res, $contentList, $isRecentHeadlines, $page = 1, $pageCount = 0, $lastRefreshDate = 0, $isTopic = FALSE, $isCategory = FALSE) {
	$adminUrl = admin_url();
	$pluginsUrl = plugins_url();
	$errorMessage = $isRecentHeadlines ? "Failed to get recent headlines" : "Failed to do search";
	if((strlen($res->http_code) > 0) && ($res->http_code != '200')) {
		echo "<p>" . $errorMessage . " (" . $res->http_code . ': ' . $res->http_expl . ")</p>";
		if ($res->http_code == 401) {
			echo '<p>Your email address and password don\'t match a valid account. Please visit the ' .
					'<a href="' . $adminUrl . 'options-general.php?page=copyright-licensing-tools#advanced">iCopyright settings page</a> and ' .
					'click <em>Show Advanced Settings</em> to update your email address and password.  (If you\'ve set a password for <a style="text-decoration: none;" target="_blank" href="//repubhub.com">www.repubhub.com</a>, be sure to use the same password here.)</p>';
		}
		exit();
	}	
	
	if (sizeof($contentList) > 0 && icopyright_includes_embeddable($contentList)) {
		
		$firstClipId = -1;
		foreach ($contentList as $clip) {
			if (strcmp($clip->embeddable, "true") == 0) {
				$titleClass = "icx_clip_title";
				if (!$isRecentHeadlines && !$isCategory) {
					if (strtotime($clip->createdDate) > $lastRefreshDate) {
						$titleClass .= " icx_unread_title";
					}
				}				
				$clipId = (int) $clip->clipId;
				if ($clipId > $firstClipId) {
					$firstClipId = $clipId;
	        }?>
	        <div class="icx_clip">
	          <div class="icx_clip_icon_wrapper">
	            <img class="icx_clip_icon" src="<?php echo($clip->image); ?>"/>
	          </div>
	          <div class="icx_clip_wrapper">
	            <a class="<?php echo($titleClass); ?>" target="_blank" href="<?php echo($clip->link); ?><?php if (strcmp($clip->embeddable, "true") == 0) { ?>&wp_republish_url=<?php echo(urlencode($adminUrl . "post-new.php?icx_tag=".$clip->tag)); ?><?php } ?>"><?php echo($clip->title); ?></a>
	            <?php if (strcmp($clip->embeddable, "true") == 0) { ?>
	              <a class="icx_republish_btn" target="_blank" href="<?php echo($adminUrl); ?>post-new.php?icx_tag=<?php echo(urlencode($clip->tag)); ?>"><img src="<?php echo($pluginsUrl); ?>/copyright-licensing-tools/images/republishBtn.png"/></a>
	            <?php } ?>
<!-- 	            <div class="icx_clear"></div> -->
	            <div class="icx_clip_byline">
	              <?php if(!empty($clip->author)) echo "<a class=\"icx_clip_author\" href=\"" . $clip->author . "\">By " . $clip->author . "</a> &mdash;"; ?>
	              <?php echo($clip->pubDate);?>
	            </div>
	            <div class="icx_clip_body">
	            	<b>
	            	<?php 

	            		echo "<a class=\"icx_clip_publicationName\" href=\"" . $clip->publication . "\">" . $clip->publication . "</a>&nbsp;&nbsp;";
	            		?>
	             </b> 
	              <?php echo($clip->description); ?>
	            </div>
	          </div>
	        </div>
	      <?php }
	    }
	    ?>
	  <?php
	  } else { ?>
	    <p>No articles currently match that topic.</p>
	  <?php }
	  	
	  
	  $pagerClass = '';
	  if ($isRecentHeadlines) {
	  	$pagerClass = "icx_pager_rh";
	  } else if ($isCategory) {
	  	$pagerClass = "icx_pager_cat";
	  } else if ($isTopic) {
			$pagerClass = "icx_pager_topic";
		} else {
			$pagerClass = "icx_pager";
		}
		
	  echo '<p></p>';
	  
	  if ($page > 1) {
	  	$href = ($isRecentHeadlines || $isTopic) ? ($page - 1) : "prev";
	  	echo "<a style=\"float: left; text-decoration: none;\"class=\"" . $pagerClass . "\" href=\"" . $href . "\">";
	  	echo "<img src=\"" . icopyright_static_server() . "/portal/images/repubhub_prev.png\" alt=\"Previous\"/>";
	  	echo "</a>";
	  }
	  
	  if (($page < $pageCount) || $isRecentHeadlines) {
	  	$href = ($isRecentHeadlines || $isTopic) ? ($page + 1) : "next";
	  	echo "<a style=\"float: right; text-decoration: none;\"class=\"" . $pagerClass . "\" href=\"" . $href . "\">";
	  	echo "<img src=\"" . icopyright_static_server() . "/portal/images/repubhub_next.png\" alt=\"Next\"/>";
	  	echo "</a>";
	  }
	  
	  if ($pageCount > 0) {
	  	echo "<p style=\"width: 100%; text-align: center; padding-top: 4px;\">Page " . $page . " of " . $pageCount . "</p>";
	  } 
	  
		if ($isRecentHeadlines) {
			exit();
		}		
}

/**
 * Given an XML location, returns HTML for the hits. Used in an AJAX call to fill out the page
 */
function icopyright_republish_topic_hits() {
  $xml_location = $_GET['loc'];
  $topicId = $_GET['topicid'];
  $page = $_GET['page'];
  if (!$page) {
  	$page = "1";
  }

  
  $user_agent = ICOPYRIGHT_USERAGENT;
  $email = get_option('icopyright_conductor_email');
  $password = get_option('icopyright_conductor_password');
  
  $current_user = wp_get_current_user();
  $currentMemberEmail = '';
  if ($email != $current_user->user_email) {
  	$currentMemberEmail = $current_user->user_email;
  }  
  
  $res = icopyright_search_topic($user_agent, $currentMemberEmail, $email, $password, $topicId, $page);
  $search = @simplexml_load_string($res->response);
  
  if (is_object($search) && ($search->status->messages)) {
  	echo '<p style="color: red;">' . (string)$search->status->messages[0]->message . '</p>';
  } else {  	

  	$lastRefreshDate = 0;
  	if ($search->lastRefreshDate) {
  		$lastRefreshDate = strtotime($search->lastRefreshDate);
  	}  	
  	
  	if ($page == "1") {
	  	$numNewClips = 0;
	   	foreach ($search->content as $clip) {
	   		$clipCreatedDate = strtotime($clip->createdDate);
	   		if ($clipCreatedDate > $lastRefreshDate) {
	   			$numNewClips++;
	   		}
	   	}
				
	   	if ($numNewClips >= $search->maxPerPage) {
	   		$numNewClips = $numNewClips . '+';
	   	}
	  	
			echo '<div id="num_topics_' . $topicId . '" style="display: none;" data-num="' . $numNewClips . '"></div>';
  	}
  	
    icopyright_display_solr_content($res, $search->content, FALSE, (int)$search->page, (int)$search->pageCount, $lastRefreshDate, TRUE);
  }
  exit();  
}



function icopyright_includes_embeddable($clips) {
  foreach ($clips as $clip) {
    if (strcmp($clip->embeddable, "true") == 0)
      return true;
  }
  return false;
}

function icopyright_republish_has_unread($topic, $unreadCounts) {
  $topicId = (int)$topic->id;
  return array_key_exists($topicId, $unreadCounts) && $unreadCounts[$topicId]>0;
}

function icopyright_republish_topic_name($topic) {
  $nameFriendlyString = $topic->friendlyString;
  if (strlen($nameFriendlyString) > 10) {
    $nameWords = preg_split('/\s+/', $nameFriendlyString);
    $nameFriendlyString = "";
    $div = "";
    foreach($nameWords as $nameWord) {
      $nameFriendlyString .= $div . $nameWord;
      if (strlen($nameFriendlyString)>10)
        break;
      $div = " ";
    }
  }
  $name = '<span class="icx_unread_tab_count">' . $nameFriendlyString . '</span>';
  if (isset($topic->id)) {
	  $topicId = (int)$topic->id;
	  //if (array_key_exists($topicId, $unreadCounts) && $unreadCounts[$topicId]>0)
	  $idName = 'img_spinner_' . $topicId;
	  $name .= '<img id="' . $idName . '" class="icx_spinner_tab" src="' . ICOPYRIGHT_PLUGIN_URL . '/images/ajax-loader4.gif"/>';
	    //$name .= '<span class="icx_unread_tab_count"><span class="icx_unread update-plugins count-1"><span class="plugin-count">'.$unreadCounts[$topicId].'</span></span></span>';

	    
	  $name .= '<div class="icx_topic_delete_btn" data-topicid="' . $topic->id . '">X</div>';
  }

  return $name;
}

function icopyright_server_url($s) {
  $ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true:false;
  $sp = strtolower($s['SERVER_PROTOCOL']);
  $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
  $port = $s['SERVER_PORT'];
  $port = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
  $host = isset($s['HTTP_X_FORWARDED_HOST']) ? $s['HTTP_X_FORWARDED_HOST'] : isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : $s['SERVER_NAME'];
  return $protocol . '://' . $host . $port;
}

function icopyright_calculate_unread_republish_clips() {
  //
  // Only do this every 30 mins
  //
 /* $prevUpdate = get_option('icopyright_update_unread_republish_time');
  if (empty($prevUpdate)) $prevUpdate = 0;
  $now = time();
  if (($now - $prevUpdate) < (60*30))
    return;

  //
  // Fetch all topics and clips.  Calculate the unread clips per topic.
  //
  update_option('icopyright_update_unread_republish_time', time());

  $totalUnreadCount = 0;
  $unreadCounts = array();
  $unreadMarkers = icopyright_get_unread_markers();

  $user_agent = ICOPYRIGHT_USERAGENT;
  $email = get_option('icopyright_conductor_email');
  $password = get_option('icopyright_conductor_password');
  $res = icopyright_get_topics($user_agent, $email, $password);

  $xml = @simplexml_load_string($res->response);
  if (sizeof($xml->response)>0) {
    foreach ($xml->response as $topic) {
      $res = icopyright_get_topic(str_replace("http://".ICOPYRIGHT_SERVER, "", $topic->xmlLocation), $user_agent, $email, $password);
      $topicxml = @simplexml_load_string($res->response);
      $unreadCount = 0;
      if (sizeof($topicxml->clips->clip) > 0 && icopyright_includes_embeddable($topicxml->clips->clip)) {
        $lastReadclipId = 0;
        if (array_key_exists((int)$topic->id, $unreadMarkers)) {
          $lastReadclipId = (int) $unreadMarkers[(int)$topic->id];
        }
        foreach ($topicxml->clips->clip as $clip) {
          if (strcmp($clip->embeddable, "true") == 0 && (int) $clip->clipId > $lastReadclipId) {
            $unreadCount ++;
          }
        }
      }
      $unreadCounts[(int)$topic->id] = $unreadCount;
      $totalUnreadCount += $unreadCount;
    }
  }
  $unreadCounts["total"] = $totalUnreadCount;
  update_option('icopyright_unread_republish_clips_' . get_option('icopyright_pub_id'), json_encode($unreadCounts));*/
}

/**
 * When a topic is read, decrease the "unread" numbers for the total and set this one to zero
 */

function icopyright_republish_topic_read() {
	// Call to server to update the last_refresh_date of topic
	$topicId = (int) $_GET['topicId'];
	$user_agent = ICOPYRIGHT_USERAGENT;
	$email = get_option('icopyright_conductor_email');
	$password = get_option('icopyright_conductor_password');
	
	$current_user = wp_get_current_user();
  $currentMemberEmail = '';
  if ($email != $current_user->user_email) {
  	$currentMemberEmail = $current_user->user_email;
  } 
	
	icopyright_read_topic($user_agent, $currentMemberEmail, $email, $password, $topicId);

	// Update local database and return total
	$icxNumRead = (int) $_GET['icxNumRead'];
	$unreadCounts = icopyright_get_unread_counts();
	$total = 0;
	if (array_key_exists('total', $unreadCounts))
		$total = $unreadCounts['total'];
	
	$total = $total - $icxNumRead;
	if ($total<0) $total = 0;
	$unreadCounts['total'] = $total;
	update_option('icopyright_unread_republish_clips_' . get_option('icopyright_pub_id'), json_encode($unreadCounts));
	echo $total;

	exit();
}

/**
 * AJAX call to update unread total
 */
function icopyright_republish_update_unread_total() {
	$totalUnreadClips = (int)$_POST['icxTotalUnreadClips'];
	$unreadCounts = array();
	$unreadCounts['total'] = $totalUnreadClips;	
	update_option('icopyright_unread_republish_clips_' . get_option('icopyright_pub_id'), json_encode($unreadCounts));
	
	echo $totalUnreadClips;
	exit();
}

function icopyright_get_unread_counts() {
  $unreadCounts = array();
  try {
    $unreadJson = get_option('icopyright_unread_republish_clips_' . get_option('icopyright_pub_id'));
    if (!empty($unreadJson))
      $unreadCounts = json_decode($unreadJson, true);
  } catch (Exception $e) {}
  return $unreadCounts;
}

function icopyright_get_unread_markers() {
  $unreadMarkers = array();
  try {
    $markersJson = get_option('icopyright_unread_republish_markers_' . get_option('icopyright_pub_id'));
    if (!empty($markersJson))
      $unreadMarkers = json_decode($markersJson, true);
  } catch (Exception $e) {}
  return $unreadMarkers;
}

function icopyright_get_republish_title($circleStyle, $title = "repubHub") {
  icopyright_calculate_unread_republish_clips();
  $unreadCounts = icopyright_get_unread_counts();
  if (array_key_exists('total', $unreadCounts) && $unreadCounts['total']>0) {
    if ($circleStyle)
      $title .= '<span class="icx_unread update-plugins count-1"><span class="plugin-count">'.$unreadCounts['total'].'</span></span>';
    else
      $title .= '&nbsp;'.$unreadCounts['total'];
  }
  return $title;
}

function icopyright_edit_form_after_title() {
	if ((empty($_GET['rph_widget']) && (!empty($_GET['icx_tag'])) || (!empty($_GET['post']) && get_post_meta($_GET['post'], "icopyright_republish_content")))) {
		if(get_option("repubhub_dismiss_post_new_info_box") == null) {
			$adminAjaxUrl = admin_url('admin-ajax.php');
			$dataLoc = admin_url('edit.php?page=repubhub-republish');
			?>
      <p style="float:left; background:lightblue; padding:10px; margin: 0 0 20px 0;" id="icx_post_new_info_box">
			The embed code in the text editor will display the republished article. 
			To preview the article, be sure to click &quot;Save Draft&quot; first, 
			and then &quot;View Post&quot; at top (since clicking Preview will not work in some browsers). 
			You may add an intro or conclusion above or below the embed code in the text editor.
        <br/>
        <a style="float: right;" href="" id="icx_dismiss_post_new_info_box">Dismiss</a>
      </p>
      <div style="clear: both;"></div>
      <script type="text/javascript">
        jQuery(document).ready(function () {
          jQuery("#icx_dismiss_post_new_info_box").click(function (event) {
            jQuery("#icx_post_new_info_box").hide();
            jQuery.ajax({
              url : '<?php echo $adminAjaxUrl;?>',
              type : "get",
              data : {action: "repubhub_dismiss_post_new_info_box", loc: '<?php echo $dataLoc;?>'},
              success: function() {}
            });
            event.preventDefault();
          });
        });
      </script>
    <?php
    }
    if (!empty($_GET['icx_tag'])) {
  	?>
  <p style="float:left; background:lightblue; padding:10px; margin: 0 0 20px 0;" id="icx_terms_of_use_box">
    By clicking "Publish" you agree to the
  <a target="_blank" href="<?php print icopyright_get_server() ?>/rights/termsOfUse.act?sid=15&tag=<?php print urlencode($_GET['icx_tag']) ?>">terms of use</a>.
      </p>
<?php }?> 
      <div style="clear: both;"></div>
  <?php
  }
}


function icopyright_repubhub_dismiss_post_new_info_box() {
  update_option("repubhub_dismiss_post_new_info_box", "true");
}


function icopyright_repubhub_dismiss_save_search_info_box() {
	update_option("repubhub_dismiss_save_search_info_box", "true");
}
