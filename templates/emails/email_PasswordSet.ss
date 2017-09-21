<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Login Credentials</title>
</head>
<body>
<p>Your account has been created on $SiteDomain. Please use the below credentials to login to your secure area.<br /><br />
Email: $Email<br />
Password: $NewPassword

<% if $ProtectedAreaUserGroups.Count %>
<br /><br />
You now have access to the following content:<br />
<% with $ProtectedAreaUserGroups.First.Pages.First.ProtectiveParent %>
	<a href="$AbsoluteLink" target="_blank">$AbsoluteLink</a>
<% end_with %>
<% end_if %>
</p>


</body>
</html>
