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

	$TBS->LoadTemplate("templates/directivesList.html");

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

			if ($action[0] == "adddirective") {
				// Add directive to a zone, then zone to config and save it
				$d_str = Directive::$types[$action[1]]." ".$action[2];
				$d = Directive::Directive($d_str);
				$z->addDirective($d);
				$cfg->save();

				// Add new operation record in DB
				$today = date("Y-m-d H:i:s");
				$query = "INSERT INTO Operation (type_id, user_id, date, old_value, new_value)
					VALUES(".$optype['add'].", ".$userid.", '".$today."', '', 'Zone ".$zone.": ".$d_str."')";
				$result = mysql_query($query) or die('Query failed: ' . mysql_error(). $query);
			}
			if ($action[0] == "deletedirective") {
				$d = $z->getDirective($action[1]);
				$z->removeDirective($action[1]);
				$cfg->save();
				
				// Add new operation record in DB
				$today = date("Y-m-d H:i:s");
				$d_str = "Zone ".$zone.": ".$d->toString(" ");
				$query = "INSERT INTO Operation (type_id, user_id, date, old_value, new_value)
					VALUES(".$optype['delete'].", ".$userid.", '".$today."', '".$d_str."', '')";
				$result = mysql_query($query) or die('Query failed: ' . mysql_error(). $query);
			}
			if ($action[0] == "editdirective") {
			foreach ($action[1] as $key => $value) {
				$d =& $z->getDirective($key);
				$d1_str = "Zone ".$zone.": ".$d->toString(" ");
				$d->setType(Directive::$types[$action[2][$key]]);
				$d->setData($action[3][$key]);
				$r2_str = "Zone ".$zone.": ".$d->toString(" ");
				
				$cfg->save();

				// Add new operation record in DB
				$today = date("Y-m-d H:i:s");
				$query = "INSERT INTO Operation (type_id, user_id, date, old_value, new_value)
					VALUES(".$optype['edit'].", ".$userid.", '".$today."', '".$d1_str."', '".$d2_str."')";
				$result = mysql_query($query) or die('Query failed: ' . mysql_error(). $query);
			}
			}
		
			mysql_close($link);
		}


		$directives = $z->getDirectives();
		$i = 0;
		$directives_str = array();
		foreach($directives as $directive) {
			$directives_str[] = array ('id' => $i,
				'typeID' => $directive->getIntType(),
				'type' => $directive->getType(),
				'data' => $directive->getData()
			);
			$i++;
		}
		$types = array();
		foreach (Directive::$types as $key => $value) {
			$types[] = array(
				'id' => $key,
				'name' => $value
			);
		}
		$TBS->MergeBlock('type,type2', $types);
		$TBS->MergeBlock('list,list2', $directives_str);
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
