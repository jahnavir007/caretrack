<?php
declare(strict_types=1);

/** PDO connection factory. Works with MySQL (production) and SQLite (dev/tests). */
final class Database
{
    private static ?PDO $pdo = null;

    public static function connect(array $cfg): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        if ($cfg['driver'] === 'mysql') {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $cfg['host'], $cfg['port'], $cfg['name']);
            self::$pdo = new PDO($dsn, $cfg['user'], $cfg['password'], $options);
        } else {
            $path = $cfg['sqlite_path'];
            if ($path !== ':memory:' && !is_dir(dirname($path))) {
                mkdir(dirname($path), 0775, true);
            }
            self::$pdo = new PDO('sqlite:' . $path, null, null, $options);
            self::$pdo->exec('PRAGMA foreign_keys = ON');
        }
        return self::$pdo;
    }

    /** Apply the schema file for the active driver (idempotent: uses IF NOT EXISTS). */
    public static function migrate(PDO $pdo, string $driver): void
    {
        $file = __DIR__ . '/../sql/schema.' . ($driver === 'mysql' ? 'mysql' : 'sqlite') . '.sql';
        $sql  = file_get_contents($file);
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
            $pdo->exec($statement);
        }
    }

    /** For tests: drop the cached connection. */
    public static function reset(): void
    {
        self::$pdo = null;
    }
}
