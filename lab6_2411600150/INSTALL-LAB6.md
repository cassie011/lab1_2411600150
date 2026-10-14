# Lab 6 – Student / Teacher Academic Tracking (Student Portal)

All inventory code (products, stock transactions) is removed.
Lab 6 now tracks grades and attendance with Admin, Teacher and Student logins.

## 1. Back up
Copy your whole project folder (e.g. `lab6_2411600150_backup`).

## 2. Delete these old folders in your project
- `app`
- `resources`
- `routes`
- `tests`
- `database\migrations`
- `database\seeders`
- `database\factories`
- `public\js` (had the old inventory-dashboard.js)

Do NOT delete: `vendor`, `node_modules`, `bootstrap`, `config`, `storage`, `public\index.php`, `public\build`, `.env`.

## 3. Copy in the new files
Extract this zip and copy everything into your project folder. Say Replace if asked.

## 4. Rebuild the database
```
php artisan migrate:fresh --seed
php artisan optimize:clear
php artisan serve
```
`migrate:fresh` deletes all tables (including the old products tables) and rebuilds them.

## 5. Sample accounts (password: `password`)
| Email | Role | Can do |
|---|---|---|
| test@example.com | Admin | everything + delete grades/attendance |
| teacher@example.com | Teacher | students, grades, attendance, reports |
| student@example.com | Student | only own grades and attendance (Reahmeil Perocillo) |

New accounts: Register page, pick a role. Students must enter an existing Student ID (e.g. 2411600001).

## 6. Lab 6 requirements in this project
| Lab 6 requirement | In this project |
|---|---|
| Transaction model + migration | `Grade` and `AttendanceRecord` (belong to Student and User) |
| Observer auto-updates | `GradeObserver` recalculates GPA, `AttendanceRecordObserver` recalculates attendance rate |
| Reject invalid entries (Option A) | invalid grade, future date, duplicate grade are rejected and rolled back |
| DB::transaction | saving grades, whole-class attendance (all-or-nothing), deletes |
| Low-level alert notification | `StudentAtRiskNotification` when GPA < 3.00 or attendance < 80% (database + queued mail, sent once) |
| Bell icon + mark as read | header bell for all roles |
| API endpoints | `/api/dashboard/stats`, `/api/students/at-risk`, `/api/activity/recent` |
| AJAX polling (30s) | `public/js/academic-dashboard.js` |
| Adjustment | grade correction (Edit Grade) and retaking attendance for a day |
| History + running balance | Student page: grades + attendance history with running attendance rate + date filter |
| Reports + CSV | Attendance Summary, Grade Report, At-Risk Students |
| Admin only | delete grades and attendance records |

## 7. Test
Automatic: `php artisan test --filter=AcademicTrackingTest` (20 tests)

Manual:
1. Login as teacher → Take Attendance → BS Information Technology → mark someone Absent → Save. Their rate drops.
2. Grades → Record Grade → Diana, grade 0.50, 6 units → GPA drops below 3.00 → bell shows an alert.
3. Record the same subject + term again → rejected.
4. Keep Dashboard open in one tab, record a grade in another → within 30 seconds the cards and lists update.
5. Login as student@example.com → only "My Records"; opening /students gives 403.
6. Teacher: no delete buttons. Admin: can delete; GPA / rate is recalculated.

Optional email alerts: run `php artisan queue:work` (emails go to storage/logs/laravel.log).
