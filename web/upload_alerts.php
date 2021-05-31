<?php

//current data is in these arrays
//print_r($datakeys);
//print_r($datavalues);

//Deal with the data and send alerts
//print_r($user);

$alertconfig=explode("|", $user["config"]);
$alertconfig=array_filter($alertconfig, "isalert");
if (count($alertconfig) > 0) {
	//** grab second latest data (which is the latest in DB because the latest hasn't been written to DB yet)
	$userqry = mysqli_query($con, "SELECT * FROM $db_table_full WHERE user=" . quote_value($user["id"]) . " AND session=" . quote_value($sessuploadid) . " ORDER BY time DESC LIMIT 1" ) or die(mysqli_error($con));
	if (mysqli_num_rows($userqry) == 1) {
		$scndlatest = mysqli_fetch_assoc($userqry);
		$latest = array_combine($datakeys, $datavalues);
		foreach($alertconfig as $alert) {
			$alert=explode("&", $alert);
			//** if the alert is "gets bigger than"
			if ($alert[2] == 1) {
				if (isset($latest[$alert[1]]) && isset($scndlatest[$alert[1]])) {
					if ($scndlatest[$alert[1]] <= $alert[3] && $latest[$alert[1]] > $alert[3]) {
						//** Alarm triggered
						//echo "The value $alert[1] (".$latest[$alert[1]].") exceeded: ".$alert[3];
						mail($user["email"],"User Alarm","The value $alert[1] (".$latest[$alert[1]].") exceeded: ".$alert[3], $email_headers);
					}
				}
			}
			//** if the alert is "gets smaller than"
			elseif ($alert[2] == 2) {
				if (isset($latest[$alert[1]]) && isset($scndlatest[$alert[1]])) {
					if ($scndlatest[$alert[1]] >= $alert[3] && $latest[$alert[1]] < $alert[3]) {
						//** Alarm triggered
						//echo "The value $alert[1] (".$latest[$alert[1]].") is lower than: ".$alert[3];
						mail($user["email"],"User Alarm","The value $alert[1] (".$latest[$alert[1]].") dropped below: ".$alert[3], $email_headers);
					}
				}
			}
			//** if the alert is "is farther than"
			elseif ($alert[2] == 3) {
				if (isset($latest[$alert[1]]) && isset($latest[$alert[4]])) {
					if ( abs($latest[$alert[1]] - $latest[$alert[4]]) > $alert[3]) {
						//** Alarm triggered
						//echo "The values $alert[1] (".$latest[$alert[1]].") and $alert[4] (".$latest[$alert[4]].") are farther apart than: ".$alert[3];
						mail($user["email"],"User Alarm","The values $alert[1] (".$latest[$alert[1]].") and $alert[4] (".$latest[$alert[4]].") are farther apart than: ".$alert[3], $email_headers);
					}
				}
			}
			//** if the alert is "is closer than"
			elseif ($alert[2] == 4) {
				if (isset($latest[$alert[1]]) && isset($latest[$alert[4]])) {
					if ( abs($latest[$alert[1]] - $latest[$alert[4]]) < $alert[3]) {
						//** Alarm triggered
						//echo "The values $alert[1] (".$latest[$alert[1]].") and $alert[4] (".$latest[$alert[4]].") are closer together than: ".$alert[3];
						mail($user["email"],"User Alarm","The values $alert[1] (".$latest[$alert[1]].") and $alert[4] (".$latest[$alert[4]].") are closer together than: ".$alert[3], $email_headers);
					}
				}
			}
			else echo "no alarm";
		}
	}
}

//** Is the config variable an alert?
function isalert($var) {
	if ($var[0] == "A") return true;
}

?>