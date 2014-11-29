<?php
# DONE clean the data
function str_lreplace($search, $replace, $subject)
{
    $pos = strrpos($subject, $search);

    if($pos !== false)
    {
        $subject = substr_replace($subject, $replace, $pos, strlen($search));
    }

    return $subject;
}
$raw_data = file_get_contents("2014_2_exam.html");

$raw_data = explode("<center>", $raw_data)[1];
$raw_data = explode("</center>", $raw_data)[0];
$raw_data = "<center>" . $raw_data . "</center>";

$raw_data = str_replace("<BR>", "", $raw_data);
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

$raw_data = str_replace("<tr", "<TR", $raw_data);
$raw_data = str_replace("</tr", "</TR", $raw_data);
$raw_data = str_lreplace("</table>", "</TR></TR></table>", $raw_data);
$raw_data = trim($raw_data);
$raw_data = preg_replace("/ +/", " ", $raw_data);
$raw_data = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $raw_data);

# file_put_contents('2014_2_exam_data.txt', print_r($raw_data, true));
$data =  new SimpleXMLElement($raw_data);

# whew, finished cleaning data, now parse it!
# 
# 

file_put_contents('2014_2_exam_data.txt', print_r($data, true));

?>