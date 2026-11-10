<?php
set_time_limit(60);
$settings['cookiefile'] = "cookies.tmp";

function httpRequest($url, $post="") {
    global $settings;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($ch, CURLOPT_TIMEOUT, 45);
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, "HTTP " . (empty($post) ? 'GET' : 'POST') . " $url\n");
    }
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

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $elapsed = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    $error = curl_error($ch);
    $errno = curl_errno($ch);
    curl_close($ch);
    $details = "HTTP $status, curl=$errno, elapsed={$elapsed}s, bytes=" . ($xml === false ? 0 : strlen($xml));
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, "$url: $details\n");
    }
    if ($xml === false || $xml === '' || $status < 200 || $status >= 300) {
        throw new RuntimeException("Error getting data from server ($url): $details; $error");
    }

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

    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, "Selected exam plan $plan_no for $year semester $semester\n");
    }

    ### Course data
    $request['r_search_type'] = 'F';
    $request['boption'] = 'Search';
    $request['acadsem'] = $year . ';' . $semester;
    #$request['r_course_yr'] = '';
    $request['r_subj_code'] = '';
    $request['staff_access'] = 'false';

    $response = httpRequest("https://wish.wis.ntu.edu.sg/webexe/owa/AUS_SCHEDULE.main_display1", $request);
    $path = "data/raw/{$year}_{$semester}.html";
    if (file_put_contents($path, $response) === false) {
        throw new RuntimeException("Could not write course HTML: $path");
    }


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

    $path = "data/raw/{$year}_{$semester}_exam.html";
    if (file_put_contents($path, $response) === false) {
        throw new RuntimeException("Could not write exam HTML: $path");
    }

    echo "OK";
} catch (Throwable $e) {
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, get_class($e) . ": " . $e->getMessage() . "\n");
        exit(1);
    }
    die ($e->getMessage());
}
