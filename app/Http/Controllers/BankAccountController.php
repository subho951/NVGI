<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\BankAccount;
use App\Services\SiteAuthService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BankAccountController extends Controller
{
    protected $siteAuthService;
    protected $data;

    public function __construct()
    {
        $this->data = [
            'title'            => 'Bank Account',
            'controller'       => 'BankAccountController',
            'controller_route' => 'bank-account',
            'primary_key'      => 'id',
        ];

        $this->siteAuthService = new SiteAuthService();
    }

    /* list */
    public function list(Request $request)
    {
        $data['module'] = $this->data;
        $title = $this->data['title'] . ' List';
        $page_name = 'bank-account.list';
        $data['rows'] = BankAccount::where('status', '!=', 3)->orderBy('id', 'DESC')->get();
        $data['action'] = 'Add';

        $data = $this->siteAuthService->admin_after_login_layout($title, $page_name, $data);

        return view('front.pages.' . $page_name, $data);
    }
    /* list */

    /* add */
    public function add(Request $request)
    {
        $data['module'] = $this->data;
        $title = $this->data['title'] . ' Add';
        $page_name = 'bank-account.add-edit';
        $data['row'] = null;
        $data['action'] = 'Add';

        if ($request->isMethod('post')) {
            $request->validate($this->validationRules());

            BankAccount::create($this->payload($request) + [
                'status' => 1,
            ]);

            return redirect($this->data['controller_route'] . '/list')->with('success_message', $this->data['title'] . ' added successfully !!!');
        }

        $data = $this->siteAuthService->admin_after_login_layout($title, $page_name, $data);

        return view('front.pages.' . $page_name, $data);
    }
    /* add */

    /* edit */
    public function edit(Request $request, $id)
    {
        $data['module'] = $this->data;
        $id = Helper::decoded($id);
        $title = $this->data['title'] . ' Edit';
        $page_name = 'bank-account.add-edit';
        $data['row'] = BankAccount::where($this->data['primary_key'], '=', $id)
            ->where('status', '!=', 3)
            ->first();
        $data['action'] = 'Edit';

        if (!$data['row']) {
            return redirect($this->data['controller_route'] . '/list')->with('error_message', 'Bank account not found !!!');
        }

        if ($request->isMethod('post')) {
            $request->validate($this->validationRules($data['row']->id));

            $data['row']->update($this->payload($request));

            return redirect($this->data['controller_route'] . '/list')->with('success_message', $this->data['title'] . ' updated successfully !!!');
        }

        $data = $this->siteAuthService->admin_after_login_layout($title, $page_name, $data);

        return view('front.pages.' . $page_name, $data);
    }
    /* edit */

    /* change status */
    public function change_status(Request $request, $id)
    {
        $id = Helper::decoded($id);
        $model = BankAccount::where($this->data['primary_key'], '=', $id)
            ->where('status', '!=', 3)
            ->first();

        if (!$model) {
            return redirect($this->data['controller_route'] . '/list')->with('error_message', 'Bank account not found !!!');
        }

        if ((int) $model->status === 1) {
            $model->status = 0;
            $msg = 'blocked';
        } else {
            $model->status = 1;
            $msg = 'activated';
        }

        $model->save();

        return redirect($this->data['controller_route'] . '/list')->with('success_message', $this->data['title'] . ' ' . $msg . ' successfully !!!');
    }
    /* change status */

    private function validationRules($ignoreId = null): array
    {
        $accountNoRule = Rule::unique('bank_accounts', 'account_no')
            ->whereNull('deleted_at');

        if ($ignoreId !== null) {
            $accountNoRule->ignore($ignoreId);
        }

        return [
            'bank_name' => ['required', 'string', 'max:255'],
            'bank_branch' => ['required', 'string', 'max:255'],
            // 'account_no' => ['required', 'string', 'max:50', $accountNoRule],
            // 'ifsc_code' => ['required', 'string', 'size:11', 'regex:/^[A-Za-z]{4}0[A-Za-z0-9]{6}$/'],
            'account_type' => ['required', Rule::in(['CURRENT', 'SAVINGS'])],
        ];
    }

    private function payload(Request $request): array
    {
        return [
            'bank_name' => trim((string) $request->bank_name),
            'bank_branch' => trim((string) $request->bank_branch),
            'account_no' => trim((string) $request->account_no),
            'ifsc_code' => strtoupper(trim((string) $request->ifsc_code)),
            'account_type' => strtoupper(trim((string) $request->account_type)),
        ];
    }
}
