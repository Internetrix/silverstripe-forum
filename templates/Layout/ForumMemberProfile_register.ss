<div class="content-area">
    <div class="row basci-pg">
		$Content
        <div id="UserProfile">
			<% if CurrentMember %>
                <p><% _t('ForumMemberProfile_register_ss.PLEASELOGOUT', 'Please logout before you register') %> - <a href="Security/logout"><% _t('ForumMemberProfile_register_ss.LOGOUT', 'Logout') %></a></p>
			<% else %>
				$RegistrationForm
			<% end_if %>
        </div>

		<% include ForumFooter %>
    </div> <!--Row end-->
</div>
