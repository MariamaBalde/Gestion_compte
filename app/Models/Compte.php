<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;


class Compte extends Model
{
    use HasFactory, HasUuids,SoftDeletes;

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

    static::creating(function ($compte) {
        if (empty($compte->numero_compte)) {
            $compte->numero_compte = 'C' . str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        }
    });
}


    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($model) {
    //         if (empty($model->id)) {
    //             $model->id = (string) Str::uuid();
    //         }

    //         // Génération automatique du numero_compte
    //         if (empty($model->numero_compte)) {
    //             $model->numero_compte = self::generateAccountNumber();
    //         }
    //     });

      
    // }

      public function scopeNonArchive($query)
    {
        // Si SoftDeletes : whereNull('deleted_at')
        // Sinon, si tu as un champ 'archived' : ->where('archived', false)
        return $query->whereNull('deleted_at');
    }

    // scope local 
   public function scopeNumero(Builder $query, $numero): Builder
{
    return $query->where('numero_compte', $numero);
}

public function clientByPhone(Builder $query, $telephone): Builder
{
    return $query->whereHas('client', function ($q) use ($telephone) {
        $q->where('telephone', $telephone);
    });
}


//     private static function generateAccountNumber(): string
// {
//     $lastCompte = self::orderBy('numero_compte', 'desc')->first();
//     $lastNumber = $lastCompte ? (int) substr($lastCompte->numero_compte, 1) : 0;
//     $newNumber = $lastNumber + 1;

//     return 'C' . str_pad($newNumber, 8, '0', STR_PAD_LEFT);
// }

    /** Relation vers Client (Utilisateur) */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
