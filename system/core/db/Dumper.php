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
 * @version     3.4.0
 */

namespace dFramework\core\db;

use dFramework\core\exception\DatabaseException;
use dFramework\core\exception\Exception;
use dFramework\core\loader\Filesystem;
use dFramework\core\loader\Service;

/**
 * Dumper
 *
 * Database dump manager
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Db
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since		2.1
 * @file		/system/core/db/Dump.php
 */
class Dumper
{
    /**
     * @var Database
     */
    private $db;

    /**
     * @var array
     */
    private $config = [];

    /**
     * @var string
     */
    private $save_folder = DB_DUMP_DIR;

    /**
     * @var string
     */
    private $filename = null;


    /**
     * Migrator constructor.
     * @param string $group
     */
    public function __construct(?string $group = null)
    {
        if ('cli' !== PHP_SAPI)
        {
            // exit('Fonctionnalités disponible uniquement en invite de commande');
        }
        $this->db = Service::database($group);
        $this->config = $this->db->config();
    }

    /**
     * Sauvegarde l'état actuel d'une base de données dans un fichier de dump
     *
     * @param string $version
     * @return string a path for saved file
     */
    public function export(string $version) : string
    {
        if (false === is_dir($this->save_folder))
        {
            if ( mkdir($this->save_folder, 0700) === FALSE )
            {
				throw new Exception("\n Impossible de creer le repertoire pour la sauvegarde. Veuillez le créer manuellement");
            }
        }
        $filename = !empty($this->filename) ? $this->filename : $this->config['database'];
        $filename .= '_version_'.$version.'.sql';
        $save_file = rtrim($this->save_folder, DS).DS.$filename;

        $commande  = 'mysqldump';
        $commande .= ' --host=' . $this->config['host'];
        $commande .= ' --port=' . $this->config['port'];
        $commande .= ' --user=' . $this->config['username'];
        $commande .= ' --password=' . $this->config['password'];
        $commande .= ' --skip-opt';
        $commande .= ' --compress';
        $commande .= ' --add-locks';
        $commande .= ' --create-options';
        $commande .= ' --disable-keys';
        $commande .= ' --quote-names';
        $commande .= ' --quick';
        $commande .= ' --extended-insert';
        $commande .= ' --complete-insert';
        $commande .= ' --default-character-set=' . $this->config['charset'];
        $commande .= ' '.$this->config['database'];
        $commande .= '  > '.$save_file;

        shell_exec($commande);

        return $save_file;
    }

	/**
	 * down
	 * Alias of self::export
	 *
	 * @param string $version
	 * @return string
	 */
	public function down(string $version) : string
	{
		return $this->export($version);
	}


	/**
	 * Execute un fichier de dump pour importer la base de données
	 *
	 * @param string $version
	 * @return string
	 */
    public function import(string $version) : string
    {
		$filename = !empty($this->filename) ? $this->filename : $this->config['database'];
		$filename .= '_version_'.$version.'.sql';

		if ($version === 'last')
		{
			$files = Filesystem::files($this->save_folder, false, 'modifiedTime');
			/**
			 * @var \Symfony\Component\Finder\SplFileInfo
			 */
			$fileinfo = array_pop($files);
			if (!empty($fileinfo) AND $fileinfo->getExtension() == 'sql')
			{
				$filename = $fileinfo->getFilename();
			}
		}

		$file = rtrim($this->save_folder, DS).DS.$filename;

        if (!file_exists($file) OR !is_readable($file))
        {
            throw new DatabaseException('
                Impossible de charger la migration < '.$filename.' >
                <br>
                Le fichier < '.$file.' > n\'existe pas ou n\'est pas accessible en lecture
            ');
        }

       	$this->deleteAllTables();

        $command = 'mysql'
            . ' --host=' . $this->config['host']
            . ' --port=' . $this->config['port']
            . ' --user=' . $this->config['username']
            . ' --password=' . $this->config['password']
            . ' --database=' . $this->config['database']
            . ' < ' . $file;

        shell_exec($command);

        return $file;
    }

	/**
	 * up
	 * Alias of self::import()
	 *
	 * @param string $version
	 * @return string
	 */
	public function up(string $version) : string
	{
		return $this->import($version);
	}


    /**
     * suppression des anciennes sauvegardes
     *
     * @param int $duration Ancienneté des fichiers à conserver en minute
     */
    public function deleteOldFile(int $duration = 7200)
    {
        $files = glob(rtrim($this->save_folder, DS).DS.'*');
        foreach ($files As $file)
        {
            if (((time() - filemtime($file)) / 60) > $duration)
            {
                unlink($file);
            }
        }
    }

    /**
     * Delete all tables in the database
     *
     */
    private function deleteAllTables()
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
