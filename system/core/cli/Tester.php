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

use Ahc\Cli\Input\Command;
use Kahlan\Box\Box;
use Kahlan\Suite;
use Kahlan\Cli\Kahlan;
use Kahlan\Jit\ClassLoader;

/**
 * Tester
 * Unit Test Interface
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Cli
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/guide/Validator.html
 * @since       3.2.1
 * @file        /system/core/cli/Tester.php
 */
class Tester extends Command
{
    public function __construct()
    {
        parent::__construct('tester', 'Service de réalisation de tests unitaires');

        $this
            ->usage(
                '<bold>  dbot test</end> <comment> ==> Verifie les tests effectués dans le dossier "/spec"</end><eol/>'
            );
    }

    public function execute()
    {
        try {
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
        catch(\Exception $e) { }
        
        return true;
    }
}
