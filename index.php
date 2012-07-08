HUUUI

<?php

include_once("tbs_class_php5.php");

$enabled = true;

$TBS = new clsTinyButStrong();
$TBS->LoadTemplate("templates/main.html");
$TBS->Show();

?>