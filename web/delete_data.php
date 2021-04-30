<pre>
<?php
require_once("./db.php");
require_once("./auth_user.php");


if (isset($_GET["d"])) {
	//print_r($_SESSION);
	$sql = mysqli_query($con, "SELECT table_name FROM INFORMATION_SCHEMA.tables WHERE table_schema = '$db_name' and table_name like '$db_table%' ORDER BY table_name DESC;") or die(mysqli_error($con));
	while ($row = mysqli_fetch_array($sql)) {
		$alltables[]=$row["table_name"];
	}
	//** Set GPS datapoints to 0,0
	if ($_GET["d"]=="gps") {
		foreach ($alltables as $table) {
			$sql = mysqli_query($con, "UPDATE $table SET `kff1005`='0', `kff1006`='0' WHERE user='" . $_SESSION["torque_userid"] . "'") or die(mysqli_error($con));
		}
		header("Location: ./signup.php?del");
	}
	//** delete all torque log entries
	else if ($_GET["d"]=="torque" || $_GET["d"]=="all") {
		foreach ($alltables as $table) {
			$sql = mysqli_query($con, "DELETE FROM $table WHERE user='" . $_SESSION["torque_userid"] . "'") or die(mysqli_error($con));
			$sql = mysqli_query($con, "DELETE FROM $db_sessions_table WHERE eml='" . $_SESSION["torque_eml"] . "'") or die(mysqli_error($con));
		}
		if ($_GET["d"]=="torque") header("Location: ./signup.php?del");
	}
	//** delete account
	if ($_GET["d"]=="all") {
		$sql = mysqli_query($con, "DELETE FROM $db_keys_table WHERE user='" . $_SESSION["torque_userid"] . "'") or die(mysqli_error($con));
		$sql = mysqli_query($con, "DELETE FROM $db_users_table WHERE id='" . $_SESSION["torque_userid"] . "'") or die(mysqli_error($con));
		logout_user();
	}
}

?>
</pre>