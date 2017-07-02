<?php

/**
* Class with tools for interacting with MySQL databases to be inherited from sub classes
**/
abstract class database_tools
{
	/**
	* A Database Row Assoc Array representing a single row
	*/
	public $row;

	/**
	* Private Results to Store when running get requests
	*/
	static $getRowResults=array();

	/**
	* Get Row Id
	* @param int $id
	* @returns array - The Corresponding Row
	**/
	public static function getRowFromId($id)
	{
		if(empty(static::$database)||empty(static::$table))
			throw new Exception("Database or Table Not Set!");
		$mySQLiObj=static::getMySQLiObj();
		$sql="SELECT * FROM ".static::$database.".".static::$table." WHERE id='".(int)$id."' LIMIT 1";
		$rows=$mySQLiObj->mysqlidb->getRowsFromQuery($sql);
		if(empty($rows)) return array();
		return $rows[0];
	}

	/**
	* Add a record to the database
	* @param array $values - Assoc Array of the values to be insert with the key as the field name
	* @return int - The Id of the newly created row or true if there is no AUTO_INCREMENT column
	*/
	public static function add_record($values)
	{
		self::setup_fields();
		$mySQLiObj=static::getMySQLiObj();

		$sql="INSERT INTO `".static::$database."`.`".static::$table."` (";
		$i=0;
		foreach($values as $field => $value)
		{
			if(!isset(static::$fields[$field]))
				throw new Exception($field."(field) not Found in `".static::$database."`.`".static::$table."` database table. - ".print_r(static::$fields,true));
			$sql.=($i?",":"")."`".$field."`";
			$i++;
		}
		$sql.=")VALUES(";
		$i=0;
		foreach($values as $field => $value)
		{
			$sql.=""
				.($i?",":"")
				.(substr($value,0,strlen('/mysql_function/'))!='/mysql_function/'?""
					."'".$mySQLiObj->mysqlidb->real_escape_string(stripslashes($value))."'"
					:substr($value,strlen('/mysql_function/'))
				)
			;
			$i++;
		}
		$sql.=")";
		$result=$mySQLiObj->mysqlidb->query($sql);
		if(!$result)
			throw new Exception("INSERT FAILED - SQL:".$sql." - ERROR:".$mySQLiObj->mysqlidb->{'error'});
		$id=$mySQLiObj->mysqlidb->{'insert_id'};
		if(is_numeric($id)&&$id) return (int)$id;
		else return true;
	}

	/**
	* Delete a database table row
	* @param int $id - The primary id
	* @return bool - true if the update succeeded
	*/
	public static function delete_row($id=NULL)
	{
		self::setup_fields();
		if(!isset($id)||!$id)
			throw new Exception("No Id Found for Deleting!");
		$mySQLiObj=static::getMySQLiObj();
		$sql="DELETE FROM `".static::$database."`.`".static::$table."` WHERE `".static::$primaryField."`='".$mySQLiObj->mysqlidb->real_escape_string(stripslashes($id))."' LIMIT 1";
		$result=$mySQLiObj->mysqlidb->query($sql);
		if(!$result)
			throw new Exception("DELETE FAILED - SQL:".$sql." - ERROR:".$mySQLiObj->mysqlidb->error);
		return true;
	}

	/**
	* Get a row from a database Query
	* @param array $options - Array of options for the Query
	* @param int $seek - (optional) - Seek to this row position
	* @return array - assoc array of a row
	*/
	public static function get_row($options=array(),$seek=NULL)
	{
		self::setup_fields();
		$mySQLiObj=static::getMySQLiObj();

		$sql=self::getSQLFromOptions($options);
		//make a result key to store the result
		$resultKey=md5($sql);
		//if we don't already have a result - get one
		if(!isset(static::$getRowResults[$resultKey]))
			static::$getRowResults[$resultKey]=$mySQLiObj->mysqlidb->query($sql);
		//if we still don't hav a result throw error
		if(!isset(static::$getRowResults[$resultKey])||!static::$getRowResults[$resultKey])
			throw new Exception("get_row Failed: SQL: ".$sql." - ERROR: ".$mySQLiObj->mysqlidb->error);
		//if we are seeking - go to that row
		if(isset($seek)&&is_int($seek))
		{
			if(static::$getRowResults[$resultKey]->num_rows>$seek)
				static::$getRowResults[$resultKey]->data_seek($seek);
			else
				return false;
		}
		//return result resource row
		return static::$getRowResults[$resultKey]->fetch_assoc();
	}

	/**
	* Get a rows from a database Query
	* @param array $options - Array of options for the Query
	* @return array - assoc array of a row
	*/
	public static function get_rows($options=array())
	{
		$array=array();
		$i=0;
		while($r=self::get_row($options,$i))
		{
			array_push($array,$r);
			$i++;
		}
		static::freeQueryResultsFromOptions($options);
		return $array;
	}

	/**
	* Free Query Results from Options
	* @param array $options - The array of options for the Query
	* @return bool - true or false
	**/
	public static function freeQueryResultsFromOptions($options=array())
	{
		$sql=self::getSQLFromOptions($options);
		$resultKey=md5($sql);
		//if we don't already have a result - get one
		if(!isset(static::$getRowResults[$resultKey]))
			return false;
		static::$getRowResults[$resultKey]->free();
		unset(static::$getRowResults[$resultKey]);
		return true;
	}

	/**
	* Build SQL Query String From Options
	* @param array $options - Array of options for the Query
	*	array(
	*		'SELECT'=>array('comma','separated','fields','to','select'),
	*		'FROM'=>array('comma','separated','database.tables','to','select','from'),
	*		'WHERE'=>
	*			array('field1'=>'value1','field2'=>'value2') //type 1
	*			array(0=>array('field'=>'field1','CO'=>'!=','value'=>'value1')) //type 2
	*		,
	*		'ORDER BY'=>array('field1 DESC','field2 ASC'),
	*		'LIMIT'=>'4,10',
	*	)
	*/
	private static function getSQLFromOptions($options=array())
	{
		self::setup_fields();
		$mySQLiObj=static::getMySQLiObj();

		$sql=""
			."SELECT "
				.(isset($options['SQL_CALC_FOUND_ROWS'])&&$options['SQL_CALC_FOUND_ROWS']==true?"SQL_CALC_FOUND_ROWS ":"")
				.(isset($options['SELECT'])&&is_array($options['SELECT'])&&count($options['SELECT'])
					?implode(",",$options['SELECT'])
					:"*"
				)
				." "
			."FROM "
				.(isset($options['FROM'])&&is_array($options['FROM'])&&count($options['FROM'])
					?implode(",",$options['FROM'])
					:"`".static::$database."`.`".static::$table."`"
				)
				." "
		;
		if(isset($options['WHERE'])&&is_array($options['WHERE'])&&count($options['WHERE']))
		{
			$sql.="WHERE ";
			$i=0;
			foreach($options['WHERE'] as $field=>$value)
			{
				$sql.=""
					.($i?" AND ":"")
					.(is_string($field)&&is_string($value)
						?"`".$field."`='".$mySQLiObj->mysqlidb->real_escape_string(stripslashes($value))."'"
						:(is_array($value)&&isset($value['field'])&&isset($value['CO'])&&isset($value['value'])
							?""
								."`".$value['field']."`"
								.$value['CO']
								.($value['CO']=='IN'
									?""
									."(".$value['value'].")"
									:""
									."'".$mySQLiObj->mysqlidb->real_escape_string(stripslashes($value['value']))."'"
								)
							:(is_array($value)&&isset($value['field'])&&isset($value['FIND_IN_SET'])&&$value['FIND_IN_SET']&&isset($value['value'])
								?""
								."FIND_IN_SET('".$mySQLiObj->mysqlidb->real_escape_string(stripslashes($value['value']))."',`".$value['field']."`) <> 0"
								:""
							)
						)
					)
				;
				$i++;
			}
			$sql.=" ";
		}
		$sql.=""
			.(isset($options['ORDER BY'])&&is_array($options['ORDER BY'])&&count($options['ORDER BY'])
				?"ORDER BY ".implode(",",$options['ORDER BY'])." "
				:""
			)
			.(isset($options['LIMIT'])
				?"LIMIT ".$options['LIMIT']
				:""
			)
		;
		return trim($sql);
	}

	/**
	* Get Calculated Rows
	*/
	public static function getFoundRows()
	{
		$sql="SELECT FOUND_ROWS() AS found;";
		$mySQLiObj=static::getMySQLiObj();
		$found_rows=$mySQLiObj->mysqlidb->getRowsFromQuery($sql);
		return $found_rows[0]['found'];
	}

	/**
	* Setup the fields for the database
	* @return bool - true if setup
	*/
	private static function setup_fields()
	{
		if(isset(static::$fields)&&is_array(static::$fields)&&isset(static::$primaryField)&&is_string(static::$primaryField))
			return true;
		$mySQLiObj=static::getMySQLiObj();
		if(!is_string(static::$database))
			throw new Exception("No Database Set!");
		if(!is_string(static::$table))
			throw new Exception("No Table Set!");
		$sql="SHOW FIELDS FROM `".static::$database."`.`".static::$table."`";
		$rows=$mySQLiObj->mysqlidb->getRowsFromQuery($sql);
		static::$fields=array();
		foreach($rows as $r)
		{
			static::$fields[$r['Field']]=$r;
			//setup primary field
			if($r['Key']=='PRI')
				static::$primaryField=$r['Field'];
			elseif(!static::$primaryField&&$r['Key']=='UNI')
				static::$primaryField=$r['Field'];
		}
		if(!static::$primaryField)
			throw new Exception("No Primary Field Found!");
		return true;
	}

	/**
	* Class Destructor
	**/
	public static function zdestruct()
	{
		if(!empty(self::$getRowResults)&&is_array(self::$getRowResults))
			foreach(self::$getRowResults as $key=>$result)
				$result->free();
	}
}

?>
