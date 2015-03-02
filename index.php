<?php 
	session_start();
	$ip =$_SERVER['REMOTE_ADDR'];
	$ip = str_replace(':', '-', $ip);
	if(isset($_SESSION['lastId'])){
		$fileToDeleteName = (string) 'results/'.$ip.'&'.$_SESSION['lastId'].'.txt';
	}
	
	function getOffersFromFile($file){				
		if(file_exists($file)){
			$content = file_get_contents($file);
			$offers = explode("---OFFER---", $content);
			return $offers;
		}
		return false;
	}
	
	if(isset($_GET['id'])){		
		$fileName = 'results/'.$ip.'&'.$_GET['id'].'.txt';			
	}
	//set random id to a searched item
	$id = md5(rand(0,99999999) * rand(1.1, 2.2));	
	ini_set('max_execution_time', 1200);	
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html xml:lang="pl" lang="pl">
<head>
	
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8'> 
	<link rel="ICON" href="icon.png" type="image/ico" />
	<meta name="keywords" content="wyszukiwarka ogłoszeń, wyszukiwarka ofert, wyszukiwanie ofert">
	<meta name="description" content="Wyszukiwarka ogłoszeń. Szukaj ofert pośród najpopularniejszych portali ogłoszeniowych!">
	<title>Sprawdź cenę!</title>
	<link rel="stylesheet" href="style.css" type="text/css">
	<script type="text/javascript" src="jquery-2.0.3.min.js"></script>
	<script type="text/javascript" src="jquery-ui.js"></script>
	<script type="text/javascript" src="spin.js"></script>
	<script type="text/javascript" src="spin.min.js"></script>
	<script type="text/javascript" src="jquery.spin.js"></script>
	
</head>
<body>
	<script>
		$(document).ready(function(){	
			$('#submit').click(function(){	
				//loading message 
				$('body').append('<div id="loading" style="position:absolute; top:0;">Ładowanie treści. Proszę czekać.</br>'+
				'<button id="stopper">Anuluj</buton></div>');
				//using spin.js
				var opts = {
				  lines: 5, // The number of lines to draw
				  length: 70, // The length of each line
				  width: 10, // The line thickness
				  radius: 30, // The radius of the inner circle
				  corners: 1, // Corner roundness (0..1)
				  rotate: 0, // The rotation offset
				  direction: 1, // 1: clockwise, -1: counterclockwise
				  color: '#000', // #rgb or #rrggbb or array of colors
				  speed: 1, // Rounds per second
				  trail: 60, // Afterglow percentage
				  shadow: false, // Whether to render a shadow
				  hwaccel: false, // Whether to use hardware acceleration
				  className: 'spinner', // The CSS class to assign to the spinner
				  zIndex: 2e9, // The z-index (defaults to 2000000000)
				  top: '10%', // Top position relative to parent
				  left: '50%' // Left position relative to parent
				};
		
				document.getElementById('loading').innerHTML = document.getElementById('loading').innerHTML;
				var target = document.getElementById('main');
				var spinner = new Spinner().spin(target);	
			});	
				
			$(document).keyup(function(evt){
				   var charCode = (evt.which) ? evt.which : event.keyCode;
			    if (charCode ==27)		       
			       $('#loading').fadeOut();   
			       $('.spinner').fadeOut();	   
			});		
			//remove loading screen after page load			
			$('#loading').fadeOut(2000);
			//form used to stop loading without errors					
			$(document).on('click', '#stopper', function(){
				document.getElementById('stopForm').submit();
			});
			
			info = $('.info');					
			info.click(function(){
				infobox = $('#infoBox');
				searchArea = $('#searchArea');
				searchAreaPos = searchArea.position();
				searchAreaWidth = searchArea.outerWidth();
				infobox.css({
					position: "absolute",
					top: searchAreaPos.top + "px",				
				}).show();			
			});
				
			innerTextTop = $('#innerText').position().top;
			innerTextLeft = $('#innerText').position().left;
			//set position of closing button
			$('#close').css({
				position: "absolute",
				top: innerTextTop + 12 + "px",
				left: innerTextLeft + 86 + "%",
			});
			
			$('#close').click(function(){			
				infobox.fadeOut();
			});
	
			$('.descriptionSearch').click(function(){		
				if( this.checked){	
					var accept = confirm('Szukanie również w opisach może znacząco wydłużyć czas przeszukiwania stron. '+ 
					'Czy na pewno chcesz skorzystać z tej opcji?');
					if(accept==false){
						this.checked = false;
					}
				}
			});
			//If next page available	
			var page = document.getElementById('page');
			if(page){
				var goButton = document.getElementById('goButton');
				goButton.onclick = function(){
					goTo =page.value;
					var url = document.URL;
					var newPage = '&page='+goTo;
					if(goTo!==1){
						if(url.indexOf('&page')>1){	
							var newUrl = url.replace(/&page=.{1,4}/, newPage);
						}else{
							var newUrl = url+newPage;
						}
					}else if(url.indexOf('&page')>1){
						var newUrl = url.replace(/&page=.{1,4}/, '');
					}else{
						var newUrl = url;	
					}
				
					window.location.replace(newUrl);
				};
			
				$('#page').keyup(function(event){
					 if (event.keyCode == 13) {
		 			 	$('#goButton').click();
					 }
				});
			}		
		});
		//Prevent price fields from accepting not numeric values		
		function isNumberKey(evt){
		    var charCode = (evt.which) ? evt.which : event.keyCode
		    if (charCode > 31 && (charCode < 48 || charCode > 57))
		        return false;
		    return true;
		}		
	</script>
	<div id="main">	
		<div id = "searchArea">			
			<form id="searchForm" method = "get" action="index.php" >
				<div id="basicInfo">
				<input type="hidden" name = "id" value = "<?php echo $id; ?>">
				Nazwa: <input class="field1" type="text" name="itemName" value="<?php if(isset($_GET['itemName']))echo $_GET['itemName'];?>">
				Miasto: <input class="field1" type="text"  name="city" value="<?php if(isset($_GET['city'])) echo $_GET['city']; ?>">
				Cena:<div style="margin-top:11px;"></div><div class="priceArea">od: 
				<input class="field2" type="text" id="priceFrom" name="priceFrom" onkeypress="return isNumberKey(event)" value="<?php if(isset($_GET['priceFrom'])) echo $_GET['priceFrom']; ?>">zł</div>
				<div class="priceArea">do: <input class="field2" type="text" id="priceTo" name="priceTo" onkeypress="return isNumberKey(event)" value="<?php if(isset($_GET['priceTo'])) echo $_GET['priceTo']; ?>">zł</div></div>
				<ul><li><input <?php if(isset($_GET['olxDescriptions']))echo $_GET['olxDescriptions']==1?'checked':''?> type="checkbox" class="descriptionSearch" name="olxDescriptions" value="1">Olx - szukaj także w opisach</li>
				<li><input <?php if(isset($_GET['allegroDescriptions'])) echo $_GET['allegroDescriptions']==1?'checked':''?> type="checkbox" class="descriptionSearch" name="allegroDescriptions" value="1">Allegro - szukaj także w opisach</li>
				</ul>
				<input type="submit" id="submit" name="submit" value="szukaj!" style="position: relative; top:5px; width:100px; margin:auto;">
			</form>
		</div>
		<div id="infoBox"><div id="innerText">Aplikacja służy do wyszukiwania ogłoszeń na najpopularniejszych portalach ogłoszeniowych :
		<b>Olx, Gumtree oraz Allegro</b>. Od teraz nie musisz przeszukiwać każdej z tych stron osobno. Ta wyszukiwarka zrobi to za Ciebie!
		 W celu uzyskania jak największej liczby wyników pamiętaj o dokładnym wpisaniu nazwy miasta, zwróć uwagę na polskie znaki. 
		 Aby skrócić czas wyszukiwania dokładnie sprecyzuj szukane hasła oraz zakres cen.<div id="close"></div></div></div>
		<div id = "info" class="info" title="info">?</div>					
		<div id="advertisementsArea">
			<?php
				require("delete.php");
							
				if(isset($_GET['submit']) ){
					$replacements = array(' '=>'-','ą'=>'a','ł'=>'l', 'ń'=>'n', 'ó'=>'o', 'ś'=>'s', 'ż'=>'z','ź'=>'z',
									'Ł'=>'L', 'Ń'=>'N', 'Ó'=>'O', 'Ś'=>'S', 'Ż'=>'Z', 'Ź'=>'Z');
					$city = strtolower(strtr($_GET['city'], $replacements));
					$itemName =	strtolower(strtr($_GET['itemName'], $replacements));								
					function returnGivenElement($element){
						if(isset($element) && $element!==""){
							return $element;
						}else{
							return null;
						}
					}	
					$priceFrom =returnGivenElement($_GET['priceFrom']);
					$priceTo =returnGivenElement($_GET['priceTo']);
													
					require('page.php');				
					require('getData.php');	
				}
		
			require('paginator.php');
			$actualUrl = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			
			if(isset($fileName)){
				$offersFromFile= getOffersFromFile($fileName);
				foreach($offersFromFile as $offer){	
					preg_match('/\|\|TITLE\|\|(.*?)\|\|\/TITLE\|\|/s',$offer, $title);
					if(isset($title[1])){
						$title =  $title[1];
					
						preg_match('/\|\|PRICE\|\|(.*?)\|\|\/PRICE\|\|/s',$offer, $price);
						if(isset($price[1])){
							$price =  $price[1];
						}else{
							$price = "";
						}
						preg_match('/\|\|IMAGE\|\|(.*?)\|\|\/IMAGE\|\|/s',$offer, $image);
						if(isset($image[1])){
							$image =  $image[1];
						}else{
							$image = " ";
						}						
												
						$allOffersArray[$title] =[$price,$image];
					}
				}
				
				}
				//<2 because there is blank offer coming from olx
				if(isset($allOffersArray) && count($allOffersArray) <2){
					echo '<span style="font-size:25px;  margin-top:10px; "><center>Brak wyników wyszukiwania.</center></span>';
				}else if(isset($allOffersArray)){
					$resultsPaginator = new offersPaginator(15, $allOffersArray);
					$resultsPaginator->getCertainElements();	
				}
				
			if(isset($fileName) && !file_exists($fileName) && isset($_GET['itemName']) && $_GET['itemName']!==""){
				echo 'Sesja dla tego zapytania wygasła. Wyszukaj jeszcze raz. ';
			}				
		
			?>
		</div>
		<?php require('stopLoading.php'); ?>
		<div id="footer">
			<span id="pageCount" >
				<center>
					<?php
						if((isset($_GET['itemName']) || isset($_GET['newItemName']))&& isset($allOffersArray) && isset($resultsPaginator) ){
							$resultsPaginator->printPageLinks();
						}
					?>
				</center>
			</span>
		</div>
	</div>	
</body>
</html>