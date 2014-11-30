<?php
    # Get the string of courses from the form and split it to an array
    $input_courses = explode(" ", $_POST["courses"]);

    if (isset($input_courses)) {
        echo "DATA IS RECEIVED!! ";
        var_dump($input_courses);
    }

    # Get the database
	$database_course = json_decode(file_get_contents("../parsed_data/2014_2_data.json"), true);
	$database_exam = json_decode(file_get_contents("../parsed_data/2014_2_exam_data.json"), true);

    # Check exam scheduler first, if there is a clash -> break, tell the user it is impossible (which course is the problem)
?>