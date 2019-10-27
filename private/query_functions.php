<?php

  class QFunc {
    
    static public function getQuery($sql) {
      global $db;

      $result = mysqli_query($db, $sql);
      
     
      if (!$result) {
        //echo "<br>Failure?? ".$sql;
        // ^only works if the query is of SELECT type
      }
      else{
        //echo "<br>Success: ".$sql;
      }

      return $result;
    }
  }

?>
