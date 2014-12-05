<?php
ini_set('memory_limit', '512M');
# Get the database
$database_course = json_decode(file_get_contents("../parsed_data_json/2014_2_data.json"), true);
$database_exam = json_decode(file_get_contents("../parsed_data_json/2014_2_exam_data.json"), true);

/* ---------------------------------------------------------------------------------------------- */

# make times as key at MON, TUE, etc.
$times = array( "0830" => array(),"0900" => array(),"0930" => array(),"1000" => array(),"1030" => array(),"1100" => array(),"1130" => array(),"1200" => array(),"1230" => array(),"1300" => array(),"1330" => array(),
                "1400" => array(),"1430" => array(),"1500" => array(),"1530" => array(),"1600" => array(),"1630" => array(),"1700" => array(),"1730" => array(),"1800" => array(),"1830" => array(),"1900" => array(),
                "1930" => array(),"2000" => array(),"2030" => array(),"2100" => array(),"2130" => array(),"2200" => array(),"2230" => array(),"2300" => array());

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
$input_courses = explode(",", preg_replace("/\s+/", "", strtoupper($_REQUEST["courses"])));

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
    
    generate_timetable($input_courses, $timetable);
    $result["timetable"] = $all_timetable;

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

/*
    Data to store in ONE SLOT:
    - Course ID
    - Index number
    - Flag
*/

# Generate ALL POSSIBLE timetables!
$temp_timetable = $timetable;
function generate_timetable ($input_courses, $temp_timetable) {
    global $database_course, $all_timetable;
    $original_timetable = $temp_timetable;
        
    # One solution is found
    if (count($input_courses) == 0) {
        array_push($all_timetable, $temp_timetable);
        
        /*
        if (count($all_timetable == 20)) {
            # flush here
            # Empty $all_timetable to prepare for the next 20 timetables
            $all_timetable = array();
        }
        */
        
        return;
    }
        
    # Data retrieval
    $course = $database_course[$input_courses[0]];
    $course_id = $input_courses[0];
    $indices = $course["index"]; # Contains all index of a subject
        
    # Checking of timetable (clash or not) for EACH AVAILABLE INDEX
    foreach ($indices as $index) {
        $index_no = $index["index_number"];
        $index_details = $index["details"];
        $skip = false;

        foreach ($index_details as $detail) {            
            # Check for clash, for each index detail (for each lecture, each tutorial in one index)
            $clash = check_clash($course_id, $detail, $temp_timetable);
            
            if ($clash) {
                $skip = true;
                break;
            }
            
            # Assign to timetable
            $temp_timetable = assign_course($course_id, $index_no, $detail, $temp_timetable);
        }
        
        # Skip the recursion as there is a clash in this index
        # Continue to the next index
        if ($skip) {
            $temp_timetable = $original_timetable;
            continue;
        }
        
        # Reduce to get termination condition later
        $popped = array_shift($input_courses);
        generate_timetable($input_courses, $temp_timetable); # Recursion
                
        # Backtracking
        $temp_timetable = $original_timetable;
        array_unshift($input_courses, $popped);
    }
}


# Check whether there is a clash
function check_clash ($course_id, $detail, $temp_timetable) {
    $start_time = $detail["time"]["start"];
    $end_time = $detail["time"]["end"];
    $duration = $detail["time"]["duration"];
    $day = $detail["day"];
    $week = $detail["flag"];
        
    $time_keys = array_keys($temp_timetable[$day]);
    $index = array_search($start_time, $time_keys);
    
    # duration * 2 -> how many slots 
    for ($i = 0; $i < $duration * 2; $i++) {
        if (count($temp_timetable[$day][$time_keys[$index]]) > 0) {
                        
            # Take the clash course from the timetable -> it must be index 0 (because at most there are only 2 entries)
            $clash_detail = $temp_timetable[$day][$time_keys[$index]][0]; # An array object containing the data structure
            $clash_flag = $clash_detail["flag"];
            
            # Consider it as a clash IF AND ONLY IF the clash happens because of a slot is already occupied by DIFFERENT COURSE!!
            if ($course_id !== $clash_detail["id"]) {
                # If the clash is for the whole semester
                if ($week === 0) return true; 
                if ($clash_flag === 0) return true;
            
                # If one is even and one is odd or the other way round
                if ($week === $clash_flag) return true;
            }
        }
        
        $index++;
    }
    
    return false;
}


# Assign course for each index detail one by one
function assign_course ($course_id, $index_no, $detail, $temp_timetable) {
    $data = array(
                "id" => $course_id,
                "index" => $index_no,
                "flag" => $detail["flag"],
                "type" => $detail["type"]
            );
    
    $start_time = $detail["time"]["start"];
    $end_time = $detail["time"]["end"];
    $duration = $detail["time"]["duration"];
    $day = $detail["day"];
    
    $time_keys = array_keys($temp_timetable[$day]);
    $index = array_search($start_time, $time_keys);
    
    # duration * 2 -> how many slots 
    for ($i = 0; $i < $duration * 2; $i++) {
        array_push($temp_timetable[$day][$time_keys[$index++]], $data);
    }
    
    return $temp_timetable;
}  
?>