<?php

if (isset($this)) {
   	$TBS =& $this;
} else {
   	/*
   	include_once('tbs/tbs_class_php5.php');
    $TBS = new clsTinyButStrong;
    */
    exit(0);
}

global $enabled;
global $logged;
global $message;

$message = "";

if (!$logged) {
	$message = "Login or password is incorrect";
} else {
	$message = "Account is disabled";
}

$TBS->LoadTemplate("templates/error.html");
$TBS->Show();

?>