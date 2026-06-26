<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Evaluation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'evaluation_period_id',
        'evaluator_id',
        'evaluatee_id',
        'evaluation_type',
        'subject_id',
        'section_id',
        'status',
        'total_score',
        'performance_rating',
        'submitted_at',
    ];

    protected $casts = [
        'total_score' => 'decimal:2',
        'submitted_at' => 'datetime',
    ];

    public function period()
    {
        return $this->belongsTo(EvaluationPeriod::class, 'evaluation_period_id');
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function evaluatee()
    {
        return $this->belongsTo(User::class, 'evaluatee_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function responses()
    {
        return $this->hasMany(EvaluationResponse::class);
    }

    public function comments()
    {
        return $this->hasMany(EvaluationComment::class);
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }
}
