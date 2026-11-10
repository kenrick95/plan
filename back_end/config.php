<?php
ini_set('memory_limit', '512M');
// Keep CLI update failures visible while preserving the web UI's error policy.
error_reporting(PHP_SAPI === 'cli' ? E_ALL : 0);

$year = isset($_REQUEST['year']) && intval($_REQUEST['year']) > 0 ? intval($_REQUEST['year']) : 2026;
$semester = isset($_REQUEST['semester']) && intval($_REQUEST['semester']) > 0 ? intval($_REQUEST['semester']) : 1;
date_default_timezone_set("UTC");
