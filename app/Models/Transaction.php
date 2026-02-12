<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'transactions';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'compte_id',
        'type_transaction',
        'montant',
        'devise',
        'reference',
        'solde_apres',
        'statut_transaction',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // UUID si absent
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            // Génération de la reference si absent
            if (empty($model->reference)) {
                $model->reference = self::generateReference();
            }
        });
    }

    private static function generateReference(): string
    {
        // Format exemple: TYYYYMMDD + increment sur les 6 derniers chiffres
        $datePrefix = now()->format('Ymd');
        $last = self::where('reference', 'like', "T{$datePrefix}%")
                    ->orderBy('reference', 'desc')
                    ->first();

        if (! $last) {
            $counter = 1;
        } else {
            // Récupère la partie numérique après le préfixe (ex: T20251023000001)
            $num = (int) substr($last->reference, strlen("T{$datePrefix}"));
            $counter = $num + 1;
        }

        return 'T' . $datePrefix . str_pad($counter, 6, '0', STR_PAD_LEFT);
    }

    /** Relation vers Compte */
    public function compte()
    {
        return $this->belongsTo(Compte::class, 'compte_id');
    }
}
