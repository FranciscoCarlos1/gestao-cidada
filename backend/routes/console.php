<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

Artisan::command('db:create', function () {
    $connection = Config::get('database.default');
    $cfg = Config::get("database.connections.$connection");
    if (!$cfg || ($cfg['driver'] ?? null) !== 'pgsql') {
        $this->error('Este comando suporta apenas Postgres (pgsql).');
        return 1;
    }

    $host = $cfg['host'] ?? '127.0.0.1';
    $port = $cfg['port'] ?? '5432';
    $database = $cfg['database'] ?? 'gestaocidada';
    $username = $cfg['username'] ?? 'postgres';
    $password = $cfg['password'] ?? '';

    try {
        $pdo = new PDO("pgsql:host={$host};port={$port};dbname=postgres", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $stmt = $pdo->prepare('SELECT 1 FROM pg_database WHERE datname = :db');
        $stmt->execute(['db' => $database]);
        if ($stmt->fetchColumn()) {
            $this->info("Banco '{$database}' já existe.");
            return 0;
        }
        $pdo->exec('CREATE DATABASE "'.str_replace('"','""',$database).'"');
        $this->info("Banco '{$database}' criado com sucesso.");
        return 0;
    } catch (Throwable $e) {
        $this->error('Falha ao criar banco: '.$e->getMessage());
        return 1;
    }
})->purpose('Cria o banco de dados Postgres definido em DB_DATABASE, se não existir');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
