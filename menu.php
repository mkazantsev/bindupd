<?php

if (isset($this)) {
   	$TBS =& $this;
} else {
	/*
   	include_once('tbs_class_php5.php');
    $TBS = new clsTinyButStrong;
    */
    exit(0);
}

global $admin;

$TBS->LoadTemplate("templates/menu.html");
$TBS->Show();
?>