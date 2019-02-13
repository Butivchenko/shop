<?php
class shop {
	private $registry;
	private $ShopConfig;
	
	private $LastParam;
	private $LastParamM1;
	
	private $Content;
	private $Title;
	private $Keywords;
	private $Description;
	private $NewArt;
	
	private $Divs = Array();
	private $Division;
	private $CName;
	private $MName;

	private $TestDivision0;
	private $TestDivision1;
	private $TestDivision2;
	private $TestDivision3;

	private $TestItemInfo;

	private $TestParentURL;

	private $Comments2;

	private $TestCaunt;
	private $NDivID;

	private $Paged;

	private $FullUrl;
	
	private $isItem = false;
	private $Items = Array();
	private $Item;
	private $ItemInfo;
	private $Color;
	
	private $e404 = true;
	
	private $Breadcrumbs = Array();

	private $parent_ids = array();
	private $mnames = array();
	private $tagsArray = array();
	private $tagsStr = '';

	public function __construct() {
		$this->registry = registry::instance();
		$this->ShopConfig = shopconfig :: getInstance();
		
		$this->setLastParam();
		
		if($this->registry->get('param1') == $this->ShopConfig->getParam('CartUrl')) {
			$this->e404 = false;
			$Cart = new cart();
			$this->Content = $Cart->Content();
			return;
		}

		if($this->registry->get('param1') == $this->ShopConfig->getParam('ShopUrl') and !$this->registry->get('param2') and !$this->registry->get('shopsearch')) {
			$this->e404 = false;
			return;
		}
		
		if(!$this->registry->get('param2') && !$this->registry->get('shopsearch')) {
			$this->e404 = false;
			$this->Divs = getdivs :: getDivsByIdParent();
			$this->Divs = array_splice($this->Divs, 0, -1);
		}
		
		if(!$this->registry->get('param2') && $this->registry->get('shopsearch')) {
			$this->e404 = false;
			$getitems = new getitems();
			$this->Items = $getitems->getItemsByArtOrName($this->registry->get('shopsearch'));
		}

		if($this->registry->get('page')) {
			$this->paged = $this->registry->get('page');
		} else {
			$this->paged = 1;
		}
		
		$this->Division = shopdivision :: DivisionByUrl($this->LastParam);
		if($this->Division) {
			
			$getitems = new getitems();
			$this->Items = $getitems->getItemsByDiv($this->Division->getId(), true);
		
				$this->Title = $this->Division->getTitle();
				if(!$this->Title) $this->Title = $this->Division->getName();
				$this->Keywords = $this->Division->getKeywords();
				$this->Description = $this->Division->getDescription();
		}
		
		if(!$this->Division) {
			$this->ItemInfo = item :: ItemByUrl($this->LastParam);
			if($this->ItemInfo) {
				$itemDivision = $this->ItemInfo->getDivision();
				$urlDivision = shopdivision :: DivisionByUrl($this->LastParamM1);
				if(!$itemDivision or !$urlDivision) {
					$this->e404 = true;
				}
				
				if($urlDivision && $urlDivision->getId() == $itemDivision) $this->Item = $this->ItemInfo->getId();
			}
		}
		 
		if(!empty($this->Divs)) {
			$divsviewer = new divsviewer($this->Divs);
			$this->Content = $divsviewer->DivsViewer();
		}
		
		if($this->registry->get('param2') == 'novinki') {
			$getitems = new getitems();
			$this->Items = $getitems->getItemsNew();

			$this->TestCaunt = $getitems->CountSpecialNew($this->paged);
		}
		
		if($this->registry->get('param2') == 'ot3') {
			$getitems = new getitems();
			$this->Items = $getitems->getItemsOt3();

			$this->TestCaunt = $getitems->CountSpecialOt3($this->paged);
		}

		if($this->registry->get('param2') == 'hit') {
			$getitems = new getitems();
			$this->Items = $getitems->getItemsHit();

			$this->TestCaunt = $getitems->CountSpecialHit($this->paged);
		}
		
		
		
		if($this->Division) {
			$this->e404 = false;
			$this->NDivID = $getitems->CountShopPages($this->Division->getId(), $this->paged);
			if($this->paged == 1) {
				if (strpos($_SERVER['REQUEST_URI'], 'allproduct') !== false) {
					$this->Content .= "<h1>Все товары категории \"".$this->Division->getName()."\"</h1>";
				} else {
					$this->Content .= "<h1>".$this->Division->getName()."</h1>".$this->Division->getInfo2();
				}
			} else {
				$this->Content .= "<h1>".$this->Division->getName().' — страница '.$this->paged.' из '.$this->NDivID."</h1>";
			}
			$this->CName = $this->Division->getName();
			$this->MName = $this->Division->getXName();

			if($this->registry->get('param2') && $this->Division)
			$this->Breadcrumbs = array('Главная'=>'/', 'Каталог'=>'/'.$this->ShopConfig->getParam('ShopUrl'), $this->Division->getXName()?$this->Division->getXName():$this->Division->getName()=>'');
		}
		
		if(!empty($this->Items)) {
			$this->e404 = false;
			if($this->registry->get('param2')) {
				
				$MMPR = $getitems->getMinMaxPrice();	
				if($MMPR[0] > 0 && $MMPR[1] > 0)
				$shopsearch = new shopsearch($this->Items, $getitems->getMinMaxPrice());
				else
				$shopsearch = new shopsearch($this->Items);
				$this->Content .= $shopsearch->Content();
			}
			
			$itemsviewer = new itemsviewer($this->Items);
			$this->Content .= $itemsviewer->ItemsViewer();
			
			if($this->Division) {
				require_once $this->registry->get('AbsolutePath').'modules/shop/class.subdivisionsviewer.php';
				/***** Popular_Block *****/
				$divId = $this->Division->getId();
				$ParendId = $this->Division->getParent();

				$DopComments = getitems :: getComments2($divId);
				

				if(!$this->registry->get('param3')) {
					$divId = $this->Division->getId();
				}else{
					//$ParendId = $this->Division->getParent();
					//$divId = $divParId-;
				}

				if ($ParendId == 0) {
					$DopItems = getitems :: getItemsDiv4($divId);
					$itemsviewer = new itemsviewer($DopItems);
					$this->Comments2 = $DopComments;
				} else {
					$DopItems = getitems :: getItemsDiv4($ParendId);
					$itemsviewer = new itemsviewer($DopItems);
					$this->Comments2 = '';
				}

				if(strpos($_SERVER['REQUEST_URI'], '?page') !== false or strpos($_SERVER['REQUEST_URI'], 'search') !== false or strpos($_SERVER['REQUEST_URI'], 'sort') !== false) {
					$this->Comments2 = '';
				}

					$total_length = strlen($DopItems);

					$this->TestCaunt = $getitems->CountShopItems($divId, $this->paged);
					$this->Content .= $this->TestCaunt;
					
					if($total_length !== 0) {
						$this->Content .= "<div data-id='".$divId."' class='s-etim-tovarom'><h3>Популярные товары</h3>";
						$this->Content .= $itemsviewer->ItemsViewerPopular();
						$this->Content .= "</div>";
					}

					$this->Content .= $this->Comments2;
				
				
				/***** End_Popular *****/
				
				if(!$this->registry->get('param3')) {
					$divTop = $this->Division;
				} else {
					$divTop = new shopdivision($this->Division->getParent());
				}	
				$this->Content .= new subdivisionsviewer($divTop);
				}
				if($this->Division && $this->Division->getInfo() and $this->paged == 1) {

			 		$subdivisionsSql = mysql_query("SELECT url, mname FROM shop_divisions WHERE parent_id = ".$this->Division->getParent());

			 		$url = $_SERVER["REQUEST_URI"];
			 		$url_arr = explode('/', $url);

					while($subdivision = mysql_fetch_assoc($subdivisionsSql)) {	

						if($subdivision['mname'] != '' and end($url_arr) != $subdivision['url']){
							$tagsStr .= "<div><a href='". $subdivision['url'] ."'>".$subdivision['mname']."</a> | </div>";
						}else{
							continue;
						}
						
					}

					if( $this->Division->getParent() != 0 ){
						$this->Content .= "<div class='tags'>" . $tagsStr . "</div>";
					}

					$this->Content .= '<div id="div-info">'.$this->Division->getInfo().'</div>
					<div id="chd"><img src="/template/images/chd.png" id="chdimg"></div>';
					
				}
		}
		
		if(!empty($this->Item)) {
			$this->e404 = false;
			$this->isItem = true;
			$iteminfoviewer = new iteminfoviewer($this->Item);
			$this->Content = $iteminfoviewer->View();
			
			$Division = new shopdivision($this->ItemInfo->getDivision());
			
			$this->Breadcrumbs = array('Главная'=>'/', 'Каталог'=>'/'.$this->ShopConfig->getParam('ShopUrl'), $Division->getXName()?$Division->getXName():$Division->getName()=>$Division->getDivLastHref(), $this->ItemInfo->getName().' '.$this->ItemInfo->getArt()=>'');
			
			$this->Title = $this->ItemInfo->getTitle();
			if(!$this->Title) $this->Title = $this->ItemInfo->getName()." ".$this->ItemInfo->getArt();
			
			 $this->Keywords = $this->ItemInfo->getKeywords();
			$this->Description = $this->ItemInfo->getDescription();
			$this->NewArt = $this->ItemInfo->getArt();
			$this->Names = $this->ItemInfo->getName();

			$this->Color = $iteminfoviewer->getItemColor();
		}
		if($this->NDivID < $this->registry->get('page')) {
			$this->e404 = true;
		}
		if($this->registry->get('param2') and !$this->registry->get('shopsearch')) {

			if ($this->registry->get('param2') and $this->registry->get('param3') and !$this->registry->get('param4')) {

				$this->TestDivision0 = shopdivision :: DivisionByUrl($this->registry->get('param2'));
				$this->TestDivision1 = shopdivision :: DivisionByUrl($this->registry->get('param3'));

				if(!$this->TestDivision0) {
					$this->e404 = true;
				}

				/*** parent divisions ***/
				if($this->isItem !== true and $this->TestDivision1) {
					$this->TestParentURL1 = "/shop/".$this->registry->get('param2')."/".$this->registry->get('param3');

					$this->FullUrl = $this->Division->getHref();

					if($this->TestParentURL1 !== $this->FullUrl) {
					 	$this->e404 = true;
					}
				}
				/*** ***/

			} elseif ($this->registry->get('param3') and $this->registry->get('param4') and !$this->registry->get('param5')) {

				$this->TestDivision0 = shopdivision :: DivisionByUrl($this->registry->get('param2'));
				$this->TestDivision1 = shopdivision :: DivisionByUrl($this->registry->get('param3'));
				$this->TestDivision2 = shopdivision :: DivisionByUrl($this->registry->get('param4'));

				if(!$this->TestDivision1 or !$this->TestDivision0) {
					$this->e404 = true;
				}

				/*** parent division ***/
				if($this->isItem !== true and $this->TestDivision1 and $this->TestDivision2) {
					$this->TestParentURL1 = "/shop/".$this->registry->get('param2')."/".$this->registry->get('param3')."/".$this->registry->get('param4');

					$this->FullUrl = $this->Division->getHref();

					if($this->TestParentURL1 !== $this->FullUrl) {
					 	$this->e404 = true;
					}
				}
				/*** ***/

			} elseif ($this->registry->get('param3') and $this->registry->get('param4') and $this->registry->get('param5') and !$this->registry->get('param6')) {

				$this->TestDivision0 = shopdivision :: DivisionByUrl($this->registry->get('param2'));
				$this->TestDivision1 = shopdivision :: DivisionByUrl($this->registry->get('param3'));
				$this->TestDivision2 = shopdivision :: DivisionByUrl($this->registry->get('param4'));

				if(!$this->TestDivision1 or !$this->TestDivision1 or !$this->TestDivision2) {
					$this->e404 = true;
				}

				/*** parent division ***/
				if($this->isItem !== true) {
					$this->TestParentURL1 = "/shop/".$this->registry->get('param2')."/".$this->registry->get('param3')."/".$this->registry->get('param4')."/".$this->registry->get('param5');

					$this->FullUrl = $this->Division->getHref();

					if($this->TestParentURL1 !== $this->FullUrl) {
					 	$this->e404 = true;
					}
				}
				/*** ***/
				
			} elseif ($this->registry->get('param3') and $this->registry->get('param4') and $this->registry->get('param5') and $this->registry->get('param6')) {

				$this->TestDivision0 = shopdivision :: DivisionByUrl($this->registry->get('param2'));
				$this->TestDivision1 = shopdivision :: DivisionByUrl($this->registry->get('param3'));
				$this->TestDivision2 = shopdivision :: DivisionByUrl($this->registry->get('param4'));
				$this->TestDivision3 = shopdivision :: DivisionByUrl($this->registry->get('param5'));

				if(!$this->TestDivision1 or !$this->TestDivision1 or !$this->TestDivision2 or !$this->TestDivision3) {
					$this->e404 = true;
				}
			}
		}
		
		if(empty($this->Item) && empty($this->Items)) $this->Content .= '<h2>Ничего не найдено</h2>';
	}

	public function getNames() {
		return $this->Names;
	}
	public function getNamesis() {
		$namesis = $this->Names;
		$namesis .= " ".$GLOBALS['myNewSizes'];
		$namesis .= ", купить, заказать, опт, оптом, Россия ";
		return $namesis;
	}


	
	public function isItem() {
		return $this->isItem;
	}
	public function Content() {
		if($this->registry->get('param2') == 'novinki' or $this->registry->get('param2') == 'hit' or $this->registry->get('param2') == 'ot3') {
			$this->Content .= $this->TestCaunt;
		}
		return $this->Content;
	}
	public function getTitle() {
		if($this->Title) return $this->Title; 
	}
	public function getCName() {
		if($this->CName) return $this->CName;
	}
	public function getMName() {
		if($this->MName) return $this->MName;
	}
	public function getKeywords() {
		return $this->Keywords;
	}
	public function NewArt() {
		return $this->NewArt;
	}

	public function getDescription() {
		return $this->Description;
	}
	public function Breadcrumbs() {
		return $this->Breadcrumbs;
	}

	public function ShopPagesNest() {
		if (!empty($this->TestCaunt)) {
			$nstr = $this->TestCaunt;
			$npos = strpos($nstr, "&");
			$nstr = substr($nstr, $npos+1, -7);
			$pages = $nstr;
		} else {
			$pages = 0;
		}
		return $pages;
	}

	public function ColPage() {
		if($this->NDivID) return $this->NDivID;
	}
	
	public function e404() {
		return $this->e404;
	}

	public function lastCooments() {
		return $this->Comments2;
	}
	
	private function setLastParam() {
		for($c=1;$c<=10;$c++) {
			if($this->registry->get('param'.$c)) {
				if($c > 1) $this->LastParamM1 = $this->registry->get('param'.($c-1));
				$this->LastParam = $this->registry->get('param'.$c); 
			}
			
			else return;
		}
	}

	public function getColor() {
		return $this->Color;
	}

	public function getItemInfo() {
		return $this->ItemInfo;
	}	
}