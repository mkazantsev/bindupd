<?php
	require_once("classes/Config.php");
	
	if (isset($this)) {
    	$TBS =& $this;
	} else {
    	include_once('tbs_class.php');
	    $TBS = new clsTinyButStrong;
	}

	// load properties
	$configFileName = "/var/named/named.conf.upd";
	$path = "/var/named/";
	
	$TBS->LoadTemplate("templates/zonesList.html");
	
	try {
		$cfg = new Config($configFileName, $path);

		$zones = $cfg->getZones();
		$i = 0;
		$zones_str = array();
		foreach($zones as $zone) {
			$zones_str[] = array ('id' => $i,
				'type' => $zone->getType(),
				'name' => $zone->getName(),
				'filename' => $zone->getFileName()
			);
			$i++;
		}
		$TBS->MergeBlock('list', $zones_str);
		$TBS->Show();
	} catch(InvalidArgumentException $iae) {
		echo $iae->getMessage()."\n";
	} catch (IOException $ioe) {
		echo $ioe->getMessage()."\n";
	} catch (Exception $e) {
		echo $e->getMessage()."\n";
		echo "Unable to work with config\n";
	}
?>