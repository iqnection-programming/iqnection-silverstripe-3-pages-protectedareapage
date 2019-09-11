<h1>$ProtectedAreaUser.getTitle</h1>

<% include ProtectedAreaUserActions %>

<% if $ProtectedAreaUserMessage %>
	<div class="protected-area-message">
		$ProtectedAreaUserMessage
	</div>
<% end_if %>

$Content