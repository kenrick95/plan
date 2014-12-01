<?php
# Get the database
$database_course = json_decode(file_get_contents("../parsed_data_json/2014_2_data.json"), true);
$database_exam = json_decode(file_get_contents("../parsed_data_json/2014_2_exam_data.json"), true);

/* ---------------------------------------------------------------------------------------------- */

$times = array( "0830", "0900", "0930", "1000", "1030", "1100", "1130", "1200", "1230", "1300", "1330",
                "1400", "1430", "1500", "1530", "1600", "1630", "1700", "1730", "1800", "1830", "1900",
                "1930", "2000", "2030", "2100", "2130", "2200", "2230", "2300");

# STRUCTURE FOR TIMETABLE
$timetable = array(
    "monday" => $times, 
    "tuesday" => $times,
    "wednesday" => $times,
    "thursday" => $times,
    "friday" => $times,
    "saturday" => $times
);

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

function generate_timetable ($input_courses) {
    global $database_course, $timetable;
    $temp_timetable = $timetable; // A copy from $timetable structure
    $result = array();
    
    foreach ($input_courses as $course_id) {
        $course_detail = get_course_details($course_id);
        
        # Input error -> Course ID not found
        if ($course_detail === false) {
            return false;
        }
        
        
    }
    
    return $result;
}

function get_course_details ($course_id) {
    global $database_course;
    return $database_course[$course_id];
}
?>