<?php  defined('C5_EXECUTE') or die('Access Denied');

class ApiRegister extends Object {

	public function add($api) {

		if(!self::apiRouteExists($api)) {
			$db = Loader::db();
<<<<<<< HEAD
			$db->Execute('insert into ApiRouteRegistry (ID) values (?)', array($api));
=======
			$db->Execute('insert into ApiRouteRegistry (api) values (?)', array($api));
>>>>>>> origin/master
			$ID = $db->Insert_ID();
			return self::getByID($ID);
		}
		throw new Exception(t('An API route with that name already exists!'));
	}

	public function getByID($ID) {
		$db = Loader::db();
		$row = $db->GetRow("select * from ApiRouteRegistry where ID = ?", array($ID));
		if ($row) {
			$et = new self();
			$et->setPropertiesFromArray($row);
			return $et;
		}
	}

	private function apiRouteExists($api) {
		$db = Loader::db();
<<<<<<< HEAD
		$r = $db->GetOne("select count(ID) from ApiRouteRegistry where pkgHandle = ?", array($api));
=======
		$r = $db->GetOne("select count(ID) from ApiRouteRegistry where event = ?", array($api));
>>>>>>> origin/master
		return $r > 0;
	}

	public function getID() {return $this->ID;}
	public function getApiRoute() {return $this->api;}

	public function getApiRouteList() {
		$db = Loader::db();
		$apis = array();
		$r = $db->Execute('select ID from ApiRouteRegistry order by ID asc');
		while ($row = $r->FetchRow()) {
			$apis[] = self::getByID($row['ID']);
		}
		return $apis;
	}


}