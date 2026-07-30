<?php
namespace App\Http\Controllers;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use App\Models\GeneralSetting;
use App\Models\User;
use App\Models\UserActivity;
use App\Models\EmailLog;
use App\Models\Role;
use Session;
use App\Helpers\Helper;
abstract class Controller
{
    use AuthorizesRequests;
    use ValidatesRequests;
    protected function sendMail($email, $subject, $message, $file = '')
    {
        $generalSetting             = GeneralSetting::find('1');
        $from_email                       = GeneralSetting::where('slug', 'from_email')->value('value');
        $from_name                       = GeneralSetting::where('slug', 'from_name')->value('value');
        $smtp_host                       = GeneralSetting::where('slug', 'smtp_host')->value('value');
        $smtp_username                       = GeneralSetting::where('slug', 'smtp_username')->value('value');
        $smtp_password                       = GeneralSetting::where('slug', 'smtp_password')->value('value');
        $smtp_port                       = GeneralSetting::where('slug', 'smtp_port')->value('value');        

        // Helper::pr($host);
        $mailLibrary                = new PHPMailer(true);
        $mailLibrary->CharSet       = 'UTF-8';
        $mailLibrary->SMTPDebug     = 0;
        $mailLibrary->IsSMTP();
        $mailLibrary->Host          = $smtp_host;
        $mailLibrary->SMTPAuth      = true;
        $mailLibrary->Port          = $smtp_port;
        $mailLibrary->Username      = $smtp_username;
        $mailLibrary->Password      = $smtp_password;
        $mailLibrary->SMTPSecure    = 'tls';
        $mailLibrary->From          = $from_email;
        $mailLibrary->FromName      = $from_name;
        // $mailLibrary->AddReplyTo($generalSetting->from_email, $generalSetting->from_name);
        if(is_array($email)) :
            foreach($email as $eml):
                $mailLibrary->addAddress($eml);
            endforeach;
        else:
            $mailLibrary->addAddress($email);
        endif;
        // $mailLibrary->AddCC('subhomoy.freelancer.samanta@gmail.com', 'Subhomoy Samanta');
        $mailLibrary->WordWrap      = 5000;
        $mailLibrary->Subject       = $subject;
        $mailLibrary->Body          = $message;
        $mailLibrary->isHTML(true);
        if (!empty($file)):
            $mailLibrary->AddAttachment($file);
        endif;
        return (!$mailLibrary->send()) ? false : true;
        // return true;
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
            } elseif($uploadType == 'csv') {
                if($imageFileType1 != "csv" && $imageFileType1 != "CSV") {
                    $message = 'Sorry, only CSV files are allowed';
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
                if($imageFileType1 != "jpg" && $imageFileType1 != "png" && $imageFileType1 != "jpeg" && $imageFileType1 != "JPG" && $imageFileType1 != "PNG" && $imageFileType1 != "JPEG" && $imageFileType1 != "ico" && $imageFileType1 != "ICO" && $imageFileType1 != "SVG" && $imageFileType1 != "svg" && $imageFileType1 != "GIF" && $imageFileType1 != "gif" && $imageFileType1 != "WEBP" && $imageFileType1 != "webp" && $imageFileType1 != "AVIF" && $imageFileType1 != "avif" && $imageFileType1 != "pdf" && $imageFileType1 != "PDF") {
                    $message = 'Sorry, only PDF,JPG, JPEG, ICO, SVG, PNG, GIF, WEBP files are allowed';
                    $status = 0;
                } else {
                    $message = 'Upload ok';
                    $status = 1;
                }
            } elseif($uploadType == 'all') {
                if($imageFileType1 != "jpg" && $imageFileType1 != "png" && $imageFileType1 != "jpeg" && $imageFileType1 != "JPG" && $imageFileType1 != "PNG" && $imageFileType1 != "JPEG" && $imageFileType1 != "ico" && $imageFileType1 != "ICO" && $imageFileType1 != "SVG" && $imageFileType1 != "svg" && $imageFileType1 != "GIF" && $imageFileType1 != "gif" && $imageFileType1 != "WEBP" && $imageFileType1 != "webp" && $imageFileType1 != "AVIF" && $imageFileType1 != "avif" && $imageFileType1 != "pdf" && $imageFileType1 != "PDF" && $imageFileType1 != "doc" && $imageFileType1 != "DOC" && $imageFileType1 != "docx" && $imageFileType1 != "DOCX" && $imageFileType1 != "xls" && $imageFileType1 != "XLS" && $imageFileType1 != "xlsx" && $imageFileType1 != "XLSX" && $imageFileType1 != "csv" && $imageFileType1 != "CSV" && $imageFileType1 != "ppt" && $imageFileType1 != "PPT" && $imageFileType1 != "pptx" && $imageFileType1 != "PPTX" && $imageFileType1 != "mp4" && $imageFileType1 != "3gp" && $imageFileType1 != "webm" && $imageFileType1 != "MP4" && $imageFileType1 != "3GP" && $imageFileType1 != "WEBM") {
                    $message = 'Sorry, only PDF,JPG, JPEG, ICO, SVG, PNG, GIF, WEBP files are allowed';
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
                        $upload_path = 'uploads/';
                    } else {
                        $upload_path = $path.'/';
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
        $data['head']               = view('elements.before-head', $data);
        $data['maincontent']        = view('maincontents.'.$page_name, $data);
        return view('layout-before-login', $data);
    }
    // admin after login layout
    public function admin_after_login_layout($title, $page_name, $data)
    {
        $data['generalSetting']     = GeneralSetting::select('key', 'value')->orderBy('id', 'ASC')->get();
        $data['title']              = $title.' :: '.Helper::getSettingValue('site_name');
        $data['page_header']        = $title;
        $user_id                    = session('user_data')['user_id'];
        $data['user']               = User::find($user_id);
        $role_id                    = (($data['user'])?$data['user']->role_id:0);
        $userAccess                 = Role::where('id', '=', $role_id)->where('status', '=', 1)->first();
        if($userAccess) {
            $data['module_id']      = json_decode($userAccess->module_id);
        } else {
            $data['module_id']      = [];
        }
        $data['head']               = view('elements.after-head', $data);
        $data['header']             = view('elements.header', $data);
        $data['footer']             = view('elements.footer', $data);
        $data['sidebar']            = view('elements.sidebar', $data);
        $data['maincontent']        = view('maincontents.'.$page_name, $data);
        return view('layout-after-login', $data);
    }
    public function getSettingValue($slug){
        $generalSetting     = GeneralSetting::select('value')->where('slug', '=', $slug)->first();
        // Helper::pr($generalSetting);
        return (($generalSetting)?$generalSetting->value:'');
    }
    // currency converter
    public function convertCurrency($amount, $from, $to)
    {
        // Fetching JSON
        $req_url = 'https://api.exchangerate-api.com/v4/latest/'.$from;
        $response_json = file_get_contents($req_url);
        // Continuing if we got a result
        if(false !== $response_json) {
            // Try/catch for json_decode operation
            try {
                // Decoding
                $response_object = json_decode($response_json);
                // YOUR APPLICATION CODE HERE, e.g.
                $base_price = $amount; // Your price in USD
                $EUR_price = round(($base_price * $response_object->rates->$to), 2);
                return $EUR_price;
            } catch(Exception $e) {
                // Handle JSON parse error...
            }
        }
    }
    public function perform_http_request($method, $url, $data = array())
    {
        $curl = curl_init();
        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data) {
                    $url = sprintf("%s?%s", $url, http_build_query($data));
                }
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true); //If SSL Certificate Not Available, for example, I am calling from http://localhost URL
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
    public function curl_request_post($method, $functionName, $data = array())
    {
        $url = "https://domainname/api/";
        $dataJson           = [
            'key' => 'facb6e0a6fcbe200dca2fb60dec75be7',
            'source' => 'WEB'
        ];
        $data = array_merge($dataJson, $data);
        // Helper::pr($data);
        $curl = curl_init($url.$functionName);
        curl_setopt($curl, CURLOPT_URL, $url.$functionName);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $headers = array(
            "Content-type: application/json",
            "Accept: application/json",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        //for debug only!
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $resp = curl_exec($curl);
        curl_close($curl);
        //pr($resp);
        return $resp;
    }
    public function getApiResponse($method, $functionName, $data = array())
    {
        $response               = json_decode($this->curl_request_post($method, $functionName));
        return $response->data;
    }
    /* For API Development */
        public function isJSON($string)
        {
            $valid = is_string($string) && is_array(json_decode($string, true)) ? true : false;
            if (!$valid) {
                $this->response_to_json(false, "Not JSON", 401);
            }
        }
        /* Process json from request */
        public function extract_json($key)
        {
            return json_decode($key, true);
        }
        /* Methods to check all necessary fields inside a requested post body */
        public function validateArray($keys, $arr)
        {
            return !array_diff_key(array_flip($keys), $arr);
        }
        /* Set response message response_to_json = set_response */
        public function response_to_json($success = true, $message = "success", $data = null, $extraField = null, $extraData = null)
        {
            $response = ["status" => $success, "message" => $message, "data" => $data];
            if ($extraField != null && $extraData != null) {
                $response[$extraField] = $extraData;
            }
            header('Content-Type: application/json; charset=utf-8');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
            header("Access-Control-Allow-Headers: Content-Type, X-Auth-Token, X-Requested-With, Origin, Authorization");
            print json_encode($response);
            die;
        }
        public function responseJSON($data)
        {
            print json_encode($data);
            die;
        }
    /* For API Development */
}
