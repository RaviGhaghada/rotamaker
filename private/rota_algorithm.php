<?php



class RotaAlgorithm {

    private $_priorities;
    private $_decisions;
    private $_date = "";

    /**
     * Constructor for the Rota Algorithm!
     * 
     * @param date first date of the week
     */
    function __construct($_date){
        $this->_date = $_date;
    }

    /**
     * Generate the goddamn rota!
     * 
     * @return boolean true if rota successfully generated
     */
    public function generateRota() {

        $result = false;
        //eraseActualRota();
        $this->insertNullShifts($_date);
        $this->updateZeroHours();
        $result = $this->run(NULL);
        //transferToRealRota();
        return $result;
    }

    /**
     * Reset the number of hours worked in a week to zero.
     */
    private function updateZeroHours() {
        QFunc::getQuery("UPDATE Employees SET fair_hours = 0");
    }


    // TODO: CONVERT function run(...) from recursive to iterative type
    /**  
     * A recursive function that keeps running till 
     * 1) all shifts have been allocated -> returns true
     * 2) it is unable to find a solution -> returns false
     * 
     * @param DecisionNode previous Decision Node 
     * 
     * @return boolean whether rota was successfully generated or not
     */
    private function run($pn) {
        // singleshift
        $s = $this->getFirstEmptyShift();

        if ($s==null)
            return true;

        // ArrayList<Integer>
        $employees = $this->getSuitableEmployees($s);
        // DecisionNode 
        $dn = new DecisionNode ($s, $employees, $pn);

        // boolean 
        $consumed = $dn->consumeEmployee();
        
        while (!$consumed){
            if (!$dn->canBackTrack())
                return false;
            $dn->backTrack();
            $consumed = $dn->consumeEmployee();
        }
        return $this->run($dn);
    }

    
    /**
     * Return the next earliest shift that needs to be
     * allocated to an employee
     * @return SingleShift
     */
    private function getFirstEmptyShift(){
        // String
        $query = "SELECT * FROM TempRota LEFT JOIN ShiftType ON TempRota.shift_name=ShiftType.shift_name"
                ." WHERE e_id is NULL order by TempRota.rdate, ShiftType.start_time LIMIT 1";
        
        // ResultSet
        $result = QFunc::getQuery($query);
    
        while ($c = mysqli_fetch_assoc($result)){
            $r = $c;
            mysqli_free_result($result);
            return new SingleShift($r["shift_name"], $r["rdate"], $r["start_time"], $r["end_time"]);
        }
    }


    /**
     * Copied from stackoverflow. Used to check 
     * if a haystrack-string starts with needle-string
     */
    private function startsWith($haystack, $needle) {
        return substr_compare($haystack, $needle, 0, strlen($needle)) === 0;
    }

    /**
     * Inserts unallocated skeleton shifts to the table TempRota
     * @param String date of the first day of the week
     */
    private function insertNullShifts($date){
        
        QFunc::getQuery("DELETE FROM TempRota");
        
        // ResultSet 
        $result = QFunc::getQuery("SELECT * FROM ShiftType WHERE core=1");

        // String []
        $datesOfWeek = $this->getDatesOfWeek($date);


        $query = "INSERT INTO TempRota VALUES ('%s', NULL, '%s')";

        while ($rs = mysqli_fetch_assoc($result)){
            $shiftName = $rs["shift_name"];
            for ($i = 0; $i < sizeof($datesOfWeek); $i++) {
                if ($i == 6 && $this->startsWith($shiftName, "S_")) {
                    QFunc::getQuery(sprintf($query, $shiftName, $datesOfWeek[$i]));
                }
                else if ($i!=6 && !$this->startsWith($shiftName, "S_")){
                    QFunc::getQuery(sprintf($query, $shiftName, $datesOfWeek[$i]));
                }
            }
        }
    }

    /**
     * Return an array of dates of the next 6 dates and the inputted date
     * Ideally, the input date is a Monday-date
     * @param String first date YY-MM-DD
     * @return String[] first date and next 6 dates
     */
    public function getDatesOfWeek($dt){
        $dates = array_fill(0, 7, NULL);
        $dates[0] = $dt;
        for ($i=0; $i < sizeof($dates); $i++) {
           $dates[$i] = date("Y-m-d",strtotime($dt."+".$i." day"));
        }
        return $dates;
    }

    /**
     * Get a list of suitables employees
     * for a given shift
     * 
     * @param SingleShift input shift
     * @return Array Of Integers representing the employee_ids of the suitable candidates
     */
    private function getSuitableEmployees($s){

        fillPriorities($s);

        $query = "SELECT DISTINCT e_id, min_hours, fair_hours FROM Employees";

        for ($i=0; $i < sizeof($_priorities); $i++)
            $query = $query." INNER JOIN " + $_priorities[$i] + " USING (e_id)";

        $query = $query." ORDER BY (min_hours - fair_hours), RAND()";

        // Result Set
        $result = $QFunc::getQuery();

        // array of integers
        $employees = array();

        $i=0;
        while($r = mysqli_fetch_assoc($result)) {
            $employees[$i] = $r["e_id"];
            $i++;
        }

        mysqli_free_result($result);
        return $employees;
    }

    /**
     * Construct an array of queries of a given shift 
     * To filter out employees you want for a shift
     * 
     * @param SingleShift given input shift based on which queries must be constructed
     */
    private function fillPriorities($s) {

        $_priorities = array();

        // employees available on that day
        $_priorities[0] = "(SELECT e_id FROM Employees"
                ." WHERE e_id NOT IN " 
                ."(SELECT e_id FROM Unavailability "
                ."WHERE ("
                ."edate=" . $s->getRdate() + " AND ("
                ."(start_time>=".$s->getStartTime()." AND ".$s->getStartTime()
                ."<=end_time) OR (start_time>=".$s->getEndTime()." AND ".$s->getEndTime()."<=end_time)))))a";

        // employees capable of doing that shift
        $_priorities[1] = "(SELECT e_id FROM Capability ".
                "WHERE shift_name = ".$s->getShift_name().")b";

        // employees who don't have another shift on that day
        $_priorities[2] = "(SELECT e_id FROM Employees WHERE e_id NOT IN "
                          ."(SELECT e_id FROM TempRota WHERE e_id IS NOT NULL AND rdate=".$s->getRdate()."))c";

        // employees who haven't exceeded their maximum working hours
        $_priorities[3] = "(SELECT e_id FROM Employees WHERE max_hours >= (fair_hours + SUBTIME("
            .$s->getEndTime().", ".$s->getStartTime().")))d";
    }
}


?>