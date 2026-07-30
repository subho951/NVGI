<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Branch;
use App\Models\EmployeeHoliday;
use App\Services\EmployeeAttendanceAbsenceService;
use App\Services\SiteAuthService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HolidayController extends Controller
{
    private const CATEGORIES = [
        'VHS TEACHER',
        'FRONT-DESK',
        'GROUP-D',
        'TSA TEACHER',
    ];

    protected $siteAuthService;

    protected $data;

    public function __construct()
    {
        $this->data = [
            'title' => 'Holiday Management',
            'controller' => 'HolidayController',
            'controller_route' => 'holiday',
            'primary_key' => 'id',
        ];

        $this->siteAuthService = new SiteAuthService();
    }

    public function list(Request $request)
    {
        $month = $this->resolveMonth($request->input('month'));
        $data['module'] = $this->data;
        $title = $this->data['title'];
        $pageName = 'holiday.list';
        $data['rows'] = EmployeeHoliday::where('status', '!=', 3)
            ->whereDate('holiday_date', '>=', $month->copy()->startOfMonth()->toDateString())
            ->whereDate('holiday_date', '<=', $month->copy()->endOfMonth()->toDateString())
            ->orderBy('holiday_date')
            ->orderBy('branch_name')
            ->orderBy('category')
            ->orderBy('id')
            ->get();
        $data['selectedMonth'] = $month->format('Y-m');
        $data['selectedMonthLabel'] = $month->format('F Y');
        $data['activeHolidayCount'] = $data['rows']->where('status', 1)->count();
        $data['action'] = 'List';

        $data = $this->siteAuthService->admin_after_login_layout($title, $pageName, $data);

        return view('front.pages.'.$pageName, $data);
    }

    public function add(Request $request)
    {
        $data = $this->formData('Add', null, $request->input('month'));

        if ($request->isMethod('post')) {
            $request->validate($this->validationRules());
            $payload = $this->payload($request);
            $this->validateUniqueScope($payload);

            $holiday = EmployeeHoliday::create($payload + [
                'status' => 1,
                'created_by' => $this->currentUserId(),
                'updated_by' => $this->currentUserId(),
            ]);

            $this->syncHolidayAttendance($holiday->holiday_date, $holiday->branch_name);

            return redirect($this->listUrl($holiday->holiday_date))
                ->with('success_message', 'Holiday added successfully.');
        }

        $data = $this->siteAuthService->admin_after_login_layout(
            $data['title'],
            $data['pageName'],
            $data
        );

        return view('front.pages.'.$data['pageName'], $data);
    }

    public function edit(Request $request, $id)
    {
        $holiday = EmployeeHoliday::where($this->data['primary_key'], '=', Helper::decoded($id))
            ->where('status', '!=', 3)
            ->first();

        if (! $holiday) {
            return redirect($this->data['controller_route'].'/list')
                ->with('error_message', 'Holiday not found.');
        }

        $data = $this->formData(
            'Edit',
            $holiday,
            $holiday->holiday_date->format('Y-m')
        );

        if ($request->isMethod('post')) {
            $request->validate($this->validationRules());
            $payload = $this->payload($request);
            $this->validateUniqueScope($payload, (int) $holiday->id);
            $oldDate = $holiday->holiday_date;
            $oldBranchName = $holiday->branch_name;

            $holiday->update($payload + [
                'updated_by' => $this->currentUserId(),
            ]);

            $this->syncHolidayAttendance($oldDate, $oldBranchName);
            $this->syncHolidayAttendance($holiday->holiday_date, $holiday->branch_name);

            return redirect($this->listUrl($holiday->holiday_date))
                ->with('success_message', 'Holiday updated successfully.');
        }

        $data = $this->siteAuthService->admin_after_login_layout(
            $data['title'],
            $data['pageName'],
            $data
        );

        return view('front.pages.'.$data['pageName'], $data);
    }

    public function change_status(Request $request, $id)
    {
        $holiday = EmployeeHoliday::where($this->data['primary_key'], '=', Helper::decoded($id))
            ->where('status', '!=', 3)
            ->first();

        if (! $holiday) {
            return redirect($this->data['controller_route'].'/list')
                ->with('error_message', 'Holiday not found.');
        }

        if ((int) $holiday->status === 1) {
            $holiday->status = 0;
            $message = 'Holiday deactivated successfully.';
        } else {
            $this->validateUniqueScope([
                'holiday_date' => $holiday->holiday_date,
                'branch_name' => $holiday->branch_name,
                'category' => $holiday->category,
            ], (int) $holiday->id);
            $holiday->status = 1;
            $message = 'Holiday activated successfully.';
        }

        $holiday->updated_by = $this->currentUserId();
        $holiday->save();
        $this->syncHolidayAttendance($holiday->holiday_date, $holiday->branch_name);

        return redirect($this->listUrl($holiday->holiday_date))
            ->with('success_message', $message);
    }

    public function delete(Request $request, $id)
    {
        $holiday = EmployeeHoliday::where($this->data['primary_key'], '=', Helper::decoded($id))
            ->where('status', '!=', 3)
            ->first();

        if (! $holiday) {
            return redirect($this->data['controller_route'].'/list')
                ->with('error_message', 'Holiday not found.');
        }

        $holiday->update([
            'status' => 3,
            'updated_by' => $this->currentUserId(),
        ]);
        $this->syncHolidayAttendance($holiday->holiday_date, $holiday->branch_name);

        return redirect($this->listUrl($holiday->holiday_date))
            ->with('success_message', 'Holiday deleted successfully.');
    }

    private function formData(string $action, $row, $monthValue): array
    {
        $month = $this->resolveMonth($monthValue);

        return [
            'module' => $this->data,
            'title' => $this->data['title'].' '.$action,
            'pageName' => 'holiday.add-edit',
            'row' => $row,
            'action' => $action,
            'selectedMonth' => $month->format('Y-m'),
            'branchOptions' => $this->branchOptions(),
            'categoryOptions' => self::CATEGORIES,
        ];
    }

    private function validationRules(): array
    {
        return [
            'holiday_date' => ['required', 'date'],
            'name' => ['required', 'string', 'max:160'],
            'branch_name' => [
                'nullable',
                'string',
                Rule::in($this->branchOptions()),
            ],
            'category' => [
                'nullable',
                'string',
                Rule::in(self::CATEGORIES),
            ],
        ];
    }

    private function payload(Request $request): array
    {
        return [
            'holiday_date' => Carbon::parse($request->holiday_date)->toDateString(),
            'name' => trim((string) $request->name),
            'branch_name' => $this->nullableText($request->branch_name),
            'category' => $this->nullableText($request->category),
        ];
    }

    private function validateUniqueScope(array $payload, int $excludeId = 0): void
    {
        $query = EmployeeHoliday::whereDate(
            'holiday_date',
            '=',
            Carbon::parse($payload['holiday_date'])->toDateString()
        )
            ->where('status', '!=', 3);

        foreach (['branch_name', 'category'] as $scopeColumn) {
            $scopeValue = $this->nullableText($payload[$scopeColumn] ?? null);

            if ($scopeValue === null) {
                $query->whereNull($scopeColumn);
            } else {
                $query->where($scopeColumn, '=', $scopeValue);
            }
        }

        if ($excludeId > 0) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'holiday_date' => 'A holiday already exists for the selected date, branch and category scope.',
            ]);
        }
    }

    private function resolveMonth($value): Carbon
    {
        $value = trim((string) $value);

        if (! preg_match('/^\d{4}-\d{2}$/', $value)) {
            return now()->startOfMonth();
        }

        try {
            $month = Carbon::createFromFormat('!Y-m', $value);

            return $month && $month->format('Y-m') === $value
                ? $month->startOfMonth()
                : now()->startOfMonth();
        } catch (\Throwable $e) {
            return now()->startOfMonth();
        }
    }

    private function branchOptions(): array
    {
        return Branch::where('status', '!=', 3)
            ->orderBy('name')
            ->pluck('name')
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function syncHolidayAttendance($date, $branchName): void
    {
        if (
            ! Schema::hasTable('employee_schedule_rosters')
            || ! Schema::hasTable('employee_attendances')
        ) {
            return;
        }

        try {
            $branchIds = [];
            $branchName = $this->nullableText($branchName);

            if ($branchName !== null) {
                $branchIds = Branch::where('status', '!=', 3)
                    ->where('name', '=', $branchName)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->all();
            }

            app(EmployeeAttendanceAbsenceService::class)->sync(
                Carbon::parse($date)->startOfDay(),
                Carbon::parse($date)->startOfDay(),
                $branchIds
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function listUrl($date): string
    {
        return $this->data['controller_route'].'/list?month='
            .Carbon::parse($date)->format('Y-m');
    }

    private function nullableText($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function currentUserId(): int
    {
        if (session()->has('user_data') && array_key_exists('user_id', session('user_data'))) {
            return (int) session('user_data')['user_id'];
        }

        return Auth::check() ? (int) Auth::id() : 0;
    }
}
