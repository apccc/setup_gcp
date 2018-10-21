<?php
/**
* Generic Database Interaction using MySQLi
**/
class database_mysqli extends mysqli
{
	/**
	* Create empty template database
	* @param string $database
	* @param string $table
	* @param string $template - the template to use
	* @returns bool - success or failure of last query
	**/
	function createDatabaseTable($database,$table,$template='default'){
		$d=preg_replace('/[^a-z0-9_]/','',$database);
		$t=preg_replace('/[^a-z0-9_]/','',$table);
		if($template==='default'){
			$sql="CREATE DATABASE IF NOT EXISTS `$d` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
			if(!$this->query($sql)) throw new Exception("Database could not be created: $d");
			$sql="CREATE TABLE IF NOT EXISTS `$d`.`$t` (`id` bigint(20) UNSIGNED NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
			if(!$this->query($sql)) throw new Exception("Database table could not be created: $d.$t");
			$sql="ALTER TABLE ${d}.${t} ADD PRIMARY KEY (id)";
			if(!$this->query($sql)) throw new Exception("Primary key id could not be created for: $d.$t");
			return true;
		}
	}

	/**
	* Create empty template database
	* @param string $database
	* @param string $table
	* @param string $field
	* @param array $options
	* @returns bool - success or failure of last query
	**/
	function createDatabaseTableField($database,$table,$field,$options=array()){
		$d=preg_replace('/[^a-z0-9_]/','',$database);
		$t=preg_replace('/[^a-z0-9_]/','',$table);
		$f=preg_replace('/[^a-z0-9_]/','',$field);
		$l=(int)$options['length'];
		$type=strtolower($options['type']);
		$sql=$sql="ALTER TABLE `".$d."`.`".$t."` ADD `".$f."`";
		if($type==='int'){
			$sql.="INT";
		} elseif($type==='text'){
			$sql="VARCHAR";
		} else {
			throw new Exception("Field type ".$options['type']." not supported!");
		}
		$sql.="(".$l.") NOT NULL";
		if(!$this->query($sql)) throw new Exception("Field could not be created for for: $d.$t");
		return true;
	}

	/**
	* Insert Into Database Table
	* @param string $database
	* @param string $table
	* @param array $values - key - => $value
	* @param returns int - The Id of the row inserted
	**/
	function insertInto($database,$table,$values)
	{
		if(empty($database)||empty($table)||!isset($values)||!is_array($values)) throw new Exception('insertInto Error!');
		$database=preg_replace('/[^A-Z0-9a-z_]/','',$database);
		$table=preg_replace('/[^A-Z0-9a-z_-]/','',$table);
		$sql="INSERT INTO `".$database."`.`".$table."` (";
		$i=0;
		foreach($values as $k => $v)
		{
			$sql.=($i?",":"")."`".preg_replace('/[^A-Z0-9a-z_-]/','',$k)."`";
			$i++;
		}
		$sql.=")VALUES(";
		$i=0;
		foreach($values as $k => $v)
		{
			$sql.=($i?",":"")."'".$this->real_escape_string($v)."'";
			$i++;
		}
		$sql.=")";
		if(!$this->query($sql)) throw new Exception('insertInto Error: '.$this->error. ':SQL:'.$sql);
		if($this->insert_id) return $this->insert_id;
		return true;
	}

	/**
	* Update Row(s)
	* @param string $database
	* @param string $table
	* @param array $fields - the fields to set
	* @param mixed $search - mixed search criteria - could be an int for the id of the primary field
	* @returns int - Number of rows affected.
	**/
	function updateRows($database,$table,$fields,$search)
	{
		if(empty($database)||empty($table)||empty($search)||empty($fields)) throw new Exception('updateRow Error!');
		$database=preg_replace('/[^A-Z0-9a-z_]/','',$database);
		$table=preg_replace('/[^A-Z0-9a-z_-]/','',$table);

		$updateSQL="";
		$i=0;
		foreach($fields as $k => $v)
		{
			$k=preg_replace('/[^A-Z0-9a-z_-]/','',$k);
			if(empty($k)) continue;
			$updateSQL.=($i?",":"")."`".$k."`='".$this->real_escape_string($v)."'";
		$i++;}

		if(is_numeric($search))
		{
			$where="WHERE `id`='".(int)$search."' ";
		}
		elseif(is_array($search)&&count($search))
		{
			$where="WHERE ";
			$i=0;
			foreach($search as $k => $v)
			{
				$where.=($i?"AND ":"")."`".preg_replace('/[^A-Z0-9a-z_-]/','',$k)."`='".$this->real_escape_string($v)."' ";
				$i++;
			}
		}
		else
		{
			throw new Exception('updateRows Error: No search parameter');
		}

		$sql="UPDATE `".$database."`.`".$table."` SET ".$updateSQL." ".$where;
		if(!$this->query($sql))
			throw new Exception('updateRows Error: '.$this->error.' :SQL:'.$sql);
		return $this->affected_rows;
	}

	/**
	* Delete Row(s)
	* @param string $database
	* @param string $table
	* @param mixed $search - mixed search criteria - could be an int for the id of the primary field
	* @returns int - Number of rows affected.
	**/
	function deleteRows($database,$table,$search)
	{
		if(empty($database)||empty($table)||empty($search)) throw new Exception('updateRow Error!');
		$database=preg_replace('/[^A-Z0-9a-z_]/','',$database);
		$table=preg_replace('/[^A-Z0-9a-z_-]/','',$table);

		if(is_numeric($search))
		{
			$where="WHERE `id`='".(int)$search."' ";
		}
		elseif(is_array($search)&&count($search))
		{
			$where="WHERE ";
			$i=0;
			foreach($search as $k => $v)
			{
				$where.=($i?"AND ":"")."`".preg_replace('/[^A-Z0-9a-z_-]/','',$k)."`='".$this->real_escape_string($v)."' ";
				$i++;
			}
		}
		else
		{
			throw new Exception('deleteRows Error: No search parameter');
		}

		$sql="DELETE FROM `".$database."`.`".$table."` ".$where;
		if(!$this->query($sql))
			throw new Exception('deleteRows Error: '.$this->error.' :SQL:'.$sql);
		return $this->affected_rows;
	}

	/**
	* Get Row
	* @param string $database
	* @param string $table
	* @param mixed $search - mixed search criteria - could be an int for the id of the primary field
	* @returns array - The row
	**/
	function getRow($database,$table,$search)
	{
		if(empty($database)||empty($table)||empty($search)) throw new Exception('getRow Error!');
		$database=preg_replace('/[^A-Z0-9a-z_]/','',$database);
		$table=preg_replace('/[^A-Z0-9a-z_-]/','',$table);

		if(is_numeric($search))
		{
			$where="WHERE `id`='".(int)$search."' ";
		}
		elseif(is_array($search)&&count($search))
		{
			$where="WHERE ";
			$i=0;
			foreach($search as $k => $v)
			{
				$where.=($i?"AND ":"")."`".preg_replace('/[^A-Z0-9a-z_-]/','',$k)."`='".$this->real_escape_string($v)."' ";
				$i++;
			}
		}

		$sql="SELECT * FROM `".$database."`.`".$table."` ".$where."LIMIT 1";
		if(!$result=$this->query($sql))
			throw new Exception('getRow Error: '.$this->error.' :SQL:'.$sql);
		$row=$result->fetch_assoc();
		$result->free();
		return $row;
	}

	/**
	* Get Rows
	* @param string $database
	* @param string $table
	* @param mixed $search - mixed search criteria - could be an int for the id of the primary field
	* @param string $limit - optional limit
	* @returns array - The rows
	**/
	function getRows($database,$table,$search,$limit="")
	{
		if(empty($database)||empty($table)||empty($search)) throw new Exception('getRows Error!');
		$database=preg_replace('/[^A-Z0-9a-z_]/','',$database);
		$table=preg_replace('/[^A-Z0-9a-z_-]/','',$table);

		if(is_numeric($search))
		{
			$where="WHERE `id`='".(int)$search."' ";
		}
		elseif(is_array($search)&&count($search))
		{
			$where="WHERE ";
			$i=0;
			foreach($search as $k => $v)
			{
				$where.=($i?"AND ":"")."`".preg_replace('/[^A-Z0-9a-z_-]/','',$k)."`='".$this->real_escape_string($v)."' ";
				$i++;
			}
		}

		$sql="SELECT * FROM `".$database."`.`".$table."` ".$where.($limit?"LIMIT ".$limit:"");
		if(!$result=$this->query($sql))
			throw new Exception('getRow Error: '.$this->error.' :SQL:'.$sql);
		$array=array();
		while($row=$result->fetch_assoc())
			array_push($array,$row);
		$result->free();
		return $array;
	}
	/**
	* Get Rows From Query String
	* @param string $sql - The SQL Query String
	* @returns array - Assoc Array of Results
	**/
	function getRowsFromQuery($sql)
	{
		if(!$result=$this->query($sql))
			throw new Exception('getRowsFromQuery Error: '.$this->error.' :SQL:'.$sql);
		$array=array();
		while($row=$result->fetch_assoc())
			array_push($array,$row);
		$result->free();
		return $array;
	}

	/**
	* Get Row From Query String
	* @param string $sql - The SQL Query String
	* @returns array - Assoc Array of Row
	**/
	function getRowFromQuery($sql)
	{
		if(!$result=$this->query($sql))
			throw new Exception('getRowFromQuery Error: '.$this->error.' :SQL:'.$sql);
		$row=$result->fetch_assoc();
		$result->free();
		return $row;
	}
}

?>
