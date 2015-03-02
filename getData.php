<?php

	if(isset($_GET['itemName']) && isset($_GET['city']) && $_GET['itemName']!=='' && $_GET['city']!=='' ){
		//Return page query depending on search parameters which user chose
		function returnPageQuery($pageName, $queryVariant){
			global $city;
			global $itemName;
			global $priceFrom;
			global $priceTo;	
			return	$pageName->returnFullQuery($itemName, $city,$priceFrom,$priceTo,$queryVariant);
		}	
		
		//Create olx.pl page	
		$olx = new Page('www.olx.pl','/q-','/','&search%5Bfilter_float_price%3Afrom%5D=','&search%5Bfilter_float_price%3Ato%5D=', 
		'/&search%5Border%5D=filter_float_price%3Aasc');
		$olxQuery= returnPageQuery($olx, 1);	
		//echo 'olx query:</br>'.$olxQuery;
		//This is necessary because gumtree uses specific code for each city
		function getGumtreeCityCode($city){
			$content = file_get_contents('http://www.gumtree.pl/');
			//Import city code from page content
			$pattern =$city.'\\';
			$firstStep = explode( $pattern , $content );
			$secondStep = explode("\\" , $firstStep[1] );
			
			$cityCode = $secondStep[0];
			//Create specific query code for city
			$cityCode = 'l'.substr($cityCode,3);
			return $cityCode;
		}
		
		$gumtreeCityCode = getGumtreeCityCode($_GET['city']);
		//Add city code to query
		$gumtreeSortingCode = (string) '/'.$gumtreeCityCode.'&Sort=3';
		$gumtree = new Page('www.gumtree.pl','/fp-','/','&minPrice=','&maxPrice=',$gumtreeSortingCode);
		$gumtreeQuery= returnPageQuery($gumtree, 2);			
		//echo '</br>gumtree query:</br>'.$gumtreeQuery;
		$allegro = new Page('www.allegro.pl/listing/listing.php','&string=','&postcode_enabled=2&offerTypeBuyNow=1&city=', '&price_enabled=1&price_from=', '&price_to=', '&order=d');
		$allegroQuery = returnPageQuery($allegro, 3);
		//echo '</br>allegro query:</br>'.$allegroQuery;
		//Get last unique searching session id
		$_SESSION['lastId'] = $id;
		//since now disable session writing 
		session_write_close();
		
		//If there has not been created search results file for this user	
		if(!file_exists($fileName) ){
			$olxContent = new Offer($olxQuery, 'summary="Ogłoszenie">|<\/table>', '<h3 class="large lheight20 margintop10">|<\/h3>', '<p class="price large margintop10">|<\/p>', 'http:\/\/img|.jpg', '&page=','<link rel="next"');	
			$olxOffers = $olxContent->getOffersFromAllPages($olxContent->url);
			
			$allegroContent = new Offer($allegroQuery, '\<article|<\/article>','<header>|<\/header>','span class="label">Kup Teraz<\/span>|<span class="currency"', ',"http:\/\/img|",', '&p=', '<link rel="next"');
			$allegroOffers = $allegroContent->getOffersFromAllPages($allegroContent->url);								
			
			$gumtreeContent = new Offer($gumtreeQuery,'<tr class="resultsTableSB rrow"|<\/tr>','<div class="ar-title">|<\/div>', '<div class="ar-price">|<\/div>','http:\/\/i.ebayimg.com|.JPG', '&Page=', '<link rel="next"');
			$gumtreeOffers= $gumtreeContent->getOffersFromAllPages($gumtreeContent->url);
			//Array which holds offers from all webpages
			$offersArray = array_merge( $olxOffers, $gumtreeOffers);
			$offersArray = array_merge($offersArray, $allegroOffers);
			//Sort results by price
			asort($offersArray);
			//Convert offers to string in order to put it in search results file
			$offersString = '';			
			foreach($offersArray as $item=>$details){
				//Save search results coded in text file		
				$offersString.= '---OFFER---||TITLE||'.$item.'||/TITLE||||PRICE||'.$details[0].'||/PRICE||||IMAGE||'.$details[1].'||/IMAGE||';				
			}
			
			$resultsFile = fopen($fileName, "w") or die("Unable to open file!");
			ftruncate($resultsFile, 0);
			fwrite($resultsFile, $offersString);		
		}
	}	
?>