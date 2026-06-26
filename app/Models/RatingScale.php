<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RatingScale extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_template_id',
        'scale_value',
        'label',
        'description',
    ];

    protected $casts = [
        'scale_value' => 'integer',
    ];

    public function template()
    {
        return $this->belongsTo(EvaluationTemplate::class, 'evaluation_template_id');
    }
}
