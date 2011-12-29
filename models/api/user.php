<?php defined('C5_EXECUTE') or die("Access Denied.");

class ApiUser {
	
	public function listUsers() {
		Loader::model('user_list');
		$ul = new UserList();
		$ul->setItemsPerPage(-1);
		return $ul->getPage();
	}
	
	public function addUser() {
		Loader::model('user_info');
		$resp = new ApiResponse();
		if(is_object(UserInfo::getByUserName($_POST['uName'])) || is_object(UserInfo::getByEmail($_POST['uEmail']))) {
			$resp->setError(true);
			$resp->setCode(409);
			$resp->setMessage(t('A user exists with that username or email.'));
			$resp->send();
		}
		$data = array();
		$data['uPassword'] = $_POST['uPassword'];
		$data['uName'] = $_POST['uName'];
		$data['uEmail'] = $_POST['uEmail'];
		
		$ui = UserInfo::add($data);
		if(is_object($ui)) {
			$resp->setData($ui);
			$resp->send();
		} else {
			$resp->setError(true);
			$resp->setCode(500);
			$resp->setMessage(t('An unknown error has occured.'));
			$resp->send();
		}
	}
}