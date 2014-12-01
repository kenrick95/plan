<?php
# Get the database
$database_course = json_decode(file_get_contents("../parsed_data_json/2014_2_data.json"), true);
$database_exam = json_decode(file_get_contents("../parsed_data_json/2014_2_exam_data.json"), true);

/* ---------------------------------------------------------------------------------------------- */

# make times as key at MON, TUE, etc.
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

$all_timetable = array();

/* ---------------------------------------------------------------------------------------------- */

# Get the string of courses from the form and split it to an array
$input_courses = explode(",", preg_replace("/\s+/", "", strtoupper($_POST["courses"])));


# Check sent data
if (isset($input_courses)) {
    $len = count($input_courses);
    for ($i = 0; $i < $len; $i++) {
        if ($input_courses[$i] === "") {
            unset($input_courses[$i]);
        }
    }

    $result = array("validation_result" => validate_input($input_courses, $database_exam));
    if ($result["validation_result"]) {
        $result["exam_schedule_validation"] = check_exam_schedule($input_courses);
        $result["exam_schedule"] = $exam_schedule;
    }
    echo json_encode($result);
}

/* ---------------------------------------------------------------------------------------------- */

# Check whether it is a valid course code
function validate_input ($input_courses, $database_exam) {
    foreach ($input_courses as $course) {
        if (!array_key_exists($course, $database_exam)) {
            return false;
        }
    }
    
    return true;
}


# If there is a clash, stop it there
function check_exam_schedule ($input_courses) {
    global $database_exam, $exam_schedule;
    
    foreach ($input_courses as $course) {
        $exam = get_exam_details($course, $database_exam);
        $exam_date = $exam["date"];
        $exam_time = $exam["time"];
        
        if (isset($exam_schedule[$exam_date][$exam_time])) {
            return false;
        } else {
            $exam_schedule[$exam_date][$exam_time] = $exam;
        }
    }
    
    return true;
}


# Get exam details based on the course ID
function get_exam_details ($course_id, $database_exam) {
    return $database_exam[$course_id];
}

/* ---------------------------------------------------------------------------------------------- */

$temp_timetable = $timetable;
function generate_timetable ($input_courses, $temp_timetable) {
    global $database_course, $timetable, $all_timetable;
    
    # One timetable obtained
    if (count($input_courses) == 0) {
        array_push($all_timetable, $temp_timetable);
        return;
    }
    
    /*
        Structure to save in the timetable:
        - Course ID
        - Index
        - The lecture / tutorial / lab info
    */
    
    $course_id = $input_courses[0];
    $course_detail = get_course_details($course_id);
    $course_indices = $course_detail[index][0];

    # Input error -> Course ID not found
    if ($course_detail === false) {
        return false;
    }

    foreach ($course_indices as $index) {
        $index_number = $index["index_number"];
        $index_details = $index["details"];

        foreach ($index_details as $detail) {
            $start_time = $detail["time"]["start"];
            $end_time = $detail["time"]["end"];
            $day = $detail["day"];

            # Setting up details to a timetable slot
            if (!isset($temp_timetable[$day][$start_time])) {                    
                $clash = check_clash($start_time, $end_time, $temp_timetable);

                # Clash == move to the next index
                if ($clash) {
                    continue;
                } else {
                    $data = array("id" => $course_id, "index" => $index_number, "details" => $detail);
                    $temp_timetable = assign_time_slots($day, $start_time, $end_time, $data, $temp_timetable);
                }
            } 
            # If there is already one other record
            else {
                $temp_timetable_keys = array_keys($temp_timetable);
                $i = array_search($start_time, $temp_timetable_keys);
                $key = $temp_timetable_keys[$i];

                // IF there is already one record inside that time slot, check whether that record also starts at the same time
                // as $start_time --> if YES, then a clash, move to the next index
                if ($temp_timetable[$day][$key][0]["details"]["time"]["start"] === $start_time) {
                    continue;
                } else if ($temp_timetable[$day][$key][0]["details"]["time"]["end"] === $start_time) {
                    $clash = check_clash($start_time, $end_time, $temp_timetable);
                    if ($clash) {
                        continue;
                    } else {   
                        $temp_timetable = assign_time_slots($day, $start_time, $end_time, $data, $temp_timetable);
                    }
                }
            }

            // RECURSION HERE -> delete the course code which is just processed -> for termination condition
            // ...
            // BACKTRACK HERE
            // ...

        }
    }
}

function get_course_details ($course_id) {
    global $database_course;
    return $database_course[$course_id];
}

# Check whether all time from start to end is available
function check_clash($day, $start_time, $end_time, $temp_timetable) {
    $temp_timetable_keys = array_keys($temp_timetable);
    $i = array_search($start_time, $temp_timetable_keys);
    $key = $temp_timetable_keys[$i];
    
    while ($key !== $end_time) {
        if (isset($temp_timetable[$day][$key])) {
            return true;
        } else {
            $i++;
            $key = $temp_timetable_keys[$i];
        }
    }
    
    return false;
}

# Assign slots from start_time to end_time
function assign_time_slots($day, $start_time, $end_time, $data, $temp_timetable) {
    $temp_timetable_keys = array_keys($temp_timetable);
    $i = array_search($start_time, $temp_timetable_keys);
    $key = $temp_timetable_keys[$i];
    
    while ($key !== $end_time) {
        # 0 <= count <= 1 
        $count = count($temp_timetable[$day][$key]);
        $temp_timetable[$day][$key][$count] = $data;
    }
    
    return $temp_timetable;
}
?>