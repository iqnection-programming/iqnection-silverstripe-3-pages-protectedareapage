<h1>Login</h1>

<p>You must login before you can access this content.</p>
<div class="form_full">
	$ProtectedAreaUserLoginForm
	
	<% if $ProtectedAreaUserConfig('user_can_update_password') %>
		<p class="clear" style="text-align:right"><small><a href="$Link(reset_password)">I've forgotten my password</a></small></p>
	<% end_if %>
</div>