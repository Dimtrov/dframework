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

use Kahlan\Suite;
use Kahlan\Box\Box;
use Kahlan\Cli\Kahlan;
use Kahlan\Jit\ClassLoader;
use dFramework\core\loader\ClassMapper;

/**
 * General
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Cli
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/guide/Validator.html
 * @since       3.3.0
 * @file        /system/core/cli/General.php
 */
class General extends Cli
{
    /**
     * Mapping de classes
     *
     * @return Command
     */
    protected function _map() : Command
    {
        return (new Command('map', 'Collectionne et enregistre toutes les classes de ddependances pour l\'autochargement independamment de Composer'))
            ->option('--dept', 'Map les classes des dependances interne du framework')
            ->option('--app', 'Map les classes de votre application (Utile si vous n\'avez pas un mecanisme d\'autoloader)')
            ->action(function($dept, $app) {
                /**
                 * @var Command
                 */
                $cli = $this;
                try {
                    if (empty($dept) AND empty($app))
                    {
                        $cli->io->warn('Veuillez selectionner une option pour pouvoir lancer le mapping des classes.', true);
                        return $cli->showHelp();
                    }
                    $cli->start('Service de mapping des classes pour l\'auto-chargement');
                    $cli->task('Recherche de classes en cours de traitement');

                    if (!empty($app))
                    {
                        $mapper = new ClassMapper([\APP_DIR], [
                            'excluded_paths' => [
                                rtrim(\CONTROLLER_DIR, DS),
                                rtrim(\MODEL_DIR, DS),
                                rtrim(\RESOURCE_DIR, DS),
                                \APP_DIR.'class',
                            ]
                        ]);
                        $export_file = STORAGE_DIR . '.classmap.php';
                    }
                    else
                    {
                        $mapper = new ClassMapper([\SYST_DIR.'dependencies']);
                        $export_file = \SYST_DIR.'constants'.\DS.'.classmap.php';
                    }

                    $mapper->process();

                    if ($mapper->export_result_in_file($export_file))
                    {
                        $cli->io->info("\t => Traitement terminé avec succès.", true);
                        sleep(1.5);
                        $cli->io->writer()->colors("\t => <boldGreen>".count($mapper->get_result_as_array())."</end> <white>classes collectées avec succès</end>");
                    }
                    else
                    {
                        $cli->io->error("\t Une erreur s'est produite lors de la collecte des classes \n");
                    }

                    sleep(1.5);
                    $cli->end();
                }
                catch (\Throwable $th) {
                    $cli->showError($th);
                }

                return true;
            });
    }

    /**
     * Test unitaire
     *
     * @return Command
     */
    protected function _test() : Command
    {
        return (new Command('test', 'Execute les tests unitaires decrits'))
            ->usage('<bold>  dbot test</end> <comment> ==> Verifie les tests effectués dans le dossier "/spec"</end><eol/>')
            ->action(function() {
                /**
                 * @var Command
                 */
                $cli = $this;
                try {
                    $cli->start('Service de réalisation de tests unitaires');

                    error_reporting(E_ALL);
                    $kahlan_dir = SYST_DIR.'dependencies'.DS.'kahlan'.DS.'kahlan';

                    $autoload = require  $kahlan_dir.'/autoload.php';
                    $autoloader = $autoload("{$kahlan_dir}/vendor");
                    require $kahlan_dir. '/src/functions.php';

                    $GLOBALS['__composer_autoload_files']['337663d83d8353cc8c7847676b3b0937'] = true;

                    $box = \Kahlan\box('kahlan', new Box());

                    $box->service('suite.global', function() {
                        return new Suite();
                    });

                    $specs = new Kahlan([
                        'autoloader' => $autoloader,
                        'suite'      => $box->get('suite.global')
                    ]);
                    $specs->loadConfig([
                        '--reporter=verbose'
                    ]);
                    \initKahlanGlobalFunctions();

                    if ($autoloader instanceof ClassLoader) {
                        $commandLine = $specs->commandLine();
                        $autoloader->patch([
                            'include'    => $commandLine->get('include'),
                            'exclude'    => array_merge($commandLine->get('exclude'), ['Kahlan\\']),
                            'persistent' => $commandLine->get('persistent'),
                            'cachePath'  => rtrim(realpath(sys_get_temp_dir()), DS) . DS . 'kahlan',
                            'clearCache' => $commandLine->get('cc')
                        ]);

                        $specs->initPatchers();

                        foreach ($autoloader->files() as $fileIdentifier => $file) {
                                if (!empty($GLOBALS['__composer_autoload_files'][$fileIdentifier])) {
                                    continue;
                                }
                                $autoloader->loadFile($file);
                                $GLOBALS['__composer_autoload_files'][$fileIdentifier] = true;
                        }
                    }

                    $specs->run();
                    exit($specs->status());
                }
                catch (\Throwable $th) {
                    $cli->showError($th);
                }
            });
    }
}
