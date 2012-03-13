<?php
class TwitterDBWrapper {
  private static $instance;

	/**
	 * Singleton type class with private constructor
	 */
  private function __construct(){
    $config = TwitterConfig::$config['mysql'];

    // Connect to Database
    Utils::log("Connecting to database...");
    $this->dbh = new mysqli($config['host'],$config['user'],
      $config['pass'],$config['db']);
    if (mysqli_connect_errno()) { 
       Utils::log(sprintf("Could not connect to the DB: %s\n", mysqli_connect_error())); 
       exit(); 
    } 
  }

	/**
	 * Disconnect database when script ends
	 */
  function __destruct(){
    if($this->dbh){
      $this->dbh->close();
    } 
  }

	/**
	 * Perform simple SQL query
	 * @param string $sql SQL query to execute
	 * @return Object Mysqli result object
	 */
  public static function fetch($sql){
    $h = self::getHandle();
    return $h->query(sprintf($sql, TwitterConfig::$config['mysql']['table']));
  }

	/**
	 * Perform prepared SQL statement
	 * @param string $sql SQL query to execute
	 * @param array $params Params to bind with SQL query 
	 * @return Object Mysqli result object
	 */
  public static function query($sql, $params = array()){
    $h = self::getHandle();

    if ($stmt = $h->prepare(sprintf($sql, TwitterConfig::$config['mysql']['table']))){
      if(!empty($params)){
        call_user_func_array(array(&$stmt, "bind_param"), $params);
      }
      $stmt->execute();

      return $stmt;
    } else {
      // Log and exit if cannot connect to database
      Utils::log("Could not prepare statement\n");
      echo exit();
    }
  }

	/**
	 * Get Database Handle
	 * @return Object Database handle
	 */
  public static function getHandle(){
    if(empty(self::$instance)){
      self::$instance = new TwitterDBWrapper;
    }
    return self::$instance->dbh;
  }
}
