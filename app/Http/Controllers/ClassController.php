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
use App\Models\ClassSubject;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\Unit;

use App\Helpers\Helper;
use Auth;
use Session;
use Hash;
use Illuminate\Support\Facades\DB;

class ClassController extends Controller
{
    protected $siteAuthService;
    protected $data;
    public function __construct()
    {        
        $this->data = array(
            'title'             => 'Class',
            'controller'        => 'ClassController',
            'controller_route'  => 'class',
            'primary_key'       => 'id',
        );
        $this->siteAuthService = new SiteAuthService();
    }

    private function getRows()
    {
        return Classes::with(['subjectLinks.subject'])
                        ->select('classes.*', 'units.name as unit_name')
                        ->join('units', 'units.id', '=', 'classes.unit_id')
                        ->where('classes.status', '!=', 3)
                        ->orderBy('classes.id', 'DESC')
                        ->get();
    }

    private function getSubjectOptions()
    {
        return Subject::select('id', 'name')
                        ->where('status', '=', 1)
                        ->whereNull('deleted_at')
                        ->orderBy('name', 'ASC')
                        ->get();
    }

    private function validatePayload(Request $request): void
    {
        $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'unit_id'         => ['required', 'integer', Rule::exists('units', 'id')->where(function ($query) {
                $query->where('status', '=', 1)->whereNull('deleted_at');
            })],
            'subject_ids'     => ['required', 'array', 'min:1'],
            'subject_ids.*'   => ['required', 'integer', Rule::exists('subjects', 'id')->where(function ($query) {
                $query->where('status', '=', 1)->whereNull('deleted_at');
            })],
        ]);
    }

    private function syncClassSubjects(Classes $class, array $subjectIds): void
    {
        $subjectIds = array_values(array_unique(array_filter(array_map('intval', $subjectIds))));
        $now = date('Y-m-d H:i:s');

        DB::table('class_subjects')->where('class_id', '=', $class->id)->delete();

        foreach ($subjectIds as $subjectId) {
            ClassSubject::create([
                'unit_id'    => (int) $class->unit_id,
                'class_id'   => (int) $class->id,
                'subject_id' => (int) $subjectId,
                'status'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /* list */
        public function list(Request $request){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' List';
            $page_name                      = 'class.list';
            $data['rows']                   = $this->getRows();
            $data['action']                 = 'Add';
            $data['units']                  = Unit::select('id', 'name')->where('status', '=', 1)->orderBy('id', 'ASC')->get();
            $data['subjects']               = $this->getSubjectOptions();
            $data['selected_subject_ids']   = old('subject_ids', []);

            if($request->isMethod('post')){
                $this->validatePayload($request);

                DB::transaction(function () use ($request) {
                    $class = Classes::create([
                        'unit_id'       => $request->unit_id,
                        'name'          => $request->name,
                    ]);

                    $this->syncClassSubjects($class, (array) $request->input('subject_ids', []));
                });

                return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' added successfully !!!');
            }

            $data = $this->siteAuthService->admin_after_login_layout($title, $page_name, $data);
            return view('front.pages.' . $page_name, $data);
        }
    /* list */
    /* edit */
        public function edit(Request $request, $id){
            $data['module']                 = $this->data;
            $id                             = Helper::decoded($id);
            $title                          = $this->data['title'].' Update';
            $page_name                      = 'class.edit';
            $data['single_row']             = Classes::with(['subjectLinks.subject'])->where($this->data['primary_key'], '=', $id)->first();
            $generalSetting                 = GeneralSetting::find('1');
            $data['action']                 = 'Edit';
            $data['units']                  = Unit::select('id', 'name')->where('status', '=', 1)->orderBy('id', 'ASC')->get();
            $data['subjects']               = $this->getSubjectOptions();
            $data['selected_subject_ids']   = old('subject_ids', (($data['single_row'] && $data['single_row']->subjectLinks) ? $data['single_row']->subjectLinks->pluck('subject_id')->map(function ($id) {
                                                    return (string) $id;
                                                })->values()->all() : []));

            if($request->isMethod('post')){
                $member = Classes::findOrFail($id);

                $this->validatePayload($request);

                DB::transaction(function () use ($request, $member) {
                    $member->update([
                        'unit_id'       => $request->unit_id,
                        'name'          => $request->name,
                    ]);

                    $this->syncClassSubjects($member, (array) $request->input('subject_ids', []));
                });

                return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' updated successfully !!!');
            }

            $data['rows']                   = $this->getRows();
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
            Classes::where($this->data['primary_key'], '=', $id)->update($fields);
            return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' deleted successfully !!!');
        }
    /* delete */
    /* change status */
        public function change_status(Request $request, $id){
            $id                             = Helper::decoded($id);
            $model                          = Classes::find($id);
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
}
