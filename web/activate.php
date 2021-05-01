<?php
require_once("./db.php");
require_once("./auth_signup.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EV Charge Cost - Open Torque</title>
    <meta name="description" content="Open Torque Viewer">
    <meta name="author" content="Matt Nicklay">
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
			<li class="nav-item"><a href="session.php?logout=true" class="nav-link">Logout</a></li>
		  <?php    } ?>
		</ul>
	  </header>
	</div>
    <div class="container">
	<div class="row">
        <div class="col-md-6 col-xs-12 mb-4">
			<h4>Activate email</h4>
			<br />
			<?php
				if (validtoken($_GET['token'])) {
					$userqry = mysqli_query($con, "SELECT id,email,token FROM $db_users_table WHERE token LIKE " . quote_value("%@".$_GET["token"]."@%") ) or die(mysqli_error($con));
					if (mysqli_num_rows($userqry) == 1) {
						$row = mysqli_fetch_assoc($userqry);
						$row["token"]=explode("@", $row["token"]);
						if (time() - $row["token"][0] < 30*60) {
							if ($row["email"]==$row["token"][2]."@".$row["token"][3]) {
								$userqry = mysqli_query($con, "UPDATE users SET active='1', token='' WHERE id='" . $row["id"] . "'") or die(mysqli_error($con));
								copynewPIDlist($row["id"]);
								echo "Your account has been activated.<br />You can <a href=\"session.php\">log in</a> now.";
							}
							else {
								$userqry = mysqli_query($con, "UPDATE users SET email=" . 	quote_value($row["token"][2]."@".$row["token"][3]) . ", token='' WHERE id='" . $row["id"] . "'") or die(mysqli_error($con));
								echo "Your email has been updated.<br />You need to <a href=\"session.php\">log in</a> again.";
								session_destroy();
							}
						}
						else {
							$userqry = mysqli_query($con, "UPDATE users SET token='' WHERE id='" . $row["id"] . "'") or die(mysqli_error($con));
							echo "Your link was expired.";
						}
					}
					else echo "Nothing to see here.";
				}
				echo $debug
			?>
        </div>
      </div>
    </div>
  </body>
</html><?php

function copynewPIDlist($id) {
	global $con,$db_keys_table;
	$userqry = mysqli_query($con, "CREATE TEMPORARY TABLE tmptable SELECT * FROM $db_keys_table WHERE user='0';") or die(mysqli_error($con));
	$userqry = mysqli_query($con, "UPDATE tmptable SET user='$id' WHERE user='0';") or die(mysqli_error($con));
	$userqry = mysqli_query($con, "INSERT INTO $db_keys_table SELECT * FROM tmptable;") or die(mysqli_error($con));
	$userqry = mysqli_query($con, "DROP TEMPORARY TABLE IF EXISTS tmptable;") or die(mysqli_error($con));
	return true;
}

?>