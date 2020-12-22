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

use dFramework\core\db\Seeder as DbSeeder;
use dFramework\core\db\seeder\Faker;

/**
 * Seeder
 *
 * A simple database seeder service
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Cli
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @since       3.2.3
 * @file        /system/core/cli/Seeder.php
 */
class Seeder extends Cli
{
    protected $_description = 'Service de remplissage de base de donnees';
    protected $_name = 'Seeder';

    public function __construct()
    {
        parent::__construct();
        
        $this
            ->argument('<file>', 'Nom du fichier à seeder')
            // Usage examples:
            ->usage(
                '<bold>  dbot seed user</end> <comment> => Execute la methode User::seed() contenue dans le fichier "/app/resources/database/seeds/User.php"</end><eol/>'
            );
    }

    public function execute($file)
    {
        try{
            $this->_startMsg();
            
            $seed = ucfirst(strtolower($file));
            $file = RESOURCE_DIR.'database'.DS.'seeds'.DS.$seed.'.php';

            if (!file_exists($file)) 
            {
                $this->_io->error('Impossible de demarrer le remplissage car le fichier "'.$file.'" n\'existe pas', true); 
                return $this->showHelp();
            }
            require_once $file;
                
            if (!class_exists($seed))
            {
                $this->_io->error('Impossible de demarrer le remplissage car le fichier "'.$file.'" ne contient pas de classe "'.$seed.'"', true); 
                return $this->showHelp();
            }
            
            $class = new $seed;

            if (!($class instanceof DbSeeder)) 
            {
                $this->_io->error('Impossible d\'effectuer le remplissage car la classe "'.$seed.'" n\'est pas une instance de "'.DbSeeder::class.'"', true); 
                return $this->showHelp();
            }
            if (!method_exists($class, 'seed')) 
            {
                $this->_io->error('Impossible d\'effectuer le remplissage car la classe "'.$seed.'" n\'implemente pas la methode "seed()"', true); 
                return $this->showHelp();
            }

            $this->_io->write("\n\t Remplissage en cours de traitement : Utilisation de la clase '".$seed."' \n");
            sleep(2.5);
            $class->seed(new Faker)->run();
            sleep(2);
            $this->_io->ok("\t => Remplissage terminé avec succès. \n");
            sleep(1.5);
            
            $this->_endMsg();
        }
        catch(\Exception $e) { 
            die($e->getMessage());
        }
        
        return true;
    }
}
