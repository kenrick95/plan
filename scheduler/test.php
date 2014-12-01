<?php

$times = array( "0830", "0900", "0930", "1000", "1030", "1100", "1130", "1200", "1230", "1300", "1330",
                "1400", "1430", "1500", "1530", "1600", "1630", "1700", "1730", "1800", "1830", "1900",
                "1930", "2000", "2030", "2100", "2130", "2200", "2230", "2300");

# STRUCTURE FOR TIMETABLE
$timetable = array(
    "MON" => $times, 
    "TUE" => $times,
    "WED" => $times,
    "THU" => $times,
    "FRI" => $times,
    "SAT" => $times
    );

$times = null;

var_dump($timetable);
$member_time_start = "0830";
$member_time_end = "1500";
var_dump((intval($member_time_end) - intval($member_time_start)));
?>