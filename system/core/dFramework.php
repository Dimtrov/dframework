<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019, Dimtrov Sarl
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 *  @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @link	    https://dimtrov.hebfree.org/works/dframework
 *  @version    3.0
 */


namespace dFramework\core;

use dFramework\core\exception\Exception;
use dFramework\core\loader\Load;
use dFramework\core\route\Router;
use dFramework\core\security\Session;
use MirazMac\Requirements\Checker As envChecker;

/**
 * dFramework
 *
 * Initialisation of application processing
 *
 * @package		dFramework
 * @subpackage	Core
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @since       1.0
 * @file        /system/core/dFramework.php
 */

class dFramework
{
    const VERSION = '3.0';

	/**
	 * @var array Liste des extensions requises pour le fonctionnement du framework
	 */
	public static $required_extensions = [
        'pdo', 
        'reflection', 
        'openssl', 
        'dom', 
        'xml', 
        'fileinfo'
    ];
	
	
    /**
     * @throws Exception
     * @throws \ReflectionException
     * @throws exception\ConfigException
     * @throws exception\LoadException
     * @throws exception\RouterException
     */
    public static function init()
    {
        self::checkRequirements();

        /**
         * Lance la capture des exceptions et erreurs
         */
        Exception::init();

        /**
         * Initialise les configurations du systeme a partir des fichiers se trouvant dans /app/config
         */
        Config::init();

        /**
         * Demarre la session
         */
        Session::start();

        /**
         * Autocharge les elements specifiés par le dev a travers le fichier /app/config/autoload
         */
        Load::init();

        /**
         * Initialise le routing de l'application. Point d'entrer de l'application
         */
        Router::init();
    }




    /**
     * Checks if PHP version is compatible and all extension needed are loaded.
     * @param string $minVersion Min supported version.
     */
    private static function checkRequirements()
    {
        $checker = (new envChecker)
            ->requirePhpVersion('>=7.1')
            ->requirePhpExtensions(self::$required_extensions)
            ->requireDirectory(SYST_DIR, envChecker::CHECK_IS_READABLE)
            ->requireDirectory(APP_DIR, envChecker::CHECK_IS_READABLE);

        $output = $checker->check();
        if (! $checker->isSatisfied()) 
        {
            echo '<h3>An error encourred</h3>';
            exit(join('<br/> ', $checker->getErrors()));
        }
    }

}
