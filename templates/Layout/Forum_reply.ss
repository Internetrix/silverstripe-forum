<div class="content-area">
    <div class="row basci-pg">
		<% include ForumHeader %>
    </div>

    <div class="row basci-pg">
		<% if CurrentMember.isSuspended %>
        <p class="forum-message-suspended red">
			$CurrentMember.ForumSuspensionMessage
        </p>
		<% else %>
			$PostMessageForm
		<% end_if %>

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
    </div>
</div>
