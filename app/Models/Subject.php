<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'program_id',
        'name',
        'code',
        'description',
        'units',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'units' => 'integer',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}
