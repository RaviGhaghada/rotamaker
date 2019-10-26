<?php

  require_once('db_credentials.php');

  class Database {

    /**
     * Connect to a database
     * 
     * @return connection database connection
     */
    public static function db_connect() {
      $connection = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
      Database::confirm_db_connect();    
      return $connection;
    }

    public static function db_disconnect($connection) {
      if(isset($connection)) {
        mysqli_close($connection);
      }
    }
      
    public static function confirm_db_connect(){
      if(mysqli_connect_errno()) {
        $msg = "Database connection failed: ";
        $msg .= mysqli_connect_error();
        $msg .= " (" . mysqli_connect_errno() . ")";
        exit($msg);
      }
    }
}


?>
