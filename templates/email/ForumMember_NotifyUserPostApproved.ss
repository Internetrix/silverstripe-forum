<p>Hello $Author.FirstName $Author.Surname,</p>

<p>You are receiving this notification because your $PostType.LowerCase "$Post.Title" at "$Forum.Title" was approved by a moderator.</p>

<% if $ForPost %>
<p>If you want to view the post, click the following link: <br /><a href="$Post.Link('show')" target="_blank">$Post.Link('show')</a></p>
<% end_if %>

<p>If you want to view the topic, click the following link: <br /><a href="$Post.Thread.Link('show')" target="_blank">$Post.Thread.Link('show')</a></p>