<?php  defined('C5_EXECUTE') or die('Access Denied');

class ApiRegister extends Object {

	public function add($api) {

		if(!self::apiRouteExists($api)) {
			$db = Loader::db();
			$db->Execute('insert into ApiRouteRegistry (api) values (?)', array($api));
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
		$r = $db->GetOne("select count(ID) from ApiRouteRegistry where route = ?", array($api));
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