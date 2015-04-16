<?php

class db extends pdo {
	public function __construct($dbfile) {
		parent::__construct('sqlite:'.$dbfile);

		$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	public function createTables() {
		//Create the database tables
		return true;
	}
	
	public function queryAsTable($statement, $headers=Array(), $links=Array(), $format=Array()) {
		/*
		Returns a query result as an HTML table

		Options:
			headers	Title of column headers, if emptystring provided that column is hidden
					defaults to column name in table
			links	Associative array in the form ('colname' => 'url'), replaces {colname} 
					With value of that column for the current row. Note that the column should
					also be selected in the query
			format	Associative array as in links. calls the given string formating function,
					the formating function should return a string and must accept one parameter
					as a string
		*/

		try {
			$result = $this->query($statement);

			$columnNames = Array();
			for ($i = 0; $i < $result->columnCount(); $i++) {
				$meta = $result->getColumnMeta($i);
				$columnNames[$i] = $meta['name'];
				if (!isset($headers[$i])) {
					$headers[$i] = $meta['name'];
				}
			}

			$table = '<table>';

			$table .= '<tr>';
			foreach ($headers as $header) {
				if ($header != '') {
					$table .= '<th>'.$header.'</th>';
				}
			}
			$table .= '</tr>';

			foreach ($result as $row) {
				$table .= '<tr>';
				for ($i = 0; $i < $result->columnCount(); $i++) {
					$columnName = $columnNames[$i];
					$cellvalue = $row[$i];

					if (isset($format[$columnName])) {
						$formatFunction = $format[$columnName];
						$cellvalue = $formatFunction($cellvalue);
					}

					if ($headers[$i] != '') {
						if ( isset( $links[$columnName] ) ) {
							$link = $links[$columnName];
							foreach ($columnNames as $column => $columnName) {
								$link = str_replace('{'.$columnName.'}', urlencode($row[$column]), $link);
							}
							$table .= '<td><a href="'.$link.'" >'.$cellvalue.'</a></td>';
						} else {
							$table .= '<td>'.$cellvalue.'</td>';
						}
					}
				}
				$table .= '</tr>';
			}

			$table .= '</table>';

			$result->closeCursor();

			return $table;
		} catch (Exception $e) {
			$result->closeCursor();

			return '<div class="error">No results</div>';
		}
	}
}

?>
