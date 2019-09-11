<% if $ProtectiveParent.Exists %>
	<% with $ProtectiveParent %>
		<div class="protected-user-actions">
			<ul class="protected-user-links">
				<% if $ProtectedAreaUser.Exists %>
					<li><a href="$Link(logout)">Logout</a></li>
					<% if $ProtectedAreaUserConfig('user_can_update_password') %>
						<li><a href="$Link(change-password)">Change My Password</a></li>
					<% end_if %>
				<% else %>
					<li><a href="$Link(login)">Login</a></li>
				<% end_if %>
			</ul>
		</div>
	<% end_with %>
<% end_if %>