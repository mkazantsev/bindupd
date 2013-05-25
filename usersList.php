<?php
	// Configuration loading
	include_once("config.inc.php");
	global $mysql_host, $mysql_user, $mysql_password, $mysql_db;

	// Subtemplating
	if (isset($this)) {
    	$TBS =& $this;
	} else {
	    exit(0);
	}
	
	include_once("tbs/tbs_plugin_html.php");
	$TBS->PlugIn(TBS_INSTALL,TBS_HTML);
	
	global $admin, $action;
	
	if ($admin != 1)
		exit(0);
		
	session_start();
	
	$link = mysql_connect($mysql_host, $mysql_user, $mysql_password)
		or die("Could not connect: ".mysql_error());
    
	mysql_select_db($mysql_db) or die("Could not select database");
	
	// Actions if there are some
	if (isset($action) && $action && count($action) > 0) {
		if ($action[0] == "chable") {
			// Current state
			$query = "SELECT User_State.name AS state FROM User, User_State
				WHERE User_State.id = User.state_id
				AND User.id = ".$action[1];
			$result = mysql_query($query) or die('Query failed: ' . mysql_error(). $query);
			$row = mysql_fetch_array($result, MYSQL_BOTH);
			$doEnable = true;
			$newID = 0;
			if ($row['state'] == "enabled")
				$doEnable = false;
			mysql_free_result($result);
			
			// Another state
			$query = "SELECT id, name FROM User_State";
			$result = mysql_query($query) or die('Query failed: ' . mysql_error(). $query);
			while ($row = mysql_fetch_array($result, MYSQL_BOTH)) {
				if ($row['name'] == ($doEnable ? "enabled" : "disabled"))
					$newID = $row['id'];
			}
			mysql_free_result($result);
			
			// Set new state
			$query = "UPDATE User SET state_id = ".$newID." WHERE id = ".$action[1];
			$result = mysql_query($query) or die('Query failed: ' . mysql_error(). $query);
			mysql_free_result($result);
		}
		if ($action[0] == "uptypes") {
			// Update types
			foreach ($action[1] as $key => $value) {
				$query = "UPDATE User SET type_id = ".$value." WHERE id = ".$key;
				$result = mysql_query($query) or die('Query failed: ' . mysql_error(). $query);
				mysql_free_result($result);
			}
		}
	}
	
	// Show the list
	$query = "SELECT User.id AS id, User.name AS name, User_State.name AS state, User_Type.id AS type FROM 
		User, User_State, User_Type
		WHERE User_State.id = User.state_id AND User_Type.id = User.type_id
		ORDER BY User.id";
		
	$result = mysql_query($query) or die('Query failed: ' . mysql_error(). $query);
	
	$users = array();
	
	while($row = mysql_fetch_array($result, MYSQL_BOTH)) {
		$users[] = array(
			'id' => $row['id'],
			'name' => $row['name'],
			'state' => $row['state'],
			'type' => $row['type']
		);
	}
	
	mysql_free_result($result);	
	
	// User types
	$query = "SELECT id, name FROM User_Type";
	$result = mysql_query($query) or die('Query failed: ' . mysql_error(). $query);	
	$types = array();
	
	while($row = mysql_fetch_array($result, MYSQL_BOTH)) {
		$types[] = array(
			'id' => $row['id'],
			'name' => $row['name']
		);
	}
	
	mysql_free_result($result);
	mysql_close($link);

	$TBS->LoadTemplate("templates/usersList.html");
	$TBS->MergeBlock('type', $types);
	$TBS->MergeBlock('user', $users);
	$TBS->Show();
?>
