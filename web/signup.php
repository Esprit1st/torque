<?php
require_once("./db.php");
require_once("./auth_functions.php");
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
	<script language="javascript" type="text/javascript" src="static/js/torquehelpers.js"></script>
  </head>
  <body>
	<div class="container-xxl">
	  <header class="d-flex flex-wrap justify-content-center py-3 mb-4 border-bottom">
		<a href="session.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-dark text-decoration-none">
		  <span class="fs-4">EV-Charge-Cost Torque Viewer</span>
		</a>

		<ul class="nav nav-pills">
		  <li class="nav-item"><a href="session.php" class="nav-link active">Home</a></li>
		  <?php    if ( $_SESSION['torque_user'] ) { ?>
			<li class="nav-item"><a href="signup.php" class="nav-link"><?php echo $_SESSION['torque_user'] ?></a></li>
			<li class="nav-item"><a href="session.php?logout=true" class="nav-link">Logout</a></li>
		  <?php    } ?>
		</ul>
	  </header>
	</div>
    <div class="container">
        <div id="right-container" class="col-md-5 col-xs-12">
				<?php
					if ($_SESSION['torque_logged_in']) echo "<h4>Account</h4>";
					else echo "<h4>Signup</h4>";
					if ($signed_up==true) echo "<br />Signup complete. Please check your email and activate your account.";
					else if ($data_saved==true) echo "<br />Data saved.<br /><a href=\"session.php\">Continue</a>";
					else {
				?>
				<div class="row center-block" style="padding-bottom:4px;">
				  <form method="post" class="form-horizontal" role="form" action="signup.php" id="formsignup" onChange="javascript:validate()">
					<?php
					if (!$error || $error["user"]!=2) { ?><div class="mb-3 form-group"><label class="form-label" for="username">Username</label><input class="form-control<?php if ($error["user"]==1) echo " is-invalid"; ?>" id="username" type="text" name="user" value="<?php echo $user ?>" placeholder="(Username)" title="Username"<?php if ($_SESSION['torque_logged_in']) echo " disabled"; ?> /><div id="usernameFeedback" class="invalid-feedback">Invalid Username. Must be 4-15 characters long. Can have letters, numbers, "-" and "_".</div></div><?php }
					else { ?><div class="mb-3 form-group"><label class="form-label" for="username">Username</label><input class="form-control is-invalid" id="username" type="text" name="user" value="<?php echo $user ?>" placeholder="(Username)" title="Username" /><div id="usernameFeedback" class="invalid-feedback">Username not available.</div></div><?php }
					?>
					<div class="mb-3 form-group"><label class="form-label" for="password">Password</label><input class="form-control<?php if ($error["pass"]) echo " is-invalid" ?>" type="password" id="password" name="pass" value="<?php echo $pass ?>" placeholder="(Password)" title="Password" /><div id="passFeedback" class="invalid-feedback">Invalid Password. Must be 8-32 characters long. Must have at least one upper and lowercase letter, number and special character: #?!@$%^&*-</div></div>
					<div class="mb-3 form-group"><label class="form-label" for="pass2">Confirm Password</label><input class="form-control<?php if ($error["pass2"]) echo " is-invalid" ?>" type="password" id="pass2" name="pass2" value="<?php echo $pass2 ?>" placeholder="(Password)" title="verify Password" /><div id="pass2Feedback" class="invalid-feedback">Passwords don't match.</div></div>
					<?php
					if ($error["email"]==false) { ?><div class="mb-3 form-group"><label class="form-label" for="email">Email</label><input class="form-control" id="email" type="text" name="email" value="<?php echo $email ?>" placeholder="(Email)" title="Email" /><div id="emailFeedback" class="invalid-feedback">Email adress invalid.</div></div><?php }
					else { ?><div class="mb-3 form-group"><label class="form-label" for="email">Email</label><input class="form-control is-invalid" id="email" type="text" name="email" value="<?php echo $email ?>" placeholder="(Email)" title="Email" /><div id="emailFeedback" class="invalid-feedback">Email adress invalid.</div></div><?php }
					?>
<?php
	if ($_SESSION['torque_logged_in']) {
?>
					<?php
					if ($error["torque_eml"]==false) { ?><div class="mb-3 form-group"><label class="form-label" for="torque_eml">Torque-eml (obtain from ABRP)</label><input class="form-control" id="torque_eml" type="text" name="torque_eml" value="<?php echo $torque_eml; ?>" placeholder="(Torque eml)" title="Torque EML" /><div id="torque_emlFeedback" class="invalid-feedback">Torque-eml invalid.</div></div><?php }
					else { ?><div class="mb-3 form-group"><label class="form-label" for="torque_eml">Torque-eml (obtain from ABRP)</label><input class="form-control" id="torque_eml" type="text" name="torque_eml" value="<?php echo $torque_eml; ?>" placeholder="(Torque eml)" title="Torque EML" /><div id="torque_emlFeedback" class="invalid-feedback">Torque-eml invalid.</div></div><?php }
					?>
					<div class="mb-3 form-group"><label class="form-label" for="abrp">ABRP forward URL</label><input class="form-control" id="abrp" type="text" name="abrp" value="<?php echo $abrp; ?>" placeholder="(ABRP forward URL)" /></div>
					<div class="mb-3 form-group">Upload-URL for Torque-pro:<br /><?php echo $t_upload_url ?></div>
<?php
	}
?>
					<br /><div class="form-group"><input class="form-control btn-primary" type="submit" id="formlogin" name="Submit" value="Submit" /></div>
				  </form>
				</div>
				<?php }
					echo $debug;
				?>
        </div>
    </div>
  </body>
</html>