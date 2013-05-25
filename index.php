<?php

// Templater
include_once("tbs/tbs_class_php5.php");

include_once("classes/ViewUtils.php");

// View variables
$logged = false;
$enabled = -1;
$admin = 0;
$messages = array();
$userid = 0;
$zone = 0;

// Current page variables
global $zones, $users, $ops, $recs, $decs, $action;
$zones = 0;
$users = 0;
$ops = 0;
$recs = 0;
$decs = 0;

$action = array();

// New templater
$TBS = new clsTinyButStrong();

// Configuration loading
include_once("config.inc.php");
global $mysql_host, $mysql_user, $mysql_password, $mysql_db;
global $reload_cmd;

$username = "";
$password = "";

$loginSet = false;
$doMySQL = true;

session_start();

// Logout command
if ($_GET['do'] == "out") {
	session_destroy();
	header("Location: ?");
}

// Login processing
if (isset($_POST['username']) && isset($_POST['password'])) {
	$username = $_POST['username'];
	$password = $_POST['password'];
	$loginSet = true;
	$messages = ViewUtils::checkLoginFields($_POST['username'], $_POST['password']);
	if (count($messages) > 0) {
		$doMySQL = false;
	} else {
		$_SESSION['username'] = $username;
		$_SESSION['password'] = $password;
	}
}

// Login checking
if (!$loginSet && isset($_SESSION['username']) && isset($_SESSION['password'])) {
	$loginSet = true;
	$messages = ViewUtils::checkLoginFields($_SESSION['username'], $_SESSION['password']);
	if (count($messages) > 0) {
		$doMySQL = false;
	} else {
		$username = $_SESSION['username'];
		$password = $_SESSION['password'];
	}
}

// No data, need to log in
if (!$loginSet) {
	$TBS->LoadTemplate("templates/main.html");
	$TBS->Show();
	exit(0);
}

if ($doMySQL) {

	$link = mysql_connect($mysql_host, $mysql_user, $mysql_password)
    	or die("Could not connect: ".mysql_error());
    
	mysql_select_db($mysql_db) or die("Could not select database");
	
	$query = "SELECT User.id AS id, User_State.name AS state, User_Type.name AS type FROM 
		User, User_State, User_Type
		WHERE User.name = '".$username."'
			AND User.password_hash = '".md5($password)."'
			AND User_State.id = User.state_id AND User_Type.id = User.type_id";
		
	$result = mysql_query($query) or die('Query failed: ' . mysql_error(). $query);
	$row = mysql_fetch_array($result, MYSQL_BOTH);

	if ($row) { // User exists
		$logged = true;
		$userid = $row['id'];
	} else {
		session_destroy();
	}
	
	// View configuration
	if ($row['state'] == "enabled")
		$enabled = 1;
	else
		$enabled = 0;
	if ($row['type'] == "admin")
		$admin = 1;

	mysql_free_result($result);	
	mysql_close($link);
}

// what to show on view?
if ($enabled) {
	if (isset($_GET['act'])) {
		$action[] = $_GET['act'];
		if ($action[0] == "chable") {
			if (isset($_GET['id']) && ViewUtils::checkID($_GET['id'])) {
				$action[] = $_GET['id'];
			} else {
				$action = array();
			}
		}
		if ($action[0] == "uptypes") {
			if (isset($_POST['type'])) {
				$action[] = $_POST['type'];
				$valid = true;
				foreach ($action[1] as $key => $value) {
					if (!ViewUtils::checkID($key))
						$valid = false;
					if (!ViewUtils::checkID($value))
						$valid = false;
				}
				if (!$valid)
					$action = array();
			}
		}
		if ($action[0] == "addzone") {
			if (isset($_POST['type']) && 
				isset($_POST['name']) &&
				isset($_POST['filename']) &&
				ViewUtils::checkZoneData($_POST['type'],
					$_POST['name'],
					$_POST['filename'])
				) {
					$action[] = $_POST['type'];
					$action[] = $_POST['name'];
					$action[] = $_POST['filename'];
				} else {
					$action = array();
				}
		}
		if ($action[0] == "deletezone") {
			if (isset($_GET['id']) && ViewUtils::checkID($_GET['id'])) {
				$action[] = $_GET['id'];
			} else {
				$action = array();
			}
		}
		if ($action[0] == "editzone") {
			$valid = true;
			if (isset($_POST['is'])) {
				foreach ($_POST['is'] as $key => $value) {
					if (!ViewUtils::checkZoneData($_POST['type'][$key],
						$_POST['name'][$key], $_POST['filename'][$key]))
						$valid = false;
				}
				$action[] = $_POST['is'];
				$action[] = $_POST['type'];
				$action[] = $_POST['name'];
				$action[] = $_POST['filename'];
			} else {
				$valid = false;
			}
			if (!$valid)
				$action = array();
		}
		if ($action[0] == "addrecord") {
			if (isset($_POST['name']) && 
				isset($_POST['ttl']) &&
				isset($_POST['type']) &&
				isset($_POST['data']) &&
				ViewUtils::checkRecordData($_POST['name'],
					$_POST['ttl'], $_POST['type'], $_POST['data'])
				) {
					$action[] = $_POST['name'];
					$action[] = $_POST['ttl'];
					$action[] = $_POST['type'];
					$action[] = $_POST['data'];
				} else {
					$action = array();
				}
		}
		if ($action[0] == "deleterecord") {
			if (isset($_GET['id']) && ViewUtils::checkID($_GET['id'])) {
				$action[] = $_GET['id'];
			} else {
				$action = array();
			}
		}
		if ($action[0] == "editrecord") {
			$valid = true;
			if (isset($_POST['is'])) {
				foreach ($_POST['is'] as $key => $value) {
					if (!ViewUtils::checkRecordData($_POST['name'][$key],
						$_POST['ttl'][$key], $_POST['type'][$key], $_POST['data'][$key]))
						$valid = false;
				}
				$action[] = $_POST['is'];
				$action[] = $_POST['name'];
				$action[] = $_POST['ttl'];
				$action[] = $_POST['type'];
				$action[] = $_POST['data'];
			} else {
				$valid = false;
			}
			if (!$valid)
				$action = array();
		}
		if ($action[0] == "adddirective") {
			if (isset($_POST['type']) && 
				isset($_POST['data']) &&
				ViewUtils::checkDirectiveData($_POST['type'], $_POST['data'])
				) {
					$action[] = $_POST['type'];
					$action[] = $_POST['data'];
				} else {
					$action = array();
				}
		}
		if ($action[0] == "deletedirective") {
			if (isset($_GET['id']) && ViewUtils::checkID($_GET['id'])) {
				$action[] = $_GET['id'];
			} else {
				$action = array();
			}
		}
		if ($action[0] == "editdirective") {
			$valid = true;
			if (isset($_POST['is'])) {
				foreach ($_POST['is'] as $key => $value) {
					if (!ViewUtils::checkDirectiveData($_POST['type'][$key], $_POST['data'][$key]))
						$valid = false;
				}
				$action[] = $_POST['is'];
				$action[] = $_POST['type'];
				$action[] = $_POST['data'];
			} else {
				$valid = false;
			}
			if (!$valid)
				$action = array();
		}
	}
	if (isset($_GET['do'])) {
		if ($_GET['do'] == "users") {
			$users = 1;
		} else if ($_GET['do'] == "ops") {
			$ops = 1;
			if (isset($_GET['sort']))
				$action[0] = $_GET['sort'];
			if (isset($_POST['op']))
				$action[1] = $_POST['op'];
			if (isset($_POST['user']))
				$action[2] = $_POST['user'];
		} else if ($_GET['do'] == "records" && isset($_GET['zone'])
			&& ViewUtils::checkID($_GET['zone'])) {
			$recs = 1;
			$zone = $_GET['zone'];
		} else if ($_GET['do'] == "directives" && isset($_GET['zone'])
			&& ViewUtils::checkID($_GET['zone'])) {
			$decs = 1;
			$zone = $_GET['zone'];
		} else if ($_GET['do'] == "restart") {
			shell_exec($reload_cmd);	
		} else { // default page
			$zones = 1;
		}
	} else { // the most default page
		$zones = 1;
	}
}

$TBS->LoadTemplate("templates/main.html");
$TBS->MergeBlock('blk', $messages);
$TBS->Show();

?>
