<?php
// BUG: HW0310, HW0210
// all major are shown, filter by major
// remove non-selected major before generate_timetable

ini_set('memory_limit', '512M');
error_reporting(E_ALL ^ E_WARNING);
$year = isset($_GET['year']) ? intval($_GET['year']) : 2015;
$semester = isset($_GET['semester']) ? intval($_GET['semester']) : 1;

# Get the database
$database_course = json_decode(file_get_contents("data/parsed/json/". $year . "_" . $semester . "_data.json"), true);
$database_exam = json_decode(file_get_contents("data/parsed/json/". $year . "_" . $semester . "_exam_data.json"), true);

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
$user_major= strtoupper($_REQUEST["major"]);

# Check sent data
if (isset($input_courses)) {
    $len = count($input_courses);
    for ($i = 0; $i < $len; $i++) {
        if ($input_courses[$i] === "") {
            unset($input_courses[$i]);
        }
    }

    $result = array("validation_result" => validate_input($input_courses, $database_course));
    if ($result["validation_result"]) {
        $result["exam_schedule_validation"] = check_exam_schedule($input_courses);
        $result["exam_schedule"] = $exam_schedule;
    }

    # Filter HW0210 / HW0310 based on the user major
    filter_HW0188_timetable($user_major);
    filter_HW0210_timetable($user_major);
    filter_HW0310_timetable($user_major);

    # Generate all possible timetables
    generate_timetable($input_courses, $timetable);
    $result["timetable"] = $all_timetable;

    echo json_encode($result);
}

/* ---------------------------------------------------------------------------------------------- */

# Check whether it is a valid course code
function validate_input ($input_courses, $database_course) {
    foreach ($input_courses as $course) {
        if (!array_key_exists($course, $database_course)) {
            return false;
        }
    }
    
    return true;
}


# If there is a clash, stop it there
function check_exam_schedule ($input_courses) {
    global $database_exam, $database_course, $exam_schedule;
    
    foreach ($input_courses as $course) {
        $exam = get_exam_details($course, $database_exam);
        if ($exam === -1) {
            $exam_date = -1;
            $exam_time = -1;

            $exam = [];
            $exam["au"] = trim($database_course[$course]['au']);;
            $exam["code"] = $course;
            $exam["date"] = -1;
            $exam["day"] = -1;
            $exam["duration"] = -1;
            $exam["end_time"] = -1;
            $exam["name"] = trim($database_course[$course]["name"]);
            $exam["time"] = -1;
            $exam_schedule[$course][$course] = $exam;
        } else {
            $exam_date = $exam["date"];
            $exam_time = $exam["time"];

            // parse time
            $hour = intval($exam["time"][0]);
            $minutes = intval($exam["time"][2] . $exam["time"][3]);
            if ($exam["time"][5] . $exam["time"][6] === "pm") {
                $hour += 12;
            }
            $exam["time"] = pad($hour) . pad($minutes);

            $time = ($hour * 60 + $minutes) + $exam["duration"] * 60;
            $hour = (int) ($time / 60);
            $minutes = (int) ($time % 60);

            $exam["end_time"] = pad($hour) . pad($minutes);
            $exam["au"]= trim($database_course[$course]['au']);
        
            if (isset($exam_schedule[$exam_date][$exam_time])) {
                return false;
            } else {
                $exam_schedule[$exam_date][$exam_time] = $exam;
            }
        }
    }
    
    return true;
}


# Get exam details based on the course ID
function get_exam_details ($course_id, $database_exam) {
    if (!array_key_exists($course_id, $database_exam)) {
        return -1;
    }

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
        // Don't store empty keys
        foreach ($temp_timetable as $day => $times) {
            foreach ($times as $time => $indices) {
                if (empty($indices))
                    unset($temp_timetable[$day][$time]);
            }
            if (empty($temp_timetable[$day]))
                unset($temp_timetable[$day]);
        }
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
        
        #echo "Index: " . $index_no . "Course: " . $course_id . "\n\n";
        
        foreach ($index_details as $detail) {            
            # Check for clash, for each index detail (for each lecture, each tutorial in one index)
            $clash = check_clash($course_id, $index_no, $detail, $temp_timetable);
            
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
function check_clash ($course_id, $index_no, $detail, $temp_timetable) {
    $start_time = $detail["time"]["start"];
    $end_time = $detail["time"]["end"];
    $duration = $detail["time"]["duration"];
    $day = $detail["day"];
    $week = $detail["flag"];
    
    // $week = remarks_to_weeks($detail["remarks"]);
        
    $time_keys = array_keys($temp_timetable[$day]);
    $index = array_search($start_time, $time_keys); # Iterator for each time slot in the temp_timetable
        
    # duration * 2 -> how many slots 
    for ($i = 0; $i < $duration * 2; $i++) {
        if (count($temp_timetable[$day][$time_keys[$index]]) > 0) {
            # In case there are 2 courses at that slot already!
            if (count($temp_timetable[$day][$time_keys[$index]]) >= 2) return true;
            
            # Take the clash course from the timetable -> it must be index 0 (because at most there are only 2 entries)
            $clash_detail = $temp_timetable[$day][$time_keys[$index]][0]; # An array object containing the data structure
            $clash_flag = $clash_detail["flag"];
            
            # Consider it as a clash IF AND ONLY IF the clash happens because of a slot is already occupied by DIFFERENT COURSE!!
            if ($course_id !== $clash_detail["id"]) {
                # If the clash is for the whole semester
                if ($week === 0) return true; 
                if ($clash_flag === 0) return true;            
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
                "type" => $detail["type"],
                "location" => $detail["location"],
                "group" => $detail["group"],
                "remarks" => $detail["remarks"]
            );
    
    $start_time = $detail["time"]["start"];
    $end_time = $detail["time"]["end"];
    $duration = $detail["time"]["duration"];
    $day = $detail["day"];
    
    $time_keys = array_keys($temp_timetable[$day]);
    $index = array_search($start_time, $time_keys);
    
    # duration * 2 -> how many slots 
    for ($i = 0; $i < $duration * 2; $i++) {
        # To skip if there is two same courses, same type, in the same slot -> e.g. BU8401 FOM
        if (count($temp_timetable[$day][$time_keys[$index]]) > 0 && $temp_timetable[$day][$time_keys[$index]][0]["id"] === $course_id) break;
        else array_push($temp_timetable[$day][$time_keys[$index]], $data);
        
        $index++;
    }
    
    return $temp_timetable;
}  

/* ---------------------------------------------------------------------------------------------- */
/* ---------------------------------------------------------------------------------------------- */

function filter_HW0188_timetable ($user_major) {
    global $database_course;
    $HW0188_unfiltered = $database_course["HW0188"]["index"];
    $HW0188_filtered = array();

    foreach ($HW0188_unfiltered as $i) {
        if (strpos($i["details"][0]["group"], $user_major) !== false) {
            array_push($HW0188_filtered, $i);
        }
    }

    $database_course["HW0188"]["index"] = $HW0188_filtered;
}

function filter_HW0210_timetable ($user_major) {
    global $database_course;
    $HW0210_unfiltered = $database_course["HW0210"]["index"];
    $HW0210_filtered = array();

    foreach ($HW0210_unfiltered as $i) {
        if (strpos($i["details"][0]["group"], $user_major) !== false) {
            array_push($HW0210_filtered, $i);
        }
    }

    $database_course["HW0210"]["index"] = $HW0210_filtered;
}

# After reading the data, SPECIAL CASE FOR CBE --> check only array index 2, instead of 0
# Only CBE has 3 entries per array, others only 1
function filter_HW0310_timetable ($user_major) {
    global $database_course;
    $HW0310_unfiltered = $database_course["HW0310"]["index"];
    $HW0310_filtered = array();

    // if (strcmp($user_major, "CBE") === 0) {
    //     $check_index = 2;
    // } else if (strcmp($user_major, "MAT") === 0) {

    if (strcmp($user_major, "MAT") === 0 || strcmp($user_major, "MAE") === 0) {
        $check_index = 0;
    } else {
        $check_index = 1;
    }

    foreach ($HW0310_unfiltered as $i) {
        // if (count($i["details"]) === $check_index + 1) {
            if (strpos($i["details"][$check_index]["group"], $user_major) !== false) {
                array_push($HW0310_filtered, $i);
            }
        // }
    }

    $database_course["HW0310"]["index"] = $HW0310_filtered;
}




/* ---------------------------------------------------------------------------------------------- */
// Helper function
function pad ($num) {
    if ($num < 10) return "0" . $num;
    return $num;
}

/**
 *  
 * @param  String $remarks      Remarks string
 * @return Array                Boolean array of size 13 (0-based), indicating whether course is held on week i or not
 */
function remarks_to_weeks ($remarks) {
    $ret = [];
    for ($i = 0; $i < 13; $i++) {
        $ret[$i] = false;
    }
    $start = stripos($remarks, "wk");
    $start += 2; // skip "wk"
    
    $cur_val = 0;
    $cur_val2 = 0;
    $range = false;
    for ($i = $start; $i < strlen($remarks); $i++) {
        if ('0' <= $remarks[$i] && $remarks[$i] <= '9') {
            if ($range)
                $cur_val2 = $cur_val2 * 10 + intval($remarks[$i]);
            else
                $cur_val = $cur_val * 10 + intval($remarks[$i]);
        } else if ($remarks[$i] === '-') {
            $range = true;
        } else if ($remarks[$i] === ',') {
            if ($range) {
                for ($wk = $cur_val; $wk <= $cur_val2; $wk++) {
                    $ret[$wk] = true;
                }
            } else {
                $ret[$cur_val] = true;
            }

            $cur_val = 0;
            $cur_val2 = 0;
            $range = false;
        }
    }
    // final
    if ($cur_val !== 0)
        if ($range) {
            for ($wk = $cur_val; $wk <= $cur_val2; $wk++) {
                $ret[$wk] = true;
            }
        } else {
            $ret[$cur_val] = true;
        }

    return $ret;
}

?>