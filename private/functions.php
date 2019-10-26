
<?php
	function loadData(){

	    $strJsonFileContents = file_get_contents("../private/teams.json");
	    // Convert to array
	    $arr = json_decode($strJsonFileContents, true);

	    return $arr;
	}

	function getGoalFactor($g1, $g2){
		$N = abs($g1 - $g2);
		
		if ($N==0 or $N==1){
			return 1;
		}
		else if ($N == 2){
			return 3/2;
		}
		else if ($N > 2){
			return (11+$N)/8;
		}
	}

	function getWActual($primaryTeamGoals, $otherTeamGoals){
		if ($primaryTeamGoals == $otherTeamGoals)
			return 0.5;
		else if ($primaryTeamGoals < $otherTeamGoals)
			return 0;
		else 
			return 1;
	}

	function getWExpected($rating, $otherating, $ishome){
		$dr = $rating - $otherating;
		if ($ishome)
			$dr = $dr + 100;
		return  1/(1+ pow(10, (-1*$dr/400)));
	}

	function getNewRating($team, $otherteam, $goals, $othergoals, $ishome){
		$WA = getWActual($goals, $othergoals);
		$WE = getWExpected($team["rating"], $otherteam["rating"], $ishome);
		$G = getGoalFactor($goals, $othergoals);
		$P = 50*$G*($WA - $WE);

		$Rnew = $teams["rating"] + $P; 

		return $Rnew;
	}

?>