<?php  defined('C5_EXECUTE') or die('Access Denied');

class ApiRegister extends ApiPackage{

	public function add($event) {
		if(!self::eventExists($event)) {
			$db = Loader::db();
			$db->Execute('insert into ApiRouteTesterApiRoutes (event) values (?)', array(self::CleanApiRoute($event)));
			$ID = $db->Insert_ID();
			return self::getByID($ID);
		}
		throw new Exception(t('An event with that name already exists!'));
	}

	public function getByApiRoute($event) {
		$db = Loader::db();
		$row = $db->GetRow("select * from ApiRouteTesterApiRoutes where handle = ?", array(self::CleanApiRoute($event)));
		if ($row) {
			$ID = $row['ID'];
			return self::getByID($ID);
		}
	}

	public function getByID($ID) {
		$db = Loader::db();
		$row = $db->GetRow("select * from ApiRouteTesterApiRoutes where ID = ?", array($ID));
		if ($row) {
			$et = new self();
			$et->setPropertiesFromArray($row);
			return $et;
		}
	}

	private function ApiRouteExists($event) {
		$db = Loader::db();
		$r = $db->GetOne("select count(ID) from ApiRouteTesterApiRoutes where event = ?", array(self::CleanApiRoute($event)));
		return $r > 0;
	}

	public function getID() {return $this->ID;}
	public function getApiRoute() {return $this->event;}

	public function getApiRouteList() {
		$db = Loader::db();
		$events = array();
		$r = $db->Execute('select ID from ApiRouteTesterApiRoutes order by ID asc');
		while ($row = $r->FetchRow()) {
			$events[] = self::getByID($row['ID']);
		}
		return $events;
	}


}