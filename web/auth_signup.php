<?php
session_start();

//** forgot password / generate token and save to DB
if (!$_SESSION['torque_logged_in'] && $_POST["Submit"]=="Send" && $_POST["email"]!="") {
	if ( validemail($_POST["email"]) ) {
		$userqry = mysqli_query($con, "SELECT id FROM $db_users_table WHERE email=" . quote_value($_POST["email"]) ) or die(mysqli_error($con));
		if (mysqli_num_rows($userqry) == 1) {
			$row = mysqli_fetch_assoc($userqry);
			$token = bin2hex(random_bytes(32));
			$userqry= "UPDATE $db_users_table SET `token` = ".quote_value(time() ."@". $token)." WHERE id=" . $row["id"];
			mysqli_query($con, $userqry) or die(mysqli_error($con));
			mail($_POST["email"],"Password recovery","This is your password recovery link: https://torquetest.evchargecost.com/forgot.php?token=".$token);
			$sent=true;
		}
	}
	else $error["email"] = true;
}

//** new password entered after recovery link clicked
if (validtoken($_POST["token"]) && $_POST["Submit"]=="Send") {
	unset($error);
	$userqry = mysqli_query($con, "SELECT id,email,token FROM $db_users_table WHERE token LIKE " . quote_value("%@".$_POST["token"]) ) or die(mysqli_error($con));
	if (mysqli_num_rows($userqry) == 1) {
		$row = mysqli_fetch_assoc($userqry);
		if (time() - substr($row["token"],0,10) < 30*60) {
			//** Throw error if password not valid
			if ( !validpassword($_POST["pass"]) ) { $error["pass"] = true; }
			//** Throw error if passwords don't match
			else if ($_POST["pass"]!=$_POST["pass2"]) { $error["pass2"] = true; }
			if (!$error) {
				$data["password"]=password_hash($_POST["pass"], PASSWORD_DEFAULT);;
				$data["token"]="";
				foreach ($data as $key => $value) {
					$entries[] = $key ." = ". quote_value($value);
				}
				$userqry = mysqli_query($con, "UPDATE users SET ". implode(", ", $entries) .
					" WHERE id='" . $row["id"] . "'") or die(mysqli_error($con));
				mail($row["email"],"Password reset","Your password on www.ev-charge-cost.com has been reset.\nIf you did not reset your password, get in contact with the admin.");
				header("Location: /session.php");
			}
		}
	}
}

//** if signup data has been submitted
if (!$_SESSION['torque_logged_in'] && $_POST["Submit"]=="Submit") {
	//** Throw error if username not valid
	if ( !validusername($_POST["user"]) ) { $error["user"] = 1; }
	//** Throw error if username not available
	else if ( !availableusername($_POST["user"]) ) { $error["user"] = 2; }
	//** Throw error if password not valid
	if ( !validpassword($_POST["pass"]) ) { $error["pass"] = true; }
	//** Throw error if passwords don't match
	else if ($_POST["pass"]!=$_POST["pass2"]) { $error["pass2"] = true; }
	//** Throw error if email not valid
	if ( !validemail($_POST["email"]) ) { $error["email"] = true; }
	//** Throw error if torque-eml not valid
	if ( !empty($_POST["torque_eml"]) && !validtorqueeml($_POST["torque_eml"]) ) { $error["torque_eml"] = true; }
	
	$user=$_POST["user"];
	$pass=$_POST["pass"];
	$pass2=$_POST["pass2"];
	$email=$_POST["email"];
	$torqueeml=$_POST["torque_eml"];

	//** insert new user into database
	if (!$error) {
		$data["username"]=$user;
		$data["password"]=password_hash($pass, PASSWORD_DEFAULT);;
		$data["email"]=$email;
		$data["token"]="";
		$data["salt"]="";
		$data["torque_eml"]="";
		$data["active"]="1";
		$userqry= "INSERT INTO $db_users_table (".quote_names(array_keys($data)).") VALUES (".quote_values(array_values($data)).")" ;
//$debug = $userqry;
		mysqli_query($con, $userqry) or die(mysqli_error($con));
	}
}

//** if user logged in
//** update user credentials
//** read user data from database
if ($_SESSION['torque_logged_in']) {
	//** update user in database
	if ($_POST["Submit"]=="Submit") {
		//** Throw error if password not valid
		if ( !validpassword($_POST["pass"]) ) { $error["pass"] = true; }
		//** Throw error if passwords don't match
		else if ($_POST["pass"]!=$_POST["pass2"]) { $error["pass2"] = true; }
		//** If both passwords are empty remove error
		if (empty($_POST["pass"]) && empty($_POST["pass2"])) { unset($error["pass"]); unset($error["pass2"]); }
		//** Throw error if email not valid
		if ( !validemail($_POST["email"]) ) { $error["email"] = true; }
		//** Throw error if torque-eml not valid
		if ( !empty($_POST["torque_eml"]) && !validtorqueeml($_POST["torque_eml"]) ) { $error["torque_eml"] = true; }
		//** update userdata to database
		if (!$error ) {
			//** If Password is set, generate hash and save to DB
			if ($_POST["pass"] != "") $data["password"] = password_hash($_POST["pass"], PASSWORD_DEFAULT);
			$data["email"]=$_POST["email"];
			$data["torque_eml"]=$_POST["torque_eml"];
			$data["abrp"]=$_POST["abrp"];
			foreach ($data as $key => $value) {
				$entries[] = $key ." = ". quote_value($value);
				//$debug.=$_POST[$value];
			}
			$userqry = mysqli_query($con, "UPDATE users SET ". implode(", ", $entries) .
				" WHERE id='" . $_SESSION['torque_userid'] . "'") or die(mysqli_error($con));
		}
	}

	//** fill form from database
	$userqry = mysqli_query($con, "SELECT username, email, torque_eml, abrp, active
		FROM $db_users_table
		WHERE username='" . $_SESSION['torque_user'] . "'") or die(mysqli_error($con));
	if (mysqli_num_rows($userqry) > 0) {
		// output data
		while($row = mysqli_fetch_assoc($userqry)) {
			$user = $row["username"];
			$email = $row["email"];
			$torque_eml = $row["torque_eml"];
			$abrp = $row["abrp"];
			$active = $row["active"];
		}
	}
}

function validusername($var) {
	//** Minimum 4 characters, maximum 15 characters, allowed are lower english, upper english characters, numbers, - _
	if ( preg_match("/^[a-zA-Z0-9_-]{4,15}$/", $var) ) return true;
}

function availableusername($var) {
	//** check if username is taken
	global $db_users_table, $con;
	$userqry = mysqli_query($con, "SELECT username FROM $db_users_table	WHERE username='" . $var . "'") or die(mysqli_error($con));
	if (mysqli_num_rows($userqry) > 0) return false;
	else return true;
}

function validpassword($var) {
	//** Since it is prehashed on the client we just check that it's a valid md5 checksum
	if ( preg_match("/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,32}$/", $var) ) return true;
}

function validemail($var) {
	//** email
	if ( preg_match("/^[^@:; \t\r\n]+@[^@:; \t\r\n]+\.[^@:; \t\r\n]+$/", $var) ) return true;
}

function validtoken($var) {
	//** token
	if ( preg_match("/^[a-f0-9]*$/", $var) ) return true;
}

?>