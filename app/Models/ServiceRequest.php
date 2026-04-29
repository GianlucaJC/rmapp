<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_type',
        'service_name',
        'service_description',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}