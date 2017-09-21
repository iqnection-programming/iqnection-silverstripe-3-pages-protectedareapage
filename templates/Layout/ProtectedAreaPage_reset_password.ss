<h1>Reset Your Password</h1>

<p>Please enter your email address, a temporary password will be generated and emailed to you.
<% if $ProtectedAreaUserConfig('user_can_update_password') %>
	<br />Once you receive your temporary password, you may login and update your password.
<% end_if %>
</p>
<div class="form_full">
	$ResetProtectedAreaUserPasswordForm
</div>