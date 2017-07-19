<?php

/**
 * Adds Icopy_Widget widget.
 */

class Icopy_Widget extends WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
				'icopy_widget', // Base ID
				'repubHub Headlines',
				array('classname' => 'icopy_widget', 'description' => esc_html( 'Republish world-class articles on your site.') ) // Args
		);
	}
	

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		if (!wp_script_is( 'icopy-widget-jq', $list = 'enqueued' )) {
			wp_enqueue_script('icopy-widget-jq', "//ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js", array(), RPH_WIDGET_VERSION);
		}

		if (!wp_script_is( 'icopyright-widget-ui', $list = 'enqueued' )) {
			wp_enqueue_script('icopyright-widget-ui', "//code.jquery.com/ui/1.11.4/jquery-ui.js", array('icopy-widget-jq'), ICOPYRIGHT_VERSION);
		}
		
		if (!wp_style_is( 'icopyright-widget-style', $list = 'enqueued' )) {
			wp_enqueue_style('icopyright-widget-style', "//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css", array(), ICOPYRIGHT_VERSION);
		}		
		
		if (!wp_script_is( 'icopy-widget-custom-style', $list = 'enqueued' )) {
			wp_enqueue_style('icopy-widget-custom-style', plugins_url('css/icopy_widget.css', __FILE__), array(), RPH_WIDGET_VERSION);
		}		
				
		if (!wp_script_is( 'icopyright-widget-js', $list = 'enqueued' )) {
			wp_enqueue_script('icopyright-widget-js', plugins_url('js/icopy-widget.js', __FILE__), array('icopyright-widget-ui'), ICOPYRIGHT_VERSION);
		}
		wp_localize_script( 'icopyright-widget-js', 'admin_ajax_url', array('url' => admin_url('admin-ajax.php')));

				
		$canEdit = current_user_can("edit_others_posts");
		
		// get articles from db or iCopyright server
		$articles = !empty($instance['articles']) ? $instance['articles'] : array();
		
		// If we don't have any articles or have never updated the articles, then force update
		if (empty($articles)) {
			$articles = icopy_get_widget_articles($instance);
		}
		
		if ($instance['agree_tos'] != 'checked') {
			if ($canEdit) {
				echo '<p style="font-size: 9pt;">In order to display this repubHub widget, please agree to the Terms of Use in your <a href="' . admin_url() . "widgets.php" . '">widgets</a> page.  Note: only admins and editors see this message.	</p>';
			}
			return ;
		}		
		
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
		
		$page = get_pages(array('post_type' => 'page', 'include' => $instance['pageId'], 'post_status' => 'publish'));
		$showThumbs = $instance['show_thumbs'];
		$ulStyle = $showThumbs != NULL && $showThumbs == 'checked' ? "list-style: none; margin: 0;" : "";
		echo '<ul class="icopy_widget" style="' . $ulStyle . '">';
		
		if ($articles && !empty($articles)) {
			foreach ($articles as $i => &$value) {
				$style = $i >= $instance['num_articles'] ? ' class="icopy_widget_extra" style="display: none; " ' : '';
				
				$pageLink = (!empty ($instance['pageId']) && !empty($page)) ? get_page_link($instance['pageId']) . "#". $value['tag'] . "&showTitle=true" : getPortal() . '/freePost.act?tag=' . $value['tag'];
	
				echo '<li data-widgetid="' . esc_attr($args["widget_id"]) . '" data-publicationid="' . esc_attr($value['publicationId']) . '" data-articleid="' . esc_attr($value['id']) . '" ' . $style . ' >';
				
				if ($showThumbs != NULL && $showThumbs == 'checked') {
	  			echo '<table><tr>';
	      	echo '<td style="vertical-align: middle; padding: 2px 5px 0 0; width: ' . esc_attr($instance['thumb_width']) . 'px; border: 0;"><a style="display: inline; border: none;" href="' . esc_url($pageLink) . '"><img style="max-width: ' . esc_attr($instance['thumb_width']) . 'px;"  height: auto;" src="' . esc_url($value['img']) . '&width='.esc_attr($instance['thumb_width']).'&height='.esc_attr($instance['thumb_width']).'"/></a></td>';
	        echo '<td><a href="' . esc_url($pageLink) . '">' .esc_html__($value['title']) . '</a></td>';
	        echo '</tr></table>';
				} else {
					echo '<a href="' . esc_url($pageLink) . '">' .esc_html__($value['title']) . '</a>';
				}
	      
	      
	      if ($canEdit) {
	      	echo '<table class="icopy_widget_admin_options">';
	      	echo '<tr><td colspan="2">';
	      	echo '<a class="icopy_widget_admin_options" target="_blank" id="icopy_widget_publish_article" href="' . admin_url() . 'post-new.php?icx_tag=' . urlencode(esc_attr($value['tag'])) . '" data-widgetid="' . esc_attr($args["widget_id"]) . '" data-articleid="' . esc_attr($value['id']) . '">Publish</a>';
	      	echo '<span>&nbsp;|&nbsp;</span>';
	      	echo '<a class="icopy_widget_admin_options" id="icopy_widget_remove_article" href="" data-widgetid="' . esc_attr($args["widget_id"]) . '" data-articleid="' . esc_attr($value['id']) . '">Remove</a>';
	      	echo '<span>&nbsp;|&nbsp;</span>';
	      	echo '<a class="icopy_widget_admin_options" id="icopy_widget_remove_publication" href="" data-widgetid="' . esc_attr($args["widget_id"]) . '" data-articleid="' . esc_attr($value['id']) . '" data-publicationid="' . $value['publicationId'] . '" data-publication="' . esc_attr($value['publication']) . '">Exclude all ' . esc_html($value['publication']) . '</a>';      	
	      	echo '</td></tr>';
	      	echo '</table>';
	      }
	      
	      echo '</li>';
			} 
		}
		
		echo '</ul>';
		
		echo $args['after_widget'];
		
		// Update the articles in the background if enough time has passed
		if (empty($instance['last_updated']) || ((time() - $instance['last_updated']) > (60 * 20))) {
			wp_schedule_single_event( time(), 'icopy_update_widget_articles', array($this->number, $instance, $this));
		}
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if (!wp_style_is( 'icopyright-widget-css-select2', $list = 'enqueued' )) {
			wp_enqueue_style('icopyright-widget-css-select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css');
		}
		
		if (!wp_script_is( 'icopyright-widget-js-select2', $list = 'enqueued' )) {
			wp_enqueue_script('icopyright-widget-js-select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.js');
		}
		
		if (!wp_script_is( 'icopyright-widget-js', $list = 'enqueued' )) {
			wp_enqueue_script('icopyright-widget-js', plugins_url('js/icopy-widget.js', __FILE__), array(), ICOPYRIGHT_VERSION);
		}
		
		wp_localize_script( 'icopyright-widget-js', 'publications_url', array('url' => getPortal() . '/searchPublications.act'));		
		
		$categories = icopy_get_categories();
		$featuredPubs = icopy_widget_get_featured();
		$title = array_key_exists('title', $instance) ? $instance['title'] : esc_html( 'Trending News');
		$numArticles = !empty( $instance['num_articles'] ) ? $instance['num_articles'] : esc_html( '5');
		$thumbWidth = !empty( $instance['thumb_width'] ) ? $instance['thumb_width'] : esc_html( '75');
		$hideTitle = array_key_exists('hide_title', $instance ) ? $instance['hide_title'] : 'checked';
		$showThumbnails = array_key_exists('show_thumbs', $instance ) ? $instance['show_thumbs'] : 'checked';
		$catId = !empty( $instance['category_id'] ) ? $instance['category_id'] : esc_html('0');
		$blacklist = !empty( $instance['blacklist'] ) ? $instance['blacklist'] : array();
		$whitelist = !empty( $instance['whitelist'] ) ? $instance['whitelist'] : array();
		$featuredlist = !empty( $instance['featuredlist'] ) ? $instance['featuredlist'] : array();
		$agreeTos = array_key_exists('agree_tos', $instance ) ? $instance['agree_tos'] : '';

		if ($agreeTos != 'checked') {
			echo '<p style="color: red;">Please accept Terms of Use at the bottom and click Save.</p>';
		}		
		
		?>
		
		<h4>Basic Options:</h4>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">Title:</label> 
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'category_id' ) ); ?>">Choose a category:</label><br/>
		<select class="icopy-widget-category" id="<?php echo esc_attr( $this->get_field_id( 'category_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'category_id' ) ); ?>">
		<?php foreach ($categories as &$value) {
			$valueName = $value["name"] == "Trending News" ? "All" : $value["name"];
		  echo '<option value="' . $value["id"] . '" ' . ($value["id"] == $catId ? "selected" : "") . '>' . $valueName . '</option>';
   	}?>		
		</select>
		</p>		

		<br/>
		<h4>Display articles from:</h4>		
		<p class="icopy_widget_select2">
		<label for="<?php echo esc_attr( $this->get_field_id( 'featuredlist' ) ); ?>">Choose from a featured publication or group:</label>
		<br/>
		<select multiple="multiple" style="width: 100%" class="icopy-widget-featuredlist" id="<?php echo esc_attr( $this->get_field_id( 'featuredlist' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'featuredlist' ) ); ?>[]">
		<option></option>
		  <?php 
		  if (!empty($featuredPubs)) {
		  	
		  	foreach ($featuredPubs as &$value) {
		  		$selected = in_array($value["id"], $featuredlist, TRUE) ? "selected" : "";
		  		if ($value["id"] == "parent") {
		  			echo '<optgroup label="' . esc_attr($value["name"]) . '">';
		  		} else {
						echo '<option value="' . esc_attr($value["id"]) . '" ' . $selected . '>' . esc_html($value["name"]) . '</option>';
		  		}
					
					if ($arr[0] == "parent") {
						echo '</optgroup>';
					}
		  	}
		  }
		  ?>
		</select>
		</p>					
				
				<h5 style="text-align: center;">OR</h5>		
		
		
		<p class="icopy_widget_select2">
		<label for="<?php echo esc_attr( $this->get_field_id( 'whitelist' ) ); ?>">Type to find specific publication:</label>
		<br/>
		<select multiple="multiple" style="width: 100%" class="icopy-widget-whitelist" id="<?php echo esc_attr( $this->get_field_id( 'whitelist' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'whitelist' ) ); ?>[]">
		  <?php 
		  if (!empty($whitelist)) {
		  	foreach ($whitelist as &$value) {
		  		$arr = explode("-", $value);
					echo '<option value="' . $value . '" selected>' . $arr[1] . '</option>';
		  	}
		  }
		  ?>
		</select>
		</p>					
		
		<br/>
		<h4>Never display articles from:</h4>		
		<p class="icopy_widget_select2">
		<label for="<?php echo esc_attr( $this->get_field_id( 'blacklist' ) ); ?>">Never display articles from:</label>
		<br/>
		<select multiple="multiple" style="width: 100%" class="icopy-widget-blacklist" id="<?php echo esc_attr( $this->get_field_id( 'blacklist' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'blacklist' ) ); ?>[]">

		  <?php 
		  if (!empty($blacklist)) {
		  	foreach ($blacklist as &$value) {
		  		$arr = explode("-", $value);
					echo '<option value="' . $value . '" selected>' . $arr[1] . '</option>';
		  	}
		  }
		  ?>
		</select>
		</p>		
		
		
		<br/>
		<h4>Display Options:</h4>		
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'num_articles' ) ); ?>">Number of articles to display:</label>
		<br/>
		<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'num_articles' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'num_articles' ) ); ?>" type="number" step="1" min="1" max="10" size="3" value="<?php echo esc_attr( $numArticles ); ?>">
		</p>		

		
		<p>
		<input class="icx_widget_checkbox" type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_thumbs' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_thumbs' ) ); ?>" type="text" <?php echo esc_attr( $showThumbnails ); ?> >
		<label for="<?php echo esc_attr( $this->get_field_id( 'show_thumbs' ) ); ?>">Show thumbnails:</label>
		<br/><br/>
		<label style="<?php echo $showThumbnails == 'checked' ? '' : 'opacity: .5;' ?>" for="<?php echo esc_attr( $this->get_field_id( 'thumb_width' ) ); ?>">Width of thumbnails:</label>
		<br/>
		<input <?php echo $showThumbnails == 'checked' ? '' : 'disabled' ?> id="<?php echo esc_attr( $this->get_field_id( 'thumb_width' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'thumb_width' ) ); ?>" type="text" value="<?php echo esc_attr( $thumbWidth ); ?>" size="3">		
		</p>				
		
		<p>
		<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'hide_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hide_title' ) ); ?>" <?php echo esc_attr( $hideTitle); ?> />
		<label for="<?php echo esc_attr( $this->get_field_id( 'hide_title' ) ); ?>">Hide title of article page</label>
		</p>				
		
				
		<p>
		<input required type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'agree_tos' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'agree_tos' ) ); ?>" <?php echo esc_attr( $agreeTos); ?> />
		By using this service, you agree to these <a target="_blank" href="<?php echo ICOPYRIGHT_URL; ?>/rights/termsOfUse.act?w=t">Terms of Use.</a>
		</p>							
							
		<script>
			if (typeof icopyInitSelects === "function") { 
				icopyInitSelects();
			} 
		</script>		
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		global $current_user;
		get_currentuserinfo();
				
		$instance = array();
		$instance['email'] = $current_user->user_email;
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		
		$numArticles = intval($new_instance['num_articles'] );
		if (!$numArticles) {
			$numArticles = 5;
		}
		
		$instance['num_articles'] = $numArticles;
		
		$thumbWidth = sanitize_text_field($new_instance['thumb_width']);
		$thumbWidth = intval($thumbWidth);
		if (!$thumbWidth) {
			$thumbWidth = 75;
		}
		
		$instance['thumb_width'] = $thumbWidth;
		$instance['show_thumbs'] = $new_instance['show_thumbs'] == 'on' ? 'checked' : sanitize_text_field($new_instance['show_thumbs']);
		$instance['hide_title'] = $new_instance['hide_title'] == 'on' ? 'checked' : sanitize_text_field($new_instance['hide_title']);
		$instance['agree_tos'] = $new_instance['agree_tos'] == 'on' ? 'checked' : sanitize_text_field($new_instance['agree_tos']);
		
		$categoryId = intval($new_instance['category_id']);
		if (!$categoryId) {
			$categoryId = 0;
		}
		$instance['category_id'] = $categoryId;
		
		
		if (!empty($new_instance['blacklist'])) {
			foreach ($new_instance['blacklist'] as &$value) {
				$arr = explode("-", $value);
				if ($arr && count($arr == 2)) {
					if (intval($arr[0])) {
						$instance['blacklist'][] = sanitize_text_field($value);
					}
				}
			}
		}
		
		if (!empty($new_instance['whitelist'])) {
			foreach ($new_instance['whitelist'] as &$value) {
				$arr = explode("-", $value);
				if ($arr && count($arr == 2)) {
					if (intval($arr[0])) {
						$instance['whitelist'][] = sanitize_text_field($value);
					}
				}
			}
		}
		
		if (!empty($new_instance['featuredlist'])) {
			foreach ($new_instance['featuredlist'] as &$value) {
				$arr = explode("-", $value);
				if ($arr && count($arr == 2) && strlen($arr[0]) == 1 && intval($arr[1])) {
					$instance['featuredlist'][] = sanitize_text_field($value);
				}
			}
		}
		
		if (!empty($old_instance['exclude_articles'])) {
			foreach ($old_instance['exclude_articles'] as &$value) {
				if (intval($value)) {
					$instance['exclude_articles'][] = $value;
				}
			}
		}
		
		$articles = icopy_get_widget_articles($instance);
		if ($articles !== null && !empty($articles)) {
			$instance['articles'] = $articles;
			$instance['last_updated'] = time();
		}
		
		// Create page if one does not exist
		if (empty ($old_instance['pageId'])) {
			$post = array(
					'post_status' => 'publish',
					'post_type' => 'page',
					'post_title' => 'Trending News',
					'post_content' => '[icopyright_widget_default class="repubhubembed" tag="' . $instance['articles'][0]['tag'] . '"]
<!-- This is the hosting page for a repubHub widget.  If you delete this page, the related widget will not work properly.  Headlines will link to repubHub rather than to your site. -->
					'
			);
			
			// Insert the post into the database
			$pageId = wp_insert_post( $post );
	
			if ($pageId) {
				$instance['pageId'] = $pageId;
			}
		} else {
			$instance['pageId'] = $old_instance['pageId'];
			
			$post = array(
					'ID' => $instance['pageId'],
					'post_type' => 'page',
					'post_content' => '[icopyright_widget_default class="repubhubembed" tag="' . $instance['articles'][0]['tag'] . '"]
<!-- This is the hosting page for a repubHub widget.  If you delete this page, the related widget will not work properly.  Headlines will link to repubHub rather than to your site. -->
					'
			);
			
			// Update the post with new title
			wp_update_post( $post );			
		}

		if ($instance['hide_title'] != NULL && $instance['hide_title'] == 'checked') {
			update_option('icopy_widget_hide_title_' . $instance['pageId'], "true");
		} else {
			delete_option('icopy_widget_hide_title_' . $instance['pageId']);
		}		
		
		return $instance;
	}

} // class Icopy_Widget

$icopy_url = "http:" . icopyright_static_server() . "/api/json/widget/";

function icopy_get_widget_articles($instance) {
	global $icopy_url;
	
	$params = array('numResults' => $instance['num_articles'] * 2, 'categoryId' => $instance['category_id'], 'email' => urlencode($instance['email']), 'siteUrl' => urlencode(get_home_url()));

	$url = add_query_arg($params, $icopy_url . 'articles' ) ;
	if (!empty($instance['blacklist'])) {
		foreach ($instance['blacklist'] as &$value) {
			$arr = explode("-", $value);
			$url .= '&blacklist=' . $arr[0];
		}
	}

	if (!empty($instance['whitelist'])) {
		foreach ($instance['whitelist'] as &$value) {
			$arr = explode("-", $value);
			$url .= '&whitelist=' . $arr[0];
		}
	}
	
	if (!empty($instance['featuredlist'])) {
		foreach ($instance['featuredlist'] as &$value) {
			$arr = explode("-", $value);
			if ($arr[0] == 'p') {
				$url .= '&whitelist=' . $arr[1];
			} else if ($arr[0] == 'g') {
				$url .= '&groups=' . $arr[1];
			}
		}
	}	
	
	if (!empty($instance['exclude_articles'])) {
		$keys = array_keys($instance['exclude_articles']);
		foreach ($keys as $key) {
			$url .= '&excludeArticleIds=' . $key;
		}
	}
	

	$r = wp_safe_remote_get($url, array( 'timeout' => 30));
	if ( ! is_wp_error( $r ) ) {
		$body = json_decode( wp_remote_retrieve_body( $r ), true);

		return($body);
	}

	return NULL;
}

function icopy_get_categories() {
	global $icopy_url;
	$r = wp_safe_remote_get($icopy_url . 'categories', array( 'timeout' => 30));
	if ( ! is_wp_error( $r ) ) {
		$body = json_decode( wp_remote_retrieve_body( $r ), true);

		return($body);
	}

	return NULL;
}

function icopy_widget_get_featured() {
	global $icopy_url;
	$r = wp_safe_remote_get($icopy_url . 'featured', array( 'timeout' => 30));
	if ( ! is_wp_error( $r ) ) {
		$body = json_decode( wp_remote_retrieve_body( $r ), true);

		return($body);
	}

	return NULL;
}

function icopy_update_widget_articles($widget_number, $instance, $icopy_widget) {
  // check if enough time has elapsed
  
  $articles = icopy_get_widget_articles($instance);
  if ($articles !== null && !empty($articles)) {
  	$instances = $icopy_widget->get_settings();
  	if ( array_key_exists( $widget_number, $instances ) ) {
  		$instances[$widget_number]['articles'] = $articles;
  		$instances[$widget_number]['last_updated'] = time();
  		
  		// Set the default article in the widget page
  		if (!empty ($instances[$widget_number]['pageId'])) {
  			$post = array(
  					'ID' => $instances[$widget_number]['pageId'],
  					'post_type' => 'page',
  					'post_content' => '[icopyright_widget_default class="repubhubembed" tag="' . $articles[0]['tag'] . '"]
<!-- This is the hosting page for a repubHub widget.  If you delete this page, the related widget will not work properly.  Headlines will link to repubHub rather than to your site. -->  					
  					'
  			);
  				
  			wp_update_post( $post );
  		} 	
  		
  		// Go through and clean up the exclude articles if it's been long enough (so that we don't have a long list being passed)
  		if (!empty($instances[$widget_number]['exclude_articles'])) {
				$instances[$widget_number]['exclude_articles'] = array_filter($instances[$widget_number]['exclude_articles'], "icopy_handle_array_filter"); 			
  		}  		
  		
  		$icopy_widget->save_settings($instances);
  	}  	
  }  
}

function icopy_handle_array_filter($e) {
	$keep = (time() - $e) < (60 * 60 * 24 * 5) ? true : false; //5 days
	return $keep; 
}

add_action( 'icopy_update_widget_articles','icopy_update_widget_articles', 10, 3);


add_action('wp_ajax_icopy_widget_delete_article', 'icopyright_widget_delete_article' );

function icopyright_widget_delete_article() {
	// Clear scheduled hooks so that we don't accidentally overwrite article exclude
	wp_clear_scheduled_hook( 'icopy_update_widget_articles' );	
	
	global $wp_registered_widgets;
	$icopy_widget = $wp_registered_widgets[sanitize_text_field($_POST["widgetId"])]["callback"][0];
	$widget_number = $wp_registered_widgets[sanitize_text_field($_POST["widgetId"])]["params"][0]['number'];
	
	$instances = $icopy_widget->get_settings();
	if ( array_key_exists( $widget_number, $instances ) ) {
		$arr = is_array($instances[$widget_number]['exclude_articles']) ? $instances[$widget_number]['exclude_articles'] : array();
		
		$articleId = sanitize_text_field($_POST["articleId"]);
		$articleId = intval($articleId);
		
		if ($articleId) {
			$arr[$articleId] = time();
		
			$instances[$widget_number]['exclude_articles'] = $arr;
			$icopy_widget->save_settings($instances);
		}		
		
	}

	icopy_update_widget_articles($widget_number, $instances[$widget_number], $icopy_widget);
		
	exit();
}


add_action('wp_ajax_icopy_widget_exclude_publication', 'icopy_widget_exclude_publication' );

function icopy_widget_exclude_publication() {
	// Clear scheduled hooks so that we don't accidentally overwrite our publication exclude
	wp_clear_scheduled_hook( 'icopy_update_widget_articles' );
	
	global $wp_registered_widgets;
	$icopy_widget = $wp_registered_widgets[sanitize_text_field($_POST["widgetId"])]["callback"][0];
	$widget_number = $wp_registered_widgets[sanitize_text_field($_POST["widgetId"])]["params"][0]['number'];
	
	$instances = $icopy_widget->get_settings();
	if ( array_key_exists( $widget_number, $instances ) ) {
		$pubId = sanitize_text_field($_POST["publicationId"]);
		$pubId = intval($pubId);
		
		if ($pubId) {
			$instances[$widget_number]['blacklist'][] = $pubId . "-" . sanitize_text_field($_POST["publication"]);
			$icopy_widget->save_settings($instances);
		}		
	}

	icopy_update_widget_articles($widget_number, $instances[$widget_number], $icopy_widget);

	exit();
}
