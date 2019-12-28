<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Group Corp
 * This content is released under the Creative Commons License 3.0 (CC) BY-SA
 *
 * @package	    dFramework
 * @author	    Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Group Corp. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    Creative Commons BY-SA License http://creativecommons.org/licenses/by-sa/3.0/
 * @link	    https://dimtrov.hebfree.org/works/dframework
 * @version     2.0
 */

/**
 * dFramework
 *
 * Initialisation of process
 *
 * @class       dFramework
 * @package		dFramework
 * @subpackage	Core
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 */


namespace dFramework\core;


use dFramework\core\exception\Exception;
use dFramework\core\loader\Load;
use dFramework\core\route\Router;

class dFramework
{
    /**
     * @throws Exception
     * @throws \ReflectionException
     * @throws exception\ConfigException
     * @throws exception\LoadException
     * @throws exception\RouterException
     */
    public static function init()
    {
        self::checkPHPVersion('7.0');

        session_start();
        if(!isset($_SESSION['df_session'])) {
            $_SESSION['df_session'] = [];
        }

        require_once SYST_DIR . 'constants.php';

        /**
         * Lance la capture des exceptions et erreurs
         */
        Exception::init();

        /**
         * Initialise les configurations du systeme a partir des fichiers se trouvant dana /app/config
         */
        Config::init();

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
    private static function checkPHPVersion($minVersion="5.3.6")
    {
        if (version_compare(phpversion(), $minVersion, '<'))
        {
            echo 'The PHP Version of your server is not compatible with this framework. please use the version <b>'.$minVersion.'</b> or more';
            exit(3);
        }
        self::checkExtension();
    }

    /**
     * Verify if all extensions needed are loaded
     */
    private static function checkExtension()
    {
        $extensions = ['pdo', 'reflection'];
        foreach ($extensions As $extension)
        {
            if (!extension_loaded($extension))
            {
                echo 'Error: <b>'.ucfirst($extension).'</b> Extension is not loaded. Configure PHP with this extension.';
                exit(3);
            }
        }
    }
}

const dF_VERSION = '2.0';