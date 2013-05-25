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

global $messages, $username, $password;

$TBS->LoadTemplate("templates/login.html");
$TBS->MergeBlock('blk', $messages);
$TBS->Show();
?>