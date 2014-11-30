<?php
    # Get the database
    $database_course = json_decode(file_get_contents("../parsed_data_json/2014_2_data.json"), true);
    $database_exam = json_decode(file_get_contents("../parsed_data_json/2014_2_exam_data.json"), true);

    # Get the string of courses from the form and split it to an array
    $input_courses = explode(" ", $_POST["courses"]);

    # STRUCTURE FOR TIMETABLE
    $timetable = array(
        "monday" => array(), 
        "tuesday" => array(), 
        "wednesday" => array(), 
        "thursday" => array(), 
        "friday" => array()
    );

    # STRUCTURE FOR EXAM SCHEDULE
    # Need to change the date for every semester
    $exam_schedule = array(
        "20 April 2015" => array(
            "9.00 am",
            "11.00 am",
            "1.00 pm",
            "5.00 pm"
        ),
        "21 April 2015" => array(
            "9.00 am",
            "11.00 am",
            "1.00 pm",
            "5.00 pm"
        ),
        "22 April 2015" => array(
            "9.00 am",
            "11.00 am",
            "1.00 pm",
            "5.00 pm"
        ),
        "23 April 2015" => array(
            "9.00 am",
            "11.00 am",
            "1.00 pm",
            "5.00 pm"
        ),
        "24 April 2015" => array(
            "9.00 am",
            "2.30 pm"
        ),
        "27 April 2015" => array(
            "9.00 am",
            "11.00 am",
            "1.00 pm",
            "5.00 pm"
        ),
        "28 April 2015" => array(
            "9.00 am",
            "11.00 am",
            "1.00 pm",
            "5.00 pm"
        ),
        "29 April 2015" => array(
            "9.00 am",
            "11.00 am",
            "1.00 pm",
            "5.00 pm"
        ),
        "30 April 2015" => array(
            "9.00 am",
            "11.00 am",
            "1.00 pm",
            "5.00 pm"
        ),  
        "1 May 2015" => array(
            "9.00 am",
            "2.30 pm"
        ),
        "4 May 2015" => array(
            "9.00 am",
            "11.00 am",
            "1.00 pm",
            "5.00 pm"
        ),
        "5 May 2015" => array(
            "9.00 am",
            "11.00 am",
            "1.00 pm",
            "5.00 pm"
        ),
        "6 May 2015" => array(
            "9.00 am",
            "11.00 am",
            "1.00 pm",
            "5.00 pm"
        ),
        "7 May 2015" => array(
            "9.00 am",
            "11.00 am",
            "1.00 pm",
            "5.00 pm"
        ),
        "8 May 2015" => array(
            "9.00 am",
            "2.30 pm"
        ),
    );

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