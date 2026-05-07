<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use NotificationChannels\WebPush\PushSubscription; // Importa il modello PushSubscription (corretto)
use Illuminate\Database\Eloquent\Relations\HasMany; // Spostato sotto per coerenza, ma non strettamente necessario
use NotificationChannels\WebPush\HasPushSubscriptions;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasPushSubscriptions;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'codice_fiscale',
        'phone_number',
        'contract_type',
        'job_title',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the push subscriptions for the user.
     */
    public function webPushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class); // Usa PushSubscription::class
    }
}