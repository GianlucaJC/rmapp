<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'start_date',
        'end_date',
        'is_suspended',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_suspended' => 'boolean',
    ];
}