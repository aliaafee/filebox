<?php

include_once 'db.php';

class authdb extends db {
	public function addUser($user, $pass, $permissions, $date, $fullname, $email='', $phone='') {
		if ($this->getUser($user) == false) {
			try {
				$q_user = $this->quote($user);
				$q_pass = $this->quote($pass);
				$q_permissions = $this->quote($permissions);
				$q_date	= $this->quote($date);
				$q_fullname = $this->quote($fullname);
				$q_email = $this->quote($email);
				$q_phone = $this->quote($phone);

				$result = $this->exec(
					"INSERT INTO users(user, pass, permissions, date, fullname, email, phone)
					VALUES ($q_user, $q_pass, $q_permissions, $q_date, $q_fullname, $q_email, $q_phone)"
				);

				return true;
			} catch (PDOException $e) {
				return false;
			}
		}
		return false;
	}

	public function getUser($user) {
		try {
			$q_user = $this->quote($user);

			$result = $this->query("SELECT user, pass, permissions, date, fullname, email, phone FROM users WHERE user=$q_user");

			$row = $result->fetch(PDO::FETCH_ASSOC);

			$result->closeCursor();

			return $row;
		} catch (PDOException $e) {
			return false;
		}

	}

	public function createTables() {
		parent::query (
			'CREATE TABLE users (
				user	TEXT PRIMARY KEY ,
				pass	TEXT,
				permissions	TEXT,
				date	INTEGER,
				fullname	TEXT,
				email	TEXT,
				phone	TEXT
			);'
		);

		return true;
	}
}

?>
