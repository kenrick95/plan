<?php
// Offline regression coverage for NTU's old and venue-inclusive exam tables.
$root = dirname(__DIR__, 2);
$temp = sys_get_temp_dir() . '/plan-exam-test-' . bin2hex(random_bytes(6));
foreach (['parser', 'data/raw', 'data/parsed/json', 'data/parsed/text'] as $directory) {
    mkdir("$temp/$directory", 0777, true);
}
copy("$root/back_end/config.php", "$temp/config.php");
copy("$root/back_end/parser/parse_exam.php", "$temp/parser/parse_exam.php");
function check($condition, $message) {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}
function parseFixture($temp, $html) {
    file_put_contents("$temp/data/raw/2026_1_exam.html", $html);
    $process = proc_open([PHP_BINARY, '-d', 'display_errors=stderr', '-r',
        'parse_str("year=2026&semester=1", $_REQUEST); require "parse_exam.php";'],
        [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, "$temp/parser");
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    return [proc_close($process), $stdout, $stderr];
}
try {
    foreach ([false, true] as $withVenue) {
        $html = '<html><body><center><table><tr><td colspan=2>Open book legend</td></tr></table><table><tr>';
        foreach (['Date', 'Day', 'Time', 'Course', 'Course Title', 'Duration'] as $heading) {
            $html .= "<td><b>$heading</b></td>";
        }
        // The historical page omits the header's closing row tag.
        $html .= $withVenue ? '<td>Venue</td></tr>' : '';
        foreach (['BF3218+', 'MA3701*', 'AAE18C'] as $code) {
            $html .= "<tr><td>23 November 2026</td><td>Monday</td><td>9.00 am</td><td>$code</td><td>SCIENCE &amp; SOCIETY</td><td>2 hr</td>";
            $html .= $withVenue ? '<td>HALL A & B</td></tr>' : '</tr>';
        }
        $html .= '</table></center></body></html>';
        [$status, $stdout, $stderr] = parseFixture($temp, $html);
        check($status === 0 && $stdout === 'OK', "Parser failed: $stderr");
        $data = json_decode(file_get_contents("$temp/data/parsed/json/2026_1_exam_data.json"), true, 512, JSON_THROW_ON_ERROR);
        check(array_keys($data) === ['BF3218', 'MA3701', 'AAE18C'], 'Course codes lost or book markers retained');
        check($data['BF3218'] === ['date' => '23 November 2026', 'day' => 'Monday', 'time' => '9.00 am',
            'code' => 'BF3218', 'name' => 'SCIENCE & SOCIETY', 'duration' => '2 hr'], 'Exam fields changed');
        check(strpos($stderr, 'Parsed 3 exam courses') !== false, 'Missing parser diagnostics');
    }
    $before = file_get_contents("$temp/data/parsed/json/2026_1_exam_data.json");
    foreach (['', '<html><body>Service unavailable</body></html>'] as $html) {
        [$status, $stdout, $stderr] = parseFixture($temp, $html);
        check($status !== 0 && $stdout !== 'OK', 'Invalid input accepted');
        check(strpos($stderr, '2026_1_exam.html') !== false, 'Failure did not identify source file');
        check(file_get_contents("$temp/data/parsed/json/2026_1_exam_data.json") === $before, 'Failure overwrote previous output');
    }
    echo "Exam parser regressions passed\n";
} finally {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($temp, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $file) {
        $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
    }
    rmdir($temp);
}
