<?php defined('C5_EXECUTE') or die("Access Denied.");

class ApiUser {
	
	public function listUsers() {
		Loader::model('user_list');
		$ul = new UserList();
		$ul->setItemsPerPage(-1);
		return $ul->getPage();
	}
}