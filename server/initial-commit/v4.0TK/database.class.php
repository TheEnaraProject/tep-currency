<?php

// Include database class
include_once 'configuration.php';

class Database{
	private $host = DB_HOST;
	private $user = DB_USER;
	private $pass = DB_PASS;
	private $dbname = DB_NAME;
	
	private $dbh;
        private $error;
	
	private $stmt;
 
    public function __construct(){
        // Set DSN
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname;
        // Set options
        $options = array(
            PDO::ATTR_PERSISTENT    => true,
            PDO::ATTR_ERRMODE       => PDO::ERRMODE_EXCEPTION
        );
        // Create a new PDO instanace
        try{
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        }
        // Catch any errors
        catch(PDOException $e){
            $this->error = $e->getMessage();
        }
    }
	
	// Set the query to be run
	public function query($query){
		$this->stmt = $this->dbh->prepare($query);
	}
	
	// Bind a value to a SQL parameter
	public function bind($param, $value, $type = null){
		if (is_null($type)) {
			switch (true) {
				case is_int($value):
					$type = PDO::PARAM_INT;
					break;
				case is_bool($value):
					$type = PDO::PARAM_BOOL;
					break;
				case is_null($value):
					$type = PDO::PARAM_NULL;
					break;
				default:
					$type = PDO::PARAM_STR;
			}
		}
		$this->stmt->bindValue($param, $value, $type);
	}
	
	// Execute the query
	public function execute(){
		return $this->stmt->execute();
	}

	// Return the result set of the entered query
	public function resultset(){
		$this->execute();
		return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	// Return a single row of the query
	public function singleRow(){
		$this->execute();
		return $this->stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	// Return a single row of the query
	public function singleValue(){
		$this->execute();
		return $this->stmt->fetchColumn();
	}
	
	// Return row count of query
	public function rowCount(){
		return $this->stmt->rowCount();
	}
	
	// Return last inserted ID of query
	public function lastInsertId(){
		return $this->dbh->lastInsertId();
	}
	
	// Start transaction
	public function beginTransaction(){
		return $this->dbh->beginTransaction();
	}
	
	// End transaction
	public function endTransaction(){
		return $this->dbh->commit();
	}
	
	// Roll back transaction
	public function cancelTransaction(){
		return $this->dbh->rollBack();
	}
        
        public function truncateTable($table_name)
        {
            $this->query("TRUNCATE TABLE " . $table_name);
            return $this->execute();
        }
        
	
	// Debug function to return query parameters entered
	public function debugDumpParams(){
		return $this->stmt->debugDumpParams();
	}
}

?>