<?php	
	//this will remove search results files older than 50 mins
	$filesToRemove = glob("results/*");
	$currentTime = time();
	foreach($filesToRemove as $file){		
		if($currentTime - filemtime($file) >=3000){
			unlink($file);
		}
	}
?>