<?php
include_once("tbs/tbs_class_php5.php");

include_once("classes/ViewUtils.php");

include_once("config.inc.php");
global $mysql_host, $mysql_user, $mysql_password, $mysql_db;

$messages = array();
$username = "";
$password = "";

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate("templates/register.html");

// Main registration form
if (!(isset($_POST['username']) && isset($_POST['password']))) {
	$TBS->MergeBlock('blk', $messages);
	$TBS->Show();
	exit(0);
}

$username = $_POST['username'];
$password = $_POST['password'];
$regexp_username = "/^[a-zA-Z0-9_]+$/";
$regexp_password = "/^[a-zA-Z0-9_]+$/";

$doMySQL = true;
$messages = ViewUtils::checkLoginFields($username, $password);
if (count($messages) != 0)
	$doMySQL = false;

// Data is suitable
if ($doMySQL) {
	$link = mysql_connect($mysql_host, $mysql_user, $mysql_password)
    	or die("Could not connect: ".mysql_error());
    
	mysql_select_db($mysql_db) or die("Could not select database");
	
	$query = "SELECT id FROM User WHERE name = '".$username."'";
		
	$result = mysql_query($query) or die('Query failed: ' . mysql_error(). $query);
	$row = mysql_fetch_array($result, MYSQL_BOTH);

	if ($row) { // User exists
		$messages[] = "Username ".$username." is already taken. Please choose another one.";
	} else { // Register new user
		mysql_free_result($result);
		$password_hash = md5($password);
		
		$state_id = 0;		
		// Has disabled state really got id=0?
		$query = "SELECT id FROM User_State WHERE name = 'disabled'";
		$result = mysql_query($query)
			or die('Query failed: ' . mysql_error(). $query);
		$row = mysql_fetch_array($result, MYSQL_BOTH);
		if ($row)
			$state_id = $row['id'];
		mysql_free_result($result);
		
		$type_id = 0;		
		// Has common user really got id=0?
		$query = "SELECT id FROM User_Type WHERE name = 'user'";
		$result = mysql_query($query)
			or die('Query failed: ' . mysql_error(). $query);
		$row = mysql_fetch_array($result, MYSQL_BOTH);
		if ($row)
			$type_id = $row['id'];
		mysql_free_result($result);
					
		$query = "INSERT INTO User (name, password_hash, state_id, type_id)
		VALUES ('".$username."', '".$password_hash."', '".$state_id."', ".$type_id.")";
		
		$result = mysql_query($query)
			or die('Registration failed: ' . mysql_error(). $query);
			
		echo 'Registered.';
		
		mysql_close($link);
		
		exit(0);
	}
	
	mysql_close($link);
}

$TBS->MergeBlock('blk', $messages);
$TBS->Show();

?>