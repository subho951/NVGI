<?php

namespace App\Services;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserActivity;
use App\Models\EmailLog;
use App\Models\Role;
use Session;
use App\Helpers\Helper;

class SiteAuthService
{
    use AuthorizesRequests;
    use ValidatesRequests;
    protected function sendMail($email, $subject, $message, $file = '')
    {
        $generalSetting             = GeneralSetting::find('1');
        $mailLibrary                = new PHPMailer(true);
        $mailLibrary->CharSet       = 'UTF-8';
        $mailLibrary->SMTPDebug     = 0;
        //$mailLibrary->IsSMTP();
        $mailLibrary->Host          = $generalSetting->smtp_host;
        $mailLibrary->SMTPAuth      = true;
        $mailLibrary->Port          = $generalSetting->smtp_port;
        $mailLibrary->Username      = $generalSetting->smtp_username;
        $mailLibrary->Password      = $generalSetting->smtp_password;
        $mailLibrary->SMTPSecure    = 'ssl';
        $mailLibrary->From          = $generalSetting->from_email;
        $mailLibrary->FromName      = $generalSetting->from_name;
        // $mailLibrary->AddReplyTo($generalSetting->from_email, $generalSetting->from_name);
        if(is_array($email)) :
            foreach($email as $eml):
                $mailLibrary->addAddress($eml);
            endforeach;
        else:
            $mailLibrary->addAddress($email);
        endif;
        $mailLibrary->AddCC('subhomoysamanta1989@gmail.com', 'Subhomoy Samanta');
        $mailLibrary->WordWrap      = 5000;
        $mailLibrary->Subject       = $subject;
        $mailLibrary->Body          = $message;
        $mailLibrary->isHTML(true);
        if (!empty($file)):
            $mailLibrary->AddAttachment($file);
        endif;
        return (!$mailLibrary->send()) ? false : true;
    }
    // single file upload
    public function upload_single_file($fieldName, $fileName, $uploadedpath, $uploadType, $tempFile = '')
    {
        $imge = $fileName;
        if($imge == '') {
            $slider_image = 'no-user-image.jpg';
        } else {
            $imageFileType1 = pathinfo($imge, PATHINFO_EXTENSION);
            if($uploadType == 'image') {
                if($imageFileType1 != "jpg" && $imageFileType1 != "png" && $imageFileType1 != "jpeg" && $imageFileType1 != "JPG" && $imageFileType1 != "PNG" && $imageFileType1 != "JPEG" && $imageFileType1 != "ico" && $imageFileType1 != "ICO" && $imageFileType1 != "SVG" && $imageFileType1 != "svg" && $imageFileType1 != "GIF" && $imageFileType1 != "gif" && $imageFileType1 != "WEBP" && $imageFileType1 != "webp" && $imageFileType1 != "AVIF" && $imageFileType1 != "avif") {
                    $message = 'Sorry, only JPG, JPEG, ICO, SVG, PNG, GIF, WEBP files are allowed';
                    $status = 0;
                } else {
                    $message = 'Upload ok';
                    $status = 1;
                }
            } elseif($uploadType == 'pdf') {
                if($imageFileType1 != "pdf" && $imageFileType1 != "PDF") {
                    $message = 'Sorry, only PDF files are allowed';
                    $status = 0;
                } else {
                    $message = 'Upload ok';
                    $status = 1;
                }
            } elseif($uploadType == 'word') {
                if($imageFileType1 != "doc" && $imageFileType1 != "DOC" && $imageFileType1 != "docx" && $imageFileType1 != "DOCX") {
                    $message = 'Sorry, only DOC files are allowed';
                    $status = 0;
                } else {
                    $message = 'Upload ok';
                    $status = 1;
                }
            } elseif($uploadType == 'excel') {
                if($imageFileType1 != "xls" && $imageFileType1 != "XLS" && $imageFileType1 != "xlsx" && $imageFileType1 != "XLSX") {
                    $message = 'Sorry, only EXCEl files are allowed';
                    $status = 0;
                } else {
                    $message = 'Upload ok';
                    $status = 1;
                }
            } elseif($uploadType == 'powerpoint') {
                if($imageFileType1 != "ppt" && $imageFileType1 != "PPT" && $imageFileType1 != "pptx" && $imageFileType1 != "PPTX") {
                    $message = 'Sorry, only PPT files are allowed';
                    $status = 0;
                } else {
                    $message = 'Upload ok';
                    $status = 1;
                }
            } elseif($uploadType == 'video') {
                if($imageFileType1 != "mp4" && $imageFileType1 != "3gp" && $imageFileType1 != "webm" && $imageFileType1 != "MP4" && $imageFileType1 != "3GP" && $imageFileType1 != "WEBM") {
                    $message = 'Sorry, only Video files are allowed';
                    $status = 0;
                } else {
                    $message = 'Upload ok';
                    $status = 1;
                }
            } elseif($uploadType == 'custom') {
                if($imageFileType1 != "doc" && $imageFileType1 != "DOC" && $imageFileType1 != "docx" && $imageFileType1 != "DOCX" && $imageFileType1 != "pdf" && $imageFileType1 != "PDF" && $imageFileType1 != "ppt" && $imageFileType1 != "PPT" && $imageFileType1 != "pptx" && $imageFileType1 != "PPTX" && $imageFileType1 != "txt" && $imageFileType1 != "TXT" && $imageFileType1 != "xls" && $imageFileType1 != "XLS" && $imageFileType1 != "xlsx" && $imageFileType1 != "XLSX") {
                    $message = 'Sorry, only .DOC,.DOCX,.PPT,.PPTX,.PDF,.XLS,.XLSX files are allowed';
                    $status = 0;
                } else {
                    $message = 'Upload ok';
                    $status = 1;
                }
            }
            $newFilename = time().$imge;
            if($tempFile == ''){
                $temp = $_FILES[$fieldName]["tmp_name"];
            } else {
                $temp = $tempFile;
            }
            if($uploadedpath == '') {
                $upload_path = public_path('uploads/');
            } else {
                $upload_path = public_path('uploads/'.$uploadedpath.'/');
            }
            if($status) {
                // move_uploaded_file($temp, $upload_path.$newFilename);
                move_uploaded_file($temp, $upload_path . $newFilename);
                $return_array = array('status' => 1, 'message' => $message, 'newFilename' => $newFilename);
            } else {
                $return_array = array('status' => 0, 'message' => $message, 'newFilename' => '');
            }
            return $return_array;
        }
    }
    // multiple files upload
    public function commonFileArrayUpload($path = '', $images = array(), $uploadType = '')
    {
        $apiStatus = false;
        $apiMessage = [];
        $apiResponse = [];
        if(count($images) > 0) {
            for($p = 0;$p < count($images);$p++) {
                $imge = $images[$p]->getClientOriginalName();
                if($imge == '') {
                    $slider_image = 'no-user-image.jpg';
                } else {
                    $imageFileType1 = pathinfo($imge, PATHINFO_EXTENSION);
                    if($uploadType == 'image') {
                        if($imageFileType1 != "jpg" && $imageFileType1 != "png" && $imageFileType1 != "jpeg" && $imageFileType1 != "JPG" && $imageFileType1 != "PNG" && $imageFileType1 != "JPEG" && $imageFileType1 != "ico" && $imageFileType1 != "ICO" && $imageFileType1 != "SVG" && $imageFileType1 != "svg" && $imageFileType1 != "GIF" && $imageFileType1 != "gif" && $imageFileType1 != "WEBP" && $imageFileType1 != "webp" && $imageFileType1 != "AVIF" && $imageFileType1 != "avif") {
                            $message = 'Sorry, only JPG, JPEG, ICO, PNG & GIF files are allowed';
                            $status = 0;
                        } else {
                            $message = 'Upload ok';
                            $status = 1;
                        }
                    } elseif($uploadType == 'pdf') {
                        if($imageFileType1 != "pdf" && $imageFileType1 != "PDF") {
                            $message = 'Sorry, only PDF files are allowed';
                            $status = 0;
                        } else {
                            $message = 'Upload ok';
                            $status = 1;
                        }
                    } elseif($uploadType == 'word') {
                        if($imageFileType1 != "doc" && $imageFileType1 != "DOC" && $imageFileType1 != "docx" && $imageFileType1 != "DOCX") {
                            $message = 'Sorry, only DOC files are allowed';
                            $status = 0;
                        } else {
                            $message = 'Upload ok';
                            $status = 1;
                        }
                    } elseif($uploadType == 'excel') {
                        if($imageFileType1 != "xls" && $imageFileType1 != "XLS" && $imageFileType1 != "xlsx" && $imageFileType1 != "XLSX") {
                            $message = 'Sorry, only EXCEl files are allowed';
                            $status = 0;
                        } else {
                            $message = 'Upload ok';
                            $status = 1;
                        }
                    } elseif($uploadType == 'powerpoint') {
                        if($imageFileType1 != "ppt" && $imageFileType1 != "PPT" && $imageFileType1 != "pptx" && $imageFileType1 != "PPTX") {
                            $message = 'Sorry, only PPT files are allowed';
                            $status = 0;
                        } else {
                            $message = 'Upload ok';
                            $status = 1;
                        }
                    }
                    $newFilename = uniqid().".".$imageFileType1;
                    // $temp = $images[$p]->getTempName();
                    $temp = $images[$p]->getPathName();
                    if($path == '') {
                        $upload_path = public_path('uploads/');
                    } else {
                        $upload_path = public_path('uploads/'.$path.'/');
                    }
                    if($status) {
                        move_uploaded_file($temp, $upload_path.$newFilename);
                        //$apiStatus      = TRUE;
                        //$apiMessage     = $message;
                        $apiResponse[]  = $newFilename;
                    } else {
                        //$apiStatus      = FALSE;
                        //$apiMessage     = $message;
                    }
                }
            }
        }
        //$return_array = array('status'=> $apiStatus, 'message'=> $apiMessage, 'newFilename'=> $apiResponse);
        return $apiResponse;
    }
    // admin authentication layout
    public function admin_before_login_layout($title, $page_name, $data)
    {
        $data['generalSetting']     = GeneralSetting::select('key', 'value')->orderBy('id', 'ASC')->get();
        $data['title']              = $title.' - '.Helper::getSettingValue('site_name');
        $data['page_header']        = $title;
        // return view('layout-before-login', $data);
        return $data;
    }
    // member registration layout
    public function registration_layout($title, $page_name, $data)
    {
        $data['generalSetting']     = GeneralSetting::select('key', 'value')->orderBy('id', 'ASC')->get();
        $data['title']              = $title.' :: '.Helper::getSettingValue('site_name2');
        $data['page_header']        = $title;
        return $data;
    }
    // certificate layout
    public function certificate_layout($title, $page_name, $data)
    {
        $data['generalSetting']     = GeneralSetting::select('key', 'value')->orderBy('id', 'ASC')->get();
        $data['title']              = $title.' :: '.Helper::getSettingValue('site_name2');
        $data['page_header']        = $title;
        return $data;
    }
    // admin after login layout
    public function admin_after_login_layout($title, $page_name, $data)
    {
        $data['generalSetting']     = GeneralSetting::select('key', 'value')->orderBy('id', 'ASC')->get();
        $data['title']              = $title.' :: '.Helper::getSettingValue('site_name');
        $data['page_header']        = $title;
        $user_id                    = Auth::user()->id;
        $data['user']               = Auth::user();
        $role_id                    = (($data['user'])?$data['user']->role_id:0);
        $userAccess                 = Role::where('id', '=', $role_id)->where('status', '=', 1)->first();
        if($userAccess) {
            $data['moduleIds']      = json_decode($userAccess->module_id);
        } else {
            $data['moduleIds']      = [];
        }
        return $data;
    }
    public function getSettingValue($slug){
        $generalSetting     = GeneralSetting::select('value')->where('slug', '=', $slug)->first();
        // Helper::pr($generalSetting);
        return (($generalSetting)?$generalSetting->value:'');
    }
}
