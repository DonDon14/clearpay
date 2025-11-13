<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 */
class Database extends Config
{
    /**
     * The directory that holds the Migrations and Seeds directories.
     */
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    /**
     * Lets you choose which connection group to use if no other is specified.
     */
    public string $defaultGroup = 'default';

    /**
     * The default database connection.
     *
     * @var array<string, mixed>
     */
    public array $default = [
        'DSN'          => '',
        'hostname'     => 'localhost',
        'username'     => 'root',
        'password'     => '',
        'database'     => 'clearpaydb',
        'DBDriver'     => 'MySQLi',
        'DBPrefix'     => '',
        'pConnect'     => false,
        'DBDebug'      => true,
        'charset'      => 'utf8mb4',
        'DBCollat'     => 'utf8mb4_general_ci',
        'swapPre'      => '',
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => false,
        'failover'     => [],
        'port'         => 3306,
        'numberNative' => false,
        'foundRows'    => false,
        'dateFormat'   => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    //    /**
    //     * Sample database connection for SQLite3.
    //     *
    //     * @var array<string, mixed>
    //     */
    //    public array $default = [
    //        'database'    => 'database.db',
    //        'DBDriver'    => 'SQLite3',
    //        'DBPrefix'    => '',
    //        'DBDebug'     => true,
    //        'swapPre'     => '',
    //        'failover'    => [],
    //        'foreignKeys' => true,
    //        'busyTimeout' => 1000,
    //        'synchronous' => null,
    //        'dateFormat'  => [
    //            'date'     => 'Y-m-d',
    //            'datetime' => 'Y-m-d H:i:s',
    //            'time'     => 'H:i:s',
    //        ],
    //    ];

    //    /**
    //     * Sample database connection for Postgre.
    //     *
    //     * @var array<string, mixed>
    //     */
    //    public array $default = [
    //        'DSN'        => '',
    //        'hostname'   => 'localhost',
    //        'username'   => 'root',
    //        'password'   => 'root',
    //        'database'   => 'ci4',
    //        'schema'     => 'public',
    //        'DBDriver'   => 'Postgre',
    //        'DBPrefix'   => '',
    //        'pConnect'   => false,
    //        'DBDebug'    => true,
    //        'charset'    => 'utf8',
    //        'swapPre'    => '',
    //        'failover'   => [],
    //        'port'       => 5432,
    //        'dateFormat' => [
    //            'date'     => 'Y-m-d',
    //            'datetime' => 'Y-m-d H:i:s',
    //            'time'     => 'H:i:s',
    //        ],
    //    ];

    //    /**
    //     * Sample database connection for SQLSRV.
    //     *
    //     * @var array<string, mixed>
    //     */
    //    public array $default = [
    //        'DSN'        => '',
    //        'hostname'   => 'localhost',
    //        'username'   => 'root',
    //        'password'   => 'root',
    //        'database'   => 'ci4',
    //        'schema'     => 'dbo',
    //        'DBDriver'   => 'SQLSRV',
    //        'DBPrefix'   => '',
    //        'pConnect'   => false,
    //        'DBDebug'    => true,
    //        'charset'    => 'utf8',
    //        'swapPre'    => '',
    //        'encrypt'    => false,
    //        'failover'   => [],
    //        'port'       => 1433,
    //        'dateFormat' => [
    //            'date'     => 'Y-m-d',
    //            'datetime' => 'Y-m-d H:i:s',
    //            'time'     => 'H:i:s',
    //        ],
    //    ];

    //    /**
    //     * Sample database connection for OCI8.
    //     *
    //     * You may need the following environment variables:
    //     *   NLS_LANG                = 'AMERICAN_AMERICA.UTF8'
    //     *   NLS_DATE_FORMAT         = 'YYYY-MM-DD HH24:MI:SS'
    //     *   NLS_TIMESTAMP_FORMAT    = 'YYYY-MM-DD HH24:MI:SS'
    //     *   NLS_TIMESTAMP_TZ_FORMAT = 'YYYY-MM-DD HH24:MI:SS'
    //     *
    //     * @var array<string, mixed>
    //     */
    //    public array $default = [
    //        'DSN'        => 'localhost:1521/XEPDB1',
    //        'username'   => 'root',
    //        'password'   => 'root',
    //        'DBDriver'   => 'OCI8',
    //        'DBPrefix'   => '',
    //        'pConnect'   => false,
    //        'DBDebug'    => true,
    //        'charset'    => 'AL32UTF8',
    //        'swapPre'    => '',
    //        'failover'   => [],
    //        'dateFormat' => [
    //            'date'     => 'Y-m-d',
    //            'datetime' => 'Y-m-d H:i:s',
    //            'time'     => 'H:i:s',
    //        ],
    //    ];

    /**
     * This database connection is used when running PHPUnit database tests.
     *
     * @var array<string, mixed>
     */
    public array $tests = [
        'DSN'         => '',
        'hostname'    => '127.0.0.1',
        'username'    => '',
        'password'    => '',
        'database'    => ':memory:',
        'DBDriver'    => 'SQLite3',
        'DBPrefix'    => 'db_',  // Needed to ensure we're working correctly with prefixes live. DO NOT REMOVE FOR CI DEVS
        'pConnect'    => false,
        'DBDebug'     => true,
        'charset'     => 'utf8',
        'DBCollat'    => '',
        'swapPre'     => '',
        'encrypt'     => false,
        'compress'    => false,
        'strictOn'    => false,
        'failover'    => [],
        'port'        => 3306,
        'foreignKeys' => true,
        'busyTimeout' => 1000,
        'dateFormat'  => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    public function __construct()
    {
        // Call parent constructor FIRST - this loads .env values
        parent::__construct();

        // AFTER parent::__construct(), check if PostgreSQL is configured
        // BaseConfig loads .env values, so $this->default['DBDriver'] should now be set from .env
        $isPostgres = false;
        
        // Check the DBDriver value (may be set from .env by parent::__construct())
        if (!empty($this->default['DBDriver']) && $this->default['DBDriver'] === 'Postgre') {
            $isPostgres = true;
        } else {
            // Fallback: check environment directly if not set yet
            if (function_exists('env')) {
                $dbDriver = env('database.default.DBDriver') ?: env('DB_DRIVER');
                if ($dbDriver === 'Postgre') {
                    $isPostgres = true;
                    $this->default['DBDriver'] = 'Postgre';
                }
            } elseif (getenv('DB_DRIVER') === 'Postgre' || getenv('database.default.DBDriver') === 'Postgre') {
                $isPostgres = true;
                $this->default['DBDriver'] = 'Postgre';
            }
        }
        
        // CRITICAL: If PostgreSQL, FORCE charset to UTF8 (PostgreSQL doesn't support utf8mb4)
        // This MUST override any value set by parent::__construct() or .env
        if ($isPostgres) {
            $this->default['DBDriver'] = 'Postgre';
            $this->default['charset'] = 'UTF8'; // PostgreSQL requires UTF8, NOT utf8mb4
            if (!isset($this->default['schema']) || empty($this->default['schema'])) {
                $this->default['schema'] = 'public';
            }
            if (!isset($this->default['port']) || $this->default['port'] == 3306) {
                $this->default['port'] = 5432;
            }
            // Remove MySQL-specific settings that PostgreSQL doesn't use
            unset($this->default['DBCollat']);
            unset($this->default['numberNative']);
            unset($this->default['foundRows']);
        }

        // Support Render.com environment variables
        // Render provides DATABASE_URL or individual DB_* variables
        if (getenv('DB_HOST')) {
            $this->default['hostname'] = getenv('DB_HOST');
        }
        if (getenv('DB_PORT')) {
            $this->default['port'] = (int) getenv('DB_PORT');
        }
        if (getenv('DB_NAME')) {
            $this->default['database'] = getenv('DB_NAME');
        }
        if (getenv('DB_USER')) {
            $this->default['username'] = getenv('DB_USER');
        }
        if (getenv('DB_PASSWORD')) {
            $this->default['password'] = getenv('DB_PASSWORD');
        }
        if (getenv('DB_DRIVER')) {
            $this->default['DBDriver'] = getenv('DB_DRIVER');
        }

        // Parse DATABASE_URL if provided (mainly for PostgreSQL on Render)
        if (getenv('DATABASE_URL')) {
            $url = parse_url(getenv('DATABASE_URL'));
            if ($url) {
                // Detect if it's PostgreSQL (postgres:// or postgresql://)
                $isPostgres = isset($url['scheme']) && 
                    (strpos($url['scheme'], 'postgres') !== false);
                
                $this->default['hostname'] = $url['host'] ?? 'localhost';
                $this->default['port'] = $url['port'] ?? ($isPostgres ? 5432 : 3306);
                $this->default['username'] = $url['user'] ?? 'root';
                $this->default['password'] = $url['pass'] ?? '';
                $this->default['database'] = ltrim($url['path'] ?? '', '/');
                
                // Auto-detect PostgreSQL driver if not explicitly set
                if ($isPostgres && !getenv('DB_DRIVER')) {
                    $this->default['DBDriver'] = 'Postgre';
                    $this->default['schema'] = 'public';
                    $this->default['charset'] = 'UTF8'; // PostgreSQL uses uppercase UTF8
                    // Remove MySQL-specific settings
                    unset($this->default['DBCollat']);
                    unset($this->default['numberNative']);
                    unset($this->default['foundRows']);
                }
            }
        }
        
        // FINAL OVERRIDE: Ensure PostgreSQL charset is ALWAYS UTF8
        // This is the LAST check - runs after ALL environment variables are loaded
        // BaseConfig may have loaded charset from .env or kept default utf8mb4
        // We MUST override it if PostgreSQL is being used
        $finalDbDriver = $this->default['DBDriver'] ?? null;
        
        // Double-check if PostgreSQL (in case it was set by environment variables above)
        if (empty($finalDbDriver) || $finalDbDriver !== 'Postgre') {
            // Check environment one more time
            if (function_exists('env')) {
                $envDriver = env('database.default.DBDriver') ?: env('DB_DRIVER');
                if ($envDriver === 'Postgre') {
                    $finalDbDriver = 'Postgre';
                    $this->default['DBDriver'] = 'Postgre';
                }
            }
        }
        
        // ABSOLUTE FINAL CHECK: If PostgreSQL, FORCE charset to UTF8
        // This overrides ANY previous charset value (including utf8mb4 from default or .env)
        if ($finalDbDriver === 'Postgre' || (!empty($this->default['DBDriver']) && $this->default['DBDriver'] === 'Postgre')) {
            // Force PostgreSQL settings - this MUST be the final word
            $this->default['DBDriver'] = 'Postgre';
            $this->default['charset'] = 'UTF8'; // PostgreSQL ONLY supports UTF8, NOT utf8mb4
            if (empty($this->default['schema'])) {
                $this->default['schema'] = 'public';
            }
            if (empty($this->default['port']) || $this->default['port'] == 3306) {
                $this->default['port'] = 5432;
            }
            // Remove MySQL-specific settings that cause issues with PostgreSQL
            unset($this->default['DBCollat']);
            unset($this->default['numberNative']);
            unset($this->default['foundRows']);
        }

        // Ensure that we always set the database group to 'tests' if
        // we are currently running an automated test suite, so that
        // we don't overwrite live data on accident.
        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }
    }
}
