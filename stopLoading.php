<?php
	$actualUrl = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>

<form id="stopForm" style="display:none;" name="stop" method="post" action = "<?php echo $actualUrl; ?>">
	<input type = "submit" value="stop" name="stop">
</form>

