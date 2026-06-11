<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeScheduleRoster;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class BranchPortalController extends Controller
{
    private const FULL_MONTH_ROSTER_CATEGORIES = ['TSA TEACHER', 'FRONT-DESK', 'GROUP-D'];
    private const BRANCH_COLOR_MAP = [
        'bibirhat' => ['class' => 'violet', 'label' => 'Bibirhat', 'color' => '#c7b7ff'],
        'mukundapur' => ['class' => 'yellow', 'label' => 'Mukundapur', 'color' => '#ffed9d'],
        'rajarhat' => ['class' => 'light-green', 'label' => 'Rajarhat', 'color' => '#b7f3c8'],
    ];

    public function showLogin(Request $request)
    {
        if ($request->session()->has('branch_portal.branch_id')) {
            return redirect()->route('branch.portal.employees');
        }

        return view('front.pages.branch-portal.login', [
            'title' => 'Branch Employee Login',
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);

        $username = trim((string) $credentials['username']);
        $rateLimitKey = Str::lower($username) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            return redirect()
                ->back()
                ->withInput($request->only('username'))
                ->with('error_message', 'Too many login attempts. Please try again in ' . $seconds . ' seconds.');
        }

        $branch = Branch::whereRaw('LOWER(serial_id) = ?', [Str::lower($username)])
            ->where('status', '=', 1)
            ->whereNull('deleted_at')
            ->first();

        if (!$branch || empty($branch->password) || !Hash::check($credentials['password'], $branch->password)) {
            RateLimiter::hit($rateLimitKey, 60);

            return redirect()
                ->back()
                ->withInput($request->only('username'))
                ->with('error_message', 'Invalid username or password.');
        }

        RateLimiter::clear($rateLimitKey);
        $request->session()->regenerate();
        $request->session()->put('branch_portal', [
            'branch_id' => (int) $branch->id,
            'unit_id' => (int) $branch->unit_id,
            'serial_id' => (string) $branch->serial_id,
            'branch_name' => (string) $branch->name,
        ]);

        return redirect()
            ->route('branch.portal.employees')
            ->with('success_message', 'Welcome to the branch employee directory.');
    }

    public function employees(Request $request)
    {
        $branch = $request->attributes->get('branch_portal');
        $centreBranchIds = $this->centreBranchIds($branch);

        $rows = Employee::where('status', '!=', 3)
            ->where(function ($query) use ($centreBranchIds) {
                foreach ($centreBranchIds as $branchId) {
                    $query->orWhereJsonContains('branch', $branchId);
                }
            })
            ->orderBy('first_name', 'ASC')
            ->orderBy('last_name', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->map(function ($employee) {
                $employee->employee_name = $this->buildEmployeeName($employee);

                return $employee;
            });

        return view('front.pages.branch-portal.employees', [
            'title' => 'Branch Employee Directory',
            'branch' => $branch,
            'centre_branch_ids' => $centreBranchIds,
            'rows' => $rows,
        ]);
    }

    public function rosters(Request $request)
    {
        $branch = $request->attributes->get('branch_portal');
        $centreBranchIds = $this->centreBranchIds($branch);
        $targetMonth = $this->resolveRosterMonth($request->input('month', Carbon::now()->format('Y-m')));
        $category = $this->resolveRosterCategory($request->input('category', ''));
        $employeeId = (int) $request->input('employee_id', 0);
        $dateCategories = $this->selectedRosterDateCategories($category);

        $employeeOptions = $this->branchEmployees($centreBranchIds, $category)
            ->map(function ($employee) {
                return [
                    'id' => (int) $employee->id,
                    'employee_no' => (string) $employee->employee_no,
                    'employee_name' => $employee->employee_name,
                    'label' => trim((string) $employee->employee_no . ' - ' . $employee->employee_name),
                ];
            })
            ->values()
            ->all();

        $rows = $this->branchRosterRows($targetMonth, $centreBranchIds, $category, $employeeId);

        return view('front.pages.branch-portal.rosters', [
            'title' => 'Branch Schedule Roster',
            'branch' => $branch,
            'centre_branch_ids' => $centreBranchIds,
            'categories' => $this->rosterCategories(),
            'selected_month_value' => $targetMonth->format('Y-m'),
            'selected_month_label' => $targetMonth->format('F Y'),
            'selected_category' => $category,
            'selected_employee_id' => $employeeId,
            'month_options' => $this->monthOptions($targetMonth),
            'employee_options' => $employeeOptions,
            'calendar_dates' => $this->calendarDates(
                $targetMonth,
                $dateCategories,
                (string) $branch->name
            ),
            'calendar_groups' => $this->buildRosterCalendarGroups($rows, $targetMonth),
            'branch_color_legend' => $this->branchColorLegend(),
            'stats' => [
                'rows' => $rows->count(),
                'employees' => $rows->pluck('employee_id')->unique()->count(),
                'categories' => $rows->pluck('category')->unique()->count(),
                'dates' => $rows->pluck('roster_date')->unique()->count(),
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->session()->forget('branch_portal');
        $request->session()->regenerateToken();

        return redirect()
            ->route('branch.portal.login')
            ->with('success_message', 'You have been logged out successfully.');
    }

    private function buildEmployeeName($employee): string
    {
        return collect([
            $employee->first_name,
            $employee->middle_name,
            $employee->last_name,
        ])->map(function ($namePart) {
            return trim((string) $namePart);
        })->filter()->implode(' ');
    }

    private function centreBranchIds($branch): array
    {
        return Branch::whereRaw('LOWER(name) = ?', [Str::lower(trim((string) $branch->name))])
            ->where('status', '=', 1)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->map(function ($branchId) {
                return (int) $branchId;
            })
            ->values()
            ->all();
    }

    private function branchEmployees(array $centreBranchIds, string $category = '')
    {
        return Employee::where('status', '!=', 3)
            ->where(function ($query) use ($centreBranchIds) {
                foreach ($centreBranchIds as $branchId) {
                    $query->orWhereJsonContains('branch', $branchId);
                }
            })
            ->orderBy('employee_no', 'ASC')
            ->orderBy('first_name', 'ASC')
            ->orderBy('last_name', 'ASC')
            ->get()
            ->map(function ($employee) {
                $employee->employee_name = $this->buildEmployeeName($employee);

                return $employee;
            })
            ->filter(function ($employee) use ($category) {
                return $category === '' || in_array($category, $this->employeeCategoryValues($employee->category), true);
            })
            ->values();
    }

    private function branchRosterRows(Carbon $targetMonth, array $centreBranchIds, string $category = '', int $employeeId = 0)
    {
        if (empty($centreBranchIds)) {
            return collect();
        }

        $query = EmployeeScheduleRoster::whereIn('branch_id', $centreBranchIds)
            ->whereIn('category', $this->rosterCategories())
            ->where('roster_month', '=', (int) $targetMonth->month)
            ->where('roster_year', '=', (int) $targetMonth->year)
            ->where('status', '!=', 3);

        if ($category !== '') {
            $query->where('category', '=', $category);
        }

        if ($employeeId > 0) {
            $query->where('employee_id', '=', $employeeId);
        }

        return $query
            ->orderBy('category', 'ASC')
            ->orderBy('employee_no', 'ASC')
            ->orderBy('roster_date', 'ASC')
            ->get();
    }

    private function buildRosterCalendarGroups($rows, Carbon $targetMonth): array
    {
        return $rows
            ->groupBy('category')
            ->map(function ($groupRows, $category) use ($targetMonth) {
                $calendarData = $this->buildCalendarData($groupRows, $targetMonth, [(string) $category]);

                return [
                    'category' => (string) $category,
                    'rows' => $groupRows->count(),
                    'employees_count' => $groupRows->pluck('employee_id')->unique()->count(),
                    'employees' => $calendarData['employees'],
                    'cells' => $calendarData['cells'],
                ];
            })
            ->values()
            ->all();
    }

    private function buildCalendarData($rows, Carbon $targetMonth, ?array $dateCategories = null): array
    {
        $employees = $rows
            ->groupBy('employee_id')
            ->map(function ($employeeRows) {
                $firstRow = $employeeRows->first();

                return [
                    'id' => (int) $firstRow->employee_id,
                    'employee_no' => (string) $firstRow->employee_no,
                    'employee_name' => (string) $firstRow->employee_name,
                ];
            })
            ->sortBy('employee_no', SORT_NATURAL)
            ->values()
            ->all();

        $cells = [];
        foreach ($rows as $row) {
            $employeeId = (int) $row->employee_id;
            $dateKey = Carbon::parse($row->roster_date)->toDateString();

            if (!isset($cells[$employeeId])) {
                $cells[$employeeId] = [];
            }

            if (!isset($cells[$employeeId][$dateKey])) {
                $cells[$employeeId][$dateKey] = [];
            }

            $cells[$employeeId][$dateKey][] = [
                'branch_name' => (string) $row->branch_name,
                'branch_code' => $this->branchCode($row->branch_name),
                'unit_name' => (string) $row->unit_name,
                'time_display' => $this->displayTimeRange($row->in_time, $row->out_time),
                'shift_class' => $this->branchColorClass($row->branch_name),
            ];
        }

        return [
            'dates' => $this->calendarDates($targetMonth, $dateCategories),
            'employees' => $employees,
            'cells' => $cells,
        ];
    }

    private function calendarDates(
        Carbon $targetMonth,
        ?array $dateCategories = null,
        string $branchName = ''
    ): array
    {
        return $this->monthDates($targetMonth)
            ->map(function ($date) use ($targetMonth, $dateCategories, $branchName) {
                $isRosterWorkingDate = $this->isVhsOnlyDateCategory($dateCategories)
                    ? $this->isVhsWorkingDate($date, $branchName)
                    : $this->isRosterWorkingDate($date, $dateCategories);

                return [
                    'date' => $date->toDateString(),
                    'day_label' => $date->format('l'),
                    'day_initial' => strtoupper(substr($date->format('D'), 0, 1)),
                    'date_label' => $date->format('j'),
                    'month_label' => $date->format('M'),
                    'is_current_month' => $date->month === $targetMonth->month && $date->year === $targetMonth->year,
                    'is_today' => $date->isToday(),
                    'is_saturday' => $date->isSaturday(),
                    'is_roster_working_date' => $isRosterWorkingDate,
                    'is_skipped_date' => !$isRosterWorkingDate,
                ];
            })
            ->values()
            ->all();
    }

    private function isVhsWorkingDate(Carbon $date, string $branchName): bool
    {
        if ($date->isSunday()) {
            return false;
        }

        if (!$date->isSaturday()) {
            return true;
        }

        $saturdayNumber = (int) ceil($date->day / 7);

        return !in_array($saturdayNumber, [2, 4], true)
            || strcasecmp($branchName, 'Rajarhat') === 0;
    }

    private function isVhsOnlyDateCategory(?array $dateCategories): bool
    {
        $dateCategories = $this->normalizeRosterDateCategories($dateCategories ?? []);

        return count($dateCategories) === 1 && $dateCategories[0] === 'VHS TEACHER';
    }

    private function monthDates(Carbon $targetMonth)
    {
        return collect(CarbonPeriod::create($targetMonth->copy()->startOfMonth(), $targetMonth->copy()->endOfMonth()))
            ->map(function ($date) {
                return $date->copy();
            })
            ->values();
    }

    private function rosterDates(Carbon $targetMonth, ?array $dateCategories = null)
    {
        return $this->monthDates($targetMonth)
            ->filter(function ($date) use ($dateCategories) {
                return $this->isRosterWorkingDate($date, $dateCategories);
            })
            ->values();
    }

    private function isRosterWorkingDate(Carbon $date, ?array $dateCategories = null): bool
    {
        if ($this->hasFullMonthRosterCategory($dateCategories)) {
            return true;
        }

        if ($date->isSunday()) {
            return false;
        }

        if ($date->isSaturday()) {
            $saturdayNumber = (int) ceil($date->day / 7);

            return !in_array($saturdayNumber, [2, 4], true);
        }

        return true;
    }

    private function selectedRosterDateCategories(string $category): array
    {
        return $category !== '' ? [$category] : $this->rosterCategories();
    }

    private function hasFullMonthRosterCategory(?array $dateCategories): bool
    {
        foreach ($this->normalizeRosterDateCategories($dateCategories ?? []) as $category) {
            if (in_array($category, self::FULL_MONTH_ROSTER_CATEGORIES, true)) {
                return true;
            }
        }

        return false;
    }

    private function normalizeRosterDateCategories(array $dateCategories): array
    {
        return collect($dateCategories)
            ->map(function ($category) {
                return trim((string) $category);
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function branchColorLegend(): array
    {
        return array_values(self::BRANCH_COLOR_MAP);
    }

    private function branchColorClass($branchName): string
    {
        $branchKey = strtolower(trim((string) $branchName));

        return self::BRANCH_COLOR_MAP[$branchKey]['class'] ?? 'neutral';
    }

    private function resolveRosterMonth($monthValue): Carbon
    {
        $monthValue = trim((string) $monthValue);
        if (!preg_match('/^\d{4}-\d{2}$/', $monthValue)) {
            $monthValue = Carbon::now()->format('Y-m');
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $monthValue . '-01')->startOfMonth();
        } catch (\Throwable $e) {
            return Carbon::now()->startOfMonth();
        }
    }

    private function monthOptions(Carbon $targetMonth): array
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $months = collect([
            $currentMonth->copy(),
            $currentMonth->copy()->addMonth(),
            $targetMonth->copy()->startOfMonth(),
        ]);

        return $months->unique(function ($date) {
            return $date->format('Y-m');
        })->sortBy(function ($date) {
            return $date->format('Y-m');
        })->map(function ($date) {
            return [
                'value' => $date->format('Y-m'),
                'label' => $date->format('F Y'),
            ];
        })->values()->all();
    }

    private function resolveRosterCategory($category): string
    {
        $category = trim((string) $category);

        if ($category === '' || strtoupper($category) === 'ALL') {
            return '';
        }

        foreach ($this->rosterCategories() as $rosterCategory) {
            if (strcasecmp($category, $rosterCategory) === 0) {
                return $rosterCategory;
            }
        }

        return '';
    }

    private function rosterCategories(): array
    {
        return ['VHS TEACHER', 'TSA TEACHER', 'FRONT-DESK', 'GROUP-D'];
    }

    private function employeeCategoryValues($category): array
    {
        $decodedCategories = json_decode((string) $category, true);
        $categories = is_array($decodedCategories) ? $decodedCategories : [$category];
        $categories = array_map(function ($categoryValue) {
            return trim((string) $categoryValue);
        }, $categories);

        return array_values(array_filter($categories, function ($categoryValue) {
            return $categoryValue !== '';
        }));
    }

    private function displayTimeRange($inTime, $outTime): string
    {
        $inTime = trim((string) $inTime);
        $outTime = trim((string) $outTime);

        if ($inTime === '' || $outTime === '') {
            return '';
        }

        try {
            return Carbon::createFromFormat('H:i', substr($inTime, 0, 5))->format('g:i a')
                . ' - '
                . Carbon::createFromFormat('H:i', substr($outTime, 0, 5))->format('h:i a');
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function branchCode($branchName): string
    {
        $branchName = preg_replace('/[^A-Za-z0-9]/', '', (string) $branchName);

        return strtoupper(substr($branchName, 0, 3));
    }
}
