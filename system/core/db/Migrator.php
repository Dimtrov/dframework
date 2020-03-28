<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Sarl
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.0
 */


namespace dFramework\core\db;

use dFramework\core\exception\DatabaseException;

/**
 * Migrator
 *
 * Database version manager
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Db
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since		2.1
 * @file		/system/core/db/Migrator.php
 */

class Migrator
{
    /**
     * @var Database
     */
    private $db;

    /**
     * @var string
     */
    private $save_folder = RESOURCE_DIR . 'migrations' . DS;

    /**
     * @var string
     */
    private $filename = null;


    /**
     * Migrator constructor.
     * @param Database $db
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Sauvegarde une base de donnee
     * 
     * @param string $version
     */
    public function down(string $version)
    {
        if(false === is_dir($this->save_folder))
        {
            if( mkdir($this->save_folder, 0700) === FALSE )
            {
                exit('<br /><br />Impossible de creer le repertoire pour la sauvegarde. Veuillez le creer manuellement');
            }
        }
        $filename = (!empty($this->filename)) ? $this->filename : $this->db->config['database'];
        $filename .= '_v'.$version.'.sql';

        $commande  = 'mysqldump';
        $commande .= ' --host=' . $this->db->config['host'];
        $commande .= ' --port=' . $this->db->config['port'];
        $commande .= ' --user=' . $this->db->config['username'];
        $commande .= ' --password=' . $this->db->config['password'];
        $commande .= ' --skip-opt';
        $commande .= ' --compress';
        $commande .= ' --add-locks';
        $commande .= ' --create-options';
        $commande .= ' --disable-keys';
        $commande .= ' --quote-names';
        $commande .= ' --quick';
        $commande .= ' --extended-insert';
        $commande .= ' --complete-insert';
        $commande .= ' --default-character-set=' . $this->db->config['charset'];
        $commande .= ' '.$this->db->config['database'] ;
        $commande .= '  > '.rtrim($this->save_folder, DS).DS.$filename;

        system($commande);
    }

    public function up(string $version)
    {
        $filename = (!empty($this->filename)) ? $this->filename : $this->db->config['database'];
        $filename .= '_v'.$version.'.sql';
        $file = rtrim($this->save_folder, DS).DS.$filename;

        if(!file_exists($file) OR !is_readable($file))
        {
            DatabaseException::except('
                Impossible de charger la migration <b>'.$filename.'</b>
                <br>
                Le fichier de &laquo; '.$file.' &raquo; n\'existe pas ou n\'est pas accessible en lecture
            ');
        }

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
            if(((time() - filemtime($file)) / 60) > $duration)
            {
                unlink($file);
            }
        }
    }



}