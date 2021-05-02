<?php
require_once("./db.php");
require_once("./auth_user.php");

//** grab latest session
$sql = mysqli_query($con, "SELECT * FROM $db_sessions_table WHERE eml='".$_SESSION["torque_eml"]."' ORDER BY time DESC LIMIT 1") or die(mysqli_error($con));
if ( $latest_session = mysqli_fetch_array($sql) ) {

	//** grab latest data from that session
	$session_ym = date("Y_m", substr($latest_session["time"],0,10));
	$sql = mysqli_query($con, "SELECT * FROM " . $db_table . "_" . $session_ym . " WHERE user='".$_SESSION["torque_userid"]."' AND session='".$latest_session["session"]."' ORDER BY time DESC LIMIT 1") or die(mysqli_error($con));
	$latest_data = mysqli_fetch_array($sql);

	//** grab column description
	$sql = mysqli_query($con, "SELECT id, description, units FROM $db_keys_table WHERE user='".$_SESSION["torque_userid"]."'") or die(mysqli_error($con));
	while ($row = mysqli_fetch_array($sql)) {
		$keyiddesc[$row["id"]][0] = $row["description"];
		$keyiddesc[$row["id"]][1] = $row["units"];
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EV Charge Cost - Open Torque</title>
    <meta name="description" content="Open Torque Viewer">
    <meta name="author" content="Joe Gullo (surfrock66)">
	<meta name="author" content="Ingo Nehls">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.0/chosen.min.css">
    <link rel="stylesheet" href="static/css/torque.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
    <script language="javascript" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script language="javascript" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js" integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf" crossorigin="anonymous"></script>
    <script language="javascript" type="text/javascript" src="static/js/jquery.peity.min.js"></script>
    <script language="javascript" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.1.0/chosen.jquery.min.js"></script>
    <script language="javascript" type="text/javascript" src="static/js/torquehelpers.js"></script>
  </head>
  <body>
	<div class="container-xxl">
	  <header class="d-flex flex-wrap justify-content-center py-3 mb-4 border-bottom">
		<a href="session.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-dark text-decoration-none">
		  <span class="fs-4">EV-Charge-Cost Torque Viewer</span>
		</a>

		<ul class="nav nav-pills">
		    <?php if (!empty($homelink)) { ?><li class="nav-item"><a href="<?php echo $homelink; ?>" class="nav-link active">Home</a></li><?php } ?>
		    <li class="nav-item"><a href="session.php" class="nav-link">Torque</a></li>
		  <?php    if ( $_SESSION['torque_user'] ) { ?>
			<li class="nav-item"><a href="signup.php" class="nav-link"><?php echo $_SESSION['torque_user'] ?></a></li>
			<li class="nav-item"><a href="latest.php" class="nav-link">Latest</a></li>
			<li class="nav-item"><a href="session.php?logout=true" class="nav-link">Logout</a></li>
		  <?php    } ?>
		</ul>
	  </header>
	</div>
	<div class="container">
	<div class="row">
		<div class="badge bg-info mb-3"><h4>Session</h4></div>
		<?php echo print_table($latest_session, "session,time,profileName,timestart,timeend,sessionsize"); ?>
		<div class="badge bg-info mb-3"><h4>Data</h4></div>
		<?php echo print_table($latest_data, ""); ?>
	</div>
	</div>
  </body>
</html>
<?php

function print_table ($array, $vars) {
	$display0=false;
	global $keyiddesc;
	$out1="";
	$out2="";
	if ($vars) $vars = explode(",", $vars);
	foreach ( $array as $key => $value ) {
		if ( $display0 || empty($value)==$display0 ) {
			if ( (in_array($key, $vars, true) || empty($vars)) && !is_int($key) ) {
				if (strstr($key, "time")) $value = date("m/d/Y h:i", substr($value,0,10));
				if ( $keyiddesc[$key] ) {
					$value .= "&nbsp;".$keyiddesc[$key][1];
					$key = $keyiddesc[$key][0];
				}
				$out1 .= "<div class=\"col text-center text-nowrap pb-5\"><div class=\"fw-bold\">" . $key . "</div><br />" . $value . "</div>";
			}
		}
	}
	return "<div class=\"row\">".$out1."</div>";
}

?>