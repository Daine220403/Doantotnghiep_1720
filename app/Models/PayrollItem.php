<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    use HasFactory;

    protected $table = 'payroll_items';

    protected $fillable = [
        'payroll_period_id',
        'staff_id',
        'base_salary',
        'total_working_days',
        'total_overtime_hours',
        'allowances',
        'deductions',
        'bonus',
        'net_salary',
        'generated_at',
        'approved_by',
        'status',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function period()
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }
}
