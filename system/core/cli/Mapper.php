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
 * @version     3.0
 */


namespace dFramework\core\cli;

use Ahc\Cli\Input\Command;
use Ahc\Cli\IO\Interactor;
use Ahc\Cli\Output\Color;
use Ahc\Cli\Output\Writer;
use dFramework\core\loader\ClassMapper;

/**
 * Mapper
 *
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Cli
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @since       3.0
 * @file        /system/core/cli/Mapper.php
 */
class Mapper extends Command
{
    public function __construct()
    {
        parent::__construct('mapper', 'Service de mapping des classes pour l\'auto-chargement');

        $this
            ->option('-d --dept', 'Map les classes des dependances interne du framework')
            ->option('-a --app', 'Map les classes de votre application (Utile si vous n\'avez pas un mecanisme d\'autoloader)')
            // Usage examples:
            ->usage(
                '<bold>  dbot mapper -d</end> <comment> => Map toutes les classes des dependances interne du framework pour les charger automatiquement</end><eol/>' .
                '<bold>  dbot mapper -a</end> <comment> => Map les classes se trouvant dans un dossier specifique de votre application pour les charger automatiquement</end><eol/>' 
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

                $writer->bold->colors("\n\t<bgGreen> dFramework v3.0.0 </end></eol>");
            }

        }
        catch(\Exception $e) { }
        
        return true;
    }
}