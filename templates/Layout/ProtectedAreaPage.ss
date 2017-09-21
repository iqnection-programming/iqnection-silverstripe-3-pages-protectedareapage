<h1>$ProtectedAreaUser.getTitle</h1>

<div class="protected-user-actions">
	<a href="$Link(logout)">Logout</a><% if $ProtectedAreaUserConfig('user_can_update_password') %> | <a href="$Link(change-password)">Change My Password</a><% end_if %>
</div>

<% if $ProtectedAreaUserMessage %>
	<div class="protected-area-message">
		$ProtectedAreaUserMessage
	</div>
<% end_if %>

$Content