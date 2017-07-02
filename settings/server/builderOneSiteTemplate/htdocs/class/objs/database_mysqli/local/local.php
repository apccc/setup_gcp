<?php
require_once dirname(dirname(__FILE__)).'/database_mysqli.php';
require_once dirname(dirname(__FILE__)).'/database_tools.php';
require_once dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/settings.php';

/**
* Class for connecting and using the main database
**/
class database_mysqli_local extends database_tools
{
	//only allow one construction
	private static $constructed=false;

	/**
	* The timer for reconnection
	**/
	private static $lastConnectionTime = false;
	private static $maxSecondsBeforeReconnectCheck = 30;

	//constructor
	public function __construct()
	{
		if(static::$constructed) throw new Exception('Already Constructed!');
		static::$constructed=true;

		$this->dbConnect();
	}

	//connect
	function dbConnect()
	{
		//set the last connection time, if this has not been set
		self::$lastConnectionTime=time();

		$this->masterLink=true;
		global $MYSQL_USER;
		global $MYSQL_PASS;
		$DBu=$MYSQL_USER;
		$DBp=$MYSQL_PASS;
		$DBh='localhost';
		if(!$this->mysqlidb=new database_mysqli($DBh,$DBu,$DBp))
		{
			$this->masterLink=false;
		}
		$this->mysqlidb->query("SET NAMES 'utf8mb4'");
	}

	//disconnection
	function dbDisconnect()
	{
		mysqli_close($this->mysqlidb);
	}

	//reconnect
	function dbReconnect()
	{
		$this->dbDisconnect();
		$this->dbConnect();
	}

	//reconnect, if connection is old
	function reconnectOnStaleConnection()
	{
		if(time()-self::$lastConnectionTime < self::$maxSecondsBeforeReconnectCheck) return false;
		$this->dbReconnect();
	}

	//getobject
	/**
	* @returns obj - The database_mysqli connected object
	**/
	public static function getMySQLiObj()
	{
		global $database_mysqli_local;
		return $database_mysqli_local;
	}

	//destructor
	function __destruct()
	{
		self::zdestruct();
		$this->dbDisconnect();
	}

}

global $database_mysqli_local;
$database_mysqli_local=new database_mysqli_local;

?>