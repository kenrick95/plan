/*jslint browser: true, sloppy: true, plusplus: true, continue: true */
/*global jQuery, $, swal */
$(document).ready(function ($) {
    $("#loading").css("margin-top", ($("#overlay").outerHeight() - $("#loading").outerHeight()) / 2  + "px");
    $("#overlay").fadeOut();

    function log(msg, load_id) {
        var timestamp = new Date();
        $("#target").append("[" + timestamp.toISOString() + "] ");
        if (load_id !== undefined) {
            $("#target").append(msg);
            $("#target").append("<img id=\"" + load_id + "-load\" src=\"/css/images/loading.gif\" width=\"16\"><br> ");
        } else {
            $("#target").append(msg + "<br>");
        }
    }

    $("#select_form").submit(function (e) {
        e.preventDefault();
        var year = parseInt($("#year").val(), 10),
            semester = parseInt($("#semester").val(), 10),
            plan_no = parseInt($("#plan_no").val(), 10),
            timestamp = null;

        $("#select_form #submit").prop('disabled', true);
        $.ajax({
            type: "POST",
            url: "/back_end/getter.php",
            data: {
                year: year,
                semester: semester,
                plan_no: plan_no
            },
            beforeSend: function () {
                log("Start getting data... ", "getter");

                if (year === null || semester === null || plan_no === null) {
                    swal("Empty input", "Please enter them.", "error");
                    $("#select_form #submit").removeAttr("disabled");
                    return false;
                }
            },
            success: function (d) {
                $("#getter-load").remove();
                if (d === "OK") {
                    log("Finished getting data!");
                    var done = 0;

                    // Parse course data
                    $.ajax({
                        type: "POST",
                        url: "/back_end/parser/parse.php",
                        data: {
                            year: year,
                            semester: semester
                        },
                        beforeSend: function () {
                            log("Start parsing course data... ", "parse");
                        },
                        success: function (d) {
                            $("#parse-load").remove();
                            log("Finished parsing course data!");

                            if (d === "OK") {
                                log("Successfully parsed course data!");
                                done++;
                                if (done === 2) {
                                    $("#select_form #submit").removeAttr("disabled");
                                    log("<b>All done!</b><br>");
                                }
                            }

                        }
                    });
                    // Parse exam data
                    $.ajax({
                        type: "POST",
                        url: "/back_end/parser/parse_exam.php",
                        data: {
                            year: year,
                            semester: semester
                        },
                        beforeSend: function () {
                            log("Start parsing course data... ", "parse_exam");
                        },
                        success: function (d) {
                            $("#parse_exam-load").remove();
                            log("Finished parsing exam data!");

                            if (d === "OK") {
                                log("Successfully parsed exam data!");
                                done++;
                                if (done === 2) {
                                    $("#select_form #submit").removeAttr("disabled");
                                    log("<b>All done!</b><br>");
                                }
                            }

                        }
                    });
                }

            }
        });
    });
});