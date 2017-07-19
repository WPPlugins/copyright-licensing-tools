var icxTotalUnreadClips = 0;

function icxInitGlobalSettings() {
    jQuery("#icx_global_settings_wrap").click(function (event) {    	
    	  var url = '';
    	  if (jQuery("#icx_global_settings_wrap_inner").is(":visible")) {
      		url = icx_plugin_url.url + '/images/16_arrow_down.ico';
    	  } else {
    		url = icx_plugin_url.url + '/images/16_arrow_up.ico';
    	  }
    	  jQuery("#icx_global_settings_wrap").css("background-image", "url(" + url + ")");
    	  jQuery("#icx_global_settings_wrap_inner").fadeToggle(400);
    	  //jQuery("#icx_global_settings_wrap_inner").slideToggle(300);
      });
      
      jQuery("#icx_global_settings_form").submit(function (event) {
      	  jQuery("#icx_global_settings_overlay").show();
  		  var onFail = function(result, textStatus, errorThrown) {jQuery("#icx_global_settings_overlay").hide();};
  		  var formData = jQuery(this).serialize();
  	      jQuery.ajax({
  	          url : admin_ajax_url.url,
  	          type : "post",
  	          data : {action: "repubhub_update_global_settings", formValues: formData},
  	          error: onFail,
  	          success:function(result){
  	        	  jQuery("#icx_global_settings_wrap").trigger("click");
  	        	  jQuery("#icx_global_settings_overlay").hide();
  	        	  jQuery("#icx_status_message").html(result);
  	        	  icxInitFadeOut();
  	        	  
  	        	  jQuery("form.topic_refresh").each(function() {
  	        		  jQuery(this).trigger("submit");
  	        	  });
  	          }
  	      });
  	      event.preventDefault(); 
      });
      
      
      // This is for clicking anywhere on screen to hide global settings and edit search boxes
      jQuery(document).mouseup(function (e) {
    	  if (jQuery("#icx_global_settings_wrap_inner").is(":visible")) {
  		      var container = jQuery("#icx_global_settings_wrap_inner");
  		      var btn = jQuery("#icx_global_settings_wrap");
  		      
  		      if (!container.is(e.target) && !btn.is(e.target) // if the target of the click isn't the container...
  		          && container.has(e.target).length === 0) // ... nor a descendant of the container
  		      {
  		          btn.trigger("click");
  		      }
    	  }
    	  
    	  if (jQuery("#editTopic").is(":visible")) {
  		      var container = jQuery("#editTopic");
  		      
  		      if (!container.is(e.target)  // if the target of the click isn't the container...
  		          && container.has(e.target).length === 0) // ... nor a descendant of the container
  		      {
  		    	jQuery("#editTopic").fadeToggle(400);
  		      }
    	  }    	  
  	  });
      
      jQuery("h4.icx_global_setting_section_title").click(function (event) {
    	  var obj = jQuery(this);
    	  obj.next("div.icx_global_setting_section_data").slideToggle(200, function(){
    		  var url = '';
        	  if(jQuery(this).is(":visible")) {
        		  obj.css("border-bottom-left-radius", "0");
        		  obj.css("border-bottom-right-radius", "0");
        		  obj.css("background-color", "#fff");
        		  url = icx_plugin_url.url + '/images/bullet_down.ico';
        	  } else {
        		  obj.css("border-bottom-left-radius", "4px");
        		  obj.css("border-bottom-right-radius", "4px");
        		  obj.css("border-bottom", "1px solid #d3d3d3");
        		  obj.css("background-color", "#e6e6e6");
        		  url = icx_plugin_url.url + '/images/bullet_right.ico';
        	  }  
        	  
        	obj.css("background-image", "url(" + url + ")");
    	  });

      });	
}

function icxInitSearchSection() {
	if (typeof jQuery.fn.select2 == "function") {
		jQuery("#featuredPublicationSelect").select2({
			width: "resolve"
		});

		jQuery("#featuredPublicationSelectEdit").select2({
			placeholderOption: "first",
			width: "350px"
		});	
		
		jQuery("#featuredPublicationSelect").on("select2-opening", function(event) {
			jQuery("ul.select2-choices").addClass("icx_bottom_border_none");
		});
		
		jQuery("#featuredPublicationSelect").on("change", function(event) {
			if (event.val.length == 0) {
				jQuery("#featuredPublicationSelect").select2("val", "0");
			} else if (event.val.length >= 2) {
				if(event.val[0] == 0) {
					// Remove all publications
					event.val.splice(0, 1);
					jQuery("#featuredPublicationSelect").select2("val", event.val);
				}
			}
		});		

		jQuery("#featuredPublicationSelect").on("select2-close", function(event) {
			jQuery("ul.select2-choices").removeClass("icx_bottom_border_none");
		});			
	}
	
	jQuery(".icx_republish_advanced_btn").click(function (event) {
	    if (jQuery(".icx_republish_advanced_fields_front_page").is(":visible")) {
	      jQuery(".icx_republish_advanced_fields_front_page").hide();
	      jQuery(".icx_republish_advanced_btn").html("More Options");
	      // clear the form
	      jQuery("#icx_search_form div.icx_republish_advanced_fields_front_page input[type='text']").val('');
	    } else {
	      jQuery(".icx_republish_advanced_fields_front_page").show();
	      jQuery(".icx_republish_advanced_btn").html("Less Options");
	    }
	    event.preventDefault();
	  });	
	
	
	jQuery("#icx_search_form select[name='featuredPublicationString'], #icx_search_form select[name='dateFilter']").change(function(event) {
		jQuery("#icx_search_form input[name='page']").val('1');
		jQuery("#icx_search_form").trigger("submit");
	});
	
	jQuery("#icx_sortBy").on("change", function(event) {
	  jQuery("#icx_search_form input[name='page']").val('1');
	  jQuery("#icx_nav_tab_recent_headlines").click();
	  jQuery("#rh-spinner").show();
	  jQuery("div#icx_clips_for_topic_0").html('');
	  var selectedCat = jQuery(".icx-active-cat");
	  var catId = selectedCat ? selectedCat.html() : '';
	  
	  var action = "repubhub_recent_headlines";
	  var formData = jQuery("#icx_search_form").serialize();
	  if (catId != '') {
	    formData += "&categoryId=" + jQuery(this).data("id");
	    action = "repubhub_search";
	  }
	  
	  formData += "&sortBy=" + jQuery(this).val();

	  var onFail = function(result, textStatus, errorThrown) {};
	  jQuery.ajax({
	      url : admin_ajax_url.url,
	      type : "post",
	      data : {action: action, formValues: formData},
	      error: onFail,
	      success:function(result){
	    	jQuery("#rh-spinner").hide();
	    	jQuery("div#icx_clips_for_topic_0").html(result);
	      }
	  });	  
	 event.preventDefault();		
	});
	
	jQuery("#icx_search_form input.icx_add_btn").on("click", function (event) {
		jQuery("#icx_search_form input[name='page']").val('1');	
	});
	
	jQuery("#icx_search_form").on("submit", function (event){
		  jQuery("#icx_topic_search_results_inner").html("");
		  jQuery("#icx_nav_tab_search_results").html("Search Results");
		  jQuery("#icx_nav_tab_search_results").attr("style", "display: inline-block;");
		  jQuery("#icx_nav_tab_search_results").trigger("click");
		  jQuery("#icx_search_results_spinner").show();
		  
		  // Check if andWords is a quoted string.  If so, move it to Exact Phrase
		  var andWords = jQuery("#icx_and_words").val();
		  if (andWords && andWords.length > 0) {
			  var firstChar = andWords.charAt(0);
			  if (firstChar == '"' || firstChar == '&quot;') {
				  var lastChar = andWords.charAt(andWords.length -1);
				  if (lastChar == '"' || lastChar == '&quot;') {
					  jQuery("#icx_exact_words").val(andWords.substring(1, andWords.length - 1));
					  jQuery("#icx_and_words").val('');
					  jQuery(".icx_republish_advanced_fields_front_page").show();
				      jQuery(".icx_republish_advanced_btn").html("Less Options");					  
				  }
			  }
		  }
		  
		  var onFail = function(result, textStatus, errorThrown) {};
		  var formData = jQuery(this).serialize();
	      jQuery.ajax({
	          url : admin_ajax_url.url,
	          type : "post",
	          data : {action: "repubhub_search", formValues: formData},
	          error: onFail,
	          success:function(result){
	        	     jQuery("#icx_search_results_spinner").hide();
	        	     jQuery("#icx_topic_search_results_inner").html(result);
	        	  }
	      });
	      
	      event.preventDefault();
	  });	
}

function icxInitContentCategorySection() {
  jQuery("#icx_article_categories td").click(function (event) {
	  if (jQuery(this).is(":hover")) {
		  jQuery("#icx_search_form input[name='page']").val('1');
	  }
	  
	  jQuery("#icx_article_categories td").removeClass("icx-active-cat");
	  jQuery("#icx_nav_tab_recent_headlines").click();
	  jQuery("#rh-spinner").show();
	  
	  var text = jQuery(this).html();
	  var action = "repubhub_search";
	  if (text == 'All'){
		text = 'Recent Headlines';
		action = "repubhub_recent_headlines";
	  } else {
		jQuery(this).addClass("icx-active-cat");
	  }
	  
	  jQuery("#icx_nav_tab_recent_headlines").html(text);
	  jQuery("div#icx_clips_for_topic_0").html('');
	  var onFail = function(result, textStatus, errorThrown) {};
	  var formData = jQuery("#icx_search_form").serialize() + "&categoryId=" + jQuery(this).data("id");
      jQuery.ajax({
          url : admin_ajax_url.url,
          type : "post",
          data : {action: action, formValues: formData},
          error: onFail,
          success:function(result){
        	jQuery("#rh-spinner").hide();
        	jQuery("div#icx_clips_for_topic_0").html(result);
          }
      });	  
     event.preventDefault();
  });	
}


function icxInitTabsSection() {
  jQuery(".icx_nav_tab").click(function (event) {
	    jQuery(".icx_topic").hide();
	    jQuery("#icx_topic_" + jQuery(this).attr("href")).show();

	    jQuery(".icx_nav_tab").removeClass("nav-tab-active");
	    jQuery("#icx_nav_tab_" + jQuery(this).attr("href")).addClass("nav-tab-active");
	    
	    jQuery(".icx_nav_tab").removeClass("icx-nav-tab-active");
	    jQuery("#icx_nav_tab_" + jQuery(this).attr("href")).addClass("icx-nav-tab-active");

	    icopyright_update_unread_notification(jQuery(this).attr("href"));
	    
	    event.preventDefault();
	  });

	  jQuery("div.icx_repubhub_clips").each(function () {
	    topic = jQuery(this).attr("id").replace(/\D/g,'');
	    div = "div#icx_clips_for_topic_" + topic;
	    var onSuccess = (function(divinclosure, topicinclosure) {
	      return function(data){
	    	  jQuery(divinclosure).html(data);
		        var unread = jQuery("#num_topics_"+topicinclosure).data("num");
		        jQuery("img#img_spinner_"+topicinclosure).hide();
		        if (unread) {
		        	icopyright_update_unread_notification_message(jQuery("#icx_nav_tab_"+topicinclosure+ " span.icx_unread_tab_count"), unread, true);
		        	unread = '' + unread;
		        	unread = parseInt(unread.replace('+', ''));
		        	icxTotalUnreadClips += unread;

		        	// Update the total in the db
				      jQuery.ajax({
				          url : admin_ajax_url.url,
				          type : "post",
				          data : {action: "repubhub_update_unread_total", icxTotalUnreadClips : icxTotalUnreadClips},
				          success:function(result){
				              icopyright_update_unread_notification_message(jQuery("#wp-admin-bar-republish-1 a"), icxTotalUnreadClips, false);
				              icopyright_update_unread_notification_message(jQuery("#wp-admin-bar-republish-2 a").first(), icxTotalUnreadClips, false);
				              icopyright_update_unread_notification_message(jQuery("#wp-admin-bar-republish-4 a").first(), icxTotalUnreadClips, false);
				              icopyright_update_unread_notification_message(jQuery("li.current a.current[href='edit.php?page=repubhub-republish']"), icxTotalUnreadClips, true);
				          }
				      });		        	
		        }
		        
		        if (divinclosure == "div#icx_clips_for_topic_0")
		        	jQuery("#rh-spinner").hide();
		        if (jQuery("#icx_nav_tab_"+topicinclosure).hasClass('nav-tab-active'))
		            icopyright_update_unread_notification(topicinclosure);

	      };
	    })(div, topic);
	    
	    var onFail = (function(divinclosure, topicinclosure) {
		      return function(data){
		    	  jQuery(divinclosure).html(data);
			        jQuery("img#img_spinner_"+topicinclosure).hide();
		      };
		    })(div, topic);	    
	    
	    
	    if (topic == 0) {
	        jQuery.ajax({
	            url : admin_ajax_url.url,
	            type : "get",
	            data : {action: "repubhub_recent_headlines"},
	            success: onSuccess
	        });
	    } else {
	        jQuery.ajax({
	          url : admin_ajax_url.url,
	          type : "get",
	          data : {action: "repubhub_clips", loc: jQuery(this).data("loc"), topicid: jQuery(this).data("topicid")},
	          error: onFail,
	          success: onSuccess
	        });
	    }
	  });
	  
	  jQuery(document).on("submit", "#icx_form_save_search", function(event) {
		  jQuery("input[value='Save Search']").hide();
		  jQuery("#icx_search_results_spinner").show();
		  
		  var dateFilterFromSearch = jQuery("#icx_search_form select[name='dateFilter']").val();
		  jQuery("#icx_form_save_search input[name='dateFilter']").val(dateFilterFromSearch);

		  event.preventDefault();
		  var onFail = function(result, textStatus, errorThrown) {};
		  var formData = jQuery(this).serialize();
	      jQuery.ajax({
	          url : admin_ajax_url.url,
	          type : "post",
	          data : {action: "repubhub_add_topic", formValues: formData},
	          error: onFail,
	          success:function(result){
	        	var json = JSON.parse(result);
	        	var thisResultsDiv = jQuery("#icx_topic_search_results");
	        	if (json.error && json.error == 'true') {
	        		thisResultsDiv.prepend(json.message);
	        	} else {
		        	
		        	// Clone the search results tab, and set our current tab to be a topic tab, and no longer a search results tab
		            var thisTab = jQuery("#icx_nav_tab_search_results");
		        	var cloneTab = thisTab.clone(true);
		        	cloneTab.hide();
		        	cloneTab.removeClass("icx-nav-tab-active");
		        	cloneTab.removeClass("nav-tab-active");
		        	
		        	
		        	thisTab.attr("id", "icx_nav_tab_"+json.id);
		        	thisTab.attr("href", json.id);
		        	thisTab.removeClass("nav-tab-search");
		        	
		        	var thisTabHtml = '<span class="icx_unread_tab_count">'+json.friendlyString+'</span>';
		        	thisTabHtml += '<img id="img_spinner_'+json.id+'" class="icx_spinner_tab" src="'+icx_plugin_url.url+'/images/ajax-loader4.gif" style="display: none;">';
		        	thisTabHtml += '<div class="icx_topic_delete_btn" data-topicid="'+json.id+'">X</div>';
		        	thisTab.html(thisTabHtml);
		        	
		        	jQuery("#icx_nav_tab_recent_headlines").after(cloneTab);
		        	
		        	// Change our search results div to be a topic div
		        	
		        	var cloneResultsDiv = thisResultsDiv.clone(true);
		        	cloneResultsDiv.hide();
		        		        	
		        	jQuery("#icx_search_results_spinner", thisResultsDiv).remove();
		        	thisResultsDiv.attr("id", "icx_topic_"+json.id);
		        	var clips = jQuery("#icx_topic_search_results_inner", thisResultsDiv);
		        	clips.attr("id", "icx_clips_for_topic_"+json.id);
		        	clips.addClass("icx_repubhub_clips");
		        	clips.attr("data-topicid", json.id);
		        	jQuery(".icx_topic_controls", clips).remove();
		        	
		        	var andWords = !jQuery.isEmptyObject(json.andWords) ? json.andWords : '';
		        	var exactPhrase = !jQuery.isEmptyObject(json.exactPhrase) ? json.exactPhrase : '';
		        	var orWords = !jQuery.isEmptyObject(json.orWords) ? json.orWords : '';
		        	var notWords = !jQuery.isEmptyObject(json.notWords) ? json.notWords : '';
		        	var fps = !jQuery.isEmptyObject(json.featuredPublicationString) ? json.featuredPublicationString : '';
		        	var author = !jQuery.isEmptyObject(json.author) ? json.author : '';
		        	var publicationName = !jQuery.isEmptyObject(json.publicationName) ? json.publicationName : '';
		        	var publicationOrGroupName = !jQuery.isEmptyObject(json.publicationOrGroupName) ? json.publicationOrGroupName : '';
		        	
		        	var resultsDivHtml = '<div class="icx_topic_title">';
		        	if (andWords.length > 0) {
		        		resultsDivHtml += 'With all the words: <strong>' + andWords + '</strong><br/>';
		        	}
		        	if (exactPhrase.length > 0) {
		        		resultsDivHtml += 'With the exact phrase: <strong>' + exactPhrase + '</strong><br/>';
		        	}	        		
		        	if (orWords.length > 0) {
		        		resultsDivHtml += 'With at least one of the words: <strong>' + orWords + '</strong><br/>';
		        	}
		        	if (notWords.length > 0) {
		        		resultsDivHtml += 'Without the words: <strong>' + notWords + '</strong><br/>';
		        	}
		        	if (publicationOrGroupName.length > 0) {
		        		resultsDivHtml += 'Publication(s): <strong>' + publicationOrGroupName + '</strong><br/>';
		        	}		        	
		        	if (author.length) {
		        		resultsDivHtml += 'Author: <strong>' + author + '</strong><br/>';
		        	}		        	
		        	if (json.friendlyDateFilter) {
		        		resultsDivHtml += 'Date range: <strong>' + json.friendlyDateFilter + '</strong><br/>';
		        	}
		        	if (json.friendlyFrequency) {
		        		resultsDivHtml += 'Email me: <strong>' + json.friendlyFrequency + '</strong><br/>';
		        	}	        	
		        	resultsDivHtml += '</div>';
		        	
		        	resultsDivHtml += '<div class="icx_topic_controls">';
		        	resultsDivHtml += '<form style="display: inline;" class="topic_refresh" method="get" action="">';
			        resultsDivHtml += '<input type="hidden" name="topicId" value="'+json.id+'">';
			        resultsDivHtml += '<button class="icx_refresh_btn icx_btn" type="submit" title="Refresh this search">Refresh</button></form>';
			        resultsDivHtml += '<form style="display: inline;" id="icx_form_edit_topic_'+json.id+'" data-topicid="'+json.id+'" method="get" action="">';
			        resultsDivHtml += '<input type="hidden" name="action" value="edit">';
			        resultsDivHtml += '<input type="hidden" name="page" value="repubhub-republish">';
			        resultsDivHtml += '<input type="hidden" name="topicId" value="'+json.id+'">';
			        resultsDivHtml += '<input type="hidden" name="andWords" value="'+andWords+'">';
			        resultsDivHtml += '<input type="hidden" name="exactPhrase" value="'+exactPhrase+'">';
			        resultsDivHtml += '<input type="hidden" name="orWords" value="'+orWords+'">';
			        resultsDivHtml += '<input type="hidden" name="notWords" value="'+notWords+'">';
			        resultsDivHtml += '<input type="hidden" name="frequency" value="'+json.frequency+'">';
			        resultsDivHtml += '<input type="hidden" name="featuredPublicationString" value="'+fps+'">';
			        resultsDivHtml += '<input type="hidden" name="author" value="'+author+'">';
			        resultsDivHtml += '<input type="hidden" name="publicationName" value="'+publicationName+'">';
			        resultsDivHtml += '<input type="hidden" name="dateFilter" value="'+json.dateFilter+'">';
			        resultsDivHtml += '<input type="hidden" name="allowRss" value="false">';
			        resultsDivHtml += '<button class="icx_btn icx_edit_btn" type="submit" title="Edit Search">Edit Search</button></form>';
			        
			        resultsDivHtml += '</div><div class="icx_clear"></div>';
			        
			        clips.before(resultsDivHtml);
			        thisResultsDiv.before(cloneResultsDiv);
			        
			        thisResultsDiv.prepend(json.message);
			        
			        // Init delete btn
			        var deleteBtn = jQuery(".icx_topic_delete_btn", thisTab);
			        icxInitDeleteButton(deleteBtn);
			        
			        // Change next/prev btns
			        var page = parseInt(jQuery("#icx_search_form input[name='page']").val());
			        var nextBtn = jQuery("a.icx_pager[href='next']", clips);
			        var prevBtn = jQuery("a.icx_pager[href='prev']", clips);
			        
			        if (nextBtn) {
			        	nextBtn.removeClass("icx_pager");
			        	nextBtn.addClass("icx_pager_topic");
			        	nextBtn.attr("href", page + 1);
			        }
			        
			        if (prevBtn) {
			        	prevBtn.removeClass("icx_pager");
			        	prevBtn.addClass("icx_pager_topic");
			        	prevBtn.attr("href", page - 1);
			        }			        
	        	}
		        
        	    jQuery("#icx_search_results_spinner").hide();      	     
        	    icxInitFadeOut();
	          }
	      });
	      event.preventDefault();
	  });  
	  jQuery(document).on("submit", "[id^=icx_form_edit_topic_]", function(event) {
		  var formData = jQuery(this).serialize();
		  // Set the values of the edit form
		  jQuery("#editTopic").fadeToggle(400);
		  jQuery("#icx_republish_form input[name='topicId']").val(jQuery("input[name='topicId']", this).val());
		  jQuery("#icx_republish_form input[name='andWords']").val(jQuery("input[name='andWords']", this).val());
		  jQuery("#icx_republish_form input[name='exactPhrase']").val(jQuery("input[name='exactPhrase']", this).val());
		  jQuery("#icx_republish_form input[name='orWords']").val(jQuery("input[name='orWords']", this).val());
		  jQuery("#icx_republish_form input[name='notWords']").val(jQuery("input[name='notWords']", this).val());
		  jQuery("#icx_republish_form input[name='author']").val(jQuery("input[name='author']", this).val());
		  jQuery("#icx_republish_form input[name='publicationName']").val(jQuery("input[name='publicationName']", this).val());
		  
		  var allowRss = jQuery("input[name='allowRss']", this).val();
		  if (allowRss != null && allowRss == 'true') {
		  	jQuery("#icx_republish_form input[name='allowRss']").attr("checked", "");
	  	  } else {
	  		jQuery("#icx_republish_form input[name='allowRss']").removeAttr("checked"); 
	  	  }
		  
		  jQuery("#icx_republish_form select[name='frequency']").val(jQuery("input[name='frequency']", this).val());
		  
		  var fps = jQuery("input[name='featuredPublicationString']", this).val();
		  if (fps != null & fps.length > 0) {
			  var arr = fps.split(",");
			  jQuery("#featuredPublicationSelectEdit").select2("val", arr);
		  } else {
			  jQuery("#featuredPublicationSelectEdit").select2("val", "");
		  }
		  
		  jQuery("#icx_republish_form select[name='dateFilter']").val(jQuery("input[name='dateFilter']", this).val());
		  
		  event.preventDefault();
	  });
	  
	  jQuery("#icx_republish_form").submit(function(event) {
		  var topicId = jQuery("input[name='topicId']", this).val();
		  jQuery(".editError").hide();
      	  jQuery("#icx_edit_topic_overlay").show();
  		  var onFail = function(result, textStatus, errorThrown) {jQuery("#icx_edit_topic_overlay").hide();};
		  var formData = jQuery(this).serialize();
	      jQuery.ajax({
	          url : admin_ajax_url.url,
	          type : "post",
	          data : {action: "repubhub_edit_topic", formValues: formData},
	          error: onFail,
	          success:function(result){
	        	  jQuery("#icx_edit_topic_overlay").hide();
	        	  
	        	  var json = JSON.parse(result);
	        	  
	        	  var editError = json.edit_error;
	        	  var success = json.success;
	        	  var error = json.error;
	        	  if (editError && editError.length > 0) {
	        	    jQuery(".editError").html(editError);
	        	    jQuery(".editError").show();
	        	  } else {
	        		  if (error && error.length > 0) {
	        			jQuery("#icx_status_message").html(error);
	        		  } else {
	        			  jQuery("#icx_status_message").html(success);
	        			  
	       			      // Update the Edit Search form
	       			      var editForm = jQuery("#icx_form_edit_topic_"+topicId);
	       			      jQuery("input[name='topicId']", editForm).val(jQuery("#icx_republish_form input[name='topicId']").val());
	       			      jQuery("input[name='andWords']", editForm).val(jQuery("#icx_republish_form input[name='andWords']").val());
	       			      jQuery("input[name='exactPhrase']", editForm).val(jQuery("#icx_republish_form input[name='exactPhrase']").val());
	       			      jQuery("input[name='orWords']", editForm).val(jQuery("#icx_republish_form input[name='orWords']").val());
		       			  jQuery("input[name='notWords']", editForm).val(jQuery("#icx_republish_form input[name='notWords']").val());
		       			  jQuery("input[name='author']", editForm).val(jQuery("#icx_republish_form input[name='author']").val());
		       			  jQuery("input[name='publicationName']", editForm).val(jQuery("#icx_republish_form input[name='publicationName']").val());	      
	       			  
		       			  var allowRss = jQuery("#icx_republish_form input[name='allowRss']").attr("checked");
		       			  if (allowRss != null && allowRss.length > 0) {
		       			  	jQuery("input[name='allowRss']", editForm).val("true");
		       		  	  }else {
		       		  		jQuery("input[name='allowRss']", editForm).val("");
		       		  	  }
		       			  
		       			  jQuery("input[name='frequency']", editForm).val(jQuery("#icx_republish_form select[name='frequency']").val());
		       			  jQuery("input[name='featuredPublicationString']", editForm).val()
		       			  jQuery("input[name='featuredPublicationString']", editForm).val(jQuery("#featuredPublicationSelectEdit").val());
		       			  jQuery("input[name='dateFilter']", editForm).val(jQuery("#icx_republish_form select[name='dateFilter']").val());	  
		       			  
		       			  var topicFriendlyString = json.topic.friendlyString;
		       			  if (topicFriendlyString.length > 20) {
		       				  topicFriendlyString = topicFriendlyString.substring(0, 19);
		       			  }
		       			  jQuery("a#icx_nav_tab_"+topicId+" span.icx_unread_tab_count").html(topicFriendlyString);
		       			  
		       			  var resultsDivHtml = '';
				        	if (json.topic.andWords != null && json.topic.andWords.length > 0) {
				        		resultsDivHtml += 'With all the words: <strong>' + json.topic.andWords + '</strong><br/>';
				        	}
				        	if (json.topic.exactPhrase != null && json.topic.exactPhrase.length > 0) {
				        		resultsDivHtml += 'With the exact phrase: <strong>' + json.topic.exactPhrase + '</strong><br/>';
				        	}	        		
				        	if (json.topic.orWords != null && json.topic.orWords.length > 0) {
				        		resultsDivHtml += 'With at least one of the words: <strong>' + json.topic.orWords + '</strong><br/>';
				        	}
				        	if (json.topic.notWords != null && json.topic.notWords.length > 0) {
				        		resultsDivHtml += 'Without the words: <strong>' + json.topic.notWords + '</strong><br/>';
				        	}
				        	if (json.topic.publicationOrGroupName != null && json.topic.publicationOrGroupName.length > 0) {
				        		resultsDivHtml += 'Publication(s): <strong>' + json.topic.publicationOrGroupName + '</strong><br/>';
				        	}		        	
				        	if (json.topic.author != null && json.topic.author.length > 0) {
				        		resultsDivHtml += 'Author: <strong>' + json.topic.author + '</strong><br/>';
				        	}		        	
				        	if (json.topic.friendlyDateFilter != null && json.topic.friendlyDateFilter.length > 0) {
				        		resultsDivHtml += 'Date range: <strong>' + json.topic.friendlyDateFilter + '</strong><br/>';
				        	}
				        	if (json.topic.friendlyFrequency != null && json.topic.friendlyFrequency.length > 0) {
				        		resultsDivHtml += 'Email me: <strong>' + json.topic.friendlyFrequency + '</strong><br/>';
				        	}	 
				        	if (json.topic.allowRss != null && json.topic.allowRss == 'true') {
								resultsDivHtml += '<p>';
								resultsDivHtml += '<a href="' + json.topic.rssLocation + '" target="_blank"><img src="' + icx_plugin_url.url + '/images/feed-rss.gif"/></a>';
								resultsDivHtml += '&nbsp;&nbsp;<a href="' + json.topic.htmlLocation + '" target="_blank"><img src="' + icx_plugin_url.url + '/images/feed-html.gif"/></a>';
								resultsDivHtml += '</p>';				        		
				        	}				        	
				        	
				        	jQuery("#icx_topic_"+topicId+" div.icx_topic_title").html(resultsDivHtml);
				        	
				        	// Refresh the topic
				        	jQuery("#icx_topic_"+topicId+" form.topic_refresh").trigger("submit");
		       			    
		        	  }
		        		  jQuery("#editTopic").fadeToggle(400);
		        		  icxInitFadeOut();
	        	  }
	          }
	      });		  
		  event.preventDefault();
	  });	
	  
	  jQuery("#icx_save_cancel_btn").click(function(event) {
		  jQuery("#editTopic").fadeToggle(400);
		  event.preventDefault();
	  });	  
	  	  
}
function icxInitDeleteButton(elements) {
	
	  elements.on("click", function (event) {
		  event.stopPropagation();
		  event.preventDefault();	
		  var onFail = function(result, textStatus, errorThrown) {};
		  var topicId = jQuery(this).data("topicid");
		  
      	jQuery("a#icx_nav_tab_"+topicId).animate({padding:0, margin:0, width: 0, height: 0}, 200, function () {  
            if (jQuery(this).hasClass("nav-tab-active")) {
              jQuery(this).removeClass("nav-tab-active");
              jQuery(this).removeClass("icx-nav-tab-active");
              var ele;
              if(jQuery(this).next("a.icx_nav_tab").length) {
                ele = jQuery(this).next("a.icx_nav_tab");
              }
              else {
            	ele = jQuery(this).prev("a.icx_nav_tab");
              }
              
              // Grabbed the search results tab.  Go to recent headlines instead
              if (ele.attr("id") == "icx_nav_tab_search_results") {
                ele = jQuery("#icx_nav_tab_recent_headlines");
              }
              
              if (ele) {
                jQuery(ele).addClass("nav-tab-active");
                jQuery(ele).addClass("icx-nav-tab-active");
                jQuery(ele).trigger("click");
              }
            }
            
            jQuery(this).remove(); 
            jQuery("div#icx_topic_"+topicId).remove();
        });		  
		  
		      jQuery.ajax({
		          url : admin_ajax_url.url,
		          type : "post",
		          data : {action: "repubhub_delete_topic", topicId: topicId},
		          error: onFail,
		          success:function(result){
		            var text = '';	  
		            if (result == 'error') {
		              text = '<div class="icx_error fadeout"><p>Unable to delete topic at this time.  Please try again later.</p></div>';
		            } else if (result == 'success') {
		            	text = '<div class="icx_success fadeout"><p>Topic has been deleted.</p></div>';
		            }
		            
		            jQuery("div.icx_wrap").prepend(text);
		            icxInitFadeOut();
		          }
		      });
	      event.preventDefault();		  
	  });	
}

function icxInitDocumentBindings() {
	  
	  jQuery(document).on("click", ".icx_pager", function(event) {
		  var page = parseInt(jQuery("#icx_search_form input[name='page']").val());
		  if (jQuery(this).attr("href") == "prev")
			  page = page -1;
		  else
			  page = page +1;
		  
		  jQuery("#icx_search_form input[name='page']").val('' + page);
		  jQuery("#icx_search_form").trigger("submit");
	      event.preventDefault();
	  });
	  
	  jQuery(document).on("click", ".icx_pager_topic", function(event) {
		  location.href = "#rh";
		  var page = jQuery(this).attr("href");
		  var parent = jQuery(this).parent(".icx_repubhub_clips");
		  var topicId = parent.data("topicid");
		  jQuery("img#img_spinner_"+topicId).show();
		  var url = icx_plugin_url.url + '/images/animated-spinner.gif';

		  parent.html('<img src="' + url + '"/>');
	        jQuery.ajax({
		          url : admin_ajax_url.url,
		          type : "get",
		          data : {action: "repubhub_clips", topicid: topicId, page: page},
		          success: function(result) {
		        	  jQuery("img#img_spinner_"+topicId).hide();
		        	  parent.html(result);
		          }
		        });
		  
	      event.preventDefault();
	  });	  
	  
	  jQuery(document).on("click", ".icx_pager_cat", function(event) {
		  jQuery("#rh-spinner").show();	  
		  jQuery("div#icx_clips_for_topic_0").html('');
		  
		  location.href = "#rh";
		  var page = parseInt(jQuery("#icx_search_form input[name='page']").val());
		  if (jQuery(this).attr("href") == "prev")
			  page = page -1;
		  else
			  page = page +1;
		  
		  jQuery("#icx_search_form input[name='page']").val('' + page);
		  
		  jQuery("#icx_article_categories td.icx-active-cat").click();
		  
	      event.preventDefault();
	  });
	  
	  
	  jQuery(document).on("click", ".icx_pager_rh", function(event) {
		  jQuery("#rh-spinner").show();	  
		  jQuery("div#icx_clips_for_topic_0").html('');
		  
		  location.href = "#rh";
		  var page = jQuery(this).attr("href");
	        jQuery.ajax({
	            url : admin_ajax_url.url,
	            type : "get",
	            data : {action: "repubhub_recent_headlines", rhPage: page},
	            success: function(result) {
	            	jQuery("#rh-spinner").hide();
	            	jQuery("div#icx_clips_for_topic_0").html(result);
	            }
	        });
		  
	      event.preventDefault();
	  });	  
	  
	  jQuery(document).on("click", ".icx_clip_author, .icx_clip_publicationId, .icx_clip_publicationName", function(event) {
		  jQuery("#icx_search_form").trigger("reset");
		  
		  var href = jQuery(this).attr("href");
		  var styleClass = jQuery(this).attr("class");
		  if (styleClass == "icx_clip_author")
		    jQuery("#icx_search_form input[name='author']").val(href);
		  else if (styleClass == "icx_clip_publicationId")
			jQuery("#icx_search_form select[name='featuredPublicationString']").val("p-" + href);
		  else if (styleClass == "icx_clip_publicationName")
			jQuery("#icx_search_form input[name='publicationName']").val(href);
		  
		  jQuery("#icx_search_form input[name='page']").val('1');
		  jQuery("#icx_search_form").trigger("submit");
		  jQuery(".icx_republish_advanced_fields_front_page").show();
	      jQuery(".icx_republish_advanced_btn").html("Less Options");		  
	      event.preventDefault();
	  });  
	  

    jQuery(document).on("click", "#icx_dismiss_save_hint_info_box",function (event) {
        jQuery("#icx_save_hint_info_box").hide();
        jQuery.ajax({
          url : admin_ajax_url.url,
          type : "get",
          data : {action: "repubhub_dismiss_save_search_info_box"},
          success: function() {}
        });
        event.preventDefault();
      });		
    
	  jQuery(document).on("submit", ".topic_refresh", function(event) {
		  var topicId = jQuery("input[type='hidden']", this).val();
		  jQuery("img#img_spinner_"+topicId).show();

		  var parent = jQuery(this).parent("div.icx_topic_controls");
		  var sibling = parent.siblings("div.icx_repubhub_clips");
		  var url = icx_plugin_url.url + '/images/animated-spinner.gif';

		  sibling.html('<img src="' + url + '"/>');
	        jQuery.ajax({
		          url : admin_ajax_url.url,
		          type : "get",
		          data : {action: "repubhub_clips", topicid: topicId, page: 1},
		          success: function(result) {
		        	  jQuery("img#img_spinner_"+topicId).hide();
		        	  sibling.html(result);
		          }
		        });
	      
	      event.preventDefault();
	  });    
}

function icxInit() {
	  jQuery("h2#wait").hide();
	  jQuery("div#noneedtohide").show();
	  jQuery("#toggle_advance_setting").toggle(function () {
	      jQuery(this).next().show();
	      jQuery("#toggle_advance_setting").val("Hide Advanced Settings");
	    },
	    function () {
	      jQuery(this).next().hide();
	      jQuery("#toggle_advance_setting").val("Show Advanced Settings");
	    }
	  );
	  jQuery("#toggle_advance_setting").next().hide();

	  jQuery("#toggle_account_setting").toggle(function () {
	      jQuery(this).next().show();
	      jQuery("#toggle_account_setting").val("Hide Address");
	    },
	    function () {
	      jQuery(this).next().hide();
	      jQuery("#toggle_account_setting").val("Show Address");
	    }
	  );
	  jQuery("#toggle_account_setting").next().hide();

	  categoryListDisplay();
	  jQuery("input.category-radio").change(function () {
	    categoryListDisplay();
	  });
	   

		jQuery("div.empty_callback").parents("tr").remove();

	  toolbarTouch();
	  jQuery('#icopyright_article_tools_theme').change(function () {
	    toolbarTouch();
	  });
	  jQuery('input:radio[name=icopyright_background]').change(function () {
	    toolbarTouch();
	  });

	  jQuery("input[name='icopyright_pub_id']").keyup(function () {
	    if (jQuery(this).val() != "") {
	      jQuery("#no_pub_id_message").hide();
	    } else {
	      jQuery("#no_pub_id_message").show();
	    }
	  });

	  if (jQuery("input[name='icopyright_pub_id']").val() != "") {
	    jQuery("#no_pub_id_message").hide();
	  }

	  jQuery("#icopyright_manual_option").click(function() {
		  jQuery('#M3').show();
	  });
	  
	  jQuery("#icopyright_auto_option").click(function() {
		  jQuery('#M3').hide();
	  });
	  
	  jQuery("#icopyright_none_option").click(function() {
		  jQuery('#M3').hide();
	  });	  
	  
	  jQuery('a#icopyright_wp_settings_video').colorbox({ href: 'https://www.youtube.com/embed/V7g6E4OZjXQ?autoplay=1&vq=hd720"', width: '800px', height: '600px', iframe: true });
	  jQuery('a#icopyright_wp_republishing_video').colorbox({ href: 'https://www.youtube.com/embed/V7g6E4OZjXQ?autoplay=1&vq=hd720"', width: '800px', height: '600px', iframe: true });
	  jQuery('a#icopyright_wp_syndicating_video').colorbox({ href: 'https://www.youtube.com/embed/ZedjWrtVJ58?autoplay=1&vq=hd720"', width: '800px', height: '600px', iframe: true });
	  jQuery('a#icopyright_wp_revenue_video').colorbox({ href: 'https://www.youtube.com/embed/MWSuvjkM1NI?autoplay=1&vq=hd720"', width: '800px', height: '600px', iframe: true });
	  
	  
	  jQuery("#icopyright_ad_network_selector").change(function() {
		 var val = jQuery(this).val();
		 if (val == 'ADBLADE') {
		   jQuery(this).next().show();
		   jQuery("#icopyright_google_explanation").parent().parent().hide();
		   jQuery("#icopyright_adblade_explanation").parent().parent().show();
		   jQuery(this).next().find('input:not([name^="icopyright_adblade"])').parent().parent().hide();
		   jQuery(this).next().find('input[name^="icopyright_adblade"]').parent().parent().show();
		 } else if (val == 'GOOGLE') {
		   jQuery(this).next().show();
		   jQuery("#icopyright_adblade_explanation").parent().parent().hide();
		   jQuery("#icopyright_google_explanation").parent().parent().show();		   
		   jQuery(this).next().find('input:not([name^="icopyright_google"])').parent().parent().hide();
		   jQuery(this).next().find('input[name^="icopyright_google"]').parent().parent().show();
		 } else {
		   jQuery(this).next().hide(); 
		 }
	  });
	  
	  jQuery("#icopyright_ad_network_selector").next().hide();
	  
	  jQuery('input[name^="icopyright_google"]').change(function() {
		 if (jQuery(this).val().length == 0) {
			 jQuery(this).next("a.icopyright_ad_preview").hide();
		 } else if (jQuery('input[name="icopyright_google_client"]').val().length > 0){
			 jQuery(this).next("a.icopyright_ad_preview").show();
		 }
	  });
	  
	  jQuery('input[name^="icopyright_adblade"]').change(function() {
			 if (jQuery(this).val().length == 0) {
				 jQuery(this).next("a.icopyright_ad_preview").hide();
			 } else {
				 jQuery(this).next("a.icopyright_ad_preview").show();
			 }
		  });	  
	  
	  jQuery('a.icopyright_ad_preview').click(function(e) {
		e.preventDefault();
		var size = jQuery(this).data("size");
		var w = 350;
		var h = 250;
		if (size == 'LEADERBOARD') {
			w = 728;
			h = 90;
		} else if (size == 'FULL_BANNER') {
			w = 468;
			h = 200;			
		} else if (size == 'WIDE_SKYSCRAPER') {
			w = 160;
			h = 600; 
		}
		
		var google_ad_client = '';
		if (jQuery(this).data('type') == 'google') {
			google_ad_client = jQuery('input[name="icopyright_google_client"]').val();
		}
		
		var url = jQuery(this).attr('href') + '?type=' + jQuery(this).data("type") + '&size=' + jQuery(this).data("size") + '&adSlot=' + jQuery(this).prev().val() + "&google_ad_client=" + google_ad_client;
	    var previewPopup = window.open(url, '_blank', 'width=' + (w + 50) + ', height=' + (h + 50) + ', scrollbars=yes, resizable=yes');
	    previewPopup.focus();		  
	  });
	  
	  jQuery('#upload-btn').click(function(e) {
	        e.preventDefault();
	        var image = wp.media({ 
	            title: 'Upload Image',
	            // mutiple: true if you want to upload multiple files at once
	            multiple: false
	        }).open()
	        .on('select', function(e){
	            // This will return the selected image from the Media Uploader, the result is an object
	            var uploaded_image = image.state().get('selection').first();
	            // We convert uploaded_image to a JSON object to make accessing it easier
	            // Output to the console uploaded_image
	            console.log(uploaded_image);
	            var image_url = uploaded_image.toJSON().url;
	            // Let's assign the url value to the input field
	            jQuery('#icopyright_site_logo').val(image_url);
	            jQuery("#icopyright_site_logo_image").children("img").attr("src", image_url);
	            jQuery("#icopyright_site_logo_image").show();
	        });
	    });	  
}

function icxInitFadeOut() {
  jQuery('.fadeout').delay(5000).fadeOut('slow');
}


	// Function to update the previews with what the toolbars will look like with these settings
	function toolbarTouch() {
	  if (jQuery('#pub_id').html() == '') return;
	  var theme = jQuery('#icopyright_article_tools_theme').val();
	  var background = jQuery('input:radio[name=icopyright_background]:checked').val();
	  var publication = jQuery('#pub_id').html();
	  var url_h = jQuery('#icopyright_server').html() + '/rights/TouchToolbar.act?' +
	    jQuery.param({
	      theme: theme,
	      background: background,
	      orientation: 'horz',
	      publication: publication});
	  jQuery('#horizontal-article-tools-preview').attr('src', url_h);
	  var url_v = jQuery('#icopyright_server').html() + '/rights/TouchToolbar.act?' +
	    jQuery.param({
	      theme: theme,
	      background: background,
	      orientation: 'vert',
	      publication: publication});
	  jQuery('#vertical-article-tools-preview').attr('src', url_v);
	  var url_o = jQuery('#icopyright_server').html() + '/rights/TouchToolbar.act?' +
	    jQuery.param({
	      theme: theme,
	      background: background,
	      orientation: 'one-button',
	      publication: publication});
	  jQuery('#onebutton-article-tools-preview').attr('src', url_o);
	  var noticeUrl = jQuery('#icopyright_server').html() + '/rights/copyright-preview.jsp?' +
	    jQuery.param({
	      themeName: theme,
	      background: background,
	      publicationId: publication,
	      publicationName: jQuery('#site_name').html()
	    });
	  jQuery('#copyright-notice-preview').attr('src', noticeUrl);
	}

	function categoryListDisplay() {

	  if (jQuery("input.category-radio:checked").val() == "yes") {
	    jQuery("#icopyright-category-list").show();
	  } else {
	    jQuery("#icopyright-category-list").hide();
	  }
	}

	function authorListDisplay() {

	  if (jQuery("input.author-radio:checked").val() == "yes") {
	    jQuery("#icopyright-author-list").show();
	  } else {
	    jQuery("#icopyright-author-list").hide();
	  }
	}

function icopyright_update_unread_notification(topicId) {
	if (topicId == 'search_results') {
		return;
	}
	
	var unread = jQuery("#num_topics_"+topicId).data("num");
	if (!unread)
		return;
	
    var onSuccess = function(data) {
    	jQuery("#num_topics_"+topicId).remove();
        // Update unread notifications
        icopyright_update_unread_notification_message(jQuery("#icx_nav_tab_"+topicId+ " span.icx_unread_tab_count"), 0, true);
        icopyright_update_unread_notification_message(jQuery("#wp-admin-bar-republish-1 a"), data, false);
        icopyright_update_unread_notification_message(jQuery("#wp-admin-bar-republish-2 a").first(), data, false);
        icopyright_update_unread_notification_message(jQuery("#wp-admin-bar-republish-4 a").first(), data, false);
        icopyright_update_unread_notification_message(jQuery("li.current a.current[href='edit.php?page=repubhub-republish']"), data, true);
    };
    unread = '' + unread;
    unread = parseInt(unread.replace('+', ''));

    jQuery.ajax({
        url : admin_ajax_url.url,
        type : "get",
        data : {action: "repubhub_clips_read", topicId: topicId, icxNumRead: unread},
        success: onSuccess
    });
}

function icopyright_update_unread_notification_message(item, value, circle) {
    var title = item.html();
    var newValue = "&nbsp;"+value;
    
    var lastIndexVal = "&nbsp;";
    if (circle) {
        newValue = '<span class="icx_unread update-plugins count-1"><span class="plugin-count">'+value+'</span></span>';
        lastIndexVal = '<span class="icx_unread';
    }
    if (title.lastIndexOf(lastIndexVal)>0) {
        if (endsWith(""+value, "+") || value > 0)
            item.html(title.substr(0, title.lastIndexOf(lastIndexVal)) + newValue);
        else
            item.html(title.substr(0, title.lastIndexOf(lastIndexVal)));
    } else {
        if (endsWith(""+value, "+") || value > 0)
            item.html(title + newValue);
    }
}

function endsWith(str, suffix) {
    return str.indexOf(suffix, str.length - suffix.length) !== -1;
}

jQuery(document).ready(function () {
    icxInitGlobalSettings();
    icxInitSearchSection();
    icxInitContentCategorySection();
    icxInitTabsSection();
    icxInitDeleteButton(jQuery(".icx_topic_delete_btn"));
    icxInitDocumentBindings();
    icxInitFadeOut();
    icxInit();
 });


function authorPopup() {
    var authorPopup = window.open(icx_plugin_url.url + '/icopyright-exclude-authors.php', '_blank', 'width=511, height=550, scrollbars=yes, resizable=yes');
    authorPopup.focus();	
}
