<?php

class getitems {
	private $registry;
	
	private $Items = Array();
	
	private $Price;
	private $Sort = ' order by sort';
	
	private $DivId;
	private $ShowSubDivs;
	
	private $ByBrand;

	private $page = 1;
	private $countOnPage = 16;
	private $CountItems;
	private $CountPages;
	
	private $SqlWithoutSearch = "SELECT shop_items.id FROM shop_items";
	private $PageLimit = ' limit 0, 16';
	
	private $SqlWithSearch = "SELECT shop_items.id FROM shop_items LEFT JOIN
	shop_items_price as price ON price.id_item=shop_items.id LEFT JOIN
    shop_items_rows_values as brand on brand.id_row='6' and
                                       brand.id_item=shop_items.id LEFT JOIN
    shop_items_rows_values as sezon on sezon.id_row='4' and
                                       sezon.id_item=shop_items.id LEFT JOIN
    shop_items_rows_values as color on color.id_row='14' and
                                       color.id_item=shop_items.id LEFT JOIN
    shop_items_rows_values as material on material.id_row='15' and
                                       material.id_item=shop_items.id LEFT JOIN
    shop_items_rows_values as sizes52 on sizes52.id_row='10' and
                                       sizes52.id_item=shop_items.id";
	
	private $SqlMinMaxPrice = "SELECT min(shop_items_price.price_sh), 
										max(shop_items_price.price_sh) 
										FROM shop_items INNER JOIN shop_items_price ON shop_items.id=shop_items_price.id_item ";

	public function __construct() {
		$this->registry = registry::instance();
		if($this->registry->get('sort')) $this->Sort = " order by shop_items.".$this->registry->get('sort');
		if($this->registry->get('price')) $this->Price = explode(';', $this->registry->get('price'));
		if($this->isSortByPrice()) $this->Sort = $this->sortByPrice();
		if($this->registry->get('page')) $this->page = $this->registry->get('page');
	}

	public function getItemsByDiv($DivId, $ShowSubDivs = false) {	
		
		$this->DivId = $DivId;
		$this->ShowSubDivs = $ShowSubDivs;

		if($this->registry->get('page')) {
			$this->page = $this->registry->get('page');
			$this->PageLimit = " limit ".(($this->page-1)*$this->countOnPage).", ".$this->countOnPage;
		}

		if($this->registry->get('allproduct') == 'allproduct') {

			$Sql = 	$this->SqlWithoutSearch.
					$this->byTagSql().
					$this->byDivisions('where').
					$this->notArchive().
					$this->Sort;

		} elseif ($this->registry->get('search') != 'search') {

			$Sql = 	$this->SqlWithoutSearch.
					$this->byTagSql().
					$this->byDivisions('where').
					$this->notArchive().
					$this->Sort.
					$this->PageLimit;			
				
		} else {
			$Sql = 	$this->SqlWithSearch.
					$this->byTagSql().
					$this->byDivisions('where').
					$this->bySezon().
					$this->byColor().
					$this->byMaterial().
					$this->byBrand().
					$this->byRazmer52().
					$this->PriceLimit().
					$this->notArchive().
					$this->Sort;
					
		}

		$ItemsSql = mysql_query($Sql);
		
		while($Item = mysql_fetch_array($ItemsSql)) {
			$this->Items[] = $Item['id'];
		}
		return $this->Items;
	}
	
	public function getMinMaxPrice() {
		$Sql = 		$this->SqlMinMaxPrice.
					$this->byDivisions('and').
					$this->notArchive().
					$this->byArtOrName('and', $this->registry->get('shopsearch'));
		//echo $Sql;			
		$MinMaxPrice = mysql_fetch_array(mysql_query($Sql));
		return array($MinMaxPrice[0], $MinMaxPrice[1]);
	}
	
	public function getItemsNew() {	
		if($this->registry->get('page')) {
			$this->page = $this->registry->get('page');
			$this->PageLimit = " limit ".(($this->page-1)*$this->countOnPage).", ".$this->countOnPage;
		}
		if($this->registry->get('allproduct') == 'allproduct') {

			$Sql = 	$this->SqlWithSearch.
					$this->byNew('where').
					$this->bySezon().
					$this->byBrand().
					$this->byRazmer52().
					$this->PriceLimit().
					$this->notArchive().
					$this->Sort;
		}else{

		$Sql = 	$this->SqlWithSearch.
					$this->byNew('where').
					$this->bySezon().
					$this->byBrand().
					$this->byRazmer52().
					$this->PriceLimit().
					$this->notArchive().
					$this->Sort.
					$this->PageLimit;	
		}				

		$ItemsSql = mysql_query($Sql);
		
		while($Item = mysql_fetch_array($ItemsSql)) {
			$this->Items[] = $Item['id'];
		}
		return $this->Items;
	}
	
	public function getItemsHit() {
		if($this->registry->get('page')) {
			$this->page = $this->registry->get('page');
			$this->PageLimit = " limit ".(($this->page-1)*$this->countOnPage).", ".$this->countOnPage;
		}
		if($this->registry->get('allproduct') == 'allproduct') {

			$Sql = 	$this->SqlWithSearch.
					$this->byHit('where').
					$this->bySezon().
					$this->byBrand().
					$this->byRazmer52().
					$this->PriceLimit().
					$this->notArchive().
					$this->Sort;
		}else{
			$Sql = 	$this->SqlWithSearch.
					$this->byHit('where').
					$this->bySezon().
					$this->byBrand().
					$this->byRazmer52().
					$this->PriceLimit().
					$this->notArchive().
					$this->Sort.
					$this->PageLimit;
		}
					
		$ItemsSql = mysql_query($Sql);
		
		while($Item = mysql_fetch_array($ItemsSql)) {
			$this->Items[] = $Item['id'];
		}
		return $this->Items;
	}
	
	public function getItemsOt3() {	
		if($this->registry->get('page')) {
			$this->page = $this->registry->get('page');
			$this->PageLimit = " limit ".(($this->page-1)*$this->countOnPage).", ".$this->countOnPage;
		}

		if($this->registry->get('allproduct') == 'allproduct') {

			$Sql = 	$this->SqlWithSearch.
					$this->byOt3('where').
					$this->bySezon().
					$this->byBrand().
					$this->byRazmer52().
					$this->PriceLimit().
					$this->notArchive().
					$this->Sort;
		}else{
			$Sql = 	$this->SqlWithSearch.
					$this->byOt3('where').
					$this->bySezon().
					$this->byBrand().
					$this->byRazmer52().
					$this->PriceLimit().
					$this->notArchive().
					$this->Sort.
					$this->PageLimit;
		}		
		$ItemsSql = mysql_query($Sql);
		
		while($Item = mysql_fetch_array($ItemsSql)) {
			$this->Items[] = $Item['id'];
		}
		return $this->Items;
	}
	
	public function getItemsNewHit($Type) {	
		
		$Type = ' and '.$Type.' = "1" ';
		
		$Sql = 	$this->SqlWithSearch.
					$this->bySezon().
					$this->byBrand().
					$this->byRazmer52().
					$this->PriceLimit().
					$this->notArchive().
					$Type.
					$this->Sort;
				
		$ItemsSql = mysql_query($Sql);
		
		while($Item = mysql_fetch_array($ItemsSql)) {
			$this->Items[] = $Item['id'];
		}
		return $this->Items;
	}

	public function getAllItems() {
		$Items = Array();
		$ItemsSql = mysql_query("select * from shop_items");
		while($Item = mysql_fetch_array($ItemsSql)) {
			$Items[] = $Item['id'];
		}
		return $Items;
	}
	
	public function getItemsByArtOrName($ArtOrName) {
		$Sql = 	$this->SqlWithSearch.
					$this->byArtOrName('where', $ArtOrName).
					$this->bySezon().
					$this->byBrand().
					$this->byRazmer52().
					$this->PriceLimit().
					$this->notArchive().
					$this->Sort;
		
		$ItemsSql = mysql_query($Sql);
		
		while($Item = mysql_fetch_array($ItemsSql)) {
			$this->Items[] = $Item['id'];
		}
		return $this->Items;
	}
	
	private function getParentDivs() {
		$ParentDivs = '';
		if($this->ShowSubDivs && mysql_result(mysql_query("select count(*) from shop_divisions where parent_id = '".$this->DivId."'"), 0)) {
			$ParentDivsSql = mysql_query("select id from shop_divisions where parent_id = '".$this->DivId."'");
			while($ParentDiv = mysql_fetch_array($ParentDivsSql)) {
				$ParentDivs .= " or shop_items.division = '".$ParentDiv['id']."' or shop_items.divisions like '%;".$ParentDiv['id'].";%'";
			}
		}
		return $ParentDivs;
	}
	
	private function byTagSql() {
		if($this->registry->get('tag')) {
			$Tag = tags::tagByUrl($this->registry->get('tag'));
			if($Tag) return " INNER JOIN divisions_tags_items as tag on shop_items.id = tag.item_id and tag.tag_id = '".$Tag['id']."'";
		}
	}
	
	private function byTag() {
		
	}

	/* */
	public static function ShopPagesMeta() {

	}
	public static function CountShopItems($DivId, $CurentPage) {
		$DivId = $DivId;
		$CountItems = mysql_result(mysql_query("SELECT count(*) FROM shop_items WHERE (shop_items.division = '".$DivId."' or shop_items.divisions like '%;".$DivId.";%') and shop_items.archive != 1"), 0);
		$countOnPage = 16;
		$CountPages = ceil($CountItems/$countOnPage);

		if($CountPages > 1) {
			$content = '<div class="shop-pages">Страницы: ';
			for($c = 1; $c <= $CountPages; $c++) {
				if($CurentPage == $c) {
					$content .= '<span>'.$c.'</span>';
				} else {
					if ($c == 1) {
						$s = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
						$s2 = '?page='.substr($s, -1);
						$s = str_replace($s2,'',$s);
						$content .= '<a href="'.$s.'">'.$c.'</a>';
					} else {
						$content .= '<a href="?page='.$c.'">'.$c.'</a>';
					}
					
				}
			}
			if(strpos($_SERVER['REQUEST_URI'], 'allproduct') !== false) {
				$content .= '<span>Все товары категории</span>';
			} else {
				$content .= '<a href="?allproduct=allproduct">Все товары категории</a>';
			}
			$content .= '</div><span>&'.$c.'</span>';

			$url = $_SERVER['REQUEST_URI'];
			$splits = split("/", $url);
			//$tags_shopcustomtags = shopcustomtags(); 
			//
		}
		
		return $content;
	}

	public function shopcustomtags() {	
		// $sql = "SELECT count(*) FROM shop_divisions WHERE url = '". $url ."'";

		// $result = mysql_query($sql);
		// $subdivision = mysql_fetch_assoc($result)

		// }
		//return true;
	}
	

	public static function CountShopPages($DivId, $CurentPage) {
		$DivId = $DivId;
		$CountItems = mysql_result(mysql_query("SELECT count(*) FROM shop_items WHERE (shop_items.division = '".$DivId."' or shop_items.divisions like '%;".$DivId.";%') and shop_items.archive != 1"), 0);
		$countOnPage = 16;
		$CountPages = ceil($CountItems/$countOnPage);
		return $CountPages;
	}

	public static function CountSpecialNew($CurentPage) {
		$CountItems = mysql_result(mysql_query("SELECT count(*) FROM shop_items WHERE shop_items.archive != 1 and shop_items.new = 1"), 0);
		$countOnPage = 16;
		$CountPages = ceil($CountItems/$countOnPage);

		if($CountPages > 1) {
			$content = '<div class="shop-pages">Страницы: ';
			for($c = 1; $c <= $CountPages; $c++) {
				if($CurentPage == $c) {
					$content .= '<span>'.$c.'</span>';
				} else {
					$content .= '<a href="?page='.$c.'">'.$c.'</a>';
				}
			}

			if(strpos($_SERVER['REQUEST_URI'], 'allproduct') !== false) {
				$content .= '<span>Все товары категории</span>';
			} else {
				$content .= '<a href="?allproduct=allproduct">Все товары категории</a>';
			}
			$content .= '</div><span>&'.$c.'</span>';
		}
		return $content;
	}

	public static function CountSpecialHit($CurentPage) {
		$CountItems = mysql_result(mysql_query("SELECT count(*) FROM shop_items WHERE shop_items.archive != 1 and shop_items.hit = 1"), 0);
		$countOnPage = 16;
		$CountPages = ceil($CountItems/$countOnPage);

		if($CountPages > 1) {
			$content = '<div class="shop-pages">Страницы: ';
			for($c = 1; $c <= $CountPages; $c++) {
				if($CurentPage == $c) {
					$content .= '<span>'.$c.'</span>';
				} else {
					$content .= '<a href="?page='.$c.'">'.$c.'</a>';
				}
			}
			if(strpos($_SERVER['REQUEST_URI'], 'allproduct') !== false) {
				$content .= '<span>Все товары категории</span>';
			} else {
				$content .= '<a href="?allproduct=allproduct">Все товары категории</a>';
			}
			$content .= '</div><span>&'.$c.'</span>';
		}

		return $content;
	}
	public static function CountSpecialOt3($CurentPage) {
		$CountItems = mysql_result(mysql_query("SELECT count(*) FROM shop_items WHERE shop_items.archive != 1 and shop_items.ot3 = 1"), 0);
		$countOnPage = 16;
		$CountPages = ceil($CountItems/$countOnPage);

		if($CountPages > 2) {
			$content = '<div class="shop-pages">Страницы: ';
			for($c = 1; $c <= $CountPages; $c++) {
				if($CurentPage == $c) {
					$content .= '<span>'.$c.'</span>';
				} else {
					$content .= '<a href="?page='.$c.'">'.$c.'</a>';
				}
			}
			if(strpos($_SERVER['REQUEST_URI'], 'allproduct') !== false) {
				$content .= '<span>Все товары категории</span>';
			} else {
				$content .= '<a href="?allproduct=allproduct">Все товары категории</a>';
			}
			$content .= '</div><span>&'.$c.'</span>';
		}

		return $content;
	}
	/* */

	private function byDivisions($AndWhere = 'where') {
		if($this->DivId) return " ".$AndWhere." (shop_items.division = '".$this->DivId."' or shop_items.divisions like '%;".$this->DivId.";%') ";
	}
	
	private function byArtOrName($WhereAnd, $ArtOrName) {
		if($ArtOrName)
		return " ".$WhereAnd." (shop_items.name like '%".$ArtOrName."%' or shop_items.art like '%".$ArtOrName."%') ";
	}
	
	private function byNew($WhereAnd = 'and') {
		return " ".$WhereAnd." shop_items.new = '1'";
	}
	
	private function byHit($WhereAnd = 'and') {
		return " ".$WhereAnd." shop_items.hit = '1'";
	}
	
	private function byOt3($WhereAnd = 'and') {
		return " ".$WhereAnd." shop_items.ot3 = '1'";
	}
	
	private function bySezon($WhereAnd = 'and') {
		if($this->registry->get('sezon'))
		return " ".$WhereAnd." sezon.value like '%;".$this->registry->get('sezon').";%' ";
	}

	private function byColor($WhereAnd = 'and') {
		if($this->registry->get('color'))
		return " ".$WhereAnd." color.value like '%;".$this->registry->get('color').";%' ";
	}
	private function byMaterial($WhereAnd = 'and') {
		if($this->registry->get('material'))
		return " ".$WhereAnd." material.value like '%;".$this->registry->get('material').";%' ";
	}

	private function byBrand($WhereAnd = 'and') {
		if($this->registry->get('shopbrand'))
		return " ".$WhereAnd." brand.value = '".$this->registry->get('shopbrand')."' ";
	}
	
	private function byRazmer52($WhereAnd = 'and') {
		if($this->registry->get('razmeri')&&$this->registry->get('razmeri')!='12')
		return " ".$WhereAnd." sizes52.value like '%;".$this->registry->get('razmeri').";%' ";
	}
	
	
	private function isSortByPrice() {
		if($this->registry->get('sort') == 'price' or $this->registry->get('sort') == 'price DESC') return 1;
	}
	
	private function sortByPrice() {
		return " order by price.price_sh ".($this->registry->get('sort') == 'price DESC'?'DESC':'ASC');
	}
	
	private function PriceLimit() {
		if($this->Price) {
			return ' and (price.price_sh >= "'.$this->Price[0].'" and price.price_sh <= "'.$this->Price[1].'") ';
		}
	}
	
	private function notArchive() {
		return " and shop_items.archive != '1' ";
	}
	
	public static function getItemsRand4() {
		$Sql = 	"SELECT id FROM shop_items where shop_items.archive != '1' order by rand() limit 0, 4";
					
		$ItemsSql = mysql_query($Sql);
		
		while($Item = mysql_fetch_array($ItemsSql)) {
			$Items[] = $Item['id'];
		}
		sort($Items);
		return $Items;
	}

	public static function getItemsDiv4($divId) {
		$divId = $divId;
		$Sql = 	"SELECT id FROM shop_items where shop_items.division = '".$divId."' and shop_items.archive != '1' order by rand() limit 0, 4";
					
		$ItemsSql = mysql_query($Sql);
		
		while($Item = mysql_fetch_array($ItemsSql)) {
			$Items[] = $Item['id'];
		}
		sort($Items);
		return $Items;
	}
	public static function getComments2($divId) {
		$divId = $divId;
		$Sql = 	"SELECT id FROM shop_items where shop_items.division = '".$divId."' and shop_items.archive != '1' order by rand() limit 0, 100";
					
		$ItemsSql = mysql_query($Sql);
		
		while($Item = mysql_fetch_array($ItemsSql)) {
			$Items[] = 'shop_item_'.$Item['id'];
		}

		if (count($Items) !== 0) {

			$CommentsSql = "SELECT * FROM `comments` WHERE `codename` IN (";
			foreach ($Items as $value) {
				$CommentsSql .= "'".$value."'".', ';
			}

			$CommentsSql = substr($CommentsSql, 0, -2);
			$CommentsSql .= ") ORDER BY `datetime` DESC LIMIT 0, 2";

			$CommSql = mysql_query($CommentsSql);

			$CommentDiv = "<div class='sidebar-comments'><div class='sidebar-com-title'>Последние отзывы</div>";
			while($Comm = mysql_fetch_array($CommSql)) {
				if($Comm['visibility'] == 1){
					$CommentDiv .= "<div class='comment-title'>".$Comm['name']."</div><div class='comment-text'>".$Comm['comment']."</div>";
				}
			}
			$CommentDiv .= "</div>";

		} else {
			$CommentDiv = '';
		}
		
		return $CommentDiv;
	}

	private function byDivisions4($AndWhere = 'where') {
		if($this->DivId) return " ".$AndWhere." (shop_items.division = '".$this->DivId."' or shop_items.divisions like '%;".$this->DivId.";%') ";
	}
}