<script type="text/javascript">

    tinyMCE.init({ 
     theme : "advanced", 
     mode: "textareas", 
     plugins: "media",
     extended_valid_elements : "iframe[src|frameborder|style|scrolling|class|width|height|name|align]",
     theme_advanced_toolbar_location : "top", 
     theme_advanced_buttons1 : "formatselect,|,bold,italic,underline,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,bullist,numlist,outdent,indent,separator,undo,redo",
     theme_advanced_buttons2 : "code", 
     theme_advanced_buttons3 : "",
      media_strict: false,
    });
    
    /* Check if the content area has content */
   /* TinyMCE has an issue with the HTML5 required field, use triggerSave() to force the content from TinyMCE into the hidden form field. */
  
  
    $(document).ready(function() {
	    $('#Form_PostMessageForm_action_doPostMessageForm').click(function() {
	    	var content = tinyMCE.get('Form_PostMessageForm_Content').getContent();
	    	if(content == "") {
	    		// Empty field, show an error
	    		$('<span class="error">Content area is empty</span>').insertAfter($(tinyMCE.get('Form_PostMessageForm_Content').getContainer()));
	    	} else {
	    		// Field has content
	    		tinyMCE.triggerSave();
	    	}
	    	
	    });
    });
   
   </script>
<% include ForumHeader %>
	$PostMessageForm
	
	<div id="PreviousPosts">
		<ul id="Posts">
			<% loop Posts(DESC) %>
				<li class="$EvenOdd">
					<% include SinglePost %>
				</li>
			<% end_loop %>
		</ul>
		<div class="clear"><!-- --></div>
	</div>
	
<% include ForumFooter %>
