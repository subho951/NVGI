<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\LeaveType;
use App\Services\SiteAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class LeaveTypeController extends Controller
{
    protected $siteAuthService;
    protected $data;

    public function __construct()
    {
        $this->data = [
            'title' => 'Leave Type',
            'controller' => 'LeaveTypeController',
            'controller_route' => 'payroll-leave/leave-type',
            'primary_key' => 'id',
        ];

        $this->siteAuthService = new SiteAuthService();
    }

    public function list(Request $request)
    {
        $data['module'] = $this->data;
        $title = $this->data['title'].' List';
        $pageName = 'payroll-leave.leave-type.list';
        $data['rows'] = LeaveType::where('status', '!=', 3)->orderBy('id', 'DESC')->get();
        $data['action'] = 'List';

        $data = $this->siteAuthService->admin_after_login_layout($title, $pageName, $data);

        return view('front.pages.'.$pageName, $data);
    }

    public function add(Request $request)
    {
        $data['module'] = $this->data;
        $title = $this->data['title'].' Add';
        $pageName = 'payroll-leave.leave-type.add-edit';
        $data['row'] = null;
        $data['action'] = 'Add';

        if ($request->isMethod('post')) {
            $request->validate($this->validationRules());

            LeaveType::create($this->payload($request) + [
                'status' => 1,
                'created_by' => $this->currentUserId(),
                'updated_by' => $this->currentUserId(),
            ]);

            return redirect($this->data['controller_route'].'/list')->with('success_message', $this->data['title'].' added successfully !!!');
        }

        $data = $this->siteAuthService->admin_after_login_layout($title, $pageName, $data);

        return view('front.pages.'.$pageName, $data);
    }

    public function edit(Request $request, $id)
    {
        $data['module'] = $this->data;
        $id = Helper::decoded($id);
        $title = $this->data['title'].' Edit';
        $pageName = 'payroll-leave.leave-type.add-edit';
        $data['row'] = LeaveType::where($this->data['primary_key'], '=', $id)
            ->where('status', '!=', 3)
            ->first();
        $data['action'] = 'Edit';

        if (! $data['row']) {
            return redirect($this->data['controller_route'].'/list')->with('error_message', 'Leave type not found !!!');
        }

        if ($request->isMethod('post')) {
            $request->validate($this->validationRules($data['row']->id));

            $data['row']->update($this->payload($request) + [
                'updated_by' => $this->currentUserId(),
            ]);

            return redirect($this->data['controller_route'].'/list')->with('success_message', $this->data['title'].' updated successfully !!!');
        }

        $data = $this->siteAuthService->admin_after_login_layout($title, $pageName, $data);

        return view('front.pages.'.$pageName, $data);
    }

    public function change_status(Request $request, $id)
    {
        $id = Helper::decoded($id);
        $model = LeaveType::where($this->data['primary_key'], '=', $id)
            ->where('status', '!=', 3)
            ->first();

        if (! $model) {
            return redirect($this->data['controller_route'].'/list')->with('error_message', 'Leave type not found !!!');
        }

        if ((int) $model->status === 1) {
            $model->status = 0;
            $msg = 'deactivated';
        } else {
            $model->status = 1;
            $msg = 'activated';
        }

        $model->updated_by = $this->currentUserId();
        $model->save();

        return redirect($this->data['controller_route'].'/list')->with('success_message', $this->data['title'].' '.$msg.' successfully !!!');
    }

    private function validationRules($ignoreId = null): array
    {
        $nameRule = Rule::unique('leave_types', 'name')->whereNull('deleted_at');

        if ($ignoreId !== null) {
            $nameRule->ignore($ignoreId);
        }

        return [
            'leave_type_name' => ['required', 'string', 'max:255', $nameRule],
            'leave_type_description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function payload(Request $request): array
    {
        return [
            'name' => trim((string) $request->leave_type_name),
            'description' => $this->nullableText($request->leave_type_description),
        ];
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
