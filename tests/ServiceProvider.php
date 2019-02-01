<?php

namespace Cruxinator\OQGraphLaravel\Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use PDO;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register(): void
    {
        //register
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations/');
    }

    protected function loadMigrationsFrom($path): void
    {
        $_ENV['type'] = 'mysql';  //sqlite, mysql, pgsql
        $database = DB::getDatabaseName();
        //$pdo = \DB::connection()->getPdo();
        $pdo = $this->getPDOConnection(
            Config::get('database.connections.mysql.host'),
            Config::get('database.connections.mysql.port', 3306),
            Config::get('database.connections.mysql.username'),
            Config::get('database.connections.mysql.password')
        );
        $pdo->exec(sprintf(
            'DROP DATABASE %s;',
        $database
            ));
        $pdo->exec(sprintf(
            'CREATE DATABASE IF NOT EXISTS %s;',
            $database
        ));

        Artisan::call('migrate', ['--database' => $_ENV['type']]);

        $migrator = $this->app->make('migrator');
        $migrator->run($path);
    }
    
    private function getPDOConnection(string $host, int $port, string $username, string $password): PDO
    {
        return new PDO(sprintf('mysql:host=%s;port=%d;', $host, $port), $username, $password);
    }
}
