<?php

include_once 'db.php';

class fileboxdb extends db {
	public function insertFile($ip, $user, $size, $comment, $filename) {
		$q_ip = $this->quote($ip);
		$q_size = $this->quote($size);
		$q_comment = $this->quote($comment);
		$q_filename = $this->quote($filename);
		$q_user = $this->quote($user);

		$result = $this->exec("INSERT INTO files VALUES (NULL, datetime('now'), $q_ip, $q_size, $q_comment, $q_filename, $q_filename, $q_user )");
		
		$id = $this->lastInsertId();
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		if ($ext != "") { $ext = ".$ext"; }
		$filename = str_pad($id, 10, "0", STR_PAD_LEFT)."$ext";
		$q_filename = $this->quote($filename);
		$q_id = $this->quote($id);

		$result = $this->exec("UPDATE files SET filename=$q_filename WHERE id=$q_id");

		return $filename;
	}

	public function getFileList() {
		return $this->queryAsTable(
			"SELECT id, date, user, comment, ofilename, filename, size FROM files ORDER BY date DESC",
			$headers= Array('', 'Date', 'From', 'Comment', 'File', '', 'Size'),
			$links= Array(
				'ofilename' => '?file={id}&name={ofilename}'
			),
			$format= Array(
				'size'	=> 'humanSize'
			)
		);

		return $result;
	}

	public function getFilename($id) {
		try {
			$q_id = $this->quote($id);

			$result = $this->query("SELECT ofilename, filename FROM files WHERE id=$q_id");

			$row = $result->fetch(PDO::FETCH_ASSOC);

			$result->closeCursor();

			return $row;
		} catch (PDOException $e) {
			$result->closeCursor();

			return false;
		}
	}

	public function createTables() {
		parent::query (
			'CREATE TABLE files (
				id INTEGER PRIMARY KEY   AUTOINCREMENT ,
				date INTEGER,
				ip TEXT,
				size INTEGER,
				comment BLOB,
				ofilename	TEXT,
				filename	TEXT,
				user	TEXT
			);'
		);
	}
}


?>
