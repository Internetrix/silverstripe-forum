<script type="text/javascript">

    tinyMCE.init({ 
     theme : "advanced", 
     mode: "textareas", 
     theme_advanced_toolbar_location : "top", 
     theme_advanced_buttons1 : "formatselect,|,bold,italic,underline,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,bullist,numlist,outdent,indent,separator,undo,redo",
     theme_advanced_buttons2 : "", 
     theme_advanced_buttons3 : "",
     height:"250px", 
     width:"400px"
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
