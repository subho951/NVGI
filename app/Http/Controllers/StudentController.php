<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Services\SiteAuthService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

use App\Models\GeneralSetting;
use App\Models\User;
use App\Models\Student;
use App\Models\Unit;
use App\Models\Branch;
use App\Models\Session;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\Medium;
use App\Models\KnowAbout;
use App\Models\Religion;
use App\Models\Board;
use App\Models\StudentPayment;
use App\Models\Transaction;
use App\Models\BankAccount;

use App\Helpers\Helper;
use Auth;
use Hash;
use Dompdf\Dompdf;
use Dompdf\Options;
use DateTime;

class StudentController extends Controller
{
    protected $siteAuthService;
    protected $data;
    public function __construct()
    {        
        $this->data = array(
            'title'             => 'Student',
            'controller'        => 'StudentController',
            'controller_route'  => 'student',
            'primary_key'       => 'id',
        );
        $this->siteAuthService = new SiteAuthService();
    }
    /* list */
        public function list(Request $request){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' List';
            $page_name                      = 'student.list';
            $data['rows']                   = Student::select(
                                                                'students.*',
                                                                'units.name as unit_name',
                                                                'branches.name as branch_name',
                                                                'sessions.name as session_name',
                                                                DB::raw("COALESCE(tsa_classes.name, vhs_classes.name) as class_name")
                                                            )
                                                            ->leftJoin('units', 'units.id', '=', 'students.unit_id')
                                                            ->leftJoin('branches', 'branches.id', '=', 'students.branch_id')
                                                            ->leftJoin('sessions', 'sessions.id', '=', 'students.session_id')
                                                            ->leftJoin('classes as tsa_classes', 'tsa_classes.id', '=', 'students.tsa_class_id')
                                                            ->leftJoin('classes as vhs_classes', 'vhs_classes.id', '=', 'students.vhs_class_id')
                                                            ->where(function ($q) {
                                                                $q->where('students.status', '!=', 3)
                                                                ->orWhereNull('students.status');
                                                            })
                                                            ->orderBy('students.id', 'DESC')
                                                            ->get();
            $data['vhs_classes']            = Classes::select('id', 'name')->where('status', '=', 1)->where('unit_id', '=', 1)->orderBy('name', 'ASC')->get();
            $data['tsa_classes']            = Classes::select('id', 'name')->where('status', '=', 1)->where('unit_id', '=', 2)->orderBy('name', 'ASC')->get();
            $data['bankAccounts']           = $this->bankAccountOptions();
            // Helper::pr($data['rows']);
            $data = $this->siteAuthService->admin_after_login_layout($title, $page_name, $data);
            return view('front.pages.' . $page_name, $data);
        }
    /* list */
    /* add */
        public function add(Request $request){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' Update';
            $page_name                      = 'student.add-edit';
            $data['row']                    = [];
            $generalSetting                 = GeneralSetting::find('1');
            $data['action']                 = 'Add';

            $data['units']                  = Unit::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['branches']               = Branch::select('id', 'name', 'unit_id')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['sessions']               = Session::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['vhs_classes']            = Classes::select('id', 'name')->where('status', '=', 1)->where('unit_id', '=', 1)->orderBy('name', 'ASC')->get();
            $data['tsa_classes']            = Classes::select('id', 'name')->where('status', '=', 1)->where('unit_id', '=', 2)->orderBy('name', 'ASC')->get();
            $data['subjects']               = Subject::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['mediums']                = Medium::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['knowAbouts']             = KnowAbout::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['religions']              = Religion::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['boards']                 = Board::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['bankAccounts']           = $this->bankAccountOptions();

            if($request->isMethod('post')){
                $paymentMode = (string)$request->input('payment_mode');
                $bankAccountRules = (($paymentMode === 'Bank')
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
                $paymentReferenceRules = (($paymentMode === 'Bank')
                                        ? [
                                            'required',
                                            'string',
                                            'max:2000',
                                        ]
                                        : [
                                            'nullable',
                                            'string',
                                            'max:2000',
                                        ]);

                $request->validate([
                    'unit_id'                   => 'required|integer',
                    'branch_id'                 => 'required|integer',
                    'session_id'                => 'required|integer',
                    'admission_date'            => 'required|date',
                    'first_name'                => 'required|string',
                    'last_name'                 => 'required|string',
                    'gender'                    => 'required|string',
                    'religion_id'               => 'required|integer',
                    'caste'                     => 'required|string',
                    'dob'                       => 'required|date',
                    'is_ph'                     => 'required|integer',
                    'permanent_address'         => 'required|string',
                    'permanent_pincode'         => 'required|string',
                    'father_name'               => 'required|string',
                    'father_occupation'         => 'required|string',
                    'father_mobile'             => 'required|string',
                    'emergency_name'            => 'required|string',
                    'emergency_phone'           => 'required|string',
                    'emergency_relation'        => 'required|string',
                    'know_about_us'             => 'required|string',
                    'blood_group'               => 'required|string',
                    'payment_mode'              => 'required|in:Cash,Bank',
                    'bank_account_id'           => $bankAccountRules,
                    'payment_reference'         => $paymentReferenceRules,
                    'admission_fees'            => 'required|numeric|gt:0',
                    'monthly_fees'              => 'required|numeric|gt:0',
                    // 'books_fee'                 => 'required|numeric|gt:0',
                    // 'uniform_fee'               => 'required|numeric|gt:0',
                ], [
                    'bank_account_id.required'   => 'Please select a bank account when payment mode is Bank.',
                    'bank_account_id.exists'     => 'Selected bank account is not valid.',
                    'payment_reference.required' => 'Please enter a payment reference when payment mode is Bank.',
                ]);

                if($request->middle_name != ''){
                    $full_name = $request->first_name . ' ' . $request->middle_name . ' ' . $request->last_name;
                } else {
                    $full_name = $request->first_name . ' ' . $request->last_name;
                }                

                /* student serial no generation */
                    $getUnit            = Unit::select('serial_id')->where('id', '=', $request->unit_id)->first();
                    $getBranch          = Branch::select('serial_id')->where('id', '=', $request->branch_id)->first();
                    $getLastStudent     = Student::orderBy('id', 'DESC')->first();
                    if($getLastStudent){
                        $sl_no              = $getLastStudent->sl_no;
                        $next_sl_no         = $sl_no + 1;
                        $next_sl_no_string  = str_pad($next_sl_no, 4, 0, STR_PAD_LEFT);
                        $student_id_serial  = (($getUnit)?$getUnit->serial_id:'') . '-' . (($getBranch)?$getBranch->serial_id:'') . '-' . $next_sl_no_string;
                    } else {
                        $next_sl_no         = 1;
                        $next_sl_no_string  = str_pad($next_sl_no, 4, 0, STR_PAD_LEFT);
                        $student_id_serial  = (($getUnit)?$getUnit->serial_id:'') . '-' . (($getBranch)?$getBranch->serial_id:'') . '-' . $next_sl_no_string;
                    }
                /* student serial no generation */

                /* photo */
                    $imageFile      = $request->file('photo');
                    if($imageFile != ''){
                        $imageName      = $imageFile->getClientOriginalName();
                        $uploadedFile   = $this->upload_single_file('photo', $imageName, 'student', 'image');
                        if($uploadedFile['status']){
                            $photo = '/uploads/student/' . $uploadedFile['newFilename'];
                        } else {
                            return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                        }
                } else {
                    $photo = '';
                }
                /* photo */
                $postData               = $request->all();
                $updatedBy              = $this->currentUserId();
                if($request->unit_id == 1){
                    $tsa_subjects = json_encode(array());
                } else {
                    if(array_key_exists("tsa_subjects",$postData)){
                        $tsa_subjects = (($request->tsa_subjects != '')?json_encode($request->tsa_subjects):[]);
                    } else {
                        $tsa_subjects = json_encode(array());
                    }
                }
                // array_key_exists("tsa_subjects",$postData))
                $fields = [
                    'sl_no'                     => $next_sl_no,
                    'student_id_serial'         => $student_id_serial,
                    'unit_id'                   => $request->unit_id,
                    'branch_id'                 => $request->branch_id,
                    'session_id'                => $request->session_id,
                    'admission_date'            => $request->admission_date,
                    'first_name'                => $request->first_name,
                    'middle_name'               => $request->middle_name,
                    'last_name'                 => $request->last_name,
                    'full_name'                 => $full_name,
                    'gender'                    => $request->gender,
                    'religion_id'               => $request->religion_id,
                    'caste'                     => $request->caste,
                    'dob'                       => $request->dob,
                    'is_ph'                     => $request->is_ph,
                    'permanent_address'         => $request->permanent_address,
                    'permanent_pincode'         => $request->permanent_pincode,
                    'vhs_class_id'              => $request->vhs_class_id,
                    'vhs_daycare'               => $request->vhs_daycare,
                    'tsa_class_id'              => $request->tsa_class_id,
                    'tsa_board'                 => $request->tsa_board,
                    'tsa_subjects'              => $tsa_subjects,
                    'tsa_medium'                => $request->tsa_medium,
                    'father_name'               => $request->father_name,
                    'father_occupation'         => $request->father_occupation,
                    'father_mobile'             => $request->father_mobile,
                    'mother_name'               => $request->mother_name,
                    'mother_occupation'         => $request->mother_occupation,
                    'mother_mobile'             => $request->mother_mobile,
                    'emergency_name'            => $request->emergency_name,
                    'emergency_phone'           => $request->emergency_phone,
                    'emergency_relation'        => $request->emergency_relation,
                    'know_about_us'             => $request->know_about_us,
                    'blood_group'               => $request->blood_group,
                    'admission_fees'            => $request->admission_fees,
                    'monthly_fees'              => $request->monthly_fees,
                    'books_fee'                 => number_format((float)$request->books_fee, 2, '.', ''),
                    'uniform_fee'               => number_format((float)$request->uniform_fee, 2, '.', ''),
                    'photo'                     => $photo,
                    'created_by'                => $updatedBy,
                    'updated_by'                => $updatedBy,
                ];
                
                // Helper::pr($fields);
                $bankAccountId = (($paymentMode === 'Bank') ? ((int)$request->bank_account_id ?: null) : null);
                $paymentReference = $this->normalizePaymentReference($paymentMode, $request->payment_reference);

                DB::transaction(function () use ($fields, $request, $updatedBy, $full_name, $student_id_serial, $paymentMode, $bankAccountId, $paymentReference) {
                    $student = Student::create($fields);
                    $id = $student->id;

                    /* student fees schedule generate */
                        $sessionData = $this->getFinancialSessionData($request->session_id);
                        $financialMonths = $this->buildFinancialMonths($sessionData['start_year'], $sessionData['end_year']);

                        foreach ($financialMonths as $monthConfig) {
                            $fields = [
                                'student_id'        => $id,
                                'unit_id'           => $request->unit_id,
                                'branch_id'         => $request->branch_id,
                                'payable_month'     => $monthConfig['month'],
                                'payable_year'      => $monthConfig['year'],
                                'payable_amount'    => $request->monthly_fees,
                                'due_amount'        => $request->monthly_fees,
                                'created_by'        => $updatedBy,
                                'updated_by'        => $updatedBy,
                            ];
                            StudentPayment::insert($fields);
                        }
                    /* student fees schedule generate */

                    $lastTransaction = Transaction::withTrashed()->select('sl_no')
                                                ->orderBy('sl_no', 'DESC')
                                                ->lockForUpdate()
                                                ->first();
                    $nextSlNo       = (($lastTransaction)?((int)$lastTransaction->sl_no + 1):1);
                    $nextTxnNo      = str_pad($nextSlNo, 8, '0', STR_PAD_LEFT);

                    Transaction::create([
                        'sl_no'                  => $nextSlNo,
                        'txn_no'                 => $nextTxnNo,
                        'fee_id'                 => 0,
                        'unit_id'                => (int)$request->unit_id,
                        'branch_id'              => (int)$request->branch_id,
                        'payment_mode'           => $paymentMode,
                        'bank_account_id'        => $bankAccountId,
                        'payment_reference'      => $paymentReference,
                        'type'                   => 'INCOME',
                        'ledger_id'              => 3,
                        'transaction_timestamp'  => Carbon::now(),
                        'transaction_amount'     => number_format((float)$request->admission_fees, 2, '.', ''),
                        'particulars'            => 'Admission fee collected for '.$full_name.' ('. $student_id_serial .') during new admission',
                        'note'                   => 'New student admission',
                        'status'                 => 1,
                        'created_by'             => $updatedBy,
                        'updated_by'             => $updatedBy,
                    ]);
                });

                return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' added successfully !!!');
            }

            $data = $this->siteAuthService->admin_after_login_layout($title, $page_name, $data);
            return view('front.pages.' . $page_name, $data);
        }
    /* add */
    /* edit */
        public function edit(Request $request, $id){
            $data['module']                 = $this->data;
            $id                             = Helper::decoded($id);
            $title                          = $this->data['title'].' Update';
            $page_name                      = 'student.add-edit';
            $data['row']                    = Student::where($this->data['primary_key'], '=', $id)->first();
            
            $generalSetting                 = GeneralSetting::find('1');
            $data['action']                 = 'Edit';

            $data['units']                  = Unit::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['branches']               = Branch::select('id', 'name', 'unit_id')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['sessions']               = Session::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['vhs_classes']            = Classes::select('id', 'name')->where('status', '=', 1)->where('unit_id', '=', 1)->orderBy('name', 'ASC')->get();
            $data['tsa_classes']            = Classes::select('id', 'name')->where('status', '=', 1)->where('unit_id', '=', 2)->orderBy('name', 'ASC')->get();
            $data['subjects']               = Subject::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['mediums']                = Medium::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['knowAbouts']             = KnowAbout::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['religions']              = Religion::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['boards']                 = Board::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();

            if($request->isMethod('post')){
                $member = Student::findOrFail($id);

                if($request->middle_name != ''){
                    $full_name = $request->first_name . ' ' . $request->middle_name . ' ' . $request->last_name;
                } else {
                    $full_name = $request->first_name . ' ' . $request->last_name;
                }

                /* student serial no generation */
                    $getUnit            = Unit::select('serial_id')->where('id', '=', $request->unit_id)->first();
                    $getBranch          = Branch::select('serial_id')->where('id', '=', $request->branch_id)->first();
                    $next_sl_no         = (($data['row'])?$data['row']->sl_no:0);
                    $next_sl_no_string  = str_pad($next_sl_no, 4, 0, STR_PAD_LEFT);
                    $student_id_serial  = (($getUnit)?$getUnit->serial_id:'') . '-' . (($getBranch)?$getBranch->serial_id:'') . '-' . $next_sl_no_string;
                /* student serial no generation */

                /* photo */
                    $imageFile      = $request->file('photo');
                    if($imageFile != ''){
                        $imageName      = $imageFile->getClientOriginalName();
                        $uploadedFile   = $this->upload_single_file('photo', $imageName, 'student', 'image');
                        if($uploadedFile['status']){
                            $photo = 'uploads/student/' . $uploadedFile['newFilename'];
                        } else {
                            return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                        }
                    } else {
                        $photo = (($data['row'])?$data['row']->photo:'');
                    }
                /* photo */

                $request->validate([
                    'unit_id'                   => 'required|integer',
                    'branch_id'                 => 'required|integer',
                    'session_id'                => 'required|integer',
                    'admission_date'            => 'required|date',
                    'first_name'                => 'required|string',
                    'last_name'                 => 'required|string',
                    'gender'                    => 'required|string',
                    'religion_id'               => 'required|integer',
                    'caste'                     => 'required|string',
                    'dob'                       => 'required|date',
                    'is_ph'                     => 'required|integer',
                    'permanent_address'         => 'required|string',
                    'permanent_pincode'         => 'required|string',
                    'father_name'               => 'required|string',
                    'father_occupation'         => 'required|string',
                    'father_mobile'             => 'required|string',
                    'emergency_name'            => 'required|string',
                    'emergency_phone'           => 'required|string',
                    'emergency_relation'        => 'required|string',
                    'know_about_us'             => 'required|string',
                    'blood_group'               => 'required|string',
                    'admission_fees'            => 'required|numeric|gt:0',
                    'monthly_fees'              => 'required|numeric|gt:0',
                    // 'books_fee'                 => 'required|numeric|gt:0',
                    // 'uniform_fee'               => 'required|numeric|gt:0',
                ]);
                $postData               = $request->all();
                if($request->unit_id == 1){
                    $tsa_subjects = json_encode(array());
                } else {
                    if(array_key_exists("tsa_subjects",$postData)){
                        $tsa_subjects = (($request->tsa_subjects != '')?json_encode($request->tsa_subjects):[]);
                    } else {
                        $tsa_subjects = json_encode(array());
                    }
                }
                $member->update([
                    'student_id_serial'         => $student_id_serial,
                    'unit_id'                   => $request->unit_id,
                    'branch_id'                 => $request->branch_id,
                    'session_id'                => $request->session_id,
                    'admission_date'            => $request->admission_date,
                    'first_name'                => $request->first_name,
                    'middle_name'               => $request->middle_name,
                    'last_name'                 => $request->last_name,
                    'full_name'                 => $full_name,
                    'gender'                    => $request->gender,
                    'religion_id'               => $request->religion_id,
                    'caste'                     => $request->caste,
                    'dob'                       => $request->dob,
                    'is_ph'                     => $request->is_ph,
                    'permanent_address'         => $request->permanent_address,
                    'permanent_pincode'         => $request->permanent_pincode,
                    'vhs_class_id'              => $request->vhs_class_id,
                    'vhs_daycare'               => $request->vhs_daycare,
                    'tsa_class_id'              => $request->tsa_class_id,
                    'tsa_board'                 => $request->tsa_board,
                    'tsa_subjects'              => $tsa_subjects,
                    'tsa_medium'                => $request->tsa_medium,
                    'father_name'               => $request->father_name,
                    'father_occupation'         => $request->father_occupation,
                    'father_mobile'             => $request->father_mobile,
                    'mother_name'               => $request->mother_name,
                    'mother_occupation'         => $request->mother_occupation,
                    'mother_mobile'             => $request->mother_mobile,
                    'emergency_name'            => $request->emergency_name,
                    'emergency_phone'           => $request->emergency_phone,
                    'emergency_relation'        => $request->emergency_relation,
                    'know_about_us'             => $request->know_about_us,
                    'blood_group'               => $request->blood_group,
                    'admission_fees'            => $request->admission_fees,
                    'monthly_fees'              => $request->monthly_fees,
                    'books_fee'                 => number_format((float)$request->books_fee, 2, '.', ''),
                    'uniform_fee'               => number_format((float)$request->uniform_fee, 2, '.', ''),
                    'photo'                     => $photo,
                    'updated_by'                => session('user_data')['user_id'],
                ]);

                /* student monthly fees schedule update */
                    $monthly_fees = $request->monthly_fees;
                    $student_id = $id;

                    $fees_fields = [
                        'payable_amount'    => $monthly_fees,
                        'due_amount'        => $monthly_fees,
                    ];
                    StudentPayment::where('student_id', '=', $student_id)->update($fees_fields);
                /* student monthly fees schedule update */
                return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' updated successfully !!!');
            }

            $data = $this->siteAuthService->admin_after_login_layout($title, $page_name, $data);
            return view('front.pages.' . $page_name, $data);
        }
    /* edit */
    /* delete */
        public function delete(Request $request, $id){
            $id                             = Helper::decoded($id);
            $fields = [
                'status'             => 3,
                'deleted_at'         => date('Y-m-d H:i:s'),
            ];
            Student::where($this->data['primary_key'], '=', $id)->update($fields);
            return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' deleted successfully !!!');
        }
    /* delete */
    /* change status */
        public function change_status(Request $request, $id){
            $id                             = Helper::decoded($id);
            $model                          = Student::find($id);
            if ($model->status == 1)
            {
                $model->status  = 0;
                $msg            = 'blocked';
            } else {
                $model->status  = 1;
                $msg            = 'activated';
            }
            $model->save();
            return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' '.$msg.' successfully !!!');
        }
    /* change status */
    /* promote */
        public function promote(Request $request){
            $paymentMode = (string)$request->input('payment_mode');
            $bankAccountRules = (($paymentMode === 'Bank')
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
            $paymentReferenceRules = (($paymentMode === 'Bank')
                                    ? [
                                        'required',
                                        'string',
                                        'max:2000',
                                    ]
                                    : [
                                        'nullable',
                                        'string',
                                        'max:2000',
                                    ]);

            $request->validate([
                'student_id'            => 'required|integer|exists:students,id',
                'admission_fees'        => 'required|numeric|gt:0',
                'monthly_fees'          => 'required|numeric|gt:0',
                'payment_mode'          => 'required|in:Cash,Bank',
                'ledger_id'             => 'required|integer|in:3',
                'bank_account_id'       => $bankAccountRules,
                'payment_reference'     => $paymentReferenceRules,
            ], [
                'bank_account_id.required'   => 'Please select a bank account when payment mode is Bank.',
                'bank_account_id.exists'     => 'Selected bank account is not valid.',
                'payment_reference.required'  => 'Please enter a payment reference when payment mode is Bank.',
            ]);

            $student = Student::select(
                                'students.id',
                                'students.unit_id',
                                'students.branch_id',
                                'students.full_name',
                                'students.student_id_serial',
                                'students.admission_fees as current_admission_fees',
                                'students.monthly_fees as current_monthly_fees',
                                'students.vhs_class_id',
                                'students.tsa_class_id',
                                'units.name as unit_name',
                                'branches.name as branch_name',
                                DB::raw("COALESCE(tsa_classes.name, vhs_classes.name) as current_class_name")
                            )
                            ->leftJoin('units', 'units.id', '=', 'students.unit_id')
                            ->leftJoin('branches', 'branches.id', '=', 'students.branch_id')
                            ->leftJoin('classes as tsa_classes', 'tsa_classes.id', '=', 'students.tsa_class_id')
                            ->leftJoin('classes as vhs_classes', 'vhs_classes.id', '=', 'students.vhs_class_id')
                            ->where('students.id', '=', (int)$request->student_id)
                            ->where(function ($q) {
                                $q->where('students.status', '=', 1)
                                  ->orWhereNull('students.status');
                            })
                            ->first();

            if (!$student) {
                return redirect()->back()->with('error_message', 'Student not found !!!')->withInput();
            }

            $currentClassId = ((int)$student->unit_id === 1) ? (int)$student->vhs_class_id : (int)$student->tsa_class_id;
            $ledgerId = (int)$request->ledger_id;
            $bankAccountId = (($paymentMode === 'Bank') ? ((int)$request->bank_account_id ?: null) : null);
            $paymentReference = $this->normalizePaymentReference($paymentMode, $request->payment_reference);

            $request->validate([
                'promoted_class_id' => [
                    'required',
                    'integer',
                    Rule::exists('classes', 'id')->where(function ($query) use ($student) {
                        $query->where('status', '=', 1)
                              ->where('unit_id', (int)$student->unit_id);
                    }),
                ],
            ]);

            $promotedClassId = (int)$request->promoted_class_id;
            // if ($currentClassId > 0 && $promotedClassId === $currentClassId) {
            //     return redirect()->back()->with('error_message', 'Promoted class must be different from the present class !!!')->withInput();
            // }

            $promotedClass = Classes::select('id', 'name')
                                    ->where('id', '=', $promotedClassId)
                                    ->where('status', '=', 1)
                                    ->first();
            if (!$promotedClass) {
                return redirect()->back()->with('error_message', 'Promoted class not found !!!')->withInput();
            }

            $updatedBy            = $this->currentUserId();
            $admissionFeesValue   = number_format((float)$request->admission_fees, 2, '.', '');
            $monthlyFeesValue     = number_format((float)$request->monthly_fees, 2, '.', '');
            $financialSessionData = $this->getFinancialSessionData();
            $financialMonths      = $this->buildFinancialMonths($financialSessionData['start_year'], $financialSessionData['end_year']);

            DB::transaction(function () use ($student, $promotedClass, $promotedClassId, $updatedBy, $admissionFeesValue, $monthlyFeesValue, $financialMonths, $financialSessionData, $paymentMode, $ledgerId, $bankAccountId, $paymentReference) {
                $studentUpdate = [
                    'admission_fees'    => $admissionFeesValue,
                    'monthly_fees'      => $monthlyFeesValue,
                    // 'session_id'        => $financialSessionData['session_id'],
                    'updated_by'        => $updatedBy,
                ];

                if ((int)$student->unit_id === 1) {
                    $studentUpdate['vhs_class_id'] = $promotedClassId;
                } else {
                    $studentUpdate['tsa_class_id'] = $promotedClassId;
                }

                Student::where('id', '=', $student->id)->update($studentUpdate);

                foreach ($financialMonths as $monthConfig) {
                    $studentPayment = StudentPayment::firstOrNew([
                        'student_id'    => $student->id,
                        'payable_month' => $monthConfig['month'],
                        'payable_year'  => $monthConfig['year'],
                    ]);

                    $alreadyPaid = (float)((isset($studentPayment->payment_amount)) ? $studentPayment->payment_amount : 0);

                    $studentPayment->student_id      = $student->id;
                    $studentPayment->unit_id         = $student->unit_id;
                    $studentPayment->branch_id       = $student->branch_id;
                    $studentPayment->payable_month   = $monthConfig['month'];
                    $studentPayment->payable_year    = $monthConfig['year'];
                    $studentPayment->payable_amount  = $monthlyFeesValue;
                    $studentPayment->due_amount      = max(((float)$monthlyFeesValue - $alreadyPaid), 0);
                    $studentPayment->updated_by      = $updatedBy;

                    if (!$studentPayment->exists) {
                        $studentPayment->payment_amount = 0;
                        $studentPayment->payment_date   = null;
                        $studentPayment->created_by     = $updatedBy;
                    }

                    $studentPayment->save();
                }

                $lastTransaction = Transaction::withTrashed()->select('sl_no')
                                            ->orderBy('sl_no', 'DESC')
                                            ->lockForUpdate()
                                            ->first();
                $nextSlNo       = (($lastTransaction)?((int)$lastTransaction->sl_no + 1):1);
                $nextTxnNo      = str_pad($nextSlNo, 8, '0', STR_PAD_LEFT);

                Transaction::create([
                    'sl_no'                  => $nextSlNo,
                    'txn_no'                 => $nextTxnNo,
                    'fee_id'                 => 0,
                    'unit_id'                => (int)$student->unit_id,
                    'branch_id'              => (int)$student->branch_id,
                    'ledger_id'              => $ledgerId,
                    'payment_mode'           => $paymentMode,
                    'bank_account_id'        => $bankAccountId,
                    'payment_reference'      => $paymentReference,
                    'type'                   => 'INCOME',
                    'transaction_timestamp'  => Carbon::now(),
                    'transaction_amount'     => $admissionFeesValue,
                    'particulars'            => 'Session fee collected for '.$student->full_name.' ('.$student->student_id_serial.') during promotion to '.$promotedClass->name,
                    'note'                   => 'Promotion from '.(($student->current_class_name != '') ? $student->current_class_name : '-').' to '.$promotedClass->name,
                    'status'                 => 1,
                    'created_by'             => $updatedBy,
                    'updated_by'             => $updatedBy,
                ]);
            });

            return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' promoted successfully !!!');
        }
    /* promote */
    /* special fee collection */
        public function collectSpecialFee(Request $request)
        {
            $paymentMode = (string)$request->input('special_fee_payment_mode');
            $bankAccountRules = (($paymentMode === 'Bank')
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
            $paymentReferenceRules = (($paymentMode === 'Bank')
                                    ? [
                                        'required',
                                        'string',
                                        'max:2000',
                                    ]
                                    : [
                                        'nullable',
                                        'string',
                                        'max:2000',
                                    ]);

            $request->validate([
                'special_fee_student_id'  => 'required|integer|exists:students,id',
                'special_fee_type'        => 'required|in:books,uniform',
                'special_fee_payment_mode'=> 'required|in:Cash,Bank',
                'special_fee_ledger_id'   => 'required|integer|in:4',
                'bank_account_id'         => $bankAccountRules,
                'payment_reference'       => $paymentReferenceRules,
                'special_fee_amount'      => 'required|numeric|gt:0',
            ], [
                'bank_account_id.required'    => 'Please select a bank account when payment mode is Bank.',
                'bank_account_id.exists'      => 'Selected bank account is not valid.',
                'payment_reference.required'  => 'Please enter a payment reference when payment mode is Bank.',
            ]);

            $student = Student::select(
                                'students.id',
                                'students.unit_id',
                                'students.branch_id',
                                'students.full_name',
                                'students.student_id_serial',
                                'students.session_id',
                                'sessions.name as session_name'
                            )
                            ->leftJoin('sessions', 'sessions.id', '=', 'students.session_id')
                            ->where('students.id', '=', (int)$request->special_fee_student_id)
                            ->where(function ($q) {
                                $q->where('students.status', '=', 1)
                                  ->orWhereNull('students.status');
                            })
                            ->first();

            if (!$student) {
                return redirect()->back()->with('error_message', 'Student not found !!!')->withInput();
            }

            $feeType = (string)$request->special_fee_type;
            $feeLabel = (($feeType === 'books') ? 'Books Fee' : 'Uniform Fee');
            $feeField = (($feeType === 'books') ? 'books_fee' : 'uniform_fee');
            $feeAmount = number_format((float)$request->special_fee_amount, 2, '.', '');
            $ledgerId = (int)$request->special_fee_ledger_id;
            $bankAccountId = (($paymentMode === 'Bank') ? ((int)$request->bank_account_id ?: null) : null);
            $paymentReference = $this->normalizePaymentReference($paymentMode, $request->payment_reference);
            $studentName = trim((string)$student->full_name);
            if ($studentName === '') {
                $studentName = 'Unknown Student';
            }

            $sessionName = trim((string)$student->session_name);
            $sessionText = (($sessionName !== '') ? ' for session ' . $sessionName : '');
            $updatedBy = $this->currentUserId();

            DB::transaction(function () use ($student, $feeField, $feeAmount, $feeLabel, $studentName, $sessionText, $updatedBy, $paymentMode, $ledgerId, $bankAccountId, $paymentReference) {
                $student->{$feeField} = $feeAmount;
                $student->updated_by = $updatedBy;
                $student->save();

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
                    'unit_id'                => (int)$student->unit_id,
                    'branch_id'              => (int)$student->branch_id,
                    'ledger_id'              => $ledgerId,
                    'payment_mode'           => $paymentMode,
                    'bank_account_id'        => $bankAccountId,
                    'payment_reference'      => $paymentReference,
                    'type'                   => 'INCOME',
                    'transaction_timestamp'  => Carbon::now(),
                    'transaction_amount'     => $feeAmount,
                    'particulars'            => $feeLabel . ' collected for ' . $studentName . ' (' . $student->student_id_serial . ')' . $sessionText,
                    'note'                   => $feeLabel . ' collection',
                    'status'                 => 1,
                    'created_by'             => $updatedBy,
                    'updated_by'             => $updatedBy,
                ]);
            });

            return redirect($this->data['controller_route'] . "/list")->with('success_message', $feeLabel.' collected successfully for '.$studentName.' !!!');
        }
    /* special fee collection */
    public function details($id)
    {
        $id = Helper::decoded($id);
        $student = [];
        $getStudent = Student::where('id', '=', $id)->first();
        if($getStudent){
            if($getStudent->unit_id == 1){
                $student = Student::leftJoin('units','units.id','=','students.unit_id')
                                    ->leftJoin('branches','branches.id','=','students.branch_id')
                                    ->leftJoin('classes','classes.id','=','students.vhs_class_id')
                                    ->leftJoin('sessions','sessions.id','=','students.session_id')
                                    ->leftJoin('religions','religions.id','=','students.religion_id')
                                    ->leftJoin('know_abouts','know_abouts.id','=','students.know_about_us')
                                    ->select(
                                        'students.*',
                                        'units.name as unit_name',
                                        'branches.name as branch_name',
                                        'classes.name as class_name',
                                        'sessions.name as session_name',
                                        'religions.name as religion_name',
                                        'know_abouts.name as source_name',
                                    )
                                    ->where('students.id', $id)
                                    ->first();
            } else {
                $student = Student::leftJoin('units','units.id','=','students.unit_id')
                                    ->leftJoin('branches','branches.id','=','students.branch_id')
                                    ->leftJoin('classes','classes.id','=','students.tsa_class_id')
                                    ->leftJoin('sessions','sessions.id','=','students.session_id')
                                    ->leftJoin('religions','religions.id','=','students.religion_id')
                                    ->leftJoin('know_abouts','know_abouts.id','=','students.know_about_us')
                                    ->select(
                                        'students.*',
                                        'units.name as unit_name',
                                        'branches.name as branch_name',
                                        'classes.name as class_name',
                                        'sessions.name as session_name',
                                        'religions.name as religion_name',
                                        'know_abouts.name as source_name',
                                    )
                                    ->where('students.id', $id)
                                    ->first();
            }
        }

        if (!$student) {
            return response()->json([
                'status' => false,
                'html' => '<div class="alert alert-danger">Student not found</div>'
            ]);
        }

        $html = view('front.pages.student.student-details', compact('student'))->render();

        return response()->json([
            'status' => true,
            'html' => $html
        ]);
    }
    public function studentPrint(Request $request, $id){
        $data['module']                 = $this->data;
        $id                             = Helper::decoded($id);
        $page_name                      = 'student.student-details-print';
        
        $generalSetting                 = GeneralSetting::find('1');
        $data['action']                 = 'Print';

        $student                        = [];
        $getStudent                     = Student::where('id', '=', $id)->first();
        $title                          = 'Print-' . (($getStudent)?$getStudent->student_id_serial:'');
        if($getStudent){
            if($getStudent->unit_id == 1){
                $student = Student::leftJoin('units','units.id','=','students.unit_id')
                                    ->leftJoin('branches','branches.id','=','students.branch_id')
                                    ->leftJoin('classes','classes.id','=','students.vhs_class_id')
                                    ->leftJoin('sessions','sessions.id','=','students.session_id')
                                    ->leftJoin('religions','religions.id','=','students.religion_id')
                                    ->leftJoin('know_abouts','know_abouts.id','=','students.know_about_us')
                                    ->select(
                                        'students.*',
                                        'units.name as unit_name',
                                        'branches.name as branch_name',
                                        'classes.name as class_name',
                                        'sessions.name as session_name',
                                        'religions.name as religion_name',
                                        'know_abouts.name as source_name',
                                    )
                                    ->where('students.id', $id)
                                    ->first();
            } else {
                $student = Student::leftJoin('units','units.id','=','students.unit_id')
                                    ->leftJoin('branches','branches.id','=','students.branch_id')
                                    ->leftJoin('classes','classes.id','=','students.tsa_class_id')
                                    ->leftJoin('sessions','sessions.id','=','students.session_id')
                                    ->leftJoin('religions','religions.id','=','students.religion_id')
                                    ->leftJoin('know_abouts','know_abouts.id','=','students.know_about_us')
                                    ->select(
                                        'students.*',
                                        'units.name as unit_name',
                                        'branches.name as branch_name',
                                        'classes.name as class_name',
                                        'sessions.name as session_name',
                                        'religions.name as religion_name',
                                        'know_abouts.name as source_name',
                                    )
                                    ->where('students.id', $id)
                                    ->first();
            }
        }

        $data['student']                 = $student;
        
        $data = $this->siteAuthService->admin_after_login_layout($title, $page_name, $data);
        return view('front.pages.' . $page_name, $data);
    }
    public function studentPDF(Request $request, $id){
        $data['module']                 = $this->data;
        $id                             = Helper::decoded($id);
        $page_name                      = 'student.student-details-pdf';
        $generalSetting                 = GeneralSetting::find('1');
        $data['action']                 = 'Print';

        $student                        = [];
        $getStudent                     = Student::where('id', '=', $id)->first();
        $title                          = 'Print-' . (($getStudent)?$getStudent->student_id_serial:'');
        $data['title']                  = 'Print-' . (($getStudent)?$getStudent->student_id_serial:'');
        if($getStudent){
            if($getStudent->unit_id == 1){
                $student = Student::leftJoin('units','units.id','=','students.unit_id')
                                    ->leftJoin('branches','branches.id','=','students.branch_id')
                                    ->leftJoin('classes','classes.id','=','students.vhs_class_id')
                                    ->leftJoin('sessions','sessions.id','=','students.session_id')
                                    ->leftJoin('religions','religions.id','=','students.religion_id')
                                    ->leftJoin('know_abouts','know_abouts.id','=','students.know_about_us')
                                    ->select(
                                        'students.*',
                                        'units.name as unit_name',
                                        'branches.name as branch_name',
                                        'classes.name as class_name',
                                        'sessions.name as session_name',
                                        'religions.name as religion_name',
                                        'know_abouts.name as source_name',
                                    )
                                    ->where('students.id', $id)
                                    ->first();
            } else {
                $student = Student::leftJoin('units','units.id','=','students.unit_id')
                                    ->leftJoin('branches','branches.id','=','students.branch_id')
                                    ->leftJoin('classes','classes.id','=','students.tsa_class_id')
                                    ->leftJoin('sessions','sessions.id','=','students.session_id')
                                    ->leftJoin('religions','religions.id','=','students.religion_id')
                                    ->leftJoin('know_abouts','know_abouts.id','=','students.know_about_us')
                                    ->select(
                                        'students.*',
                                        'units.name as unit_name',
                                        'branches.name as branch_name',
                                        'classes.name as class_name',
                                        'sessions.name as session_name',
                                        'religions.name as religion_name',
                                        'know_abouts.name as source_name',
                                    )
                                    ->where('students.id', $id)
                                    ->first();
            }
        }

        $data['student']                 = $student;


        /* generate inspection pdf & save it to directory */
            $message                        = view('front.pages.student.student-details-pdf',$data);
            // echo $message;die;
            $options    = new Options();
            $options->set('defaultFont', 'Courier');
            $dompdf     = new Dompdf($options);
            $html       = $message;
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $output = $dompdf->output();
            // Output the generated PDF to browser
            $dompdf->stream("document.pdf", array("Attachment" => false));
        /* generate inspection pdf & save it to directory */
        
        $data = $this->siteAuthService->admin_after_login_layout($title, $page_name, $data);
        return view('front.pages.' . $page_name, $data);
    }
    public function generateIdCard(Request $request)
    {
        $data['module']             = $this->data;
        $title                      = 'Generate ID Card';
        $page_name                  = 'student.id-card.index';
        $data['brand']              = $this->getStudentIdCardBranding();
        $data['units']              = Unit::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
        $data['branches']           = collect();
        $data['classes']            = collect();
        $data['students']           = null;
        $data['search_applied']     = false;
        $data['search_student_id']  = trim((string)$request->query('student_id', ''));
        $data['search_unit_id']     = (($request->filled('unit_id')) ? (int)$request->query('unit_id') : '');
        $data['search_branch_id']   = (($request->filled('branch_id')) ? (int)$request->query('branch_id') : '');
        $data['search_class_id']    = (($request->filled('class_id')) ? (int)$request->query('class_id') : '');
        $data['filters_locked']     = ($data['search_student_id'] !== '');

        $selectedBranchUnitId = null;
        if ($data['search_branch_id'] !== '') {
            $selectedBranch = Branch::select('unit_id')
                                    ->where('id', '=', (int)$data['search_branch_id'])
                                    ->where('status', '=', 1)
                                    ->first();
            $selectedBranchUnitId = (($selectedBranch) ? (int)$selectedBranch->unit_id : null);
        }

        $selectedClassUnitId = null;
        if ($data['search_class_id'] !== '') {
            $selectedClass = Classes::select('unit_id')
                                    ->where('id', '=', (int)$data['search_class_id'])
                                    ->where('status', '=', 1)
                                    ->first();
            $selectedClassUnitId = (($selectedClass) ? (int)$selectedClass->unit_id : null);
        }

        if ($data['search_unit_id'] === '' && $selectedBranchUnitId !== null) {
            $data['search_unit_id'] = $selectedBranchUnitId;
        }

        if ($data['search_unit_id'] === '' && $selectedClassUnitId !== null) {
            $data['search_unit_id'] = $selectedClassUnitId;
        }

        if ($data['search_unit_id'] !== '') {
            $data['branches'] = $this->getStudentIdCardBranches($data['search_unit_id']);
        }

        if ($data['search_branch_id'] !== '') {
            $data['classes'] = $this->getStudentIdCardClasses($data['search_branch_id'], $data['search_unit_id']);
        } elseif ($data['search_class_id'] !== '' && $data['search_unit_id'] !== '') {
            $data['classes'] = $this->getStudentIdCardClasses(null, $data['search_unit_id']);
        }

        $searchRequested = $request->boolean('search_submitted') || $request->hasAny(['student_id', 'unit_id', 'branch_id', 'class_id']);
        $resolvedUnitForValidation = $this->resolveIdCardUnitId($data['search_unit_id'], $data['search_branch_id']);
        if ($resolvedUnitForValidation === null && $selectedClassUnitId !== null) {
            $resolvedUnitForValidation = $selectedClassUnitId;
        }

        if ($searchRequested) {
            $validator = Validator::make($request->all(), [
                'student_id' => 'nullable|string|max:60',
                'unit_id'    => 'nullable|integer|exists:units,id',
                'branch_id'  => [
                    'nullable',
                    'integer',
                    Rule::exists('branches', 'id')->where(function ($query) {
                        $query->where('status', '=', 1);
                    }),
                ],
                'class_id'   => [
                    'nullable',
                    'integer',
                    Rule::exists('classes', 'id')->where(function ($query) {
                        $query->where('status', '=', 1);
                    }),
                ],
            ]);

            $validator->after(function ($validator) use ($request, $resolvedUnitForValidation, $selectedClassUnitId) {
                $studentId = trim((string)$request->input('student_id', ''));

                if ($studentId !== '') {
                    return;
                }

                if (!$request->filled('unit_id') && !$request->filled('branch_id') && !$request->filled('class_id')) {
                    $validator->errors()->add('search', 'Please select at least one filter or enter a Student ID.');
                    return;
                }

                if ($request->filled('branch_id') && $request->filled('unit_id')) {
                    $branch = Branch::select('unit_id')
                                    ->where('id', '=', (int)$request->input('branch_id'))
                                    ->where('status', '=', 1)
                                    ->first();

                    if ($branch && (int)$branch->unit_id !== (int)$request->input('unit_id')) {
                        $validator->errors()->add('branch_id', 'Selected branch does not belong to the selected unit.');
                    }
                }

                if ($request->filled('class_id')) {
                    if ($resolvedUnitForValidation !== null && $selectedClassUnitId !== null && (int)$selectedClassUnitId !== (int)$resolvedUnitForValidation) {
                        $validator->errors()->add('class_id', 'Selected class does not belong to the selected unit or branch.');
                    }
                }
            });

            if ($validator->fails()) {
                $data['errors'] = $validator->errors();
                $data = $this->siteAuthService->admin_after_login_layout($title, $page_name, $data);
                return view('front.pages.' . $page_name, $data);
            }

            $studentQuery = $this->buildStudentIdCardQuery();
            $studentQuery->where('students.status', '=', 1);

            if ($data['search_student_id'] !== '') {
                $escapedStudentId = addcslashes($data['search_student_id'], '\\%_');
                $studentQuery->where('students.student_id_serial', 'like', '%' . $escapedStudentId . '%');
            } else {
                if ($data['search_unit_id'] !== '') {
                    $studentQuery->where('students.unit_id', '=', (int)$data['search_unit_id']);
                }

                if ($data['search_branch_id'] !== '') {
                    $studentQuery->where('students.branch_id', '=', (int)$data['search_branch_id']);
                }

                if ($data['search_class_id'] !== '') {
                    $unitForClassFilter = $this->resolveIdCardUnitId($data['search_unit_id'], $data['search_branch_id']);
                    if ($unitForClassFilter === null && $selectedClassUnitId !== null) {
                        $unitForClassFilter = $selectedClassUnitId;
                    }

                    if ((int)$unitForClassFilter === 1) {
                        $studentQuery->where('students.vhs_class_id', '=', (int)$data['search_class_id']);
                    } elseif ((int)$unitForClassFilter === 2) {
                        $studentQuery->where('students.tsa_class_id', '=', (int)$data['search_class_id']);
                    } else {
                        $studentQuery->where(function ($query) use ($data) {
                            $query->where('students.vhs_class_id', '=', (int)$data['search_class_id'])
                                  ->orWhere('students.tsa_class_id', '=', (int)$data['search_class_id']);
                        });
                    }
                }
            }

            $data['students']       = $studentQuery->orderBy('students.full_name', 'ASC')
                                                ->orderBy('students.id', 'ASC')
                                                ->paginate(12)
                                                ->withQueryString();
            $data['search_applied'] = true;
        }

        $data['selection_key'] = $this->buildStudentIdCardSelectionKey(
            $data['search_student_id'],
            $data['search_unit_id'],
            $data['search_branch_id'],
            $data['search_class_id']
        );

        $data = $this->siteAuthService->admin_after_login_layout($title, $page_name, $data);
        return view('front.pages.' . $page_name, $data);
    }
    public function generateIdCardPreview(Request $request)
    {
        return $this->generateStudentCardPreview(
            $request,
            'student/generate-id-card',
            'Student ID Card Preview',
            'id-card-bg.jpeg',
            'ID Card',
            'ID Cards'
        );
    }
    public function generateEscortCardPreview(Request $request)
    {
        return $this->generateStudentCardPreview(
            $request,
            'student/generate-id-card',
            'Student Escort Card Preview',
            'escort-card-bg.jpeg',
            'Escort Card',
            'Escort Cards'
        );
    }
    private function generateStudentCardPreview(Request $request, string $redirectPath, string $previewTitle, string $backgroundFile, string $cardLabel, string $cardPluralLabel)
    {
        $validator = Validator::make($request->all(), [
            'student_ids'   => 'required|array|min:1|max:200',
            'student_ids.*' => 'integer|distinct|exists:students,id',
        ]);

        if ($validator->fails()) {
            return redirect($redirectPath)->with('error_message', $validator->errors()->first());
        }

        $selectedIds = array_values(array_unique(array_map('intval', (array)$request->input('student_ids', []))));
        if (count($selectedIds) === 0) {
            return redirect($redirectPath)->with('error_message', 'Please select at least one student to generate ' . $cardPluralLabel . '.');
        }

        $studentsById = $this->buildStudentIdCardQuery()
                            ->whereIn('students.id', $selectedIds)
                            ->get()
                            ->keyBy('id');

        $orderedStudents = collect($selectedIds)->map(function ($studentId) use ($studentsById) {
            return $studentsById->get($studentId);
        })->filter()->values();

        if ($orderedStudents->count() !== count($selectedIds)) {
            return redirect($redirectPath)->with('error_message', 'One or more selected students are not available for ' . $cardLabel . ' generation.');
        }

        $data['module']             = $this->data;
        $data['brand']              = $this->getStudentIdCardBranding();
        $data['students']           = $orderedStudents;
        $data['selected_count']     = $orderedStudents->count();
        $data['generated_at']       = Carbon::now()->format('d-m-Y h:i A');
        $data['preview_title']      = $previewTitle;
        // $data['card_background_url'] = config('constants.app_url') . config('constants.uploads_url_path') . 'student/' . $backgroundFile;
        $data['card_background_url'] = config('constants.uploads_url') . 'student/' . $backgroundFile;

        // $cardBackgroundUrl = config('constants.uploads_url') . 'student/id-card-bg.jpeg';

        return view('front.pages.student.id-card.preview', $data);
    }
    public function generateIdCardBranches(Request $request)
    {
        $request->validate([
            'unit_id' => 'required|integer|exists:units,id',
        ]);

        $branches = $this->getStudentIdCardBranches((int)$request->input('unit_id'));

        return response()->json([
            'status'   => true,
            'branches' => $branches,
        ]);
    }
    public function generateIdCardClasses(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|integer|exists:branches,id',
        ]);

        $branch = Branch::select('unit_id')
                        ->where('id', '=', (int)$request->input('branch_id'))
                        ->where('status', '=', 1)
                        ->first();

        if (!$branch) {
            return response()->json([
                'status'  => false,
                'classes' => [],
            ], 422);
        }

        $classes = $this->getStudentIdCardClasses((int)$request->input('branch_id'), (int)$branch->unit_id);

        return response()->json([
            'status'  => true,
            'classes' => $classes,
        ]);
    }
    /* fees collection */
    public function feesCollection(Request $request){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' List';
            $page_name                      = 'student.fees-collection';
            $data['sessions']               = Session::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();

            $defaultSessionData             = $this->getFinancialSessionData();
            $data['search_unit']            = '';
            $data['search_branch']          = '';
            $data['search_session']         = $defaultSessionData['session_id'];
            $data['search_session_name']    = $defaultSessionData['session_name'];
            $data['financial_months']       = $this->buildFinancialMonths($defaultSessionData['start_year'], $defaultSessionData['end_year']);
            $data['is_search']              = 0;
            $data['rows']                   = [];
            $data['report_unit']            = '';
            $data['report_branch']          = '';
            $data['report_class']           = '';
            $data['report_session']         = $defaultSessionData['session_id'];
            $data['report_session_name']    = $defaultSessionData['session_name'];
            $data['report_months']          = [];
            $data['bankAccounts']           = $this->bankAccountOptions();

            if($request->isMethod('post')){
                $unit_id            = $request->unit_id;
                $branch_id          = $request->branch_id;
                $session_id         = $request->collection_session_id;
                $sessionData        = $this->getFinancialSessionData($session_id);
                $financialMonths    = $this->buildFinancialMonths($sessionData['start_year'], $sessionData['end_year']);

                $data['search_unit']            = $unit_id;
                $data['search_branch']          = $branch_id;
                $data['search_session']         = $sessionData['session_id'];
                $data['search_session_name']    = $sessionData['session_name'];
                $data['financial_months']       = $financialMonths;
                $data['is_search']              = 1;
                $data['report_unit']            = $unit_id;
                $data['report_branch']          = $branch_id;
                $data['report_session']         = $sessionData['session_id'];
                $data['report_session_name']    = $sessionData['session_name'];

                $paymentSubQuery = $this->buildFinancialPaymentSummarySubQuery($sessionData['session_id'], $financialMonths);

                $data['rows'] = Student::select(
                                        'students.id',
                                        'students.student_id_serial',
                                        'students.full_name',
                                        'students.father_mobile',
                                        'students.photo',
                                        'students.session_id',
                                        'units.name as unit_name',
                                        'branches.name as branch_name',
                                        'sessions.name as session_name',
                                        'users.first_name',
                                        'users.last_name',
                                        DB::raw("COALESCE(tsa_classes.name, vhs_classes.name) as class_name"),
                                        'payments.*'
                                    )
                                    ->leftJoinSub($paymentSubQuery, 'payments', function ($join) {
                                        $join->on('payments.student_id', '=', 'students.id');
                                    })
                                    ->leftJoin('sessions', 'sessions.id', '=', 'students.session_id')
                                    ->leftJoin('units', 'units.id', '=', 'students.unit_id')
                                    ->leftJoin('branches', 'branches.id', '=', 'students.branch_id')
                                    ->leftJoin('classes as tsa_classes', 'tsa_classes.id', '=', 'students.tsa_class_id')
                                    ->leftJoin('classes as vhs_classes', 'vhs_classes.id', '=', 'students.vhs_class_id')
                                    ->leftJoin('users', 'users.id', '=', 'students.created_by')
                                    ->where('students.status', '=', 1)
                                    ->where('students.session_id', '=', $sessionData['session_id'])
                                    ->where('students.unit_id', $unit_id)
                                    ->where('students.branch_id', $branch_id)
                                    ->orderBy('students.id', 'DESC')
                                    ->get();
            }            
            
            $data['units']                  = Unit::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['branches']               = Branch::select('id', 'name', 'unit_id')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['classes']                = Classes::select('id', 'name', 'unit_id')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            
            $data = $this->siteAuthService->admin_after_login_layout($title, $page_name, $data);
            return view('front.pages.' . $page_name, $data);
        }
        public function feesCollectionDueReport(Request $request){
            $validator = Validator::make($request->all(), [
                'report_unit_id'     => 'required|integer|min:1',
                'report_branch_id'   => 'required|integer|min:1',
                'report_class_id'    => 'nullable|integer|min:1',
                'report_session_id'   => 'required|integer|min:1',
                'report_months'      => 'required|array|min:1',
                'report_months.*'    => 'integer|min:1|max:12',
            ]);

            if ($validator->fails()) {
                return redirect('student/fees-collection')->with('error_message', $validator->errors()->first());
            }

            $unitId          = (int)$request->report_unit_id;
            $branchId        = (int)$request->report_branch_id;
            $classId         = (($request->report_class_id != '') ? (int)$request->report_class_id : '');
            $sessionData     = $this->getFinancialSessionData($request->report_session_id);
            $financialMonths = $this->buildFinancialMonths($sessionData['start_year'], $sessionData['end_year']);
            $selectedMonths  = array_values(array_unique(array_map('intval', (array)$request->report_months)));

            if (count($selectedMonths) == 0) {
                return redirect('student/fees-collection')->with('error_message', 'Please select at least one month for due report.');
            }

            $selectedMonthLookup = array_flip($selectedMonths);
            $monthColumns = [];
            foreach ($financialMonths as $monthConfig) {
                if (!array_key_exists($monthConfig['month'], $selectedMonthLookup)) {
                    continue;
                }

                $monthColumns[] = [
                    'month' => $monthConfig['month'],
                    'year'  => $monthConfig['year'],
                    'name'  => $monthConfig['short'],
                    'alias'  => $monthConfig['alias'] . '_due',
                ];
            }

            if (count($monthColumns) == 0) {
                return redirect('student/fees-collection')->with('error_message', 'Please select at least one valid month for the selected session.');
            }

            $paymentSubQuery = $this->buildFinancialDueSubQuery($sessionData['session_id'], $monthColumns);

            $reportQuery = Student::select(
                                    'students.student_id_serial',
                                    'students.full_name',
                                    'students.father_mobile',
                                    'units.name as unit_name',
                                    'branches.name as branch_name',
                                    'sessions.name as session_name',
                                    DB::raw("COALESCE(tsa_classes.name, vhs_classes.name) as class_name"),
                                    'payment_due.*'
                                )
                                ->leftJoinSub($paymentSubQuery, 'payment_due', function ($join) {
                                    $join->on('payment_due.student_id', '=', 'students.id');
                                })
                                ->leftJoin('sessions', 'sessions.id', '=', 'students.session_id')
                                ->leftJoin('units', 'units.id', '=', 'students.unit_id')
                                ->leftJoin('branches', 'branches.id', '=', 'students.branch_id')
                                ->leftJoin('classes as tsa_classes', 'tsa_classes.id', '=', 'students.tsa_class_id')
                                ->leftJoin('classes as vhs_classes', 'vhs_classes.id', '=', 'students.vhs_class_id')
                                ->where('students.status', '=', 1)
                                ->where('students.session_id', '=', $sessionData['session_id'])
                                ->where('students.unit_id', $unitId)
                                ->where('students.branch_id', $branchId)
                                ->whereRaw('COALESCE(payment_due.total_due, 0) > 0');

            if ($classId != '') {
                $reportQuery->where(function ($query) use ($classId) {
                    $query->where('students.tsa_class_id', '=', $classId)
                          ->orWhere('students.vhs_class_id', '=', $classId);
                });
            }

            $reportRows = $reportQuery->orderBy('students.full_name', 'ASC')->get();

            $unitName   = (($getUnit = Unit::select('name')->where('id', '=', $unitId)->first()) ? $getUnit->name : '');
            $branchName = (($getBranch = Branch::select('name')->where('id', '=', $branchId)->first()) ? $getBranch->name : '');
            $className  = 'ALL';
            if ($classId != '') {
                $className = (($getClass = Classes::select('name')->where('id', '=', $classId)->first()) ? $getClass->name : 'ALL');
            }

            $reportData = [
                'rows'                => $reportRows,
                'month_columns'       => $monthColumns,
                'collection_session'  => $sessionData['session_name'],
                'unit_name'           => $unitName,
                'branch_name'         => $branchName,
                'class_name'          => $className,
                'generated_at'        => date('d-m-Y h:i A'),
            ];

            $safeSessionName = preg_replace('/[^A-Za-z0-9]+/', '-', $sessionData['session_name']);
            $safeSessionName = trim($safeSessionName, '-');
            if ($safeSessionName == '') {
                $safeSessionName = 'session';
            }

            $fileName = 'due-student-report-' . $safeSessionName . '-' . date('YmdHis') . '.xls';
            $html = view('front.pages.student.fees-due-report-excel', $reportData)->render();

            return response("\xEF\xBB\xBF" . $html)
                    ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
                    ->header('Content-Disposition', 'attachment; filename=' . $fileName);
        }
        public function updateFeesCollection(Request $request){
            $paymentMode = (string)$request->input('payment_mode');
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
            $paymentReferenceRules = (($paymentMode === 'Bank')
                                    ? [
                                        'required',
                                        'string',
                                        'max:2000',
                                    ]
                                    : [
                                        'nullable',
                                        'string',
                                        'max:2000',
                                    ]);

            $validator = Validator::make($request->all(), [
                'student_id'     => 'required|integer',
                'payable_month'  => 'required|integer|min:1|max:12',
                'payable_year'   => 'required|integer|min:2000|max:2100',
                'payment_mode'   => 'required|in:Cash,Bank',
                'ledger_id'      => 'required|integer|in:1',
                'bank_account_id' => $bankAccountRules,
                'payment_reference' => $paymentReferenceRules,
                'payment_amount' => 'required|numeric|gt:0',
            ], [
                'bank_account_id.required'    => 'Please select a bank account when payment mode is Bank.',
                'bank_account_id.exists'      => 'Selected bank account is not valid.',
                'payment_reference.required'   => 'Please enter a payment reference when payment mode is Bank.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $student = Student::select('id', 'full_name', 'student_id_serial')
                                ->where('id', $request->student_id)
                                ->first();
            if (!$student) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Student not found.',
                ], 404);
            }

            $studentPayment = StudentPayment::where('student_id', $request->student_id)
                                            ->where('payable_month', $request->payable_month)
                                            ->where('payable_year', $request->payable_year)
                                            ->first();
            if (!$studentPayment) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Payment schedule not found for this month.',
                ], 404);
            }

            $monthName       = date('F', mktime(0, 0, 0, (int)$request->payable_month, 1));
            $payableAmount   = (float)$studentPayment->payable_amount;
            $alreadyPaid     = (float)$studentPayment->payment_amount;
            $enteredAmount   = (float)$request->payment_amount;
            $currentDue      = max($payableAmount - $alreadyPaid, 0);

            if ($currentDue <= 0) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No due left for '.$student->full_name.' ('.$monthName.' '.$request->payable_year.').',
                ], 422);
            }

            if ($enteredAmount > $payableAmount) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Payment amount cannot be greater than payable amount for '.$student->full_name.' ('.$monthName.' '.$request->payable_year.').',
                ], 422);
            }

            if ($enteredAmount > $currentDue) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Payment amount cannot be greater than due amount for '.$student->full_name.' ('.$monthName.' '.$request->payable_year.').',
                ], 422);
            }

            $newPaidAmount   = $alreadyPaid + $enteredAmount;
            $newDueAmount    = max($payableAmount - $newPaidAmount, 0);
            $updatedBy       = ((session()->has('user_data') && array_key_exists('user_id', session('user_data')))?session('user_data')['user_id']:((Auth::check())?Auth::id():0));
            $transactionAmount = number_format($enteredAmount, 2, '.', '');
            $ledgerId = (int)$request->ledger_id;
            $bankAccountId = (($paymentMode === 'Bank') ? ((int)$request->bank_account_id ?: null) : null);
            $paymentReference = $this->normalizePaymentReference($paymentMode, $request->payment_reference);

            DB::transaction(function () use ($studentPayment, $newPaidAmount, $newDueAmount, $updatedBy, $student, $monthName, $request, $transactionAmount, $paymentMode, $ledgerId, $bankAccountId, $paymentReference) {
                $studentPayment->update([
                    'payment_amount' => $newPaidAmount,
                    'payment_date'   => date('Y-m-d'),
                    'due_amount'     => $newDueAmount,
                    'updated_by'     => $updatedBy,
                ]);

                $lastTransaction = Transaction::withTrashed()->select('sl_no')
                                                ->orderBy('sl_no', 'DESC')
                                                ->lockForUpdate()
                                                ->first();
                $nextSlNo       = (($lastTransaction)?((int)$lastTransaction->sl_no + 1):1);
                $nextTxnNo      = str_pad($nextSlNo, 8, '0', STR_PAD_LEFT);

                                Transaction::create([
                    'sl_no'                  => $nextSlNo,
                    'txn_no'                 => $nextTxnNo,
                    'fee_id'                 => $studentPayment->id,
                    'unit_id'                => (int)$studentPayment->unit_id,
                    'branch_id'              => (int)$studentPayment->branch_id,
                    'ledger_id'              => $ledgerId,
                    'payment_mode'           => $paymentMode,
                    'bank_account_id'        => $bankAccountId,
                    'payment_reference'      => $paymentReference,
                    'type'                   => 'INCOME',
                    'transaction_timestamp'  => Carbon::now(),
                    'transaction_amount'     => $transactionAmount,
                    'particulars'            => 'Fees collection for '.$student->full_name.' ('.$student->student_id_serial.') '.$monthName.' '.$request->payable_year.' with amount '.$transactionAmount,
                    'created_by'             => $updatedBy,
                    'updated_by'             => $updatedBy,
                ]);
            });

            $totals = StudentPayment::select(
                                        DB::raw("COALESCE(SUM(payable_amount), 0) as total_payable"),
                                        DB::raw("COALESCE(SUM(payment_amount), 0) as total_paid"),
                                        DB::raw("COALESCE(SUM(due_amount), 0) as total_due")
                                    )
                                    ->where('student_id', $request->student_id)
                                    ->where('payable_year', $request->payable_year)
                                    ->first();

            return response()->json([
                'status'  => true,
                'message' => 'Fees collection successfull for '.$student->full_name.' ('.$monthName.' '.$request->payable_year.').',
                'student' => [
                    'id'   => $student->id,
                    'name' => $student->full_name,
                ],
                'month' => [
                    'name'    => $monthName,
                    'year'    => (int)$request->payable_year,
                    'payable' => number_format($payableAmount, 2),
                    'paid'    => number_format($newPaidAmount, 2),
                    'due'     => number_format($newDueAmount, 2),
                    'payable_numeric' => $payableAmount,
                    'paid_numeric'    => $newPaidAmount,
                    'due_numeric'     => $newDueAmount,
                ],
                'total' => [
                    'payable' => number_format((float)$totals->total_payable, 2),
                    'paid'    => number_format((float)$totals->total_paid, 2),
                    'due'     => number_format((float)$totals->total_due, 2),
                ],
            ]);
        }
        public function feesEntry(){
            // $students                   = Student::select('id', 'unit_id', 'branch_id', 'session_id', 'monthly_fees')
            //                                     ->where(function ($query) {
            //                                         $query->where('status', '!=', 3)
            //                                                 ->where('id', '=', 11)
            //                                               ->orWhereNull('status');
            //                                     })
            //                                     ->get();
            $students                   = Student::select('id', 'unit_id', 'branch_id', 'session_id', 'monthly_fees')
                                                ->where(function ($query) {
                                                    $query->where('status', '!=', 3)
                                                          ->orWhereNull('status');
                                                })
                                                ->get();

            if($students){
                $targetMonths = [
                    ['month' => 4, 'year' => 2026],
                    ['month' => 5, 'year' => 2026],
                    ['month' => 6, 'year' => 2026],
                    ['month' => 7, 'year' => 2026],
                    ['month' => 8, 'year' => 2026],
                    ['month' => 9, 'year' => 2026],
                    ['month' => 10, 'year' => 2026],
                    ['month' => 11, 'year' => 2026],
                    ['month' => 12, 'year' => 2026],
                    ['month' => 1, 'year' => 2027],
                    ['month' => 2, 'year' => 2027],
                    ['month' => 3, 'year' => 2027],
                ];
                $updatedBy = $this->currentUserId();
                $createdCount = 0;
                $updatedCount = 0;
                $skippedCount  = 0;

                DB::transaction(function () use ($students, $targetMonths, $updatedBy, &$createdCount, &$updatedCount, &$skippedCount) {
                    foreach($students as $student){
                        if ((float)$student->monthly_fees <= 0) {
                            $skippedCount++;
                            continue;
                        }

                        foreach($targetMonths as $monthConfig){
                            $studentPayment = StudentPayment::firstOrNew([
                                'student_id'    => $student->id,
                                'payable_month' => $monthConfig['month'],
                                'payable_year'  => $monthConfig['year'],
                            ]);

                            $alreadyPaid = (float)((isset($studentPayment->payment_amount)) ? $studentPayment->payment_amount : 0);

                            $studentPayment->student_id      = $student->id;
                            $studentPayment->unit_id         = $student->unit_id;
                            $studentPayment->branch_id       = $student->branch_id;
                            $studentPayment->payable_month   = $monthConfig['month'];
                            $studentPayment->payable_year    = $monthConfig['year'];
                            $studentPayment->payable_amount  = $student->monthly_fees;
                            $studentPayment->due_amount      = max(((float)$student->monthly_fees - $alreadyPaid), 0);
                            $studentPayment->updated_by      = $updatedBy;

                            if (!$studentPayment->exists) {
                                $studentPayment->payment_amount = 0;
                                $studentPayment->payment_date   = null;
                                $studentPayment->created_by     = $updatedBy;
                                $createdCount++;
                            } else {
                                $updatedCount++;
                            }

                            $studentPayment->save();
                        }
                    }
                });

                echo 'Student payment schedule updated for January 2027 to March 2027. Created: '.$createdCount.'. Updated: '.$updatedCount.'. Skipped: '.$skippedCount.'.';
                return;
            }
            // die;
            echo 'No active students found';
        }
    /* fees collection */

    public function admissionFeesEntry(){
        $students = Student::select(
                            'id',
                            'student_id_serial',
                            'full_name',
                            'unit_id',
                            'branch_id',
                            'admission_date',
                            'admission_fees'
                        )
                        ->where(function ($query) {
                            $query->where('status', '!=', 3)
                                    ->orWhereNull('status');
                        })
                        ->where('admission_fees', '>', 0)
                        ->orderBy('id', 'ASC')
                        ->get();

        if (!$students || $students->isEmpty()) {
            echo 'No students found';
            return;
        }

        $createdCount = 0;
        $skippedCount  = 0;
        $updatedBy     = $this->currentUserId();

        DB::transaction(function () use ($students, $updatedBy, &$createdCount, &$skippedCount) {
            $lastTransaction = Transaction::withTrashed()->select('sl_no')
                                    ->orderBy('sl_no', 'DESC')
                                    ->lockForUpdate()
                                    ->first();

            $nextSlNo = (($lastTransaction) ? ((int)$lastTransaction->sl_no + 1) : 1);

            foreach ($students as $student) {
                $admissionFeeAmount = number_format((float)$student->admission_fees, 2, '.', '');

                $alreadyExists = Transaction::where('fee_id', 0)
                                            ->where('type', 'INCOME')
                                            ->where('unit_id', (int)$student->unit_id)
                                            ->where('branch_id', (int)$student->branch_id)
                                            ->where('transaction_amount', $admissionFeeAmount)
                                            ->where(function ($query) use ($student) {
                                                $query->where('particulars', 'like', '%'.$student->full_name.'%')
                                                        ->orWhere('particulars', 'like', '%'.$student->student_id_serial.'%')
                                                        ->orWhere('note', 'like', '%'.$student->full_name.'%')
                                                        ->orWhere('note', 'like', '%'.$student->student_id_serial.'%');
                                            })
                                            ->exists();

                if ($alreadyExists) {
                    $skippedCount++;
                    continue;
                }

                $transactionTimestamp = (!empty($student->admission_date))
                                            ? $student->admission_date . ' ' . date('H:i:s')
                                            : date('Y-m-d H:i:s');
                $txnNo = str_pad($nextSlNo, 8, '0', STR_PAD_LEFT);

                $fields1 = [
                    'sl_no'                  => $nextSlNo,
                    'txn_no'                 => $txnNo,
                    'fee_id'                 => 0,
                    'unit_id'                => (int)$student->unit_id,
                    'branch_id'              => (int)$student->branch_id,
                    'payment_mode'           => 'Cash',
                    'payment_reference'      => null,
                    'type'                   => 'INCOME',
                    'transaction_timestamp'  => $transactionTimestamp,
                    'transaction_amount'     => $admissionFeeAmount,
                    'particulars'            => 'Admission fee collected for '.$student->full_name.' ('.$student->student_id_serial.') during backfill',
                    'note'                   => 'Admission fee backfill for '.$student->student_id_serial,
                    'status'                 => 1,
                    'created_by'             => $updatedBy,
                    'updated_by'             => $updatedBy,
                ];

                Transaction::insert($fields1);

                $nextSlNo++;
                $createdCount++;
            }
        });

        echo 'Admission fee transactions created: '.$createdCount.'. Skipped: '.$skippedCount.'.';
    }

    private function getFinancialSessionData($sessionId = null)
    {
        $session = null;
        if ($sessionId !== null && $sessionId !== '') {
            $session = Session::select('id', 'name')
                                ->where('id', '=', (int)$sessionId)
                                ->first();
        }

        $currentYear  = (int)Carbon::now()->year;
        $currentMonth = (int)Carbon::now()->month;
        $startYear    = (($currentMonth >= 4) ? $currentYear : ($currentYear - 1));
        $endYear      = $startYear + 1;

        if (!$session) {
            $activeSessions = Session::select('id', 'name')
                                        ->where('status', '=', 1)
                                        ->orderBy('name', 'ASC')
                                        ->get();

            foreach ($activeSessions as $sessionItem) {
                $sessionName = trim((string)$sessionItem->name);
                if ($sessionName !== '' && preg_match('/(\d{4})\D+(\d{4})/', $sessionName, $matches)) {
                    if ((int)$matches[1] === $startYear && (int)$matches[2] === $endYear) {
                        $session = $sessionItem;
                        break;
                    }
                }
            }

            if (!$session && $activeSessions->count() > 0) {
                $session = $activeSessions->first();
            }
        }

        if ($session) {
            $sessionName = trim((string)$session->name);
            if ($sessionName !== '' && preg_match('/(\d{4})\D+(\d{4})/', $sessionName, $matches)) {
                $startYear = (int)$matches[1];
                $endYear   = (int)$matches[2];

                return [
                    'session_id'   => (int)$session->id,
                    'session_name' => $sessionName,
                    'start_year'   => $startYear,
                    'end_year'     => $endYear,
                ];
            }
        }

        return [
            'session_id'   => (($session) ? (int)$session->id : 0),
            'session_name' => (($session && trim((string)$session->name) != '') ? trim((string)$session->name) : ($startYear . '-' . $endYear)),
            'start_year'   => $startYear,
            'end_year'     => $endYear,
        ];
    }

    private function findSessionByFinancialYear($startYear, $endYear)
    {
        $sessions = Session::select('id', 'name')
                            ->where('status', '=', 1)
                            ->orderBy('name', 'ASC')
                            ->get();

        foreach ($sessions as $sessionItem) {
            $sessionName = trim((string)$sessionItem->name);
            if ($sessionName !== '' && preg_match('/(\d{4})\D+(\d{4})/', $sessionName, $matches)) {
                if ((int)$matches[1] === (int)$startYear && (int)$matches[2] === (int)$endYear) {
                    return $sessionItem;
                }
            }
        }

        return null;
    }

    private function getNextFinancialSessionData($sessionId = null)
    {
        $currentSessionData = $this->getFinancialSessionData($sessionId);
        $nextStartYear      = ((int)$currentSessionData['end_year']);
        $nextEndYear        = $nextStartYear + 1;
        $nextSession        = $this->findSessionByFinancialYear($nextStartYear, $nextEndYear);

        return [
            'session_id'   => (($nextSession) ? (int)$nextSession->id : ((int)$currentSessionData['session_id'])),
            'session_name' => (($nextSession && trim((string)$nextSession->name) != '') ? trim((string)$nextSession->name) : ($nextStartYear . '-' . $nextEndYear)),
            'start_year'   => $nextStartYear,
            'end_year'     => $nextEndYear,
        ];
    }

    private function buildFinancialMonths($startYear, $endYear)
    {
        return [
            ['month' => 4,  'year' => (int)$startYear, 'alias' => 'apr', 'short' => 'Apr'],
            ['month' => 5,  'year' => (int)$startYear, 'alias' => 'may', 'short' => 'May'],
            ['month' => 6,  'year' => (int)$startYear, 'alias' => 'jun', 'short' => 'Jun'],
            ['month' => 7,  'year' => (int)$startYear, 'alias' => 'jul', 'short' => 'Jul'],
            ['month' => 8,  'year' => (int)$startYear, 'alias' => 'aug', 'short' => 'Aug'],
            ['month' => 9,  'year' => (int)$startYear, 'alias' => 'sep', 'short' => 'Sep'],
            ['month' => 10, 'year' => (int)$startYear, 'alias' => 'oct', 'short' => 'Oct'],
            ['month' => 11, 'year' => (int)$startYear, 'alias' => 'nov', 'short' => 'Nov'],
            ['month' => 12, 'year' => (int)$startYear, 'alias' => 'dec', 'short' => 'Dec'],
            ['month' => 1,  'year' => (int)$endYear,   'alias' => 'jan', 'short' => 'Jan'],
            ['month' => 2,  'year' => (int)$endYear,   'alias' => 'feb', 'short' => 'Feb'],
            ['month' => 3,  'year' => (int)$endYear,   'alias' => 'mar', 'short' => 'Mar'],
        ];
    }

    private function buildFinancialPaymentSummarySubQuery($sessionId, array $financialMonths)
    {
        $paymentSubQuery = DB::table('student_payments as sp')
                                ->join('students as st', 'st.id', '=', 'sp.student_id')
                                ->select('sp.student_id')
                                ->where('st.session_id', '=', (int)$sessionId);

        $sessionMonthConditions = [];
        foreach ($financialMonths as $monthConfig) {
            $monthNumber = (int)$monthConfig['month'];
            $monthYear   = (int)$monthConfig['year'];
            $monthAlias  = $monthConfig['alias'];

            $paymentSubQuery->addSelect(DB::raw("SUM(CASE WHEN sp.payable_month = {$monthNumber} AND sp.payable_year = {$monthYear} THEN sp.payable_amount ELSE 0 END) as {$monthAlias}_payable"));
            $paymentSubQuery->addSelect(DB::raw("SUM(CASE WHEN sp.payable_month = {$monthNumber} AND sp.payable_year = {$monthYear} THEN sp.payment_amount ELSE 0 END) as {$monthAlias}_paid"));
            $paymentSubQuery->addSelect(DB::raw("SUM(CASE WHEN sp.payable_month = {$monthNumber} AND sp.payable_year = {$monthYear} THEN sp.due_amount ELSE 0 END) as {$monthAlias}_due"));

            $sessionMonthConditions[] = "(sp.payable_month = {$monthNumber} AND sp.payable_year = {$monthYear})";
        }

        $sessionMonthCondition = implode(' OR ', $sessionMonthConditions);

        $paymentSubQuery->addSelect(DB::raw("SUM(CASE WHEN {$sessionMonthCondition} THEN sp.payable_amount ELSE 0 END) as total_payable"))
                        ->addSelect(DB::raw("SUM(CASE WHEN {$sessionMonthCondition} THEN sp.payment_amount ELSE 0 END) as total_paid"))
                        ->addSelect(DB::raw("SUM(CASE WHEN {$sessionMonthCondition} THEN sp.due_amount ELSE 0 END) as total_due"))
                        ->groupBy('sp.student_id');

        return $paymentSubQuery;
    }

    private function buildFinancialDueSubQuery($sessionId, array $monthColumns)
    {
        $paymentSubQuery = DB::table('student_payments as sp')
                                ->join('students as st', 'st.id', '=', 'sp.student_id')
                                ->select('sp.student_id')
                                ->where('st.session_id', '=', (int)$sessionId);

        $monthConditions = [];
        foreach ($monthColumns as $monthConfig) {
            $monthNumber = (int)$monthConfig['month'];
            $monthYear   = (int)$monthConfig['year'];
            $monthAlias  = $monthConfig['alias'];

            $paymentSubQuery->addSelect(DB::raw("SUM(CASE WHEN sp.payable_month = {$monthNumber} AND sp.payable_year = {$monthYear} THEN sp.due_amount ELSE 0 END) as {$monthAlias}"));
            $monthConditions[] = "(sp.payable_month = {$monthNumber} AND sp.payable_year = {$monthYear})";
        }

        $monthCondition = implode(' OR ', $monthConditions);
        $paymentSubQuery->addSelect(DB::raw("SUM(CASE WHEN {$monthCondition} THEN sp.due_amount ELSE 0 END) as total_due"))
                        ->groupBy('sp.student_id');

        return $paymentSubQuery;
    }

    private function getStudentIdCardBranding()
    {
        $siteName = trim((string)Helper::getSettingValue('site_name'));
        $siteTagline = trim((string)Helper::getSettingValue('description'));
        $siteLogo = trim((string)Helper::getSettingValue('site_logo'));
        $siteName = (($siteName !== '') ? $siteName : config('app.name'));

        $initials = '';
        $nameParts = preg_split('/\s+/', $siteName, -1, PREG_SPLIT_NO_EMPTY);
        if (is_array($nameParts)) {
            foreach (array_slice($nameParts, 0, 3) as $part) {
                $initials .= strtoupper(substr($part, 0, 1));
            }
        }
        if ($initials === '') {
            $initials = 'ID';
        }

        return [
            'site_name'     => $siteName,
            'site_tagline'  => $siteTagline,
            'site_logo_url' => (($siteLogo !== '') ? config('constants.app_url') . config('constants.uploads_url_path') . $siteLogo : ''),
            'site_initials' => $initials,
        ];
    }

    private function buildStudentIdCardQuery()
    {
        return Student::select(
                        'students.id',
                        'students.student_id_serial',
                        'students.full_name',
                        'students.photo',
                        'students.dob',
                        'students.permanent_address',
                        'students.permanent_pincode',
                        'students.father_name',
                        'students.mother_name',
                        'students.emergency_name',
                        'students.father_mobile',
                        'students.mother_mobile',
                        'students.emergency_phone',
                        'students.blood_group',
                        'students.unit_id',
                        'students.branch_id',
                        'students.vhs_class_id',
                        'students.tsa_class_id',
                        'units.name as unit_name',
                        'branches.name as branch_name',
                        DB::raw('COALESCE(vhs_classes.name, tsa_classes.name) as class_name')
                    )
                    ->leftJoin('units', 'units.id', '=', 'students.unit_id')
                    ->leftJoin('branches', 'branches.id', '=', 'students.branch_id')
                    ->leftJoin('classes as vhs_classes', 'vhs_classes.id', '=', 'students.vhs_class_id')
                    ->leftJoin('classes as tsa_classes', 'tsa_classes.id', '=', 'students.tsa_class_id')
                    ->where(function ($query) {
                        $query->where('students.status', '=', 1)
                              ->orWhereNull('students.status');
                    });
    }

    private function getStudentIdCardBranches($unitId = null)
    {
        if (!$unitId) {
            return collect();
        }

        return Branch::select('id', 'name', 'unit_id')
                    ->where('status', '=', 1)
                    ->where('unit_id', '=', (int)$unitId)
                    ->orderBy('name', 'ASC')
                    ->get();
    }

    private function getStudentIdCardClasses($branchId = null, $unitId = null)
    {
        if ($branchId) {
            $branch = Branch::select('unit_id')
                            ->where('id', '=', (int)$branchId)
                            ->where('status', '=', 1)
                            ->first();
            if ($branch) {
                $unitId = (int)$branch->unit_id;
            }
        }

        if (!$unitId) {
            return collect();
        }

        return Classes::select('id', 'name', 'unit_id')
                        ->where('status', '=', 1)
                        ->where('unit_id', '=', (int)$unitId)
                        ->orderBy('name', 'ASC')
                        ->get();
    }

    private function resolveIdCardUnitId($unitId = null, $branchId = null)
    {
        if ($branchId) {
            $branch = Branch::select('unit_id')
                            ->where('id', '=', (int)$branchId)
                            ->where('status', '=', 1)
                            ->first();
            if ($branch) {
                return (int)$branch->unit_id;
            }
        }

        if ($unitId !== null && $unitId !== '') {
            return (int)$unitId;
        }

        return null;
    }

    private function buildStudentIdCardSelectionKey($studentId = '', $unitId = '', $branchId = '', $classId = '')
    {
        if (trim((string)$studentId) !== '') {
            return 'student-id-card-' . md5('student_id:' . trim((string)$studentId));
        }

        $payload = [
            'unit_id'   => (($unitId !== '') ? (int)$unitId : ''),
            'branch_id' => (($branchId !== '') ? (int)$branchId : ''),
            'class_id'  => (($classId !== '') ? (int)$classId : ''),
        ];

        return 'student-id-card-' . md5(json_encode($payload));
    }

    private function currentUserId()
    {
        if (session()->has('user_data') && array_key_exists('user_id', session('user_data'))) {
            return (int) session('user_data')['user_id'];
        }

        return (Auth::check()) ? (int) Auth::id() : 0;
    }

    private function normalizePaymentReference($paymentMode, $paymentReference)
    {
        if ($paymentMode !== 'Bank') {
            return null;
        }

        $paymentReference = trim((string)$paymentReference);

        return (($paymentReference !== '') ? $paymentReference : null);
    }

    private function bankAccountOptions()
    {
        return BankAccount::select('id', 'bank_name', 'bank_branch', 'account_no')
                        ->where('status', '=', 1)
                        ->orderBy('bank_name', 'ASC')
                        ->get();
    }
}
