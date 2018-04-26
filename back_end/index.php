<?php
require("config.php");
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
        <link rel="stylesheet" href="../css/jquery-ui.min.css">
        <link rel="stylesheet" href="../css/jquery-ui.theme.min.css">
        <link rel="stylesheet" href="../css/bootstrap.min.css">
        <link rel="stylesheet" href="../css/sweet-alert.css">
        <!--<link rel="stylesheet" href="css/bootstrap-theme.min.css">-->
        <link rel="stylesheet" href="../css/style.css">
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
                        <a class="navbar-brand pointer">plan* Dashboard</a>
                    </div>
                    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                        <ul class="nav navbar-nav">
                            <li class="active"><a class="pointer">Home</a></li>
                        </ul>
                    </div>
                </div>
        </nav>
        <div id="main" class="container">
            <div class="row">
            <form id="select_form" class="form-horizontal col-sm-6">
                <h2>Settings</h2>
                <div class="form-group">
                    <label for="year" class="col-sm-2 control-label">Year</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" id="year" placeholder="Year" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="semester" class="col-sm-2 control-label">Semester</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" id="semester" placeholder="Semester" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="semester" class="col-sm-2 control-label">Plan_no</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" id="plan_no" placeholder="Plan_no" required>
                        Get <code>plan_no</code> manually <a href="https://wis.ntu.edu.sg/webexe/owa/exam_timetable_und.main">here</a>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                    <button id="submit" type="submit" class="btn btn-primary">Get + Parse!</button>
                    </div>
                </div>
            </form>
            <div class="col-sm-6">
                <h2>Log</h2>
                <div id="target" class="well">

                </div>
            </div>

            </div>

            <footer>
            &copy; <?php echo date("Y"); ?> <a href="http://github.com/edwin-candinegara" target="_blank">Edwin Candinegara</a> &amp; <a href="http://kenrick95.org" target="_blank">Kenrick</a>
            </footer>
        </div>

        <script src="../js/jquery-1.12.4.min.js"></script>
        <script src="../js/jquery-migrate-1.4.1.min.js"></script>
        <script src="../js/jquery-ui.min.js"></script>
        <script src="../js/bootstrap.min.js"></script>
        <script src="../js/sweet-alert.min.js"></script>
        <script src="../js/admin.js"></script>
    </body>
</html>
