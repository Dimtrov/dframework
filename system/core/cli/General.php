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

use Ahc\Cli\Helper\Shell;
use dFramework\core\loader\ClassMapper;
use Kahlan\Suite;
use Kahlan\Box\Box;
use Kahlan\Cli\Kahlan;
use Kahlan\Jit\ClassLoader;

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
            ->usage(
                '<bold>  dbot map --dept</end> <comment> => Map toutes les classes des dependances interne du framework pour les charger automatiquement</end><eol/>' .
                '<bold>  dbot map --app</end> <comment> => Map les classes se trouvant dans un dossier specifique de votre application pour les charger automatiquement</end><eol/>' 
            )->action(function($dept, $app) {
                try {
                    /**
                     * @var Command
                     */
                    $cli = $this;

                    if (empty($dept) AND empty($app))
                    {
                        $cli->_io->warn('Veuillez selectionner une option pour pouvoir lancer le mapping des classes.', true);
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
                        $cli->_io->info("\t => Traitement terminé avec succès.", true);
                        sleep(1.5);
                        $cli->_io->writer()->colors("\t => <boldGreen>".count($mapper->get_result_as_array())."</end> <white>classes collectées avec succès</end>");
                    }
                    else 
                    {
                        $cli->_io->error("\t Une erreur s'est produite lors de la collecte des classes \n");
                    }
            
                    sleep(1.5);
                    $cli->end();
                } 
                catch (\Throwable $th) {
                    die($th->getMessage());
                }

                return true;
            });
    }

    /**
     * Serveur de developpement
     * 
     * @return Command
     */
    protected function _serve() : Command
    {
        return (new Command('serve', 'Demarre le serveur de developpement.'))
            ->option('--host', 'Hote sur lequel votre application sera lancée. "localhost" par defaut', null, 'localhost')
            ->option('--port', 'Port sur lequel vous souhaitez demarrer le serveur. "3200" par defaut', null, 3200)
            ->option('--php', 'Chemin vers l\'executable php à utiliser pour démarrer le serveur.', null, PHP_BINARY)
            ->usage(
                '<bold>  dbot serve</end> <comment> ==> Lance le serveur sur l\'hote "http://localhost:3200" et y heberge votre application</end><eol/>' .
                '<bold>  dbot serve --port=8080</end> <comment> ==> Utilise le port "8080" pour lancer le serveur et heberger votre application</end><eol/>' . 
                '<bold>  dbot serve --host=local.dev --port=3000</end> <comment> ==> Heberge votre application sur l\'hote virtuel "local.dev" et utilise le port 3000 pour lancer le serveur</end><eol/>'
            )->action(function($host, $port, $php) {
                try {
                    /**
                     * @var Command
                     */
                    $cli = $this;

                    $cli->start('Service de lancement du serveur de developpement');
                    $cli->task('Demarrage du serveur de developpement');

                    sleep(2);
                    $cli->_io->ok("\t => Le serveur a démarré avec succès.", true);
                    
                    sleep(2.5);
                    $cli->_io->writer()->colors("\t => <white>Ouvrez votre navigateur a l'adresse</end> <boldGreen><http://".$host.":".$port."></end>");
            
                    sleep(1.5);
                    $cli->end();

                    $shell = new Shell($php . ' -S '. $host . ':' . $port . ' -t ' . escapeshellarg(\WEBROOT));
                    $shell->setOptions(dirname(\WEBROOT), null, 2.5)->execute()->isRunning();
                    
                    $shell->stop();
                    $shell->kill();
                } 
                catch (\Throwable $th) {
                    die($th->getMessage());
                }
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
                try {
                    /**
                     * @var Command
                     */
                    $cli = $this;

                    $cli->start('Service de réalisation de tests unitaires');
                   // $cli->task('Rollback des migrations');

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
                    
                    $cli->end();
                }
                catch (\Throwable $th) {
                    die($th->getMessage());
                }
            });
    }
}
