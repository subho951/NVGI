<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Services\SiteAuthService;
use App\Helpers\Helper;

use App\Models\GeneralSetting;
use App\Models\Member;
use App\Models\ConferencePackage;
use App\Models\MemberCategory;
use App\Models\PackageCategory;
use App\Models\User;
use App\Models\Submission;
use App\Models\SubmissionAssociate;

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use Auth;
use Session;
use Hash;
use DB;
use PHPUnit\TextUI\Help;

class FrontendController extends Controller
{
    protected $siteAuthService;
    protected $data;
    public function __construct()
    {
        $this->siteAuthService = new SiteAuthService();
    }
    /* home */
        public function index(Request $request){
            if($request->isMethod('post')){
                $rules = [                    
                    'email' => 'required|email'
                ];
                if($this->validate($request, $rules)){
                    $otp = rand(100000,999999);
                    $fields = [
                        'email'                  => $request->email,
                        'otp'                    => $otp,
                    ];
                    $checkMember = Submission::where('email', '=', $request->email)->first();
                    if($checkMember){
                        Submission::where('email', '=', $request->email)->update($fields);
                        $submission_id              = $checkMember->id;
                        $email                      = $checkMember->id;
                        $total_payable_amount       = $checkMember->total_payable_amount;
                        $status                     = $checkMember->status;
                        
                        // if($email != '' && $total_payable_amount <= 0  && $status == 0){
                        //     Submission::where('email', '=', $request->email)->update(['otp' => 0]);
                        //     return redirect(url('/') . "/member-info/" . Helper::encoded($submission_id))->with('success_message', 'Please update member info !!!');
                        // }
                        // if($email != '' && $total_payable_amount > 0  && $status == 0){
                        //     Submission::where('email', '=', $request->email)->update(['otp' => 0]);
                        //     return redirect(url('/') . "/checkout/" . Helper::encoded($submission_id))->with('success_message', 'Please complete the payment !!!');
                        // }
                        if($email != '' && $total_payable_amount > 0  && $status == 1){
                            Submission::where('email', '=', $request->email)->update(['otp' => 0]);
                            return redirect(url('/') . "/thankyou/" . Helper::encoded($submission_id))->with('success_message', 'Payment already completed !!!');
                        }
                    } else {
                        $submission_id = Submission::insertGetId($fields);
                    }

                    /* otp mail */
                        $to = $request->email;
                        $subject = "Your " . Helper::getSettingValue('site_name2') . " OTP";
                        $message = "
                                    Dear Doctor,<br><br>
                                    Thank you for accessing the " . Helper::getSettingValue('site_name2') . " Portal.<br>
                                    Please enter the following OTP on the page to proceed.<br><br>
                                    <b>OTP: $otp</b><br><br>
                                    This OTP is only valid for the next 5 minutes. If you did not request this OTP, please inform us immediately by contacting our support team at <a href=\"mailto:support@oswb.org\">support@oswb.org</a>.<br><br>
                                    Best regards,<br>
                                    OSWB";
                        $this->sendMail($to,$subject,$message);
                    /* otp mail */
                    
                    return redirect(url('/') . "/verify-otp/" . Helper::encoded($submission_id))->with('success_message', 'Please enter OTP sent to your entered email !!!');
                } else {
                    return redirect()->back()->with('error_message', 'All Fields Required !!!');
                }
            }

            $title                          = 'Home';
            $page_name                      = 'index';
            $data['row']                    = [];
            $data                           = $this->siteAuthService ->registration_layout($title,$page_name,$data);
            return view('front.pages.' . $page_name, $data);
        }
        public function checkMemberEmail(Request $request)
        {
            $email = $request->email;
            $exists = Member::where('Email', $email)->exists();

            return response()->json(['exists' => $exists]);
        }
    /* home */
    /* validate otp */
        public function verifyOTP(Request $request, $submission_id){
            $submission_id                              = Helper::decoded($submission_id);
            $data['submission_id']                      = $submission_id;
            $getSubmission                              = Submission::where('id', '=', $submission_id)->first();

            if($request->isMethod('post')){
                $rules = [                    
                    'otp1' => 'required',
                    'otp2' => 'required',
                    'otp3' => 'required',
                    'otp4' => 'required',
                    'otp5' => 'required',
                    'otp6' => 'required',
                ];
                if($this->validate($request, $rules)){
                    if($getSubmission){
                        $otp = $getSubmission->otp;
                        $enter_otp = $request->otp1.$request->otp2.$request->otp3.$request->otp4.$request->otp5.$request->otp6;
                        if($otp == $enter_otp){
                            Submission::where('id', '=', $submission_id)->update(['otp' => 0]);

                            $current_date = date('Y-m-d');
                            // $current_date = '2025-10-02';
                            $getPackageCategory = PackageCategory::select('id')->where('status', '=', 1)->where('start_date', '<=', $current_date)->where('end_date', '>=', $current_date)->first();
                            if($getPackageCategory){
                                $memberInfo         = [];
                                $package_id         = 0;
                                $payable_amount     = 0;
                                $checkMember = Member::where('Email', '=', $getSubmission->email)->first();
                                if($checkMember){
                                    $member_id          = $checkMember->id;
                                    $First_Name         = $checkMember->First_Name;
                                    $Middle_Name        = $checkMember->Middle_Name;
                                    $Last_Name          = $checkMember->Last_Name;
                                    $Email              = $checkMember->Email;
                                    $Mobile_No          = $checkMember->Mobile_No;
                                    $DOB                = $checkMember->DOB;
                                    $Category           = $checkMember->Category;
                                    $OSWB_No2           = $checkMember->OSWB_No2;

                                    $age = 0;
                                    $dob = $DOB;
                                    if($DOB != ''){
                                        $dob = date_format(date_create($DOB), "Y-m-d"); // your date of birth
                                        $age = Carbon::parse($dob)->age;
                                    }
                                    $is_food_option         = 1;

                                    /* payment calculation */
                                        if($OSWB_No2 <= 1823 & $Category != 'PGT'){
                                            if($age < 75){
                                                if($getPackageCategory->id == 1){
                                                    $package_id             = 1;
                                                } elseif($getPackageCategory->id == 2){
                                                    $package_id             = 10;
                                                } elseif($getPackageCategory->id == 3){
                                                    $package_id             = 19;
                                                }
                                                
                                                $getConferencePackage   = ConferencePackage::select('package_amount')->where('id', '=', $package_id)->first();
                                                $payable_amount         = (($getConferencePackage)?$getConferencePackage->package_amount:0);
                                                $is_have_associate      = 1;
                                                $dob                    = $dob;
                                                $age                    = $age;
                                            } else {
                                                if($getPackageCategory->id == 1){
                                                    $package_id             = 4;
                                                } elseif($getPackageCategory->id == 2){
                                                    $package_id             = 13;
                                                } elseif($getPackageCategory->id == 3){
                                                    $package_id             = 22;
                                                }
                                                
                                                $getConferencePackage   = ConferencePackage::select('package_amount')->where('id', '=', $package_id)->first();
                                                $payable_amount         = (($getConferencePackage)?$getConferencePackage->package_amount:0);
                                                $is_have_associate      = 1;
                                                $dob                    = $dob;
                                                $age                    = $age;
                                            }
                                        }
                                        if($OSWB_No2 > 1823 & $Category != 'PGT'){
                                            if($getPackageCategory->id == 1){
                                                $package_id             = 2;
                                            } elseif($getPackageCategory->id == 2){
                                                $package_id             = 11;
                                            } elseif($getPackageCategory->id == 3){
                                                $package_id             = 20;
                                            }
                                            
                                            $getConferencePackage   = ConferencePackage::select('package_amount')->where('id', '=', $package_id)->first();
                                            $payable_amount         = (($getConferencePackage)?$getConferencePackage->package_amount:0);
                                            $is_have_associate      = 1;
                                            $dob                    = $dob;
                                            $age                    = $age;
                                        }
                                        if($OSWB_No2 <= 1823 & $Category == 'PGT'){
                                            if($getPackageCategory->id == 1){
                                                $package_id             = 6;
                                            } elseif($getPackageCategory->id == 2){
                                                $package_id             = 15;
                                            } elseif($getPackageCategory->id == 3){
                                                $package_id             = 24;
                                            }
                                            
                                            $getConferencePackage   = ConferencePackage::select('package_amount')->where('id', '=', $package_id)->first();
                                            $payable_amount         = (($getConferencePackage)?$getConferencePackage->package_amount:0);
                                            $is_have_associate      = 0;
                                            $dob                    = $dob;
                                            $age                    = $age;
                                        }
                                        if($OSWB_No2 > 1823 & $Category == 'PGT'){
                                            if($getPackageCategory->id == 1){
                                                $package_id             = 7;
                                            } elseif($getPackageCategory->id == 2){
                                                $package_id             = 16;
                                            } elseif($getPackageCategory->id == 3){
                                                $package_id             = 25;
                                            }
                                            
                                            $getConferencePackage   = ConferencePackage::select('package_amount')->where('id', '=', $package_id)->first();
                                            $payable_amount         = (($getConferencePackage)?$getConferencePackage->package_amount:0);
                                            $is_have_associate      = 0;
                                            $dob                    = $dob;
                                            $age                    = $age;
                                        }
                                    /* payment calculation */

                                    $memberInfo = [
                                        'member_id'         => Helper::encoded($member_id),
                                        'first_name'        => $First_Name,
                                        'middle_name'       => $Middle_Name,
                                        'last_name'         => $Last_Name,
                                        'email'             => $Email,
                                        'phone'             => $Mobile_No,
                                        'dob'               => $DOB,
                                        'category'          => $Category,
                                        'package_id'        => $package_id,
                                        'payable_amount'    => $payable_amount,
                                        'is_have_associate' => $is_have_associate,
                                        'dob'               => $dob,
                                        'age'               => $age,
                                        'is_food_option'    => $is_food_option,
                                    ];
                                } else {
                                    if($getPackageCategory->id == 1){
                                        $package_id             = 8;
                                    } elseif($getPackageCategory->id == 2){
                                        $package_id             = 17;
                                    } elseif($getPackageCategory->id == 3){
                                        $package_id             = 26;
                                    }
                                    
                                    $getConferencePackage   = ConferencePackage::select('package_amount', 'name')->where('id', '=', $package_id)->first();
                                    $payable_amount         = (($getConferencePackage)?$getConferencePackage->package_amount:0);
                                    $is_have_associate      = 0;

                                    $dob = '';
                                    $age = 0;
                                    $is_food_option         = 1;

                                    $memberInfo = [
                                        'member_id'         => Helper::encoded(0),
                                        'first_name'        => '',
                                        'middle_name'       => '',
                                        'last_name'         => '',
                                        'email'             => $getSubmission->email,
                                        'phone'             => '',
                                        'dob'               => '',
                                        'category'          => (($getConferencePackage)?$getConferencePackage->name:''),
                                        'package_id'        => $package_id,
                                        'payable_amount'    => $payable_amount,
                                        'is_have_associate' => $is_have_associate,
                                        'dob'               => $dob,
                                        'age'               => $age,
                                        'is_food_option'    => $is_food_option,
                                    ];
                                }
                                // Helper::pr($memberInfo);
                                session($memberInfo);
                                return redirect(url('/') . "/member-info/" . Helper::encoded($submission_id))->with('success_message', 'OTP Verified Sucessfully !!!');
                            } else {
                                return redirect()->back()->with('error_message', 'Package category not found !!!');
                            }
                        } else {
                            return redirect()->back()->with('error_message', 'OTP mismatched !!!');
                        }
                    } else {
                        return redirect("/")->with('error_message', 'Submission not found !!!');
                    }
                } else {
                    return redirect()->back()->with('error_message', 'All Fields Required !!!');
                }
            }

            $title                          = 'Verify OTP';
            $page_name                      = 'verify-otp';
            $data['row']                    = [];
            $data                           = $this->siteAuthService ->registration_layout($title,$page_name,$data);
            return view('front.pages.' . $page_name, $data);
        }
    /* validate otp */
    /* resend otp */
        public function resendOTP(Request $request, $submission_id){
            $submission_id      = Helper::decoded($submission_id);
            $otp                = rand(100000,999999);
            Submission::where('id', '=', $submission_id)->update(['otp' => $otp]);
            $getSubmission      = Submission::where('id', '=', $submission_id)->first();

            /* otp mail */
                $to = (($getSubmission)?$getSubmission->email:'');
                $subject = "Your " . Helper::getSettingValue('site_name2') . " OTP";
                $message = "
                            Dear Doctor,<br><br>
                            Thank you for accessing the " . Helper::getSettingValue('site_name2') . " Portal.<br>
                            Please enter the following OTP on the page to proceed.<br><br>
                            <b>OTP: $otp</b><br><br>
                            This OTP is only valid for the next 5 minutes. If you did not request this OTP, please inform us immediately by contacting our support team at <a href=\"mailto:support@oswb.org\">support@oswb.org</a>.<br><br>
                            Best regards,<br>
                            OSWB";
                $this->sendMail($to,$subject,$message);
            /* otp mail */
            return redirect(url('/') . "/verify-otp/" . Helper::encoded($submission_id))->with('success_message', 'OTP resend to your entered email !!!');
        }
    /* resend otp */
    /* member info */
        public function memberInfo(Request $request, $submission_id){
            $submission_id                  = Helper::decoded($submission_id);
            $data['submission_id']          = $submission_id;
            $getSubmission                              = Submission::where('id', '=', $submission_id)->first();

            if($request->isMethod('post')){
                $postData               = $request->all();
                $total_payable_amount   = 0;

                
                $current_date = date('Y-m-d');
                // $current_date = '2025-10-02';
                $getPackageCategory = PackageCategory::select('id')->where('status', '=', 1)->where('start_date', '<=', $current_date)->where('end_date', '>=', $current_date)->first();
                if($getPackageCategory){
                    // food option
                        $DOB = $postData['dob'];
                        $age = 0;
                        $dob = $DOB;
                        if($DOB != ''){
                            $dob = date_format(date_create($DOB), "Y-m-d"); // your date of birth
                            $age = Carbon::parse($dob)->age;
                        }
                        $is_food_option         = 1;

                        $checkMember = Member::where('Email', '=', $getSubmission->email)->first();
                        if($checkMember){
                            $member_id          = $checkMember->id;
                            $First_Name         = $checkMember->First_Name;
                            $Middle_Name        = $checkMember->Middle_Name;
                            $Last_Name          = $checkMember->Last_Name;
                            $Email              = $checkMember->Email;
                            $Mobile_No          = $checkMember->Mobile_No;
                            $Category           = $checkMember->Category;
                            $OSWB_No2           = $checkMember->OSWB_No2;

                            if($request->is_food_option){
                                if($OSWB_No2 <= 1823 & $Category != 'PGT'){
                                    if($age < 75){
                                        if($getPackageCategory->id == 1){
                                            $pkg_id             = 1;
                                        } elseif($getPackageCategory->id == 2){
                                            $pkg_id             = 10;
                                        } elseif($getPackageCategory->id == 3){
                                            $pkg_id             = 19;
                                        }
                                    } else {
                                        if($getPackageCategory->id == 1){
                                            $pkg_id             = 4;
                                        } elseif($getPackageCategory->id == 2){
                                            $pkg_id             = 13;
                                        } elseif($getPackageCategory->id == 3){
                                            $pkg_id             = 22;
                                        }
                                    }
                                }
                                if($OSWB_No2 > 1823 & $Category != 'PGT'){
                                    if($getPackageCategory->id == 1){
                                        $pkg_id             = 2;
                                    } elseif($getPackageCategory->id == 2){
                                        $pkg_id             = 11;
                                    } elseif($getPackageCategory->id == 3){
                                        $pkg_id             = 20;
                                    }
                                }
                                if($OSWB_No2 <= 1823 & $Category == 'PGT'){
                                    if($getPackageCategory->id == 1){
                                        $pkg_id             = 6;
                                    } elseif($getPackageCategory->id == 2){
                                        $pkg_id             = 15;
                                    } elseif($getPackageCategory->id == 3){
                                        $pkg_id             = 24;
                                    }
                                }
                                if($OSWB_No2 > 1823 & $Category == 'PGT'){
                                    if($getPackageCategory->id == 1){
                                        $pkg_id             = 7;
                                    } elseif($getPackageCategory->id == 2){
                                        $pkg_id             = 16;
                                    } elseif($getPackageCategory->id == 3){
                                        $pkg_id             = 25;
                                    }
                                }
                                $is_food_option = $postData['is_food_option'];
                            } else {
                                if($getPackageCategory->id == 1){
                                    $pkg_id         = 9;
                                } elseif($getPackageCategory->id == 2){
                                    $pkg_id             = 18;
                                } elseif($getPackageCategory->id == 3){
                                    $pkg_id             = 27;
                                }
                                $is_food_option = $postData['is_food_option'];
                            }
                        } else {
                            if($request->is_food_option){
                                $pkg_id         = $request->package_id;
                                $is_food_option = $postData['is_food_option'];
                            } else {
                                if($getPackageCategory->id == 1){
                                    $pkg_id         = 9;
                                } elseif($getPackageCategory->id == 2){
                                    $pkg_id             = 18;
                                } elseif($getPackageCategory->id == 3){
                                    $pkg_id             = 27;
                                }
                                $is_food_option = $postData['is_food_option'];
                            }
                        }

                        $package_id = $pkg_id;
                        $getConferencePackage   = ConferencePackage::select('package_amount')->where('id', '=', $package_id)->first();
                        $payable_amount         = (($getConferencePackage)?$getConferencePackage->package_amount:0);

                        $total_payable_amount   += $payable_amount;
                    // food option
                    // associate
                        $associates         = [];
                        $associate_count    = 0;
                        $associate_amount   = 0;

                        if(array_key_exists("associate_name",$postData) && array_key_exists("associate_email",$postData) && array_key_exists("associate_phone",$postData) && array_key_exists("associate_age",$postData)){
                            $associate_count            = count($postData['associate_name']);
                            if(!empty($postData['associate_name'])){
                                for($k=0;$k<$associate_count;$k++){
                                    $associate_name     = $postData['associate_name'][$k];
                                    $associate_email    = $postData['associate_email'][$k];
                                    $associate_phone    = $postData['associate_phone'][$k];
                                    $associate_age      = $postData['associate_age'][$k];
                                    if($associate_age > 75){
                                        if($getPackageCategory->id == 1){
                                            $associate_package_id = 5;
                                        } elseif($getPackageCategory->id == 2){
                                            $associate_package_id = 14;
                                        } elseif($getPackageCategory->id == 3){
                                            $associate_package_id = 23;
                                        }
                                    } else {
                                        if($getPackageCategory->id == 1){
                                            $associate_package_id = 3;
                                        } elseif($getPackageCategory->id == 2){
                                            $associate_package_id = 12;
                                        } elseif($getPackageCategory->id == 3){
                                            $associate_package_id = 21;
                                        }
                                    }
                                    $getAssociateAmount         = ConferencePackage::select('package_amount')->where('id', '=', $associate_package_id)->first();
                                    if($request->is_food_option){
                                        $associate_package_amount   = (($getAssociateAmount)?$getAssociateAmount->package_amount:0);
                                    } else {
                                        $associate_package_amount   = $payable_amount;
                                    }

                                    $associates[] = [
                                        'associate_name'        => $associate_name,
                                        'associate_email'       => $associate_email,
                                        'associate_phone'       => $associate_phone,
                                        'associate_age'         => $associate_age,
                                        'associate_package_id'  => $associate_package_id,
                                        'associate_amount'      => $associate_package_amount,
                                    ];
                                    $associate_amount           += $associate_package_amount;
                                }
                            }
                        } else {
                            $associate_count            = 0;
                            $associate_amount           = 0;
                        }

                        $total_payable_amount   += $associate_amount;
                    // associate
                    // certificate
                        if(array_key_exists("is_certificate",$postData)){
                            $is_certificate         = 1;
                            $certificate_amount     = (350.00 + ((350.00 * 18) / 100));
                        } else {
                            $is_certificate         = 0;
                            $certificate_amount     = 0.00;
                        }

                        $total_payable_amount   += $certificate_amount;
                    // certificate
                    // dob proof
                        $dob_proof = '';
                        if(array_key_exists("dob_proof",$postData)){
                            $imageFile      = $request->file('dob_proof');
                            if($imageFile != ''){
                                $imageName      = $imageFile->getClientOriginalName();
                                $uploadedFile   = $this->upload_single_file('dob_proof', $imageName, 'member/reg_certificate/', 'custom');
                                if($uploadedFile['status']){
                                    $dob_proof = 'uploads/member/reg_certificate/' . $uploadedFile['newFilename'];
                                } else {
                                    return redirect()->back()->with(['error_message' => $uploadedFile['message']]);
                                }
                            }
                        }
                    // dob proof

                    $age = 0;
                    $dob = date_format(date_create($request->dob), "Y-m-d"); // your date of birth
                    $age = Carbon::parse($dob)->age;
                    
                    $fields = [
                        'member_id'             => $request->member_id,
                        'first_name'            => $request->first_name,
                        'middle_name'           => $request->middle_name,
                        'last_name'             => $request->last_name,
                        'email'                 => $request->email,
                        'phone'                 => $request->phone,
                        'dob'                   => $request->dob,
                        'dob_proof'             => $dob_proof,
                        'age'                   => $age,
                        'category'              => $request->category,
                        'package_id'            => $package_id,
                        'payable_amount'        => $payable_amount,
                        'is_food_option'        => $is_food_option,
                        'is_associate'          => (($associate_count > 0)?1:0),
                        'associate_count'       => $associate_count,
                        'associate_amount'      => $associate_amount,
                        'is_certificate'        => $is_certificate,
                        'certificate_amount'    => $certificate_amount,
                        'total_payable_amount'  => $total_payable_amount,
                    ];
                    // Helper::pr($fields);
                    Submission::where('id', '=', $request->submission_id)->update($fields);

                    if(!empty($associates)){
                        for($i=0;$i<count($associates);$i++){
                            $fields2 = [
                                'submission_id'         => $request->submission_id,
                                'associate_name'        => $associates[$i]['associate_name'],
                                'associate_email'       => $associates[$i]['associate_email'],
                                'associate_phone'       => $associates[$i]['associate_phone'],
                                'associate_age'         => $associates[$i]['associate_age'],
                                'associate_package_id'  => $associates[$i]['associate_package_id'],
                                'associate_amount'      => $associates[$i]['associate_amount'],
                            ];
                            SubmissionAssociate::insert($fields2);
                        }
                    }
                    $request->session()->flush();
                    return redirect(url('/') . "/checkout/" . Helper::encoded($request->submission_id))->with('success_message', 'Member Info Updated Successfully !!!');
                } else {
                    return redirect()->back()->with('error_message', 'Package category not found !!!');
                }
            }

            $title                          = 'Member Information';
            $page_name                      = 'member-info';
            $data['row']                    = [];
            $data                           = $this->siteAuthService ->registration_layout($title,$page_name,$data);
            return view('front.pages.' . $page_name, $data);
        }
    /* member info */
    /* checkout */
        public function checkout(Request $request, $submission_id){
            $submission_id                  = Helper::decoded($submission_id);
            $data['submission_id']          = $submission_id;
            $data['getSubmission']          = Submission::where('id', '=', $submission_id)->first();

            $title                          = 'Checkout';
            $page_name                      = 'checkout';
            $data['row']                    = [];
            $data                           = $this->siteAuthService ->registration_layout($title,$page_name,$data);
            return view('front.pages.' . $page_name, $data);
        }
    /* checkout */
    /* payment with Razorpay */
        public function razorpay(Request $request)
        {
            // ✅ Fetch member details from DB
            $memberId   = $request->member_id;
            $packageId  = $request->package_id;
            $amount     = $request->amount;
            $member     = Submission::where('id', $memberId)->first();

            $name       = (($member)?$member->first_name . ' ' . $member->last_name:'Guest') ?? 'Guest';
            $email      = (($member)?$member->email:'noemail@test.com') ?? 'noemail@test.com';
            $phone      = (($member)?$member->phone:'0000000000') ?? '0000000000';
            $api        = new Api(env('RAZORPAY_KEY_NEW'), env('RAZORPAY_SECRET_NEW'));

            $order = $api->order->create([
                'receipt' => 'INV_' . rand(10000, 99999),
                'amount' => $amount,
                'currency' => 'INR',
                'payment_capture' => 1,
                'notes' => [
                    'member_id'     => $memberId,
                    'package_id'    => $packageId,
                    'name'          => $name,
                    'email'         => $email,
                    'contact'       => $phone,
                ]
            ]);                
            return response()->json([            
                'order_id'      => $order['id'],
                'member_name'   => $name,
                'member_email'  => $email,
                'member_phone'  => $phone
            ]);        
        }
        public function razorpaycallback(Request $request)
        {
            $input = $request->all();
            $api = new Api(env('RAZORPAY_KEY_NEW'), env('RAZORPAY_SECRET_NEW'));

            try {
                if (!empty($input['razorpay_payment_id'])) {
                    // ✅ Fetch payment details from Razorpay
                    $payment = $api->payment->fetch($input['razorpay_payment_id']);
                                
                    // ✅ Verify payment signature
                    $attributes = [
                        'razorpay_order_id'   => $input['razorpay_order_id'],
                        'razorpay_payment_id' => $input['razorpay_payment_id'],
                        'razorpay_signature'  => $input['razorpay_signature'],
                    ];
                    $api->utility->verifyPaymentSignature($attributes);

                    // ✅ Update your DB (mark payment successful)
                    DB::table('payment_details')->insert([
                        'type'                  => 'CONFERENCE',
                        'member_id'             => $input['member_id'],
                        'transaction_id'        => $payment['id'],
                        'payment_amount'        => $input['payble_amount'],
                        'payment_mode'          => 'Razor Pay',
                        'payment_date'          => date('Y-m-d'),
                        'package_start_date'    => date('Y-m-d'),
                        'package_end_date'      => date('Y-m-d', strtotime('+365 days')),
                        'package_id'            => $input['package'],            
                        'status'                => 'Success',   
                        'response'              => json_encode($payment->toArray()),
                        'created_at'            => date('Y-m-d H:i:s'), 
                        'updated_at'            => date('Y-m-d H:i:s'),  
                    ]);
                    
                    $member             = Submission::where('id', $input['member_id'])->first();
                    $member_name        = (($member)?$member->first_name . ' ' . $member->last_name:'');
                    $getPackage         = ConferencePackage::where('id', $input['package'])->first();
                    $getMemberCategory  = MemberCategory::select('name')->where('id', '=', (($getPackage)?$getPackage->member_category_id:0))->first();
                    $getPackageCategory  = PackageCategory::select('name')->where('id', '=', (($getPackage)?$getPackage->package_category_id:0))->first();
                    $payment_amount     = $input['payble_amount'];
                    $transaction        = $payment['id'];
                    $payment_date = date('Y-m-d H:i:s');
                                    
                    // ✅ Mail to Doctor (Member)
                    $toDoctor = (($member)?$member->email:'');
                    $subjectDoctor = "Please find your registration details below:";
                    $messageDoctor = "
                        Dear $member_name,<br><br>
                        Thank you for your payment on the OSWB Conference Portal.<br>
                        Please find your registration details below: <br><br>
                        <b>Registration serial no.: " . (($member)?$member->id:0) . "</b><br>
                        <b>Registration as: " . (($getMemberCategory)?$getMemberCategory->name:'') . "</b><br>
                        <b>Ticket type: " . (($getPackageCategory)?$getPackageCategory->name:'') . "</b><br>
                        <b>Member Amount: " . number_format((($member)?$member->payable_amount:0),2) . "</b><br>
                        <b>Associate Amount: " . number_format((($member)?$member->associate_amount:''),2) . "</b><br>
                        <b>Certificate Amount: " . number_format((($member)?$member->certificate_amount:''),2) . "</b><br><br>
                        <b>Total Amount: " . number_format($payment_amount,2) . "</b><br>
                        <b>Transaction Id: $transaction</b><br><br>
                        <b>Payment Date: $payment_date</b><br>
                        <b>Payment Status: Success</b><br><br>
                        If you encounter any issues or require assistance, please contact our support team at 
                        <a href=\"mailto:support@oswb.org\">support@oswb.org</a>.<br><br>
                        Best regards,<br>
                        OSWB";
                    // echo $messageDoctor;die;
                    $this->sendMail($toDoctor, $subjectDoctor, $messageDoctor);


                    // ✅ Mail to Admin
                    $user = User::where('role_id', 1)->first();
                    $toAdmin = $user->email;  // or set a fixed admin email if needed
                    $subjectAdmin = "Please find registration details below:";
                    $messageAdmin = "
                        Dear Admin,<br><br>
                        Please find registration details below: <br><br>
                        <b>Registration serial no.: " . (($member)?$member->id:0) . "</b><br>
                        <b>Registration as: " . (($getMemberCategory)?$getMemberCategory->name:'') . "</b><br>
                        <b>Ticket type: " . (($getPackageCategory)?$getPackageCategory->name:'') . "</b><br>
                        <b>Member Amount: " . number_format((($member)?$member->payable_amount:0),2) . "</b><br>
                        <b>Associate Amount: " . number_format((($member)?$member->associate_amount:''),2) . "</b><br>
                        <b>Certificate Amount: " . number_format((($member)?$member->certificate_amount:''),2) . "</b><br><br>
                        <b>Total Amount: " . number_format($payment_amount,2) . "</b><br>
                        <b>Transaction Id: $transaction</b><br><br>
                        <b>Payment Date: $payment_date</b><br>
                        <b>Payment Status: Success</b><br><br>
                        Best regards,<br>
                        OSWB";
                    $this->sendMail($toAdmin, $subjectAdmin, $messageAdmin);

                    DB::table('submissions')->where('id', $input['member_id'])->update([
                            'payment_status'        => 1, 
                            'payment_amount'        => $payment_amount, 
                            'payment_txn'           => $transaction,
                            'payment_timestamp'     => date('Y-m-d H:i:s'),
                            'status'                => 1,
                            ]);

                    $status = [
                        'status'        => 'success',
                        'transactionid' => $input['razorpay_payment_id'],
                        'amount'        => number_format($payment->amount / 100, 2, '.', ''),
                    ];

                    // ✅ Redirect with success message
                    return redirect(url('/') . "/thankyou/" . Helper::encoded($input['member_id']))->with('success_message', 'Payment completed successfully !!!');
                } else {
                    throw new \Exception("Payment ID missing in callback");
                }

            } catch (SignatureVerificationError $e) {
                // ❌ Signature failed
                DB::table('payment_details')->insert([
                    'type'                  => 'CONFERENCE',
                    'member_id'             => $input['member_id'],
                    'transaction_id'        => $payment['id'],
                    'payment_amount'        => $input['payble_amount'],
                    'payment_mode'          => 'Razor Pay',
                    'payment_date'          => date('Y-m-d'),
                    'package_start_date'    => date('Y-m-d'),
                    'package_end_date'      => date('Y-m-d', strtotime('+365 days')),
                    'package_id'            => $input['package'],            
                    'status'                => 'Success',   
                    'response'              => json_encode($payment->toArray()),
                    'created_at'            => date('Y-m-d H:i:s'), 
                    'updated_at'            => date('Y-m-d H:i:s'),  
                ]);

                $status = [
                    'status'  => 'failed',
                    'message' => $e->getMessage(),
                ];
                return redirect(url('/') . "/checkout/" . Helper::encoded($input['member_id']))->with('error_message', 'Payment verification failed: '.$e->getMessage());
            } catch (\Exception $e) {
                dd($e->getMessage(), $e->getTraceAsString());
                // ❌ Any other error
                $status = [
                    'status'  => 'failed',
                    'message' => $e->getMessage(),
                ];
            }

            return redirect(url('/') . "/checkout/" . Helper::encoded($input['member_id']))->with('error_message', 'Payment failed or cancelled !!!');
        }
    /* payment with Razorpay */
    /* thank you */
        public function thankyou(Request $request, $submission_id){
            $submission_id                  = Helper::decoded($submission_id);
            $data['submission_id']          = $submission_id;
            $data['getSubmission']          = Submission::where('id', '=', $submission_id)->first();

            $title                          = 'Thank You';
            $page_name                      = 'thankyou';
            $data                           = $this->siteAuthService ->registration_layout($title,$page_name,$data);
            return view('front.pages.' . $page_name, $data);
        }
    /* thank you */
}