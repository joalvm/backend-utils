<?php

namespace Joalvm\Utils\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Input\InputArgument;

class GenerateDatabaseCommand extends Command
{
    public const ORDER_FILES = [
        'types/',
        'enums/',
        'tables.sql',
        'functions/',
        'procedures/',
        'views/',
        'materialized_views/',
        'indexes/',
        'triggers/',
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:generate database?
        {--omit-seed : Omitir la carga de datos de ejemplo (seed) después de crear la base de datos}';

    /**
     * Nombre de la base de datos que se va a crear.
     */
    protected ?string $databaseName;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea una nueva base de datos con la estructura definida en los scripts SQL.';

    public function __construct()
    {
        $this->databaseName = $this->getDefaultDatabaseName();

        parent::__construct();

        $this->addArgument(
            'database',
            InputArgument::OPTIONAL,
            'Nombre de la base de datos que se va a crear',
            $this->databaseName
        );
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->databaseName = $this->argument('database');

        if (!$this->canContinue()) {
            return Command::INVALID;
        }

        $this->changeToPostgresDatabase();
        $this->renameOldDatabaseIfExists($this->databaseName);
        $this->createNewDatabase($this->databaseName);
        $this->loadStructure();

        $this->line(PHP_EOL);

        if ($this->option('omit-seed')) {
            return Command::SUCCESS;
        }

        Artisan::call('db:seed', [], $this->output);

        $this->info('Success!');

        return Command::SUCCESS;
    }

    private function canContinue(): bool
    {
        $config = Arr::only(
            Config::get('database.connections.' . DB::getDefaultConnection()),
            ['host', 'port', 'username']
        );

        $config['database'] = $this->databaseName;

        $this->newLine();
        $this->line('Conexión:');
        $this->table(array_keys($config), [array_values($config)]);

        return $this->confirm('¿Desea continuar?', false);
    }

    private function getDefaultDatabaseName(): string
    {
        $default = DB::getDefaultConnection();

        return Config::get("database.connections.{$default}.database");
    }

    private function loadStructure()
    {
        $scripts = $this->searchScripts();
        $progress = $this->output->createProgressBar(count($scripts));

        $progress->start();

        $this->line('- Cargando estructura de la base de datos: ' . $this->databaseName);

        foreach ($scripts as $script) {
            $file = File::get($script);

            if ($file) {
                $this->tempConn()->unprepared($file);
                $progress->advance();
                usleep(5000);
            }
        }

        $progress->finish();
    }

    private function createNewDatabase(string $databaseName)
    {
        $this->line('- Creando la nueva base de datos: ' . $databaseName);

        $this->tempConn()->statement(
            "CREATE DATABASE {$databaseName}
            WITH OWNER=postgres
            ENCODING='UTF8'
            TABLESPACE=default
            CONNECTION LIMIT=-1"
        );

        // Cambiar la conexión a la nueva base de datos
        Config::set('database.connections.pgsql_temp.database', $databaseName);

        $this->tempConn()->reconnect();
    }

    private function searchScripts(): array
    {
        $files = [];

        foreach (self::ORDER_FILES as $path) {
            $dir = $this->laravel->databasePath("scripts/{$path}");

            if (File::isFile($dir)) {
                array_push($files, $dir);
            } else {
                $files = array_merge(
                    $files,
                    File::glob("{$dir}*.sql") ?? []
                );
            }
        }

        return $files;
    }

    private function changeToPostgresDatabase()
    {
        $default = DB::getDefaultConnection();

        $this->line('- Cambiando a la base de datos por defecto.');

        Config::set('database.connections.pgsql_temp', array_merge(
            Config::get("database.connections.{$default}"),
            ['database' => 'postgres']
        ));
    }

    private function renameOldDatabaseIfExists(string $databaseName)
    {
        if (!$this->existsDatabase($databaseName)) {
            return;
        }

        $this->killConnection($databaseName);

        $this->line('- Renombrando la base de datos existente.');
        $suffix = str_replace(
            ['-', ':', ' '],
            ['', '', '_'],
            Carbon::now()->format('Y-m-d H:i:s')
        );

        $this->tempConn()->unprepared(
            "ALTER DATABASE {$databaseName}
            RENAME TO {$databaseName}_" . $suffix
        );
    }

    private function killConnection(string $databaseName)
    {
        $this->line('- Cerrando todas las conexiones de la base de datos existente.');

        $this->tempConn()->unprepared("
            SELECT pg_terminate_backend(pid)
            FROM pg_stat_activity
            WHERE pid <> pg_backend_pid()
            AND datname = '{$databaseName}'
        ");
    }

    private function existsDatabase(string $databaseName)
    {
        $result = $this->tempConn()->selectOne(
            "SELECT exists(
                SELECT 1 FROM pg_database
                WHERE datname='{$databaseName}'
            ) AS exists"
        );

        return $result->exists;
    }

    private function tempConn(): Connection
    {
        return DB::connection('pgsql_temp');
    }
}
