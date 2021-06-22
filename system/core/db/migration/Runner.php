<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019 - 2021, Dimtrov Lab's
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 *  @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.3.0
 */

namespace dFramework\core\db\migration;

use PDO;
use RuntimeException;
use Throwable;
use dFramework\core\db\query\Builder;
use dFramework\core\loader\Filesystem;
use dFramework\core\utilities\Collection;
use dFramework\core\utilities\Str;

/**
 * Runner
 * MigrationRunner : class to execute migrations
 *
 * @package		dFramework
 * @subpackage	Core
 * @category 	Db/Migration
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.3.0
 * @file		/system/core/db/migration/Runner.php
 */
class Runner
{
	/**
	 * Tracks whether we have already ensured
	 * the table exists or not.
	 *
	 * @var boolean
	 */
	protected $tableChecked = false;
	/**
	 * Used to skip current migration.
	 *
	 * @var boolean
	 */
	protected $groupSkip = false;

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
    private $messages = [];


	/**
	 * Name of table to store meta information
	 *
	 * @var string
	 */
	protected $table = '_df_migrations_';

	/**
	 * Database connection
	 *
	 * @var Builder
	 */
	protected $db;

	/**
	 * The pattern used to locate migration file versions.
	 *
	 * @var string
	 */
	protected $regex = '/^\d{4}\d{2}\d{2}\d{6}-(\w+)$/';

	/**
     * @var self
     */
    private static $_instance;


    //--------------------------------------------------------------------

	/**
	 * Constructor.
	 *
	 */
	public function __construct(?string $group = null)
	{
		if (PHP_SAPI !== 'cli')
        {
        	throw new RuntimeException("Disponible unique via l'invite de commande", 1);
        }
		$this->db = new Builder($group);
		$this->files = new Filesystem;
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
	 * Locate and run all new migrations
	 */
	public function latest()
	{
		$this->ensureTable();

		// Locate the migrations
		$migrations = $this->getMigrations();

		// If nothing was found then we're done
		if (empty($migrations))
		{
			return true;
		}

		// Remove any migrations already in the history
		foreach ($this->getHistory() As $history)
		{
			unset($migrations[$this->getObjectUid($history)]);
		}

		// Start a new batch
		$batch = $this->getLastBatch() + 1;

		// Run each migration
		foreach ($migrations as $migration)
		{
			if ($this->migrate('up', $migration))
			{
				if ($this->groupSkip === true)
				{
					$this->groupSkip = false;
					continue;
				}
				$this->addHistory($migration, $batch);
			}
			// If a migration failed then try to back out what was done
			else
			{
				$this->regress(-1);

				throw new RuntimeException('Migration failed!');
			}
		}

		return true;
	}

	/**
	 * Migrate down to a previous batch
	 *
	 * Calls each migration step required to get to the provided batch
	 *
	 * @param integer     $targetBatch Target batch number, or negative for a relative batch, 0 for all
	 * @return mixed Current batch number on success, FALSE on failure or no migrations are found
	 */
	public function regress(int $targetBatch = 0)
	{
		$this->ensureTable();

		// Get all the batches
		$batches = $this->getBatches();

		// Convert a relative batch to its absolute
		if ($targetBatch < 0)
		{
			$targetBatch = $batches[count($batches) - 1 + $targetBatch] ?? 0;
		}

		// If the goal was rollback then check if it is done
		if (empty($batches) AND $targetBatch === 0)
		{
			return true;
		}

		// Make sure $targetBatch is found
		if ($targetBatch !== 0 AND ! in_array($targetBatch, $batches))
		{
			throw new RuntimeException('Target batch not found: ' . $targetBatch);
		}

		$allMigrations   = $this->getMigrations();

		// Gather migrations down through each batch until reaching the target
		$migrations = [];
		while ($batch = array_pop($batches))
		{
			// Check if reached target
			if ($batch <= $targetBatch)
			{
				break;
			}

			// Get the migrations from each history
			foreach ($this->getBatchHistory($batch, 'desc') As $history)
			{
				// Create a UID from the history to match its migration
				$uid = $this->getObjectUid($history);

				// Make sure the migration is still available
				if (! isset($allMigrations[$uid]))
				{
					throw new RuntimeException('There is a gap in the migration sequence near version number: ' . $history->version);
				}

				// Add the history and put it on the list
				$migration          = $allMigrations[$uid];
				$migration->history = $history;
				$migrations[]       = $migration;
			}
		}

		// Run each migration
		foreach ($migrations As $migration)
		{
			if ($this->migrate('down', $migration))
			{
				$this->removeHistory($migration->history);
			}
			// If a migration failed then quit so as not to ruin the whole batch
			else
			{
				throw new RuntimeException('Migration failed!');
			}
		}

		return true;
	}

	/**
	 * Migrate a single file regardless of order or batches.
	 * Method "up" or "down" determined by presence in history.
	 * NOTE: This is not recommended and provided mostly for testing.
	 *
	 * @param string      $path  Full path to a valid migration file
	 * @param string      $path  Namespace of the target migration
	 * @param string|null $group
	 */
	public function force(string $path)
	{
		$this->ensureTable();

		// Create and validate the migration
		$migration = $this->migrationFromFile($path);
		if (empty($migration))
		{
			throw new RuntimeException('Migration file not found: '.$path);
		}

		// Check the history for a match
		$method = 'up';
		foreach ($this->getHistory() As $history)
		{
			if ($this->getObjectUid($history) === $migration->uid)
			{
				$method             = 'down';
				$migration->history = $history;
				break;
			}
		}

		// up
		if ($method === 'up')
		{
			// Start a new batch
			$batch = $this->getLastBatch() + 1;

			if ($this->migrate('up', $migration))
			{
				$this->addHistory($migration, $batch);
				return true;
			}
		}

		// down
		elseif ($this->migrate('down', $migration))
		{
			$this->removeHistory($migration->history);
			return true;
		}

		throw new RuntimeException('Migration failed!');
	}

	//--------------------------------------------------------------------

	/**
     * Recupere toutes les migrations
     *
     * @param array $paths
     * @return array
     */
    private function getMigrations(array $paths = []) : array
    {
        $migrations = [];
        $paths = $this->getMigrationFiles(array_merge($this->paths, $paths));

       	foreach ($paths As $item)
        {
			$migration = $this->migrationFromFile($item->getPathname());
			$migrations[$migration->uid] = $migration;
        }

        return $migrations;
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
            return basename($file, '.php');
        })->values()->keyBy(function ($file) {
            return basename($file, '.php');
        })->all();
    }
	/**
	 * Create a migration object from a file path.
	 *
	 * @param string $path The path to the file
	 * @return object|false    Returns the migration object, or false on failure
	 */
	protected function migrationFromFile(string $path)
	{
		if (substr($path, -4) !== '.php')
		{
			return false;
		}

		// Remove the extension
		$name = basename($path, '.php');

		// Filter out non-migration files
		if (! preg_match($this->regex, $name))
		{
			return false;
		}

		// Create migration object using stdClass
		$migration = new \stdClass();

		// Get migration version number
		$migration->version  = $this->getMigrationNumber($name);
		$migration->name     = $name;
		$migration->location = $path;
		$migration->class    = $this->getMigrationClass($name);
		$migration->uid      = $this->getObjectUid($migration);

		return $migration;
	}

	/**
	 * Extracts the migration number from a filename
	 *
	 * @param string $migration
	 *
	 * @return string    Numeric portion of a migration filename
	 */
	protected function getMigrationNumber(string $migration): string
	{
		preg_match('/^\d{4}\d{2}\d{2}\d{6}/', $migration, $matches);

		return count($matches) ? $matches[0] : '0';
	}
	/**
	 * Extrait le nom de la classe de migration
	 *
	 * @param string $migration
	 * @return string
	 */
	private function getMigrationClass(string $migration) : string
	{
		return Str::toPascal(preg_replace('/^\d{4}\d{2}\d{2}\d{6}-/', '', $migration));
	}

	/**
	 * Uses the non-repeatable portions of a migration or history
	 * to create a sortable unique key
	 *
	 * @param object $migration or $history
	 *
	 * @return string
	 */
	private function getObjectUid($object): string
	{
		return preg_replace('/[^0-9]/', '', $object->version) . $object->class;
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieves messages formatted for CLI output
	 *
	 * @return array    Current migration version
	 */
	public function getMessages(): array
	{
		return $this->messages;
	}
	/**
	 * Set CLI messages
	 *
	 * @param string $message
	 * @param string $color
	 * @return self
	 */
	private function pushMessage(string $message, string $color = 'green') : self
	{
		$this->messages[] = compact('message', 'color');
		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Truncates the history table.
	 *
	 * @return void
	 */
	public function clearHistory()
	{
		if ($this->db->tableExist($this->table))
		{
			$this->db->truncate($this->table);
		}
	}

	/**
	 * Add a history to the table.
	 *
	 * @param object  $migration
	 * @param integer $batch
	 *
	 * @return void
	 */
	protected function addHistory($migration, int $batch)
	{
		$this->db->into($this->table)->insert([
			'version'   => $migration->version,
			'class'     => $migration->class,
			'time'      => time(),
			'batch'     => $batch,
		]);
		$this->pushMessage('Running: ' . $migration->version . '_' . $migration->class, 'yellow');
	}

	/**
	 * Removes a single history
	 *
	 * @param object $history
	 *
	 * @return void
	 */
	protected function removeHistory($history)
	{
		$this->db->from($this->table)->where('id', $history->id)->delete();

		$this->pushMessage('Rolling back: ' . $history->version . '_' . $history->class, 'yellow');
	}

	//--------------------------------------------------------------------

	/**
	 * Grabs the full migration history from the database for a group
	 *
	 * @param string $group
	 *
	 * @return array
	 */
	public function getHistory(): array
	{
		$this->ensureTable();

		return $this->db->from($this->table)->sortAsc('id')->all();
	}

	/**
	 * Returns the migration history for a single batch.
	 *
	 * @param integer $batch
	 *
	 * @return array
	 */
	public function getBatchHistory(int $batch, $order = 'asc'): array
	{
		$this->ensureTable();

		return $this->db->from($this->table)->where('batch', $batch)->orderBy('id', $order)->all();
	}

	//--------------------------------------------------------------------

	/**
	 * Returns all the batches from the database history in order
	 *
	 * @return array
	 */
	public function getBatches(): array
	{
		$this->ensureTable();

		$batches = $this->db->from($this->table)
						  ->select('batch')
						  ->distinct()
						  ->sortAsc('batch')
						  ->all(PDO::FETCH_ASSOC);

		return array_column($batches, 'batch');
	}

	/**
	 * Returns the value of the last batch in the database.
	 *
	 * @return integer
	 */
	public function getLastBatch(): int
	{
		$this->ensureTable();

		return (int) $this->db->from($this->table)->max('batch');
	}

	/**
	 * Returns the version number of the first migration for a batch.
	 * Mostly just for tests.
	 *
	 * @param integer $batch
	 * @param integer $targetBatch
	 * @return string
	 */
	public function getBatchStart(int $batch, int $targetBatch = 0): string
	{
		// Convert a relative batch to its absolute
		if ($batch < 0)
		{
			$batches = $this->getBatches();
			$batch   = $batches[count($batches) - 1 + $targetBatch] ?? 0;
		}

		$migration = $this->db->from($this->table)->where('batch', $batch)->sortAsc('id')->first();

		return $migration->version ?? '0';
	}

	/**
	 * Returns the version number of the last migration for a batch.
	 * Mostly just for tests.
	 *
	 * @param integer $batch
	 * @param integer $targetBatch
	 * @return string
	 */
	public function getBatchEnd(int $batch, int $targetBatch = 0): string
	{
		// Convert a relative batch to its absolute
		if ($batch < 0)
		{
			$batches = $this->getBatches();
			$batch   = $batches[count($batches) - 1 + $targetBatch] ?? 0;
		}

		$migration = $this->db->from($this->table)->where('batch', $batch)->sortDesc('id')->first();

		return $migration->version ?? '0';
	}

	//--------------------------------------------------------------------

	/**
	 * Ensures that we have created our migrations table in the database.
	 */
	public function ensureTable()
	{
		if ($this->tableChecked OR $this->db->tableExist($this->table))
		{
			return;
		}

		$schema = new Schema($this->table);
		$schema->bigIncrements('id');
		$schema->string('version');
		$schema->string('class');
		$schema->integer('time');
		$schema->integer('batch')->unsigned();
		$schema->create();

		$this->execute($schema);
	}

	/**
	 * Handles the actual running of a migration.
	 *
	 * @param string $direction   "up" or "down"
	 * @param object $migration   The migration to run
	 *
	 * @return boolean
	 */
	protected function migrate($direction, $migration): bool
	{
		include_once $migration->location;

		$class = $migration->class;

		// Validate the migration file structure
		if (! class_exists($class, false))
		{
			throw new RuntimeException(sprintf('The migration class "%s" could not be found.', $class));
		}

		// Initialize migration
		$instance = new $class();

		if (! is_callable([$instance, $direction]))
		{
			throw new RuntimeException(sprintf('The migration class is missing an "%s" method.', $direction));
		}

		$instance->{$direction}();

		foreach ($instance->getSchemas() As $schema)
		{
			$this->execute($schema);
		}

		return true;
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

        $commands = $this->getSchemaCommands($schema);
        $columns = $this->getSchemaColumns($schema);
		$passedCommands = $this->passedCommands($commands);

        foreach ($commands as $command)
        {
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
                $this->db->query($sql);
            }
        }
        catch (Throwable $th) {
            throw $th;
        }
    }

	/**
	 * Recupere les commandes a executer
	 *
	 * @param Schema $schema
	 * @return array
	 */
	private function getSchemaCommands(Schema $schema) : array
	{
		$commands = Collection::make($schema->getCommands())->map(function($command) {
            return $command->getAttributes();
        })->all();

        return array_map(function($command) {return (object) $command; }, $commands);
	}
	/**
	 * Recupere les colonnes a prendre en compte
	 *
	 * @param Schema $schema
	 * @return array
	 */
	private function getSchemaColumns(Schema $schema) : array
	{
		$columns = Collection::make($schema->getColumns())->map(function($column) {
            return $column->getAttributes();
        })->all();

        return array_map(function($column) {return (object) $column; }, $columns);
	}
	/**
	 * Filtre et retourne uniquement les commandes a executer
	 *
	 * @param array $commands
	 * @return array
	 */
	private function passedCommands(array $commands) : array
	{
		return array_filter($commands, function($v) {
			return !in_array($v->name, ['create', 'modify']);
		});
	}

	/**
	 * Genere la requete sql permetant de creer une table a partie du schema
	 *
	 * @param string $table
	 * @param array $columns
	 * @param array $commands
	 * @return string
	 */
	private function createTable(string $table, array $columns, array $commands) : string
    {
        $creator = new Creator;

        foreach ($columns as $column)
        {
            $creator->makeColumn($column);
        }

        return $creator->createTable($table, $commands);
    }
	/**
	 * Genere la requete sql pour supprimer une table
	 *
	 * @param string $table
	 * @param boolean $ifExist
	 * @return string
	 */
    private function dropTable(string $table, bool $ifExist) : string
    {
        return (new Creator)->dropTable($table, $ifExist);
    }
	/**
	 * Genere la requete sql pour modifier une table
	 *
	 * @param string $table
	 * @param array $columns
	 * @param array $commands
	 * @return string
	 */
    private function modifyTable(string $table, array $columns, array $commands) : string
    {
        $creator = new Creator;

        foreach ($columns as $column)
        {
            $creator->makeColumn($column);
        }

        return $creator->modifyTable($table, $commands);
    }
}
