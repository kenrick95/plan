# Contributions

The authors of this project have graduated from university and hence would no longer develop new features for this project.

I (@kenrick95) would be happy to update the data from time to time, but you are also welcome to send a pull request to update the data.

## How to update data?

This assumes you have a clone of the repository locally.

### When a new semester came up

1. Read NTU's published academic calendar PDF and add the new semester's four dates to `academic-calendar.json` in `YYYY-MM-DD` format. Keep the previous entries; the updater selects the latest year and semester.
2. Commit and push the calendar change. The scheduled update runs each Monday at 04:17 UTC, or you can start **Update NTU data** from GitHub Actions immediately. It fetches course and exam timetables, validates them, updates the site defaults, commits the resulting files, and deploys them. A failed fetch or parse stops before publishing; check the Actions log and retry when NTU has published the data.

### Refresh the current semester

Run **Update NTU data** from GitHub Actions. It also runs weekly to capture changes to the published course and exam schedules. Locally, with PHP CLI, curl and XML extensions installed, run `php scripts/update_data.php`.

### Manual fallback

1. Serve the project locally:
  ```sh
  php -S localhost:8000
  ```
2. Open browser and navigate to `http://localhost:8000/back_end/`
3. Input the year and semester
4. Leave `plan_no` empty to detect it automatically. If detection fails, inspect the `p_plan_no` radio value for that year and semester at https://wis.ntu.edu.sg/webexe/owa/exam_timetable_und.Main and enter it manually.
5. Click "Get + parse"
6. Wait till all done
   - If anything goes wrong, please file an issue.
7. Commit your changes and send a pull request.
