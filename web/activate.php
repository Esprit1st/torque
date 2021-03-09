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
    <title>Open Torque Viewer</title>
    <meta name="description" content="Open Torque Viewer">
    <meta name="author" content="Matt Nicklay">
    <meta name="author" content="Joe Gullo (surfrock66)">
    <link rel="stylesheet" href="static/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.0/chosen.min.css">
    <link rel="stylesheet" href="static/css/torque.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
    <script language="javascript" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script language="javascript" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
    <script language="javascript" type="text/javascript" src="https://netdna.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script language="javascript" type="text/javascript" src="static/js/jquery.peity.min.js"></script>
    <script language="javascript" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.1.0/chosen.jquery.min.js"></script>
	<script language="javascript" type="text/javascript" src="static/js/torquehelpers.js"></script>
  </head>
  <body>
    <div class="navbar navbar-default navbar-fixed-top navbar-inverse" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <a class="navbar-brand" href="session.php">Open Torque Viewer</a>
        </div>
        <div id="map-container" class="col-md-7 col-xs-12">&nbsp;</div>
        <div id="right-container" class="col-md-5 col-xs-12">
          <div id="right-cell">
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
    </div>
  </body>
</html>