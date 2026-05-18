<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'service_type',
        'service_name',
        'service_description',
        'status',
        'id_funzionario',
        'additional_data',
        'admin_notes',
        'uploaded_documents',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $casts = [
        'additional_data' => 'array',
        'uploaded_documents' => 'array', // Aggiungi questa riga
    ];
}