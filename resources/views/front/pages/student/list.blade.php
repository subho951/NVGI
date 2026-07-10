@extends('front.layouts.afterlogin')
@section('content')
<?php

use App\Helpers\Helper;

$controllerRoute = $module['controller_route'];
?>
<style>
    a{
        text-decoration: none;
    }
    .student-page-title{
        font-weight: 700;
        color: #1f2937;
        letter-spacing: 0.2px;
    }
    .student-list-card{
        border: 0;
        border-radius: 14px;
        overflow: hidden;
        background: linear-gradient(180deg, #f6fbff 0%, #ffffff 35%);
    }
    .student-list-card .card-header{
        background: linear-gradient(90deg, #ffffff 0%, #eaf4ff 100%);
        border-bottom: 1px solid #d8e6f4;
    }
    .student-list-card .card-body{
        padding-top: 1rem;
    }
    .student-list-note{
        border: 1px solid #c8def6;
        background: linear-gradient(90deg, #dbeafe 0%, #e0f2fe 100%);
        color: #0f172a;
        font-weight: 600;
    }
    .student-table-wrap{
        border: 1px solid #d8e6f4;
        border-radius: 12px;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        background: #ffffff;
    }
    #example{
        margin-bottom: 0 !important;
    }
    #example thead th{
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        font-weight: 700;
        white-space: nowrap;
    }
    #example tbody td{
        font-size: 0.9rem;
        color: #1f2937;
        vertical-align: middle;
    }
    #example tbody tr:hover{
        background-color: #f0f7ff;
    }
    .student-photo{
        width: 52px;
        height: 52px;
        object-fit: cover;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
    }
    .fee-value{
        font-weight: 700;
        color: #0f766e;
    }
    .student-fee-btn{
        min-width: 92px;
        white-space: nowrap;
    }
    .student-fee-btn.fee-paid{
        background: #198754;
        border-color: #198754;
        color: #ffffff;
        opacity: 1;
    }
    .student-fee-btn.fee-warning{
        background: #f59f00;
        border-color: #f59f00;
        color: #111827;
    }
    .student-fee-btn.fee-error{
        background: #dc3545;
        border-color: #dc3545;
        color: #ffffff;
    }
</style>
<h2 class="student-page-title mb-3">Manage <?= $module['title'] ?></h2>
@if(session('success_message'))
<div class="alert alert-success bg-success text-light border-0 alert-dismissible fade show autohide" role="alert">
    {{ session('success_message') }}
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
@if(session('error_message'))
<div class="alert alert-danger bg-danger text-light border-0 alert-dismissible fade show autohide" role="alert">
    {{ session('error_message') }}
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if ($errors->any())
<div class="alert alert-danger">
    <strong>Please fix the following errors:</strong>
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="card shadow-sm student-list-card">
    <div class="card-header">
        <h5>
            <a href="<?= url('student/add') ?>" class="btn btn-success btn-sm">Add New <?= $module['title'] ?></a>
        </h5>
    </div>
    <div class="card-body">
        <h6 class="text-center alert alert-sm py-2 px-2 student-list-note">List of <?= $module['title'] ?></h6>
        <div class="table-responsive student-table-wrap">
            <table id="example" class="table table-striped table-bordered align-middle text-center" style="width:100%">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-center">ID</th>
                        <th class="text-center">Name of the Student</th>
                        <th class="text-center">Contact</th>
                        <th class="text-center">Unit</th>
                        <th class="text-center">Branch</th>
                        <th class="text-center">Class</th>
                        <th class="text-center">DOB</th>
                        <th class="text-center">Parent Name</th>
                        <th class="text-center">Address & Pincode</th>
                        <th class="text-center">Admission Fees</th>
                        <th class="text-center">Monthly Fees</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows) {
                        $sl = 1;
                        foreach ($rows as $row) { ?>
                            <?php
                                $studentNameDisplay = (!empty(trim((string)$row->full_name)) && strcasecmp(trim((string)$row->full_name), 'No Name') !== 0) ? $row->full_name : '-';
                                $currentClassId = (($row->unit_id == 1) ? $row->vhs_class_id : $row->tsa_class_id);
                            ?>
                            <tr>
                                <td><?= $sl++ ?></td>
                                <td><?= $row->student_id_serial ?></td>
                                <td>
                                    <?php if ($row->photo != '') { ?>
                                        <img src="<?= config('constants.app_url') . config('constants.uploads_url_path') . $row->photo ?>" class="student-photo">
                                    <?php } else { ?>
                                        <img src="{{ config('constants.no_image_avatar') }}" class="student-photo">
                                    <?php } ?>
                                    <br>
                                    <?= $studentNameDisplay ?>
                                </td>
                                <td><?= !empty($row->father_mobile) ? $row->father_mobile : '-' ?></td>
                                <td><?= !empty($row->unit_name) ? $row->unit_name : '-' ?></td>
                                <td><?= !empty($row->branch_name) ? $row->branch_name : '-' ?></td>
                                <td><?= !empty($row->class_name) ? $row->class_name : '-' ?></td>
                                <td><?= (!empty($row->dob) && strtotime($row->dob)) ? date("d-m-Y", strtotime($row->dob)) : '-' ?></td>
                                <td>
                                    <strong>Father:</strong> <?= (!empty(trim((string)$row->father_name)) && strcasecmp(trim((string)$row->father_name), 'No Name') !== 0) ? $row->father_name : '-' ?>
                                    <br>
                                    <strong>Mother:</strong> <?= (!empty(trim((string)$row->mother_name)) && strcasecmp(trim((string)$row->mother_name), 'No Name') !== 0) ? $row->mother_name : '-' ?>
                                </td>
                                <td>
                                    <?= !empty($row->permanent_address) ? $row->permanent_address : '-' ?>
                                    <br>
                                    PIN: <?= !empty($row->permanent_pincode) ? $row->permanent_pincode : '-' ?>
                                </td>
                                <td><span class="fee-value"><?= is_numeric($row->admission_fees) ? number_format((float)$row->admission_fees, 2) : '-' ?></span></td>
                                <td><span class="fee-value"><?= is_numeric($row->monthly_fees) ? number_format((float)$row->monthly_fees, 2) : '-' ?></span></td>
                                <td>
                                    <?php
                                    $encoded_id     = Helper::encoded($row->id);
                                    $delete_url     = $controllerRoute . '/delete/';
                                    $status_url     = $controllerRoute . '/change-status/';
                                    $edit_url       = $controllerRoute . '/edit/';
                                    ?>
                                    <a href="<?= url($controllerRoute . '/edit/' . Helper::encoded($row->id)) ?>" class="text-primary" title="Edit <?= $module['title'] ?>"><i class="fa fa-edit text-primary"></i></a>
                                    |
                                    <?php if ($row->status) { ?>
                                        <a href="javascript:void(0);" onclick="showConfirmBox('<?= $encoded_id ?>', '<?= $status_url ?>', 'Are you sure you want to deactivate this record?')" class="text-success" title="Active <?= $module['title'] ?>"><i class="fa fa-check text-success"></i></a>
                                    <?php } else { ?>
                                        <a href="javascript:void(0);" onclick="showConfirmBox('<?= $encoded_id ?>', '<?= $status_url ?>', 'Are you sure you want to activate this record?')" class="text-warning" title="Blocked <?= $module['title'] ?>"><i class="fas fa-ban text-danger"></i></a>
                                    <?php } ?>
                                    |
                                    <?php if($row->unit_id == 1){
                                        $booksFeeAmount = (float)$row->books_fee;
                                        $booksTxnCount = (int)($row->books_fee_txn_count ?? 0);
                                        $booksTxnAmount = (float)($row->books_fee_txn_amount ?? 0);
                                        $booksIsPaid = ($booksFeeAmount > 0 && $booksTxnCount > 0 && abs($booksTxnAmount - $booksFeeAmount) <= 0.009);
                                        $booksHasMismatch = ($booksTxnCount > 0 && !$booksIsPaid);
                                        $booksBtnClass = ($booksIsPaid ? 'fee-paid' : ($booksHasMismatch ? 'fee-error' : (($booksFeeAmount > 0) ? 'fee-warning' : '')));
                                        $booksBtnLabel = ($booksIsPaid ? 'Books Paid' : ($booksHasMismatch ? 'Books Check' : (($booksFeeAmount > 0) ? 'Books Missing' : 'Books Fee')));
                                        $booksBtnDisabled = (($booksIsPaid || $booksHasMismatch) ? 'disabled' : '');

                                        $uniformFeeAmount = (float)$row->uniform_fee;
                                        $uniformTxnCount = (int)($row->uniform_fee_txn_count ?? 0);
                                        $uniformTxnAmount = (float)($row->uniform_fee_txn_amount ?? 0);
                                        $uniformIsPaid = ($uniformFeeAmount > 0 && $uniformTxnCount > 0 && abs($uniformTxnAmount - $uniformFeeAmount) <= 0.009);
                                        $uniformHasMismatch = ($uniformTxnCount > 0 && !$uniformIsPaid);
                                        $uniformBtnClass = ($uniformIsPaid ? 'fee-paid' : ($uniformHasMismatch ? 'fee-error' : (($uniformFeeAmount > 0) ? 'fee-warning' : '')));
                                        $uniformBtnLabel = ($uniformIsPaid ? 'Uniform Paid' : ($uniformHasMismatch ? 'Uniform Check' : (($uniformFeeAmount > 0) ? 'Uniform Missing' : 'Uniform Fee')));
                                        $uniformBtnDisabled = (($uniformIsPaid || $uniformHasMismatch) ? 'disabled' : '');
                                    ?>
                                        <button type="button"
                                                class="btn btn-info btn-sm student-fee-btn feeCollectionBtn <?= $booksBtnClass ?>"
                                                data-student-id="<?= $row->id ?>"
                                                data-student-name="<?= e($studentNameDisplay) ?>"
                                                data-student-serial="<?= e($row->student_id_serial) ?>"
                                                data-session-name="<?= e(!empty($row->session_name) ? $row->session_name : '-') ?>"
                                                data-books-fee="<?= e($row->books_fee) ?>"
                                                data-uniform-fee="<?= e($row->uniform_fee) ?>"
                                                data-fee-type="books"
                                                data-fee-label="Books Fee"
                                                <?= $booksBtnDisabled ?>>
                                            <?= $booksBtnLabel ?>
                                        </button>
                                        |
                                        <button type="button"
                                                class="btn btn-secondary btn-sm student-fee-btn feeCollectionBtn <?= $uniformBtnClass ?>"
                                                data-student-id="<?= $row->id ?>"
                                                data-student-name="<?= e($studentNameDisplay) ?>"
                                                data-student-serial="<?= e($row->student_id_serial) ?>"
                                                data-session-name="<?= e(!empty($row->session_name) ? $row->session_name : '-') ?>"
                                                data-books-fee="<?= e($row->books_fee) ?>"
                                                data-uniform-fee="<?= e($row->uniform_fee) ?>"
                                                data-fee-type="uniform"
                                                data-fee-label="Uniform Fee"
                                                <?= $uniformBtnDisabled ?>>
                                            <?= $uniformBtnLabel ?>
                                        </button>
                                        |
                                    <?php }?>
                                    <button type="button"
                                            class="btn btn-warning btn-sm promoteStudentBtn"
                                            data-student-id="<?= $row->id ?>"
                                            data-student-name="<?= e($studentNameDisplay) ?>"
                                            data-present-class-name="<?= e(!empty($row->class_name) ? $row->class_name : '-') ?>"
                                            data-admission-fees="<?= e($row->admission_fees) ?>"
                                            data-monthly-fees="<?= e($row->monthly_fees) ?>"
                                            data-unit-id="<?= $row->unit_id ?>"
                                            data-current-class-id="<?= $currentClassId ?>">
                                        Promote
                                    </button>
                                    |
                                    <!-- <a href="javascript:void(0);" onclick="showConfirmBox('<?= $encoded_id ?>', '<?= $delete_url ?>', 'This record will be permanently deleted. Do you want to proceed?')" class="text-danger" title="Delete <?= $module['title'] ?>"><i class="fa fa-trash text-danger"></i></a> -->
                                    <!-- <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#studentDetails<?= $row->id ?>">
                                        <i class="fa fa-eye text-success"></i>
                                    </a> -->
                                    <a href="javascript:void(0);" class="viewStudentBtn text-info" data-id="<?= Helper::encoded($row->id) ?>" title="View <?= $module['title'] ?>">
                                        <i class="fa fa-eye text-info"></i>
                                    </a>
                                    |
                                    <a href="<?= url($controllerRoute . '/student-print/' . Helper::encoded($row->id)) ?>" class="text-primary" target="_blank" title="Print <?= $module['title'] ?>">
                                        <i class="fa fa-print text-primary"></i>
                                    </a>
                                    |
                                    <a href="<?= url($controllerRoute . '/student-pdf/' . Helper::encoded($row->id)) ?>" class="text-danger" target="_blank" title="PDF <?= $module['title'] ?>">
                                        <i class="fa-solid fa-file-pdf text-danger"></i>
                                    </a>
                                </td>
                            </tr>
                    <?php }
                    } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="studentDetailsModal" tabindex="-1" aria-labelledby="studentDetailsLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-centered">
        <div class="modal-content" id="studentDetailsContent">
            
        </div>
    </div>
</div>

<div class="modal fade" id="studentPromoteModal" tabindex="-1" aria-labelledby="studentPromoteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="studentPromoteLabel">Promote Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="studentPromoteForm" method="POST" action="{{ route('student.promote') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="student_id" id="promote_student_id" value="{{ old('student_id') }}">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="promote_student_name" class="form-label">Student Name</label>
                            <input type="text" class="form-control form-control-sm" id="promote_student_name" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="promote_present_class" class="form-label">Present Class</label>
                            <input type="text" class="form-control form-control-sm" id="promote_present_class" readonly>
                        </div>
                        <div class="col-md-4">
                            <label for="promote_admission_fees" class="form-label">Session Fee <span class="text-danger">*</span></label>
                            <input type="number" step="1" min="1" class="form-control form-control-sm" name="admission_fees" id="promote_admission_fees" value="{{ old('admission_fees') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="promote_monthly_fees" class="form-label">Monthly Fee <span class="text-danger">*</span></label>
                            <input type="number" step="1" min="1" class="form-control form-control-sm" name="monthly_fees" id="promote_monthly_fees" value="{{ old('monthly_fees') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="promote_payment_mode" class="form-label">Payment Mode <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" name="payment_mode" id="promote_payment_mode" required>
                                <option value="Cash" {{ ((string)old('payment_mode', 'Bank') === 'Cash') ? 'selected' : '' }}>Cash</option>
                                <option value="Bank" {{ ((string)old('payment_mode', 'Bank') === 'Bank') ? 'selected' : '' }}>Bank</option>
                            </select>
                            <input type="hidden" name="ledger_id" id="promote_ledger_id" value="{{ old('ledger_id', 3) }}">
                        </div>
                        <div class="col-md-4">
                            <label for="promoted_class_id" class="form-label">Promoted To Class <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" name="promoted_class_id" id="promoted_class_id" required>
                                <option value="">Select</option>
                            </select>
                        </div>
                        <div class="col-md-6 payment-bank-group d-none" id="promote_bank_account_group">
                            <label for="promote_bank_account_id" class="form-label">Bank Account <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" name="bank_account_id" id="promote_bank_account_id" disabled>
                                <option value="">Select Bank Account</option>
                                @forelse($bankAccounts as $bankAccountRow)
                                    <option value="{{ $bankAccountRow->id }}" {{ ((string)old('bank_account_id') === (string)$bankAccountRow->id) ? 'selected' : '' }}>
                                        {{ $bankAccountRow->bank_name }}{{ ($bankAccountRow->account_no != '') ? ' (' . $bankAccountRow->account_no . ')' : '' }}
                                    </option>
                                @empty
                                    <option value="">No bank accounts available</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="col-md-6 payment-reference-group d-none" id="promote_payment_reference_group">
                            <label for="promote_payment_reference" class="form-label">Payment Reference <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control form-control-sm"
                                   name="payment_reference"
                                   id="promote_payment_reference"
                                   value="{{ old('payment_reference') }}"
                                   placeholder="UTR / cheque number / transaction ID"
                                   autocomplete="off"
                                   disabled>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-info py-2 mb-0">
                                The promoted class options are based on the student unit.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning btn-sm">Promote</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="studentSpecialFeeModal" tabindex="-1" aria-labelledby="studentSpecialFeeLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="studentSpecialFeeLabel">Special Fee Collection</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="studentSpecialFeeForm" method="POST" action="{{ route('student.special-fee.collect') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="special_fee_student_id" id="special_fee_student_id" value="{{ old('special_fee_student_id') }}">
                    <input type="hidden" name="special_fee_type" id="special_fee_type" value="{{ old('special_fee_type') }}">
                    <input type="hidden" name="special_fee_ledger_id" id="special_fee_ledger_id" value="{{ old('special_fee_ledger_id', 4) }}">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="special_fee_student_name" class="form-label">Student Name</label>
                            <input type="text" class="form-control form-control-sm" id="special_fee_student_name" readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="special_fee_student_serial" class="form-label">Student Serial No</label>
                            <input type="text" class="form-control form-control-sm" id="special_fee_student_serial" readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="special_fee_session_year" class="form-label">Current Session Year</label>
                            <input type="text" class="form-control form-control-sm" id="special_fee_session_year" readonly>
                        </div>
                        <div class="col-md-4">
                            <label for="special_fee_payment_mode" class="form-label">Payment Mode <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" name="special_fee_payment_mode" id="special_fee_payment_mode" required>
                                <option value="Cash" {{ ((string)old('special_fee_payment_mode', 'Bank') === 'Cash') ? 'selected' : '' }}>Cash</option>
                                <option value="Bank" {{ ((string)old('special_fee_payment_mode', 'Bank') === 'Bank') ? 'selected' : '' }}>Bank</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="special_fee_amount" class="form-label">Fee Amount <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" class="form-control form-control-sm" name="special_fee_amount" id="special_fee_amount" value="{{ old('special_fee_amount') }}" required>
                        </div>
                        <div class="col-md-6 payment-bank-group d-none" id="special_fee_bank_account_group">
                            <label for="special_fee_bank_account_id" class="form-label">Bank Account <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" name="bank_account_id" id="special_fee_bank_account_id" disabled>
                                <option value="">Select Bank Account</option>
                                @forelse($bankAccounts as $bankAccountRow)
                                    <option value="{{ $bankAccountRow->id }}" {{ ((string)old('bank_account_id') === (string)$bankAccountRow->id) ? 'selected' : '' }}>
                                        {{ $bankAccountRow->bank_name }}{{ ($bankAccountRow->account_no != '') ? ' (' . $bankAccountRow->account_no . ')' : '' }}
                                    </option>
                                @empty
                                    <option value="">No bank accounts available</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="col-md-6 payment-reference-group d-none" id="special_fee_payment_reference_group">
                            <label for="special_fee_payment_reference" class="form-label">Payment Reference <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control form-control-sm"
                                   name="payment_reference"
                                   id="special_fee_payment_reference"
                                   value="{{ old('payment_reference') }}"
                                   placeholder="UTR / cheque number / transaction ID"
                                   autocomplete="off"
                                   disabled>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-info py-2 mb-0">
                                The selected fee will be saved on the student record and logged as an income transaction.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success btn-sm" id="special_fee_submit_btn">
                        <span class="special-fee-submit-label">Collect Fee</span>
                        <i class="fa-solid fa-spinner fa-spin special-fee-submit-loader d-none"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('scripts')
    <script>
    document.addEventListener("click", function (e) {

        let btn = e.target.closest(".viewStudentBtn");

        if (btn) {

            let studentId = btn.dataset.id;
            let modalElement = document.getElementById('studentDetailsModal');

            if (!modalElement) {
                console.error("Modal not found!");
                return;
            }

            let modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            modal.show();

            

            fetch("{{ url('student/details') }}/" + studentId)
                .then(response => response.json())
                .then(data => {
                    document.getElementById("studentDetailsContent").innerHTML = data.html;
                })
                .catch(error => {
                    document.getElementById("studentDetailsContent").innerHTML =
                        `<div class="alert alert-danger">Something went wrong.</div>`;
                });
        }
    });
    </script>
    <script>
        const promoteVhsClasses = @json($vhs_classes);
        const promoteTsaClasses = @json($tsa_classes);
        const promoteOldData = {
            studentId: @json(old('student_id')),
            admissionFees: @json(old('admission_fees')),
            monthlyFees: @json(old('monthly_fees')),
            paymentMode: @json(old('payment_mode', 'Bank')),
            bankAccountId: @json(old('bank_account_id')),
            paymentReference: @json(old('payment_reference')),
            ledgerId: @json(old('ledger_id', 3)),
            promotedClassId: @json(old('promoted_class_id')),
        };

        function toggleBankPaymentFields(paymentMode, bankGroupSelector, bankInputSelector, referenceGroupSelector, referenceInputSelector) {
            const shouldShow = (paymentMode === 'Bank');
            const bankGroup = document.querySelector(bankGroupSelector);
            const bankInput = document.querySelector(bankInputSelector);
            const referenceGroup = document.querySelector(referenceGroupSelector);
            const referenceInput = document.querySelector(referenceInputSelector);

            if (bankGroup && bankInput) {
                bankGroup.classList.toggle('d-none', !shouldShow);
                bankInput.disabled = !shouldShow;
                bankInput.required = shouldShow;
            }

            if (referenceGroup && referenceInput) {
                referenceGroup.classList.toggle('d-none', !shouldShow);
                referenceInput.disabled = !shouldShow;
                referenceInput.required = shouldShow;
            }
        }

        function populatePromotedClassOptions(unitId, selectedClassId) {
            const classSelect = document.getElementById('promoted_class_id');
            if (!classSelect) {
                return;
            }

            const classes = (String(unitId) === '1') ? promoteVhsClasses : ((String(unitId) === '2') ? promoteTsaClasses : []);

            classSelect.innerHTML = '';

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Select';
            classSelect.appendChild(defaultOption);

            (classes || []).forEach(function (classRow) {
                const option = document.createElement('option');
                option.value = String(classRow.id);
                option.textContent = classRow.name;

                if (selectedClassId !== undefined && selectedClassId !== null && String(selectedClassId) === String(classRow.id)) {
                    option.selected = true;
                }

                classSelect.appendChild(option);
            });
        }

        function openPromoteModal(btn, overrideData) {
            const promoteForm = document.getElementById('studentPromoteForm');
            const modalElement = document.getElementById('studentPromoteModal');
            const studentNameField = document.getElementById('promote_student_name');
            const presentClassField = document.getElementById('promote_present_class');
            const admissionFeesField = document.getElementById('promote_admission_fees');
            const monthlyFeesField = document.getElementById('promote_monthly_fees');
            const paymentModeField = document.getElementById('promote_payment_mode');
            const bankAccountField = document.getElementById('promote_bank_account_id');
            const paymentReferenceField = document.getElementById('promote_payment_reference');
            const ledgerIdField = document.getElementById('promote_ledger_id');
            const studentIdField = document.getElementById('promote_student_id');
            const classSelect = document.getElementById('promoted_class_id');

            const studentData = {
                studentId: btn.dataset.studentId || '',
                studentName: btn.dataset.studentName || '',
                presentClassName: btn.dataset.presentClassName || '',
                admissionFees: btn.dataset.admissionFees || '',
                monthlyFees: btn.dataset.monthlyFees || '',
                unitId: btn.dataset.unitId || '',
                currentClassId: btn.dataset.currentClassId || '',
            };

            const data = overrideData || {};

            promoteForm.action = "{{ route('student.promote') }}";
            studentIdField.value = studentData.studentId;
            studentNameField.value = studentData.studentName;
            presentClassField.value = studentData.presentClassName;
            admissionFeesField.value = (data.admissionFees !== undefined && data.admissionFees !== null) ? data.admissionFees : studentData.admissionFees;
            monthlyFeesField.value = (data.monthlyFees !== undefined && data.monthlyFees !== null) ? data.monthlyFees : studentData.monthlyFees;
            if (paymentModeField) {
                paymentModeField.value = (data.paymentMode !== undefined && data.paymentMode !== null && data.paymentMode !== '') ? data.paymentMode : 'Bank';
            }
            if (bankAccountField) {
                bankAccountField.value = (data.bankAccountId !== undefined && data.bankAccountId !== null && data.bankAccountId !== '') ? data.bankAccountId : '';
            }
            if (paymentReferenceField) {
                paymentReferenceField.value = (data.paymentReference !== undefined && data.paymentReference !== null) ? data.paymentReference : '';
            }
            if (ledgerIdField) {
                ledgerIdField.value = (data.ledgerId !== undefined && data.ledgerId !== null && data.ledgerId !== '') ? data.ledgerId : '3';
            }

            const selectedClassId = (data.promotedClassId !== undefined && data.promotedClassId !== null) ? data.promotedClassId : '';
            populatePromotedClassOptions(studentData.unitId, selectedClassId);

            toggleBankPaymentFields(
                paymentModeField ? paymentModeField.value : 'Cash',
                '#promote_bank_account_group',
                '#promote_bank_account_id',
                '#promote_payment_reference_group',
                '#promote_payment_reference'
            );

            if (classSelect && !selectedClassId) {
                classSelect.value = '';
            }

            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            modal.show();
        }

        document.addEventListener("click", function (e) {
            const promoteBtn = e.target.closest(".promoteStudentBtn");
            if (!promoteBtn) {
                return;
            }

            openPromoteModal(promoteBtn);
        });

        window.addEventListener('load', function () {
            if (!promoteOldData.studentId) {
                return;
            }

            const promoteBtn = document.querySelector('.promoteStudentBtn[data-student-id="' + promoteOldData.studentId + '"]');
            if (!promoteBtn) {
                return;
            }

            openPromoteModal(promoteBtn, promoteOldData);
        });

        if (document.getElementById('promote_payment_mode')) {
            document.getElementById('promote_payment_mode').addEventListener('change', function () {
                toggleBankPaymentFields(
                    this.value,
                    '#promote_bank_account_group',
                    '#promote_bank_account_id',
                    '#promote_payment_reference_group',
                    '#promote_payment_reference'
                );
            });
        }
    </script>
    <script>
        const specialFeeOldData = {
            studentId: @json(old('special_fee_student_id')),
            feeType: @json(old('special_fee_type')),
            paymentMode: @json(old('special_fee_payment_mode', 'Bank')),
            bankAccountId: @json(old('bank_account_id')),
            paymentReference: @json(old('payment_reference')),
            ledgerId: @json(old('special_fee_ledger_id', 4)),
            feeAmount: @json(old('special_fee_amount')),
        };

        function openSpecialFeeModal(btn, overrideData) {
            const modalElement = document.getElementById('studentSpecialFeeModal');
            const feeForm = document.getElementById('studentSpecialFeeForm');
            const modalTitle = document.getElementById('studentSpecialFeeLabel');
            const studentIdField = document.getElementById('special_fee_student_id');
            const feeTypeField = document.getElementById('special_fee_type');
            const ledgerIdField = document.getElementById('special_fee_ledger_id');
            const studentNameField = document.getElementById('special_fee_student_name');
            const studentSerialField = document.getElementById('special_fee_student_serial');
            const sessionYearField = document.getElementById('special_fee_session_year');
            const paymentModeField = document.getElementById('special_fee_payment_mode');
            const bankAccountField = document.getElementById('special_fee_bank_account_id');
            const paymentReferenceField = document.getElementById('special_fee_payment_reference');
            const amountField = document.getElementById('special_fee_amount');
            const submitButton = document.getElementById('special_fee_submit_btn');

            const studentData = {
                studentId: btn.dataset.studentId || '',
                studentName: btn.dataset.studentName || '',
                studentSerial: btn.dataset.studentSerial || '',
                sessionName: btn.dataset.sessionName || '-',
                booksFee: btn.dataset.booksFee || '',
                uniformFee: btn.dataset.uniformFee || '',
                feeType: btn.dataset.feeType || '',
                feeLabel: btn.dataset.feeLabel || 'Fee',
            };

            const data = overrideData || {};
            const feeLabel = studentData.feeLabel;
            const defaultAmount = (studentData.feeType === 'books') ? studentData.booksFee : studentData.uniformFee;

            feeForm.action = "{{ route('student.special-fee.collect') }}";
            modalTitle.textContent = feeLabel + ' Collection';
            studentIdField.value = studentData.studentId;
            feeTypeField.value = studentData.feeType;
            if (ledgerIdField) {
                ledgerIdField.value = (data.ledgerId !== undefined && data.ledgerId !== null && data.ledgerId !== '') ? data.ledgerId : '4';
            }
            studentNameField.value = studentData.studentName;
            studentSerialField.value = studentData.studentSerial;
            sessionYearField.value = studentData.sessionName;
            if (paymentModeField) {
                paymentModeField.value = (data.paymentMode !== undefined && data.paymentMode !== null && data.paymentMode !== '') ? data.paymentMode : 'Bank';
            }
            if (bankAccountField) {
                bankAccountField.value = (data.bankAccountId !== undefined && data.bankAccountId !== null && data.bankAccountId !== '') ? data.bankAccountId : '';
            }
            if (paymentReferenceField) {
                paymentReferenceField.value = (data.paymentReference !== undefined && data.paymentReference !== null) ? data.paymentReference : '';
            }
            amountField.value = (data.feeAmount !== undefined && data.feeAmount !== null && data.feeAmount !== '') ? data.feeAmount : defaultAmount;
            if (submitButton) {
                const submitLabel = submitButton.querySelector('.special-fee-submit-label');
                const submitLoader = submitButton.querySelector('.special-fee-submit-loader');
                if (submitLabel) {
                    submitLabel.textContent = 'Collect ' + feeLabel;
                }
                if (submitLoader) {
                    submitLoader.classList.add('d-none');
                }
                submitButton.disabled = false;
            }

            toggleBankPaymentFields(
                paymentModeField ? paymentModeField.value : 'Cash',
                '#special_fee_bank_account_group',
                '#special_fee_bank_account_id',
                '#special_fee_payment_reference_group',
                '#special_fee_payment_reference'
            );

            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            modal.show();
        }

        document.addEventListener("click", function (e) {
            const feeBtn = e.target.closest(".feeCollectionBtn");
            if (!feeBtn) {
                return;
            }

            openSpecialFeeModal(feeBtn);
        });

        window.addEventListener('load', function () {
            if (!specialFeeOldData.studentId || !specialFeeOldData.feeType) {
                return;
            }

            const feeBtn = document.querySelector('.feeCollectionBtn[data-student-id="' + specialFeeOldData.studentId + '"][data-fee-type="' + specialFeeOldData.feeType + '"]');
            if (!feeBtn) {
                return;
            }

            openSpecialFeeModal(feeBtn, specialFeeOldData);
        });

        if (document.getElementById('special_fee_payment_mode')) {
            document.getElementById('special_fee_payment_mode').addEventListener('change', function () {
                toggleBankPaymentFields(
                    this.value,
                    '#special_fee_bank_account_group',
                    '#special_fee_bank_account_id',
                    '#special_fee_payment_reference_group',
                    '#special_fee_payment_reference'
                );
            });
        }

        if (document.getElementById('studentSpecialFeeForm')) {
            document.getElementById('studentSpecialFeeForm').addEventListener('submit', function () {
                const submitButton = document.getElementById('special_fee_submit_btn');
                if (!submitButton) {
                    return;
                }

                const submitLabel = submitButton.querySelector('.special-fee-submit-label');
                const submitLoader = submitButton.querySelector('.special-fee-submit-loader');
                submitButton.disabled = true;
                if (submitLabel) {
                    submitLabel.textContent = 'Processing';
                }
                if (submitLoader) {
                    submitLoader.classList.remove('d-none');
                }
            });
        }
    </script>
@endsection
