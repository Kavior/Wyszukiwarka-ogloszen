<?php

	$id = $_GET['id'];
	require('db.php');
	$removeQuery = "DELETE FROM search_results WHERE id='$id'";
	mysqli_query($db, $removeQuery);
?>