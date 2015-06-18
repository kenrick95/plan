<!DOCTYPE HTML>
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
                            <option value="SCE">SCE</option>
                            <option value="MAT">MSE</option>
                            <option value="MAE">MAE</option>
                            <option value="CBE">CBE</option>
                            <option value="EEE">EEE</option>
                            <option value="CV">CEE</option>
                        </select>
                        </div>
                        </div>
                    </div>
                    <button type="submit" id="submit" class="btn btn-primary">Submit</button>
                    <!-- <a class="pull-right" id="adv-search" data-backdrop="static" data-keyboard="false" data-toggle="modal" data-target="#advanced_search_modal">Advanced Search</a> -->
                </form>
                <div id="loading_icon"></div>
                <div id="result">
                    <nav id="pager_nav" >
                        <ul class="pager">
                            <li><a class="pointer" id="page_prev">Previous</a></li>
                            <li><b id="page_number">1</b> of <b id="page_length">42</b></li>
                            <li><a class="pointer" id="page_next">Next</a></li>
                        </ul>
                    </nav>
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
						<!--
                        <p>
                            By using this application, you agree that [INSERT SOME LEGAL CLAUSE HERE].
                        </p>
						-->
                        <p>
                            This application was made during exam period of Semester 1, A.Y. 2014-15, by <a href="https://www.linkedin.com/pub/edwin-candinegara/94/45b/19b" target="_blank">Edwin Candinegara</a> &amp; <a href="http://kenrick95.org" target="_blank">Kenrick</a>.
                        </p>
                        <p>
                            <b>Note:</b> Class schedule and exam schedule data is crawled from NTU public server that does not require login for accessing data. We do not claim any intellectual property over this data.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
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
        <script src="js/engine.js"></script>
    </body>
</html>
