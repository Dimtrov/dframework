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
 * @link	    https://dimtrov.hebfree.org/works/dframework
 * @version     3.1
 */


namespace dFramework\core\cli;

use Ahc\Cli\Input\Command;
use Ahc\Cli\IO\Interactor;
use Ahc\Cli\Output\Color;
use Ahc\Cli\Output\Writer;
use dFramework\core\dFramework;
use dFramework\core\loader\ClassMapper;

/**
 * Entity
 *
 * A simple ORM service
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Cli
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @since       3.1
 * @file        /system/core/cli/Entity.php
 */
class Entity extends Command
{
    public function __construct()
    {
        parent::__construct('entity', 'Service d\'hydratation des entites et de remplissage de base de donnees');

        $this
            ->option('-g --generate', 'Genere un ou plusieurs fichier d\'entité')
            ->option('-p --populate', 'Remplit une table de la base de donnees')
            ->option('-d --database', 'Specifie la configuration de la base de donnees a utiliser. Par defaut il s\'agit de la configuration "default"')
            // Usage examples:
            ->usage(
                '<bold>  dbot entity -g users</end> <comment> => Genere le fichier d\'entite "/app/entities/UsersEntity.php" faisant reference a la table users</end><eol/>' .
                '<bold>  dbot entity -g customers/bills</end> <comment> => Genere le fichier d\'entite "/app/entities/customers/BillsEntity.php" faisant reference a la table bills</end><eol/>' .
                '<bold>  dbot entity -g users&customers/bills</end> <comment> => Genere les fichier d\'entite "/app/entities/UsersEntity.php" et "/app/entities/customers/BillsEntity.php" faisant reference aux tables users et bills respectivement</end><eol/>' .
                '<eol/>'.
                '<bold>  dbot entity -p users</end> <comment> => Crée 5 enregistrements dans la table users</end><eol/>' .
                '<bold>  dbot entity -p bills:30</end> <comment> => Crée 30 enregistrements dans la table bills</end><eol/>' .
                '<bold>  dbot entity -p users&bills:30</end> <comment> => Crée 5 enregistrements dans la table users et 30 enregistrements dans la table bills</end><eol/>' 
            );
    }

    // This method is auto called before `self::execute()` and receives `Interactor $io` instance
    public function interact(Interactor $io)
    {
        $color = new Color;

        if(!$this->app AND !$this->dept)
        {
            echo $color->warn('Veuillez selectionner une option pour pouvoir lancer le mappind des classes');
            $this->showHelp();
        }
        // Collect missing opts/args
        if ($this->app) {
            $this->set('app', $io->prompt('Entrer le dossier des classes a mapper'));
        }
    }
    public function execute()
    {
        try{
            $io = $this->app()->io();
            $color = new Color;
            $writer = new Writer();

            if($this->app) 
            {
                $io->boldYellow('Fonctionnalite en cours de test. Indisponible pour le moment');
            }
            else if($this->dept) 
            {
                $io->write("\n *******  Mapping des classes en cours de traitement  ******** \n", true);

                $mapper = (new ClassMapper())->process();

                if($mapper->export_result_in_file(SYST_DIR.'constants'.DS.'.classmap.php'))
                {
                    $io->write("\t --- Traitement terminé", true);
                   echo $color->ok("\t ".count($mapper->get_result_as_array())." Classes remappées avec succès \n");
                }
                else 
                {
                   echo $color->error("\t Une erreur s'est produite pendant le mapping des classes");
                }

                $writer->bold->colors("\n\t<bgGreen> dFramework v".dFramework::VERSION." </end></eol>");
            }

        }
        catch(\Exception $e) { }
        
        return true;
    }
}