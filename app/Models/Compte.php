<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

class Compte extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'compte';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'client_id',
        'numero_compte',
        'type_compte',
        'solde',
        'devise',
        'statut',
    ];

    protected $casts = [
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            // Génération automatique du numero_compte
            if (empty($model->numero_compte)) {
                $model->numero_compte = self::generateAccountNumber();
            }
        });
    }

     // Génération du numéro de compte unique
   

    private static function generateAccountNumber(): string
{
    $lastCompte = self::orderBy('numero_compte', 'desc')->first();
    $lastNumber = $lastCompte ? (int) substr($lastCompte->numero_compte, 1) : 0;
    $newNumber = $lastNumber + 1;

    return 'C' . str_pad($newNumber, 8, '0', STR_PAD_LEFT);
}

    /** Relation vers Client (Utilisateur) */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
