/*jslint browser: true, sloppy: true, plusplus: true, continue: true */
/*global jQuery, $, swal, ga */
$(document).ready(function ($) {
    var cache = {}, all_table = [], cur_idx, all_indices = [];
    $("#course_form #submit").removeAttr("disabled");
    if (!!window.localStorage) {
        if (!localStorage.getItem("not_first_visit")) {
            $("#about_modal").modal("show");
            localStorage.setItem("not_first_visit", true);
        }
    }
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
                        if (allTags[i].flag === 0 && allTags[i].label.toLowerCase().replace(/\s+/, "").indexOf(term) >= 0) {
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
        localStorage.setItem("cache", ""); // force to load fresh data
        if (!!localStorage.getItem("cache")) {
            console.log("boo");
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
    $("body").keypress(function (event) {
        if (event.target.nodeName.toLowerCase() !== 'input') {
            if ((event.charCode === 106 || event.keyCode === 39) && $("#page_next").is(":visible")) { // j
                // "j" or ArrowRight
                show_table(cur_idx + 1);
            } else if ((event.charCode === 107 || event.keyCode === 37) && $("#page_prev").is(":visible")) { // k
                // "k" or ArrowLeft
                show_table(cur_idx - 1);
            }
        }
    });

/* ******************************************************************************************** */

    // Handler for advanced search
    var times = {"0830": false, "0900": false, "0930": false, "1000": false, "1030": false, "1100": false, "1130": false, "1200": false, "1230": false, "1300": false, "1330": false,
                 "1400": false, "1430": false, "1500": false, "1530": false, "1600": false, "1630": false, "1700": false, "1730": false, "1800": false, "1830": false, "1900": false, 
                 "1930": false, "2000": false, "2030": false, "2100": false, "2130": false, "2200": false, "2230": false, "2300": false},

        // Need JSON.parse(JSON.stringify(times)) for OBJECT cloning
        free_times_template = {
            "MON": JSON.parse(JSON.stringify(times)),
            "TUE": JSON.parse(JSON.stringify(times)),
            "WED": JSON.parse(JSON.stringify(times)),
            "THU": JSON.parse(JSON.stringify(times)),
            "FRI": JSON.parse(JSON.stringify(times)),
            "SAT": JSON.parse(JSON.stringify(times))
        },

        user_free_times_selection = free_times_template,
        current_day = "MON";


    // Update dropdown button text
    $(".dropdown-menu").on("click", "li a", function () {
        $("#day_button").html($(this).text() + ' <span class="caret"></span>');
        current_day = $(this).attr("data-day");

        $(".free_time_checkbox").each(function () {
            this.checked = user_free_times_selection[current_day][this.value];
        });
    });

    // Update free times selection on time click
    $(".free_time_checkbox").on("click", function () {
        var time = this.value;
        
        if (this.checked) {
            user_free_times_selection[current_day][time] = true;
        } else {
            user_free_times_selection[current_day][time] = false;
        }
    });

    // Select All
    $("#free_time_select_all_button").on("click", function () {
        // Update variable
        for (var time in user_free_times_selection[current_day]) {
            user_free_times_selection[current_day][time] = true;
        }

        // Checking the checkboxes
        $(".free_time_checkbox").prop("checked", true);
    });

    // Deselect All
    $("#free_time_deselect_all_button").on("click", function () {
        // Update variable
        for (var time in user_free_times_selection[current_day]) {
            user_free_times_selection[current_day][time] = false;
        }

        // Unchecking the checkboxes
        $(".free_time_checkbox").prop("checked", false);
    });

/* ******************************************************************************************** */

    $("#course_form").submit(function (e) {
        e.preventDefault();
        var data = $("#input_courses").val(),
            major = $("#course_major").val();
        ga('send', 'event', 'form', 'submit', 'course_form');
        // console.log(data);

        data = data.toUpperCase();
        $("#course_form #submit").prop('disabled', true);
        $.ajax({
            type: "POST",
            url: "back_end/scheduler.php",
            data: {courses: data, major: major, freetime: user_free_times_selection},
            beforeSend: function () {
                var i, splitted;
                // Whenever a new request is submitted, remove all table -> in the real web, there will be a loading icon to tell the user
                // that their request is still in process
                $("#exam_table").hide();
                $("#pager_nav").hide();
                $("#target").html("");

                if (data.length === 0) {
                    swal("Empty input", "Please enter the code of the courses that you want to be scheduled.", "error");
                    $("#course_form #submit").removeAttr("disabled");
                    return false;
                }
                splitted = data.split(',');
                for (i = 0; i < splitted.length; i++) {
                    ga('send', 'event', 'form', 'submit_course', splitted[i]);
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
                    index_chosen = {}, exam_schedule, date, time, rowspan,
                    total_au, total_course, timetable_shown, dayname, sorted_exam_schedule,
                    date_time, item;
                all_table = [];
                all_indices = [];

                // console.log("RES: ");
                // console.log(res);

                //$("#target").html(d);
                $("#target").html("");
                len = res.timetable.length;
                ga('send', 'event', 'form', 'result', 'result_length', len);
                if (!res.validation_result) {
                    ga('send', 'event', 'form', 'result', 'not_found_invalid');
                    swal("We're sorry.", "Invalid input were given. Please refresh the page to try again.", "warning");
                    $("#overlay").hide();
                    $("#course_form #submit").removeAttr("disabled");
                    return;
                }
                if (!res.exam_schedule_validation.ok) {
                    var conflict_msg = "";
                    console.log(res.exam_schedule_validation);
                    for (i = 0; i < res.exam_schedule_validation.conflict.length; i++) {
                      conflict_msg += res.exam_schedule_validation.conflict[i][0] + " and " + res.exam_schedule_validation.conflict[i][1] + "\n";
                    }
                    ga('send', 'event', 'form', 'result', 'not_found_exam');
                    swal("We're sorry.", "There is no possible arrangement found for the given courses because exam schedule of the following have clashed:\n " + conflict_msg + "\nPlease try selecting another course.", "warning");
                    $("#overlay").hide();
                    $("#course_form #submit").removeAttr("disabled");
                    return;
                }
                if (len === 0) {
                    ga('send', 'event', 'form', 'result', 'not_found_impossible');
                    swal("We're sorry.", "There is no possible arrangement found for the given courses. Please try selecting another course.", "warning");
                    $("#overlay").hide();
                    $("#course_form #submit").removeAttr("disabled");
                    return;
                }

                for (i = 0; i < len; i++) {
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
                }


                // show exam table
                table = "";
                exam_schedule = res.exam_schedule;
                sorted_exam_schedule = [];

                for (date in exam_schedule) {
                    if (exam_schedule.hasOwnProperty(date)) {
                        for (time in exam_schedule[date]) {
                            if (exam_schedule[date].hasOwnProperty(time)) {
                                date_str = date + " " + time.replace(".", ":");
                                if (isNaN(Date.parse(date_str))) {
                                  date_time = Infinity;
                                }
                                else {
                                  date_time = new Date(date_str);
                                }
                                sorted_exam_schedule.push({
                                    date_time: date_time,
                                    data: exam_schedule[date][time]
                                });
                            }
                        }
                    }
                }
                sorted_exam_schedule.sort(function (a, b) {
                    return a.date_time > b.date_time;
                });
                total_au = 0;
                total_course = 0;
                for (date_time in sorted_exam_schedule) {
                    if (sorted_exam_schedule.hasOwnProperty(date_time)) {
                        total_course++;
                        item = sorted_exam_schedule[date_time].data;
                        table += "<tr>";
                        table += "<td id=\"index-" + item.code + "\">"
                            + " " // index
                            + "</td>"
                            + "<td>"
                            + item.code
                            + "</td>"
                            + "<td>"
                            + item.name
                            + "</td>"
                            + "<td>"
                            + item.au[0]
                            + "</td>"
                            + "<td>";

                        // Check for existence of examination for that course
                        if (item.day !== -1) {
                            table += item.day
                                + ", "
                                + item.date
                                + ", "
                                + item.time
                                + "&mdash;"
                                + item.end_time;
                        } else {
                            table += "N/A";
                        }

                        table += "</td>";
                        total_au += parseInt(item.au[0], 10);
                        table += "</tr>";
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
                $("#overlay").fadeOut();
                $("#course_form #submit").removeAttr("disabled");

                show_table(0);
                $("#page_length").text(all_table.length);
                $("#pager_nav").show();
            }
        });
    });

    // Google Analytics
    ga('send', 'event', 'page', 'view', 'view');
});