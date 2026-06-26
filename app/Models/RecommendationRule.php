<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecommendationRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'interpretation_rule_id',
        'recommendation',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function interpretationRule()
    {
        return $this->belongsTo(InterpretationRule::class);
    }
}
