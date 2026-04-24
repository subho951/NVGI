<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Branch;
use App\Models\BankAccount;
use App\Models\Ledger;
use App\Models\Transaction;
use App\Models\Unit;
use App\Models\User;
use App\Services\SiteAuthService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
        $unitId                 = $request->unit_id;
        $branchId               = $request->branch_id;
        $ledgerId               = $request->ledger_id;
        $paymentMode            = $request->payment_mode;

        $data['from_date']      = $fromDate;
        $data['to_date']        = $toDate;
        $data['type']           = $type;
        $data['created_by']     = $createdBy;
        $data['unit_id']        = $unitId;
        $data['branch_id']      = $branchId;
        $data['ledger_id']      = $ledgerId;
        $data['payment_mode']   = $paymentMode;

        $query = Transaction::select(
                                'transactions.*',
                                DB::raw("COALESCE(tx_ledgers.name, '') as ledger_name"),
                                DB::raw("COALESCE(tx_bank_accounts.bank_name, '') as bank_account_name"),
                                DB::raw("COALESCE(tx_bank_accounts.account_no, '') as bank_account_no"),
                                DB::raw("COALESCE(tx_units.name, fee_units.name) as unit_name"),
                                DB::raw("COALESCE(tx_branches.name, fee_branches.name) as branch_name"),
                                DB::raw("CONCAT(COALESCE(creator.first_name,''), ' ', COALESCE(creator.last_name,'')) as creator_name")
                            )
                            ->leftJoin('student_payments as fee_payments', 'fee_payments.id', '=', 'transactions.fee_id')
                            ->leftJoin('ledgers as tx_ledgers', 'tx_ledgers.id', '=', 'transactions.ledger_id')
                            ->leftJoin('bank_accounts as tx_bank_accounts', 'tx_bank_accounts.id', '=', 'transactions.bank_account_id')
                            ->leftJoin('users as creator', 'creator.id', '=', 'transactions.created_by')
                            ->leftJoin('units as tx_units', 'tx_units.id', '=', 'transactions.unit_id')
                            ->leftJoin('branches as tx_branches', 'tx_branches.id', '=', 'transactions.branch_id')
                            ->leftJoin('units as fee_units', 'fee_units.id', '=', 'fee_payments.unit_id')
                            ->leftJoin('branches as fee_branches', 'fee_branches.id', '=', 'fee_payments.branch_id')
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

        if ($unitId != '' && is_numeric($unitId) && (int)$unitId > 0) {
            $query->whereRaw('COALESCE(NULLIF(transactions.unit_id, 0), fee_payments.unit_id) = ?', [(int)$unitId]);
        }

        if ($branchId != '' && is_numeric($branchId) && (int)$branchId > 0) {
            $query->whereRaw('COALESCE(NULLIF(transactions.branch_id, 0), fee_payments.branch_id) = ?', [(int)$branchId]);
        }

        if ($ledgerId != '' && is_numeric($ledgerId) && (int)$ledgerId > 0) {
            $query->where('transactions.ledger_id', '=', (int)$ledgerId);
        }

        if (in_array($paymentMode, ['Cash', 'Bank'])) {
            $query->where('transactions.payment_mode', '=', $paymentMode);
        }

        $rows = $query->orderByRaw('CAST(transactions.txn_no AS UNSIGNED) DESC')
                        ->orderBy('transactions.id', 'DESC')
                        ->get();

        $rows->each(function ($row) {
            $row->can_edit = $this->isEditableTransaction($row);
        });

        $data['rows'] = $rows;
        $data['users'] = User::select('id', 'first_name', 'last_name')
                                ->where('status', '!=', 3)
                                ->orderBy('first_name', 'ASC')
                                ->orderBy('last_name', 'ASC')
                                ->get();
        $data['units'] = Unit::select('id', 'name')
                                ->where('status', '!=', 3)
                                ->orderBy('name', 'ASC')
                                ->get();
        $data['branches'] = Branch::select('id', 'name', 'unit_id')
                                    ->where('status', '!=', 3)
                                    ->orderBy('name', 'ASC')
                                    ->get();
        $data['ledgers'] = $this->ledgerOptions($ledgerId);

        $data['total_income']   = (float)$rows->where('type', 'INCOME')->sum('transaction_amount');
        $data['total_expense']  = (float)$rows->where('type', 'EXPENSE')->sum('transaction_amount');
        $data['net_balance']    = (float)$data['total_income'] - (float)$data['total_expense'];
        $data['total_records']  = (int)$rows->count();
        $data['unit_summary_rows'] = $rows->groupBy(function ($row) {
                                            $unitName = trim((string)$row->unit_name);
                                            return ($unitName !== '') ? $unitName : 'Unassigned';
                                        })->map(function ($group, $unitName) {
                                            $income = (float)$group->where('type', 'INCOME')->sum('transaction_amount');
                                            $expense = (float)$group->where('type', 'EXPENSE')->sum('transaction_amount');

                                            return [
                                                'unit_name'   => $unitName,
                                                'records'     => (int)$group->count(),
                                                'income'      => $income,
                                                'expense'     => $expense,
                                                'net_balance' => $income - $expense,
                                            ];
                                        })->sortBy('unit_name')->values();
        $data['branch_summary_rows'] = $rows->groupBy(function ($row) {
                                                $unitName = trim((string)$row->unit_name);
                                                $branchName = trim((string)$row->branch_name);

                                                $unitLabel = ($unitName !== '') ? $unitName : 'Unassigned';
                                                $branchLabel = ($branchName !== '') ? $branchName : 'Unassigned';

                                                return $unitLabel . '||' . $branchLabel;
                                            })->map(function ($group) {
                                                $first = $group->first();
                                                $unitName = trim((string)$first->unit_name);
                                                $branchName = trim((string)$first->branch_name);
                                                $income = (float)$group->where('type', 'INCOME')->sum('transaction_amount');
                                                $expense = (float)$group->where('type', 'EXPENSE')->sum('transaction_amount');

                                                return [
                                                    'unit_name'   => ($unitName !== '') ? $unitName : 'Unassigned',
                                                    'branch_name' => ($branchName !== '') ? $branchName : 'Unassigned',
                                                    'records'     => (int)$group->count(),
                                                    'income'      => $income,
                                                    'expense'     => $expense,
                                                    'net_balance' => $income - $expense,
                                                ];
                                            })->sortBy(function ($row) {
                                                return $row['unit_name'] . '||' . $row['branch_name'];
                                            })->values();

        $data = $this->siteAuthService->admin_after_login_layout($title, $page_name, $data);
        return view('front.pages.' . $page_name, $data);
    }
    /* list */

    /* add */
    public function add(Request $request)
    {
        $data['module']                 = $this->data;
        $title                          = $this->data['title'] . ' Add';
        $page_name                      = 'finance.add-edit';
        $data['row']                    = [];
        $data['action']                 = 'Add';
        $data['default_created_by']     = $this->currentUserId();
        $data['units'] = Unit::select('id', 'name')
                                ->where('status', '!=', 3)
                                ->orderBy('name', 'ASC')
                                ->get();
        $data['branches'] = Branch::select('id', 'name', 'unit_id')
                                    ->where('status', '!=', 3)
                                    ->orderBy('name', 'ASC')
                                    ->get();
        $data['ledgers'] = $this->ledgerOptions();
        $data['bankAccounts'] = $this->bankAccountOptions();

        $data['users'] = User::select('id', 'first_name', 'last_name')
                                ->where('status', '!=', 3)
                                ->orderBy('first_name', 'ASC')
                                ->orderBy('last_name', 'ASC')
                                ->get();

        if ($request->isMethod('post')) {
            $transactionType = $request->type;
            $bankAccountRules = (($request->input('payment_mode') === 'Bank')
                                ? [
                                    'required',
                                    'integer',
                                    Rule::exists('bank_accounts', 'id')->where(function ($query) {
                                        $query->where('status', '!=', 3);
                                    }),
                                ]
                                : [
                                    'nullable',
                                    'integer',
                                ]);

            $request->validate([
                'type'                  => 'required|in:INCOME,EXPENSE',
                'ledger_id'             => [
                    'required_if:type,INCOME,EXPENSE',
                    'integer',
                    Rule::exists('ledgers', 'id')->where(function ($query) use ($transactionType) {
                        $query->where('status', '!=', 3)
                                ->where('type', '=', $transactionType);
                    }),
                ],
                'transaction_timestamp' => 'required|date',
                'transaction_amount'    => 'required|numeric|gt:0',
                'unit_id'               => [
                    'required',
                    'integer',
                    Rule::exists('units', 'id')->where(function ($query) {
                        $query->where('status', '!=', 3);
                    }),
                ],
                'branch_id'             => [
                    'required',
                    'integer',
                    Rule::exists('branches', 'id')->where(function ($query) use ($request) {
                        $query->where('status', '!=', 3)
                                ->where('unit_id', (int)$request->unit_id);
                    }),
                ],
                'payment_mode'          => 'required|in:Cash,Bank',
                'bank_account_id'       => $bankAccountRules,
                'payment_reference'     => 'nullable|string|max:2000',
                'particulars'           => 'required|string|max:1000',
                'note'                  => 'nullable|string|max:2000',
                'created_by'            => 'required|integer|exists:users,id',
            ], [
                'ledger_id.required_if' => 'Please select a ledger for the selected transaction type.',
                'ledger_id.exists'      => 'Selected ledger is not valid for the chosen transaction type.',
                'bank_account_id.required' => 'Please select a bank account when payment mode is Bank.',
                'bank_account_id.exists'    => 'Selected bank account is not valid.',
            ]);

            $updatedBy = $this->currentUserId();
            $createdBy = (int)$request->created_by;
            $bankAccountId = (($request->payment_mode === 'Bank') ? (int)$request->bank_account_id : null);

            DB::transaction(function () use ($request, $updatedBy, $createdBy, $bankAccountId) {
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
                    'ledger_id'              => (int)$request->ledger_id,
                    'unit_id'                => (int)$request->unit_id,
                    'branch_id'              => (int)$request->branch_id,
                    'payment_mode'           => $request->payment_mode,
                    'bank_account_id'        => $bankAccountId,
                    'payment_reference'      => $this->normalizePaymentReference($request->payment_mode, $request->payment_reference),
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
        $title                          = $this->data['title'] . ' Edit';
        $page_name                      = 'finance.add-edit';
        $data['row']                    = Transaction::where($this->data['primary_key'], '=', $id)
                                                    ->where('status', '!=', 3)
                                                    ->first();
        $data['action']                 = 'Edit';
        $data['default_created_by']     = $this->currentUserId();
        $data['units'] = Unit::select('id', 'name')
                                ->where('status', '!=', 3)
                                ->orderBy('name', 'ASC')
                                ->get();
        $data['branches'] = Branch::select('id', 'name', 'unit_id')
                                    ->where('status', '!=', 3)
                                    ->orderBy('name', 'ASC')
                                    ->get();
        $data['ledgers'] = $this->ledgerOptions(($data['row']->ledger_id ?? null));
        $data['bankAccounts'] = $this->bankAccountOptions(($data['row']->bank_account_id ?? null));

        if (!$data['row']) {
            return redirect($this->data['controller_route'] . "/list")->with('error_message', 'Transaction not found !!!');
        }

        // if (!$this->isEditableTransaction($data['row'])) {
        //     return redirect($this->data['controller_route'] . "/list")->with('error_message', 'This transaction cannot be edited !!!');
        // }

        $data['users'] = User::select('id', 'first_name', 'last_name')
                                ->where('status', '!=', 3)
                                ->orderBy('first_name', 'ASC')
                                ->orderBy('last_name', 'ASC')
                                ->get();

        if ($request->isMethod('post')) {
            $transactionType = $request->type;
            $bankAccountRules = (($request->input('payment_mode') === 'Bank')
                                ? [
                                    'required',
                                    'integer',
                                    Rule::exists('bank_accounts', 'id')->where(function ($query) {
                                        $query->where('status', '!=', 3);
                                    }),
                                ]
                                : [
                                    'nullable',
                                    'integer',
                                ]);

            $request->validate([
                'type'                  => 'required|in:INCOME,EXPENSE',
                'ledger_id'             => [
                    'required_if:type,INCOME,EXPENSE',
                    'integer',
                    Rule::exists('ledgers', 'id')->where(function ($query) use ($transactionType) {
                        $query->where('status', '!=', 3)
                                ->where('type', '=', $transactionType);
                    }),
                ],
                'transaction_timestamp' => 'required|date',
                'transaction_amount'    => 'required|numeric|gt:0',
                'unit_id'               => [
                    'required',
                    'integer',
                    Rule::exists('units', 'id')->where(function ($query) {
                        $query->where('status', '!=', 3);
                    }),
                ],
                'branch_id'             => [
                    'required',
                    'integer',
                    Rule::exists('branches', 'id')->where(function ($query) use ($request) {
                        $query->where('status', '!=', 3)
                                ->where('unit_id', (int)$request->unit_id);
                    }),
                ],
                'payment_mode'          => 'required|in:Cash,Bank',
                'bank_account_id'       => $bankAccountRules,
                'payment_reference'     => 'nullable|string|max:2000',
                'particulars'           => 'required|string|max:1000',
                'note'                  => 'nullable|string|max:2000',
                'created_by'            => 'required|integer|exists:users,id',
            ], [
                'ledger_id.required_if' => 'Please select a ledger for the selected transaction type.',
                'ledger_id.exists'      => 'Selected ledger is not valid for the chosen transaction type.',
                'bank_account_id.required' => 'Please select a bank account when payment mode is Bank.',
                'bank_account_id.exists'    => 'Selected bank account is not valid.',
            ]);

            $member = Transaction::findOrFail($id);
            $bankAccountId = (($request->payment_mode === 'Bank') ? (int)$request->bank_account_id : null);
            $member->update([
                'unit_id'                => (int)$request->unit_id,
                'branch_id'              => (int)$request->branch_id,
                'ledger_id'              => (int)$request->ledger_id,
                'payment_mode'           => $request->payment_mode,
                'bank_account_id'        => $bankAccountId,
                'payment_reference'      => $this->normalizePaymentReference($request->payment_mode, $request->payment_reference),
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
                                    DB::raw("COALESCE(tx_ledgers.name, '') as ledger_name"),
                                    DB::raw("COALESCE(tx_bank_accounts.bank_name, '') as bank_account_name"),
                                    DB::raw("COALESCE(tx_bank_accounts.account_no, '') as bank_account_no"),
                                    DB::raw("COALESCE(tx_units.name, fee_units.name) as unit_name"),
                                    DB::raw("COALESCE(tx_branches.name, fee_branches.name) as branch_name"),
                                    DB::raw("CONCAT(COALESCE(creator.first_name,''), ' ', COALESCE(creator.last_name,'')) as creator_name"),
                                    DB::raw("CONCAT(COALESCE(updater.first_name,''), ' ', COALESCE(updater.last_name,'')) as updater_name")
                                )
                                ->leftJoin('student_payments as fee_payments', 'fee_payments.id', '=', 'transactions.fee_id')
                                ->leftJoin('ledgers as tx_ledgers', 'tx_ledgers.id', '=', 'transactions.ledger_id')
                                ->leftJoin('bank_accounts as tx_bank_accounts', 'tx_bank_accounts.id', '=', 'transactions.bank_account_id')
                                ->leftJoin('users as creator', 'creator.id', '=', 'transactions.created_by')
                                ->leftJoin('users as updater', 'updater.id', '=', 'transactions.updated_by')
                                ->leftJoin('units as tx_units', 'tx_units.id', '=', 'transactions.unit_id')
                                ->leftJoin('branches as tx_branches', 'tx_branches.id', '=', 'transactions.branch_id')
                                ->leftJoin('units as fee_units', 'fee_units.id', '=', 'fee_payments.unit_id')
                                ->leftJoin('branches as fee_branches', 'fee_branches.id', '=', 'fee_payments.branch_id')
                                ->where('transactions.id', '=', $id)
                                ->where('transactions.status', '!=', 3)
                                ->first();

        if (!$transaction) {
            return redirect($this->data['controller_route'] . "/list")->with('error_message', 'Transaction not found !!!');
        }

        $data['title']       = 'Transaction Invoice';
        $data['transaction'] = $transaction;

        return view('front.pages.finance.finance-invoice', $data);
    }
    /* invoice */

    private function isEditableTransaction($transaction)
    {
        if (!$transaction) {
            return false;
        }

        if ((int)$transaction->fee_id > 0) {
            return false;
        }

        $particulars = trim((string)($transaction->particulars ?? ''));
        if ($particulars !== '' && str_starts_with($particulars, 'Admission fee collected for ')) {
            return false;
        }

        if ($particulars !== '' && str_starts_with($particulars, 'Books Fee collected for ')) {
            return false;
        }

        if ($particulars !== '' && str_starts_with($particulars, 'Uniform Fee collected for ')) {
            return false;
        }

        $note = trim((string)($transaction->note ?? ''));
        if ($note !== '' && str_starts_with($note, 'Promotion from ')) {
            return false;
        }

        return true;
    }

    private function normalizePaymentReference($paymentMode, $paymentReference)
    {
        if ($paymentMode !== 'Bank') {
            return null;
        }

        $paymentReference = trim((string)$paymentReference);

        return (($paymentReference !== '') ? $paymentReference : null);
    }

    private function currentUserId()
    {
        if (session()->has('user_data') && array_key_exists('user_id', session('user_data'))) {
            return (int)session('user_data')['user_id'];
        }

        return ((Auth::check()) ? (int)Auth::id() : 0);
    }

    private function ledgerOptions($selectedLedgerId = null)
    {
        $ledgers = Ledger::select('id', 'name', 'type', 'status')
                        ->where('status', '=', 1)
                        ->orderBy('name', 'ASC')
                        ->get();

        $selectedLedgerId = (int) $selectedLedgerId;

        if ($selectedLedgerId > 0 && !$ledgers->pluck('id')->contains($selectedLedgerId)) {
            $selectedLedger = Ledger::select('id', 'name', 'type', 'status')
                                    ->where('id', '=', $selectedLedgerId)
                                    ->where('status', '!=', 3)
                                    ->first();

            if ($selectedLedger) {
                $ledgers->push($selectedLedger);
            }
        }

        return $ledgers->sortBy('name')->values();
    }

    private function bankAccountOptions($selectedBankAccountId = null)
    {
        $bankAccounts = BankAccount::select('id', 'bank_name', 'bank_branch', 'account_no', 'status')
                                    ->where('status', '=', 1)
                                    ->orderBy('bank_name', 'ASC')
                                    ->get();

        $selectedBankAccountId = (int)$selectedBankAccountId;

        if ($selectedBankAccountId > 0 && !$bankAccounts->pluck('id')->contains($selectedBankAccountId)) {
            $selectedBankAccount = BankAccount::select('id', 'bank_name', 'bank_branch', 'account_no', 'status')
                                            ->where('id', '=', $selectedBankAccountId)
                                            ->where('status', '!=', 3)
                                            ->first();

            if ($selectedBankAccount) {
                $bankAccounts->push($selectedBankAccount);
            }
        }

        return $bankAccounts->sortBy('bank_name')->values();
    }
}
