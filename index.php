<?php
require("back_end/config.php");
?><!DOCTYPE HTML>
<html>
    <head>
        <title>plan* - NTU Course Planner</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!--[if IE]>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <![endif]-->
        <link href='http://fonts.googleapis.com/css?family=Lato:400,700' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" href="css/jquery-ui.min.css">
        <link rel="stylesheet" href="css/jquery-ui.theme.min.css">
        <link rel="stylesheet" href="css/jquery.tag-it.css">
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <link rel="stylesheet" href="css/sweet-alert.css">
        <!--<link rel="stylesheet" href="css/bootstrap-theme.min.css">-->
        <link rel="stylesheet" href="css/style.css">
        <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

        ga('create', 'UA-57413916-1', 'auto');
        ga('require', 'displayfeatures');
        ga('send', 'pageview');

        </script>
    </head>
    <body>
        <div id="overlay">
            <div id="loading"></div>
        </div>
        <nav class="navbar navbar-default" role="navigation">
                <div class="container">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <a class="navbar-brand pointer">plan*</a>
                    </div>
                    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                        <ul class="nav navbar-nav">
                            <li class="active"><a class="pointer">Home</a></li>
                            <li><a class="pointer" data-toggle="modal" data-target="#about_modal">About</a></li>
                        </ul>
                    </div>
                </div>
        </nav>
        <div id="main" class="container">
            <div class="jumbotron">
                <h1 id="jumbo_title">plan* <small>NTU Course Planner</small></h1>
                <form role="form" id="course_form">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Enter course codes:</label>
                        <div class="row">
                        <div class="col-sm-10">
                        <input type="text" name="courses" id="input_courses" placeholder="Enter course code" class="form-control">
                        </div>
                        <div class="col-sm-2">
                        <select id="course_major" name="major" class="form-control">
                            <option value="SCSE">SCSE</option>
                            <option value="ADM">ADM</option>
                            <option value="CB">CBE</option>
                            <option value="CV">CEE</option>
                            <option value="EEE">EEE</option>
                            <option value="HSS">HSS</option>
                            <option value="MA">MAE</option>
                            <option value="LKCMedicine">LKCMedicine</option>
                            <option value="MAT">MSE</option>
                            <option value="NBS">NBS</option>
                            <option value="SPMS">SPMS</option>
                            <option value="WKWSCI">WKWSCI</option>
                            <option value="Others">Others</option>
                        </select>
                        </div>
                        </div>
                    </div>
                    <button type="submit" id="submit" class="btn btn-primary">Submit</button>
                    <a class="pull-right" id="adv_search" data-keyboard="false" data-toggle="modal" data-backdrop="static" data-target="#advanced_search_modal" data-toggle="tooltip" data-placement="bottom" title="Choose your preferred free time!">Advanced Search</a>
                </form>

                <div id="loading_icon"></div>
                <div id="result">
                    <nav id="pager_nav" >
                        <ul class="pager">
                            <li><a class="pointer" id="page_prev" title="Keyboard shortcut: k, &larr;">&larr; Previous</a></li>
                            <li><b id="page_number">1</b> of <b id="page_length">42</b></li>
                            <li><a class="pointer" id="page_next" title="Keyboard shortcut: j, &rarr;">Next &rarr;</a></li>
                        </ul>
                    </nav>

                    <div id="calendar_buttons" class="row">
                        <div class="col-xs-12 text-center">
                            <button type="button" class="btn btn-info" data-placement="right" data-toggle="tooltip" title="Download calendar for the shown timetable and import it to Google Calendar/iCalendar!">
                                <span class="glyphicon glyphicon-calendar"></span>
                                Generate Calendar!
                            </button>
                        </div>
                    </div>

                    <div id="tables">
                        <div id="target">

                        </div>
                        <div class="table-responsive">
	                        <table class="table" id="exam_table">
	                            <thead>
	                                <tr>
	                                    <th>Index</th>
	                                    <th>Course</th>
	                                    <th>Title</th>
	                                    <th>AUs</th>
	                                    <th>Exam Schedule</th>
	                                </tr>
	                            </thead>
	                            <tbody id="exam_body">
	                            </tbody>
	                        </table>
                        </div>
                    </div>
                </div>
            </div>

            <footer>
            &copy; <?php echo date("Y"); ?> <a href="http://github.com/edwin-candinegara" target="_blank">Edwin Candinegara</a> &amp; <a href="http://kenrick95.org" target="_blank">Kenrick</a>
            </footer>
        </div>

        <div class="modal fade" id="about_modal" tabindex="-1" role="dialog" aria-labelledby="about_modal_label" aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="about_modal_label">About</h4>
                    </div>
                    <div class="modal-body">
                        <p class="lead">
                            If A*-search returns the optimum result, <b>plan*</b> returns the perfect timetable for your NTU courses!
                        </p>
                        <p>
                            <b>plan*</b> returns you only <b>the possible combination of the courses</b>, so that you don't need to manually do trial-and-error at STARS Planner anymore. Just choose one, input at STARS Planner, and may the force be with you during the STARS Wars.
                        </p>
                        <p>
                            This application was made during exam period of Semester 1, A.Y. 2014-15, by <a href="https://www.linkedin.com/pub/edwin-candinegara/94/45b/19b" target="_blank">Edwin Candinegara</a> &amp; <a href="http://kenrick95.org" target="_blank">Kenrick</a>.
                        </p>
                        <p>
                            <b>Note:</b> Class schedule and exam schedule data is crawled from NTU public server that does not require login for the data access. We do not claim any intellectual property over this data.
                        </p>
                        <h3>
                            Data Protection – Google Analytics Statement
                        </h3>
                        <p>
                            This website uses Google Analytics, a web analytics service provided by Google, Inc. (“Google”). Google Analytics uses “cookies”, which are text files placed on your computer, to help the website analyze how users use the site. The information generated by the cookie about your use of the website (including your IP address) will be transmitted to and stored by Google on servers in the United States . Google will use this information for the purpose of evaluating your use of the website, compiling reports on website activity for website operators and providing other services relating to website activity and internet usage. Google may also transfer this information to third parties where required to do so by law, or where such third parties process the information on Google’s behalf. Google will not associate your IP address with any other data held by Google. You may refuse the use of cookies by selecting the appropriate settings on your browser, however please note that if you do this you may not be able to use the full functionality of this website. By using this website, you consent to the processing of data about you by Google in the manner and for the purposes set out above.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        </div>

		<!-- ADVANCED SEARCH MODAL -->
        <div class="modal fade" id="advanced_search_modal" tabindex="-1" role="dialog" aria-labelledby="advanced_search_modal_label" aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="advanced_search_modal_label">Choose your free time!</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="btn-group">
                                    <button type="button" id="day_button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Monday <span class="caret"></span>
                                    </button>

                                    <ul class="dropdown-menu">
                                        <li><a href="#" data-day="MON">Monday</a></li>
                                        <li><a href="#" data-day="TUE">Tuesday</a></li>
                                        <li><a href="#" data-day="WED">Wednesday</a></li>
                                        <li><a href="#" data-day="THU">Thursday</a></li>
                                        <li><a href="#" data-day="FRI">Friday</a></li>
                                        <li><a href="#" data-day="SAT">Saturday</a></li>
                                    </ul>
                                </div>

                                <button type="button" id="free_time_deselect_all_button" class="btn btn-default pull-right">Deselect All</button>
                                <button type="button" id="free_time_select_all_button" class="btn btn-default pull-right">I want a free day!</button>
                            </div>
                        </div>

                        <div id="free_time_container">
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="0830"> 0830 - 0900</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="0900"> 0900 - 0930</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="0930"> 0930 - 1000</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="1000"> 1000 - 1030</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="1030"> 1030 - 1100</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="1100"> 1100 - 1130</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="1130"> 1130 - 1200</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="1200"> 1200 - 1230</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="1230"> 1230 - 1300</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="1300"> 1300 - 0330</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="1330"> 1330 - 1400</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="1400"> 1400 - 1430</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="1430"> 1430 - 1500</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="1500"> 1500 - 1530</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="1530"> 1530 - 1600</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="1600"> 1600 - 1630</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="1630"> 1630 - 1700</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="1700"> 1700 - 1730</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="1730"> 1730 - 1800</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="1800"> 1800 - 1830</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="1830"> 1830 - 1900</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="1900"> 1900 - 1930</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="1930"> 1930 - 2000</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="2000"> 2000 - 2030</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="2030"> 2030 - 2100</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="2100"> 2100 - 2130</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="2130"> 2130 - 2200</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="2200"> 2200 - 2230</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="2230"> 2230 - 2300</label>
                            </div>
                            <div class="item">
                                <label><input type="checkbox" class="free_time_checkbox" value="2300"> 2300 - 2330</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xs-12">
                            <p id="free_time_selection_text">
                                    The long awaited feature is here! <br>
                                    You can decide when you want to take a break easily! <br>
                                    <strong>As this is a very new feature, please double check with <u>STARS Planner</u> !</strong>
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">Done</button>
                    </div>
                </div>
            </div>
        </div>

        <script src="js/jquery-1.11.1.min.js"></script>
        <script src="js/jquery-migrate-1.2.1.min.js"></script>
        <script src="js/jquery-ui.min.js"></script>
        <script src="js/jquery.tag-it.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <script src="js/sweet-alert.min.js"></script>
        <script src="js/ics.js"></script>
        <script src="js/FileSaver.min.js"></script>
        <script src="js/engine.js"></script>
    </body>
</html>
