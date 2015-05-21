<?php
$year = isset($_GET['year']) ? int($_GET['year']) : 2015;
$semester = isset($_GET['semester']) ? int($_GET['semester']) : 1;

$term = isset($_GET['term']) ? trim($_GET['term']) : '';
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