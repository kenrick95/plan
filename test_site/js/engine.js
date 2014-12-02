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
    if (!!window.localStorage) {
        if (!!localStorage.getItem("cache")) {
            cache = JSON.parse(localStorage.getItem("cache"));
        }
    }
    function split(val) {
        return val.split(/,\s*/);
    }
    function extract_last(term) {
        return split(term).pop();
    }
    $("#input_courses").bind("keydown", function (event) {
        if (event.keyCode === $.ui.keyCode.TAB &&
                $(this).autocomplete("instance").menu.active) {
            event.preventDefault();
        }
    }).autocomplete({
        source: function (request, response) {
            var term = extract_last(request.term);
            console.log(term);
            if (cache.hasOwnProperty(term)) {
                response(cache[term]);
                console.log('cache hit');
                return;
            }
            $.getJSON("search.php", {
                term: term
            }, function (data, status, xhr) {
                cache[term] = data;
                if (!!window.localStorage) {
                    localStorage.setItem("cache", JSON.stringify(cache));
                }
                response(data);
            });
        },
        search: function () {
            // custom minLength
            var term = extract_last(this.value);
            if (term.length < 2) {
                return false;
            }
        },
        focus: function () {
            // prevent value inserted on focus
            return false;
        },
        select: function (event, ui) {
            var terms = split(this.value);
            // remove the current input
            terms.pop();
            // add the selected item
            terms.push(ui.item.value);
            // add placeholder to get the comma-and-space at the end
            terms.push("");
            this.value = terms.join(", ");
            return false;
        }
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
                    table = "<table class=\"table\">"
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
                                            + "<br>"
                                            + details[0].index
                                            + "<br>"
                                            + details[0].details.type
                                            + "<br>"
                                            + details[0].details.group
                                            + "<br>"
                                            + details[0].details.location
                                            + "<br>"
                                            + details[0].details.time.full
                                            + "<br>"
                                            + details[0].details.remarks
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
                        + "</table>";
                    $("#target").append(table);
                }
            }
        });
    });
});