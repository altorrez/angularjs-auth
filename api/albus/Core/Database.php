<?php 
namespace albus\Core;

require ROOT.DS.'albus'.DS.'Config'.DS.'database.php';

define('HOST', $host);
define('NAME', $name);
define('USER', $user);
define('PASS', $pass);
define('TYPE', $type);
define('TZ', $timezone);

class Database {

	public $db;

	public function __construct() {

		date_default_timezone_set(TZ);
		try {
			$this->db = new \PDO(TYPE.':host='.HOST.';dbname='.NAME, USER, PASS);
			$this->db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false); // php is not allowed to touch prepared statements
			$this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		}catch(\PDOException $e) {
			echo $e->getMessage();
		}

	}

	public function __destruct() {
		$this->db = null;
	}

	public function getDb() {
		return $this->db;
	}

	// Prepare data for pdo by prepending : to keys
	private function pdoPrepare($data) {

		foreach($data as $key => $value) {
			unset($data[$key]);
			$key = ':'.$key;
			$data[$key] = $value;
		}
		return $data;
	}

	// prepare data for insertion into database
	public function prepareData($data, $rules) {
		$fields = array_keys($rules);
		// fill in any missing fields with data value of null
		foreach($fields as $field) {
			if(!isset($data[$field])) {
				$data[$field] = null;
			}
		}
		return $this->pdoPrepare($data);
	}
}