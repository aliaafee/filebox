<?php

class dbConnection {
	protected $setting;
	public $db;
	
	public function __construct($settings) {
		$this->setting = $settings;
	}
	
	public function __destruct() {
	
	}
	
	public function connect() {
		if ($this->db = new SQLite3($this->setting['database']['file'])) {
			return true;
		} else {
			return false;
		}
	}
	
	public function disconnect() {
		return true;
	}

	public function insertFile($ip, $size, $comment, $filename) {
		$comment = SQLite3::escapeString($comment);

		$q = $this->querySingle("INSERT INTO files VALUES (NULL, datetime('now'), '$ip', '$size', '$comment', '$filename', '')");
		
		$id = $this->db->lastInsertRowid();
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		if ($ext != "") { $ext = ".$ext"; }
		$filename = SQLite3::escapeString(str_pad($id, 10, "0", STR_PAD_LEFT)."$ext");

		$q = $this->querySingle("UPDATE files SET filename='$filename' WHERE id=$id");

		return $filename;
	}

	public function getFileList() {
		$q = $this->query("SELECT date, ip, size, comment, ofilename, filename FROM files ORDER BY date DESC");

		return $q;
	}

	private function querySingle($querystring) {
		$q = $this->db->querySingle($querystring);
		if ($q === false) {
			$this->createTables();
			$q = $this->db->querySingle($querystring);
		}
		return $q;
	}

	private function query($querystring) {
		$q = $this->db->query($querystring);
		if ($q === false) {
			$this->createTables();
			$q = $this->db->query($querystring);
		}
		return $q;
	}

	private function createTables() {
		return $this->db->querySingle(
			'CREATE TABLE files (
				id INTEGER PRIMARY KEY   AUTOINCREMENT ,
				date INTEGER,
				ip TEXT,
				size INTEGER,
				comment BLOB,
				ofilename	TEXT,
				filename	TEXT
			);'
		);
	}
}


?>
