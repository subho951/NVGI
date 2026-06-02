<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Services\SiteAuthService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

use App\Models\GeneralSetting;
use App\Models\User;
use App\Models\Unit;
use App\Models\Branch;

use App\Helpers\Helper;
use Auth;
use Session;

class BranchController extends Controller
{
    protected $siteAuthService;
    protected $data;
    public function __construct()
    {        
        $this->data = array(
            'title'             => 'Branch',
            'controller'        => 'BranchController',
            'controller_route'  => 'branch',
            'primary_key'       => 'id',
        );
        $this->siteAuthService = new SiteAuthService();
    }
    /* list */
        public function list(Request $request){
            $data['module']                 = $this->data;
            $title                          = $this->data['title'].' List';
            $page_name                      = 'branch.list';
            $data['rows']                   = Branch::select('branches.*', 'units.name as unit_name')
                                                    ->join('units', 'units.id', '=', 'branches.unit_id')
                                                    ->where('branches.status', '!=', 3)
                                                    ->orderBy('branches.id', 'DESC')
                                                    ->get();
            $data['action']                 = 'Add';
            $data['units']                  = Unit::select('id', 'name')->where('status', '=', 1)->orderBy('id', 'ASC')->get();

            if($request->isMethod('post')){
                $request->validate([
                    'name'              => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique('branches', 'name')->where(function ($query) use ($request) {
                            return $query->where('unit_id', '=', (int) $request->unit_id)
                                ->whereNull('deleted_at');
                        }),
                    ],
                    'unit_id'           => [
                        'required',
                        'integer',
                        Rule::exists('units', 'id')->where(function ($query) {
                            return $query->where('status', '=', 1);
                        }),
                    ],
                    'password'          => 'required|string|min:8|max:72',
                ]);

                Branch::create([
                    'unit_id'           => $request->unit_id,
                    'serial_id'         => $this->createUniqueSerialId($request->name),
                    'name'              => $request->name,
                    'password'          => Hash::make($request->password),
                    'original_password' => Crypt::encryptString($request->password),
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
            $page_name                      = 'branch.edit';
            $data['single_row']             = Branch::where($this->data['primary_key'], '=', $id)->first();
            $generalSetting                 = GeneralSetting::find('1');
            $data['action']                 = 'Edit';
            $data['units']                  = Unit::select('id', 'name')->where('status', '=', 1)->orderBy('id', 'ASC')->get();

            if($request->isMethod('post')){
                $member = Branch::findOrFail($id);

                $request->validate([
                    'name'              => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique('branches', 'name')->where(function ($query) use ($request) {
                            return $query->where('unit_id', '=', (int) $request->unit_id)
                                ->whereNull('deleted_at');
                        })->ignore($member->id),
                    ],
                    'unit_id'           => [
                        'required',
                        'integer',
                        Rule::exists('units', 'id')->where(function ($query) {
                            return $query->where('status', '=', 1);
                        }),
                    ],
                    'password'          => 'nullable|string|min:8|max:72',
                ]);

                $fields = [
                    'unit_id'           => $request->unit_id,
                    'serial_id'         => $this->createUniqueSerialId($request->name, $member->id),
                    'name'              => $request->name,
                ];

                if ($request->filled('password')) {
                    $fields['password'] = Hash::make($request->password);
                    $fields['original_password'] = Crypt::encryptString($request->password);
                }

                $member->update($fields);

                return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' updated successfully !!!');
            }

            $data['rows']                   = Branch::select('branches.*', 'units.name as unit_name')
                                                    ->join('units', 'units.id', '=', 'branches.unit_id')
                                                    ->where('branches.status', '!=', 3)
                                                    ->orderBy('branches.id', 'DESC')
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
            Branch::where($this->data['primary_key'], '=', $id)->update($fields);
            return redirect($this->data['controller_route'] . "/list")->with('success_message', $this->data['title'].' deleted successfully !!!');
        }
    /* delete */
    /* change status */
        public function change_status(Request $request, $id){
            $id                             = Helper::decoded($id);
            $model                          = Branch::find($id);
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

    private function createUniqueSerialId($branchName, $ignoreBranchId = null): string
    {
        $serialBase = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', trim((string) $branchName)));
        $serialBase = substr($serialBase !== '' ? $serialBase : 'BRANCH', 0, 3);
        $serialId = $serialBase;
        $suffix = 2;

        while ($this->serialIdExists($serialId, $ignoreBranchId)) {
            $serialId = $serialBase . '-' . $suffix;
            $suffix++;
        }

        return $serialId;
    }

    private function serialIdExists(string $serialId, $ignoreBranchId = null): bool
    {
        $query = Branch::whereRaw('LOWER(serial_id) = ?', [strtolower($serialId)])
                    ->where('status', '!=', 3);

        if ($ignoreBranchId !== null) {
            $query->where('id', '!=', (int) $ignoreBranchId);
        }

        return $query->exists();
    }
}
