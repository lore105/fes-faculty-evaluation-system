<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'middle_name',
        'name',
        'email',
        'password',
        'employee_id',
        'student_id',
        'phone',
        'gender',
        'birthdate',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birthdate' => 'date',
            'is_active' => 'boolean',
        ];
    }

    // Full name accessor
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    // Student relationships
    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function studentSections()
    {
        return $this->hasMany(StudentSection::class);
    }

    // Faculty relationships
    public function facultyAssignments()
    {
        return $this->hasMany(FacultyAssignment::class);
    }

    public function facultySections()
    {
        return $this->hasMany(FacultySection::class);
    }
}
