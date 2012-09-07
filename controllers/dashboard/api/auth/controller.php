<?php defined('C5_EXECUTE') or die('Access Denied');

class DashboardApiAuthController extends DashboardBaseController {
	
	public function view() {
		$categories = array();
		$c = Page::getCurrentPage();
		$children = $c->getCollectionChildrenArray(true);
		foreach($children as $cID) {
			$nc = Page::getByID($cID, 'ACTIVE');
			$ncp = new Permissions($nc);
			if ($ncp->canRead()) {
				$this->redirect($nc->getCollectionPath());
			}
		}
		$parent = $c->getCollectionParentID();
		$parent = Page::getByID($parent);
		$this->redirect($parent->getCollectionPath());
	}

}