<?php
ini_set('memory_limit', '1024M');
$location = $_SERVER['DOCUMENT_ROOT'];
require_once ($location . '/wp-load.php');


global $wpdb;
global $wp_roles;

$errorMessage = null;

$success = false;
$args = array ('hide_empty' => 0);
$systemCategories = get_categories($args);
if (!empty($_POST["submitted"])) {
	$author_excludes = $_POST["icopyright_authors"];
	$category_excludes = $_POST["icopyright_exclude_categories"];
  if ($author_excludes == NULL) {
  	$author_excludes = "";
  }
  
  $excludedCategoryNames = array();
  if ($category_excludes != NULL) {
  	if ($systemCategories != NULL && count($systemCategories) > 0) {
  		foreach ($systemCategories as $cat) {
  			if (!empty($category_excludes) && in_array($cat->term_id, $category_excludes)) {
  				$excludedCategoryNames[] = $cat->name;
  			}
  		}
  	}  	
  } else {
  	$category_excludes = "";
  }
    
	$user_agent = ICOPYRIGHT_USERAGENT;
	$email = get_option('icopyright_conductor_email');
	$password = get_option('icopyright_conductor_password');
	$pub_id_no = get_option('icopyright_pub_id');
	$res = icopyright_post_publication_categories($pub_id_no, $excludedCategoryNames, $author_excludes, $user_agent, $email, $password);

	if (icopyright_check_response($res) != TRUE) {
		$showSuccess = FALSE;
		// The update failed; let's pull out the errors and report them
		$xml = @simplexml_load_string($res->response);
	
		if (is_object($xml)) {
			$errorMessage = '<h4 class="errors">Your settings were not saved due to the following errors: <br/>';
			foreach ($xml->status->messages as $m) {
				$errorMessage = $errorMessage . '&bull; ' . $m->message. '<br/>';
			}
			$errorMessage = $errorMessage . '</h4>';			
		} else {
			$errorMessage = '<div class="errors">Your settings were not saved due to the following errors:<br/><br/>'.$res->response.'</div>';
		}
	} else {
		// success...update option
		update_option("icopyright_authors", $author_excludes);
		update_option("icopyright_exclude_categories", $category_excludes);
		$success = true;
	}	
}

icopyright_update_excludes();

$selectedCategories = get_option('icopyright_exclude_categories', array());

$selectedAuthors = get_option('icopyright_authors', array());
$selectedAuthorsTrim = array();

if (is_array($selectedAuthors)) {
	foreach($selectedAuthors as $sa) {
		// gotta trim because sometimes there is a leading whitespace for some reason
		$selectedAuthors[$sa] = trim($sa);
	}
}


echo '<html>';
?>
<head>
  <style>
    body {background-color: #F1F1F1; font-family: "Open Sans",sans-serif; font-size: 10pt;}
    input[type="submit"] {text-align: center; padding: 10px !important; margin-top: 10px !important;}
    .errors {background-color: #E88A8A; border: 1px solid black;  padding: 15px;}
    .success {background-color: #B8DEAE; border: 1px solid black;  padding: 15px;}
    h4 {font-family: "Open Sans",sans-serif !important; font-size: 10pt !important;}
    h5 {font-family: "Open Sans",sans-serif !important; font-size: 8pt !important;}
  </style>
  
  <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
  <script src="//code.jquery.com/jquery-1.10.2.js"></script>
  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>  
  
  <script>

  $(document).ready(function () {
	  $( "#author-exclude-accordion" ).accordion({
	      collapsible: true,
	      active: false,
	      heightStyle: "content",
	      header: "h3"
	  });	  

	  $( "button.exclude-toggle").click(function (event) { 
		  var parentDiv = $(this).parent();
		  var inputList = $(parentDiv).find("input");

		  if (inputList) {
		  	var firstElement = inputList.first();
		  	if (firstElement) {
					var checked = firstElement.prop("checked");
					inputList.each(function(){
					  $(this).prop("checked", checked == true ? false : true);	
					});
		  	}
		  }
		});

		$("form").submit(function(event) {
			$("input[type='submit']").prop('disabled', true);
			$("#icx_auth_spinner").show();
			document.body.scrollTop = document.documentElement.scrollTop = 0;
		});		
	});  

  </script>  
</head>

<?php 
echo '<body>';

echo '<img id="icx_auth_spinner" src="'.ICOPYRIGHT_PLUGIN_URL . '/images/animated-spinner.gif" style="display: none;"/>';
if ($success) {
	echo '<h4 class="success">Your settings have been saved.</h4>';
} else if ($errorMessage != null) {
	echo $errorMessage;
}

echo '<h4>Exclude Authors</h4>';
echo '<h5>Check the box of authors for which you don\'t have the right to sell licenses.  This will exclude their articles from the iCopyright services.  (Individual articles may be excluded on their Edit Post template.)</h5>';

echo '<form id="author-exclude-accordion" action="" method="POST">';
echo '<input type="hidden" name="submitted" value="true">';
foreach ( $wp_roles->roles as $key=>$value ) {
	if ($key == 'subscriber') continue; // We don't care about subscribers
	
	$results = $wpdb->get_results("SELECT $wpdb->users.ID, $wpdb->users.display_name FROM $wpdb->users INNER JOIN $wpdb->usermeta " .
			"ON $wpdb->users.ID = $wpdb->usermeta.user_id " .
			"WHERE $wpdb->usermeta.meta_key = '" . $wpdb->prefix . "capabilities' " .
			"AND $wpdb->usermeta.meta_value LIKE '%" . $key . "%' " .
			"ORDER BY $wpdb->users.display_name");

	if ($results && !empty($results)) {
		echo '<h3>'.$value["name"].'</h3>';
		echo '<div id="exclude-'.$key.'">';
	
	
		echo '<button type="button" class="exclude-toggle"><span style="font-size: 8pt;">Select/Unselect all</span></button>';
		foreach ($results as $user) {
			$checked = (is_array($selectedAuthors) && !empty($selectedAuthors) && in_array($user->display_name, $selectedAuthors) ? 'checked' : '');
			echo '<p>';
			echo '<input id="author_' . $user->display_name . '" type="checkbox" name="icopyright_authors[]" value="' . $user->display_name . '" ' . $checked . ' /><label style="margin-left: 5px;" for="author_' . $user->display_name . '">' . $user->display_name . '</label>';
			echo '</p>';
		}
		echo '</div>';
	}
}

echo '<p></p>';
echo '<h4>Exclude Categories</h4>';
echo '<h5>Check the box of categories for which you don\'t have the right to sell licenses.  This will exclude their articles from the iCopyright services.  (Individual articles may be excluded on their Edit Post template.)</h5>';
echo '<h3>Exclude Categories</h3>';
echo '<div id="icx-exclude-categories">';
echo '<button type="button" class="exclude-toggle"><span style="font-size: 8pt;">Select/Unselect all</span></button>';
foreach ($systemCategories as $cat) {
	$checked = (!empty($selectedCategories) && in_array($cat->term_id, $selectedCategories) ? 'checked' : '');
	echo '<p>';
	echo '<input id="cat_' . $cat->term_id . '" type="checkbox" name="icopyright_exclude_categories[]" value="' . $cat->term_id . '" ' . $checked . ' /><label style="margin-left: 5px;" for="cat_' . $cat->term_id . '">' . $cat->name . '</label>';
	echo '</p>';
}
echo '</div>';

echo "<input type='submit' value='Submit'/>";
echo '</form>';
echo '</body>';
echo '</html>';
