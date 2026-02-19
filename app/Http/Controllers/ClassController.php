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
use App\Models\Classes;
use App\Models\Unit;

use App\Helpers\Helper;
use Auth;
use Session;
use Hash;

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
    /* list */
        public function list(Request $request){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' List';
            $page_name                      = 'class.list';
            $data['rows']                   = Classes::select('classes.*', 'units.name as unit_name')
                                                    ->join('units', 'units.id', '=', 'classes.unit_id')
                                                    ->where('classes.status', '!=', 3)
                                                    ->orderBy('classes.id', 'DESC')
                                                    ->get();
            $data['action']                 = 'Add';
            $data['units']                  = Unit::select('id', 'name')->where('status', '=', 1)->orderBy('id', 'ASC')->get();

            if($request->isMethod('post')){
                $request->validate([
                    'name'         => 'required|string|max:255',
                    'unit_id'           => 'required|integer',
                ]);

                Classes::create([
                    'unit_id'       => $request->unit_id,
                    'name'          => $request->name,
                ]);

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
            $data['single_row']             = Classes::where($this->data['primary_key'], '=', $id)->first();
            $generalSetting                 = GeneralSetting::find('1');
            $data['action']                 = 'Edit';
            $data['units']                  = Unit::select('id', 'name')->where('status', '=', 1)->orderBy('id', 'ASC')->get();

            if($request->isMethod('post')){
                $member = Classes::findOrFail($id);

                $request->validate([
                    'name'              => 'required|string|max:255',
                    'unit_id'           => 'required|integer',
                ]);

                $member->update([
                    'unit_id'       => $request->unit_id,
                    'name'          => $request->name,
                ]);

                return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' updated successfully !!!');
            }

            $data['rows']                   = Classes::select('classes.*', 'units.name as unit_name')
                                                    ->join('units', 'units.id', '=', 'classes.unit_id')
                                                    ->where('classes.status', '!=', 3)
                                                    ->orderBy('classes.id', 'DESC')
                                                    ->get();
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
