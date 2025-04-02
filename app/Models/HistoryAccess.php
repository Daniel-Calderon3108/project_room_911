<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryAccess extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'employee_name_complete',
        'success',
        'reason'
    ];

    /**
     * Relationship with employee
     * @return BelongsTo
     */
    public function employee() {
        return $this->belongsTo(Employee::class);
    }
}