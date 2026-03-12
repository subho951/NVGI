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
use App\Models\Module;
use App\Models\Role;

use App\Helpers\Helper;
use Auth;
use DB;
use Hash;
use Dompdf\Dompdf;
use Dompdf\Options;
use DateTime;

class RoleController extends Controller
{
    protected $siteAuthService;
    protected $data;
    public function __construct()
    {        
        $this->data = array(
            'title'             => 'Role',
            'controller'        => 'RoleController',
            'controller_route'  => 'role',
            'primary_key'       => 'id',
        );
        $this->siteAuthService = new SiteAuthService();
    }
    /* list */
        public function list(Request $request){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' List';
            $page_name                      = 'role.list';
            $data['rows']                   = Role::where('status', '!=', 3)->orderBy('id', 'DESC')->get();
            
            $data = $this->siteAuthService->admin_after_login_layout($title, $page_name, $data);
            return view('front.pages.' . $page_name, $data);
        }
    /* list */
    /* add */
        public function add(Request $request){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' Update';
            $page_name                      = 'role.add-edit';
            $data['row']                    = [];
            $generalSetting                 = GeneralSetting::find('1');
            $data['action']                 = 'Add';

            $data['modules']                = Module::select('id', 'name')->where('status', '=', 1)->orderBy('id', 'ASC')->get();

            if($request->isMethod('post')){
                $request->validate([
                    'name'                   => 'required',
                    'module_id'              => 'required',
                ]);

                $postData               = $request->all();
                if(array_key_exists("module_id",$postData)){
                    $module_id = (($request->module_id != '')?json_encode($request->module_id):[]);
                } else {
                    $module_id = json_encode(array());
                }

                $fields = [
                    'name'              => $request->name,
                    'module_id'         => $module_id,
                ];
                // Helper::pr($fields);
                Role::create($fields);
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
            $page_name                      = 'role.add-edit';
            $data['row']                    = Role::where($this->data['primary_key'], '=', $id)->first();
            // Helper::pr($data['row']);
            $generalSetting                 = GeneralSetting::find('1');
            $data['action']                 = 'Edit';

            $data['modules']                = Module::select('id', 'name')->where('status', '=', 1)->orderBy('id', 'ASC')->get();

            if($request->isMethod('post')){
                $member = Role::findOrFail($id);                

                $request->validate([
                    'name'                   => 'required',
                    'module_id'              => 'required',
                ]);
                
                $postData               = $request->all();
                if(array_key_exists("module_id",$postData)){
                    $module_id = (($request->module_id != '')?json_encode($request->module_id):[]);
                } else {
                    $module_id = json_encode(array());
                }

                $member->update([
                    'name'              => $request->name,
                    'module_id'         => $module_id,
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
            Role::where($this->data['primary_key'], '=', $id)->update($fields);
            return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' deleted successfully !!!');
        }
    /* delete */
    /* change status */
        public function change_status(Request $request, $id){
            $id                             = Helper::decoded($id);
            $model                          = Role::find($id);
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
