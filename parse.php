<?php
# TODO clean data
$raw_data = file_get_contents("2014_2.html");

$raw_data = str_replace("<hr size=\"2\">", "", $raw_data);
$raw_data = str_replace("<hr>", "", $raw_data);
$raw_data = str_replace("<br>", "", $raw_data);
$raw_data = str_replace("&nbsp;", "", $raw_data);
# print_r($raw_data);

$data =  new SimpleXMLElement($raw_data);
$data = $data->body->center;
$super_data = array();
foreach ($data->table as $course) {
    if ($course->tbody->tr[0]->td[0]->b !== null) { // course
        $course_code = $course->tbody->tr[0]->td[0]->b->font[0];
        $course_name = $course->tbody->tr[0]->td[1]->b->font[0];
        $course_au   = $course->tbody->tr[0]->td[2]->b->font[0];
    } else { // index of the course
        $i = 0;
        foreach ($course->tbody->tr as $index) {
            if ($i == 0) {
                $i++;
                continue; // skip 
            }

            $index_number = $index->td[0]->b;
            // this will be very dirty
            // 1 course only got 1 table for all index
            // 1 index consists of multiple rows, for different types
            // index starts with td[0] as number, otherwise empty
            // see 2014_2_data_1006_index.txt
            $index_number = $index->td[0]->b;

            $i++;
        }
        $course_index   = $course->tbody->tr;
        array_push($super_data, array("code" => $course_code,
            "name" => $course_name,
            "au" => $course_au,
            "index" => $course_index));
    }


}

file_put_contents('2014_2_data.txt', print_r($super_data, true));

?>