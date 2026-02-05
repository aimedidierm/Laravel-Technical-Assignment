<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeFactory> */
    use HasFactory;

    protected $fillable = ['name', 'email', 'position', 'phone'];

    protected static function booted(): void
    {
        static::creating(function (self $employee) {
            $latestEmployeeId = self::max('employee_id');
            $nextNumber = $latestEmployeeId
                ? (int) substr($latestEmployeeId, 4) + 1
                : 1;
            $employee->employee_id = 'EMP-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        });
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
