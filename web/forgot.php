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
			<h4>Forgot Password</h4>
			<br />
			<?php
			if ($sent==true) { ?>Please check your email for the recovery link.<?php }
			else if (!isset($_GET["token"]) && !isset($_POST["token"])) { ?>This will send a password reset link to your email.<br />The link will be valid for 30 minutes.<?php }
			else if (isset($_GET["token"]) || isset($_POST["token"])) { ?>Enter your new password below.<?php }
			?>
            <div class="row center-block" style="padding-bottom:4px;">
              <form method="post" class="form-horizontal" role="form" action="forgot.php" id="formsignup" onChange="javascript:validate()">
				<?php if (isset($_GET["token"]) || isset($_POST["token"])) { ?>
				<input type="hidden" id="token" name="token" value="<?php echo $_GET["token"]; ?>">
				<div class="form-group<?php if ($error["pass"]) echo " has-error" ?>"><label class="control-label" for="password">Password</label><input class="form-control" type="password" id="password" name="pass" value="<?php echo $pass ?>" placeholder="(Password)" /><small id="passwordHelp" class="text-danger<?php if (!$error || !$error["pass"]) echo " hidden" ?>">Invalid Password. Must be 8-32 characters long. Must have at least one upper and lowercase letter, number and special character: #?!@$%^&*-</small></div>
				<div class="form-group<?php if ($error["pass2"]) echo " has-error" ?>"><label class="control-label" for="pass2">Confirm Password</label><input class="form-control" type="password" id="pass2" name="pass2" value="<?php echo $pass2 ?>" placeholder="(Password)" /><small id="pass2Help" class="text-danger<?php if (!$error || !$error["pass2"]) echo " hidden" ?>">Passwords don't match.</small></div>
				<?php } 
				else {?>
				<?php
				if ($error["email"]==false) { ?><div class="form-group"><label class="control-label" for="email">Email</label><input class="form-control" id="email" type="text" name="email" value="<?php echo $email ?>" placeholder="(Email)" /><small id="emailHelp" class="text-danger hidden">Email adress invalid.</small></div><?php }
				else { ?><div class="form-group has-error"><label class="control-label" for="email">Email</label><input class="form-control" id="email" type="text" name="email" value="<?php echo $email ?>" placeholder="(Email)" /><small id="emailHelp" class="text-danger">Email adress invalid.</small></div><?php }
				} ?>
				<br /><div class="form-group"><input class="form-control btn-primary" type="submit" id="formlogin" name="Submit" value="Send" /></div>
              </form>
            </div>
			<?php echo $debug ?>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>