<?php 
namespace Concrete\Block\DateNav;
use Loader;
use \Concrete\Core\Block\BlockController;
class Controller extends BlockController {

	protected $btTable = 'btDateNav';
	protected $btInterfaceWidth = "500";
	protected $btInterfaceHeight = "350";
	protected $btExportPageColumns = array('cParentID');
	protected $btExportPageTypeColumns = array('ptID');
	protected $btCacheBlockRecord = true;
	protected $btWrapperClass = 'ccm-ui';
	
	/** 
	 * Used for localization. If we want to localize the name/description we have to include this
	 */
	public function getBlockTypeDescription() {
		return t("A collapsible date based navigation tree");
	}
	
	public function getBlockTypeName() {
		return t("Date Navigation");
	}
	
	public function getJavaScriptStrings() {
		return array( 
		);
	}
	
	function getPages($query = null) {
		$db = Loader::db();
		$bID = $this->bID;
		if ($this->bID) {
			$q = "select * from btDateNav where bID = '$bID'";
			$r = $db->query($q);
			if ($r) {
				$row = $r->fetchRow();
			}
		} else {
			$row['num'] = $this->num;
			$row['cParentID'] = $this->cParentID;
			$row['cThis'] = $this->cThis;
			$row['orderBy'] = $this->orderBy;
			$row['ptID'] = $this->ptID;
			$row['rss'] = $this->rss;
		}

		$pl = new PageList();
		$pl->setNameSpace('b' . $this->bID);
		
		$cArray = array();

		//$pl->sortByPublicDate();
		$pl->sortByPublicDateDescending(); 

		$num = (int) $row['num'];
		
		if ($num > 0) {
			$pl->setItemsPerPage($num);
		}

		$c = $this->getCollectionObject();
		if (is_object($c)) {
			$this->cID = $c->getCollectionID();
		}
		$cParentID = ($row['cThis']) ? $this->cID : $row['cParentID'];
		
		if ($this->displayFeaturedOnly == 1) {
			
			$cak = CollectionAttributeKey::getByHandle('is_featured');
			if (is_object($cak)) {
				$pl->filterByIsFeatured(1);
			}
		}
		
		$pl->filter('cvName', '', '!=');			
	
		if ($row['ptID']) {
			$pl->filterByPageTypeID($row['ptID']);
		}
		
		$pl->filterByAttribute('exclude_nav',false);

		if ($row['cParentID'] != 0) {
			$pl->filterByParentID($cParentID);
		}

		if ($num > 0) {
			$pages = $pl->getPage();
		} else {
			$pages = $pl->get();
		}
		$this->set('pl', $pl);
		return $pages;
	}
	
	public function edit() {
		$c = Page::getCurrentPage();
		if ($c->getCollectionID() != $this->cParentID && (!$this->cThis) && ($this->cParentID != 0)) { 
			$isOtherPage = true;
			$this->set('isOtherPage', true);
		}
	}
	
	public function view() {
		$cArray = $this->getPages();
		$nh = Loader::helper('navigation');
		$this->set('nh', $nh);
		$this->set('cArray', $cArray);
	}
	
	function save($args) {
		// If we've gotten to the process() function for this class, we assume that we're in
		// the clear, as far as permissions are concerned (since we check permissions at several
		// points within the dispatcher)
		$db = Loader::db();

		$bID = $this->bID;
		$c = $this->getCollectionObject();
		if (is_object($c)) {
			$this->cID = $c->getCollectionID();
		}
		
		$args['num'] = ($args['num'] > 0) ? $args['num'] : 0;
		$args['cThis'] = ($args['cParentID'] == $this->cID) ? '1' : '0';
		$args['cParentID'] = ($args['cParentID'] == 'OTHER') ? $args['cParentIDValue'] : $args['cParentID'];
		$args['truncateSummaries'] = ($args['truncateSummaries']) ? '1' : '0';
		$args['truncateTitles'] = ($args['truncateTitles']) ? '1' : '0';
		$args['displayFeaturedOnly'] = ($args['displayFeaturedOnly']) ? '1' : '0';
		$args['truncateChars'] = intval($args['truncateChars']);
		$args['truncateTitleChars'] = intval($args['truncateTitleChars']);
		$args['showDescriptions'] = ($args['showDescriptions']) ? '1' : '0';		

		parent::save($args);		
	} 
}
