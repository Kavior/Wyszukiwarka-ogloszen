<?php
	// Class which returns link to ads
	class Page{
		public function __construct($adress, $itemQuery, $cityQuery, $priceFromQuery, $priceToQuery, $sortingQuery){
			$this->adress= $adress;
			$this->cityQuery = $cityQuery;
			$this->itemQuery = $itemQuery;
			$this->priceFromQuery = $priceFromQuery;
			$this->priceToQuery = $priceToQuery;
			$this->sortingQuery = $sortingQuery;		
		}
		
		function returnFullQuery($itemName, $city, $priceFrom, $priceTo, $queryVariant ){	
			$sortingQuery = $this->sortingQuery;
			
			$ItemAndCityFullQuery = $this->cityQuery.$city.$this->itemQuery.$itemName;
			$priceFromFullQuery = isset($this->priceFromQuery) && isset($priceFrom)? $this->priceFromQuery.$priceFrom:"" ;
			$priceToFullQuery = isset($this->priceToQuery) && isset($priceTo)? $this->priceToQuery.$priceTo:"" ;
			$additionalQuery = "";
						
			switch ($queryVariant){
				//olx.pl	
				case 1:
					//Search in descriptions too
					if(isset($_GET['olxDescriptions']) && $_GET['olxDescriptions']==1){
						$sortingQuery .='&search%5Bdescription%5D=1';		
					}
					//Replace some problematic names with proper ones
					$replacements = array('zielona-gora'=>'zielonagora', 'gorzow-wielkopolski'=>'gorzow');
					$city = strtr($city, $replacements);
					$additionalQuery = '&search%5Bdist%5D=10';	
				break;
				//gumtree.pl
				case 2:		
					$itemName = strtoupper(strtr($itemName, '-', '+'));				
					$ItemAndCityFullQuery = $this->itemQuery.$itemName.$this->cityQuery.$city;
					//necessary phrase for gumtree.pl
					if(isset($priceTo)){
						$maxBackend = $priceTo*100;
						$priceToFullQuery.= '&maxPriceBackend='.$maxBackend;
						}
					
					if(isset($priceFrom)){
						$minBackend = $priceFrom*100;
						$priceFromFullQuery.= '&minPriceBackend='.$minBackend;
					}
					break;
					
				//allegro.pl
				case 3:					
					if(isset($_GET['allegroDescriptions']) && $_GET['allegroDescriptions']==1){
						$sortingQuery.='&description=1';
					}
					$itemName.= '&bmatch=seng-v6-p-sm-isqm-3-e-0113&offerTypeBuyNow=1';
					if(isset($priceFrom) || isset($priceTo)){
						$additionalQuery = '&price_enabled=1';
					}	
					break;			
			}
			$fullQuery =  (string) 'http://'.$this->adress.$ItemAndCityFullQuery.$sortingQuery.$priceFromFullQuery.$priceToFullQuery.$additionalQuery;	
									
			return preg_replace('/&/', '?', $fullQuery, 1);			
		}

	} //Page end

	 /*Class which imports offers from pages
	 * Title, price and image mean regex responsible for DOM elements
	 */
	class Offer{		
		public function __construct($url, $wholeOfferElement, $title, $price, $image, $nextPageQuery, $nextPageHtmlCode){
			$this->url=$url;
			$this->wholeOfferElement = $wholeOfferElement;
			$this->title=$title;
			$this->price= $price;
			$this->image = $image;
			$this->nextPageQuery = $nextPageQuery;
			$this->nextPageHtmlCode= $nextPageHtmlCode;
		}

		public function getElement($regex){	
			$regexDivided = explode("|", $regex);
			$firstElement = $regexDivided[0];
			$secondElement = $regexDivided[1];
			//return whole regular expression for element
			return '/'.$firstElement.'(.*?)'.$secondElement.'/s';				
		}		
		
		public function getOffers($url){
			$page = file_get_contents($url);				
			/**	Create regex which imports offersfrom pages
			 *	wholeOfferElement is name of DOM element that hold advertisement inside	
			 */
			$regex = $this->getElement($this->wholeOfferElement);			
			preg_match_all($regex,$page, $offers);			
			return $offers;						
		}

		//Extract the element based on regex
		public function performElement($element, $source){
				$elementRegex = $this->getElement($element);
				preg_match_all($elementRegex,$source,$newElement);	
				if(isset($newElement[0][0])){
					return $newElement[0][0];
				}						
		}	
		
		//Method that creates array filled with offers		
		public function printOffers($url){
			$offersArray= array();
			$offers =$this->getOffers($url)[0];
							
			foreach($offers as $offer){
				//Extract offer title
				$title = $this->performElement($this->title, $offer);				
				if(strpos($title, 'header')){
					$reps = array('Standard Allegro'=>'', '<a href="'=>'<a href="Http://www.allegro.pl');
					$title = strtr($title, $reps);
				}
				//$title = preg_replace('/class="(.*?)"/', '', $title); //optionally remove class from title
				//Extract offer price
				$price = $this->performElement($this->price, $offer);
				$replacements = array(' '=>'', ','=>'.');
				$price = strtr($price, $replacements);
				$price = intval( preg_replace(array('/class="(.*?)"/', '/[^0-9.]+/'), '', $price) ) ;							
				
				//Extract offer image
				$image = $this->performElement($this->image, $offer);	
				//If offer contains no image, return empty place			
				if(empty($image) || $image==''){
					$image= ' ';
				}			
				//Each element of this array is separate offer with title, price and image divided
				$offersArray[$title] =[$price,$image];
			}
			return $offersArray;		
		}
		
		//If there is more than one page of search results
		public function getOffersFromAllPages(){
			$allOffers = array();	
			//Page will be containing that code if there is more than on page of search results		
			$nextPageHtmlCode=$this->nextPageHtmlCode;
			$content = file_get_contents($this->url);

			$allOffers = array_merge($allOffers,$this->printOffers($this->url));
			$nextPageAvailable = strpos($content, $nextPageHtmlCode)?true:false;
			//Start from page 2
			$page = 2;
			
			while($nextPageAvailable){
				//Url leading to next page
				$newUrl = strval($this->url.$this->nextPageQuery.$page);				
				$newContent = file_get_contents($newUrl);

				$nextPageAvailable = strpos($newContent, $nextPageHtmlCode)?true:false;
				$thisPageOffers = $this->printOffers($newUrl);
				//Add offers from this page to all offers array
				$allOffers = array_merge($allOffers, $thisPageOffers);
				$page++;
			}		
			return $allOffers;
		}
	}
		
?>


