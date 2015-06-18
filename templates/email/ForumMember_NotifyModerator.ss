<p>Hi $Forum.Title moderator,</p>

<% if NewThread %>
	<h3>New forum thread has been started.</h3>
<% else %>
	<h3>A forum post has been added or edited</h3>
<% end_if %>

<p>Username: $Author.Nickname<br />
Date and Time: {$Post.LastEdited.Nice}</p>

<h3>Actions</h3>
<ul>
	<li><a href="$Post.Link">View the Thread</a></li>
	<li><a href="$ApproveURL">Approve Post</a></li>
	<li><a href="$DeleteURL">Delete Post</a></li>
</ul>

<p>
	Kind Regards,<br />
	$Forum.Title Forum.
</p>

<p>
	NOTE: This is an automated email sent to all moderators of this forum.
</p>

