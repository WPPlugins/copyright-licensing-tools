<?php
add_action('init', 'icopyright_wp_feed_add_feed');

function icopyright_wp_feed_add_feed() {
  add_feed('icopyright_feed', 'icopyright_wp_feed_emit_feed');
}

/**
 * Feed function to display data from a post in the format expected by iCopyright's servers
 */
function icopyright_wp_feed_emit_feed() {
  get_feed_xml(true);
}

function get_feed_xml($applyFilters) {
// Load up the post
  $id = $_GET['id'];

  // Is this a heartbeat check?
  if (!empty($id) && strcasecmp($id, "heartbeat") == 0) {
    echo("icopyright-useragent:".ICOPYRIGHT_USERAGENT);
    return;
  }

  // Validate that the id is a number.
  if (!is_numeric($id)) {
    status_header(404);
    die();
  }
  $feed_post = get_post($id);

  // If not published, emit nothing
  if ($feed_post->post_status != 'publish') {
    status_header(403);
    die();
  }

  // If article doesn't pass basic filters do not display it
  if(!icopyright_post_passes_filters($id)) {
    status_header(403);
    die();
  }

  // Author name comes from the display name; allow custom author byline override
  $icopyright_author_alias = get_post_meta($id, 'author_alias', $single = TRUE);
  if ($icopyright_author_alias != NULL && $icopyright_author_alias != '') {
  	$icx_author = $icopyright_author_alias; 
  } else {
	  $user = new WP_User($feed_post->post_author);
	  $icx_author = $user->data->display_name;
	  if (function_exists('custom_author_byline')) {
	    $icx_author = custom_author_byline($icx_author);
	  }
  }

  // get copyright date and year based on the date the content was published
  $publish_date = $feed_post->post_date;
  $year = mysql2date('Y', $publish_date);
  $icx_copyright = $year . " " . get_bloginfo();
  $icx_pubyear = $year;
  // get publication date
  $icx_pubdate = mysql2date('F j, Y', $publish_date);

  // Headline is obviously the title
  $icx_headline = $feed_post->post_title;

  //get story from database
  //add in <br> to format content, so that no break tags are inserting during processing of shortcodes!
  $icx_story_raw = nl2br($feed_post->post_content);
  
  $icx_story_raw = str_replace("[icopyright_one_button_toolbar]","", $icx_story_raw);
  $icx_story_raw = str_replace("[icopyright one button toolbar]","", $icx_story_raw);
  
  $icx_story_raw = str_replace("[icopyright_horizontal_toolbar]","", $icx_story_raw);
  $icx_story_raw = str_replace("[icopyright horizontal toolbar]","", $icx_story_raw);
  
  $icx_story_raw = str_replace("[icopyright_vertical_toolbar]","", $icx_story_raw);
  $icx_story_raw = str_replace("[icopyright vertical toolbar]","", $icx_story_raw);
  
  $icx_story_raw = str_replace("[interactive_copyright_notice]","", $icx_story_raw);
  $icx_story_raw = str_replace("[interactive copyright notice]","", $icx_story_raw);
  
  $icx_story = '';
  if ($applyFilters) {
	  $icx_story = apply_filters('the_content', $icx_story_raw);
  } else {
  	$icx_story = '<p>' . $icx_story_raw . '</p>';
  }
  
  $icx_excerpt = $feed_post->post_excerpt;
  $icx_featured_image = get_the_post_thumbnail($id, 'medium', array("style" => "float: left; margin: 0 10px 10px 0;"));

	$doc2 = new DOMDocument();
	libxml_use_internal_errors(true);
	$doc2->loadHTML($icx_story);
	libxml_clear_errors();
	$xpath2 = new DOMXpath($doc2);
	$imgs2 = $xpath2->query("//img");	
		  
  if ($icx_featured_image != '') {
		$doc1 = new DOMDocument();
		$doc1->loadHTML($icx_featured_image);
		$xpath1 = new DOMXpath($doc1);
		$imgs1 = $xpath1->query("//img");
		$featured_image_src = '';
		$featured_image_alt = '';
		$img = $imgs1->item(0);
		
		if ($img) {
			$featured_image_src = $img->getAttribute("src");
			$featured_image_alt = $img->getAttribute("alt");	
		}
			
		$isIncluded = false;
	
		if ($featured_image_src != '') {
		  // Split the string in case there is a query string (i.e imageName.jpg?resize=true)
	    $featured_image_src_arr = explode("?", $featured_image_src);

		  if ($featured_image_src_arr && count($featured_image_src_arr) > 0) {
		    $featured_image_src = $featured_image_src_arr[0];
		  }

			// See if the same image as the featured image is inserted into the post content.
			// If so, don't include the featured image.

			for ($i=0; $i < $imgs2->length; $i++) {
				$img = $imgs2->item($i);
				$src = $img->getAttribute("src");
			  $alt = $img->getAttribute("alt");
			  
			  $src_arr = explode("?", $src);
			  
			  if ($src_arr && count($src_arr) > 0) {
			    $src = $src_arr[0];
			  }

				if (($src && $featured_image_src == $src) || ($alt && $featured_image_alt && $alt == $featured_image_alt)) {
					$isIncluded = true;
					break;
				} 
			}
		}					
		
		if (!$isIncluded) {
	    $icx_story = "<p>".$icx_featured_image."</p>" . $icx_story;
	  }
  }
  
	// Try and align the images correctly.  Wordpress uses class alignright/left/center
	// when aligning an image		
	for ($i=0; $i < $imgs2->length; $i++) {
		$img = $imgs2->item($i);
		$src = $img->getAttribute("src");

		if (!$img->hasAttribute("style")) {
			$class_attr = $img->getAttribute("class");
			$class_attr_arr = explode(" ", $class_attr);

			// If the user aligned the image, then WP will use its built-in class of 
			// alignright, alignleft, aligncenter, and alignnone.  If one of these classes
			// are present, then insert align attribute into the image string
			if ($class_attr_arr && count($class_attr_arr) > 0) {
				$align = null;
				foreach ($class_attr_arr as $value) {
					if ($value == 'alignright') {
						$align = "right";
						break;
					} else if ($value == 'alignleft' || $value == 'alignnone') {
						$align = "left";
						break;
					} else if ($value == 'aligncenter') {
						$align = "center";
						break;
					}
				}
				
				if ($align != null) {
					$pos = strpos($icx_story, "src=\"" . $src . "\"");
					if ($pos) {
						$icx_story = substr_replace($icx_story, " align=\"" . $align . "\" ", $pos, 0);
					}
				}
			}
		}
	}  
  
  //get url
  $icx_url = get_permalink($id);

  // get category
  $category = get_the_category($id);
  $icx_section = '';
  foreach ($category as $cat) {
  	$icx_section_raw = $cat->cat_name;  
  	
  	if (strcasecmp($icx_section_raw, 'uncategorized') != 0) {
			if ($icx_section != '') {
				$icx_section = $icx_section . '|';  // Delimiter
			}

			$icx_section = $icx_section . $icx_section_raw;
		}
  }
  

  // Construct and emit the XML feed output. Sanitation happens iCopyright-serverside
  header('Content-Type: text/xml');
  $xml  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  $xml .= "<icx>\n";
  $xml .= "  <icx_authors>$icx_author</icx_authors>\n";
  $xml .= "  <icx_copyright>$icx_copyright</icx_copyright>\n";
  $xml .= "  <icx_deckheader>$icx_excerpt</icx_deckheader>\n";
  $xml .= "  <icx_headline>$icx_headline</icx_headline>\n";
  $xml .= "  <icx_pubdate>$icx_pubdate</icx_pubdate>\n";
  $xml .= "  <icx_pubyear>$icx_pubyear</icx_pubyear>\n";
  $xml .= "  <icx_section>$icx_section</icx_section>\n";
  $xml .= "  <icx_story>\n$icx_story\n  </icx_story>\n";
  $xml .= "<icx_url>$icx_url</icx_url>\n";
  $xml .= "</icx>";
  echo $xml;

}
?>