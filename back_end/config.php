<?php
ini_set('memory_limit', '512M');
error_reporting(0);

$year = isset($_REQUEST['year']) ? intval($_REQUEST['year']) : date("Y");
$semester = isset($_REQUEST['semester']) ? intval($_REQUEST['semester']) : date("n") <= 6 ? 1 : 2;
date_default_timezone_set("UTC");
