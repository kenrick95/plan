<?php
$year = isset($_REQUEST['year']) ? intval($_REQUEST['year']) : 2016;
$semester = isset($_REQUEST['semester']) ? intval($_REQUEST['semester']) : 2;

function str_lreplace($search, $replace, $subject)
{
    $pos = strrpos($subject, $search);

    if($pos !== false)
    {
        $subject = substr_replace($subject, $replace, $pos, strlen($search));
    }

    return $subject;
}
$raw_data = file_get_contents("../data/raw/". $year . "_" . $semester . "_exam.html");

$raw_data = explode("<center>", $raw_data)[1];
$raw_data = explode("</center>", $raw_data)[0];
$raw_data = "<center>" . $raw_data . "</center>";

$raw_data = str_replace("<BR>", "", $raw_data);
$raw_data = str_replace("<br>", "", $raw_data);
$raw_data = str_replace("&nbsp;", "", $raw_data);

$raw_data = str_replace("<table border=1 width=600 cellspacing=0 cellpadding=1>", "<table>", $raw_data);
$raw_data = str_replace("<table border=1 width=100% cellspacing=0 cellpadding=1>", "<table>", $raw_data);

$raw_data = str_replace("<td align=left width=5% valign=top>", "<td>", $raw_data);
$raw_data = str_replace("<td align=left width=10% valign=top>", "<td>", $raw_data);
$raw_data = str_replace("<td align=left width=20% valign=top>", "<td>", $raw_data);
$raw_data = str_replace("<td align=left width=40% valign=top>", "<td>", $raw_data);
$raw_data = str_replace("<td align=center width=20% valign=top>", "<td>", $raw_data);
$raw_data = str_replace("<td align=left width=25% valign=top>", "<td>", $raw_data);

$raw_data = str_replace("bgcolor=#99CCFF", "", $raw_data);
$raw_data = str_replace("bgcolor=#FFFFFF", "", $raw_data);
$raw_data = str_replace("colspan=2", "", $raw_data);

$raw_data = str_replace("<tr", "<TR", $raw_data);
$raw_data = str_replace("</tr", "</TR", $raw_data);
$raw_data = str_lreplace("</table>", "</TR></TR></table>", $raw_data);
$raw_data = trim($raw_data);
$raw_data = preg_replace("/ +/", " ", $raw_data);
$raw_data = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $raw_data);

// file_put_contents('test.txt', print_r($raw_data, true));
$data =  new SimpleXMLElement($raw_data);

# whew, finished cleaning data, now parse it!
$super_data = array();
$course_list = array();
foreach ($data->table[2]->TR->TR as $entry) {
    $exam_date = trim((string) $entry->td[0]);
    $exam_day = trim((string) $entry->td[1]);
    $exam_time = trim((string) $entry->td[2]);
    $exam_code = trim((string) $entry->td[3]);
    $exam_name = trim((string) $entry->td[4]);
    $exam_duration = trim((string) $entry->td[5]);
    if (!empty($exam_code)) {
        $super_data[$exam_code] = array("date" => $exam_date,
            "day" => $exam_day,
            "time" => $exam_time,
            "code" => $exam_code,
            "name" => $exam_name,
            "duration" => $exam_duration);

        array_push($course_list, array(
            "code" => $exam_code,
            "name" => $exam_name));
    }
}

file_put_contents("../data/parsed/text/". $year . "_" . $semester . "_exam_data.txt", print_r($super_data, true));
file_put_contents("../data/parsed/json/". $year . "_" . $semester . "_exam_data.json", json_encode($super_data));

echo "OK";
?>
