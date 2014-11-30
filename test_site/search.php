<?php
$term = isset($_GET['term']) ? trim($_GET['term']) : '';
$term = preg_replace("/\s+/", "", $term);
if (strlen($term) < 2) $term = '';


$return = array();
$data = json_decode(file_get_contents("../parsed_data_json/2014_2_course_list.json"), true);
foreach($data as $entry) {
    $text = $entry["code"] . ": " . $entry["name"];
    if (stripos(preg_replace("/\s+/", "", $text), $term) !== false) {
        array_push($return, array(
            "id" => $entry["code"],
            "label" => $text,
            "value" => $entry["code"],
            ));
    }
}
echo json_encode($return);
?>