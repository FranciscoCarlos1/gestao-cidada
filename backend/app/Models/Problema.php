<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Problema extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'descricao',
        'bairro',
        'rua',
        'numero',
        'complemento',
        'cep',
        'cidade',
        'uf',
        'latitude',
        'longitude',
        'status',
        'prefeitura_id',
        'user_id',
    ];

    public function prefeitura()
    {
        return $this->belongsTo(Prefeitura::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
