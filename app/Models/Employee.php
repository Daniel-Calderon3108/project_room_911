<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'department_id',
        'user_id'
    ];

    /**
     * Relationship with user
     * @return BelongsTo
     */
    public function user() {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with departament
     * @return BelongsTo
     */
    public function department() {
        return $this->belongsTo(Department::class);
    }

    /**
     * Relationship with history access
     * @return HasMany
     */
    public function historyAccess() {
        return $this->hasMany(HistoryAccess::class);
    }

    /**
     * Define attributes for the model
     * 
     * @return array<int, string>
     */
    protected function name(): Attribute
    {
        return new Attribute(
            get: fn($value) => ucwords($value),
            set: fn($value) => strtolower($value)
        );
    }

    protected function lastName(): Attribute
    {
        return new Attribute(
            get: fn($value) => ucwords($value),
            set: fn($value) => strtolower($value)
        );
    }
}
