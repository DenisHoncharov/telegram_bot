<?php
/**
 * Created by PhpStorm.
 * User: d.goncharov
 * Date: 05.02.18
 * Time: 18:13
 */

class DbConnection {
	const DB_DNS = 'pgsql:host=ec2-23-21-195-249.compute-1.amazonaws.com;port=5432;dbname=dcb4l04t1jpoo6;
	sslmode=require';
	const DB_USERNAME = 'fmhgtgnrxtpocw';
	const DB_PASSWORD = 'f34963c9a8f79d7ab4a3d16186c894956566c88e5bf680039f61b28e43dfda7c';

	private $db;
	public function __construct($db) {
		if(!$db){
			$db = new PDO( self::DB_DNS, self::DB_USERNAME, self::DB_PASSWORD);
		}
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->db = $db;
	}

	//return true or PDOException
	public function insert($table, $fields) {
		try {
			$result = null;
			$names = '';
			$vals = '';
			foreach ($fields as $name => $val) {
				if (isset($names[0])) {
					$names .= ', ';
					$vals .= ', ';
				}
				$names .= $name;
				$vals .= ':' . $name;
			}
			$sql = "INSERT INTO " . $table . ' (' . $names . ') VALUES (' . $vals . ')';
			$rs = $this->db->prepare($sql);
			foreach ($fields as $name => $val) {
				$rs->bindValue(':' . $name, $val);
			}
			if ($rs->execute()) {
				$result = $this->db->lastInsertId(null);
			}
			return $result;
		} catch(PDOException $e) {
			error_log($e);
			throw new PDOException($e);
		}
	}
}