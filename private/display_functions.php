<?php       

	function push_back(array &$arr, $e){
		$arr[count($arr)] = $e;
	}

	function getArrayedRota(){
		QFunc::getQuery("DROP VIEW IF EXISTS rota.Tr");
		QFunc::getQuery("CREATE VIEW Tr AS SELECT Employees.e_name, Employees.type, TempRota.e_id, TempRota.rdate, ShiftType.* FROM TempRota "
	 					."INNER JOIN ShiftType ON TempRota.shift_name = ShiftType.shift_name INNER JOIN Employees ON TempRota.e_id=Employees.e_id");

		$sqlresult = QFunc::getQuery("SELECT Tr.* FROM Tr ORDER BY type DESC, e_name, rdate");
		
		$dates = RotaAlgorithm::getDatesOfWeek("2019-10-28"); 
		$tabledata = array();
		$etype = "";
		
		$result = array();
		while ($rrrow = mysqli_fetch_assoc($sqlresult)) {
			// Append all rows to an array
			$result[] = $rrrow;
		}

		$NOOFRESULTS = mysqli_num_rows($sqlresult);
		for ($c=0; $c<$NOOFRESULTS; $c++) {
			
			if($etype != $result[$c]["type"]){
				$etype = $result[$c]["type"];
				push_back($tabledata, array($etype));
			}

			$row = array();

			$row[0] = $result[$c]["e_name"];
		

			for ($i = 0; $i < count($dates); $i++) {
				if ($result[$c]["e_name"]!=$row[0] || $c>=$NOOFRESULTS) {
					$row[$i + 1] = "OFF";
				} else if ($result[$c]["rdate"]==$dates[$i]) {
					$row[$i + 1] = $result[$c]["shift_name"]."<br>".$result[$c]["start_time"]." - ".$result[$c]["end_time"];
					$c++;
				} else {
					$row[$i + 1] = "OFF";
				}
			}

			$tabledata.push_back($tabledata, $row);
		}
		mysqli_free_result($sqlresult);
		return $tabledata;
	}

	function printTable($table){
		echo '<table style="width:100%" align="right" border=1>';
		
			foreach ($table as $row){
				echo "<tr>";
				foreach($row as $elem)
					echo "<td align='center'>".$elem."</td>";
				echo "</tr>";
			}
		echo '</table>';
	}
?>