<?php

	// Connect to oracle database (with possible alternatives on failure)
	$count = count($application["oracle"]["current"]);
	for($i = 0; $i < $count; $i++){
		if((!isset($application["oracle"]["connection"])) || ($application["oracle"]["connection"] == false)){
			// Attempt to connect to the oracle database
			$application["oracle"]["connection"] = @oci_connect($application["oracle"]["current"][$i]["user"], $application["oracle"]["current"][$i]["pass"], $application["oracle"]["current"][$i]["conid"]) or ($conn_error = oracle_error());
			$application["oracle"]["current"]["confunc"][] = '@oci_connect(' . $application["oracle"]["current"][$i]["user"] . ', ' . $application["oracle"]["current"][$i]["pass"] . ', ' . $application["oracle"]["current"][$i]["conid"] . ') or ($conn_error = oracle_error());';
			// Try again if there was a failure
			if(($application["oracle"]["connection"] == false) || (isset($con_error))){
				$_SESSION["errors"][] = 'Failed attempt to connect to ' . $application["oracle"]["current"][$i]["server"];
				// Clear to prepare to reattempt
				unset($con_error);
				if(!is_bool($application["oracle"]["connection"])){
					@oci_close($application["oracle"]["connection"]);
				} // if(!is_bool($application["oracle"]["connection"]))
				$st = $i + 1;
				$fn = $i + 2;
				// Two (one more) tries per alternative
				for($j = $st; $j < $fn; $j++){
					// Wait 3 seconds
					sleep(3);
					// Try again to connect
					$application["oracle"]["connection"] = @oci_connect($application["oracle"]["current"][$i]["user"], $application["oracle"]["current"][$i]["pass"], $application["oracle"]["current"][$i]["conid"]) or ($conn_error = oracle_error());
					$application["oracle"]["current"]["confunc"][] = '@oci_connect(' . $application["oracle"]["current"][$i]["user"] . ', ' . $application["oracle"]["current"][$i]["pass"] . ', ' . $application["oracle"]["current"][$i]["conid"] . ') or ($conn_error = oracle_error());';
				} // for($j = 2; $j < 3; $j++)
			} // if($application["oracle"]["connection"] == false)
		} // if(!isset($application["oracle"]["connection"]) || ($application["oracle"]["connection"] == false))
	} // for($i = 0; $i < $count; $i++)

	if($application["oracle"]["connection"] == false){
		$_SESSION["errors"][] = '<span class="error">ERROR:</span> Unable to make database connection, cannot continue: ' . $conn_error;
	}else{
		unset($_SESSION["errors"]);
	} // if($application["oracle"]["connection"] == false)

?>
