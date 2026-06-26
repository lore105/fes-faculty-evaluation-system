<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EvaluationTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'status',
        'version',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'version' => 'integer',
    ];

    public function categories()
    {
        return $this->hasMany(EvaluationCategory::class)->orderBy('order');
    }

    public function ratingScales()
    {
        return $this->hasMany(RatingScale::class)->orderBy('scale_value');
    }

    public function interpretationRules()
    {
        return $this->hasMany(InterpretationRule::class)->orderBy('min_score');
    }

    public function evaluationPeriods()
    {
        return $this->hasMany(EvaluationPeriod::class);
    }
}
