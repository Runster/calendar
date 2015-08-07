<?php
require_once "../calendar.php";

$month = 0;

if (isset($_GET["month"]) && is_numeric($_GET["month"])) {
	$month = $_GET["month"];
}

$calendar = new Calendar($month);

if (isset($primaryConfigurationFile) && $primaryConfigurationFile != "") {
	$calendar->loadConfigFile($primaryConfigurationFile);
}

echo $calendar->output();
