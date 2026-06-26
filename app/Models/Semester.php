<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Semester extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'academic_year_id',
        'name',
        'term',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function studentEnrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function facultyAssignments()
    {
        return $this->hasMany(FacultyAssignment::class);
    }
}




