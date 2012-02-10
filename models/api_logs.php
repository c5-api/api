<?php class ApiLogEntry extends Object {

	public function getType() {return $this->rtype;}
	public function getData() {return $this->data;}
	public function getRoute() {return $this->route;}
	public function getID() {return $this->ID;}
	public function getPackage() {return $this->pkgHandle;}
	public function getTime() {return $this->time;}
	public function getTimestamp() {return $this->timestamp;}

	/** 
	 * Returns a log entry by ID
	 */
	public static function getByID($logID = 0) {
		$db = Loader::db();
		$r = $db->Execute("select * from ApiLogs where ID = ?", array($logID));
		if ($r) {
			$row = $r->FetchRow();
			$obj = new ApiLogEntry();
			$obj->setPropertiesFromArray($row);
			return $obj;
		}
	}


}

 
class ApiLog {

	private $data = '';
	private $type;
	private $route;
	private $pkgHandle;
	private $time = 0;
	private $session = false;

	public function __construct($type = false, $route = '', $pkgHandle = false, $session = true, $time = 0) {
		if ($type == false) {
			$type = t('unknown');
		}
		$this->type = $type;
		$this->session = $session;
		$this->route = $route;
		$this->pkgHandle = $pkgHandle;
		$this->time = $time;
	}

	public function write($message) {
		$this->data .= $message . "\n";
		if (!$this->session) {
			$this->close();
		}
	}

	public static function addEntry($data = '', $type = false, $route = '', $pkgHandle = false, $time = 0) {
		$l = new ApiLog($type, $route, $pkgHandle, false, $time);
		$l->write($data);
	}

	public function close() {
		$v = array($this->type, htmlentities($this->data, ENT_COMPAT, APP_CHARSET), $this->route, $this->pkgHandle, $this->time);
		$db = Loader::db();
		$db->Execute("insert into ApiLogs (rtype, data, route, pkgHandle, time) values (?, ?, ?, ?, ?)", $v);
		$this->data = '';
	}

	/** 
	 * Removes log entries by type- these are entries that an app owner has written and don't have a builtin C5 type
	 * @param string $type Is a lowercase string that uses underscores instead of spaces, e.g. sent_emails
	 */
	public function clearByType($type) {
		$db = Loader::db();
		$db->Execute("delete from ApiLogs where rtype = ?", array($type));
	}


	/** 
	 * Removes all log entries
	 */
	public function clearAll() {
		$db = Loader::db();
		$db->Execute("delete from ApiLogs");
	}

	/** 
	 * Returns the total number of entries matching this type 
	 */
	public static function getTotal($keywords, $type) {
		$db = Loader::db();
		$kw = '';
		if ($keywords != '') {
			$kw = 'and data like ' . $db->quote('%' . $keywords . '%');
		}
		if ($type != false) {
			$v = array($type);
			$r = $db->GetOne('select count(ID)  from ApiLogs where rtype = ? ' . $kw, $v);
		} else {
			$r = $db->GetOne('select count(ID)  from ApiLogs where 1=1 ' . $kw);
		}
		return $r;
	}

	/** 
	 * Returns a list of log entries
	 */
	public static function getList($keywords, $type, $limit) {
		$db = Loader::db();
		$kw = '';
		if ($keywords != '') {
			$kw = 'and data like ' . $db->quote('%' . $keywords . '%');
		}
		if ($type != false) {
			$v = array($type);
			$r = $db->Execute('select ID from ApiLogs where rtype = ? ' . $kw . ' order by timestamp desc limit ' . $limit, $v);
		} else {
			$r = $db->Execute('select ID from ApiLogs where 1=1 ' . $kw . ' order by timestamp desc limit ' . $limit);
		}

		$entries = array();
		while ($row = $r->FetchRow()) {
			$entries[] = ApiLogEntry::getByID($row['ID']);
		}
		return $entries;
	}

	/** 
	 * Returns an array of distinct log types
	 */
	public static function getTypeList() {
		$db = Loader::db();
		$lt = $db->GetCol("select distinct rtype from ApiLogs");
		if (!is_array($lt)) {
			$lt = array();
		}
		return $lt;
	}

	/** 
	 * Returns all the logs
	 */
	public static function getLogs() {
		$db = Loader::db();
		$r = $db->GetCol('select distinct rtype from ApiLogs order by rtype asc');
		return $r;
	}
	
	public static function getLogsByTime() {
		$db = Loader::db();
		$r = $db->Execute('select ID from ApiLogs where time > 0 order by time desc');

		$entries = array();
		while ($row = $r->FetchRow()) {
			$entries[] = ApiLogEntry::getByID($row['ID']);
		}
		return $entries;
	}

}