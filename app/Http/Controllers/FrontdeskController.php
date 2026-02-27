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
use App\Models\Unit;

use App\Helpers\Helper;
use Auth;
use Session;
use Hash;

class FrontdeskController extends Controller
{
    protected $siteAuthService;
    protected $data;
    public function __construct()
    {        
        $this->data = array(
            'title'             => 'Front Desk',
            'controller'        => 'FrontdeskController',
            'controller_route'  => 'front-desk',
            'primary_key'       => 'id',
        );
        $this->siteAuthService = new SiteAuthService();
    }
    /* list */
        public function list(Request $request){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' List';
            $page_name                      = 'front-desk.list';
            $data['rows']                   = User::where('status', '!=', 3)->where('role_id', '=', 2)->orderBy('id', 'DESC')->get();
            $data['action']                 = 'Add';

            if($request->isMethod('post')){
                $request->validate([
                    'first_name'            => 'required|string|max:255',
                    'last_name'             => 'required|string|max:255',
                    // 'email'                 => 'required|email|max:255|unique:users,email',
                    'country_code'          => 'required',
                    'phone'                 => 'required|digits:10|unique:users,phone',
                    'password'              => 'required|string|max:255',
                ]);

                $serial_id = 'NVGI/' . $request->first_name . '/' . $request->phone;

                User::create([
                    'role_id'           => 2,
                    'serial_id'         => $serial_id,
                    'first_name'        => $request->first_name,
                    'middle_name'       => $request->middle_name,
                    'last_name'         => $request->last_name,
                    // 'email'             => $request->email,
                    'country_code'      => $request->country_code,
                    'phone'             => $request->phone,
                    'password'          => Hash::make($request->password),
                    'original_password' => $request->password,
                    'email_verified_at' => date('Y-m-d H:i:s'),
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
            $page_name                      = 'front-desk.edit';
            $data['single_row']             = User::where($this->data['primary_key'], '=', $id)->first();
            $generalSetting                 = GeneralSetting::find('1');
            $data['action']                 = 'Edit';

            if($request->isMethod('post')){
                $member = User::findOrFail($id);
                // Helper::pr($member);

                $request->validate([
                    'first_name'            => 'required|string|max:255',
                    'last_name'             => 'required|string|max:255',
                    // 'email'                 => 'required|email|max:255|unique:users,email,'.$member->id,
                    'country_code'          => 'required',
                    'phone'                 => 'required|digits:10|unique:users,phone,'.$member->id,
                ]);

                $serial_id = 'NVGI/' . $request->first_name . '/' . $request->phone;

                $fields = [
                        'serial_id'         => $serial_id,
                        'first_name'        => $request->first_name,
                        'middle_name'       => $request->middle_name,
                        'last_name'         => $request->last_name,
                        // 'email'             => $request->email,
                        'phone'             => $request->phone,
                        'password'          => Hash::make($request->password),
                        'original_password' => $request->password,
                    ];
                Helper::pr($fields);
                // if($request->password != ''){
                //     $member->update($fields);
                // } else {
                //     $member->update([
                //         'serial_id'         => $serial_id,
                //         'first_name'        => $request->first_name,
                //         'middle_name'       => $request->middle_name,
                //         'last_name'         => $request->last_name,
                //         // 'email'             => $request->email,
                //         'country_code'      => $request->country_code,
                //         'phone'             => $request->phone,
                //         'password'          => Hash::make($request->password),
                //         'original_password' => $request->password,
                //     ]);
                // }
                $member->update($fields);                

                return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' updated successfully !!!');
            }

            $data['rows']                   = User::where('status', '!=', 3)->where('role_id', '=', 2)->orderBy('id', 'DESC')->get();
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
            User::where($this->data['primary_key'], '=', $id)->update($fields);
            return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' deleted successfully !!!');
        }
    /* delete */
    /* change status */
        public function change_status(Request $request, $id){
            $id                             = Helper::decoded($id);
            $model                          = User::find($id);
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
