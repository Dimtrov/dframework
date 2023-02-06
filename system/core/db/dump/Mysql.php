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

use dFramework\core\exception\DatabaseException;

/**
 * Mysql
 *
 * Make a mysql database dump
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Db/Dump
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.4.1
 * @file		/system/core/db/dump/Mysql.php
 */
class Mysql extends BaseDump
{
	/**
	 * Run execution of export database and save the current state of database in a dump file
	 *
	 * @param string|null $version
	 * @return string
	 */
	public function export(?string $version = null): string
	{
		if (false === is_dir($this->directory))
        {
            if ( mkdir($this->directory, 0700) === FALSE )
            {
				throw new DatabaseException('Mysql::Dump - Unable to create the database dump direactory. Please create it manually');
            }
        }

		$command = $this->initCommand('export');

		if ($this->options['use_transaction'])
		{
			$command .= ' --single-transaction';
		}
		if ($this->options['disable_fk'])
		{
			$command .= ' --disable-keys';
		}
		if ($this->options['drop_database'])
		{
			$command .= ' --add-drop-database';
		}
		if ($this->options['quick_param'])
		{
			$command .= ' --quick';
		}
		if (!empty($this->options['sql_compat']) AND $this->options['sql_compat'] != 'NONE')
		{
			$command .= ' --compatible=' . escapeshellarg($this->options['sql_compat']);
		}
		if ($this->options['add_locks'])
		{
			$command .= " --add-locks";
		}

		if ($this->options['sql_structure'])
		{
			if ($this->options['drop_table'])
			{
				$command .= ' --add-drop-table=TRUE';
			}
			else
			{
				$command .= ' --add-drop-table=FALSE';
			}
		}
		else
		{
			$command .= ' -t';
		}

		if ($this->options['sql_data'])
		{
			$command .= ' --tables';
			if ($this->options['showcolumns'])
			{
				$command .= " -c";
			}
			if ($this->options['extended_insert'])
			{
				$command .= ' --extended-insert';
			}
			else
			{
				$command .= ' --skip-extended-insert';
			}
			if ($this->options['delayed_insert'])
			{
				$command .= ' --delayed-insert';
			}
			if ($this->options['insert_ignore'])
			{
				$command .= ' --insert-ignore';
			}
			if ($this->options['hexforbinary'])
			{
				$command .= " --hex-blob";
			}
		}
		else {
			$command .= ' -d'; // No row information (no data)
		}

		if ($this->options['compress'])
		{
			$command .= ' --compress';
		}
		if ($this->options['skip_opt'])
		{
			$command .= ' --skip-opt';
		}
		if ($this->options['create_options'])
		{
			$command .= ' --create-options';
		}
		if ($this->options['quote_names'])
		{
			$command .= ' --quote-names';
		}
		if ($this->options['complete_insert'])
		{
			$command .= ' --complete-insert';
		}

        $command .= ' --default-character-set=' . escapeshellarg($this->options['charset'] ?? $this->config['charset']) . ' --no-tablespaces';
        $command .= ' ' . escapeshellarg($this->config['database']);

		$output_file = $this->makeFilename($version);

		if ($this->options['compression'] == 'none')
		{
			$command .= ' | grep -v "Warning: Using a password on the command line interface can be insecure." > "'.$output_file.'"';
		}
		else if ($this->options['compression'] == 'gz')
		{
			$command .= ' | grep -v "Warning: Using a password on the command line interface can be insecure." | gzip > "'.$output_file.'"';
		}
		else if ($this->options['compression'] == 'bz')
		{
			$command .= ' | grep -v "Warning: Using a password on the command line interface can be insecure." | bzip2 > "'.$output_file.'"';
		}
		elseif ($this->options['compression'] == 'zstd')
		{
			$command .= ' | grep -v "Warning: Using a password on the command line interface can be insecure." | zstd > "'.$output_file.'"';
		}

		shell_exec($command);

        return $output_file;
	}

	/**
	 * Run execution of import database and use a dump file to reset the database to a specific state
	 *
	 * @param string|null $version
	 * @return string
	 */
	public function import(?string $version = null): string
    {
		$file = $this->makeFilename($version);

        if (!file_exists($file) OR !is_readable($file))
        {
			$filename = pathinfo($file, PATHINFO_FILENAME);
            throw new DatabaseException("Mysql::Dump - Impossible de charger la migration < ".$filename." > \nLe fichier < '.$file.' > n\'existe pas ou n\'est pas accessible en lecture");
        }

       	$this->deleteAllTables();

        $command = $this->initCommand('import')
				. ' --database=' . escapeshellarg($this->config['database'])
				. ' < ' . escapeshellarg($file);

        shell_exec($command);

        return $file;
    }

	/**
	 *	Return full path of dump program
	 *
	 *	@return		string		Full path of dump program
	 */
	public function exportPath(): string
	{
		$cmd = 'mysqldump';

		$resql = $this->db->connection()->query('SHOW VARIABLES LIKE \'basedir\'');

		if ($resql)
		{
			$liste   = $resql->first(\PDO::FETCH_ASSOC);
			$basedir = rtrim($liste['Value'], '/\\');
			$cmd     = $basedir . (preg_match('/\/$/', $basedir) ? '' : '/') . 'bin/mysqldump';
		}

		return $cmd;
	}

	/**
	 *	Return full path of restore program
	 *
	 *	@return		string		Full path of restore program
	 */
	public function importPath(): string
	{
		$cmd = 'mysql';

		$resql = $this->db->connection()->query('SHOW VARIABLES LIKE \'basedir\'');

		if ($resql)
		{
			$liste   = $resql->first(\PDO::FETCH_ASSOC);
			$basedir = rtrim($liste['Value'], '/\\');
			$cmd     = $basedir . (preg_match('/\/$/', $basedir) ? '' : '/') . 'bin/mysql';
		}

		return $cmd;
	}

	private function initCommand(string $type) : string
	{
		$cmd = $type === 'import' ? $this->importPath() : $this->exportPath();

		if (preg_match("/\s/", $cmd))
		{
			$cmd = escapeshellarg($cmd); // Use quotes on command
		}

		return str_replace('/', DS, $cmd)
        	. ' --host=' . escapeshellarg($this->config['host'])
        	. ' --port=' . escapeshellarg($this->config['port']) . ' --protocol=tcp'
        	. ' --user=' . escapeshellarg($this->config['username'])
			. ' --password=' . escapeshellarg($this->config['password']);
	}
}
