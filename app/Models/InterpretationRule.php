<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InterpretationRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_template_id',
        'label',
        'min_score',
        'max_score',
        'description',
        'color_code',
        'order',
    ];

    protected $casts = [
        'min_score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'order' => 'integer',
    ];

    public function template()
    {
        return $this->belongsTo(EvaluationTemplate::class, 'evaluation_template_id');
    }

    public function recommendationRules()
    {
        return $this->hasMany(RecommendationRule::class)->orderBy('order');
    }
}
