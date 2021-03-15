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

use dFramework\core\generator\Controller;
use dFramework\core\generator\Entity;

/**
 * Maker
 * Generate many files to create a minimal CRUD application
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Cli
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/
 * @since       3.2.1
 * @file        /system/core/cli/Maker.php
 */
class Maker extends Cli
{
   /**
     * Genere des controleurs
     *
     * @return Command
     */
    protected function _controller() : Command
    {
        return (new Command('make:controller', 'Crée un nouveau controleur'))
            ->argument('<name>', 'Definie le nom du contrôleur à créer.')
            ->option('--resource', 'Spécifie si on souhaite créer un contrôleur REST ou pas')
            ->option('--presenter', 'Spécifie si on souhaite créer un contrôleur de présentation ou pas')
            ->option('--path', 'Défini le répertoire de stockage du contrôleur créé')
            ->action(function($name, $resource, $presenter, $path) {
                /**
                 * @var Command
                 */
                $cli = $this;
                try {
                    $cli->start('Service de construction d\'application');
                    $cli->task('Création de contrôleur');
                    sleep(1);

                    $generator = new Controller($resource, $presenter);
                    $class_name = $generator->generate($name, $path);

                    $cli->io->ok('Contrôleur créer avec succès: '.$class_name);
                    $cli->end();
                }
                catch(\Throwable $th) {
                    $cli->showError($th);
                }
            });
    }
    
    /**
     * Genere des classes d'entites
     *
     * @return Command
     */
    protected function _entity() : Command
    {
        return (new Command('make:entity', 'Crée une nouvelle classe d\'entité'))
            ->argument('<name>', 'Definie le nom de l\'entité à créer.')
            ->option('--path', 'Défini le répertoire de stockage de l\'entité créée')
            ->option('--empty', 'Spécifie qu\'on veut avoir un fichier vide')
            ->action(function($name, $path, $empty) {
                /**
                 * @var Command
                 */
                $cli = $this;
                try {
                    $cli->start('Service de construction d\'application');
                    $cli->task('Création d\'entité');
                    sleep(1);

                    $generator = new Entity($empty);
                    $class_name = $generator->generate($name, $path);

                    $cli->io->ok('Contrôleur créer avec succès: '.$class_name);
                    $cli->end();
                }
                catch(\Throwable $th) {
                    $cli->showError($th);
                }
            });
    }
    
}
