/*jslint browser: true, sloppy: true, plusplus: true */
/*global jQuery, $ */
$(document).ready(function ($) {
    var cache = {};
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
                console.log("SENT!!");
                console.log(d);
            }
        });
    });
});