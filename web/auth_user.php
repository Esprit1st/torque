<?php
require_once ('creds.php');
require_once ('auth_functions.php');

//session.cookie_path = "/torque/";
session_set_cookie_params(0,dirname($_SERVER['SCRIPT_NAME'])); 
if (!isset($_SESSION)) { session_start(); }

//This variable will be evaluated at the end of this file to check if a user is authenticated
$logged_in = false;

if (!isset($_SESSION['torque_logged_in'])) {
  $_SESSION['torque_logged_in'] = false;
}
$logged_in = (boolean)$_SESSION['torque_logged_in'];

//There are two ways to authenticate for Open Torque Viewer
//The uploading data provider running on Android uses its torque ID, while the User Interface uses User/Password.
//Which method will be chosed depends on the variable set before including this file
// Set "$auth_user_with_torque_id" for Authetification with ID
// Set "$auth_user_with_user_pass" for Authetification with User/Password
// Default is authentication with user/pass

if(!isset($auth_user_with_user_pass)) {
  $auth_user_with_user_pass = true;
}

if (!$logged_in && $auth_user_with_user_pass)
{
  if ( auth_user() ) {
    $logged_in = true;
  }
}

//ATTENTION:
//The Torque App has no way to provide other authentication information than its torque ID.
//So, if no restriction of Torque IDs was defined in "creds.php", access to the file "upload_data.php" is always possible.

if(!isset($auth_user_with_torque_id)) {
  $auth_user_with_torque_id = false;
}

if (!$logged_in && $auth_user_with_torque_id)
{
  if ( auth_id() ) {
    $session_id = get_id();
    $logged_in = true;
  }
}

$_SESSION['torque_logged_in'] = $logged_in;

if (!$logged_in) {
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
        <div id="right-container" class="col-md-5 col-xs-12">
          <div id="right-cell">
            <h3>Login</h3>
            <div style="padding-bottom:4px;">
              <form method="post" class="form-horizontal" role="form" action="session.php" id="formlogin">
				<div class="mb-3"><input type="text" name="user" class="form-control" placeholder="(Username)" aria-label="Username"></div>
				<div class="mb-3"><input type="password" name="pass" class="form-control" placeholder="(Password)" aria-label="Password"></div>
                <input class="btn btn-primary" type="submit" id="formlogin" name="Login" value="Login" />
              </form>
            </div>
			<a href="signup.php">Sign up</a><br />
			<a href="forgot.php">Forgot Password?</a>
          </div>
		  <?php echo $debug ?>
        </div>
    </div>
  </body>
</html>
<?php
  exit(0);
} else {
  //Prepare session
  //Connect to Sql, ...
}
?>
