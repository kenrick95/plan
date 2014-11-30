<?php
# Get the database
$database_course = json_decode(file_get_contents("../parsed_data_json/2014_2_data.json"), true);
$database_exam = json_decode(file_get_contents("../parsed_data_json/2014_2_exam_data.json"), true);

/* ---------------------------------------------------------------------------------------------- */

# STRUCTURE FOR TIMETABLE
$timetable = array(
    "monday" => array(), 
    "tuesday" => array(), 
    "wednesday" => array(), 
    "thursday" => array(), 
    "friday" => array()
);

/* ---------------------------------------------------------------------------------------------- */

# Get the string of courses from the form and split it to an array
$input_courses = explode(" ", $_POST["courses"]);

# Check sent data
if (isset($input_courses)) {
    echo "DATA IS RECEIVED!! ";
    var_dump($input_courses);

    $result = array("validation_result" => validateInput($input_courses, $database_exam));
    if ($result["validation_result"]) {
        $result["exam_schedule_validation"] = checkExamSchedule($input_courses);
    }

    var_dump($result);
}


# Check whether it is a valid course code
function validateInput ($input_courses, $database_exam) {
    foreach ($input_courses as $course) {
        if (!array_key_exists($course, $database_exam)) {
            return false;
        }
    }
    
    return true;
}


# If there is a clash, stop it there
function checkExamSchedule ($input_courses) {
    global $database_exam, $exam_schedule;
    
    foreach ($input_courses as $course) {
        $exam = getExamDetails($course, $database_exam);
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
function getExamDetails ($course_id, $database_exam) {
    return $database_exam[$course_id];
}
?>