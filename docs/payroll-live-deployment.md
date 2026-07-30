# Payroll and roster correction deployment

These changes include four ordered migrations. Take a database backup before
running them on the live server.

## 1. Check migration state

```bash
php artisan migrate:status
```

If the live database shows many old migrations as pending even though their
tables already exist, run only the four migration files below instead of a
general `php artisan migrate`.

## 2. Run the payroll migrations in order

```bash
php artisan migrate --force --path=database/migrations/2026_07_30_000001_create_employee_holidays_table.php
php artisan migrate --force --path=database/migrations/2026_07_30_000002_add_payroll_integrity_fields.php
php artisan migrate --force --path=database/migrations/2026_07_30_000003_repair_riddhi_duplicate_leave.php
php artisan migrate --force --path=database/migrations/2026_07_30_000004_disable_tsa_leave_allotments.php
php artisan optimize:clear
```

The migrations automatically:

- register 6 July 2026 and 16 July 2026 as global employee holidays;
- clear derived absence flags without punches on holidays and before DOJ;
- add salary-period, approved-leave, holiday, late-amount and audit fields;
- add one-to-one leave-application/history protection;
- retain one canonical 5–11 August leave for `NVGI-0007`, deactivate exact
  duplicates and recalculate the employee's leave balance;
- set TSA category leave counts to zero and deactivate TSA-only employee leave
  allotments.

## 3. Verify the live data

```sql
SELECT holiday_date, name, branch_name, category, status
FROM employee_holidays
WHERE holiday_date IN ('2026-07-06', '2026-07-16')
ORDER BY holiday_date;

SELECT la.id, e.employee_no, la.leave_from_date, la.leave_to_date,
       la.no_of_days, la.application_status, la.leave_taken_history_id, la.status
FROM leave_applications la
JOIN employees e ON e.id = la.employee_id
WHERE e.employee_no = 'NVGI-0007'
  AND la.leave_from_date = '2026-08-05'
  AND la.leave_to_date = '2026-08-11'
ORDER BY la.id;

SELECT h.id, h.leave_application_id, h.leave_date, h.leave_count, h.status
FROM employee_leave_taken_histories h
JOIN employees e ON e.id = h.employee_id
WHERE e.employee_no = 'NVGI-0007'
ORDER BY h.id;

SELECT ela.employee_no, ela.total_allotment, ela.used_leave,
       ela.balance_leave, ela.status
FROM employee_leave_allotments ela
WHERE ela.employee_no = 'NVGI-0007'
ORDER BY ela.id;

SELECT e.employee_no, ela.total_allotment, ela.balance_leave, ela.status
FROM employee_leave_allotments ela
JOIN employees e ON e.id = ela.employee_id
WHERE e.category LIKE '%TSA TEACHER%'
ORDER BY e.employee_no, ela.id;
```

For `NVGI-0007`, there should be one active application and one active history
for 5–11 August. The active allotment balance should equal:

```text
total_allotment - sum(active leave history inside the allotment tenure)
```

## 4. Recalculate July salary

Open Salary Generation and search:

- Month: July
- Year: 2026
- Branch: Bibirhat
- Category: VHS TEACHER

Review the rows, select the employees and generate again. Salary generation uses
`updateOrCreate`, so an existing July salary row is recalculated instead of
duplicated. If a salary statement/pay slip was already finalized from the old
calculation, regenerate that statement after regenerating salary.

Useful verification after generation:

```sql
SELECT employee_no, salary_period_start, salary_period_end, eligible_days,
       gross_salary, payable_gross_salary, absent_days, approved_leave_days,
       unpaid_absent_days, holiday_days, pre_doj_excluded_days,
       absent_amount, late_count, late_penalty_units, late_amount, net_salary
FROM salary_generations
WHERE salary_month = 7
  AND salary_year = 2026
  AND branch_name = 'Bibirhat'
  AND employee_category = 'VHS TEACHER'
  AND employee_no IN ('NVGI-0061', 'NVGI-0008', 'NVGI-0060', 'NVGI-0007')
ORDER BY employee_no;
```

`NVGI-0061` must show an eligible period beginning `2026-07-09` and 23 eligible
calendar days. `NVGI-0060` must begin `2026-07-02` and show 30 eligible calendar
days. Holiday and pre-joining roster dates are excluded before absence and late
deductions are calculated.
