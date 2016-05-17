plan*
----

A PHP-based application that help students of NTU (Nanyang Technological University) to plan their courses.

The application do web-scraping of course data from NTU public serverZZ, parses the data and stores them into meaningful content, and then do the scheduling of the courses based on user input.

### Back-end Architecture
* Getter ([getter.php](https://github.com/kenrick95/plan/blob/master/back_end/getter.php))
  - fetch data from NTU's server ([course schedule](http://wish.wis.ntu.edu.sg/webexe/owa/aus_schedule.main) <sub>([directly to this](http://wish.wis.ntu.edu.sg/webexe/owa/AUS_SCHEDULE.main_display1))</sub> and [exam schedule](http://wis.ntu.edu.sg/webexe/owa/exam_timetable_und.main))
  - store the HTML file locally
* Parser ([parse.php](https://github.com/kenrick95/plan/blob/master/back_end/parser/parse.php) and [parse_exam.php](https://github.com/kenrick95/plan/blob/master/back_end/parser/parse_exam.php))
  - clean and parse the HTML file (read it as XML) :persevere: 
  - store the PHP object as JSON file
* Scheduler ([scheduler.php](https://github.com/kenrick95/plan/blob/master/back_end/scheduler.php))
  - fecth locally-stored JSON file
  - convert as PHP object
  - do scheduling as requested from user input

