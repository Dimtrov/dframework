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
 * @version     3.1
 */


namespace dFramework\core\cli;

use Ahc\Cli\Input\Command;
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
class Migrate extends Command
{
    private 
        $color,
        $io,
        $writer;


    public function __construct()
    {
        parent::__construct('migrate', 'Service de migration de base de donnees');

        $this->color = new Color;
        $this->io = new Interactor();
        $this->writer = new Writer();

        $this
            ->option('-b --backup', 'Cree une sauvegarde de la base de donnees')
            ->option('-u --upgrade', 'Importe un script de base de donnees')
            ->argument('[database]', 'Specifie la configuration de la base de donnees a utiliser. Par defaut il s\'agit de la configuration "default"')
            // Usage examples:
            ->usage(
                '<bold>  dbot migrate --backup</end> <comment> => Cree un fichier de sauvegarde la base de donnees dans le repertoire "/app/resources/migrations/" sous le nom "nombd_v[num_ver].sql"</end><eol/>' .
                '<bold>  dbot migrate -upgrade</end> <comment> => Importe le script de base de donnees du fichier "nombd_v[num_ver].sql" se trouvant dans le repertoire "/app/resources/migrations/"</end><eol/>' 
            );
    }

    // This method is auto called before `self::execute()` and receives `Interactor $io` instance
    public function interact(Interactor $io)
    {
        if (!$this->backup AND !$this->upgrade)
        {
            echo $this->color->warn("Veuillez selectionner une option pour pouvoir executer cette tache. \n");
            $this->showHelp();
        }
    }
    public function execute()
    {
        try{
            if ($this->backup)
            {
                $this->backup();
            }
            else 
            {
                $this->upgrade();
            }
        }
        catch(\Exception $e) { }
        
        return true;
    }

    private function backup()
    {
        $num_ver = $this->io->prompt('Veuillez entrer le numero de la version de votre base de donnee', date('Y-m-d'));

        try {        
            $db = new Database();
            $migrator = new Migrator($db);

            $filename = $migrator->down($num_ver);

            echo $this->color->ok("\n Base de donnees sauvegarder avec succes. \n");
            echo $this->color->ok("Retrouvez le fichier de sauvegarde via le chemin <".$filename.">. \n");
            $this->writer->bold->colors("\n\t<bgGreen> dFramework v".dFramework::VERSION." </end></eol>");
        }
        catch(\Exception $e) { }
    }

    private function upgrade()
    {
        echo $this->color->info("Fonctionnalite indisponible pour le moment \n");

        $this->writer->bold->colors("\n\t<bgGreen> dFramework v".dFramework::VERSION." </end></eol>");
    }
}