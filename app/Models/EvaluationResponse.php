<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EvaluationResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_id',
        'evaluation_question_id',
        'evaluation_category_id',
        'rating_value',
        'text_response',
    ];

    protected $casts = [
        'rating_value' => 'integer',
    ];

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function question()
    {
        return $this->belongsTo(EvaluationQuestion::class, 'evaluation_question_id');
    }

    public function category()
    {
        return $this->belongsTo(EvaluationCategory::class, 'evaluation_category_id');
    }
}
