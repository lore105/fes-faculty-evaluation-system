<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EvaluationComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_id',
        'comment',
        'sentiment',
    ];

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }
}
