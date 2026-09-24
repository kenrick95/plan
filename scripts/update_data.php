<?php
// Fetch and publish the newest semester listed in academic-calendar.json.
if (PHP_SAPI !== 'cli') {
    exit(1);
}

$root = dirname(__DIR__);
$backend = $root . '/back_end';

function fail($message) {
    throw new RuntimeException($message);
}

function academicDate($entry, $field) {
    if (!isset($entry[$field]) || !is_string($entry[$field])) {
        fail("Missing academic date: $field");
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $entry[$field]);
    if (!$date || $date->format('Y-m-d') !== $entry[$field]) {
        fail("Invalid academic date: $field");
    }
    return $date;
}

function currentSemester($root) {
    $entries = json_decode(file_get_contents($root . '/academic-calendar.json'), true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($entries) || count($entries) === 0) {
        fail('Academic calendar is empty');
    }
    $seen = [];
    foreach ($entries as $entry) {
        if (!isset($entry['year'], $entry['semester']) || !is_int($entry['year']) || $entry['year'] < 2014 || !in_array($entry['semester'], [1, 2], true)) {
            fail('Invalid semester in academic calendar');
        }
        $key = $entry['year'] . '_' . $entry['semester'];
        if (isset($seen[$key])) {
            fail("Duplicate semester: $key");
        }
        $seen[$key] = true;
        $start = academicDate($entry, 'start');
        $end = academicDate($entry, 'end');
        $recessStart = academicDate($entry, 'recess_start');
        $recessEnd = academicDate($entry, 'recess_end');
        if ($start > $recessStart || $recessStart > $recessEnd || $recessEnd > $end) {
            fail("Invalid academic dates: $key");
        }
        if ((int)$start->format('Y') !== $entry['year'] + ($entry['semester'] === 2 ? 1 : 0)) {
            fail("Academic start year does not match semester: $key");
        }
    }
    usort($entries, function ($a, $b) {
        return [$b['year'], $b['semester']] <=> [$a['year'], $a['semester']];
    });
    return $entries[0];
}

function runPhp($directory, $script, $year, $semester) {
    $command = [PHP_BINARY, '-d', 'display_errors=stderr', '-r',
        'parse_str($argv[1], $_REQUEST); require $argv[2];',
        "year=$year&semester=$semester", $script];
    $pipes = [];
    $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $directory);
    if (!is_resource($process)) {
        fail("Could not start $script");
    }
    $output = stream_get_contents($pipes[1]);
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $status = proc_close($process);
    if ($status !== 0 || trim($output) !== 'OK') {
        fail("$script failed: " . trim($output . ' ' . $errors));
    }
    echo "$script: OK\n";
}

function validateData($backend, $year, $semester) {
    $stem = "{$year}_{$semester}";
    foreach (["$stem.html", "{$stem}_exam.html"] as $name) {
        if (filesize("$backend/data/raw/$name") < 5000) {
            fail("Raw data is unexpectedly small: $name");
        }
    }
    foreach (['course_list' => 500, 'data' => 500, 'exam_data' => 50] as $suffix => $minimum) {
        $name = "{$stem}_{$suffix}";
        $data = json_decode(file_get_contents("$backend/data/parsed/json/$name.json"), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data) || count($data) < $minimum) {
            fail("Parsed data is unexpectedly small: $name.json");
        }
        if (filesize("$backend/data/parsed/text/$name.txt") < 5000) {
            fail("Parsed text is unexpectedly small: $name.txt");
        }
    }
}

function publishDefaults($root, $backend, $entry) {
    $configPath = "$backend/config.php";
    $config = file_get_contents($configPath);
    foreach (['year', 'semester'] as $field) {
        $pattern = '/(\$' . $field . ' = [^\r\n]*: )\d+(;)/';
        $count = 0;
        $config = preg_replace_callback($pattern, function ($match) use ($entry, $field) {
            return $match[1] . $entry[$field] . $match[2];
        }, $config, 1, $count);
        if ($count !== 1) {
            fail("Could not locate default $field in config.php");
        }
    }

    $enginePath = "$root/js/engine.js";
    $engine = file_get_contents($enginePath);
    $fields = [
        'ACADEMIC_START_DATE' => ['start', '00:00:00'],
        'ACADEMIC_END_DATE' => ['end', '23:59:59'],
        'ACADEMIC_RECESS_START_DATE' => ['recess_start', '00:00:00'],
        'ACADEMIC_RECESS_END_DATE' => ['recess_end', '23:59:59'],
    ];
    foreach ($fields as $name => $details) {
        list($field, $time) = $details;
        $value = academicDate($entry, $field)->format('j F Y') . " $time GMT+0800";
        $pattern = "~(var $name = new Date\\(')[^']+('\\);[^\\r\\n]*)~";
        $count = 0;
        $engine = preg_replace_callback($pattern, function ($match) use ($value) {
            return $match[1] . $value . $match[2];
        }, $engine, 1, $count);
        if ($count !== 1) {
            fail("Could not locate $name in engine.js");
        }
    }
    if (file_put_contents($configPath, $config) === false || file_put_contents($enginePath, $engine) === false) {
        fail('Could not publish semester defaults');
    }
}

try {
    $entry = currentSemester($root);
    $year = $entry['year'];
    $semester = $entry['semester'];
    echo "Updating $year semester $semester\n";
    runPhp($backend, 'getter.php', $year, $semester);
    runPhp("$backend/parser", 'parse.php', $year, $semester);
    runPhp("$backend/parser", 'parse_exam.php', $year, $semester);
    validateData($backend, $year, $semester);
    publishDefaults($root, $backend, $entry);
    echo "Validated and published defaults\n";
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . "\n");
    exit(1);
}
