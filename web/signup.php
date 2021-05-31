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
        <div class="col-md-6 col-xs-12 mb-4">
				<?php
					if ($_SESSION['torque_logged_in']) echo "<h4>Account</h4>";
					else echo "<h4>Signup</h4>";
					if ($signed_up==true) echo "<br />Signup complete. Please check your email and activate your account.";
					else {
				?>
				<div class="row" style="padding-bottom:4px;">
				  <form method="post" class="form-horizontal" role="form" action="signup.php" id="formsignup" onChange="javascript:validate()">
					<?php
					if (!$error || $error["user"]!=2) { ?><div class="mb-3 form-group"><label class="form-label" for="username">Username</label><input class="form-control<?php if ($error["user"]==1) echo " is-invalid"; ?>" id="username" type="text" name="user" value="<?php echo $user ?>" placeholder="(Username)" title="Username"<?php if ($_SESSION['torque_logged_in']) echo " disabled"; ?> /><div id="usernameFeedback" class="invalid-feedback">Invalid Username. Must be 4-15 characters long. Can have letters, numbers, "-" and "_".</div></div><?php }
					else { ?><div class="mb-3 form-group"><label class="form-label" for="username">Username</label><input class="form-control is-invalid" id="username" type="text" name="user" value="<?php echo $user ?>" placeholder="(Username)" title="Username" /><div id="usernameFeedback" class="invalid-feedback">Username not available.</div></div><?php }
					?>
					<div class="mb-3 form-group"><label class="form-label" for="password">Password</label><input class="form-control<?php if ($error["pass"]) echo " is-invalid" ?>" type="password" id="password" name="pass" value="<?php echo $pass ?>" placeholder="(Password)" title="Password" /><div id="passFeedback" class="invalid-feedback">Invalid Password. Must be 8-32 characters long. Must have at least one upper and lowercase letter, number and special character: #?!@$%^&*-</div></div>
					<div class="mb-3 form-group"><label class="form-label" for="pass2">Confirm Password</label><input class="form-control<?php if ($error["pass2"]) echo " is-invalid" ?>" type="password" id="pass2" name="pass2" value="<?php echo $pass2 ?>" placeholder="(Password)" title="verify Password" /><div id="pass2Feedback" class="invalid-feedback">Passwords don't match.</div></div>
					<div class="mb-3 form-group"><label class="form-label" for="email">Email</label><input class="form-control<?php if ($error["email"]) echo " is-invalid"; ?>" id="email" type="text" name="email" value="<?php echo $email ?>" placeholder="(Email)" title="Email" /><div id="emailFeedback" class="invalid-feedback">Email adress invalid.</div></div>
<?php
	if ($_SESSION['torque_logged_in']) {
?>
					
					<h4 class="mt-5">Settings</h4>
					<div class="row">
						<div class="col-md-6 mb-3 form-group"><div class="form-check"><input class="form-check-input" type="checkbox" name="config-sf" id="config-sf"<?php if ($_SESSION['torque_config'][0]) echo " checked"; ?>><label class="form-check-label" for="config-sf">Source in Fahrenheit</label></div></div>
						<div class="col-md-6 mb-3 form-group"><div class="form-check"><input class="form-check-input" type="checkbox" name="config-uf" id="config-uf"<?php if ($_SESSION['torque_config'][1]) echo " checked"; ?>><label class="form-check-label" for="config-uf">Use Fahrenheit</label></div></div>
					</div>
					<div class="row">
						<div class="col-md-6 mb-3 form-group"><div class="form-check"><input class="form-check-input" type="checkbox" name="config-sm" id="config-sm"<?php if ($_SESSION['torque_config'][2]) echo " checked"; ?>><label class="form-check-label" for="config-sm">Source in Miles</label></div></div>
						<div class="col-md-6 mb-3 form-group"><div class="form-check"><input class="form-check-input" type="checkbox" name="config-um" id="config-um"<?php if ($_SESSION['torque_config'][3]) echo " checked"; ?>><label class="form-check-label" for="config-um">Use Miles</label></div></div>
					</div>
					<div class="mb-3 form-group"><label class="form-label" for="torque_eml">Torque-eml (obtain from ABRP)</label><input class="form-control<?php if ($error["torque_eml"]) echo " is-invalid"; ?>" id="torque_eml" type="text" name="torque_eml" value="<?php echo $torque_eml; ?>" placeholder="(Torque eml)" title="Torque EML" /><div id="torque_emlFeedback" class="invalid-feedback">Torque-eml invalid.</div></div>
					<div class="mb-3 form-group"><label class="form-label" for="abrp">ABRP forward URL</label><input class="form-control<?php if ($error["abrp"]) echo " is-invalid"; ?>" id="abrp" type="text" name="abrp" value="<?php echo $abrp; ?>" placeholder="(ABRP forward URL)" /><div id="abrpFeedback" class="invalid-feedback">URL invalid.</div></div>
					<div class="mb-3 form-group">Upload-URL for Torque-pro:<br /><?php echo $t_upload_url ?></div>
					
					<?php require_once("./config_alert.php"); ?>
<?php
	}
?>
					<br /><div class="form-group"><input class="form-control btn-primary" type="submit" id="formlogin" name="Submit" value="Submit" /></div>
				  </form>
				</div>
				<?php } ?>
        </div>
<?php
	if ($_SESSION['torque_logged_in']) {
?>
		<div class="col-md-6 col-xs-12">
			<h4>My Data</h4>
			<p>The data saved here is very sensitive. So you are completely in control of your personal data!</p>
			<p>You have the below options to delete your data or anonymize and donate it.</p>
			<p>You can either anonymize your GPS positions in all logged data-sessions or delete all logged data. Finally you can delete your account and all associated data.</p>
			<p>Every option further down includes the upper options. Deleting your account will also delete all your data. Deleting all your data will keep your account active.</p>
			
			<div class="mb-4 clearfix">
			<h5>Donate my Data</h5>
			<p>Donate your data instead of deleting everything.</p>
			<p>This will anonymize your data, so it can be used for statistical analysis.</p>
			<p>All GPS locations will be reset to 0,0 just like below. All data will be deleted from your account and moved to a generic user! This user will have all donated data of all users that chose to donate their data.
			   At that point your data cannot be distinguished from all the other data that is saved with that generic user.</p>
			<p>The data can be used to ex. analyse charging speeds etc.</p>
			<p><strong>Thank you very much</strong> if you choose to donate your data instead of deleting it.</p>
			<p>Your account information will stay untouched, you can start collecting new data if you choose so.</p>
			<button type="button" class="btn btn-danger float-end col-12" onClick="$('#delete').attr('id','delete-donate');" data-bs-toggle="modal" data-bs-target="#delete-data">
				Donate my data
			</button>
			</div>

			<div class="mb-4 clearfix">
			<h5>Reset GPS data</h5>
			<p>Delete all my GPS lat & long data.</p>
			<p>This will set all GPS positions associated with your user account to 0,0. All other data will stay untouched.</p>
			<button type="button" class="btn btn-danger float-end col-12" onClick="$('#delete').attr('id','delete-gps');" data-bs-toggle="modal" data-bs-target="#delete-data">
				Reset GPS points
			</button>
			</div>
			
			<div class="mb-4 clearfix">
			<h5>Delete Torque data</h5>
			<p>Delete all my Torque data.</p>
			<p>This will delete all data sent by Torque, but your user account will stay active.</p>
			<button type="button" class="btn btn-danger float-end col-12" onClick="$('#delete').attr('id','delete-torque');" data-bs-toggle="modal" data-bs-target="#delete-data">
				Delete Torque Log data
			</button>
			</div>
			
			<div class="mb-4 clearfix">
			<h5>Delete all data</h5>
			<p>Delete everything.</p>
			<p>This will delete all your data. All your data sent from Torque will be deleted. Also your User account will be deleted and you will be logged out.</p>
			<button type="button" class="btn btn-danger float-end col-12" onClick="$('#delete').attr('id','delete-all');" data-bs-toggle="modal" data-bs-target="#delete-data">
				Delete everything
			</button>
			</div>
			<div class="modal fade" id="delete-data" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
			  <div class="modal-dialog">
				<div class="modal-content">
				  <div class="modal-header bg-danger">
					<h5 class="modal-title text-white" id="staticBackdropLabel">Warning!</h5>
					<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
				  </div>
				  <div class="modal-body">
					<p>This action is non-reversable and your data will be lost.</p>
					<p><strong>Please proceed with caution!</strong></p>
				  </div>
				  <div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
					<button type="button" class="btn btn-danger" data-bs-dismiss="modal" id="delete">Delete now</button>
				  </div>
				</div>
			  </div>
			</div>
			<div class="modal fade" id="complete" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
			  <div class="modal-dialog">
				<div class="modal-content">
				  <div class="modal-header bg-success">
					<h5 class="modal-title text-white" id="staticBackdropLabel">Complete</h5>
					<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
				  </div>
				  <div class="modal-body" id="complete-body">
					<p>** filled by script **</p>
				  </div>
				  <div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">OK</button>
				  </div>
				</div>
			  </div>
			</div>
			
			<script language="javascript">
			if ($(location).attr('href').split("?")[1] == "del") {
				var completeModal = new bootstrap.Modal(document.getElementById("complete"), {});
				  $("#complete-body > p").text("Your data has been deleted.")
				  completeModal.show();
			}
			if ($(location).attr('href').split("?")[1] == "save") {
				var completeModal = new bootstrap.Modal(document.getElementById("complete"), {});
				  $("#complete-body > p").text("Your data has been updated.")
				  completeModal.show();
			}
			$('#delete-data .modal-footer button').on('click', function(event) {
			  var $button = $(event.target);

			  $(this).closest('.modal').one('hidden.bs.modal', function() {
				if ($button[0].id.substr(0,6) == "delete") window.location.href = "delete_data.php?d=" + $button[0].id.substr(7);
				$('#' + $button[0].id).attr('id','delete');
			  });
			});
			</script>

		</div>
<?php
	}
?>
    </div>
	</div>
  </body>
</html>