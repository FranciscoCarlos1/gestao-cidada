<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prefeitura extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome', 'slug', 'cnpj', 'email_contato', 'telefone', 'cidade', 'uf'
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function problemas(): HasMany
    {
        return $this->hasMany(Problema::class);
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class);
    }

    public function triggerWebhook(string $event, array $payload): void
    {
        $webhooks = $this->webhooks()
            ->where('event', $event)
            ->where('active', true)
            ->get();

        foreach ($webhooks as $webhook) {
            // Dispatch async job para chamar o webhook
            // \App\Jobs\TriggerWebhook::dispatch($webhook, $payload);
        }
    }
}
