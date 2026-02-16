<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\FrontdeskController;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Common\TableController;

/* Front Panel */
    // Route::get('/', function () {
    //     return view('welcome');
    // });
    /* registrations */
        Route::match(['get', 'post'], '/', [FrontendController::class, 'index']);
        Route::post('/check-member-email', [FrontendController::class, 'checkMemberEmail'])->name('check.member.email');
        Route::match(['get', 'post'], '/verify-otp/{id}', [FrontendController::class, 'verifyOTP'])->name('verifyOTP');
        Route::match(['get', 'post'], '/resend-otp/{id}', [FrontendController::class, 'resendOTP']);
        Route::match(['get', 'post'], '/member-info/{id}', [FrontendController::class, 'memberInfo']);
        Route::match(['get', 'post'], '/checkout/{id}', [FrontendController::class, 'checkout']);

        Route::post('payment/razorpay', [FrontendController::class, 'razorpay'])->name('razorpay');
        Route::post('payment/razorpaycallback', [FrontendController::class, 'razorpaycallback'])->name('razorpaycallback');

        Route::match(['get', 'post'], 'thankyou/{id}', [FrontendController::class, 'thankyou']);
    /* registrations */
    /* submissions */
        Route::match(['get', 'post'], '/submission', [FrontendSubmissionController::class, 'index']);
        Route::match(['get', 'post'], '/submission-verify-otp/{id}', [FrontendSubmissionController::class, 'verifyOTP']);
        Route::match(['get', 'post'], '/submission-resend-otp/{id}', [FrontendSubmissionController::class, 'resendOTP']);
        Route::match(['get', 'post'], '/submission-member-info/{id}', [FrontendSubmissionController::class, 'memberInfo']);
        Route::match(['get', 'post'], '/submission-category/{id}', [FrontendSubmissionController::class, 'submissionCategory']);
        Route::match(['get', 'post'], '/submission-award/{id}', [FrontendSubmissionController::class, 'submissionAward']);
        Route::match(['get', 'post'], '/upload-content/{id}', [FrontendSubmissionController::class, 'uploadContent']);
        Route::match(['get', 'post'], '/upload-videos', [FrontendSubmissionController::class, 'upload'])->name('video.upload');
        Route::post('/delete-uploaded-video', [FrontendSubmissionController::class, 'deleteUploadedVideo'])->name('video.delete');
        Route::match(['get', 'post'], '/submission-thankyou/{id}', [FrontendSubmissionController::class, 'thankyou']);
        Route::get('/get-member-by-email', [FrontendSubmissionController::class, 'getMemberByEmail'])->name('get.member.by.email');
        Route::get('/search-members', [FrontendSubmissionController::class, 'search'])->name('search.member');
    /* submissions */
    /* certificate */
        Route::match(['get', 'post'], '/certificate', [CertificateController::class, 'index']);
        Route::match(['get', 'post'], '/certificate-member-search', [CertificateController::class, 'certificateMemberSearch']);
        Route::get('/search-name', [App\Http\Controllers\CertificateController::class, 'searchName'])->name('search.name');
        Route::match(['get', 'post'], '/certificate-preview/{id}', [CertificateController::class, 'certificatePreview']);
        Route::match(['get', 'post'], '/print-preview/{id}', [CertificateController::class, 'certificatePrintPreview']);
        Route::match(['get', 'post'], '/update-delegates-from-submissions', [CertificateController::class, 'updateDelegatesFromSubmissions']);
    /* certificate */
/* Front Panel */
/* Admin Panel */
    // GET route – to display the page
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('signin', [AuthController::class, 'login'])->name('signin');

    // Route::prefix('/admin')->namespace('App\Http\Controllers\Admin')->group(function(){
        
        Route::match(['get','post'],'/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgotpassword');
        Route::match(['get','post'],'/validate-otp/{id}', [AuthController::class, 'validateOtp'])->name('validateotp');
        Route::match(['get','post'],'/resend-otp/{id}', [AuthController::class, 'resendOtp']);
        Route::match(['get','post'],'/reset-password/{id}', [AuthController::class, 'resetPassword'])->name('resetpassword');

        Route::middleware(['auth'])->group(function () {
            Route::get('dashboard', [AuthController::class, 'dashboard']);
            Route::get('logout', [AuthController::class, 'logout']);
            Route::get('email-logs', [AuthController::class, 'emailLogs']);
            Route::match(['get','post'],'/email-logs/details/{email}', [AuthController::class, 'emailLogsDetails']);
            Route::get('login-logs', [AuthController::class, 'loginLogs']);
            Route::get('user-activity-logs', [AuthController::class, 'userActivityLogs']);
            Route::match(['get','post'], '/common-delete-image/{id1}/{id2}/{id3}/{id4}/{id5}', [AuthController::class, 'commonDeleteImage']);
            /* setting */
                Route::get('settings', [AuthController::class, 'settings']);
                Route::post('profile-settings', [AuthController::class, 'profile_settings']);
                Route::post('general-settings', [AuthController::class, 'general_settings']);
                Route::post('change-password', [AuthController::class, 'change_password']);
                Route::post('email-settings', [AuthController::class, 'email_settings']);
                Route::get('test-email', [AuthController::class, 'testEmail']);
                Route::post('email-template', [AuthController::class, 'email_template']);
                Route::post('sms-settings', [AuthController::class, 'sms_settings']);
                Route::post('footer-settings', [AuthController::class, 'footer_settings']);
                Route::post('seo-settings', [AuthController::class, 'seo_settings']);
                Route::post('payment-settings', [AuthController::class, 'payment_settings']);
            /* setting */

            /* access & permission */
                /* modules */
                    Route::get('module/list', [ModuleController::class, 'list']);
                    Route::match(['get', 'post'], 'module/add', [ModuleController::class, 'add']);
                    Route::match(['get', 'post'], 'module/edit/{id}', [ModuleController::class, 'edit']);
                    Route::get('module/delete/{id}', [ModuleController::class, 'delete']);
                    Route::get('module/change-status/{id}', [ModuleController::class, 'change_status']);
                /* modules */
                /* roles */
                    Route::get('role/list', [RoleController::class, 'list']);
                    Route::match(['get', 'post'], 'role/add', [RoleController::class, 'add']);
                    Route::match(['get', 'post'], 'role/edit/{id}', [RoleController::class, 'edit']);
                    Route::get('role/delete/{id}', [RoleController::class, 'delete']);
                    Route::get('role/change-status/{id}', [RoleController::class, 'change_status']);
                /* roles */
                /* admin users */
                    Route::get('admin-user/list', [AdminUserController::class, 'list']);
                    Route::match(['get', 'post'], 'admin-user/add', [AdminUserController::class, 'add']);
                    Route::match(['get', 'post'], 'admin-user/edit/{id}', [AdminUserController::class, 'edit']);
                    Route::get('admin-user/delete/{id}', [AdminUserController::class, 'delete']);
                    Route::get('admin-user/change-status/{id}', [AdminUserController::class, 'change_status']);
                /* admin users */
            /* access & permission */

            /* unit */
                Route::match(['get', 'post'], 'unit/list', [UnitController::class, 'list']);
                Route::match(['get', 'post'], 'unit/edit/{id}', [UnitController::class, 'edit']);
                Route::get('unit/delete/{id}', [UnitController::class, 'delete']);
                Route::get('unit/change-status/{id}', [UnitController::class, 'change_status']);
            /* unit */
            /* branch */
                Route::match(['get', 'post'], 'branch/list', [BranchController::class, 'list']);
                Route::match(['get', 'post'], 'branch/edit/{id}', [BranchController::class, 'edit']);
                Route::get('branch/delete/{id}', [BranchController::class, 'delete']);
                Route::get('branch/change-status/{id}', [BranchController::class, 'change_status']);
            /* branch */
            /* front desk */
                Route::match(['get', 'post'], 'front-desk/list', [FrontdeskController::class, 'list']);
                Route::match(['get', 'post'], 'front-desk/edit/{id}', [FrontdeskController::class, 'edit']);
                Route::get('front-desk/delete/{id}', [FrontdeskController::class, 'delete']);
                Route::get('front-desk/change-status/{id}', [FrontdeskController::class, 'change_status']);
            /* front desk */
        });
    // });
/* Admin Panel */

Route::get('/table/fetch', [TableController::class, 'fetch']);
Route::get('/table/export', [TableController::class, 'export']);