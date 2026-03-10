<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    private static bool $testDatabaseEnsured = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (self::$testDatabaseEnsured) {
            return;
        }

        $connection = config('database.default');
        if ($connection !== 'mysql') {
            return;
        }

        $config = config('database.connections.mysql');
        $database = $config['database'] ?? null;
        if (empty($database) || $database === ':memory:') {
            return;
        }

        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;charset=%s',
                $config['host'] ?? '127.0.0.1',
                $config['port'] ?? 3306,
                $config['charset'] ?? 'utf8mb4'
            );
            $pdo = new \PDO($dsn, $config['username'] ?? 'root', $config['password'] ?? '', [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]);
            $name = str_replace('`', '``', $database);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}`");
        } catch (\Throwable $e) {
            // If we cannot create (e.g. no permission), tests will fail with a clear DB error
        }

        self::$testDatabaseEnsured = true;
    }
}

