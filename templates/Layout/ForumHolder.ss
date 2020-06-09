<div class="content-area">
    <div class="row basci-pg">

		<% if CurrentMember.isBanned %>
            <p class="forum-message-suspended">
                This user has been banned. Please contact us if you believe this is a mistake.
            </p>
		<% else_if not CurrentMember.CheckIfApproved %>
            <p class="forum-message-suspended">
                Your account has not been yet approved by a moderator. Please visit the forum again once you have been
                approved.
            </p>
		<% else %>
			<% if ForumAdminMsg %>
                <p class="forum-message-admin" style="color: red;">$ForumAdminMsg</p>
			<% end_if %>
            <table class="forum-topics">
				<% if GlobalAnnouncements %>
                    <tr class="category">
                        <td colspan="4">Pinned</td>
                    </tr>
					<% loop GlobalAnnouncements %>
						<% include ForumHolder_List %>
					<% end_loop %>
				<% end_if %>
					<% include ForumHeader Content=$HolderAbstract%>
				<% if CurrentMember.isSuspended %>
                    <p class="forum-message-suspended red">
						$CurrentMember.ForumSuspensionMessage
                    </p>
				<% end_if %>
				<% if ShowInCategories %>
					<% loop Forums %>
                        <tr class="category">
                            <td colspan="4">$Title</td>
                        </tr>
                        <tr class="category">
                            <th><% if Count = 1 %><% _t('ForumHolder_ss.FORUM','Forum') %><% else %><% _t('ForumHolder_ss.FORUMS', 'Forums') %><% end_if %></th>
                            <th><% _t('ForumHolder_ss.THREADS','Threads') %></th>
                            <th><% _t('ForumHolder_ss.POSTS','Posts') %></th>
                            <th><% _t('ForumHolder_ss.LASTPOST','Last Post') %></th>
                        </tr>
						<% loop CategoryForums %>
							<% include ForumHolder_List %>
						<% end_loop %>
					<% end_loop %>
				<% else %>
                    <tr class="category">
                        <td><% _t('ForumHolder_ss.FORUM','Forum') %></td>
                        <td><% _t('ForumHolder_ss.THREADS','Threads') %></td>
                        <td><% _t('ForumHolder_ss.POSTS','Posts') %></td>
                        <td><% _t('ForumHolder_ss.LASTPOST','Last Post') %></td>
                    </tr>
					<% loop Forums %>
						<% include ForumHolder_List %>
					<% end_loop %>
				<% end_if %>
            </table>

			<% if $ShowModerateTable %>
                <div class="forum-moderation">

                    <div class="large-12 column">

                        <h3><a name="mod"></a>Forum Moderation</h3>

                        <table class="forum-topics" summary="List of topics in this forum">
                            <tr class="category">
                                <td colspan="4">Users Pending Moderation</td>
                            </tr>
                            <tr>
                                <th class="odd">Name</th>
                                <th class="odd">Nickname</th>
                                <th class="even">Email</th>
                                <th class="even"></th>
                            </tr>

							<% if PendingUsers %>
								<% loop $PendingUsers %>
                                    <tr>
                                        <td class="odd">$FirstName $Surname</td>
                                        <td class="odd">$Nickname</td>
                                        <td class="even">$Email</td>
                                        <td class="even"><a href="$Top.GetApproveLink($ID)">Approve User</a><br/><a
                                                href="$Top.GetDenyLink($ID)">Decline User</a></td>
                                    </tr>
								<% end_loop %>
							<% else %>
                                <tr>
                                    <th colspan="4">There are no users pending moderation.</th>
                                </tr>
							<% end_if %>

                        </table>

                    </div>

                </div>
			<% end_if %>

			<% include ForumFooter %>
		<% end_if %>
    </div>
</div>
