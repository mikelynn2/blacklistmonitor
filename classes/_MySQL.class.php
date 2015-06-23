<?php
/*
*<blockquote>
*	How to use this class for updating queries:
*            $mysql = new _MySQL();
*            $mysql->connect($connectionArray);
*            $rs = $mysql->runQuery($sqlquery);
*            $mysql->close();
*</blockquote>
*/

class _MySQL {

	public $affectedRows = 0;
	public $identity = 0;
	public $mysqlCon = null;
	public $connectionArray = null;

	/**
	* connect() - takes a connection array - host,user,pass,database - has a default but wont work without database
	**/
	public function connect($connectionArray) {
		// [tammytattoo] Added support for port specifiers
		$hostParts = explode(':', $connectionArray[0]);
		if(count($hostParts) == 2) {
    			$connectionArray[0] = $hostParts[0];
    			$connectionArray[4] = $hostParts[1];
		}
		else {
    			$connectionArray[4] = 3306;
		}
		$this->connectionArray = $connectionArray;
		$this->close();
		$this->mysqlCon = mysqli_connect(
			$connectionArray[0],
			$connectionArray[1],
			$connectionArray[2],
			$connectionArray[3],
			$connectionArray[4]
		);
		
		// If no servers are responding, throw an exception.
		if ($this->mysqlCon === false) {
			throw new Exception(
				'Unable to connect to any db servers - last error: ' .
				mysqli_error());
		}
		return $this->mysqlCon;
	}

	public function runQuery($query) {
		if (!mysqli_ping($this->mysqlCon)) {
			$this->connect($this->connectionArray);
		}
		$result = @mysqli_query($this->mysqlCon, $query);
		if ($result === false) {
		//	error_log($query.' Error:'. mysql_error($this->mysqlCon));
		//	echo($query.' Error:'. mysql_error($this->mysqlCon));
			throw new Exception("Database query failed: $query\n\n" . mysqli_error($this->mysqlCon));
		}
		if(	
			(stripos($query,'INSERT')!==false) ||
			(stripos($query,'UPDATE')!==false) ||
			(stripos($query,'DELETE')!==false)
			) {
			$this->affectedRows = mysqli_affected_rows($this->mysqlCon);
		}else{
			//$this->affectedRows = mysql_num_rows($this->mysqlCon);
		}
		if(stripos($query,'INSERT')!==false) $this->identity = mysqli_insert_id($this->mysqlCon);
		return $result;
	}

	public function runQueryReturnVar($query) {
		$result = false;
		$rs = $this->runQuery($query . " limit 1;");
		while($row = mysqli_fetch_array($rs, MYSQL_NUM)) $result = $row[0];
		mysqli_free_result($rs);
		return $result;
	}

	public function escape($var) {
		return mysqli_real_escape_string($this->mysqlCon,$var);
	}

	public function close() {
		if ($this->mysqlCon != null) {
			mysqli_close($this->mysqlCon);
			$this->mysqlCon = null;
		}
	}
}
