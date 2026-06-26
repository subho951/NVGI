<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryHead extends Model
{
    use SoftDeletes;

    public const TYPE_EARNING = 'EARNING';
    public const TYPE_DEDUCTION = 'DEDUCTION';

    public const CALCULATION_TYPE_FLAT = 'FLAT';
    public const CALCULATION_TYPE_PERCENTAGE = 'PERCENTAGE';

    public const BASE_FIXED_AMOUNT = 'FIXED_AMOUNT';
    public const BASE_BASIC = 'BASIC';
    public const BASE_GROSS_SALARY = 'GROSS_SALARY';
    public const BASE_GROSS_BALANCE_AFTER_BASIC_HRA_DIVISOR = 'GROSS_BALANCE_AFTER_BASIC_HRA_DIVISOR';

    protected $table = 'salary_heads';

    protected $fillable = [
        'type',
        'name',
        'short_description',
        'calculation_type',
        'calculation_base',
        'calculation_amount',
        'is_payslip_show',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'calculation_amount' => 'decimal:2',
        'is_payslip_show' => 'boolean',
    ];

    public static function typeOptions(): array
    {
        return [
            self::TYPE_EARNING => 'EARNING',
            self::TYPE_DEDUCTION => 'DEDUCTION',
        ];
    }

    public static function calculationTypeOptions(): array
    {
        return [
            self::CALCULATION_TYPE_FLAT => 'FLAT',
            self::CALCULATION_TYPE_PERCENTAGE => 'PERCENTAGE',
        ];
    }

    public static function calculationBaseOptions(): array
    {
        return [
            self::BASE_FIXED_AMOUNT => 'Fixed Amount',
            self::BASE_BASIC => 'Basic',
            self::BASE_GROSS_SALARY => 'Gross Salary',
            self::BASE_GROSS_BALANCE_AFTER_BASIC_HRA_DIVISOR => 'Gross Balance After Basic + HRA',
        ];
    }

    public static function flatCalculationBases(): array
    {
        return [
            self::BASE_FIXED_AMOUNT,
            self::BASE_GROSS_BALANCE_AFTER_BASIC_HRA_DIVISOR,
        ];
    }

    public static function percentageCalculationBases(): array
    {
        return [
            self::BASE_BASIC,
            self::BASE_GROSS_SALARY,
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeOptions()[$this->type] ?? (string) $this->type;
    }

    public function getCalculationTypeLabelAttribute(): string
    {
        return self::calculationTypeOptions()[$this->calculation_type] ?? (string) $this->calculation_type;
    }

    public function getCalculationBaseLabelAttribute(): string
    {
        return self::calculationBaseOptions()[$this->calculation_base] ?? (string) $this->calculation_base;
    }

    public function getFormulaLabelAttribute(): string
    {
        $amount = self::formatAmount($this->calculation_amount);

        if ($this->calculation_base === self::BASE_FIXED_AMOUNT) {
            return 'Flat amount = '.$amount;
        }

        if ($this->calculation_base === self::BASE_BASIC) {
            return 'Basic x '.$amount.'%';
        }

        if ($this->calculation_base === self::BASE_GROSS_SALARY) {
            return 'Gross Salary x '.$amount.'%';
        }

        if ($this->calculation_base === self::BASE_GROSS_BALANCE_AFTER_BASIC_HRA_DIVISOR) {
            return '(Gross Salary - (Basic + HRA)) / '.$amount;
        }

        return $this->calculation_base_label;
    }

    public static function formatAmount($value): string
    {
        $formatted = number_format((float) $value, 2, '.', '');

        return rtrim(rtrim($formatted, '0'), '.');
    }
}
