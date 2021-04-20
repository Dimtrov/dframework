<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019 - 2021, Dimtrov Lab's
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @link	    https://dimtrov.hebfree.org/works/dframework
 * @version     3.3.0
 */

namespace dFramework\core\cli;

use dFramework\core\db\Database As Db;
use dFramework\core\db\Dumper;
use dFramework\core\db\Seeder;
use dFramework\core\db\seeder\Faker;

/**
 * Database
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Cli
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/guide/Validator.html
 * @since       3.3.0
 * @file        /system/core/cli/Database.php
 */
class Database extends Cli
{
    /**
     * Remplissage de base de donnees
     *
     * @return Command
     */
    protected function _seed() : Command
    {
        return (new Command('db:seed', 'Exécute le seeder spécifié pour remplir les données connues dans la base de données.'))
            ->argument('<file>', 'Nom du fichier à seeder')
            ->action(function($file) {
                /**
                 * @var Command
                 */
                $cli = $this;
                try {
                    $cli->start('Service de remplissage de base de donnees');
                    $cli->task('Demarrage du seed');

                    $seed = ucfirst(strtolower($file));
                    $file = RESOURCE_DIR.'database'.DS.'seeds'.DS.$seed.'.php';

                    if (!file_exists($file)) 
                    {
                        $cli->io->error('Impossible de demarrer le remplissage car le fichier "'.$file.'" n\'existe pas', true); 
                        return $cli->showHelp();
                    }
                    require_once $file;
                
                    if (!class_exists($seed))
                    {
                        $cli->io->error('Impossible de demarrer le remplissage car le fichier "'.$file.'" ne contient pas de classe "'.$seed.'"', true); 
                        return $cli->showHelp();
                    }
            
                    $class = new $seed;

                    if (!($class instanceof Seeder)) 
                    {
                        $cli->io->error('Impossible d\'effectuer le remplissage car la classe "'.$seed.'" n\'est pas une instance de "'.DbSeeder::class.'"', true); 
                        return $cli->showHelp();
                    }
                    if (!method_exists($class, 'seed')) 
                    {
                        $cli->io->error('Impossible d\'effectuer le remplissage car la classe "'.$seed.'" n\'implemente pas la methode "seed()"', true); 
                        return $cli->showHelp();
                    }

                    $cli->io->write("\n\t Remplissage en cours de traitement : Utilisation de la clase '".$seed."' \n");
                    sleep(2.5);
                    $class->seed(new Faker)->run();
                    sleep(2);
                    $cli->io->ok("\t => Remplissage terminé avec succès. \n");
                    sleep(1.5);
            
                    $cli->end();
                } 
                catch (\Throwable $th) {
                    $cli->showError($th);
                }
            });
    }

    /**
     * Import/export de base de donnees
     *
     * @return Command
     */
    protected function _dump() : Command
    {
        return (new Command('db:dump', 'Demarre l\'importation ou l\'exportation de votre base de données'))
            ->option('-b --backup', 'Cree une sauvegarde de la base de donnees')
            ->option('-u --upgrade', 'Importe un script de base de donnees')
            ->argument('[database]', 'Specifie la configuration de la base de donnees a utiliser. Par defaut il s\'agit de la configuration "default"')
            ->action(function($backup, $upgrade, $database) {
                /**
                 * @var Command
                 */
                $cli = $this;
                try {
                    if (empty($backup) AND empty($upgrade))
                    {
                        $cli->io->warn("\n Veuillez selectionner une option pour pouvoir executer cette tache.", true);
                        return $cli->showHelp();
                    }
                    $cli->start('Service de d\'import/export de base de donnees');

                    $dump = new Dumper($database);
                        
                    if (!empty($backup)) 
                    {
                        $cli->task('Sauvegarde de la base de données');
                        $num_ver = $cli->io->prompt("\nVeuillez entrer le numero de la version de votre base de donnee", date('Y-m-d'));

                        $filename = $dump->down($num_ver);
                
                        $cli->io->ok("\n\t Base de donnees sauvegardée avec succès.", true);
                        $cli->io->info("\t Fichier de sauvegarde: ".$filename);
                    }
                    else 
                    {
                        $cli->task('Importation de la base de données en cours');
                        $num_ver = $cli->io->prompt("\nVeuillez entrer le numero de la version de votre base de donnee", date('Y-m-d'));

                        $filename = $dump->up($num_ver);
                
                        $cli->io->ok("\n\t Base de donnees migrée avec succès.", true);
                        $cli->io->info("\t Fichier utilisé: ".$filename);
                    }

                    $cli->end();
                } 
                catch (\Throwable $th) {
                    $cli->showError($th);
                }
            });
    }
}
