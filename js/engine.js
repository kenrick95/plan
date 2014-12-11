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
            caseSensitive: false,
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
            beforeTagAdded: function (event, ui) {
                var i, len = allTags.length, course = ui.tagLabel.toUpperCase(), found = false;
                for (i = 0; i < len; i++) {
                    // To ignore the case sensitivity
                    if (allTags[i].value === course) {
                        found = true;
                        break;
                    }
                }
                if (!found) {
                    return false;
                }
            },
            afterTagAdded: function (event, ui) {
                var i, len = allTags.length, course = ui.tagLabel.toUpperCase();
                for (i = 0; i < len; i++) {
                    // To ignore the case sensitivity
                    if (allTags[i].value === course) {
                        allTags[i].flag = 1;
                        break;
                    }
                }
            },
            afterTagRemoved: function (event, ui) {
                var i, len = allTags.length, course = ui.tagLabel.toUpperCase();
                for (i = 0; i < len; i++) {
                    // To ignore the case sensitivity
                    if (allTags[i].value === course) {
                        allTags[i].flag = 0;
                        break;
                    }
                }
            },
            preprocessTag: function (val) {
                return val.toUpperCase();
            }
        });
    }
    if (!!window.localStorage) {
        localStorage.clear();
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
    $("#loading").css("margin-top", ($("#overlay").outerHeight() - $("#loading").outerHeight()) / 2  + "px");
    $("#overlay").fadeOut();

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

    $("#course_form").submit(function (e) {
        e.preventDefault();
        var data = $("#input_courses").val(), c;
        data = data.toUpperCase();
        $("#course_form #submit").prop('disabled', true);
        $.ajax({
            type: "POST",
            url: "back_end/scheduler.php",
            data: {courses: data},
            beforeSend: function () {
                // Whenever a new request is submitted, remove all table -> in the real web, there will be a loading icon to tell the user
                // that their request is still in process
                $("#exam_table").hide();
                $("#pager_nav").hide();
                $("#target").html("");

                if (data.length === 0) {
                    $("#input_empty_modal").modal('show');
                    $("#course_form #submit").removeAttr("disabled");
                    return false;
                }
                $("#overlay").fadeIn();
            },
            success: function (d) {
                var res = JSON.parse(d), timetable, len, i, j, k, table, details,
                    day, days = ["MON", "TUE", "WED", "THU", "FRI", "SAT"],
                    times = ["0830", "0900", "0930", "1000", "1030",
                        "1100", "1130", "1200", "1230", "1300", "1330", "1400",
                        "1430", "1500", "1530", "1600", "1630", "1700", "1730",
                        "1800", "1830", "1900", "1930", "2000", "2030", "2100",
                        "2130", "2200", "2230", "2300"], lentime = times.length,
                    index_chosen = {}, exam_schedule, date, time, rowspanning, rowspan,
                    total_au, total_course, timetable_shown, dayname;
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
                    timetable_shown = {};
                    for (j = 0; j < lentime - 1; j++) {
                        table += "<tr>";
                        table += "<td>" + times[j] + "-" + times[j + 1] + "</td>";
                        for (day in days) {
                            if (days.hasOwnProperty(day)) {
                                dayname = days[day];
                                if (timetable[dayname] === undefined
                                        || timetable[dayname][times[j]] === undefined) {
                                    table += "<td></td>";
                                    continue;
                                }
                                details = timetable[dayname][times[j]];
                                rowspan = 1;
                                if (details[0] !== undefined) {
                                    if (!timetable_shown.hasOwnProperty(j + " " + day)) {
                                        timetable_shown[j + " " + day] = true;
                                    } else {
                                        continue;
                                    }
                                    for (k = j + 1; k < lentime - 1; k++) {
                                        if (timetable[dayname][times[k]] === undefined
                                                || timetable[dayname][times[k]][0] === undefined) {
                                            break;
                                        }
                                        if (details[0].id === timetable[dayname][times[k]][0].id
                                                && details[0].type === timetable[dayname][times[k]][0].type) {
                                            rowspan++;
                                            if (!timetable_shown.hasOwnProperty(k + " " + day)) {
                                                timetable_shown[k + " " + day] = true;
                                            }
                                        } else {
                                            break;
                                        }
                                    }
                                    if (rowspan === 1) {
                                        table += "<td>";
                                    } else {
                                        table += "<td rowspan=\"" + rowspan + "\">";
                                    }
                                    if (!index_chosen.hasOwnProperty(details[0].id)) {
                                        index_chosen[details[0].id] = details[0].index;
                                    }
                                    table += details[0].id
                                        + " "
                                        + details[0].type
                                        + " "
                                        + details[0].group
                                        + " "
                                        + details[0].location
                                        + " "
                                        + details[0].remarks;
                                    if (details[1] !== undefined) {
                                        table += "<br>"
                                            + details[1].id
                                            + " "
                                            + details[1].type
                                            + " "
                                            + details[1].group
                                            + " "
                                            + details[1].location
                                            + " "
                                            + details[1].remarks;
                                        if (!index_chosen.hasOwnProperty(details[1].id)) {
                                            index_chosen[details[1].id] = details[1].index;
                                        }
                                    }
                                    table += "</td>";
                                } else {
                                    table += "<td></td>";
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
                $("#overlay").fadeOut();
                $("#course_form #submit").removeAttr("disabled");

                show_table(0);
                $("#page_length").text(all_table.length);
                $("#pager_nav").show();
            }
        });
    });
});