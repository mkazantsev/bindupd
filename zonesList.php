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

	global $action;

	include_once("tbs/tbs_plugin_html.php");
	$TBS->PlugIn(TBS_INSTALL,TBS_HTML);

	$TBS->LoadTemplate("templates/zonesList.html");

	$link = mysql_connect($mysql_host, $mysql_user, $mysql_password)
		or die("Could not connect: ".mysql_error());
	mysql_select_db($mysql_db) or die("Could not select database");

	try {
		$cfg = new Config($configFileName, $path);

		// Actions if there are some
		if (isset($action) && $action && count($action) > 0) {

			global $userid;
			$optype = array();

			// What are optype numbers?
			$query = "SELECT id, name FROM Operation_Type";
			$result = mysql_query($query) or die('Query failed: ' . mysql_error(). $query);
			while ($row = mysql_fetch_array($result, MYSQL_BOTH)) {
				if ($row['name'] == "delete")
					$optype['delete'] = $row['id'];
				if ($row['name'] == "add")
					$optype['add'] = $row['id'];
				if ($row['name'] == "edit")
					$optype['edit'] = $row['id'];
			}
			mysql_free_result($result);

			if ($action[0] == "addzone") {
				// Add zone to config and save it
				$z = new Zone(Zone::$types[$action[1]], $action[2], $action[3],
					$path, true);
				$cfg->addZone($z);
				$cfg->save();

				// Add new operation record in DB
				$today = date("Y-m-d H:i:s");
				$z_str = $z->getType()." ".$z->getName()." ".$z->getFileName();
				$query = "INSERT INTO Operation (type_id, user_id, date, old_value, new_value)
					VALUES(".$optype['add'].", ".$userid.", '".$today."', '', '".$z_str."')";
				$result = mysql_query($query) or die('Query failed: ' . mysql_error(). $query);
			}
			if ($action[0] == "deletezone") {
				$z = $cfg->getZone($action[1]);
				$cfg->removeZone($action[1]);
				$cfg->save();
				
				// Add new operation record in DB
				$today = date("Y-m-d H:i:s");
				$z_str = $z->getType()." ".$z->getName()." ".$z->getFileName();
				$query = "INSERT INTO Operation (type_id, user_id, date, old_value, new_value)
					VALUES(".$optype['delete'].", ".$userid.", '".$today."', '".$z_str."', '')";
				$result = mysql_query($query) or die('Query failed: ' . mysql_error(). $query);
			}
			if ($action[0] == "editzone") {
			foreach ($action[1] as $key => $value) {
				$z =& $cfg->getZone($key);
				$z1_str = $z->getType()." ".$z->getName()." ".$z->getFileName();
				$z->setType(Zone::$types[$action[2][$key]]);
				$z->setName($action[3][$key]);
				$z->setFileName($action[4][$key]);
				$z2_str = $z->getType()." ".$z->getName()." ".$z->getFileName();

				$cfg->save();

				// Add new operation record in DB
				$today = date("Y-m-d H:i:s");
				$query = "INSERT INTO Operation (type_id, user_id, date, old_value, new_value)
					VALUES(".$optype['delete'].", ".$userid.", '".$today."', '".$z1_str."', '".$z2_str."')";
				$result = mysql_query($query) or die('Query failed: ' . mysql_error(). $query);
			}
			}
		}
		
		mysql_close($link);

		$zones = $cfg->getZones();
		$i = 0;
		$zones_str = array();
		foreach($zones as $zone) {
			$zones_str[] = array ('id' => $i,
				'type' => $zone->getType(),
				'typeID' => $zone->getIntType(),
				'name' => $zone->getName(),
				'filename' => $zone->getFileName()
			);
			$i++;
		}
		$types = array();
		foreach (Zone::$types as $key => $value) {
			$types[] = array(
				'id' => $key,
				'name' => $value
			);
		}
		$TBS->MergeBlock('type,type2', $types);
		$TBS->MergeBlock('list,list2', $zones_str);
		$TBS->Show();
	} catch(InvalidArgumentException $iae) {
		echo $iae->getMessage()."\n";
		echo 'Invalid data';
	} catch (IOException $ioe) {
		echo $ioe->getMessage()."\n";
		echo 'Invalid input/output';
	} catch (Exception $e) {
		echo $e->getMessage()."\n";
		echo "Unable to work with config\n";
	}
?>
