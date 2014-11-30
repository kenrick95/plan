$(document).ready(function($) {
	$("#course_form #submit").click( function (e) {
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