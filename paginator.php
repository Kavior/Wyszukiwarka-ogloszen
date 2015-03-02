<?php

	//Class which will output offers to the page
	class offersPaginator{
		public function __construct($resultsPerPage, $allOffers){
			$this->resultsPerPage = $resultsPerPage;
			$this->allOffers = $allOffers;
			
		}
		
		public function printOffer($offerTitle, $details){
			//Strlen>5 helps avoid printing broken offers
			if(strlen($offerTitle)>5){
				$offerPrice = $details[0];
				$offerImage =  $details[1];
				//Replacement for allegro purposes
				$replacements= array(',"'=>'','",'=>'');
				$trimmedImg = strtr($offerImage, $replacements);	
				//If offer contains image, print it, if not, leave empty place
				if($trimmedImg!==' '){
					$img = '<img class="offerImage" src="'.$trimmedImg.'">';
				}else{
					$img = '';
				}												
				echo '<div class="wholeOffer"><div class="title">'.$offerTitle.'</div>'.$img.'<div class="price">Cena: '.$offerPrice.' zł</div>
				</div>';
			}			
		}
		
		public function getCertainElements(){
			$resultsPerPage  =$this->resultsPerPage;
			$allOffers = $this->allOffers;
			//When on page 2 and more
			if(isset( $_GET['page'])){
				$pageNumber = $_GET['page'];
				//Start and end are indexes equal to offers
				$start = $pageNumber * $resultsPerPage - $resultsPerPage;
				$end = $start +$resultsPerPage -1;
				$i = $start;
				foreach(@$allOffers as $title=>$details){
					$index = array_search($title,array_keys($allOffers));
					while(($index>=$i) && ($index<=$end)){
						$this->printOffer($title, $details);
						$i++;		
					}
				}												
			}else{
				foreach(@$allOffers as $title=>$details){
					$index = array_search($title,array_keys($allOffers));
					//Print only one page
					if($index<=$resultsPerPage){
						$this->printOffer($title, $details);
					}
				}
			}
		} // getCertainElements end
		
		//Make current page link bold and red
		public function stylePageLink($i){
			if(!isset($_GET['page']) && $i==1){
				return 'class="bold"';
			}else if(isset($_GET['page']) && $i==$_GET['page']){
				return 'class="bold"';
			}
			return 'class="normal"';
		}
		//Print links to pages
		public function printPageLinks(){
			if(isset($_GET['page'])){
				$pageNumber = $_GET['page'];
			}
			$resultsPerPage  =$this->resultsPerPage;
			$allOffers = $this->allOffers;
			$lastPage = ceil (count($allOffers)/$resultsPerPage);
			//if there is offers for more than 1 page
			if( $lastPage>1){
				$actualUrl = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
				//Remove actual page
				$urlWithoutPage = preg_replace('/&page=.{1,4}/','',$actualUrl);
				// If there are not more than 7 pages, print all
				if($lastPage<=7){
						echo '<a '.$this->stylePageLink(1).'href ="'.$urlWithoutPage.'">'.'1'.' </a> ';		
					for($i=2;$i<=$lastPage; $i++){
						echo '<a '.$this->stylePageLink($i).'href ="'.$urlWithoutPage.'&page='.$i.'">'.$i.' </a> ';					
					}
				//If more than 7 pages							
				}else{
					if(isset($pageNumber) && $pageNumber >5){
						echo '<a '.$this->stylePageLink(1).' href ="'.$urlWithoutPage.'">1</a> ... ';
						for($i=$pageNumber-3;$i<=$pageNumber;$i++){
							echo '<a '.$this->stylePageLink($i).'href ="'.$urlWithoutPage.'&page='.$i.'">'.$i.'</a> ';
						}
		
						$middleLastNumber=$pageNumber+4>=$lastPage?$lastPage:$pageNumber+4;
						for($i=$pageNumber+1;$i<=$middleLastNumber;$i++){
							echo '<a '.$this->stylePageLink($i).' href ="'.$urlWithoutPage.'&page='.$i.'">'.$i.'</a> ';
						}
						if($middleLastNumber<$lastPage){
							echo '... <a  '.$this->stylePageLink($i).' href ="'.$urlWithoutPage.'&page='.$lastPage.'">'.$lastPage.'</a> ';
						}
					}else{
						echo '<a '.$this->stylePageLink(1).' href ="'.$urlWithoutPage.'">'.'1'.'</a> ';	
						for($i=2;$i<=7;$i++){
							echo '<a '.$this->stylePageLink($i).' href ="'.$urlWithoutPage.'&page='.$i.'">'.$i.'</a> ';
						}
						echo '... <a '.$this->stylePageLink($i).' href ="'.$urlWithoutPage.'&page='.$lastPage.'">'.$lastPage.'</a> ';
						
					}
				}
	
				echo '<div id="goToPage"><span style="color:black; font-size:26px;">Idź do strony: </span><input  type="text" name="page" id="page">
				<button id="goButton">OK</button></div>';		
			}
		}
	}// offersPaginator end
	
?>