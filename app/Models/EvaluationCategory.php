<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EvaluationCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_template_id',
        'name',
        'description',
        'weight',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'weight' => 'decimal:2',
        'order' => 'integer',
    ];

    public function template()
    {
        return $this->belongsTo(EvaluationTemplate::class, 'evaluation_template_id');
    }

    public function questions()
    {
        return $this->hasMany(EvaluationQuestion::class)->orderBy('order');
    }
}
