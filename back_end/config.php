<?php
ini_set('memory_limit', '512M');
error_reporting(0);

$year = isset($_REQUEST['year']) ? intval($_REQUEST['year']) : 2018;
$semester = isset($_REQUEST['semester']) ? intval($_REQUEST['semester']) : 2;
date_default_timezone_set("UTC");
