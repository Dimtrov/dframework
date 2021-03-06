<?php 
namespace dFramework\core\db\migration;

use Throwable;
use ReflectionClass;
use dFramework\core\db\Query;
use dFramework\core\utilities\Str;
use dFramework\core\utilities\Utils;
use dFramework\core\loader\Filesystem;
use dFramework\core\exception\Exception;
use dFramework\core\utilities\Collection;

class Runner 
{
    /**
     * @var array paths to store migration files
     */
    private $paths = [
        RESOURCE_DIR . 'database' . DS . 'migrations' . DS
    ];

    /**
     * @var Filesystem
     */
    private $files;

    /**
     * @var array
     */
    private $migrations = [];
    
    /**
     * @var array migrations deja executee
     */
    private $runnedMigrations = [];
    /**
     * @var string Fichier de sauvegarde des migrations deja executee
     */
    private $runnedMigrationsFile = RESOURCE_DIR .'reserved'.DS.'.migrations.df';

    /**
     * @var Query
     */
    private $query;

    /**
     * @var self
     */
    private static $_instance;

    /**
     * Constructor
     */
    private function __construct()
    {
        if (PHP_SAPI !== 'cli') 
        {
            throw new Exception("Disponible unique via l'invite de commande", 1);
        }
        $this->files = new Filesystem;

        $this->runnedMigrations = Utils::jsonToArray($this->runnedMigrationsFile);
        if (empty($this->runnedMigrations))
        {
            $this->runnedMigrations = [];
        }
        $this->migrations = $this->getMigrations();
    }
    /**
     * Get a single instance 
     *
     * @return self
     */
    public static function instance() : self 
    {
        if (null === self::$_instance) 
        {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    

    /**
     * Run migration
     *
     * @return array
     */
    public function up() : array
    {
        return $this->getRunnedMigrations('up');
    }
    /**
     * Rollback migration
     *
     * @return array
     */
    public function down() : array
    {
        return $this->getRunnedMigrations('down');
    }

    /**
     * Lance la migration
     *
     * @param object $migration
     * @param string $dir
     * @return void
     */
    public function launch(object $migration, string $dir)
    {
        try {
            $this->files->requireOnce($migration->location);

            $instance = (new ReflectionClass($migration->class))->newInstance();
            
            $dir === 'up' ? $instance->up() : $instance->down();
            
            $schemas = $instance->getSchemas();
            foreach ($schemas as $schema) 
            {
                $this->execute($schema);
            }
            $this->commitMigration($migration->name, $dir);
        }
        catch(Throwable $th) {
            throw $th;
        }
    }

    
    private function createTable(string $table, array $columns, array $commands) : string
    {
        $creator = new Creator;

        foreach ($columns as $column) 
        {
            $creator->makeColumn($column);
        }

        return $creator->createTable($table);
    }

    private function dropTable(string $table, bool $ifExist) : string 
    {
        return (new Creator)->dropTable($table, $ifExist);
    }

    private function modifyTable(string $table, array $columns, array $commands) : string
    {
        $creator = new Creator;

        foreach ($columns as $column) 
        {
            $creator->makeColumn($column);
        }

        return $creator->modifyTable($table, $commands);
    }


    /**
     * Execute les migrations definies
     *
     * @param Schema $schema
     * @return void
     */
    private function execute(Schema $schema)
    {
        $table = $schema->getTable();
        $sql = '';

        $commands = Collection::make($schema->getCommands())->map(function($command) {
            return $command->getAttributes();
        })->all();
        $commands = array_map(function($command) {return (object) $command; }, $commands);


        $columns = Collection::make($schema->getColumns())->map(function($column) {
            return $column->getAttributes();
        })->all();
        $columns = array_map(function($column) {return (object) $column; }, $columns);

        foreach ($commands as $command) 
        {
            $passedCommands = array_filter($commands, function($v) {
                return !in_array($v->name, ['create', 'modify']);
            });
            $commandName = $command->name ?? '';

            if ($commandName === 'create') 
            {
                $sql = $this->createTable($table, $columns, $passedCommands);
                break;
            }
            if (in_array($commandName, ['drop', 'dropIfExists'])) 
            {
                $sql = $this->dropTable($table, $commandName === 'dropIfExists');
                break;
            }
            if ($commandName === 'modify') 
            {
                $sql = $this->modifyTable($table, $columns, $passedCommands);
                break;
            }
        }

        try {
            if (!empty($sql)) 
            {
                $this->query->exec($sql, false);
            }
        } 
        catch (Throwable $th) {
            throw $th;
        }
    }

    /**
     * Enregistre les migrations executees
     *
     * @param string $name
     * @param string $dir
     * @return void
     */
    private function commitMigration(string $name, string $dir) 
    {
        $exist = false;
        $batch = $dir === 'up' ? 1 : 2;

        foreach ($this->runnedMigrations As &$runned) 
        {
            if ($runned['name'] === $name)
            {
                $exist = true;
                $runned['batch'] = $batch;
                break;
            }
        }

        if (!$exist) 
        {
            $this->runnedMigrations[] = compact('name', 'batch');
        }

        Utils::arrayToJson($this->runnedMigrations, $this->runnedMigrationsFile);
    }

    /**
     * Recupere les migrations a executer
     *
     * @param string $dir
     * @return array
     */
    private function getRunnedMigrations(string $dir) : array 
    {
        $runned = [];

        $upDownMigrations = [
            'up' => array_filter($this->runnedMigrations, function($v) { return $v['batch'] != 1; }),
            'down' => array_filter($this->runnedMigrations, function($v) { return $v['batch'] != 2; })
        ];
        $namedMigrations = array_map(function($v){ return $v['name']; }, $this->runnedMigrations);

        foreach ($this->migrations as $item) 
        {
            if (!in_array($item->name, $namedMigrations)) 
            {
                $runned[] = $item;
            }
            else if (in_array($dir, ['up', 'down']))
            {
                foreach ($upDownMigrations[$dir] ?? [] As $v) 
                {
                    if ($v['name'] === $item->name) 
                    {
                        $runned[] = $item;
                    }
                }
            }
        }

        if (!empty($runned))
        {
            $this->query = new Query();
        }

        return $runned;
    }

    /**
     * Recupere toutes les migrations
     *
     * @param array $paths
     * @return array
     */
    private function getMigrations(array $paths = []) : array
    {
        $classes = [];
        $paths = $this->getMigrationFiles(array_merge($this->paths, $paths));

        foreach ($paths As $item) 
        {
            $name = $this->getMigrationName($item->getFilename());
            $file = $item->getPathname();
            $class = preg_replace(['#^[0-9]{4}[0-9]{2}[0-9]{2}[0-9]{6}-#', '#\.php$#'], '', $name);
            
            $classes[] = (object) [
                'class'    => Str::toPascal($class),
                'name'     => $name,
                'location' => $file
            ];
        }

        return $classes;
    }

    /**
     * Get all of the migration files in a given path.
     *
     * @param  string|array  $paths
     * @return array
     */
    private function getMigrationFiles($paths) : array
    {
        $files = [];

        Collection::make($paths)->each(function($path) use(&$files) {
            $files = array_merge($files, $this->files->files($path));    
        });
        return Collection::make($files)->flatMap(function ($path) {
            return Str::endsWith($path, '.php') ? [$path] : [$this->files->glob($path.'/*_*.php')];
        })->filter()->sortBy(function ($file) {
            return $this->getMigrationName($file);
        })->values()->keyBy(function ($file) {
            return $this->getMigrationName($file);
        })->all();
    }

    /**
     * Get the name of the migration.
     *
     * @param  string  $path
     * @return string
     */
    private function getMigrationName($path)
    {
        return str_replace('.php', '', basename($path));
    }
}
