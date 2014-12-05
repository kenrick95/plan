/*jslint browser: true, sloppy: true, plusplus: true */
/*global jQuery, $ */
$(document).ready(function ($) {
    // Array Remove - By John Resig (MIT Licensed)
    Array.prototype.remove = function (from, to) {
        var rest = this.slice((to || from) + 1 || this.length);
        this.length = from < 0 ? this.length + from : from;
        return this.push.apply(this, rest);
    };
    var cache = {};
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
        console.log(data);
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
            $.getJSON("search.php", { term: '' }, tagit);
        }
    } else {
        $.getJSON("search.php", { term: '' }, tagit);
    }

    $("#course_form #submit").click(function (e) {
        /*
            Check whether the input is valid (using regex)
        */
        e.preventDefault();
        var data = $("#input_courses").val();
        console.log(data);

        $.ajax({
            type: "POST",
            url: "../scheduler/scheduler.php",
            data: {courses: data},
            success: function (d) {
                var res = JSON.parse(d), timetable, len, i, table, details, detail,
                    day, days = ["MON", "TUE", "WED", "THU", "FRI", "SAT"],
                    time, times = ["0830", "0900", "0930", "1000", "1030",
                    "1100", "1130", "1200", "1230", "1300", "1330", "1400",
                    "1430", "1500", "1530", "1600", "1630", "1700", "1730",
                    "1800", "1830", "1900", "1930", "2000", "2030", "2100",
                    "2130", "2200", "2230", "2300"];

                console.log(res);
                //$("#target").html(d);
                $("#target").html("");
                len = res.timetable.length;
                for (i = 0; i < len; i++) {
                    timetable = res.timetable[i];
                    table = "<div class=\"table-responsive\">"
                        + "<table class=\"table\">"
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

                    for (time in times) {
                        if (times.hasOwnProperty(time)) {
                            table += "<tr>";
                            table += "<td>" + times[time] + "</td>";
                            for (day in days) {
                                if (days.hasOwnProperty(day)) {
                                    details = timetable[days[day]][times[time]];
                                    if (details[0] !== undefined) {
                                        table += "<td>"
                                            + details[0].id
                                            + " "
                                            + details[0].index
                                            + " "
                                            + details[0].type
                                            + " "
                                            + details[0].flag
                                            + "</td>";
                                    } else {
                                        table += "<td>"
                                            + "</td>";
                                    }
                                }
                            }
                            table += "</tr>";
                        }
                    }
                    table += "</tbody>"
                        + "</table>"
                        + "</div>";
                    $("#target").append(table);
                }
            }
        });
    });
});