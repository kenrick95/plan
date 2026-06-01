<?php
set_time_limit(60);
$settings['cookiefile'] = "cookies.tmp";

function httpRequest($url, $post="") {
    global $settings;

    $ch = curl_init();
    //Change the user agent below suitably
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:126.0) Gecko/20100101 Firefox/126.0');
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
    // curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
    // curl_setopt($ch, CURLOPT_SSLVERSION, 4);
    //curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'SSLv3');


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
function fetchPlanNo($year, $semester) {
    $target = sprintf("AY%d-%02d SEM %d", $year, ($year + 1) % 100, $semester);
    $html = httpRequest(
        "https://wis.ntu.edu.sg/webexe/owa/exam_timetable_und.MainSubmit",
        "p_opt=1&p_type=UE&bOption=Next"
    );
    // Match: value="NNN" /> ... AY2026-27 SEM 1
    if (!preg_match_all('/name="p_plan_no"\s+value="(\d+)"\s*\/>([^<]*)/i', $html, $matches)) {
        throw new Exception("Could not parse p_plan_no options from exam timetable page");
    }
    foreach ($matches[1] as $i => $value) {
        $label = strtoupper(trim($matches[2][$i]));
        if ($label === strtoupper($target)) {
            return $value;
        }
    }
    throw new Exception("Could not find p_plan_no for '$target'. Available options: " . implode(", ", array_map('trim', $matches[2])));
}

try {
    if (empty($_REQUEST['year'])) {
        throw new Exception("Year is empty");
    }
    if (empty($_REQUEST['semester'])) {
        throw new Exception("Semester is empty");
    }
    $year = $_REQUEST['year'];
    $semester = $_REQUEST['semester'];
    $plan_no = !empty($_REQUEST['plan_no']) && is_numeric($_REQUEST['plan_no']) ? $_REQUEST['plan_no'] : fetchPlanNo($year, $semester);


    if (empty($plan_no)) {
        throw new Exception("Plan number is empty");
    }

    ### Course data
    $request['r_search_type'] = 'F';
    $request['boption'] = 'Search';
    $request['acadsem'] = $year . ';' . $semester;
    #$request['r_course_yr'] = '';
    $request['r_subj_code'] = '';
    $request['staff_access'] = 'false';

    $response = httpRequest("https://wish.wis.ntu.edu.sg/webexe/owa/AUS_SCHEDULE.main_display1", $request);
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
    $request['p_type'] = 'UE';
    $request['academic_session'] = 'Semester '. $semester .' Academic Year '.$year .'-'.($year + 1);
    $request['bOption'] = 'Next';

    $response = httpRequest("https://wis.ntu.edu.sg/webexe/owa/exam_timetable_und.get_detail", $request);

    file_put_contents("data/raw/". $year . "_" . $semester . "_exam.html", $response);

    echo "OK";
} catch (Exception $e) {
    die ($e->getMessage());
}
