<?php
/**
 * CareTrack configuration.
 * Set DB_DRIVER=mysql for production (see sql/schema.mysql.sql) or sqlite for local/dev/tests.
 * Values can be overridden with environment variables.
 */
declare(strict_types=1);

return [
    'db' => [
        'driver'   => getenv('DB_DRIVER') ?: 'sqlite',
        // MySQL
        'host'     => getenv('DB_HOST') ?: '127.0.0.1',
        'port'     => getenv('DB_PORT') ?: '3306',
        'name'     => getenv('DB_NAME') ?: 'caretrack',
        'user'     => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        // SQLite
        'sqlite_path' => getenv('DB_SQLITE_PATH') ?: __DIR__ . '/../storage/caretrack.sqlite',
    ],
    'app' => [
        'name'         => 'CareTrack',
        'session_name' => 'caretrack_session',
    ],
];
