<?php
require_once("./db.php");
require_once("./auth_user.php");


if (isset($_GET["sid"])) {
    $session_id = $_GET['sid'];
    // Get data for session
    $output = "";
    $tableYear = date( "Y", $session_id/1000 );
    $tableMonth = date( "m", $session_id/1000 );
    $db_table_full = "{$db_table}_{$tableYear}_{$tableMonth}";
	// if plotvars is selected build a selector with only those variables
    if ( isset($_GET["plotvars"]) ) {
		$selector[]="session";
		$selector[]="time";
		$selector=quote_names( array_merge($selector,explode(",", $_GET["plotvars"])) );
	}
	else $selector="*";

	if (isset($_GET["from"]) && isset($_GET["to"])) {
		// if a selection on the graph has been made, only export that selection
		//**$sql = mysqli_query($con, "SELECT * FROM $db_table_full join $db_sessions_table on $db_table_full.session = $db_sessions_table.session WHERE $db_table_full.session=".quote_value($session_id)." AND $db_table_full.time > ".quote_value($_GET['from'])." AND $db_table_full.time < ".quote_value($_GET['to'])." ORDER BY $db_table_full.time DESC;") or die(mysqli_error($con));
		$sql = mysqli_query($con, "SELECT $selector FROM $db_table_full WHERE session=".quote_value($session_id)." AND time > ".quote_value($_GET['from'])." AND time < ".quote_value($_GET['to'])." ORDER BY time DESC;") or die(mysqli_error($con));
    }
	else {
		// export full session
		$replacesql=array("`session`","`time`");
		$replacewithsql=array("$db_table_full.`session`","$db_table_full.`time`");
		$sqlquery="SELECT $selector FROM $db_table_full join $db_sessions_table on $db_table_full.session = $db_sessions_table.session WHERE $db_table_full.session=".quote_value($session_id)." ORDER BY $db_table_full.time DESC;";
		$sqlquery=str_replace(array("`session`","`time`"), array("$db_table_full.`session`","$db_table_full.`time`"), $sqlquery);
		$sql = mysqli_query($con, $sqlquery) or die(mysqli_error($con));
    }

    if ($_GET["filetype"] == "csv") {
        $columns_total = mysqli_num_fields($sql);

        // Get The Field Name
		$counter = 0;
        while ($property = mysqli_fetch_field_direct($sql, $counter)) {
            $output .='"'.$property->name.'",';
            $counter++;
        }
        $output .="\n";

        // Get Records from the table
        while ($row = mysqli_fetch_array($sql)) {
            for ($i = 0; $i < $columns_total; $i++) {
                $output .='"'.$row["$i"].'",';
            }
            $output .="\n";
        }
		
		// remove the last comma before every line break
		$output=str_replace(",\n", "\n", $output);

        mysqli_free_result($sql);

        // Download the file
        $csvfilename = "torque_session_".$session_id.".csv";
//        header('Content-type: application/csv');
//        header('Content-Disposition: attachment; filename='.$csvfilename);

        echo $output."</pre>";
        exit;
    }
    else if ($_GET["filetype"] == "json") {
        $rows = array();
        while($r = mysqli_fetch_assoc($sql)) {
            $rows[] = $r;
        }
        $jsonrows = json_encode($rows);

        mysqli_free_result($sql);

        // Download the file
        $jsonfilename = "torque_session_".$session_id.".json";
        header('Content-type: application/json');
        header('Content-Disposition: attachment; filename='.$jsonfilename);

        echo $jsonrows;
    }
}

mysqli_close($con);

?>
