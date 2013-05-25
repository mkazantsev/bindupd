<?php
	require_once("classes/Config.php");

	// Configuration loading
	include_once("config.inc.php");
	global $configFileName, $path;
	global $mysql_host, $mysql_user, $mysql_password, $mysql_db;

	// Subtemplating
	if (isset($this)) {
    	$TBS =& $this;
	} else {
	    exit(0);
	}

	include_once("tbs/tbs_plugin_html.php");
	$TBS->PlugIn(TBS_INSTALL,TBS_HTML);
	
	// index variables
	global $action, $zone, $admin;

	if ($admin != 1)
		exit(0);

	// current positions
	global $cur_op, $cur_user;
	$cur_op = 0;
	$cur_user = 0;

	// sort mapping
	$sort = 4;
	$sort_mapping = array(
		1 => 'Operation.id',
		2 => 'Operation_Type.name',
		3 => 'User.name',
		4 => 'date',
		5 => 'old_value',
		6 => 'new_value'
	);

	// filtering variable
	$filter = "";

	$TBS->LoadTemplate("templates/operationsList.html");

	$link = mysql_connect($mysql_host, $mysql_user, $mysql_password)
		or die("Could not connect: ".mysql_error());
	mysql_select_db($mysql_db) or die("Could not select database");

	// Operation types
	$op_types = array();
	$query = "SELECT id, name FROM Operation_Type";
	$result = mysql_query($query) or die('Query failed: ' . mysql_error(). $query);
	while ($row = mysql_fetch_array($result, MYSQL_BOTH)) {
		$op_types[] = array(
			'id' => $row[0],
			'name' => $row[1]
		);
	}
	$cur_op = $op_types[0]['id'];

	// Users
	$users = array();
	$query = "SELECT id, name FROM User";
	$result = mysql_query($query) or die('Query failed: ' . mysql_error(). $query);
	while ($row = mysql_fetch_array($result, MYSQL_BOTH)) {
		$users[] = array(
			'id' => $row[0],
			'name' => $row[1]
		);
	}
	$cur_user = $users[0]['id'];

	// Actions if there are some
	if (isset($action) && $action && count($action) > 0) {
		// Sorting
		if (isset($action[0])) {
			foreach ($sort_mapping as $key => $value) {
				if ($key == $action[0]) {
					$sort = $key;
					break;
				}
			}
		}

		// Type filtering
		if (isset($action[1])) {
			foreach ($op_types as $type) {
				if ($type['id'] == $action[1]) {
					$cur_op = $type['id'];
					$filter .= " AND Operation.type_id = ".$type['id'];
					break;
				}
			}
		}

		// User filtering
		if (isset($action[2])) {
			foreach ($users as $user) {
				if ($user['id'] == $action[2]) {	
					$cur_user = $user['id'];
					$filter .= " AND Operation.user_id = ".$user['id'];
					break;
				}
			}
		}
	}


	// Operations list
	$ops = array();
	$query = "SELECT Operation.id AS id,
		Operation_Type.name AS type,
		User.name AS user,
		Operation.date AS date,
		old_value AS oldValue,
		new_value AS newValue FROM Operation, Operation_Type, User
		WHERE Operation.type_id = Operation_Type.id
		AND Operation.user_id = User.id";

	// Filtering included
	$query .= $filter;

	// Sorting
	$query .= " ORDER BY ".$sort_mapping[$sort];

	$result = mysql_query($query) or die('Query failed: ' . mysql_error(). $query);
	while ($row = mysql_fetch_array($result, MYSQL_BOTH)) {
		$ops[] = array(
			'id' => $row['id'],
			'type' => $row['type'],
			'user' => $row['user'],
			'date' => $row['date'],
			'oldValue' => $row['oldValue'],
			'newValue' => $row['newValue']
		);
	}

	$TBS->MergeBlock('optype', $op_types);
	$TBS->MergeBlock('user', $users);
	$TBS->MergeBlock('list', $ops);
	$TBS->Show();

	mysql_close($link);
?>
