<?php
$settings['cookiefile'] = "cookies.tmp";

function httpRequest($url, $post="") {
    global $settings;

    $ch = curl_init();
    //Change the user agent below suitably
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0');
    curl_setopt($ch, CURLOPT_URL, ($url));
    curl_setopt( $ch, CURLOPT_ENCODING, "UTF-8" );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt ($ch, CURLOPT_COOKIEFILE, $settings['cookiefile']);
    curl_setopt ($ch, CURLOPT_COOKIEJAR, $settings['cookiefile']);

    /*****************************************************************
    //   NOTE: THIS IS A QUICK FIX, BUT PLEASE REALLY FIX
    //   SSL certificate problem: unable to get local issuer certificate
    //   READ http://www.saotn.org/stop-turning-off-curlopt_ssl_verifypeer-and-fix-your-php-config/
    //   THANK YOU
    // */
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSLVERSION, 3);


    if (!empty($post)) curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
    //UNCOMMENT TO DEBUG TO output.tmp
    //curl_setopt($ch, CURLOPT_VERBOSE, true); // Display communication with server
    //$fp = fopen("output.tmp", "w");
    //curl_setopt($ch, CURLOPT_STDERR, $fp); // Display communication with server
    
    $xml = curl_exec($ch);
    
    if (!$xml) {
        throw new Exception("Error getting data from server ($url): " . curl_error($ch));
    }

    curl_close($ch);
    
    return $xml;
}
try {
    if (empty($_REQUEST['year'])) {
        throw new Exception("Year is empty");
    }
    if (empty($_REQUEST['semester'])) {
        throw new Exception("Semester is empty");
    }
    if (empty($_REQUEST['plan_no'])) {
        if ($_REQUEST['semester'] == 2) $plan_no = 4;
            else
        throw new Exception("plan_no is empty. Get it manually from https://wis.ntu.edu.sg/webexe/owa/exam_timetable_und.main");
    }
    $year = $_REQUEST['year'];
    $semester = $_REQUEST['semester'];
    
    if ($_REQUEST['semester'] == 2) $plan_no = 4;
    else $plan_no = $_REQUEST['plan_no'];

    ### Course data
    $request['r_search_type'] = 'F';
    $request['boption'] = 'Search';
    $request['acadsem'] = $year . ';' . $semester;
    $request['r_course_yr'] = '';
    $request['r_subj_code'] = '';
    $request['staff_access'] = 'false';

    $response = httpRequest("http://wish.wis.ntu.edu.sg/webexe/owa/AUS_SCHEDULE.main_display1", $request);
    file_put_contents("data/raw/". $year . "_" . $semester . ".html", $response);


    ### Exam data
    unset($request);
    $request['p_exam_dt'] = '';
    $request['p_start_time'] = '';
    $request['p_dept'] = '';
    $request['p_subj'] = '';
    $request['p_venue'] = '';
    $request['p_plan_no'] = $plan_no;
    $request['p_exam_yr'] = $year;
    $request['p_semester'] = $semester;
    $request['academic_session'] = 'Semester '. $semester .' Academic Year '.$year .'-'.($year + 1);
    $request['boption'] = 'Next';

    $response = httpRequest("http://wis.ntu.edu.sg/webexe/owa/exam_timetable_und.get_detail", $request);

    file_put_contents("data/raw/". $year . "_" . $semester . "_exam.html", $response);

    echo "OK";
} catch (Exception $e) {
    die ($e->getMessage());
}