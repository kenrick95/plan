<?php
require("config.php");

$term = isset($_REQUEST['term']) ? trim($_REQUEST['term']) : '';
$term = preg_replace("/\s+/", "", $term);
// if (strlen($term) < 2) $term = '';

$return = array();
$data = json_decode(file_get_contents("data/parsed/json/". $year . "_" . $semester . "_course_list.json"), true);
foreach($data as $entry) {
    $text = $entry["code"] . ": " . $entry["name"];
    //if (stripos(preg_replace("/\s+/", "", $text), $term) !== false) {
        array_push($return, array(
            "id" => $entry["code"],
            "label" => $text,
            "value" => $entry["code"],
            "flag" => 0
            ));
    //}
}
echo json_encode($return);
?>
