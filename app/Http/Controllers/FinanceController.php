<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Transaction;
use App\Models\User;
use App\Services\SiteAuthService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    protected $siteAuthService;
    protected $data;

    public function __construct()
    {
        $this->data = array(
            'title'             => 'Transactions',
            'controller'        => 'FinanceController',
            'controller_route'  => 'finance',
            'primary_key'       => 'id',
        );
        $this->siteAuthService = new SiteAuthService();
    }

    /* list */
    public function list(Request $request)
    {
        $data['module']         = $this->data;
        $title                  = $this->data['title'] . ' List';
        $page_name              = 'finance.list';

        $fromDate               = $request->from_date;
        $toDate                 = $request->to_date;
        $type                   = $request->type;
        $createdBy              = $request->created_by;

        $data['from_date']      = $fromDate;
        $data['to_date']        = $toDate;
        $data['type']           = $type;
        $data['created_by']     = $createdBy;

        $query = Transaction::select(
                                'transactions.*',
                                DB::raw("CONCAT(COALESCE(creator.first_name,''), ' ', COALESCE(creator.last_name,'')) as creator_name")
                            )
                            ->leftJoin('users as creator', 'creator.id', '=', 'transactions.created_by')
                            ->where('transactions.status', '!=', 3);

        if ($fromDate != '') {
            $query->whereDate('transactions.transaction_timestamp', '>=', $fromDate);
        }

        if ($toDate != '') {
            $query->whereDate('transactions.transaction_timestamp', '<=', $toDate);
        }

        if (in_array($type, ['INCOME', 'EXPENSE'])) {
            $query->where('transactions.type', '=', $type);
        }

        if ($createdBy != '' && is_numeric($createdBy)) {
            $query->where('transactions.created_by', '=', (int)$createdBy);
        }

        $rows = $query->orderByRaw('CAST(transactions.txn_no AS UNSIGNED) DESC')
                        ->orderBy('transactions.id', 'DESC')
                        ->get();

        $data['rows'] = $rows;
        $data['users'] = User::select('id', 'first_name', 'last_name')
                                ->where('status', '!=', 3)
                                ->orderBy('first_name', 'ASC')
                                ->orderBy('last_name', 'ASC')
                                ->get();

        $data['total_income']   = (float)$rows->where('type', 'INCOME')->sum('transaction_amount');
        $data['total_expense']  = (float)$rows->where('type', 'EXPENSE')->sum('transaction_amount');
        $data['net_balance']    = (float)$data['total_income'] - (float)$data['total_expense'];
        $data['total_records']  = (int)$rows->count();

        $data = $this->siteAuthService->admin_after_login_layout($title, $page_name, $data);
        return view('front.pages.' . $page_name, $data);
    }
    /* list */

    /* add */
    public function add(Request $request)
    {
        $data['module']                 = $this->data;
        $title                          = $this->data['title'] . ' Update';
        $page_name                      = 'finance.add-edit';
        $data['row']                    = [];
        $data['action']                 = 'Add';
        $data['default_created_by']     = $this->currentUserId();

        $data['users'] = User::select('id', 'first_name', 'last_name')
                                ->where('status', '!=', 3)
                                ->orderBy('first_name', 'ASC')
                                ->orderBy('last_name', 'ASC')
                                ->get();

        if ($request->isMethod('post')) {
            $request->validate([
                'type'                   => 'required|in:INCOME,EXPENSE',
                'transaction_timestamp'  => 'required|date',
                'transaction_amount'     => 'required|numeric|gt:0',
                'particulars'            => 'required|string|max:1000',
                'note'                   => 'nullable|string|max:2000',
                'created_by'             => 'required|integer|exists:users,id',
            ]);

            $updatedBy = $this->currentUserId();
            $createdBy = (int)$request->created_by;

            DB::transaction(function () use ($request, $updatedBy, $createdBy) {
                $lastTransaction = Transaction::withTrashed()->select('sl_no')
                                        ->orderBy('sl_no', 'DESC')
                                        ->lockForUpdate()
                                        ->first();

                $nextSlNo  = (($lastTransaction) ? ((int)$lastTransaction->sl_no + 1) : 1);
                $nextTxnNo = str_pad($nextSlNo, 8, '0', STR_PAD_LEFT);

                Transaction::create([
                    'sl_no'                  => $nextSlNo,
                    'txn_no'                 => $nextTxnNo,
                    'fee_id'                 => 0,
                    'type'                   => $request->type,
                    'transaction_timestamp'  => Carbon::parse($request->transaction_timestamp),
                    'transaction_amount'     => number_format((float)$request->transaction_amount, 2, '.', ''),
                    'particulars'            => trim($request->particulars),
                    'note'                   => trim((string)$request->note),
                    'status'                 => 1,
                    'created_by'             => $createdBy,
                    'updated_by'             => $updatedBy,
                ]);
            });

            return redirect($this->data['controller_route'] . "/list")->with('success_message', 'Transaction added successfully !!!');
        }

        $data = $this->siteAuthService->admin_after_login_layout($title, $page_name, $data);
        return view('front.pages.' . $page_name, $data);
    }
    /* add */

    /* edit */
    public function edit(Request $request, $id)
    {
        $data['module']                 = $this->data;
        $id                             = Helper::decoded($id);
        $title                          = $this->data['title'] . ' Update';
        $page_name                      = 'finance.add-edit';
        $data['row']                    = Transaction::where($this->data['primary_key'], '=', $id)
                                                    ->where('status', '!=', 3)
                                                    ->first();
        $data['action']                 = 'Edit';
        $data['default_created_by']     = $this->currentUserId();

        if (!$data['row']) {
            return redirect($this->data['controller_route'] . "/list")->with('error_message', 'Transaction not found !!!');
        }

        $data['users'] = User::select('id', 'first_name', 'last_name')
                                ->where('status', '!=', 3)
                                ->orderBy('first_name', 'ASC')
                                ->orderBy('last_name', 'ASC')
                                ->get();

        if ($request->isMethod('post')) {
            $request->validate([
                'type'                   => 'required|in:INCOME,EXPENSE',
                'transaction_timestamp'  => 'required|date',
                'transaction_amount'     => 'required|numeric|gt:0',
                'particulars'            => 'required|string|max:1000',
                'note'                   => 'nullable|string|max:2000',
                'created_by'             => 'required|integer|exists:users,id',
            ]);

            $member = Transaction::findOrFail($id);
            $member->update([
                'type'                   => $request->type,
                'transaction_timestamp'  => Carbon::parse($request->transaction_timestamp),
                'transaction_amount'     => number_format((float)$request->transaction_amount, 2, '.', ''),
                'particulars'            => trim($request->particulars),
                'note'                   => trim((string)$request->note),
                'created_by'             => (int)$request->created_by,
                'updated_by'             => $this->currentUserId(),
            ]);

            return redirect($this->data['controller_route'] . "/list")->with('success_message', 'Transaction updated successfully !!!');
        }

        $data = $this->siteAuthService->admin_after_login_layout($title, $page_name, $data);
        return view('front.pages.' . $page_name, $data);
    }
    /* edit */

    /* delete */
    public function delete(Request $request, $id)
    {
        $id = Helper::decoded($id);

        Transaction::where($this->data['primary_key'], '=', $id)->update([
            'status'     => 3,
            'deleted_at' => date('Y-m-d H:i:s'),
            'updated_by' => $this->currentUserId(),
        ]);

        return redirect($this->data['controller_route'] . "/list")->with('success_message', 'Transaction deleted successfully !!!');
    }
    /* delete */

    /* invoice */
    public function invoice(Request $request, $id)
    {
        $id = Helper::decoded($id);

        $transaction = Transaction::select(
                                    'transactions.*',
                                    DB::raw("CONCAT(COALESCE(creator.first_name,''), ' ', COALESCE(creator.last_name,'')) as creator_name"),
                                    DB::raw("CONCAT(COALESCE(updater.first_name,''), ' ', COALESCE(updater.last_name,'')) as updater_name")
                                )
                                ->leftJoin('users as creator', 'creator.id', '=', 'transactions.created_by')
                                ->leftJoin('users as updater', 'updater.id', '=', 'transactions.updated_by')
                                ->where('transactions.id', '=', $id)
                                ->where('transactions.status', '!=', 3)
                                ->first();

        if (!$transaction) {
            return redirect($this->data['controller_route'] . "/list")->with('error_message', 'Transaction not found !!!');
        }

        $data['title']       = 'Transaction Invoice';
        $data['transaction'] = $transaction;

        return view('front.pages.finance.invoice', $data);
    }
    /* invoice */

    private function currentUserId()
    {
        if (session()->has('user_data') && array_key_exists('user_id', session('user_data'))) {
            return (int)session('user_data')['user_id'];
        }

        return ((Auth::check()) ? (int)Auth::id() : 0);
    }
}
