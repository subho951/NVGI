<?php

namespace Tests\Feature;

use App\Http\Controllers\StudentController;
use App\Models\Student;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StudentSpecialFeeTransactionSyncTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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
    }

    public function test_changed_books_fee_updates_the_existing_finance_transaction(): void
    {
        $transaction = $this->createFeeTransaction('Books Fee', 'OLD-0001', 500, [
            'payment_mode' => 'Bank',
            'bank_account_id' => 9,
            'payment_reference' => 'BOOK-REF',
        ]);

        $student = $this->student('NEW-0001', 'Test Student', 2, 7);

        $this->sync($student, 'OLD-0001', 'Books Fee', 500, 650, 12);

        $transaction->refresh();
        $this->assertEquals(650.00, $transaction->transaction_amount);
        $this->assertSame('Bank', $transaction->payment_mode);
        $this->assertSame(9, $transaction->bank_account_id);
        $this->assertSame('BOOK-REF', $transaction->payment_reference);
        $this->assertSame(2, $transaction->unit_id);
        $this->assertSame(7, $transaction->branch_id);
        $this->assertSame(12, $transaction->updated_by);
        $this->assertStringContainsString('(NEW-0001)', $transaction->particulars);
        $this->assertStringContainsString('500.00 to 650.00', $transaction->note);
    }

    public function test_changed_uniform_fee_creates_a_cash_transaction_when_one_is_missing(): void
    {
        $student = $this->student('STU-0002', 'Second Student', 1, 4);

        $this->sync($student, 'STU-0002', 'Uniform Fee', 0, 800, 15);

        $this->assertDatabaseHas('transactions', [
            'fee_id' => 0,
            'unit_id' => 1,
            'branch_id' => 4,
            'ledger_id' => 4,
            'payment_mode' => 'Cash',
            'type' => 'INCOME',
            'transaction_amount' => 800,
            'status' => 1,
            'created_by' => 15,
            'updated_by' => 15,
        ]);
        $this->assertStringContainsString(
            'Uniform Fee collected for Second Student (STU-0002)',
            (string)Transaction::first()->particulars
        );
    }

    public function test_changing_a_special_fee_to_zero_removes_its_active_finance_effect(): void
    {
        $transaction = $this->createFeeTransaction('Uniform Fee', 'STU-0003', 300);

        $this->sync($this->student('STU-0003'), 'STU-0003', 'Uniform Fee', 300, 0, 21);

        $this->assertNull(Transaction::find($transaction->id));
        $deletedTransaction = Transaction::withTrashed()->findOrFail($transaction->id);
        $this->assertSame(3, $deletedTransaction->status);
        $this->assertSame(21, $deletedTransaction->updated_by);
        $this->assertNotNull($deletedTransaction->deleted_at);
    }

    public function test_unchanged_fee_does_not_modify_the_finance_transaction(): void
    {
        $transaction = $this->createFeeTransaction('Books Fee', 'STU-0004', 450, [
            'note' => 'Original transaction note',
            'updated_by' => 3,
        ]);

        $this->sync($this->student('STU-0004'), 'STU-0004', 'Books Fee', 450, 450, 99);

        $transaction->refresh();
        $this->assertSame('Original transaction note', $transaction->note);
        $this->assertSame(3, $transaction->updated_by);
    }

    private function sync(Student $student, string $originalSerial, string $feeLabel, float $originalAmount, float $newAmount, int $updatedBy): void
    {
        $method = new \ReflectionMethod(StudentController::class, 'syncStudentSpecialFeeTransaction');
        $method->invoke(
            app(StudentController::class),
            $student,
            $originalSerial,
            $feeLabel,
            $originalAmount,
            $newAmount,
            $updatedBy
        );
    }

    private function student(string $serial, string $name = 'Test Student', int $unitId = 1, int $branchId = 1): Student
    {
        return new Student([
            'student_id_serial' => $serial,
            'full_name' => $name,
            'unit_id' => $unitId,
            'branch_id' => $branchId,
        ]);
    }

    private function createFeeTransaction(string $feeLabel, string $studentSerial, float $amount, array $overrides = []): Transaction
    {
        return Transaction::create(array_merge([
            'sl_no' => 1,
            'txn_no' => '00000001',
            'fee_id' => 0,
            'unit_id' => 1,
            'branch_id' => 1,
            'payment_mode' => 'Cash',
            'bank_account_id' => null,
            'payment_reference' => null,
            'type' => 'INCOME',
            'ledger_id' => 4,
            'transaction_timestamp' => Carbon::parse('2026-08-01 10:00:00'),
            'transaction_amount' => $amount,
            'particulars' => $feeLabel . ' collected for Test Student (' . $studentSerial . ') during new admission',
            'note' => $feeLabel . ' collection',
            'status' => 1,
            'created_by' => 3,
            'updated_by' => 3,
        ], $overrides));
    }
}
