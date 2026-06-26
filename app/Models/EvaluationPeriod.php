<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EvaluationPeriod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'academic_year_id',
        'semester_id',
        'evaluation_template_id',
        'name',
        'status',
        'start_date',
        'end_date',
        'allow_student_evaluation',
        'allow_peer_evaluation',
        'allow_supervisor_evaluation',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'allow_student_evaluation' => 'boolean',
        'allow_peer_evaluation' => 'boolean',
        'allow_supervisor_evaluation' => 'boolean',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function template()
    {
        return $this->belongsTo(EvaluationTemplate::class, 'evaluation_template_id');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
