<?php
require_once("./db.php");

session_start();

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
    <script language="javascript" type="text/javascript" src="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
    <script language="javascript" type="text/javascript" src="static/js/jquery.peity.min.js"></script>
    <script language="javascript" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.1.0/chosen.jquery.min.js"></script>
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
            <h4>Signup</h4>
            <div class="row center-block" style="padding-bottom:4px;">
              <form method="post" class="form-horizontal" role="form" action="auth_signup.php" id="formsignup">
                <label for="username">Username</label><input class="form-control" id="username" type="text" name="user" value="" placeholder="(Username)" />
                <label for="username">Password</label><input class="form-control" type="password" id="password" name="pass" value="" placeholder="(Password)" />
				<label for="username">Confirm Password</label><input class="form-control" id="pass2" type="password" name="pass2" value="" placeholder="(Password)" />
                <label for="username">Email</label><input class="form-control" id="email" type="text" name="email" value="" placeholder="(Email)" />
<?php
	if ($_SESSION['torque_logged_in']) {
?>
				<label for="username">Torque-eml</label><input class="form-control" id="torqueeml" type="text" name="torqueeml" value="" placeholder="(Torque eml)" />
				<label for="username">Torque-id</label><input class="form-control" id="torqueid" type="text" name="torqueid" value="" placeholder="(Torque ID)" />
<?php
	}
?>
				<br /><input class="form-control" type="submit" id="formlogin" name="Submit" value="Submit" />
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>