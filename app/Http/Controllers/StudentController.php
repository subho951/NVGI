<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Services\SiteAuthService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File;

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

use App\Helpers\Helper;
use Auth;
use DB;
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
                                                                'users.first_name',
                                                                'users.last_name',
                                                                DB::raw("COALESCE(tsa_classes.name, vhs_classes.name) as class_name")
                                                            )
                                                            ->leftJoin('units', 'units.id', '=', 'students.unit_id')
                                                            ->leftJoin('branches', 'branches.id', '=', 'students.branch_id')
                                                            ->leftJoin('classes as tsa_classes', 'tsa_classes.id', '=', 'students.tsa_class_id')
                                                            ->leftJoin('classes as vhs_classes', 'vhs_classes.id', '=', 'students.vhs_class_id')
                                                            ->leftJoin('users', 'users.id', '=', 'students.created_by')
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
                    $tsa_subjects = array();
                } else {
                    $tsa_subjects = (($request->tsa_subjects != '')?json_encode($request->tsa_subjects):[]);
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
                    $tsa_subjects = array();
                } else {
                    $tsa_subjects = (($request->tsa_subjects != '')?json_encode($request->tsa_subjects):[]);
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
}
