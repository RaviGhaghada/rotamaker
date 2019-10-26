<?php


class SingleShift {

    // All strings
    // employee id
    private $eid;
    private $shift_name;
    private $rdate;
    private $startTime;
    private $endTime;

    /**
     * Constructor
     * @param shift_name name of the shift
     * @param eid employee to be allocated, -1 if none
     * @param rdate date of the shift
     * @param startTime startTime of the shift
     * @param endTime endTime of the shift
     */
    function __construct($shift_name, $eid, $rdate, $startTime, $endTime) {
        $this->eid = $eid;
        $this->shift_name = $shift_name;
        $this->rdate = $rdate;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }

    /**
     * Getter method for getting the shift name
     * @return String name of the shift
     */
    public function getShift_name() {
        return shift_name;
    }

    /**
     * Setter method for setting the shift name
     * @param String name of the shift
     */
    public function setShift_name($shift_name) {
        $this->shift_name = $shift_name;
    }

    /**
     * Getter method for the date of the shift
     * @return String Shift's date
     */
    public function getRdate() {
        return $this->rdate;
    }

    /**
     * Setter method for the date of the shift
     * @param String Shift's date
     */
    public function setRdate($rdate) {
        $this->rdate = $rdate;
    }

    /**
     * Getter method for the employee id
     * @return String employee id
     */
    public function getEid() {
        return $this->eid;
    }

    /**
     * Setter method for the employee id
     * @param String employee id
     */
    public function setEid($eid) {
        $this->eid = $eid;
    }

    /**
     * Set the current shift to null
     * WARNING: use this function will directly affect the number of hours an employee has worked
     * in table Rota.Employees(fair_hours)
     */
    public function setSQLNullShift(){
        if ($this->eid!= "-1"){
            $query =  "UPDATE Employees "
                    ."SET fair_hours = fair_hours - (SELECT TIME_TO_SEC(TIMEDIFF(end_time, start_time))/3600 FROM ShiftType WHERE shift_name = '%s') "
                    ."WHERE e_id = %s";

            $q = sprintf($query, $this->shift_name, $this->eid);
            QFunc::getQuery($q);

        }
        $this->eid = "-1";
    }

    /**
     * When does the shift end
     * @return String answer to the above
     */
    public function getEndTime() {
        return $this->endTime;
    }

    /**
     * Setter method for endtime
     */
    public function setEndTime($endTime) {
        $this->endTime = $endTime;
    }

    /**
     * Getter method for start time of the shift
     * @return String start time of the shift
     */
    public function getStartTime() {
        return $startTime;
    }

    /**
     * Push this shift to the real mysql table rota.TempRota
     */
    public function updateSQL() {
        $query = "UPDATE TempRota SET e_id = %s WHERE shift_name = '%s' AND rdate = '%s'";
        $q = "";
        if ($this->eid == "-1")
            $q = sprintf($query, "NULL", $this->shift_name, $this->rdate);
        else {
            $q = sprintf($query, $this->eid, $this->shift_name, $this->rdate);

            $quick_query = "UPDATE Employees "
            ."SET fair_hours = fair_hours + (SELECT TIME_TO_SEC(TIMEDIFF(end_time, start_time))/3600 FROM ShiftType WHERE shift_name = '%s') " 
            ."WHERE e_id = %s";
            QFunc::getQuery(sprintf($quick_query, $this->shift_name, $this->eid));
        }
        QFunc::getQuery($q);
    }            
}

class DecisionNode {

    // SingleShift
    private $_shift;

    // Array of employee ids suitable for the shift
    private $_eids;

    // Previous decision node
    private $_parent;
    
    /**
     * Constructor for a decision node object
     * 
     * @param Singleshift object representing a shift
     * @param Array list of employee ids that can work the above shift
     * @param DecisionNode previous decision node
     */
    function __construct($shift, $eids, $parent) {
        $this->_shift = $shift;
        $this->_eids = $eids;
        $this->_parent = $parent;
    }

    /**
     * Return true if the decision node can backtrack
     * Else returns NULL
     * 
     * @return boolean
     */
    public function canBackTrack(){
        return !is_null($_parent);
    }

    public function backTrack(){
        $this->_shift->setNullShift();
        $this->_shift->updateSQL();

        $this->shift = $this->parent->shift;
        $this->eids = $this->eids;
        $this->parent = $this->parent->parent;
    }

    /**
     * Go for the next available employee
     * 
     * @return boolean true if employee assigned to shift, else false
     */
    public function consumeEmployee(){
        if ($sizeof($eids)<1)
            return false;

        $shift->setEid(eids.remove(eids.size()-1));
        $shift->updateSQL();

        return true;
    }

}
