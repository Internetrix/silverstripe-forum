<div class="content-area">
    <div class="row basci-pg">
		<% include ForumHeader %>
		<% if CurrentMember.isSuspended %>
            <p class="forum-message-suspended red">
				$CurrentMember.ForumSuspensionMessage
            </p>
		<% else %>
            <div id="TopicTree">
                <div id="Root">
					$PostMessageForm(true)
                </div>
            </div>

			<% include ForumFooter %>

		<% end_if %>
	</div>
</div>


