<?php
//echo "<!-- Begin del_session.php at ".date("H:i:s", microtime(true))." -->\r\n";
// this page relies on being included from another page that has already connected to db

if (!isset($_SESSION)) { session_start(); }

if (isset($_POST["deletesession"])) {
    $deletesession = preg_replace('/\D/', '', $_POST['deletesession']);
	$deletefrom = preg_replace('/\D/', '', $_POST['from']);
	$deleteto = preg_replace('/\D/', '', $_POST['to']);
}
elseif (isset($_GET["deletesession"])) {
    $deletesession = preg_replace('/\D/', '', $_GET['deletesession']);
	$deletefrom = preg_replace('/\D/', '', $_GET['from']);
	$deleteto = preg_replace('/\D/', '', $_GET['to']);
}

if (isset($deletesession) && !empty($deletesession)) {
    $tableYear = date( "Y", $deletesession/1000 );
    $tableMonth = date( "m", $deletesession/1000 );
    $db_table_full = "{$db_table}_{$tableYear}_{$tableMonth}";
    if (isset($deletefrom) && !empty($deletefrom) && isset($deleteto) && !empty($deleteto)) $range = " AND time >= " . quote_value($deletefrom) . " AND time <= " . quote_value($deleteto);
	else {
		$range = "";
		$delresult = mysqli_query($con, "DELETE FROM $db_sessions_table WHERE session=" . quote_value($deletesession) . " AND eml=" . quote_value($_SESSION["torque_eml"]) ) or die(mysqli_error($con));
	}
	$delresult = mysqli_query($con, "DELETE FROM $db_table_full WHERE session=" . quote_value($deletesession).$range . " AND user=" . quote_value($_SESSION["torque_userid"])) or die(mysqli_error($con));
	//** update the session data in the sessions table
	$userqry = mysqli_query($con, "SELECT MIN(time) AS start, MAX(time) AS end, COUNT(*) as size FROM $db_table_full WHERE session=" . quote_value($deletesession) ) or die(mysqli_error($con));
	if (mysqli_num_rows($userqry) == 1) {
		$row = mysqli_fetch_assoc($userqry);
		//** now update session with new start, end, time
		$userqry= "UPDATE $db_sessions_table SET `time` = ".quote_value($row["start"]).", `timestart` = ".quote_value($row["start"]).", `timeend` = ".quote_value($row["end"]).", `sessionsize` = ".quote_value($row["size"])." WHERE session=" . quote_value($deletesession);
		mysqli_query($con, $userqry) or die(mysqli_error($con));
	}
}
//echo "<!-- End del_session.php at ".date("H:i:s", microtime(true))." -->\r\n";
?>
