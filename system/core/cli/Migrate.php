<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Sarl
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @link	    https://dimtrov.hebfree.org/works/dframework
 * @version     3.2.3
 */


namespace dFramework\core\cli;

use Ahc\Cli\IO\Interactor;
use Ahc\Cli\Output\Color;
use Ahc\Cli\Output\Writer;
use dFramework\core\db\Database;
use dFramework\core\db\Migrator;
use dFramework\core\dFramework;
use Exception;

/**
 * Migrate
 *
 * A simple database migration service
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Cli
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @since       3.1
 * @file        /system/core/cli/Migrate.php
 */
class Migrate extends Cli
{
    protected $_description = 'Service de migration de base de donnees';
    protected $_name = 'Migrate';

    public function __construct()
    {
        parent::__construct();
        $this->writer = new Writer();

        $this
            ->option('-b --backup', 'Cree une sauvegarde de la base de donnees')
            ->option('-u --upgrade', 'Importe un script de base de donnees')
            ->argument('[database]', 'Specifie la configuration de la base de donnees a utiliser. Par defaut il s\'agit de la configuration "default"', 'default')
            // Usage examples:
            ->usage(
                '<bold>  dbot migrate --backup</end> <comment> => Cree un fichier de sauvegarde la base de donnees dans le repertoire "/app/resources/database/migrations/" sous le nom "nombd_v[num_ver].sql"</end><eol/>' .
                '<bold>  dbot migrate -upgrade</end> <comment> => Importe le script de base de donnees du fichier "nombd_v[num_ver].sql" se trouvant dans le repertoire "/app/resources/database/migrations/"</end><eol/>' 
            );
    }

    // This method is auto called before `self::execute()` and receives `Interactor $io` instance
    public function interact(Interactor $io)
    {
        if (!$this->backup AND !$this->upgrade)
        {
            $this->_io->warn("\n Veuillez selectionner une option pour pouvoir executer cette tache.", true);
            $this->showHelp();
        }
    }
   
    public function execute($database)
    {
        try{
            $this->_startMsg();

            if ($this->backup)
            {
                $this->backup($database);
            }
            else 
            {
                $this->upgrade($database);
            }

            $this->_endMsg();
        }
        catch(\Exception $e) { }
        
        return true;
    }

    private function backup(string $db_group)
    {
        $num_ver = $this->_io->prompt("\nVeuillez entrer le numero de la version de votre base de donnee", date('Y-m-d'));

        try {        
            $migrator = new Migrator(Database::instance()->use($db_group));

            $filename = $migrator->down($num_ver);

            $this->_io->ok("\n\t Base de donnees sauvegardée avec succès.", true);
            $this->_io->info("\t Retrouvez le fichier de sauvegarde via le chemin <".$filename.">.", true);
        }
        catch(\Exception $e) { 
            die($e->getMessage());
        }
    }

    private function upgrade(string $db_group)
    {
        $num_ver = $this->_io->prompt("\nVeuillez entrer le numero de la version de votre base de donnee", date('Y-m-d'));

        try {        
            $migrator = new Migrator(Database::instance()->use($db_group));

            $filename = $migrator->up($num_ver);

            $this->_io->ok("\n\t Base de donnees migrée avec succès.", true);
            $this->_io->info("\t Le fichier utilisé est : <".$filename.">.", true);
        }
        catch(\Exception $e) { 
            die($e->getMessage());
        }
    }
}