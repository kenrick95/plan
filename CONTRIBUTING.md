# Contributions

The authors of this project have graduated from university and hence would no longer develop new features for this project.

I (@kenrick95) would be happy to update the data from time to time, but you are also welcome to send a pull request to update the data.

## How to update data?

This assumes you have a clone of the repository locally.

### When a new semester came up

1. Update `back_end/config.php` to the correct year and semester.
2. Update `js/engine.js` to use the new semester's academic calendar dates, specifically these four variables:
   - `ACADEMIC_START_DATE`
   - `ACADEMIC_END_DATE`
   - `ACADEMIC_RECESS_START_DATE`
   - `ACADEMIC_RECESS_END_DATE`
3. Proceed to steps below

### On the current semester

1. Serve the project locally:
  ```sh
  php -S localhost:8000
  ```
2. Open browser and navigate to `http://localhost:8000/back_end/`
3. Input the year and semester
4. Input the plan_no. Plan_no is a bit tricky, as you need to inspect the value of `p_plan_no` from this NTU's page: https://wis.ntu.edu.sg/webexe/owa/exam_timetable_und.Main
   - Select "General Access", click Next
   - Open browser console.
   - Inspect the HTML, find the radio button for the year/semester's option. Use the value in the attribute "value". As an example: `<input type="radio" name="p_plan_no" value="123">` (means the "plan_no" is "123)
5. Click "Get + parse"
6. Wait till all done
   - If anything goes wrong, please file an issue.
7. Commit your changes and send a pull request.
