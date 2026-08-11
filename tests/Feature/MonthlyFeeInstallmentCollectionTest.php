<?php

namespace Tests\Feature;

use App\Http\Controllers\StudentController;
use App\Models\StudentPayment;
use App\Models\Transaction;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MonthlyFeeInstallmentCollectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_id_serial');
            $table->string('full_name');
            $table->integer('session_id');
            $table->tinyInteger('status')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('student_payments', function (Blueprint $table) {
            $table->id();
            $table->integer('student_id');
            $table->integer('unit_id');
            $table->integer('branch_id');
            $table->integer('payable_month');
            $table->integer('payable_year');
            $table->decimal('payable_amount', 12, 2);
            $table->decimal('payment_amount', 12, 2)->default(0);
            $table->date('payment_date')->nullable();
            $table->decimal('due_amount', 12, 2);
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('sl_no');
            $table->string('txn_no');
            $table->integer('fee_id')->default(0);
            $table->integer('unit_id')->nullable();
            $table->integer('branch_id')->nullable();
            $table->string('payment_mode')->nullable();
            $table->integer('bank_account_id')->nullable();
            $table->text('payment_reference')->nullable();
            $table->string('type');
            $table->integer('ledger_id')->nullable();
            $table->dateTime('transaction_timestamp')->nullable();
            $table->decimal('transaction_amount', 12, 2);
            $table->text('particulars')->nullable();
            $table->text('note')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name')->nullable();
            $table->tinyInteger('status')->default(1);
        });

        DB::table('students')->insert([
            'id' => 343,
            'student_id_serial' => 'VHS-BIB-2-0343',
            'full_name' => 'Installment Student',
            'session_id' => 10,
            'status' => 1,
        ]);
        DB::table('student_payments')->insert([
            'id' => 81,
            'student_id' => 343,
            'unit_id' => 1,
            'branch_id' => 2,
            'payable_month' => 8,
            'payable_year' => 2026,
            'payable_amount' => 1449,
            'payment_amount' => 0,
            'due_amount' => 1449,
            'created_by' => 1,
            'updated_by' => 1,
        ]);
        DB::table('bank_accounts')->insert([
            'id' => 5,
            'bank_name' => 'Test Bank',
            'status' => 1,
        ]);
    }

    public function test_bank_installment_then_cash_balance_creates_two_transactions_and_clears_due(): void
    {
        $bankResponse = $this->collect([
            'payment_mode' => 'Bank',
            'bank_account_id' => 5,
            'payment_reference' => 'BANK-449',
            'payment_amount' => 449,
        ]);

        $this->assertSame(200, $bankResponse->getStatusCode());
        $bankData = $bankResponse->getData(true);
        $this->assertEquals(449, $bankData['month']['paid_numeric']);
        $this->assertEquals(1000, $bankData['month']['due_numeric']);
        $this->assertSame(1, $bankData['month']['transaction_count']);
        $this->assertDatabaseHas('student_payments', [
            'id' => 81,
            'payment_amount' => 449,
            'due_amount' => 1000,
        ]);
        $this->assertDatabaseHas('transactions', [
            'fee_id' => 81,
            'payment_mode' => 'Bank',
            'bank_account_id' => 5,
            'payment_reference' => 'BANK-449',
            'transaction_amount' => 449,
        ]);

        $cashResponse = $this->collect([
            'payment_mode' => 'Cash',
            'payment_amount' => 1000,
        ]);

        $this->assertSame(200, $cashResponse->getStatusCode());
        $cashData = $cashResponse->getData(true);
        $this->assertEquals(1449, $cashData['month']['paid_numeric']);
        $this->assertEquals(0, $cashData['month']['due_numeric']);
        $this->assertSame(2, $cashData['month']['transaction_count']);
        $this->assertDatabaseHas('student_payments', [
            'id' => 81,
            'payment_amount' => 1449,
            'due_amount' => 0,
        ]);
        $this->assertDatabaseHas('transactions', [
            'fee_id' => 81,
            'payment_mode' => 'Cash',
            'bank_account_id' => null,
            'payment_reference' => null,
            'transaction_amount' => 1000,
        ]);
        $this->assertSame(2, Transaction::where('fee_id', 81)->count());
        $this->assertEquals(1449, Transaction::where('fee_id', 81)->sum('transaction_amount'));
    }

    public function test_existing_partial_payment_with_zero_stored_due_accepts_the_remaining_balance(): void
    {
        StudentPayment::where('id', 81)->update([
            'payment_amount' => 449,
            'due_amount' => 0,
        ]);
        $this->createExistingTransaction(449, 'Bank');

        $response = $this->collect([
            'payment_mode' => 'Cash',
            'payment_amount' => 1000,
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertDatabaseHas('student_payments', [
            'id' => 81,
            'payment_amount' => 1449,
            'due_amount' => 0,
        ]);
        $this->assertSame(2, Transaction::where('fee_id', 81)->count());
    }

    public function test_payment_greater_than_remaining_due_is_rejected_without_a_new_transaction(): void
    {
        StudentPayment::where('id', 81)->update([
            'payment_amount' => 449,
            'due_amount' => 1000,
        ]);
        $this->createExistingTransaction(449, 'Bank');

        $response = $this->collect([
            'payment_mode' => 'Cash',
            'payment_amount' => 1000.01,
        ]);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertStringContainsString('remaining due', $response->getData(true)['message']);
        $this->assertSame(1, Transaction::where('fee_id', 81)->count());
        $this->assertDatabaseHas('student_payments', [
            'id' => 81,
            'payment_amount' => 449,
            'due_amount' => 1000,
        ]);
    }

    public function test_collection_summary_derives_remaining_due_for_a_legacy_partial_record(): void
    {
        StudentPayment::where('id', 81)->update([
            'payment_amount' => 449,
            'due_amount' => 0,
        ]);
        $this->createExistingTransaction(449, 'Bank');

        $method = new \ReflectionMethod(StudentController::class, 'buildFinancialPaymentSummarySubQuery');
        $query = $method->invoke(app(StudentController::class), 10, [
            ['month' => 8, 'year' => 2026, 'alias' => 'aug'],
        ]);
        $summary = $query->first();

        $this->assertEquals(1449, $summary->aug_payable);
        $this->assertEquals(449, $summary->aug_paid);
        $this->assertEquals(1000, $summary->aug_due);
        $this->assertEquals(1, $summary->aug_txn_count);
        $this->assertEquals(449, $summary->aug_txn_amount);
        $this->assertEquals(1000, $summary->total_due);

        $dueMethod = new \ReflectionMethod(StudentController::class, 'buildFinancialDueSubQuery');
        $dueQuery = $dueMethod->invoke(app(StudentController::class), 10, [
            ['month' => 8, 'year' => 2026, 'alias' => 'aug_due'],
        ]);
        $dueSummary = $dueQuery->first();

        $this->assertEquals(1000, $dueSummary->aug_due);
        $this->assertEquals(1000, $dueSummary->total_due);
    }

    private function collect(array $overrides)
    {
        $request = Request::create('/student/fees-collection/update', 'POST', array_merge([
            'student_id' => 343,
            'payable_month' => 8,
            'payable_year' => 2026,
            'payment_mode' => 'Cash',
            'ledger_id' => 1,
            'payment_amount' => 1,
        ], $overrides));
        $request->setLaravelSession(app('session')->driver());
        $request->session()->put('user_data', ['user_id' => 17]);

        return app(StudentController::class)->updateFeesCollection($request);
    }

    private function createExistingTransaction(float $amount, string $paymentMode): Transaction
    {
        return Transaction::create([
            'sl_no' => 1,
            'txn_no' => '00000001',
            'fee_id' => 81,
            'unit_id' => 1,
            'branch_id' => 2,
            'payment_mode' => $paymentMode,
            'bank_account_id' => (($paymentMode === 'Bank') ? 5 : null),
            'payment_reference' => (($paymentMode === 'Bank') ? 'BANK-449' : null),
            'type' => 'INCOME',
            'ledger_id' => 1,
            'transaction_timestamp' => now(),
            'transaction_amount' => $amount,
            'particulars' => 'Fees collection for Installment Student (VHS-BIB-2-0343) August 2026',
            'note' => 'Monthly fees collection',
            'status' => 1,
            'created_by' => 17,
            'updated_by' => 17,
        ]);
    }
}
