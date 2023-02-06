<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019 - 2021, Dimtrov Lab's
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.4.1
 */

namespace dFramework\core\db\dump;

use dFramework\core\db\Database;
use dFramework\core\loader\Filesystem;

/**
 * BaseDump
 *
 * Abstract class to make a database dump
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Db/Dump
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.4.1
 * @file		/system/core/db/dump/BaseDump.php
 */
abstract class BaseDump
{
	/**
	 * Database connection
	 *
	 * @var Database
	 */
	protected $db;

	/**
	 * Database connection parameters
	 *
     * @var array
     */
    protected $config = [];

    /**
	 * Dump directory
	 *
     * @var string
     */
    protected $directory = DB_DUMP_DIR;

    /**
	 * Dump output/input filename
	 *
     * @var string
     */
    protected $filename = null;

	/**
	 * Dump output configuration
	 *
	 * @var array
	 */
	protected $options = [
		'use_transaction' => false,
		'disable_fk'      => true,
		'drop_database'   => false,
		'quick_param'     => true,
		'sql_compat'      => 'NONE',
		'sql_structure'   => true,
		'sql_data'        => true,
		'drop_table'      => false,
		'add_locks'       => true,
		'extended_insert' => true,
		'delayed_insert'  => false,
		'complete_insert' => true,
		'insert_ignore'   => false,
		'showcolumns'     => false,
		'hexforbinary'    => false,
		'compress'        => true,
		'skip_opt'        => true,
		'create_options'  => true,
		'quote_names'     => true,
		'compression'     => 'none',
	];


	public function __construct(Database $db)
	{
		$this->db = $db;
		$this->config = $db->config();
	}

	/**
	 * Run execution of export database and save the current state of database in a dump file
	 *
	 * @param string|null $version
	 * @return string
	 */
	abstract public function export(?string $version = null): string;

	/**
	 * Run execution of import database and use a dump file to reset the database to a specific state
	 *
	 * @param string|null $version
	 * @return string
	 */
	abstract public function import(?string $version = null): string;

	/**
	 *	Return full path of dump program
	 *
	 *	@return		string		Full path of dump program
	 */
	abstract public function exportPath(): string;
	
	/**
	 *	Return full path of restore program
	 *
	 *	@return		string		Full path of restore program
	 */
	abstract public function importPath(): string;

	/**
	 * down
	 * Alias of self::export
	 *
	 * @param string|null $version
	 * @return string
	 */
	public function down(?string $version = null) : string
	{
		return $this->export($version);
	}

	/**
	 * up
	 * Alias of self::import()
	 *
	 * @param string|null $version
	 * @return string
	 */
	public function up(?string $version = null) : string
	{
		return $this->import($version);
	}

	/**
	 * Set directory to use for saving dump
	 *
	 * @param string $directory
	 *
	 * @return self
	 */
	public function setDirectory(string $directory): self
	{
		$this->directory = $directory;

		return $this;
	}

	/**
	 * Set filename to use for storing dump
	 *
	 * @param string $filename
	 * @return self
	 */
	public function setFilename(string $filename): self
	{
		$this->filename = $filename;

		return $this;
	}

	/**
	 * Merge custom dump options with the default settings
	 *
	 * @param array $options
	 * @return self
	 */
	public function setOptions(array $options): self
	{
		$this->options = array_merge($this->options, $options);

		return $this;
	}

	/**
     * Drop all old dump files
     *
     * @param int $duration Ancienneté des fichiers à conserver en minute
     */
    public function deleteOldFile(int $duration = 7200)
    {
        $files = glob(rtrim($this->directory, DS) . DS . '*');
        foreach ($files As $file)
        {
            if (((time() - filemtime($file)) / 60) > $duration)
            {
                unlink($file);
            }
        }
    }

	/**
	 * Make the absolute path to the dump file
	 *
	 * @param string|null $version
	 * @return string
	 */
	protected function makeFilename(?string $version = null): string
	{
		if (empty($version))
		{
			$version = date('YmdHis');
		}

		$filename = !empty($this->filename) ? $this->filename : $this->config['database'];
        $filename .= '_version_'.$version.'.sql';

		if ($version === 'last')
		{
			$files = Filesystem::files($this->directory, false, 'modifiedTime');
			/**
			 * @var \Symfony\Component\Finder\SplFileInfo
			 */
			$fileinfo = array_pop($files);
			if (!empty($fileinfo) AND $fileinfo->getExtension() == 'sql')
			{
				$filename = $fileinfo->getFilename();
			}
		}

		if ($this->options['compression'] == 'gz')
		{
			$filename .= '.gz';
		}
		else if ($this->options['compression'] == 'bz')
		{
			$filename .= '.bz2';
		}
		else if ($this->options['compression'] == 'zstd')
		{
			$filename .= '.zst';
		}

		return rtrim($this->directory, DS) . DS . $filename;
	}

    /**
     * Delete all tables in the database
     *
     */
    protected function deleteAllTables()
    {
        $pdo = $this->db->connection();

        $tables = $pdo->tables();
        $pdo->disableFk();
        foreach ($tables As $table)
        {
            $pdo->query('DROP TABLE '.$table);
        }
    }
}
