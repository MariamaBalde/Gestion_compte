<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class Client extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'client'; 
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'titulaire',
        'nci',
        'email',
        'telephone',
        'adresse',
    ];

    protected $hidden = [ ];

    public function comptes()
    {
        return $this->hasMany(Compte::class, 'client_id');
    }
}
