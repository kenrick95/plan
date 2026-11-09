<?php
require("../config.php");

$path = "../data/raw/{$year}_{$semester}_exam.html";
$raw_data = file_get_contents($path);
if ($raw_data === false || trim($raw_data) === '') {
    throw new RuntimeException("Exam HTML is missing or empty: $path");
}

// NTU publishes HTML, including older pages with unclosed header rows.
// Let the HTML parser repair those instead of inserting XML closing tags.
$previous = libxml_use_internal_errors(true);
libxml_clear_errors();
$document = new DOMDocument();
$loaded = $document->loadHTML($raw_data, LIBXML_NONET);
$issues = libxml_get_errors();
libxml_clear_errors();
libxml_use_internal_errors($previous);
if (!$loaded) {
    $messages = array_map(function ($issue) {
        return "line {$issue->line}: " . trim($issue->message);
    }, array_slice($issues, 0, 5));
    throw new RuntimeException("Could not parse exam HTML ($path): " . implode('; ', $messages));
}

$xpath = new DOMXPath($document);
$super_data = [];
foreach ($xpath->query('//tr') as $row) {
    $cells = $xpath->query('./td', $row);
    if ($cells->length < 6) {
        continue;
    }
    $values = [];
    foreach ($cells as $cell) {
        $values[] = trim(preg_replace('/ +/', ' ', str_replace("\xc2\xa0", '', $cell->textContent)));
    }
    // Skip headings and informational tables; venues may add a seventh column.
    if (!preg_match('/^\d{1,2} [A-Za-z]+ \d{4}$/', $values[0])) {
        continue;
    }
    // * and + describe open/restricted-book exams, not part of the course code.
    $code = rtrim($values[3], "*+ \t\n\r");
    if ($code === '') {
        throw new RuntimeException("Empty exam course code in $path for {$values[0]}");
    }
    $super_data[$code] = [
        'date' => $values[0],
        'day' => $values[1],
        'time' => $values[2],
        'code' => $code,
        'name' => $values[4],
        'duration' => $values[5],
    ];
}
if (count($super_data) === 0) {
    throw new RuntimeException("No exam rows found in $path (" . strlen($raw_data) . " bytes); expected date, day, time, course, title, duration columns");
}
if (PHP_SAPI === 'cli') {
    fwrite(STDERR, "Parsed " . count($super_data) . " exam courses from $path (" . strlen($raw_data) . " bytes)\n");
}

$json = json_encode($super_data, JSON_THROW_ON_ERROR);
foreach (['text' => print_r($super_data, true), 'json' => $json] as $format => $contents) {
    $extension = $format === 'text' ? 'txt' : 'json';
    $output = "../data/parsed/$format/{$year}_{$semester}_exam_data.$extension";
    if (file_put_contents($output, $contents) === false) {
        throw new RuntimeException("Could not write parsed exam data: $output");
    }
}
echo "OK";
