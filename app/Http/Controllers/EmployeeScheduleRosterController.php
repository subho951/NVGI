<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeScheduleRoster;
use App\Services\SiteAuthService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeScheduleRosterController extends Controller
{
    private const VHS_TEACHER = 'VHS TEACHER';
    private const TSA_TEACHER = 'TSA TEACHER';
    private const VHS_CATEGORIES = ['VHS TEACHER'];
    private const SUPPORT_CATEGORIES = ['FRONT-DESK', 'GROUP-D'];
    private const TSA_CATEGORIES = ['TSA TEACHER'];
    private const ALL_ROSTER_CATEGORIES = ['VHS TEACHER', 'TSA TEACHER', 'FRONT-DESK', 'GROUP-D'];
    private const FULL_MONTH_ROSTER_CATEGORIES = ['TSA TEACHER', 'FRONT-DESK', 'GROUP-D'];
    private const SUPPORT_BRANCH_NAMES = ['Bibirhat', 'Mukundapur', 'Rajarhat'];
    private const BRANCH_COLOR_MAP = [
        'bibirhat' => ['class' => 'violet', 'label' => 'Bibirhat', 'color' => '#c7b7ff'],
        'mukundapur' => ['class' => 'yellow', 'label' => 'Mukundapur', 'color' => '#ffed9d'],
        'rajarhat' => ['class' => 'light-green', 'label' => 'Rajarhat', 'color' => '#b7f3c8'],
    ];

    protected $siteAuthService;
    protected $data;

    public function __construct()
    {
        $this->data = [
            'title' => 'Schedule Roster',
            'controller' => 'EmployeeScheduleRosterController',
            'controller_route' => 'employee/schedule-roster/vhs',
            'primary_key' => 'id',
        ];
        $this->siteAuthService = new SiteAuthService();
    }

    public function vhs(Request $request)
    {
        $targetMonth = $this->resolveRosterMonth($request);
        $selectedMonthValue = $targetMonth->format('Y-m');
        $generationResult = null;

        if ($request->isMethod('post')) {
            $generationResult = $this->generateRosterForCategory(self::VHS_TEACHER, $targetMonth);

            return redirect()
                ->to(url('employee/schedule-roster/vhs') . '?month=' . $selectedMonthValue)
                ->with('success_message', $this->buildGenerationMessage($generationResult));
        }

        $selectedBranchId = $this->positiveInt($request->input('branch_id'));
        $selectedEmployeeId = $this->positiveInt($request->input('employee_id'));
        $rows = $this->getRosterRows(self::VHS_TEACHER, $targetMonth, $selectedBranchId, $selectedEmployeeId);
        $calendarData = $this->buildCalendarData($rows, $targetMonth);
        $pdfQuery = array_filter([
            'month' => $selectedMonthValue,
            'branch_id' => $selectedBranchId ?: null,
            'employee_id' => $selectedEmployeeId ?: null,
        ], function ($value) {
            return $value !== null && $value !== '';
        });
        $data = [
            'module' => $this->data,
            'action' => 'VHS Teacher',
            'category' => self::VHS_TEACHER,
            'monthOptions' => $this->monthOptions($targetMonth),
            'selectedMonthValue' => $selectedMonthValue,
            'selectedMonthLabel' => $targetMonth->format('F Y'),
            'selectedBranchId' => $selectedBranchId,
            'selectedEmployeeId' => $selectedEmployeeId,
            'selectedBranchLabel' => $this->branchLabelById($selectedBranchId),
            'selectedEmployeeLabel' => $this->employeeLabelById($selectedEmployeeId),
            'branchOptions' => $this->rosterBranchOptionsForCategory(self::VHS_TEACHER),
            'employeeOptions' => $this->rosterEmployeeOptionsForCategory(self::VHS_TEACHER),
            'individualEmployees' => $this->availableSupportEmployeesForManual(self::VHS_TEACHER, $targetMonth, self::VHS_CATEGORIES),
            'individualBranchOptions' => $this->supportBranchOptions(),
            'individualCalendarDates' => $this->calendarDates($targetMonth, self::VHS_CATEGORIES),
            'monthStartDate' => $targetMonth->copy()->startOfMonth(),
            'monthEndDate' => $targetMonth->copy()->endOfMonth(),
            'eligibleTeacherCount' => $this->eligibleEmployees(self::VHS_TEACHER)->count(),
            'rows' => $rows,
            'stats' => $this->buildRosterStats($rows),
            'calendarDates' => $calendarData['dates'],
            'calendarEmployees' => $calendarData['employees'],
            'calendarCells' => $calendarData['cells'],
            'branchColorLegend' => $this->branchColorLegend(),
            'generationResult' => $generationResult,
            'pdfUrl' => url('employee/schedule-roster/vhs/pdf') . '?' . http_build_query($pdfQuery),
        ];

        $title = 'VHS Teacher Schedule Roster';
        $pageName = 'employee.schedule-roster-vhs';
        $data = $this->siteAuthService->admin_after_login_layout($title, $pageName, $data);

        return view('front.pages.' . $pageName, $data);
    }

    public function vhsIndividual(Request $request)
    {
        return $this->storeSupportRoster(
            $request,
            self::VHS_CATEGORIES,
            'employee/schedule-roster/vhs'
        );
    }

    public function vhsPdf(Request $request)
    {
        $targetMonth = $this->resolveRosterMonth($request);
        $selectedBranchId = $this->positiveInt($request->input('branch_id'));
        $selectedEmployeeId = $this->positiveInt($request->input('employee_id'));
        $rows = $this->getRosterRows(self::VHS_TEACHER, $targetMonth, $selectedBranchId, $selectedEmployeeId);
        $calendarData = $this->buildCalendarData($rows, $targetMonth);
        $data = [
            'category' => self::VHS_TEACHER,
            'selectedMonthLabel' => $targetMonth->format('F Y'),
            'selectedBranchLabel' => $this->branchLabelById($selectedBranchId),
            'selectedEmployeeLabel' => $this->employeeLabelById($selectedEmployeeId),
            'monthStartDate' => $targetMonth->copy()->startOfMonth(),
            'monthEndDate' => $targetMonth->copy()->endOfMonth(),
            'rows' => $rows,
            'stats' => $this->buildRosterStats($rows),
            'calendarDates' => $calendarData['dates'],
            'calendarEmployees' => $calendarData['employees'],
            'calendarCells' => $calendarData['cells'],
            'branchColorLegend' => $this->branchColorLegend(),
        ];

        $html = view('front.pages.employee.schedule-roster-vhs-pdf', $data)->render();
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A3', 'landscape');
        $dompdf->render();

        $filenameParts = ['vhs-teacher-roster', $targetMonth->format('Y-m')];
        if ($selectedBranchId > 0) {
            $filenameParts[] = 'branch-' . $selectedBranchId;
        }
        if ($selectedEmployeeId > 0) {
            $filenameParts[] = 'employee-' . $selectedEmployeeId;
        }
        $filename = implode('-', $filenameParts) . '.pdf';

        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function vhsDelete(Request $request)
    {
        $targetMonth = $this->resolveRosterMonthFromValue($request->input('delete_month', Carbon::now()->format('Y-m')));
        $employeeId = $this->positiveInt($request->input('employee_id'));
        $branchId = $this->positiveInt($request->input('branch_id'));

        if ($employeeId <= 0) {
            return redirect()->back()->with('error_message', 'Please select a valid VHS teacher roster to delete.');
        }

        $employee = Employee::where('id', '=', $employeeId)->where('status', '!=', 3)->first();
        if (!$employee || !$this->employeeHasCategory($employee, self::VHS_TEACHER)) {
            return redirect()->back()->with('error_message', 'Please select a valid VHS teacher roster to delete.');
        }

        $query = EmployeeScheduleRoster::where('category', '=', self::VHS_TEACHER)
            ->where('employee_id', '=', $employeeId)
            ->where('roster_month', '=', (int) $targetMonth->month)
            ->where('roster_year', '=', (int) $targetMonth->year)
            ->where('status', '!=', 3);

        if ($branchId > 0) {
            $query->where('branch_id', '=', $branchId);
        }

        $deleted = $query->update([
            'status' => 3,
            'updated_by' => $this->currentUserId(),
        ]);

        $redirectQuery = [
            'month' => $targetMonth->format('Y-m'),
        ];
        if ($branchId > 0) {
            $redirectQuery['branch_id'] = $branchId;
        }

        return redirect()
            ->to(url('employee/schedule-roster/vhs') . '?' . http_build_query($redirectQuery))
            ->with($deleted > 0 ? 'success_message' : 'error_message', $deleted > 0 ? 'VHS teacher roster deleted successfully.' : 'No active VHS teacher roster found to delete.');
    }

    public function vhsUpdateTime(Request $request)
    {
        $rosterId = $this->positiveInt($request->input('roster_id'));
        $inTime = $this->normalizeSubmittedTime($request->input('in_time', ''));
        $outTime = $this->normalizeSubmittedTime($request->input('out_time', ''));

        if ($rosterId <= 0 || !$inTime || !$outTime) {
            return redirect()->back()->withInput()->with('error_message', 'Please select a valid VHS roster date and time.');
        }

        if (!$this->isValidRosterTimeRange($inTime, $outTime)) {
            return redirect()->back()->withInput()->with('error_message', 'Class end time must be after class start time.');
        }

        $roster = EmployeeScheduleRoster::where('id', '=', $rosterId)
            ->where('category', '=', self::VHS_TEACHER)
            ->where('status', '!=', 3)
            ->first();

        if (!$roster) {
            return redirect()->back()->with('error_message', 'VHS roster date not found.');
        }

        if (!$this->isEditableVhsSaturday($roster->roster_date)) {
            return redirect()->back()->with('error_message', 'VHS time can be edited only on 1st, 3rd and 5th Saturday roster dates.');
        }

        if ($this->rosterRowsHaveAttendance([(int) $roster->id])) {
            return redirect()->back()->with('error_message', 'This VHS roster date cannot be edited because attendance already exists.');
        }

        if ($this->rosterTimeUpdateHasConflict($roster, $inTime)) {
            return redirect()->back()->withInput()->with('error_message', 'Another VHS roster already exists with the selected start time.');
        }

        $roster->update([
            'in_time' => $inTime,
            'out_time' => $outTime,
            'updated_by' => $this->currentUserId(),
        ]);

        return redirect()->back()->with('success_message', 'VHS Saturday roster time updated successfully.');
    }

    public function support(Request $request)
    {
        return $this->manualRosterPage(
            $request,
            self::SUPPORT_CATEGORIES,
            'employee/schedule-roster/front-desk-group-d',
            'Front Desk & Group D Roster',
            'Front Desk & Group D Schedule Roster',
            'front-desk-group-d-roster',
            false
        );
    }

    public function supportPdf(Request $request)
    {
        return $this->manualRosterPdf($request, self::SUPPORT_CATEGORIES, 'Front Desk & Group D Schedule Roster', 'front-desk-group-d-roster');
    }

    public function supportCopy(Request $request)
    {
        return $this->manualRosterCopy($request, self::SUPPORT_CATEGORIES, 'employee/schedule-roster/front-desk-group-d');
    }

    public function supportDelete(Request $request)
    {
        return $this->manualRosterDelete($request, self::SUPPORT_CATEGORIES, 'employee/schedule-roster/front-desk-group-d');
    }

    public function supportDeleteDate(Request $request)
    {
        return $this->manualRosterDeleteDate($request, self::SUPPORT_CATEGORIES);
    }

    public function supportShiftDate(Request $request)
    {
        return $this->manualRosterShiftDate($request, self::SUPPORT_CATEGORIES);
    }

    public function tsa(Request $request)
    {
        return $this->manualRosterPage(
            $request,
            self::TSA_CATEGORIES,
            'employee/schedule-roster/tsa',
            'TSA Teacher Roster',
            'TSA Teacher Schedule Roster',
            'tsa-teacher-roster',
            true
        );
    }

    public function tsaPdf(Request $request)
    {
        return $this->manualRosterPdf($request, self::TSA_CATEGORIES, 'TSA Teacher Schedule Roster', 'tsa-teacher-roster');
    }

    public function tsaCopy(Request $request)
    {
        return $this->manualRosterCopy($request, self::TSA_CATEGORIES, 'employee/schedule-roster/tsa');
    }

    public function tsaShiftDate(Request $request)
    {
        return $this->manualRosterShiftDate($request, self::TSA_CATEGORIES);
    }

    public function tsaAddClass(Request $request)
    {
        $targetMonth = $this->resolveRosterMonthFromValue($request->input('month', Carbon::now()->format('Y-m')));
        $employeeId = $this->positiveInt($request->input('employee_id'));
        $branchName = $this->resolveSupportBranchName($request->input('branch_name', self::SUPPORT_BRANCH_NAMES[0]));
        $inTime = $this->normalizeSubmittedTime($request->input('in_time', ''));
        $outTime = $this->normalizeSubmittedTime($request->input('out_time', ''));
        $rosterDateValue = trim((string) $request->input('roster_date', ''));

        try {
            $rosterDate = Carbon::createFromFormat('Y-m-d', $rosterDateValue)->startOfDay();
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error_message', 'Please select a valid roster date.');
        }

        if ($employeeId <= 0 || !$inTime || !$outTime) {
            return redirect()->back()->withInput()->with('error_message', 'Please select employee, from time and to time.');
        }

        if (!$this->isValidRosterTimeRange($inTime, $outTime)) {
            return redirect()->back()->withInput()->with('error_message', 'Class end time must be after class start time.');
        }

        if ($rosterDate->format('Y-m') !== $targetMonth->format('Y-m')) {
            return redirect()->back()->withInput()->with('error_message', 'Roster date must be within selected month.');
        }

        if (!$this->isRosterWorkingDate($rosterDate, self::TSA_CATEGORIES)) {
            return redirect()->back()->withInput()->with('error_message', 'Additional class cannot be added on this roster date.');
        }

        $employee = Employee::where('status', '=', 1)->where('id', '=', $employeeId)->first();
        if (!$employee || !$this->employeeHasCategory($employee, self::TSA_TEACHER)) {
            return redirect()->back()->withInput()->with('error_message', 'Please select a valid TSA teacher.');
        }

        $branch = $this->supportBranchForEmployee($employee, $branchName);
        if (!$branch) {
            return redirect()->back()->withInput()->with('error_message', 'Please select a valid branch.');
        }

        if ($this->hasOverlappingTsaClass((int) $employee->id, $rosterDate, $inTime, $outTime)) {
            return redirect()->back()->withInput()->with('error_message', 'This TSA teacher already has another class overlapping the selected time.');
        }

        $result = 'existing';
        $userId = $this->currentUserId();

        DB::transaction(function () use ($employee, $branch, $rosterDate, $targetMonth, $inTime, $outTime, $userId, &$result) {
            $keys = [
                'employee_id' => (int) $employee->id,
                'category' => self::TSA_TEACHER,
                'branch_id' => (int) $branch->id,
                'roster_date' => $rosterDate->toDateString(),
                'in_time' => $inTime,
            ];

            $values = [
                'employee_no' => (string) $employee->employee_no,
                'employee_name' => $this->employeeName($employee),
                'unit_id' => (int) $branch->unit_id,
                'unit_name' => (string) ($branch->unit_name ?? ''),
                'branch_name' => (string) $branch->name,
                'roster_month' => (int) $targetMonth->month,
                'roster_year' => (int) $targetMonth->year,
                'day_name' => $rosterDate->format('l'),
                'out_time' => $outTime,
                'status' => 1,
                'generated_at' => now(),
                'created_by' => $userId,
                'updated_by' => $userId,
            ];

            $result = $this->saveRosterRowPreservingActive($keys, $values);
        });

        $message = $result === 'existing'
            ? 'This exact TSA class already exists for the selected branch, date and start time.'
            : 'Additional TSA class saved successfully.';

        return redirect()
            ->to(url('employee/schedule-roster/tsa') . '?' . http_build_query([
                'month' => $targetMonth->format('Y-m'),
                'category' => self::TSA_TEACHER,
                'employee_id' => $employeeId,
            ]))
            ->with($result === 'existing' ? 'error_message' : 'success_message', $message);
    }

    public function tsaReschedule(Request $request)
    {
        $rosterId = $this->positiveInt($request->input('roster_id'));
        $inTime = $this->normalizeSubmittedTime($request->input('in_time', ''));
        $outTime = $this->normalizeSubmittedTime($request->input('out_time', ''));

        if ($rosterId <= 0 || !$inTime || !$outTime) {
            return redirect()->back()->withInput()->with('error_message', 'Please provide a valid TSA roster date and time.');
        }

        if (!$this->isValidRosterTimeRange($inTime, $outTime)) {
            return redirect()->back()->withInput()->with('error_message', 'Class end time must be after class start time.');
        }

        $roster = EmployeeScheduleRoster::where('id', '=', $rosterId)
            ->where('category', '=', self::TSA_TEACHER)
            ->where('status', '!=', 3)
            ->first();

        if (!$roster) {
            return redirect()->back()->with('error_message', 'TSA roster date not found.');
        }

        if (!$this->canRescheduleRosterRow($roster) || !$this->isRosterStartOutsideRescheduleWindow($roster->roster_date, $inTime)) {
            return redirect()->back()->with('error_message', 'This TSA roster date is locked because the start time is within 48 hours.');
        }

        if ($this->hasOverlappingTsaClass(
            (int) $roster->employee_id,
            Carbon::parse($roster->roster_date),
            $inTime,
            $outTime,
            (int) $roster->id
        )) {
            return redirect()->back()->withInput()->with('error_message', 'This TSA teacher already has another class overlapping the selected time.');
        }

        $roster->update([
            'in_time' => $inTime,
            'out_time' => $outTime,
            'updated_by' => $this->currentUserId(),
        ]);

        return redirect()->back()->with('success_message', 'TSA roster date rescheduled successfully.');
    }

    public function tsaDeleteDate(Request $request)
    {
        return $this->manualRosterDeleteDate($request, self::TSA_CATEGORIES);
    }

    public function employeeRosterPdf(Request $request, $employeeId)
    {
        $employee = Employee::where('id', '=', (int) $employeeId)
            ->where('status', '!=', 3)
            ->first();

        if (!$employee) {
            abort(404);
        }

        $targetMonth = $this->resolveRosterMonth($request);
        $rows = $this->getEmployeeRosterRows($targetMonth, (int) $employee->id);
        $employeeLabel = trim((string) $employee->employee_no . ' - ' . $this->employeeName($employee));
        $data = $this->manualRosterPdfData($rows, $targetMonth, 'Employee Schedule Roster', '', '', (int) $employee->id, $employeeLabel);
        $filename = 'employee-roster-' . $this->safeFilename($employee->employee_no ?: $employee->id) . '-' . $targetMonth->format('Y-m') . '.pdf';

        return $this->downloadPdf($this->renderSupportRosterPdf($data), $filename);
    }

    public function emailEmployeeRoster(Request $request, $employeeId)
    {
        $employee = Employee::where('id', '=', (int) $employeeId)
            ->where('status', '!=', 3)
            ->first();

        if (!$employee) {
            return redirect()->back()->with('error_message', 'Employee not found.');
        }

        if (trim((string) $employee->email) === '') {
            return redirect()->back()->with('error_message', 'Employee email is not available.');
        }

        $targetMonth = $this->resolveRosterMonth($request);
        $rows = $this->getEmployeeRosterRows($targetMonth, (int) $employee->id);

        if ($rows->isEmpty()) {
            return redirect()->back()->with('error_message', 'No roster found for this employee in ' . $targetMonth->format('F Y') . '.');
        }

        $employeeLabel = trim((string) $employee->employee_no . ' - ' . $this->employeeName($employee));
        $data = $this->manualRosterPdfData($rows, $targetMonth, 'Employee Schedule Roster', '', '', (int) $employee->id, $employeeLabel);
        $directory = storage_path('app/roster-mail');
        $filename = 'employee-roster-' . $this->safeFilename($employee->employee_no ?: $employee->id) . '-' . $targetMonth->format('Y-m') . '.pdf';
        $filePath = $directory . DIRECTORY_SEPARATOR . $filename;

        try {
            if (!is_dir($directory)) {
                mkdir($directory, 0775, true);
            }

            file_put_contents($filePath, $this->renderSupportRosterPdf($data));

            $message = '<p>Dear ' . e($this->employeeName($employee)) . ',</p>'
                . '<p>Please find attached your schedule roster for ' . e($targetMonth->format('F Y')) . '.</p>'
                . '<p>Regards,<br>NVGI</p>';

            $sent = $this->sendMail(
                (string) $employee->email,
                'Schedule Roster - ' . $targetMonth->format('F Y'),
                $message,
                $filePath
            );
        } catch (\Throwable $e) {
            report($e);
            $sent = false;
        } finally {
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }

        return redirect()
            ->back()
            ->with($sent ? 'success_message' : 'error_message', $sent ? 'Roster emailed successfully.' : 'Unable to email roster. Please check mail settings.');
    }

    private function manualRosterPage(Request $request, array $allowedCategories, string $routePath, string $rosterTitle, string $pageTitle, string $pdfFilenamePrefix, bool $allowReschedule)
    {
        if ($request->isMethod('post')) {
            return $this->storeSupportRoster($request, $allowedCategories, $routePath);
        }

        $defaultCategory = $allowedCategories[0] ?? self::SUPPORT_CATEGORIES[0];
        $entryMonth = $this->resolveRosterMonthFromValue($request->input('entry_month', Carbon::now()->format('Y-m')));
        $entryCategory = $this->resolveSupportCategory($request->input('entry_category', $defaultCategory), $allowedCategories);
        $entryBranchName = $this->resolveSupportBranchName($request->input('entry_branch_name', self::SUPPORT_BRANCH_NAMES[0]));

        $searchMonth = $this->resolveRosterMonthFromValue($request->input('month', $entryMonth->format('Y-m')));
        $searchCategory = $this->resolveSupportSearchCategory($request->input('category', ''), $allowedCategories);
        $searchBranchName = $this->resolveSupportBranchFilter($request->input('branch_name', ''));
        $searchEmployeeId = $this->positiveInt($request->input('employee_id'));
        $searchDateCategories = $this->selectedRosterDateCategories($searchCategory, $allowedCategories);

        $rows = $this->getSupportRosterRows($searchMonth, $searchCategory, $searchBranchName, $searchEmployeeId, $allowedCategories);
        $calendarGroups = $this->buildSupportCalendarGroups($rows, $searchMonth, $this->shouldGroupManualRosterByBranch($allowedCategories));

        $copySourceMonth = $this->resolveRosterMonthFromValue($request->input('copy_source_month', $searchMonth->format('Y-m')));
        $copyTargetMonth = $this->resolveRosterMonthFromValue($request->input('copy_target_month', $copySourceMonth->copy()->addMonth()->format('Y-m')));

        $pdfQuery = array_filter([
            'month' => $searchMonth->format('Y-m'),
            'category' => $searchCategory,
            'branch_name' => $searchBranchName,
            'employee_id' => $searchEmployeeId ?: null,
        ], function ($value) {
            return $value !== null && $value !== '';
        });

        $data = [
            'module' => array_merge($this->data, [
                'controller_route' => $routePath,
            ]),
            'action' => $rosterTitle,
            'rosterTitle' => $rosterTitle,
            'rosterRoute' => $routePath,
            'pdfRoute' => $routePath . '/pdf',
            'copyRoute' => $routePath . '/copy',
            'deleteRoute' => $routePath . '/delete',
            'shiftDateRoute' => $routePath . '/shift-date',
            'additionalClassRoute' => $allowReschedule ? $routePath . '/add-class' : '',
            'rescheduleRoute' => $allowReschedule ? $routePath . '/reschedule' : '',
            'dateDeleteRoute' => $routePath . '/delete-date',
            'allowReschedule' => $allowReschedule,
            'allowDateDelete' => true,
            'allowAdditionalClass' => $allowReschedule,
            'allowWholeEmployeeDelete' => !$allowReschedule,
            'supportCategories' => $allowedCategories,
            'branchOptions' => $this->supportBranchOptions(),
            'entryCategory' => $entryCategory,
            'entryMonthValue' => $entryMonth->format('Y-m'),
            'entryMonthLabel' => $entryMonth->format('F Y'),
            'entryBranchName' => $entryBranchName,
            'entryEmployees' => $this->availableSupportEmployeesForManual($entryCategory, $entryMonth, $allowedCategories),
            'entryCalendarDates' => $this->calendarDates($entryMonth, [$entryCategory]),
            'additionalClassEmployees' => $allowReschedule ? $this->supportEmployeeOptions(self::TSA_TEACHER, self::TSA_CATEGORIES) : [],
            'additionalClassDates' => $allowReschedule ? $this->calendarDates($searchMonth, self::TSA_CATEGORIES) : [],
            'monthOptions' => $this->monthOptions($entryMonth, $searchMonth, $copySourceMonth, $copyTargetMonth),
            'selectedMonthValue' => $searchMonth->format('Y-m'),
            'selectedMonthLabel' => $searchMonth->format('F Y'),
            'selectedCategory' => $searchCategory,
            'selectedBranchName' => $searchBranchName,
            'selectedEmployeeId' => $searchEmployeeId,
            'searchEmployees' => $this->supportEmployeeOptions($searchCategory ?: null, $allowedCategories),
            'copySourceMonthValue' => $copySourceMonth->format('Y-m'),
            'copyTargetMonthValue' => $copyTargetMonth->format('Y-m'),
            'copyEmployees' => $this->supportEmployeeOptions(null, $allowedCategories),
            'monthStartDate' => $searchMonth->copy()->startOfMonth(),
            'monthEndDate' => $searchMonth->copy()->endOfMonth(),
            'rows' => $rows,
            'stats' => $this->buildRosterStats($rows),
            'calendarDates' => $this->calendarDates($searchMonth, $searchDateCategories),
            'calendarGroups' => $calendarGroups,
            'branchColorLegend' => $this->branchColorLegend(),
            'pdfUrl' => url($routePath . '/pdf') . '?' . http_build_query($pdfQuery),
        ];

        $pageName = 'employee.schedule-roster-support';
        $data = $this->siteAuthService->admin_after_login_layout($pageTitle, $pageName, $data);

        return view('front.pages.' . $pageName, $data);
    }

    private function manualRosterPdf(Request $request, array $allowedCategories, string $title, string $filenamePrefix)
    {
        $targetMonth = $this->resolveRosterMonth($request);
        $category = $this->resolveSupportSearchCategory($request->input('category', ''), $allowedCategories);
        $branchName = $this->resolveSupportBranchFilter($request->input('branch_name', ''));
        $employeeId = $this->positiveInt($request->input('employee_id'));
        $rows = $this->getSupportRosterRows($targetMonth, $category, $branchName, $employeeId, $allowedCategories);
        $dateCategories = $this->selectedRosterDateCategories($category, $allowedCategories);

        $data = $this->manualRosterPdfData($rows, $targetMonth, $title, $category, $branchName, $employeeId, '', $this->shouldGroupManualRosterByBranch($allowedCategories), $dateCategories);
        $filenameParts = [$filenamePrefix, $targetMonth->format('Y-m')];
        if ($category !== '') {
            $filenameParts[] = strtolower(str_replace([' ', '/'], '-', $category));
        }
        if ($employeeId > 0) {
            $filenameParts[] = 'employee-' . $employeeId;
        }

        return $this->downloadPdf($this->renderSupportRosterPdf($data), implode('-', $filenameParts) . '.pdf');
    }

    private function manualRosterCopy(Request $request, array $allowedCategories, string $routePath)
    {
        $sourceMonth = $this->resolveRosterMonthFromValue($request->input('copy_source_month', Carbon::now()->format('Y-m')));
        $targetMonth = $this->resolveRosterMonthFromValue($request->input('copy_target_month', $sourceMonth->copy()->addMonth()->format('Y-m')));
        $category = $this->resolveSupportSearchCategory($request->input('copy_category', ''), $allowedCategories);
        $employeeId = $this->positiveInt($request->input('copy_employee_id'));

        if ($sourceMonth->format('Y-m') === $targetMonth->format('Y-m')) {
            return redirect()->back()->withInput()->with('error_message', 'Source month and target month cannot be same.');
        }

        $result = $this->copySupportRoster($sourceMonth, $targetMonth, $category, $employeeId, $allowedCategories);

        if ($result['source_rows'] <= 0) {
            return redirect()->back()->withInput()->with('error_message', 'No source roster found for copy.');
        }

        $query = [
            'month' => $targetMonth->format('Y-m'),
            'category' => $category,
            'employee_id' => $employeeId ?: '',
        ];

        return redirect()
            ->to(url($routePath) . '?' . http_build_query(array_filter($query)))
            ->with('success_message', 'Roster copied. New rows: ' . $result['created'] . ', restored rows: ' . $result['restored'] . ', existing rows kept: ' . $result['existing'] . '.');
    }

    private function manualRosterDelete(Request $request, array $allowedCategories, string $routePath)
    {
        $targetMonth = $this->resolveRosterMonthFromValue($request->input('delete_month', Carbon::now()->format('Y-m')));
        $category = $this->resolveSupportCategory($request->input('category', ''), $allowedCategories);
        $branchName = $this->resolveSupportBranchFilter($request->input('branch_name', ''));
        $employeeId = $this->positiveInt($request->input('employee_id'));

        if ($employeeId <= 0 || $branchName === '') {
            return redirect()->back()->with('error_message', 'Please select a valid employee roster to delete.');
        }

        $deleted = EmployeeScheduleRoster::where('category', '=', $category)
            ->where('employee_id', '=', $employeeId)
            ->where('branch_name', '=', $branchName)
            ->where('roster_month', '=', (int) $targetMonth->month)
            ->where('roster_year', '=', (int) $targetMonth->year)
            ->where('status', '!=', 3)
            ->update([
                'status' => 3,
                'updated_by' => $this->currentUserId(),
            ]);

        return redirect()
            ->to(url($routePath) . '?month=' . $targetMonth->format('Y-m') . '&category=' . urlencode($category) . '&branch_name=' . urlencode($branchName))
            ->with($deleted > 0 ? 'success_message' : 'error_message', $deleted > 0 ? 'Roster deleted successfully.' : 'No active roster found to delete.');
    }

    private function manualRosterDeleteDate(Request $request, array $allowedCategories)
    {
        $rosterId = $this->positiveInt($request->input('roster_id'));

        if ($rosterId <= 0) {
            return redirect()->back()->with('error_message', 'Please select a valid roster date to delete.');
        }

        $deleted = EmployeeScheduleRoster::where('id', '=', $rosterId)
            ->whereIn('category', $allowedCategories)
            ->where('status', '!=', 3)
            ->update([
                'status' => 3,
                'updated_by' => $this->currentUserId(),
            ]);

        return redirect()
            ->back()
            ->with($deleted > 0 ? 'success_message' : 'error_message', $deleted > 0 ? 'Roster date deleted successfully.' : 'No active roster date found to delete.');
    }

    private function manualRosterShiftDate(Request $request, array $allowedCategories)
    {
        $category = $this->resolveSupportCategory($request->input('category', ''), $allowedCategories);
        $employeeId = $this->positiveInt($request->input('employee_id'));
        $branchName = $this->resolveShiftBranchFilter($request->input('branch_name', ''));
        $sourceDate = $this->parseRosterDateValue($request->input('source_date', ''));
        $targetDate = $this->parseRosterDateValue($request->input('target_date', ''));

        if ($employeeId <= 0 || !$sourceDate || !$targetDate) {
            return redirect()->back()->withInput()->with('error_message', 'Please select a valid employee, source date and target date.');
        }

        if ($sourceDate->isSameDay($targetDate)) {
            return redirect()->back()->withInput()->with('error_message', 'Source date and target date cannot be same.');
        }

        if ($sourceDate->format('Y-m') !== $targetDate->format('Y-m')) {
            return redirect()->back()->withInput()->with('error_message', 'Target date must be within the same month.');
        }

        if (!$this->isRosterWorkingDate($sourceDate, [$category]) || !$this->isRosterWorkingDate($targetDate, [$category])) {
            return redirect()->back()->withInput()->with('error_message', 'Please select valid roster dates for ' . $category . '.');
        }

        $employee = Employee::where('status', '!=', 3)->where('id', '=', $employeeId)->first();
        if (!$employee || !$this->employeeHasCategory($employee, $category)) {
            return redirect()->back()->withInput()->with('error_message', 'Please select a valid ' . $category . ' employee.');
        }

        $result = [
            'shifted' => false,
            'message' => '',
            'rows' => 0,
        ];

        DB::transaction(function () use ($employeeId, $category, $branchName, $sourceDate, $targetDate, &$result) {
            $sourceRows = $this->manualRosterShiftRows($employeeId, $category, $sourceDate, $branchName)
                ->lockForUpdate()
                ->get();
            $targetRows = $this->manualRosterShiftRows($employeeId, $category, $targetDate, $branchName)
                ->lockForUpdate()
                ->get();

            if ($sourceRows->isEmpty() && $targetRows->isEmpty()) {
                $result['message'] = 'No duty found on either selected date.';

                return;
            }

            $rowIds = $sourceRows->merge($targetRows)->pluck('id')->map(function ($id) {
                return (int) $id;
            })->values()->all();

            if ($this->rosterRowsHaveAttendance($rowIds)) {
                $result['message'] = 'This duty cannot be shifted because attendance already exists for one of the selected dates.';

                return;
            }

            $userId = $this->currentUserId();
            if ($sourceRows->isNotEmpty() && $targetRows->isNotEmpty()) {
                $temporaryDate = $this->temporaryRosterSwapDate($sourceRows);

                foreach ($sourceRows as $row) {
                    $this->applyRosterDate($row, $temporaryDate, $userId);
                }
            }

            foreach ($targetRows as $row) {
                $this->applyRosterDate($row, $sourceDate, $userId);
            }

            foreach ($sourceRows as $row) {
                $this->applyRosterDate($row, $targetDate, $userId);
            }

            $result['shifted'] = true;
            $result['rows'] = count($rowIds);
            $result['message'] = 'Duty/weekoff shifted between ' . $sourceDate->format('d-m-Y') . ' and ' . $targetDate->format('d-m-Y') . '.';
        });

        return redirect()
            ->back()
            ->with($result['shifted'] ? 'success_message' : 'error_message', $result['message'] ?: 'Unable to shift duty/weekoff.');
    }

    private function storeSupportRoster(Request $request, ?array $allowedCategories = null, string $routePath = 'employee/schedule-roster/front-desk-group-d')
    {
        $allowedCategories = $allowedCategories ?: self::SUPPORT_CATEGORIES;
        $category = $this->resolveSupportCategory($request->input('category', $allowedCategories[0] ?? self::SUPPORT_CATEGORIES[0]), $allowedCategories);
        $targetMonth = $this->resolveRosterMonthFromValue($request->input('month', Carbon::now()->format('Y-m')));
        $employeeId = $this->positiveInt($request->input('employee_id'));
        $branchName = $this->resolveSupportBranchName($request->input('branch_name', self::SUPPORT_BRANCH_NAMES[0]));
        $employee = $employeeId > 0 ? Employee::where('status', '=', 1)->where('id', '=', $employeeId)->first() : null;

        if (!$employee || !$this->employeeHasCategory($employee, $category)) {
            return redirect()->back()->withInput()->with('error_message', 'Please select a valid ' . $category . ' employee.');
        }

        if ($this->supportEmployeeHasActiveRoster($employeeId, $category, $targetMonth)) {
            return redirect()->back()->withInput()->with('error_message', 'This employee already has a roster for ' . $targetMonth->format('F Y') . '.');
        }

        $branch = $this->supportBranchForEmployee($employee, $branchName);
        if (!$branch) {
            return redirect()->back()->withInput()->with('error_message', 'Please select a valid branch.');
        }

        $times = $request->input('times', []);
        $rosterDates = $this->rosterDates($targetMonth, [$category]);
        $preparedRows = [];
        $partialDates = [];

        foreach ($rosterDates as $date) {
            $dateKey = $date->toDateString();
            $inTime = $this->normalizeSubmittedTime($times[$dateKey]['in_time'] ?? '');
            $outTime = $this->normalizeSubmittedTime($times[$dateKey]['out_time'] ?? '');

            if (!$inTime && !$outTime) {
                continue;
            }

            if (!$inTime || !$outTime) {
                $partialDates[] = $date->format('d-m-Y');
                continue;
            }

            $preparedRows[] = [
                'date' => $date->copy(),
                'in_time' => $inTime,
                'out_time' => $outTime,
            ];
        }

        if (!empty($partialDates)) {
            return redirect()->back()->withInput()->with('error_message', 'Please fill both from and to time, or clear the date, for: ' . implode(', ', $partialDates) . '.');
        }

        if (empty($preparedRows)) {
            return redirect()->back()->withInput()->with('error_message', 'Please fill at least one roster date.');
        }

        $result = [
            'created' => 0,
            'restored' => 0,
            'existing' => 0,
        ];
        $userId = $this->currentUserId();

        DB::transaction(function () use ($preparedRows, $employee, $category, $branch, $targetMonth, $userId, &$result) {
            foreach ($preparedRows as $preparedRow) {
                $date = $preparedRow['date'];
                $keys = [
                    'employee_id' => (int) $employee->id,
                    'category' => $category,
                    'branch_id' => (int) $branch->id,
                    'roster_date' => $date->toDateString(),
                ];

                $values = [
                    'employee_no' => (string) $employee->employee_no,
                    'employee_name' => $this->employeeName($employee),
                    'unit_id' => (int) $branch->unit_id,
                    'unit_name' => (string) ($branch->unit_name ?? ''),
                    'branch_name' => (string) $branch->name,
                    'roster_month' => (int) $targetMonth->month,
                    'roster_year' => (int) $targetMonth->year,
                    'day_name' => $date->format('l'),
                    'in_time' => $preparedRow['in_time'],
                    'out_time' => $preparedRow['out_time'],
                    'status' => 1,
                    'generated_at' => now(),
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ];

                $result[$this->saveRosterRowPreservingActive($keys, $values)]++;
            }
        });

        return redirect()
            ->to(url($routePath) . '?entry_category=' . urlencode($category) . '&entry_month=' . $targetMonth->format('Y-m') . '&month=' . $targetMonth->format('Y-m') . '&category=' . urlencode($category) . '&branch_name=' . urlencode($branch->name))
            ->with('success_message', $category . ' roster saved. New rows: ' . $result['created'] . ', restored rows: ' . $result['restored'] . ', existing rows kept: ' . $result['existing'] . '.');
    }

    private function generateRosterForCategory(string $category, Carbon $targetMonth): array
    {
        $dates = $this->rosterDates($targetMonth, [$category]);
        $employees = $this->eligibleEmployees($category);
        $branchMap = $this->branchMapForEmployees($employees);
        $userId = $this->currentUserId();
        $result = [
            'employees' => $employees->count(),
            'dates' => $dates->count(),
            'created' => 0,
            'existing' => 0,
            'skipped' => 0,
        ];

        DB::transaction(function () use ($employees, $branchMap, $dates, $category, $targetMonth, $userId, &$result) {
            foreach ($employees as $employee) {
                $branchIds = $this->normalizeBranchIds($employee->branch);

                if (empty($branchIds)) {
                    $result['skipped']++;
                    continue;
                }

                foreach ($branchIds as $branchId) {
                    $branch = $branchMap[$branchId] ?? null;

                    if (!$branch) {
                        $result['skipped']++;
                        continue;
                    }

                    foreach ($dates as $date) {
                        $roster = EmployeeScheduleRoster::firstOrCreate(
                            [
                                'employee_id' => (int) $employee->id,
                                'category' => $category,
                                'branch_id' => (int) $branch->id,
                                'roster_date' => $date->toDateString(),
                            ],
                            [
                                'employee_no' => (string) $employee->employee_no,
                                'employee_name' => $this->employeeName($employee),
                                'unit_id' => (int) $branch->unit_id,
                                'unit_name' => (string) ($branch->unit_name ?? ''),
                                'branch_name' => (string) $branch->name,
                                'roster_month' => (int) $targetMonth->month,
                                'roster_year' => (int) $targetMonth->year,
                                'day_name' => $date->format('l'),
                                'in_time' => $this->formatRosterTime($employee->in_time),
                                'out_time' => $this->formatRosterTime($employee->out_time),
                                'status' => 1,
                                'generated_at' => now(),
                                'created_by' => $userId,
                                'updated_by' => $userId,
                            ]
                        );

                        if ($roster->wasRecentlyCreated) {
                            $result['created']++;
                        } else {
                            $result['existing']++;
                        }
                    }
                }
            }
        });

        return $result;
    }

    private function getRosterRows(string $category, Carbon $targetMonth, int $branchId = 0, int $employeeId = 0)
    {
        $rosterDates = $this->rosterDates($targetMonth, [$category])
            ->map(function ($date) {
                return $date->toDateString();
            })
            ->values()
            ->all();

        if (empty($rosterDates)) {
            return collect();
        }

        $query = EmployeeScheduleRoster::where('category', '=', $category)
            ->where('roster_month', '=', (int) $targetMonth->month)
            ->where('roster_year', '=', (int) $targetMonth->year)
            ->whereIn('roster_date', $rosterDates)
            ->where('status', '!=', 3);

        if ($branchId > 0) {
            $query->where('branch_id', '=', $branchId);
        }

        if ($employeeId > 0) {
            $query->where('employee_id', '=', $employeeId);
        }

        return $query
            ->orderBy('roster_date', 'ASC')
            ->orderBy('employee_no', 'ASC')
            ->orderBy('branch_name', 'ASC')
            ->get();
    }

    private function rosterBranchOptionsForCategory(string $category): array
    {
        $employees = $this->eligibleEmployees($category);

        return collect($this->branchMapForEmployees($employees))
            ->map(function ($branch) {
                $unitName = trim((string) ($branch->unit_name ?? ''));

                return [
                    'id' => (int) $branch->id,
                    'name' => (string) $branch->name,
                    'unit_name' => $unitName,
                    'label' => trim((string) $branch->name . ($unitName !== '' ? ' - ' . $unitName : '')),
                ];
            })
            ->sortBy('label')
            ->values()
            ->all();
    }

    private function rosterEmployeeOptionsForCategory(string $category): array
    {
        return $this->eligibleEmployees($category)
            ->map(function ($employee) {
                return [
                    'id' => (int) $employee->id,
                    'employee_no' => (string) $employee->employee_no,
                    'employee_name' => $this->employeeName($employee),
                    'label' => trim((string) $employee->employee_no . ' - ' . $this->employeeName($employee)),
                ];
            })
            ->values()
            ->all();
    }

    private function getSupportRosterRows(Carbon $targetMonth, string $category = '', string $branchName = '', int $employeeId = 0, ?array $allowedCategories = null)
    {
        $allowedCategories = $allowedCategories ?: self::SUPPORT_CATEGORIES;
        $rosterDates = $this->rosterDates($targetMonth, $this->selectedRosterDateCategories($category, $allowedCategories))
            ->map(function ($date) {
                return $date->toDateString();
            })
            ->values()
            ->all();

        if (empty($rosterDates)) {
            return collect();
        }

        $query = EmployeeScheduleRoster::whereIn('category', $allowedCategories)
            ->where('roster_month', '=', (int) $targetMonth->month)
            ->where('roster_year', '=', (int) $targetMonth->year)
            ->whereIn('roster_date', $rosterDates)
            ->where('status', '!=', 3);

        if ($category !== '') {
            $query->where('category', '=', $category);
        }

        if ($branchName !== '') {
            $query->where('branch_name', '=', $branchName);
        }

        if ($employeeId > 0) {
            $query->where('employee_id', '=', $employeeId);
        }

        return $query
            ->orderBy('branch_name', 'ASC')
            ->orderBy('category', 'ASC')
            ->orderBy('employee_no', 'ASC')
            ->orderBy('roster_date', 'ASC')
            ->get();
    }

    private function getEmployeeRosterRows(Carbon $targetMonth, int $employeeId)
    {
        $rosterDates = $this->rosterDates($targetMonth, self::ALL_ROSTER_CATEGORIES)
            ->map(function ($date) {
                return $date->toDateString();
            })
            ->values()
            ->all();

        if ($employeeId <= 0 || empty($rosterDates)) {
            return collect();
        }

        return EmployeeScheduleRoster::where('employee_id', '=', $employeeId)
            ->whereIn('category', self::ALL_ROSTER_CATEGORIES)
            ->where('roster_month', '=', (int) $targetMonth->month)
            ->where('roster_year', '=', (int) $targetMonth->year)
            ->whereIn('roster_date', $rosterDates)
            ->where('status', '!=', 3)
            ->orderBy('category', 'ASC')
            ->orderBy('branch_name', 'ASC')
            ->orderBy('roster_date', 'ASC')
            ->get();
    }

    private function supportEmployees(?string $category = null, ?array $allowedCategories = null)
    {
        $allowedCategories = $allowedCategories ?: self::SUPPORT_CATEGORIES;

        return Employee::where('status', '=', 1)
            ->orderBy('employee_no', 'ASC')
            ->orderBy('first_name', 'ASC')
            ->orderBy('last_name', 'ASC')
            ->get()
            ->filter(function ($employee) use ($category, $allowedCategories) {
                if ($category) {
                    return $this->employeeHasCategory($employee, $category);
                }

                foreach ($allowedCategories as $supportCategory) {
                    if ($this->employeeHasCategory($employee, $supportCategory)) {
                        return true;
                    }
                }

                return false;
            })
            ->values();
    }

    private function supportEmployeeOptions(?string $category = null, ?array $allowedCategories = null)
    {
        return $this->supportEmployees($category, $allowedCategories)
            ->map(function ($employee) {
                return [
                    'id' => (int) $employee->id,
                    'employee_no' => (string) $employee->employee_no,
                    'employee_name' => $this->employeeName($employee),
                    'label' => trim((string) $employee->employee_no . ' - ' . $this->employeeName($employee)),
                ];
            })
            ->values()
            ->all();
    }

    private function availableSupportEmployeesForManual(string $category, Carbon $targetMonth, ?array $allowedCategories = null)
    {
        $rosteredEmployeeIds = EmployeeScheduleRoster::where('category', '=', $category)
            ->where('roster_month', '=', (int) $targetMonth->month)
            ->where('roster_year', '=', (int) $targetMonth->year)
            ->where('status', '!=', 3)
            ->pluck('employee_id')
            ->map(function ($employeeId) {
                return (int) $employeeId;
            })
            ->unique()
            ->values()
            ->all();

        return $this->supportEmployees($category, $allowedCategories)
            ->reject(function ($employee) use ($rosteredEmployeeIds) {
                return in_array((int) $employee->id, $rosteredEmployeeIds, true);
            })
            ->map(function ($employee) {
                return [
                    'id' => (int) $employee->id,
                    'employee_no' => (string) $employee->employee_no,
                    'employee_name' => $this->employeeName($employee),
                    'label' => trim((string) $employee->employee_no . ' - ' . $this->employeeName($employee)),
                ];
            })
            ->values()
            ->all();
    }

    private function supportBranchOptions(): array
    {
        $branches = $this->supportBranchRows()
            ->groupBy('name')
            ->map(function ($branchRows, $branchName) {
                return [
                    'name' => (string) $branchName,
                    'label' => (string) $branchName,
                    'branch_ids' => $branchRows->pluck('id')->map(function ($id) {
                        return (int) $id;
                    })->values()->all(),
                    'unit_names' => $branchRows->pluck('unit_name')->filter()->unique()->values()->all(),
                ];
            })
            ->values()
            ->all();

        usort($branches, function ($left, $right) {
            return $this->supportBranchSortIndex($left['name']) <=> $this->supportBranchSortIndex($right['name']);
        });

        return $branches;
    }

    private function supportBranchRows()
    {
        return Branch::select('branches.id', 'branches.name', 'branches.unit_id', 'units.name as unit_name')
            ->leftJoin('units', 'units.id', '=', 'branches.unit_id')
            ->whereIn('branches.name', self::SUPPORT_BRANCH_NAMES)
            ->where('branches.status', '!=', 3)
            ->orderBy('branches.name', 'ASC')
            ->orderBy('branches.unit_id', 'ASC')
            ->get();
    }

    private function supportBranchForEmployee($employee, string $branchName)
    {
        $branchRows = $this->supportBranchRows()
            ->filter(function ($branch) use ($branchName) {
                return strcasecmp((string) $branch->name, $branchName) === 0;
            })
            ->values();

        if ($branchRows->isEmpty()) {
            return null;
        }

        $employeeBranchIds = $this->normalizeBranchIds($employee->branch);
        $assignedBranch = $branchRows->first(function ($branch) use ($employeeBranchIds) {
            return in_array((int) $branch->id, $employeeBranchIds, true);
        });

        return $assignedBranch ?: $branchRows->first();
    }

    private function buildSupportCalendarGroups($rows, Carbon $targetMonth, bool $groupByBranch = true): array
    {
        return $rows
            ->groupBy(function ($row) use ($groupByBranch) {
                return (string) $row->category . '|' . ($groupByBranch ? (string) $row->branch_name : 'ALL');
            })
            ->map(function ($groupRows) use ($targetMonth, $groupByBranch) {
                $firstRow = $groupRows->first();
                $calendarData = $this->buildCalendarData($groupRows, $targetMonth, [(string) $firstRow->category]);

                return [
                    'category' => (string) $firstRow->category,
                    'branch_name' => $groupByBranch ? (string) $firstRow->branch_name : 'All Branches',
                    'unit_names' => $groupRows->pluck('unit_name')->filter()->unique()->values()->all(),
                    'rows' => $groupRows->count(),
                    'employees_count' => $groupRows->pluck('employee_id')->unique()->count(),
                    'employees' => $calendarData['employees'],
                    'cells' => $calendarData['cells'],
                ];
            })
            ->sortBy(function ($group) {
                return str_pad((string) $this->supportBranchSortIndex($group['branch_name']), 2, '0', STR_PAD_LEFT) . '|' . $group['category'];
            })
            ->values()
            ->all();
    }

    private function shouldGroupManualRosterByBranch(array $allowedCategories): bool
    {
        return !(count($allowedCategories) === 1 && strcasecmp((string) $allowedCategories[0], self::TSA_TEACHER) === 0);
    }

    private function eligibleEmployees(string $category)
    {
        return Employee::where('status', '=', 1)
            ->whereNotNull('in_time')
            ->whereNotNull('out_time')
            ->whereRaw("TRIM(COALESCE(in_time, '')) != ''")
            ->whereRaw("TRIM(COALESCE(out_time, '')) != ''")
            ->orderBy('first_name', 'ASC')
            ->orderBy('last_name', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->filter(function ($employee) use ($category) {
                return in_array($category, $this->employeeCategoryValues($employee->category), true);
            })
            ->values();
    }

    private function branchMapForEmployees($employees): array
    {
        $branchIds = $employees
            ->flatMap(function ($employee) {
                return $this->normalizeBranchIds($employee->branch);
            })
            ->unique()
            ->values()
            ->all();

        if (empty($branchIds)) {
            return [];
        }

        return Branch::select('branches.id', 'branches.name', 'branches.unit_id', 'units.name as unit_name')
            ->leftJoin('units', 'units.id', '=', 'branches.unit_id')
            ->whereIn('branches.id', $branchIds)
            ->where('branches.status', '!=', 3)
            ->orderBy('branches.name', 'ASC')
            ->get()
            ->keyBy('id')
            ->all();
    }

    private function buildRosterStats($rows): array
    {
        $lastGeneratedAt = $rows->max('generated_at');

        return [
            'rows' => $rows->count(),
            'employees' => $rows->pluck('employee_id')->unique()->count(),
            'branches' => $rows->pluck('branch_id')->unique()->count(),
            'dates' => $rows->pluck('roster_date')->unique()->count(),
            'last_generated_at' => $lastGeneratedAt ? Carbon::parse($lastGeneratedAt)->format('d-m-Y H:i') : null,
        ];
    }

    private function manualRosterPdfData($rows, Carbon $targetMonth, string $title, string $category = '', string $branchName = '', int $employeeId = 0, string $employeeLabel = '', bool $groupByBranch = true, ?array $dateCategories = null): array
    {
        $dateCategories = $dateCategories ?: $this->rosterDateCategoriesFromRows($rows);

        return [
            'title' => $title,
            'selectedMonthLabel' => $targetMonth->format('F Y'),
            'monthStartDate' => $targetMonth->copy()->startOfMonth(),
            'monthEndDate' => $targetMonth->copy()->endOfMonth(),
            'selectedCategory' => $category,
            'selectedBranchName' => $branchName,
            'selectedEmployeeId' => $employeeId,
            'selectedEmployeeLabel' => $employeeLabel !== '' ? $employeeLabel : $this->employeeLabelById($employeeId),
            'stats' => $this->buildRosterStats($rows),
            'calendarDates' => $this->calendarDates($targetMonth, $dateCategories),
            'calendarGroups' => $this->buildSupportCalendarGroups($rows, $targetMonth, $groupByBranch),
            'branchColorLegend' => $this->branchColorLegend(),
        ];
    }

    private function renderSupportRosterPdf(array $data): string
    {
        $html = view('front.pages.employee.schedule-roster-support-pdf', $data)->render();
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A3', 'landscape');
        $dompdf->render();

        return $dompdf->output();
    }

    private function downloadPdf(string $pdfOutput, string $filename)
    {
        return response($pdfOutput, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    private function canRescheduleRosterRow($row): bool
    {
        return $this->isRosterStartOutsideRescheduleWindow($row->roster_date, $row->in_time);
    }

    private function isRosterStartOutsideRescheduleWindow($rosterDate, $inTime): bool
    {
        $inTime = $this->normalizeSubmittedTime($inTime);
        if (!$inTime) {
            return false;
        }

        try {
            $startAt = Carbon::createFromFormat('Y-m-d H:i', Carbon::parse($rosterDate)->toDateString() . ' ' . $inTime);
        } catch (\Throwable $e) {
            return false;
        }

        return Carbon::now()->lt($startAt->copy()->subHours(48));
    }

    private function rescheduleDeadlineLabel($row): string
    {
        $inTime = $this->normalizeSubmittedTime($row->in_time);
        if (!$inTime) {
            return '';
        }

        try {
            return Carbon::createFromFormat('Y-m-d H:i', Carbon::parse($row->roster_date)->toDateString() . ' ' . $inTime)
                ->subHours(48)
                ->format('d-m-Y h:i a');
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function buildCalendarData($rows, Carbon $targetMonth, ?array $dateCategories = null): array
    {
        $dateCategories = $dateCategories ?: $this->rosterDateCategoriesFromRows($rows);
        $employees = $rows
            ->groupBy('employee_id')
            ->map(function ($employeeRows) {
                $firstRow = $employeeRows->first();
                $employeeId = (int) $firstRow->employee_id;

                return [
                    'id' => $employeeId,
                    'employee_no' => (string) $firstRow->employee_no,
                    'employee_name' => (string) $firstRow->employee_name,
                    'branch_count' => $employeeRows->pluck('branch_id')->unique()->count(),
                ];
            })
            ->sortBy('employee_no', SORT_NATURAL)
            ->values()
            ->all();

        $cells = [];
        foreach ($rows as $row) {
            $dateKey = Carbon::parse($row->roster_date)->toDateString();
            $employeeId = (int) $row->employee_id;

            if (!isset($cells[$employeeId])) {
                $cells[$employeeId] = [];
            }

            if (!isset($cells[$employeeId][$dateKey])) {
                $cells[$employeeId][$dateKey] = [];
            }

            $cells[$employeeId][$dateKey][] = [
                'roster_id' => (int) $row->id,
                'unit_name' => (string) $row->unit_name,
                'branch_name' => (string) $row->branch_name,
                'branch_code' => $this->branchCode($row->branch_name),
                'in_time' => (string) $row->in_time,
                'out_time' => (string) $row->out_time,
                'time_short' => $this->shortTimeRange($row->in_time, $row->out_time),
                'time_display' => $this->displayTimeRange($row->in_time, $row->out_time),
                'shift_class' => $this->branchColorClass($row->branch_name),
                'can_reschedule' => $this->canRescheduleRosterRow($row),
                'can_edit_vhs_time' => (string) $row->category === self::VHS_TEACHER && $this->isEditableVhsSaturday($row->roster_date),
                'reschedule_deadline' => $this->rescheduleDeadlineLabel($row),
                'roster_date_label' => Carbon::parse($row->roster_date)->format('d-m-Y'),
            ];
        }

        return [
            'dates' => $this->calendarDates($targetMonth, $dateCategories),
            'employees' => $employees,
            'cells' => $cells,
        ];
    }

    private function calendarDates(Carbon $targetMonth, ?array $dateCategories = null): array
    {
        return $this->monthDates($targetMonth)
            ->map(function ($date) use ($targetMonth, $dateCategories) {
                $isRosterWorkingDate = $this->isRosterWorkingDate($date, $dateCategories);

                return [
                    'date' => $date->toDateString(),
                    'day_label' => $date->format('l'),
                    'short_day_label' => $date->format('D'),
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

    private function selectedRosterDateCategories(string $category, array $allowedCategories): array
    {
        return $category !== '' ? [$category] : $allowedCategories;
    }

    private function rosterDateCategoriesFromRows($rows): array
    {
        if (!method_exists($rows, 'pluck')) {
            return [];
        }

        return $this->normalizeRosterDateCategories($rows->pluck('category')->all());
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

    private function manualRosterShiftRows(int $employeeId, string $category, Carbon $rosterDate, string $branchName = '')
    {
        $query = EmployeeScheduleRoster::where('employee_id', '=', $employeeId)
            ->where('category', '=', $category)
            ->whereDate('roster_date', '=', $rosterDate->toDateString())
            ->where('status', '!=', 3);

        if ($branchName !== '') {
            $query->where('branch_name', '=', $branchName);
        }

        return $query
            ->orderBy('branch_id', 'ASC')
            ->orderBy('in_time', 'ASC')
            ->orderBy('id', 'ASC');
    }

    private function rosterRowsHaveAttendance(array $rosterIds): bool
    {
        $rosterIds = array_values(array_filter(array_map('intval', $rosterIds)));

        if (empty($rosterIds) || !DB::getSchemaBuilder()->hasTable('employee_attendances')) {
            return false;
        }

        return DB::table('employee_attendances')
            ->whereIn('roster_id', $rosterIds)
            ->exists();
    }

    private function isEditableVhsSaturday($rosterDate): bool
    {
        try {
            $date = $rosterDate instanceof Carbon ? $rosterDate->copy() : Carbon::parse($rosterDate);
        } catch (\Throwable $e) {
            return false;
        }

        if (!$date->isSaturday()) {
            return false;
        }

        return in_array((int) ceil($date->day / 7), [1, 3, 5], true);
    }

    private function rosterTimeUpdateHasConflict($row, string $inTime): bool
    {
        return EmployeeScheduleRoster::where('id', '!=', (int) $row->id)
            ->where('employee_id', '=', (int) $row->employee_id)
            ->where('category', '=', (string) $row->category)
            ->where('branch_id', '=', (int) $row->branch_id)
            ->whereDate('roster_date', '=', Carbon::parse($row->roster_date)->toDateString())
            ->where('in_time', '=', $inTime)
            ->exists();
    }

    private function temporaryRosterSwapDate($rows): Carbon
    {
        $baseDate = Carbon::create(1900, 1, 1)->startOfDay();

        for ($offset = 0; $offset < 3660; $offset++) {
            $temporaryDate = $baseDate->copy()->addDays($offset);
            $hasConflict = $rows->contains(function ($row) use ($temporaryDate) {
                return $this->rosterSwapDateHasConflict($row, $temporaryDate);
            });

            if (!$hasConflict) {
                return $temporaryDate;
            }
        }

        throw new \RuntimeException('Unable to reserve temporary roster date.');
    }

    private function rosterSwapDateHasConflict($row, Carbon $rosterDate): bool
    {
        $query = EmployeeScheduleRoster::where('id', '!=', (int) $row->id)
            ->where('employee_id', '=', (int) $row->employee_id)
            ->where('category', '=', (string) $row->category)
            ->where('branch_id', '=', (int) $row->branch_id)
            ->whereDate('roster_date', '=', $rosterDate->toDateString());

        $inTime = trim((string) $row->in_time);
        if ($inTime === '') {
            $query->where(function ($timeQuery) {
                $timeQuery->whereNull('in_time')->orWhere('in_time', '=', '');
            });
        } else {
            $query->where('in_time', '=', $inTime);
        }

        return $query->exists();
    }

    private function applyRosterDate($row, Carbon $rosterDate, int $userId): void
    {
        $row->fill([
            'roster_date' => $rosterDate->toDateString(),
            'roster_month' => (int) $rosterDate->month,
            'roster_year' => (int) $rosterDate->year,
            'day_name' => $rosterDate->format('l'),
            'updated_by' => $userId,
        ]);
        $row->save();
    }

    private function copySupportRoster(Carbon $sourceMonth, Carbon $targetMonth, string $category = '', int $employeeId = 0, ?array $allowedCategories = null): array
    {
        $allowedCategories = $allowedCategories ?: self::SUPPORT_CATEGORIES;
        $sourceRows = $this->getSupportRosterRows($sourceMonth, $category, '', $employeeId, $allowedCategories);
        $targetDates = $this->rosterDates($targetMonth, $this->selectedRosterDateCategories($category, $allowedCategories));
        $userId = $this->currentUserId();
        $result = [
            'source_rows' => $sourceRows->count(),
            'created' => 0,
            'restored' => 0,
            'existing' => 0,
        ];

        if ($sourceRows->isEmpty()) {
            return $result;
        }

        DB::transaction(function () use ($sourceRows, $targetDates, $targetMonth, $userId, &$result) {
            $sourceGroups = $sourceRows->groupBy(function ($row) {
                return (int) $row->employee_id . '|' . (string) $row->category . '|' . (int) $row->branch_id;
            });

            foreach ($sourceGroups as $groupRows) {
                $groupRows = $groupRows->sortBy('roster_date')->values();
                $firstRow = $groupRows->first();

                foreach ($targetDates as $targetDate) {
                    $sourceRow = $this->sourceRowForTargetDate($groupRows, $targetDate);
                    if (!$sourceRow) {
                        continue;
                    }

                    $keys = [
                        'employee_id' => (int) $firstRow->employee_id,
                        'category' => (string) $firstRow->category,
                        'branch_id' => (int) $firstRow->branch_id,
                        'roster_date' => $targetDate->toDateString(),
                    ];
                    $values = [
                        'employee_no' => (string) $firstRow->employee_no,
                        'employee_name' => (string) $firstRow->employee_name,
                        'unit_id' => (int) $firstRow->unit_id,
                        'unit_name' => (string) $firstRow->unit_name,
                        'branch_name' => (string) $firstRow->branch_name,
                        'roster_month' => (int) $targetMonth->month,
                        'roster_year' => (int) $targetMonth->year,
                        'day_name' => $targetDate->format('l'),
                        'in_time' => (string) $sourceRow->in_time,
                        'out_time' => (string) $sourceRow->out_time,
                        'status' => 1,
                        'generated_at' => now(),
                        'created_by' => (int) ($firstRow->created_by ?: $userId),
                        'updated_by' => $userId,
                    ];

                    $result[$this->saveRosterRowPreservingActive($keys, $values)]++;
                }
            }
        });

        return $result;
    }

    private function sourceRowForTargetDate($sourceRows, Carbon $targetDate)
    {
        $sameDayOfMonth = $sourceRows->first(function ($row) use ($targetDate) {
            return Carbon::parse($row->roster_date)->day === $targetDate->day;
        });

        if ($sameDayOfMonth) {
            return $sameDayOfMonth;
        }

        $sameWeekday = $sourceRows->first(function ($row) use ($targetDate) {
            return Carbon::parse($row->roster_date)->dayOfWeek === $targetDate->dayOfWeek;
        });

        return $sameWeekday ?: $sourceRows->first();
    }

    private function saveRosterRowPreservingActive(array $keys, array $values): string
    {
        $existingRoster = EmployeeScheduleRoster::where($keys)->first();

        if ($existingRoster && (int) $existingRoster->status !== 3) {
            return 'existing';
        }

        if ($existingRoster) {
            $existingRoster->fill($values);
            $existingRoster->save();

            return 'restored';
        }

        EmployeeScheduleRoster::create(array_merge($keys, $values));

        return 'created';
    }

    private function monthOptions(Carbon ...$targetMonths): array
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $months = collect([
            $currentMonth->copy(),
            $currentMonth->copy()->addMonth(),
        ]);

        foreach ($targetMonths as $targetMonth) {
            $months->push($targetMonth->copy()->startOfMonth());
        }

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

    private function resolveRosterMonth(Request $request): Carbon
    {
        return $this->resolveRosterMonthFromValue($request->input('month', Carbon::now()->format('Y-m')));
    }

    private function resolveRosterMonthFromValue($monthValue): Carbon
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

    private function parseRosterDateValue($dateValue): ?Carbon
    {
        $dateValue = trim((string) $dateValue);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateValue)) {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('Y-m-d', $dateValue)->startOfDay();

            return $date->toDateString() === $dateValue ? $date : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function resolveSupportCategory($category, ?array $allowedCategories = null): string
    {
        $allowedCategories = $allowedCategories ?: self::SUPPORT_CATEGORIES;
        $category = trim((string) $category);

        foreach ($allowedCategories as $supportCategory) {
            if (strcasecmp($category, $supportCategory) === 0) {
                return $supportCategory;
            }
        }

        return $allowedCategories[0] ?? self::SUPPORT_CATEGORIES[0];
    }

    private function resolveSupportSearchCategory($category, ?array $allowedCategories = null): string
    {
        $category = trim((string) $category);

        if ($category === '' || strtoupper($category) === 'ALL') {
            return '';
        }

        return $this->resolveSupportCategory($category, $allowedCategories);
    }

    private function resolveSupportBranchName($branchName): string
    {
        $branchName = trim((string) $branchName);

        foreach (self::SUPPORT_BRANCH_NAMES as $supportBranchName) {
            if (strcasecmp($branchName, $supportBranchName) === 0) {
                return $supportBranchName;
            }
        }

        return self::SUPPORT_BRANCH_NAMES[0];
    }

    private function resolveSupportBranchFilter($branchName): string
    {
        $branchName = trim((string) $branchName);

        if ($branchName === '' || strtoupper($branchName) === 'ALL') {
            return '';
        }

        return $this->resolveSupportBranchName($branchName);
    }

    private function resolveShiftBranchFilter($branchName): string
    {
        $branchName = trim((string) $branchName);

        if ($branchName === '' || strtoupper($branchName) === 'ALL' || strcasecmp($branchName, 'All Branches') === 0) {
            return '';
        }

        return $this->resolveSupportBranchName($branchName);
    }

    private function supportBranchSortIndex(string $branchName): int
    {
        foreach (self::SUPPORT_BRANCH_NAMES as $index => $supportBranchName) {
            if (strcasecmp($branchName, $supportBranchName) === 0) {
                return $index;
            }
        }

        return 99;
    }

    private function positiveInt($value): int
    {
        $value = (int) $value;

        return $value > 0 ? $value : 0;
    }

    private function branchLabelById(int $branchId): string
    {
        if ($branchId <= 0) {
            return '';
        }

        $branch = Branch::select('branches.id', 'branches.name', 'units.name as unit_name')
            ->leftJoin('units', 'units.id', '=', 'branches.unit_id')
            ->where('branches.id', '=', $branchId)
            ->first();

        if (!$branch) {
            return '';
        }

        $unitName = trim((string) ($branch->unit_name ?? ''));

        return trim((string) $branch->name . ($unitName !== '' ? ' - ' . $unitName : ''));
    }

    private function employeeLabelById(int $employeeId): string
    {
        if ($employeeId <= 0) {
            return '';
        }

        $employee = Employee::where('id', '=', $employeeId)->first();

        if (!$employee) {
            return '';
        }

        return trim((string) $employee->employee_no . ' - ' . $this->employeeName($employee));
    }

    private function employeeHasCategory($employee, string $category): bool
    {
        return in_array($category, $this->employeeCategoryValues($employee->category), true);
    }

    private function supportEmployeeHasActiveRoster(int $employeeId, string $category, Carbon $targetMonth): bool
    {
        return EmployeeScheduleRoster::where('employee_id', '=', $employeeId)
            ->where('category', '=', $category)
            ->where('roster_month', '=', (int) $targetMonth->month)
            ->where('roster_year', '=', (int) $targetMonth->year)
            ->where('status', '!=', 3)
            ->exists();
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

    private function normalizeBranchIds($branches): array
    {
        $decodedBranches = json_decode((string) $branches, true);
        $branches = is_array($decodedBranches) ? $decodedBranches : [];
        $branches = array_map('intval', $branches);
        $branches = array_filter($branches);

        return array_values(array_unique($branches));
    }

    private function employeeName($employee): string
    {
        return collect([
            $employee->first_name,
            $employee->middle_name,
            $employee->last_name,
        ])->map(function ($namePart) {
            return trim((string) $namePart);
        })->filter()->implode(' ');
    }

    private function formatRosterTime($value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return Carbon::createFromFormat('H:i', substr($value, 0, 5))->format('H:i');
    }

    private function normalizeSubmittedTime($value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('H:i', substr($value, 0, 5))->format('H:i');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function isValidRosterTimeRange(string $inTime, string $outTime): bool
    {
        return strcmp($inTime, $outTime) < 0;
    }

    private function hasOverlappingTsaClass(
        int $employeeId,
        Carbon $rosterDate,
        string $inTime,
        string $outTime,
        int $excludeRosterId = 0
    ): bool {
        $query = EmployeeScheduleRoster::where('employee_id', '=', $employeeId)
            ->where('category', '=', self::TSA_TEACHER)
            ->whereDate('roster_date', '=', $rosterDate->toDateString())
            ->where('status', '!=', 3)
            ->where('in_time', '<', $outTime)
            ->where('out_time', '>', $inTime);

        if ($excludeRosterId > 0) {
            $query->where('id', '!=', $excludeRosterId);
        }

        return $query->exists();
    }

    private function displayTimeRange($inTime, $outTime): string
    {
        $inTime = trim((string) $inTime);
        $outTime = trim((string) $outTime);

        if ($inTime === '' || $outTime === '') {
            return '';
        }

        return Carbon::createFromFormat('H:i', substr($inTime, 0, 5))->format('g:i a')
            . ' - '
            . Carbon::createFromFormat('H:i', substr($outTime, 0, 5))->format('h:i a');
    }

    private function shortTimeRange($inTime, $outTime): string
    {
        $inTime = trim((string) $inTime);
        $outTime = trim((string) $outTime);

        if ($inTime === '' || $outTime === '') {
            return '';
        }

        return str_replace(':00', '', substr($inTime, 0, 5)) . '-' . str_replace(':00', '', substr($outTime, 0, 5));
    }

    private function branchCode($branchName): string
    {
        $branchName = preg_replace('/[^A-Za-z0-9]/', '', (string) $branchName);

        return strtoupper(substr($branchName, 0, 3));
    }

    private function safeFilename($value): string
    {
        $filename = strtolower(preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) $value));
        $filename = trim($filename, '-_');

        return $filename !== '' ? $filename : 'roster';
    }

    private function buildGenerationMessage(array $result): string
    {
        return 'VHS Teacher roster generated. New rows: ' . $result['created'] . ', existing locked rows kept: ' . $result['existing'] . ', skipped assignments: ' . $result['skipped'] . '.';
    }

    private function currentUserId(): int
    {
        if (session()->has('user_data') && array_key_exists('user_id', session('user_data'))) {
            return (int) session('user_data')['user_id'];
        }

        return Auth::check() ? (int) Auth::id() : 0;
    }
}
