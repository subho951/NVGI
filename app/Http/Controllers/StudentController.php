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
                                                                DB::raw("COALESCE(tsa_classes.name, vhs_classes.name) as class_name")
                                                            )
                                                            ->leftJoin('units', 'units.id', '=', 'students.unit_id')
                                                            ->leftJoin('branches', 'branches.id', '=', 'students.branch_id')
                                                            ->leftJoin('classes as tsa_classes', 'tsa_classes.id', '=', 'students.tsa_class_id')
                                                            ->leftJoin('classes as vhs_classes', 'vhs_classes.id', '=', 'students.vhs_class_id')
                                                            ->where(function ($q) {
                                                                $q->where('students.status', '!=', 3)
                                                                ->orWhereNull('students.status');
                                                            })
                                                            ->orderBy('students.id', 'DESC')
                                                            ->get();
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

            if($request->isMethod('post')){
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
                    'admission_fees'            => 'required',
                    'monthly_fees'              => 'required',
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
                        $student_id_serial  = 'NVGI-' . (($getUnit)?$getUnit->serial_id:'') . '-' . (($getBranch)?$getBranch->serial_id:'') . '-' . $next_sl_no_string;
                    } else {
                        $next_sl_no         = 1;
                        $next_sl_no_string  = str_pad($next_sl_no, 4, 0, STR_PAD_LEFT);
                        $student_id_serial  = 'NVGI-' . (($getUnit)?$getUnit->serial_id:'') . '-' . (($getBranch)?$getBranch->serial_id:'') . '-' . $next_sl_no_string;
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
                    'photo'                     => $photo,
                    'created_by'                => session('user_data')['user_id'],
                    'updated_by'                => session('user_data')['user_id'],
                ];
                // Helper::pr($fields);
                Student::create($fields);
                return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' updated successfully !!!');
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
            // Helper::pr($data['row']);
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
                    $student_id_serial  = 'NVGI-' . (($getUnit)?$getUnit->serial_id:'') . '-' . (($getBranch)?$getBranch->serial_id:'') . '-' . $next_sl_no_string;
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
                    'admission_fees'            => 'required',
                    'monthly_fees'              => 'required',
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
                    'photo'                     => $photo,
                    'updated_by'                => session('user_data')['user_id'],
                ]);
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
    /* fees collection */
        public function feesCollection(Request $request){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' List';
            $page_name                      = 'student.fees-collection';

            $data['search_unit']            = '';
            $data['search_branch']          = '';
            $data['search_collection_year'] = date('Y');
            $data['is_search']              = 0;
            $data['rows']                   = [];
            $data['report_unit']            = '';
            $data['report_branch']          = '';
            $data['report_class']           = '';
            $data['report_collection_year'] = date('Y');
            $data['report_months']          = [];

            if($request->isMethod('post')){
                $unit_id            = $request->unit_id;
                $branch_id          = $request->branch_id;
                $collection_year    = $request->collection_year;

                $data['search_unit']            = $unit_id;
                $data['search_branch']          = $branch_id;
                $data['search_collection_year'] = $collection_year;
                $data['is_search']              = 1;
                $data['report_unit']            = $unit_id;
                $data['report_branch']          = $branch_id;
                $data['report_collection_year'] = $collection_year;

                $paymentSubQuery = DB::table('student_payments')
                                        ->select(
                                            'student_id',

                                            DB::raw("SUM(CASE WHEN payable_month = 1 THEN payable_amount ELSE 0 END) as jan_payable"),
                                            DB::raw("SUM(CASE WHEN payable_month = 1 THEN payment_amount ELSE 0 END) as jan_paid"),
                                            DB::raw("SUM(CASE WHEN payable_month = 1 THEN due_amount ELSE 0 END) as jan_due"),

                                            DB::raw("SUM(CASE WHEN payable_month = 2 THEN payable_amount ELSE 0 END) as feb_payable"),
                                            DB::raw("SUM(CASE WHEN payable_month = 2 THEN payment_amount ELSE 0 END) as feb_paid"),
                                            DB::raw("SUM(CASE WHEN payable_month = 2 THEN due_amount ELSE 0 END) as feb_due"),

                                            DB::raw("SUM(CASE WHEN payable_month = 3 THEN payable_amount ELSE 0 END) as mar_payable"),
                                            DB::raw("SUM(CASE WHEN payable_month = 3 THEN payment_amount ELSE 0 END) as mar_paid"),
                                            DB::raw("SUM(CASE WHEN payable_month = 3 THEN due_amount ELSE 0 END) as mar_due"),

                                            DB::raw("SUM(CASE WHEN payable_month = 4 THEN payable_amount ELSE 0 END) as apr_payable"),
                                            DB::raw("SUM(CASE WHEN payable_month = 4 THEN payment_amount ELSE 0 END) as apr_paid"),
                                            DB::raw("SUM(CASE WHEN payable_month = 4 THEN due_amount ELSE 0 END) as apr_due"),

                                            DB::raw("SUM(CASE WHEN payable_month = 5 THEN payable_amount ELSE 0 END) as may_payable"),
                                            DB::raw("SUM(CASE WHEN payable_month = 5 THEN payment_amount ELSE 0 END) as may_paid"),
                                            DB::raw("SUM(CASE WHEN payable_month = 5 THEN due_amount ELSE 0 END) as may_due"),

                                            DB::raw("SUM(CASE WHEN payable_month = 6 THEN payable_amount ELSE 0 END) as jun_payable"),
                                            DB::raw("SUM(CASE WHEN payable_month = 6 THEN payment_amount ELSE 0 END) as jun_paid"),
                                            DB::raw("SUM(CASE WHEN payable_month = 6 THEN due_amount ELSE 0 END) as jun_due"),

                                            DB::raw("SUM(CASE WHEN payable_month = 7 THEN payable_amount ELSE 0 END) as jul_payable"),
                                            DB::raw("SUM(CASE WHEN payable_month = 7 THEN payment_amount ELSE 0 END) as jul_paid"),
                                            DB::raw("SUM(CASE WHEN payable_month = 7 THEN due_amount ELSE 0 END) as jul_due"),

                                            DB::raw("SUM(CASE WHEN payable_month = 8 THEN payable_amount ELSE 0 END) as aug_payable"),
                                            DB::raw("SUM(CASE WHEN payable_month = 8 THEN payment_amount ELSE 0 END) as aug_paid"),
                                            DB::raw("SUM(CASE WHEN payable_month = 8 THEN due_amount ELSE 0 END) as aug_due"),

                                            DB::raw("SUM(CASE WHEN payable_month = 9 THEN payable_amount ELSE 0 END) as sep_payable"),
                                            DB::raw("SUM(CASE WHEN payable_month = 9 THEN payment_amount ELSE 0 END) as sep_paid"),
                                            DB::raw("SUM(CASE WHEN payable_month = 9 THEN due_amount ELSE 0 END) as sep_due"),

                                            DB::raw("SUM(CASE WHEN payable_month = 10 THEN payable_amount ELSE 0 END) as oct_payable"),
                                            DB::raw("SUM(CASE WHEN payable_month = 10 THEN payment_amount ELSE 0 END) as oct_paid"),
                                            DB::raw("SUM(CASE WHEN payable_month = 10 THEN due_amount ELSE 0 END) as oct_due"),

                                            DB::raw("SUM(CASE WHEN payable_month = 11 THEN payable_amount ELSE 0 END) as nov_payable"),
                                            DB::raw("SUM(CASE WHEN payable_month = 11 THEN payment_amount ELSE 0 END) as nov_paid"),
                                            DB::raw("SUM(CASE WHEN payable_month = 11 THEN due_amount ELSE 0 END) as nov_due"),

                                            DB::raw("SUM(CASE WHEN payable_month = 12 THEN payable_amount ELSE 0 END) as dec_payable"),
                                            DB::raw("SUM(CASE WHEN payable_month = 12 THEN payment_amount ELSE 0 END) as dec_paid"),
                                            DB::raw("SUM(CASE WHEN payable_month = 12 THEN due_amount ELSE 0 END) as dec_due"),

                                            DB::raw("SUM(payable_amount) as total_payable"),
                                            DB::raw("SUM(payment_amount) as total_paid"),
                                            DB::raw("SUM(payable_amount - payment_amount) as total_due")
                                        )
                                        ->where('payable_year', $collection_year)
                                        ->groupBy('student_id');


                                    $data['rows'] = Student::select(
                                            'students.id',
                                            'students.student_id_serial',
                                            'students.full_name',
                                            'students.father_mobile',
                                            'students.photo',
                                            'units.name as unit_name',
                                            'branches.name as branch_name',
                                            'users.first_name',
                                            'users.last_name',
                                            DB::raw("COALESCE(tsa_classes.name, vhs_classes.name) as class_name"),

                                            'payments.*'
                                        )

                                        ->leftJoinSub($paymentSubQuery, 'payments', function ($join) {
                                            $join->on('payments.student_id', '=', 'students.id');
                                        })

                                        ->leftJoin('units', 'units.id', '=', 'students.unit_id')
                                        ->leftJoin('branches', 'branches.id', '=', 'students.branch_id')
                                        ->leftJoin('classes as tsa_classes', 'tsa_classes.id', '=', 'students.tsa_class_id')
                                        ->leftJoin('classes as vhs_classes', 'vhs_classes.id', '=', 'students.vhs_class_id')
                                        ->leftJoin('users', 'users.id', '=', 'students.created_by')

                                        ->where('students.status', '!=', 3)
                                        ->where('students.unit_id', $unit_id)
                                        ->where('students.branch_id', $branch_id)

                                        ->orderBy('students.id', 'DESC')
                                        ->get();

                // Helper::pr($data['rows']);
            }            
            
            $data['units']                  = Unit::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['branches']               = Branch::select('id', 'name', 'unit_id')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            $data['classes']                = Classes::select('id', 'name', 'unit_id')->where('status', '=', 1)->orderBy('name', 'ASC')->get();
            
            $data = $this->siteAuthService->admin_after_login_layout($title, $page_name, $data);
            return view('front.pages.' . $page_name, $data);
        }
        public function feesCollectionDueReport(Request $request){
            $validator = Validator::make($request->all(), [
                'report_unit_id'          => 'required|integer|min:1',
                'report_branch_id'        => 'required|integer|min:1',
                'report_class_id'         => 'nullable|integer|min:1',
                'report_collection_year'  => 'required|integer|min:2000|max:2100',
                'report_months'           => 'required|array|min:1',
                'report_months.*'         => 'integer|min:1|max:12',
            ]);

            if ($validator->fails()) {
                return redirect('student/fees-collection')->with('error_message', $validator->errors()->first());
            }

            $unitId             = (int)$request->report_unit_id;
            $branchId           = (int)$request->report_branch_id;
            $classId            = (($request->report_class_id != '')?(int)$request->report_class_id:'');
            $collectionYear     = (int)$request->report_collection_year;
            $selectedMonths     = array_values(array_unique(array_map('intval', (array)$request->report_months)));
            sort($selectedMonths);

            if (count($selectedMonths) == 0) {
                return redirect('student/fees-collection')->with('error_message', 'Please select at least one month for due report.');
            }

            $paymentSubQuery = DB::table('student_payments')->select('student_id');

            $monthColumns = [];
            foreach ($selectedMonths as $monthNumber) {
                $monthAlias      = 'month_' . $monthNumber . '_due';
                $monthShortName  = date('M', mktime(0, 0, 0, $monthNumber, 1));
                $monthColumns[]  = [
                    'month'      => $monthNumber,
                    'name'       => $monthShortName,
                    'alias'      => $monthAlias,
                ];

                $paymentSubQuery->addSelect(DB::raw("SUM(CASE WHEN payable_month = {$monthNumber} THEN due_amount ELSE 0 END) as {$monthAlias}"));
            }

            $monthList = implode(',', $selectedMonths);
            $paymentSubQuery->addSelect(DB::raw("SUM(CASE WHEN payable_month IN ({$monthList}) THEN due_amount ELSE 0 END) as total_due"))
                            ->where('payable_year', $collectionYear)
                            ->groupBy('student_id');

            $reportQuery = Student::select(
                                    'students.student_id_serial',
                                    'students.full_name',
                                    'students.father_mobile',
                                    'units.name as unit_name',
                                    'branches.name as branch_name',
                                    DB::raw("COALESCE(tsa_classes.name, vhs_classes.name) as class_name"),
                                    'payment_due.*'
                                )
                                ->leftJoinSub($paymentSubQuery, 'payment_due', function ($join) {
                                    $join->on('payment_due.student_id', '=', 'students.id');
                                })
                                ->leftJoin('units', 'units.id', '=', 'students.unit_id')
                                ->leftJoin('branches', 'branches.id', '=', 'students.branch_id')
                                ->leftJoin('classes as tsa_classes', 'tsa_classes.id', '=', 'students.tsa_class_id')
                                ->leftJoin('classes as vhs_classes', 'vhs_classes.id', '=', 'students.vhs_class_id')
                                ->where('students.status', '!=', 3)
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
                'rows'               => $reportRows,
                'month_columns'      => $monthColumns,
                'collection_year'    => $collectionYear,
                'unit_name'          => $unitName,
                'branch_name'        => $branchName,
                'class_name'         => $className,
                'generated_at'       => date('d-m-Y h:i A'),
            ];

            $fileName = 'due-student-report-' . $collectionYear . '-' . date('YmdHis') . '.xls';
            $html = view('front.pages.student.fees-due-report-excel', $reportData)->render();

            return response("\xEF\xBB\xBF" . $html)
                    ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
                    ->header('Content-Disposition', 'attachment; filename=' . $fileName);
        }
        public function updateFeesCollection(Request $request){
            $validator = Validator::make($request->all(), [
                'student_id'     => 'required|integer',
                'payable_month'  => 'required|integer|min:1|max:12',
                'payable_year'   => 'required|integer|min:2000|max:2100',
                'payment_amount' => 'required|numeric|gt:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $student = Student::select('id', 'full_name')
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

            DB::transaction(function () use ($studentPayment, $newPaidAmount, $newDueAmount, $updatedBy, $student, $monthName, $request, $transactionAmount) {
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
                    'type'                   => 'INCOME',
                    'transaction_timestamp'  => Carbon::now(),
                    'transaction_amount'     => $transactionAmount,
                    'particulars'            => 'Fees collection for '.$student->full_name.' '.$monthName.' '.$request->payable_year.' with amount '.$transactionAmount,
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
            $students                   = Student::select('id', 'unit_id', 'branch_id', 'session_id', 'monthly_fees')->get();
            if($students){
                foreach($students as $student){
                    for($month=1; $month<=12; $month++){
                        $fields = [
                            'student_id'        => $student->id,
                            'unit_id'           => $student->unit_id,
                            'branch_id'         => $student->branch_id,
                            'branch_id'         => $student->session_id,
                            'payable_month'     => $month,
                            'payable_year'      => date('Y'),
                            'payable_amount'    => $student->monthly_fees,
                            'due_amount'        => $student->monthly_fees,
                        ];
                        // Helper::pr($fields,0);
                        StudentPayment::insert($fields);
                    }
                }
            }
            // die;
            echo 'Student payment schedule created';
        }
    /* fees collection */
}
