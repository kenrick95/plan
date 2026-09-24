"""Fetch and publish the newest semester listed in academic-calendar.json."""

import json
import re
import subprocess
import sys
from datetime import date
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
BACK_END = ROOT / "back_end"
CALENDAR = ROOT / "academic-calendar.json"


def current_semester():
    entries = json.loads(CALENDAR.read_text(encoding="utf-8"))
    if not entries:
        raise ValueError("Academic calendar is empty")
    seen = set()
    for entry in entries:
        key = (entry["year"], entry["semester"])
        if not isinstance(entry["year"], int) or entry["year"] < 2014 or entry["semester"] not in (1, 2):
            raise ValueError(f"Invalid semester: {key}")
        if key in seen:
            raise ValueError(f"Duplicate semester: {key}")
        seen.add(key)
        start, end, recess_start, recess_end = (
            date.fromisoformat(entry[field])
            for field in ("start", "end", "recess_start", "recess_end")
        )
        if not start <= recess_start <= recess_end <= end:
            raise ValueError(f"Invalid academic dates: {key}")
        if start.year != entry["year"] + (entry["semester"] == 2):
            raise ValueError(f"Academic start year does not match semester: {key}")
    return max(entries, key=lambda entry: (entry["year"], entry["semester"]))


def run_php(directory, script, year, semester):
    request = f"year={year}&semester={semester}"
    command = [
        "php", "-d", "display_errors=stderr", "-r",
        "parse_str($argv[1], $_REQUEST); require $argv[2];",
        request, script,
    ]
    result = subprocess.run(command, cwd=directory, capture_output=True, text=True, timeout=300)
    if result.returncode or result.stdout.strip() != "OK":
        raise RuntimeError(f"{script} failed: {result.stdout.strip()} {result.stderr.strip()}")
    print(f"{script}: OK")


def validate_data(year, semester):
    stem = f"{year}_{semester}"
    raw = BACK_END / "data" / "raw"
    parsed = BACK_END / "data" / "parsed"
    for filename in (f"{stem}.html", f"{stem}_exam.html"):
        if (raw / filename).stat().st_size < 5000:
            raise ValueError(f"Raw data is unexpectedly small: {filename}")
    for suffix, minimum in (("course_list", 500), ("data", 500), ("exam_data", 50)):
        filename = f"{stem}_{suffix}.json"
        data = json.loads((parsed / "json" / filename).read_text(encoding="utf-8"))
        if not isinstance(data, (list, dict)) or len(data) < minimum:
            raise ValueError(f"Parsed data is unexpectedly small: {filename}")
        if (parsed / "text" / filename.replace(".json", ".txt")).stat().st_size < 5000:
            raise ValueError(f"Parsed text is unexpectedly small: {filename}")


def publish_defaults(entry):
    year, semester = entry["year"], entry["semester"]
    config = BACK_END / "config.php"
    source = config.read_bytes().decode("utf-8")
    source, year_count = re.subn(r"(\$year = [^\r\n]*: )\d{4}(;)", rf"\g<1>{year}\g<2>", source, count=1)
    source, semester_count = re.subn(r"(\$semester = [^\r\n]*: )[12](;)", rf"\g<1>{semester}\g<2>", source, count=1)
    if (year_count, semester_count) != (1, 1):
        raise ValueError("Could not locate default semester in config.php")

    engine = ROOT / "js" / "engine.js"
    js = engine.read_bytes().decode("utf-8")
    fields = (
        ("ACADEMIC_START_DATE", "start", "00:00:00"),
        ("ACADEMIC_END_DATE", "end", "23:59:59"),
        ("ACADEMIC_RECESS_START_DATE", "recess_start", "00:00:00"),
        ("ACADEMIC_RECESS_END_DATE", "recess_end", "23:59:59"),
    )
    for name, field, clock in fields:
        day = date.fromisoformat(entry[field])
        human_date = f"{day.day} {day.strftime('%B')} {day.year}"
        pattern = rf"(var {name} = new Date\(')[^']+('\);[^\n]*)"
        js, count = re.subn(pattern, lambda match: f"{match[1]}{human_date} {clock} GMT+0800{match[2]}", js, count=1)
        if count != 1:
            raise ValueError(f"Could not locate {name} in engine.js")
    config.write_bytes(source.encode("utf-8"))
    engine.write_bytes(js.encode("utf-8"))


def main():
    entry = current_semester()
    year, semester = entry["year"], entry["semester"]
    print(f"Updating {year} semester {semester}")
    run_php(BACK_END, "getter.php", year, semester)
    run_php(BACK_END / "parser", "parse.php", year, semester)
    run_php(BACK_END / "parser", "parse_exam.php", year, semester)
    validate_data(year, semester)
    publish_defaults(entry)
    print("Validated and published defaults")


if __name__ == "__main__":
    try:
        main()
    except (OSError, ValueError, RuntimeError, subprocess.TimeoutExpired) as error:
        print(error, file=sys.stderr)
        sys.exit(1)
