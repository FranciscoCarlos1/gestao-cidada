<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prefeitura extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome', 'slug', 'cnpj', 'email_contato', 'telefone', 'cidade', 'uf'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function problemas()
    {
        return $this->hasMany(Problema::class);
    }
}
