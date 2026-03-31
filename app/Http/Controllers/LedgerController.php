<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Ledger;
use App\Services\SiteAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class LedgerController extends Controller
{
    protected $siteAuthService;
    protected $data;

    public function __construct()
    {
        $this->data = array(
            'title'             => 'Ledger',
            'controller'        => 'LedgerController',
            'controller_route'  => 'finance/ledger',
            'primary_key'       => 'id',
        );
        $this->siteAuthService = new SiteAuthService();
    }

    /* list */
    public function list(Request $request)
    {
        $data['module']     = $this->data;
        $title              = $this->data['title'] . ' List';
        $page_name          = 'finance.ledger.list';
        $data['rows']       = Ledger::where('status', '!=', 3)->orderBy('id', 'DESC')->get();
        $data['action']     = 'Add';

        $data = $this->siteAuthService->admin_after_login_layout($title, $page_name, $data);
        return view('front.pages.' . $page_name, $data);
    }
    /* list */

    /* add */
    public function add(Request $request)
    {
        $data['module']     = $this->data;
        $title              = $this->data['title'] . ' Add';
        $page_name          = 'finance.ledger.add-edit';
        $data['row']        = [];
        $data['action']     = 'Add';

        if ($request->isMethod('post')) {
            $request->validate([
                'type' => 'required|in:INCOME,EXPENSE',
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('ledgers', 'name')->whereNull('deleted_at'),
                ],
            ]);

            $userId = $this->currentUserId();

            Ledger::create([
                'type'       => trim($request->type),
                'name'       => trim($request->name),
                'status'     => 1,
                'created_by' => $userId,
                'updated_by' => $userId,
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
        $data['module']     = $this->data;
        $id                 = Helper::decoded($id);
        $title              = $this->data['title'] . ' Edit';
        $page_name          = 'finance.ledger.add-edit';
        $data['row']        = Ledger::where($this->data['primary_key'], '=', $id)
                                    ->where('status', '!=', 3)
                                    ->first();
        $data['action']     = 'Edit';

        if (!$data['row']) {
            return redirect($this->data['controller_route'] . '/list')->with('error_message', 'Ledger not found !!!');
        }

        if ($request->isMethod('post')) {
            $request->validate([
                'type' => 'required|in:INCOME,EXPENSE',
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('ledgers', 'name')
                        ->whereNull('deleted_at')
                        ->ignore($data['row']->id),
                ],
            ]);

            $userId = $this->currentUserId();

            $data['row']->update([
                'type'       => trim($request->type),
                'name'       => trim($request->name),
                'updated_by' => $userId,
            ]);

            return redirect($this->data['controller_route'] . '/list')->with('success_message', $this->data['title'] . ' updated successfully !!!');
        }

        $data = $this->siteAuthService->admin_after_login_layout($title, $page_name, $data);
        return view('front.pages.' . $page_name, $data);
    }
    /* edit */

    /* delete */
    public function delete(Request $request, $id)
    {
        $id = Helper::decoded($id);

        $ledger = Ledger::where($this->data['primary_key'], '=', $id)
                        ->where('status', '!=', 3)
                        ->first();

        if (!$ledger) {
            return redirect($this->data['controller_route'] . '/list')->with('error_message', 'Ledger not found !!!');
        }

        $ledger->update([
            'status'     => 3,
            'deleted_at' => date('Y-m-d H:i:s'),
            'updated_by' => $this->currentUserId(),
        ]);

        return redirect($this->data['controller_route'] . '/list')->with('success_message', $this->data['title'] . ' deleted successfully !!!');
    }
    /* delete */

    /* change status */
    public function change_status(Request $request, $id)
    {
        $id = Helper::decoded($id);

        $ledger = Ledger::where($this->data['primary_key'], '=', $id)
                        ->where('status', '!=', 3)
                        ->first();

        if (!$ledger) {
            return redirect($this->data['controller_route'] . '/list')->with('error_message', 'Ledger not found !!!');
        }

        if ((int)$ledger->status === 1) {
            $ledger->status = 0;
            $msg = 'blocked';
        } else {
            $ledger->status = 1;
            $msg = 'activated';
        }

        $ledger->updated_by = $this->currentUserId();
        $ledger->save();

        return redirect($this->data['controller_route'] . '/list')->with('success_message', $this->data['title'] . ' ' . $msg . ' successfully !!!');
    }
    /* change status */

    private function currentUserId()
    {
        if (session()->has('user_data') && array_key_exists('user_id', session('user_data'))) {
            return (int)session('user_data')['user_id'];
        }

        return ((Auth::check()) ? (int)Auth::id() : 0);
    }
}
