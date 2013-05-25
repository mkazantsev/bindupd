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
	
	global $action, $zone;

	$TBS->LoadTemplate("templates/recordsList.html");

	try {		
		$cfg = new Config($configFileName, $path);
		$zones = $cfg->getZones();
		$z =& $cfg->getZone($zone);

		// Actions if there are some
		if (isset($action) && $action && count($action) > 0) {
		
			$link = mysql_connect($mysql_host, $mysql_user, $mysql_password)
				or die("Could not connect: ".mysql_error());
			mysql_select_db($mysql_db) or die("Could not select database");


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

			if ($action[0] == "addrecord") {
				// Add record to a zone, then zone to config and save it
				$r_str = $action[1]." ".$action[2]." ".Record::$types[$action[3]]." ".$action[4];
				$r = Record::Record($r_str);
				$z->addRecord($r);
				$cfg->save();

				// Add new operation record in DB
				$today = date("Y-m-d H:i:s");
				$query = "INSERT INTO Operation (type_id, user_id, date, old_value, new_value)
					VALUES(".$optype['add'].", ".$userid.", '".$today."', '', 'Zone ".$zone.": ".$r_str."')";
				$result = mysql_query($query) or die('Query failed: ' . mysql_error(). $query);
			}
			if ($action[0] == "deleterecord") {
				$r = $z->getRecord($action[1]);
				$z->removeRecord($action[1]);
				$cfg->save();
				
				// Add new operation record in DB
				$today = date("Y-m-d H:i:s");
				$r_str = "Zone ".$zone.": ".$r->toString(" ");
				$query = "INSERT INTO Operation (type_id, user_id, date, old_value, new_value)
					VALUES(".$optype['delete'].", ".$userid.", '".$today."', '".$r_str."', '')";
				$result = mysql_query($query) or die('Query failed: ' . mysql_error(). $query);
			}
			if ($action[0] == "editrecord") {
			foreach ($action[1] as $key => $value) {
				$r =& $z->getRecord($key);
				$r1_str = "Zone ".$zone.": ".$r->toString(" ");
				$r->setName($action[2][$key]);
				$r->setTTL($action[3][$key]);
				$r->setType(Record::$types[$action[4][$key]]);
				$r->setData($action[5][$key]);
				$r2_str = "Zone ".$zone.": ".$r->toString(" ");
				
				$cfg->save();

				// Add new operation record in DB
				$today = date("Y-m-d H:i:s");
				$query = "INSERT INTO Operation (type_id, user_id, date, old_value, new_value)
					VALUES(".$optype['edit'].", ".$userid.", '".$today."', '".$r1_str."', '".$r2_str."')";
				$result = mysql_query($query) or die('Query failed: ' . mysql_error(). $query);
			}
			}
		
			mysql_close($link);
		}

		$records = $z->getRecords();
		$i = 0;
		$records_str = array();
		foreach($records as $record) {
			$records_str[] = array ('id' => $i,
				'name' => $record->getName(),
				'ttl' => $record->getTTL(),
				'typeID' => $record->getIntType(),
				'type' => $record->getType(),
				'data' => $record->getData()
			);
			if ($records_str[$i]['ttl'] == -1)
				$records_str[$i]['ttl'] = "";
			$i++;
		}
		$types = array();
		foreach (Record::$types as $key => $value) {
			$types[] = array(
				'id' => $key,
				'name' => $value
			);
		}
		$TBS->MergeBlock('type,type2', $types);
		$TBS->MergeBlock('list,list2', $records_str);
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
