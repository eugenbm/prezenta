<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $instance = null;

    public static function connection(): PDO
    {
        if (self::$instance === null) {
            $config = config('db');
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['port'] ?? '3306',
                $config['database'],
                $config['charset']
            );

            try {
                self::$instance = new PDO($dsn, $config['username'], $config['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                // Nu expune niciodată parola în producție — doar mesajul PDO merge în log.
                error_log('Eroare conexiune bază de date: ' . $e->getMessage());
                if (!headers_sent()) {
                    http_response_code(500);
                }
                render_fatal_error_page('Conectare eșuată la baza de date: ' . $e->getMessage());
                exit;
            }
        }

        return self::$instance;
    }
}
