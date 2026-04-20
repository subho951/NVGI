<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\FrontdeskController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\MediumController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\KnowAboutController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\ReligionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalesPersonController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\UnitController;
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
                Route::post('application-settings', [AuthController::class, 'application_settings']);
                // Route::get('test-email', [AuthController::class, 'testEmail']);
                // Route::post('email-template', [AuthController::class, 'email_template']);
                Route::post('sms-settings', [AuthController::class, 'sms_settings']);
                // Route::post('footer-settings', [AuthController::class, 'footer_settings']);
                // Route::post('seo-settings', [AuthController::class, 'seo_settings']);
                // Route::post('payment-settings', [AuthController::class, 'payment_settings']);
            /* setting */

            /* access & permission */
                /* modules */
                    Route::match(['get', 'post'], 'module/list', [ModuleController::class, 'list']);
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
                /* front desk or users */
                    Route::match(['get', 'post'], 'front-desk/list', [FrontdeskController::class, 'list']);
                    Route::match(['get', 'post'], 'front-desk/edit/{id}', [FrontdeskController::class, 'edit']);
                    Route::get('front-desk/delete/{id}', [FrontdeskController::class, 'delete']);
                    Route::get('front-desk/change-status/{id}', [FrontdeskController::class, 'change_status']);
                /* front desk or users */
            /* access & permission */
            /* masters */
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
                /* subject */
                    Route::match(['get', 'post'], 'subject/list', [SubjectController::class, 'list']);
                    Route::match(['get', 'post'], 'subject/edit/{id}', [SubjectController::class, 'edit']);
                    Route::get('subject/delete/{id}', [SubjectController::class, 'delete']);
                    Route::get('subject/change-status/{id}', [SubjectController::class, 'change_status']);
                /* subject */
                /* session */
                    Route::match(['get', 'post'], 'session/list', [SessionController::class, 'list']);
                    Route::match(['get', 'post'], 'session/edit/{id}', [SessionController::class, 'edit']);
                    Route::get('session/delete/{id}', [SessionController::class, 'delete']);
                    Route::get('session/change-status/{id}', [SessionController::class, 'change_status']);
                /* session */
                /* board */
                    Route::match(['get', 'post'], 'board/list', [BoardController::class, 'list']);
                    Route::match(['get', 'post'], 'board/edit/{id}', [BoardController::class, 'edit']);
                    Route::get('board/delete/{id}', [BoardController::class, 'delete']);
                    Route::get('board/change-status/{id}', [BoardController::class, 'change_status']);
                /* board */
                /* medium */
                    Route::match(['get', 'post'], 'medium/list', [MediumController::class, 'list']);
                    Route::match(['get', 'post'], 'medium/edit/{id}', [MediumController::class, 'edit']);
                    Route::get('medium/delete/{id}', [MediumController::class, 'delete']);
                    Route::get('medium/change-status/{id}', [MediumController::class, 'change_status']);
                /* medium */
                /* know about us */
                    Route::match(['get', 'post'], 'know-about/list', [KnowAboutController::class, 'list']);
                    Route::match(['get', 'post'], 'know-about/edit/{id}', [KnowAboutController::class, 'edit']);
                    Route::get('know-about/delete/{id}', [KnowAboutController::class, 'delete']);
                    Route::get('know-about/change-status/{id}', [KnowAboutController::class, 'change_status']);
                /* know about us */
                /* class */
                    Route::match(['get', 'post'], 'class/list', [ClassController::class, 'list']);
                    Route::match(['get', 'post'], 'class/edit/{id}', [ClassController::class, 'edit']);
                    Route::get('class/delete/{id}', [ClassController::class, 'delete']);
                    Route::get('class/change-status/{id}', [ClassController::class, 'change_status']);
                /* class */
                /* Religion */
                    Route::match(['get', 'post'], 'religion/list', [ReligionController::class, 'list']);
                    Route::match(['get', 'post'], 'religion/edit/{id}', [ReligionController::class, 'edit']);
                    Route::get('religion/delete/{id}', [ReligionController::class, 'delete']);
                    Route::get('religion/change-status/{id}', [ReligionController::class, 'change_status']);
                /* Religion */
            /* masters */
            /* student */
                Route::match(['get', 'post'], 'student/list', [StudentController::class, 'list']);
                Route::match(['get', 'post'], 'student/add', [StudentController::class, 'add']);
                Route::match(['get', 'post'], 'student/edit/{id}', [StudentController::class, 'edit']);
                Route::get('student/delete/{id}', [StudentController::class, 'delete']);
                Route::get('student/change-status/{id}', [StudentController::class, 'change_status']);
                Route::post('student/promote', [StudentController::class, 'promote'])->name('student.promote');
                Route::post('student/special-fee/collect', [StudentController::class, 'collectSpecialFee'])->name('student.special-fee.collect');
                Route::get('student/details/{id}', [StudentController::class, 'details'])->name('student.details');
                Route::match(['get', 'post'], 'student/student-print/{id}', [StudentController::class, 'studentPrint']);
                Route::match(['get', 'post'], 'student/student-pdf/{id}', [StudentController::class, 'studentPDF']);
                Route::match(['get', 'post'], 'student/generate-id-card', [StudentController::class, 'generateIdCard'])->name('student.id-card.index');
                Route::post('student/generate-id-card/preview', [StudentController::class, 'generateIdCardPreview'])->name('student.id-card.preview');
                Route::post('student/generate-escort-card/preview', [StudentController::class, 'generateEscortCardPreview'])->name('student.escort-card.preview');
                Route::get('student/generate-id-card/branches', [StudentController::class, 'generateIdCardBranches'])->name('student.id-card.branches');
                Route::get('student/generate-id-card/classes', [StudentController::class, 'generateIdCardClasses'])->name('student.id-card.classes');
                Route::match(['get'], 'student/admission-fees-entry', [StudentController::class, 'admissionFeesEntry']);
                Route::match(['get', 'post'], 'student/fees-collection', [StudentController::class, 'feesCollection']);
                Route::post('student/fees-collection/update', [StudentController::class, 'updateFeesCollection'])->name('student.fees-collection.update');
                Route::post('student/fees-collection/due-report', [StudentController::class, 'feesCollectionDueReport'])->name('student.fees-collection.due-report');
                Route::match(['get'], 'student/fees-entry', [StudentController::class, 'feesEntry']);
            /* student */
            /* employee */
                Route::match(['get', 'post'], 'employee/list', [EmployeeController::class, 'list']);
                Route::match(['get', 'post'], 'employee/add', [EmployeeController::class, 'add']);
                Route::match(['get', 'post'], 'employee/edit/{id}', [EmployeeController::class, 'edit']);
                Route::get('employee/delete/{id}', [EmployeeController::class, 'delete']);
                Route::get('employee/change-status/{id}', [EmployeeController::class, 'change_status']);
            /* employee */
            /* CRM */
                /* sales person */
                    Route::match(['get', 'post'], 'sales-person/list', [SalesPersonController::class, 'list']);
                    Route::match(['get', 'post'], 'sales-person/edit/{id}', [SalesPersonController::class, 'edit']);
                    Route::get('sales-person/delete/{id}', [SalesPersonController::class, 'delete']);
                    Route::get('sales-person/change-status/{id}', [SalesPersonController::class, 'change_status']);
                /* sales person */
                /* leads */
                    Route::match(['get', 'post'], 'lead/list', [LeadController::class, 'list']);
                    Route::match(['get', 'post'], 'lead/add', [LeadController::class, 'add']);
                    Route::match(['get', 'post'], 'lead/edit/{id}', [LeadController::class, 'edit']);
                    Route::get('lead/delete/{id}', [LeadController::class, 'delete']);
                    Route::get('lead/change-status/{id}', [LeadController::class, 'change_status']);
                    Route::match(['get'], 'lead/generate-report', [LeadController::class, 'generateReport']);
                /* leads */
            /* CRM */
            /* finance */
                Route::match(['get', 'post'], 'finance/list', [FinanceController::class, 'list']);
                Route::match(['get', 'post'], 'finance/add', [FinanceController::class, 'add']);
                Route::match(['get', 'post'], 'finance/edit/{id}', [FinanceController::class, 'edit']);
                Route::get('finance/delete/{id}', [FinanceController::class, 'delete']);
                Route::get('finance/invoice/{id}', [FinanceController::class, 'invoice']);
                Route::match(['get', 'post'], 'finance/ledger/list', [LedgerController::class, 'list']);
                Route::match(['get', 'post'], 'finance/ledger/add', [LedgerController::class, 'add']);
                Route::match(['get', 'post'], 'finance/ledger/edit/{id}', [LedgerController::class, 'edit']);
                Route::get('finance/ledger/delete/{id}', [LedgerController::class, 'delete']);
                Route::get('finance/ledger/change-status/{id}', [LedgerController::class, 'change_status']);
            /* finance */
        });
    // });
/* Admin Panel */

Route::get('/table/fetch', [TableController::class, 'fetch']);
Route::get('/table/export', [TableController::class, 'export']);
