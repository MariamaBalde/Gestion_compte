<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

    // UUID comme clé primaire
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // si tu as des rôles
        // autres champs
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Génération automatique de l'UUID si nécessaire
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    // Mutator pour hasher le mot de passe automatiquement
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    // Exemple de relation vers Compte si nécessaire
    public function comptes()
    {
        return $this->hasMany(Compte::class, 'client_id'); // adapter si champ différent
    }
}
