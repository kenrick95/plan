<?php
# DONE clean the data
$raw_data = file_get_contents("../raw_data/2014_2.html");

$raw_data = str_replace("<hr size=\"2\">", "", $raw_data);
$raw_data = str_replace("<hr>", "", $raw_data);
$raw_data = str_replace("<br>", "", $raw_data);
$raw_data = str_replace("&nbsp;", "", $raw_data);
$raw_data = preg_replace("/ +/", " ", $raw_data);
# print_r($raw_data);

function count_duration($start, $end) {
    $hour_start = (int) ($start / 100);
    $hour_end = (int) ($end / 100);
    $minute_start = ($start % 100);
    $minute_end = ($end % 100);
    if ($minute_end - $minute_start < 0) {
        return (double) ($hour_end - $hour_start - 1) + (double) ($minute_end - $minute_start + 60)  / 60.0;
    } else {
        return (double) ($hour_end - $hour_start) + (double) ($minute_end - $minute_start) / 60.0;
    }
    
}

$data =  new SimpleXMLElement($raw_data);
$data = $data->body->center;
$super_data = array();
foreach ($data->table as $course) {
    if ($course->tbody->tr[0]->td[0] !== null) { // course
        $course_code = (string) $course->tbody->tr[0]->td[0]->b->font[0];
        $course_name = (string) $course->tbody->tr[0]->td[1]->b->font[0];
        $course_au   = (string) $course->tbody->tr[0]->td[2]->b->font[0];
    } else { // index of the course
        
        $index_members = array();
        foreach ($course->tbody->tr as $index) {
            if ($index->td[0] == null) continue; // skip
            
            if (!empty($index->td[0]->b )) {
                if (isset($index_member)) {
                    array_push($index_members,array(
                        "index_number" => $index_number,
                        "details" => $index_member));
                    unset($index_member);
                }
                $index_number = (string) $index->td[0]->b;
                $index_member = array();
            }

            $member_type = (string) $index->td[1]->b;
            $member_group = (string) $index->td[2]->b;
            $member_day = (string) $index->td[3]->b;
            $member_time = (string) $index->td[4]->b;
            if (empty($member_time)) {
                $member_time_start = "";
                $member_time_end = "";
            } else {
                $member_time_start = explode("-", $member_time)[0];
                $member_time_end = explode("-", $member_time)[1];
                $member_time_duration = count_duration(intval($member_time_start),intval($member_time_end));
            }

            $member_location = (string) $index->td[5]->b;
            $member_remarks = (empty($index->td[6]->b)) ? "" : (string) $index->td[6]->b; // start on what week?
            array_push ($index_member, array(
                "type" => $member_type,
                "group" => $member_group,
                "day" => $member_day,
                "time" => array("full" => $member_time,
                    "start" => $member_time_start,
                    "end" => $member_time_end,
                    "duration" => $member_time_duration),
                "location" => $member_location,
                "remarks" => $member_remarks));

            //$index_number = $index->td[0]->b;
            // this will be very dirty
            // 1 course only got 1 table for all index
            // 1 index consists of multiple rows, for different types
            // index starts with td[0] as number, otherwise empty
            // see 2014_2_data_1006_index.txt
            //$index_number = $index->td[0]->b;

        }
        if (isset($index_member)) {
            array_push($index_members,array(
                "index_number" => $index_number,
                "details" => $index_member)); 
            unset($index_member);
        }
        //$course_index   = $course->tbody->tr;
        
        // Better format for searching
        $super_data[$course_code] = array("name" => $course_name,
                                          "au" => $course_au,
                                          "index" => $index_members
                                         );
        unset($index_members);
        
        /*
        array_push($super_data, array("code" => $course_code,
            "name" => $course_name,
            "au" => $course_au,
            "index" => $index_members));
        */
    }
}

file_put_contents('../parsed_data_text/2014_2_data.txt', print_r($super_data, true));
file_put_contents('../parsed_data_json/2014_2_data.json', json_encode($super_data));

echo "OK";
?>
