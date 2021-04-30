<?php

//Get Username from Browser-Request
function get_user()
{
    if (isset($_POST["user"])) {
        $user = $_POST['user'];
    }
    elseif (isset($_GET["user"])) {
        $user = $_GET['user'];
    }
	else
    {
        $user = "";
    }

    return $user;
}


//Get Password from Browser-Request
function get_pass()
{
    if (isset($_POST["pass"])) {
        $pass = $_POST['pass'];
    }
    elseif (isset($_GET["pass"])) {
        $pass = $_GET['pass'];
    }
	else
    {
        $pass = "";
    }

    return $pass;
}


//Get Torque-ID from Browser-Request
function get_id()
{
    $id = "";

    if (isset($_POST["id"])) {
        if (1 === preg_match('/[\da-f]{32}/i', $_POST['id'], $matches))
        {
            $id = $matches[0];
        }
    }
    elseif (isset($_GET["id"])) {
        if (1 === preg_match('/[\da-f]{32}/i', $_GET['id'], $matches))
        {
            $id = $matches[0];
        }
    }
    
    return $id;
}

//** Get torque-eml from torque app GET request
function get_eml() {
	if (isset($_GET["eml"])) {
        if (validtorqueeml($_GET["eml"])) return $_GET["eml"];
    }
}

//True if User/Pass match those of creds.php
//If both $auth_user and $auth_pass are empty, all passwords are accepted.
function auth_user()
{
    //global $auth_user, $auth_pass;
    global $users;
    
    $user = get_user();
    $pass = get_pass();

	//** User credentials correct?
	if ($userid = auth_db_user($user, $pass)) {
		$_SESSION['torque_user'] = $user;
		$_SESSION['torque_userid'] = $userid;
		return true;
	}
	else return false;
}

function auth_db_user($user, $pass) {
	//** check if login is correct
	global $db_users_table, $con;
	$userqry = mysqli_query($con, "SELECT id, username, password, email, torque_eml FROM $db_users_table WHERE username=" . quote_value($user) . " AND active<>'0'") or die(mysqli_error($con));
	if (mysqli_num_rows($userqry) != 1) return false;
	else {
		$row = mysqli_fetch_assoc($userqry);
		//$user, $pass, $row["username"], $row["password"]
		if (password_verify($pass, $row["password"])) {
			$_SESSION['torque_eml'] = $row["torque_eml"];
			$_SESSION['torque_useremail'] = $row["email"];
			return $row["id"];
		}
		else return false;
		$debug="good";
	}
}


//True is Torque-ID matches any of the IDs or HASHes defined in creds.php
//If both IDs and HASHes are empty, all IDs are accepted.
function auth_id()
{
    global $torque_id, $torque_id_hash;
    // Prepare authentification of Torque Instance that uploads data to this server
    // If $torque_id is defined, this will overwrite $torque_id_hash from creds.php

    $session_id = get_id();

    // Parse IDs from "creds.php", if IDs are defined these will overrule HASHES
    $auth_by_hash_possible = false;
    if (isset($torque_id) && !empty($torque_id))
    {
        if (!is_array($torque_id))
            $torque_id = array($torque_id);

        $torque_id_hash = array_map(md5,$torque_id);
        $auth_by_hash_possible = true;
    }
    // Parse HASHES
    elseif (isset($torque_id_hash) && !empty($torque_id_hash))
    {
        if (!is_array($torque_id_hash))
            $torque_id_hash = array($torque_id_hash);
        $auth_by_hash_possible = true;
    }

    // Authenticate torque instance: Check if we know its HASH
    if ($auth_by_hash_possible)
    {
        if (in_array($session_id, $torque_id_hash) )
        {
            return true;
        }
    }
    //No IDs/HASHEs defined: Allow everything
    else
    {
        return true;
    }
    return false;
}

//** This will return the user-id extracted from the user-db using the torque-eml when the app uploads data
function auth_db_id()
{
	global $db_users_table, $con;
	$eml=get_eml();
	$userqry = mysqli_query($con, "SELECT id, abrp FROM $db_users_table WHERE torque_eml=" . quote_value($eml) . " AND active<>'0'") or die(mysqli_error($con));
	if (mysqli_num_rows($userqry) != 1) return false;
	else {
		$row = mysqli_fetch_assoc($userqry);
		return array("id" => $row["id"], "abrp" => $row["abrp"]);
	}
}

function validtorqueeml($var) {
	//** torque_eml
	if ( preg_match("/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/", $var) ) return true;
}

function logout_user()
{
    session_destroy();
    header("Location: ./session.php");
    die();
}

?>
