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
 * @version     3.2.1
 */

namespace dFramework\core\cli;

use Ahc\Cli\IO\Interactor;
use Ahc\Cli\Output\Color;
use dFramework\core\loader\ClassMapper;

/**
 * Mapper
 *
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Cli
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @since       3.0
 * @file        /system/core/cli/Mapper.php
 */
class Mapper extends Cli
{
    protected $_description = 'Service de mapping des classes pour l\'auto-chargement';
    protected $_name = 'Mapper';

    public function __construct()
    {
        parent::__construct();

        $this
            ->option('-d --dept', 'Map les classes des dependances interne du framework')
            ->option('-a --app', 'Map les classes de votre application (Utile si vous n\'avez pas un mecanisme d\'autoloader)')
            // Usage examples:
            ->usage(
                '<bold>  dbot map -d</end> <comment> => Map toutes les classes des dependances interne du framework pour les charger automatiquement</end><eol/>' .
                '<bold>  dbot map -a</end> <comment> => Map les classes se trouvant dans un dossier specifique de votre application pour les charger automatiquement</end><eol/>' 
            );
    }

    // This method is auto called before `self::execute()` and receives `Interactor $io` instance
    public function interact(Interactor $io)
    {
        $color = new Color;

        if (!$this->app AND !$this->dept)
        {
            echo $color->warn('Veuillez selectionner une option pour pouvoir lancer le mapping des classes. <eol/>');
            $this->showHelp();
        }
    }
    public function execute()
    {
        try {
            $this->_startMsg();
            
            $this->_io->write("\n Recherche de classes en cours de traitement \n");
                        
            if ($this->app) 
            {
                $mapper = new ClassMapper([\APP_DIR], [
                    'excluded_paths' => [
                        rtrim(\CONTROLLER_DIR, DS),
                        rtrim(\MODEL_DIR, DS),
                        rtrim(\RESOURCE_DIR, DS),
                        \APP_DIR.'class',
                    ]
                ]);
                $export_file = \RESOURCE_DIR.'reserved'.\DS.'.classmap.php';
            }
            else 
            {
                $mapper = new ClassMapper([\SYST_DIR.'dependencies']);
                $export_file = \SYST_DIR.'constants'.\DS.'.classmap.php';
            }

            $mapper->process();
            
            if ($mapper->export_result_in_file($export_file))
            {
                $this->_io->writer()->colors("\t => <blue>Traitement terminé avec succès. </end><eol>");
                sleep(1.5);
                $this->_io->writer()->colors("\t => <boldGreen>".count($mapper->get_result_as_array())."</end> <white>classes collectées avec succès</end><eol>");
            }
            else 
            {
               $this->_io->error("\t Une erreur s'est produite lors de la collecte des classes \n");
            }
            
            sleep(1.5);
            $this->_endMsg();
        }
        catch (\Exception $e) { }
        
        return true;
    }
}
