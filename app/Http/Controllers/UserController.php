<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\GeneralSetting;
use App\Models\EmailLog;
use App\Models\UserActivity;
use App\Services\SiteAuthService;
use App\Models\User;
use Session;
use App\Helpers\Helper;
use Hash;
use DB;

class UserController extends Controller
{
    protected $siteAuthService;
    protected $data;
    
}
