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
use App\Models\Lead;
use App\Models\SalesPerson;

use App\Helpers\Helper;
use Auth;
use DB;
use Hash;
use Dompdf\Dompdf;
use Dompdf\Options;
use DateTime;

class LeadController extends Controller
{
    protected $siteAuthService;
    protected $data;
    public function __construct()
    {        
        $this->data = array(
            'title'             => 'Leads',
            'controller'        => 'LeadController',
            'controller_route'  => 'lead',
            'primary_key'       => 'id',
        );
        $this->siteAuthService = new SiteAuthService();
    }
    /* list */
        public function list(Request $request){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' List';
            $page_name                      = 'lead.list';
            $data['rows']                   = Lead::select(
                                                        'leads.*',
                                                        'sales_persons.name as sales_person_name'
                                                    )
                                                    ->leftJoin('sales_persons', 'sales_persons.id', '=', 'leads.sales_person_id')
                                                    ->where('leads.status', '!=', 3)
                                                    ->orderBy('leads.id', 'DESC')
                                                    ->get();
            
            $data = $this->siteAuthService->admin_after_login_layout($title, $page_name, $data);
            return view('front.pages.' . $page_name, $data);
        }
    /* list */
    /* add */
        public function add(Request $request){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' Update';
            $page_name                      = 'lead.add-edit';
            $data['row']                    = [];
            $generalSetting                 = GeneralSetting::find('1');
            $data['action']                 = 'Add';

            $data['salesPersons']           = SalesPerson::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();

            if($request->isMethod('post')){
                $request->validate([
                    'sales_person_id'       => 'required|integer',
                    'student_name'          => 'required',
                    'guardian_name'         => 'required',
                    'phone'                 => 'required|integer',
                    'remarks1'              => 'required',
                ]);

                $postData               = $request->all();

                $getLastLead = Lead::orderBy('id', 'DESC')->first();
                if($getLastLead){
                    $sl_no              = $getLastLead->sl_no;
                    $next_sl_no         = $sl_no + 1;
                    $next_sl_no_string  = str_pad($next_sl_no, 7, 0, STR_PAD_LEFT);
                    $lead_no            = 'NVGI-L-'.$next_sl_no_string;
                } else {
                    $next_sl_no         = 1;
                    $next_sl_no_string  = str_pad($next_sl_no, 7, 0, STR_PAD_LEFT);
                    $lead_no            = 'NVGI-L-'.$next_sl_no_string;
                }

                $fields = [
                    'sl_no'                 => $next_sl_no,
                    'lead_no'               => $lead_no,
                    'sales_person_id'       => $request->sales_person_id,
                    'student_name'          => $request->student_name,
                    'guardian_name'         => $request->guardian_name,
                    'phone'                 => $request->phone,
                    'remarks1'              => $request->remarks1,
                    'remarks2'              => $request->remarks2,
                    'remarks3'              => $request->remarks3,
                    'remarks4'              => $request->remarks4,
                    'remarks5'              => $request->remarks5,
                    'created_by'            => session('user_data')['user_id'],
                    'updated_by'            => session('user_data')['user_id'],
                ];
                // Helper::pr($fields);
                Lead::create($fields);
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
            $page_name                      = 'lead.add-edit';
            $data['row']                    = Lead::where($this->data['primary_key'], '=', $id)->first();
            // Helper::pr($data['row']);
            $generalSetting                 = GeneralSetting::find('1');
            $data['action']                 = 'Edit';

            $data['salesPersons']           = SalesPerson::select('id', 'name')->where('status', '=', 1)->orderBy('name', 'ASC')->get();

            if($request->isMethod('post')){
                $member = Lead::findOrFail($id);                

                $request->validate([
                    'sales_person_id'       => 'required|integer',
                    'student_name'          => 'required',
                    'guardian_name'         => 'required',
                    'phone'                 => 'required|integer',
                    'remarks1'              => 'required',
                ]);
                
                $postData               = $request->all();

                $member->update([
                    'sales_person_id'       => $request->sales_person_id,
                    'student_name'          => $request->student_name,
                    'guardian_name'         => $request->guardian_name,
                    'phone'                 => $request->phone,
                    'remarks1'              => $request->remarks1,
                    'remarks2'              => $request->remarks2,
                    'remarks3'              => $request->remarks3,
                    'remarks4'              => $request->remarks4,
                    'remarks5'              => $request->remarks5,
                    'created_by'            => session('user_data')['user_id'],
                    'updated_by'            => session('user_data')['user_id'],
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
            Lead::where($this->data['primary_key'], '=', $id)->update($fields);
            return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' deleted successfully !!!');
        }
    /* delete */
    /* change status */
        public function change_status(Request $request, $id){
            $id                             = Helper::decoded($id);
            $model                          = Lead::find($id);
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
