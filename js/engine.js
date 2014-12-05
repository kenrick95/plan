/*jslint browser: true, sloppy: true, plusplus: true, continue: true */
/*global jQuery, $ */
$(document).ready(function ($) {
    // Array Remove - By John Resig (MIT Licensed)
    Array.prototype.remove = function (from, to) {
        var rest = this.slice((to || from) + 1 || this.length);
        this.length = from < 0 ? this.length + from : from;
        return this.push.apply(this, rest);
    };
    var cache = {}, all_table = [], cur_idx, all_indices = [];
    function split(val) {
        return val.split(/,\s*/);
    }
    function extract_last(term) {
        return split(term).pop();
    }
    function tagit(data, status, xhr) {
        var allTags = data;
        if (!!window.localStorage) {
            localStorage.setItem("cache", JSON.stringify(data));
        }
        $('#input_courses').tagit({
            availableTags: allTags,
            allowSpaces: true,
            autocomplete: {
                source: function (request, response) {
                    var i,
                        term = extract_last(request.term).replace(/\s+/, "").toLowerCase(),
                        len = allTags.length,
                        shown_data = [];
                    for (i = 0; i < len; i++) {
                        if (allTags[i].flag === 0
                                && allTags[i].label.toLowerCase().replace(/\s+/, "").indexOf(term) >= 0) {
                            shown_data.push(allTags[i]);
                        }
                    }
                    response(shown_data);
                },
            },
            afterTagAdded: function (event, ui) {
                var i, len = allTags.length;
                for (i = 0; i < len; i++) {
                    if (allTags[i].value === ui.tagLabel) {
                        allTags[i].flag = 1;
                        break;
                    }
                }
            },
            afterTagRemoved: function (event, ui) {
                var i, len = allTags.length;
                for (i = 0; i < len; i++) {
                    if (allTags[i].value === ui.tagLabel) {
                        allTags[i].flag = 0;
                        break;
                    }
                }
            }
        });
    }
    if (!!window.localStorage) {
        if (!!localStorage.getItem("cache")) {
            cache = JSON.parse(localStorage.getItem("cache"));
            tagit(cache);
        } else {
            $.getJSON("back_end/search.php", { term: '' }, tagit);
        }
    } else {
        $.getJSON("back_end/search.php", { term: '' }, tagit);
    }

    $("#pager_nav").hide();
    $("#exam_table").hide();
    function show_table(idx) {
        if (idx < 0 || idx >= all_table.length) {
            return;
        }
        var code;
        $("#page_number").text(idx + 1);
        $("#target").html(all_table[idx]);
        // console.log(all_indices[idx]);
        for (code in all_indices[idx]) {
            if (all_indices[idx].hasOwnProperty(code)) {
                $("#index-" + code).text(all_indices[idx][code]);
            }
        }
        cur_idx = idx;
    }
    $("#page_next").click(function () {
        show_table(cur_idx + 1);
    });
    $("#page_prev").click(function () {
        show_table(cur_idx - 1);
    });

    $("#course_form #submit").click(function (e) {
        /*
            Check whether the input is valid (using regex)
        */
        e.preventDefault();
        var data = $("#input_courses").val();
        console.log(data);

        $.ajax({
            type: "POST",
            url: "back_end/scheduler.php",
            data: {courses: data},
            success: function (d) {
                var res = JSON.parse(d), timetable, len, i, j, table, details,
                    day, days = ["MON", "TUE", "WED", "THU", "FRI", "SAT"],
                    times = ["0830", "0900", "0930", "1000", "1030",
                        "1100", "1130", "1200", "1230", "1300", "1330", "1400",
                        "1430", "1500", "1530", "1600", "1630", "1700", "1730",
                        "1800", "1830", "1900", "1930", "2000", "2030", "2100",
                        "2130", "2200", "2230", "2300"], lentime = times.length,
                    index_chosen = {}, exam_schedule, date, time, rowspanning, rowspan,
                    total_au, total_course;
                all_table = [];
                all_indices = [];

                console.log(res);
                //$("#target").html(d);
                $("#target").html("");
                len = res.timetable.length;
                for (i = 0; i < len; i++) {
                    rowspanning = {};
                    index_chosen = {};
                    timetable = res.timetable[i];
                    table = "<div class=\"table-responsive\">"
                        + "<table class=\"table-condensed table-bordered\" id=\"schedule\">"
                        + "<thead>"
                        + "<tr>"
                        + "<th>Time\\Day</th>"
                        + "<th>Monday</th>"
                        + "<th>Tuesday</th>"
                        + "<th>Wednesday</th>"
                        + "<th>Thursday</th>"
                        + "<th>Friday</th>"
                        + "<th>Saturday</th>"
                        + "</tr>"
                        + "</thead>"
                        + "<tbody>";

                    for (j = 0; j < lentime - 1; j++) {
                        table += "<tr>";
                        table += "<td>" + times[j] + "-" + times[j + 1] + "</td>";
                        for (day in days) {
                            if (days.hasOwnProperty(day)) {
                                details = timetable[days[day]][times[j]];
                                if (details[0] !== undefined) {
                                    if (details[1] !== undefined) {
                                        rowspan = 0;
                                        if (!rowspanning.hasOwnProperty(details[0].id + details[0].type + details[0].start_time)) {
                                            rowspanning[details[0].id + details[0].type + details[0].start_time] = true;
                                            rowspan = parseInt(details[0].duration * 2, 10);
                                        } else {
                                            continue;
                                        }
                                        if (!rowspanning.hasOwnProperty(details[1].id + details[1].type + details[1].start_time)) {
                                            rowspanning[details[1].id + details[1].type + details[1].start_time] = true;
                                            rowspan = parseInt(details[1].duration * 2, 10);
                                        } else {
                                            continue;
                                        }
                                        if (rowspan === 0) {
                                            table += "<td>";
                                        } else {
                                            table += "<td rowspan=\"" + rowspan + "\">";
                                        }
                                        table += details[0].id
                                            + " "
                                            + details[0].type
                                            + " "
                                            + details[0].group
                                            + " "
                                            + details[0].location
                                            + " "
                                            + details[0].remarks
                                            + "<br>"
                                            + details[1].id
                                            + " "
                                            + details[1].type
                                            + " "
                                            + details[1].group
                                            + " "
                                            + details[1].location
                                            + " "
                                            + details[1].remarks
                                            + "</td>";
                                        if (!index_chosen.hasOwnProperty(details[0].id)) {
                                            index_chosen[details[0].id] = details[0].index;
                                        }
                                        if (!index_chosen.hasOwnProperty(details[1].id)) {
                                            index_chosen[details[1].id] = details[1].index;
                                        }
                                    } else {
                                        if (!rowspanning.hasOwnProperty(details[0].id + details[0].type + details[0].start_time)) {
                                            rowspanning[details[0].id + details[0].type + details[0].start_time] = true;
                                            rowspan = parseInt(details[0].duration * 2, 10);
                                        } else {
                                            continue;
                                        }
                                        if (rowspan === 0) {
                                            table += "<td>";
                                        } else {
                                            table += "<td rowspan=\"" + rowspan + "\">";
                                        }
                                        table += details[0].id
                                            + " "
                                            + details[0].type
                                            + " "
                                            + details[0].group
                                            + " "
                                            + details[0].location
                                            + " "
                                            + details[0].remarks
                                            + "</td>";
                                        if (!index_chosen.hasOwnProperty(details[0].id)) {
                                            index_chosen[details[0].id] = details[0].index;
                                        }
                                    }
                                } else {
                                    table += "<td>"
                                        + "</td>";
                                }
                            }
                        }
                        table += "</tr>";
                    }
                    table += "</tbody>"
                        + "</table>"
                        + "</div>";
                    all_table.push(table);
                    all_indices.push(index_chosen);
                    //$("#target").append(table);
                }


                // show exam table
                table = "";
                exam_schedule = res.exam_schedule;
                total_au = 0, total_course = 0;
                for (date in exam_schedule) {
                    if (exam_schedule.hasOwnProperty(date)) {
                        for (time in exam_schedule[date]) {
                            if (exam_schedule[date].hasOwnProperty(time)) {
                                total_course++;
                                table += "<tr>";
                                table += "<td id=\"index-" + exam_schedule[date][time].code + "\">"
                                    + " " // index
                                    + "</td>"
                                    + "<td>"
                                    + exam_schedule[date][time].code
                                    + "</td>"
                                    + "<td>"
                                    + exam_schedule[date][time].name
                                    + "</td>"
                                    + "<td>"
                                    + exam_schedule[date][time]["au"][0]
                                    + "</td>"
                                    + "<td>"
                                    + exam_schedule[date][time].day
                                    + ", "
                                    + date
                                    + ", "
                                    + exam_schedule[date][time].time
                                    + "&mdash;"
                                    + exam_schedule[date][time].end_time
                                    + "</td>";
                                total_au += parseInt(exam_schedule[date][time]["au"][0]);
                                table += "</tr>";
                            }
                        }
                    }
                }
                table += "<tr>"
                    + "<td>Total</td>"
                    + "<td colspan=\"2\">"
                    + total_course + " Course(s)"
                    + "</td>"
                    + "<td colspan=\"2\">"
                    + total_au + " AU(s)"
                    + "</td>"
                    + "</tr>";

                $("#exam_body").html(table);
                $("#exam_table").show();
                // $("#jumbo_title").slideUp();

                show_table(0);
                $("#page_length").text(all_table.length);
                $("#pager_nav").show();
            }
        });
    });
});