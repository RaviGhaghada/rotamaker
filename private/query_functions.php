<?php

  class QFunc {
    
    static public function getQuery($sql) {
      global $db;

      $result = mysqli_query($db, $sql);
      
      // Test if query succeeded
      if (!$result) {
        echo "<br>Failure?? ".$sql;
        //exit("Database query failed.");
      }
      else
        echo "<br>Success: ".$sql;

      return $result;
    }
  }

?>
