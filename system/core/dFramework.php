<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019 - 2021, Dimtrov Lab's
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *  @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @link	    https://dimtrov.hebfree.org/works/dframework
 *  @version    3.4.0
 */

namespace dFramework\core;

use dFramework\core\exception\Exception;
use dFramework\core\loader\DotEnv;
use dFramework\core\loader\FileLocator;
use dFramework\core\loader\Load;
use dFramework\core\router\Dispatcher;
use dFramework\core\security\Session;
use Kint\Renderer\AbstractRenderer;
use Kint\Renderer\RichRenderer;
use MirazMac\Requirements\Checker As envChecker;

/**
 * dFramework
 *
 * Initialisation of application processing
 *
 * @package		dFramework
 * @subpackage	Core
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @since       1.0
 * @file        /system/core/dFramework.php
 */
class dFramework
{
    const VERSION = '3.4.0';

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
     * @throws \dFramework\core\exception\ConfigException
     * @throws \dFramework\core\exception\LoadException
     * @throws \dFramework\core\exception\RouterException
     */
    public function init() : self
    {
        /**
         * Verifie les exigences systeme
         */
        self::checkRequirements();

        /**
         * On charge le helper global
         */
        FileLocator::helper('global');

        /**
         * On initialise le parsing du fichier .env
         */
        DotEnv::init(ROOTPATH);

		/**
         * On configure quelques extensions
         */
        self::configure_ext();

        /**
         * Initialise les configurations du systeme a partir des fichiers se trouvant dans /app/config
         */
        Config::init();

        /**
         * Lance la capture des exceptions et erreurs
         */
        Exception::init();

        /**
         * Demarre la session
         */
        Session::start();

        /**
         * Autocharge les elements specifiés par le dev a travers le fichier /app/config/autoload
         */
        Load::init();

		/**
		 * Initalise l'outil de debug Kint
		 */
		self::initializeKint();

        return $this;
    }
    public function run()
    {
        /**
         * Initialise le routing de l'application. Point d'entrer de l'application
         */
        Dispatcher::init();
    }




    /**
     * Checks if PHP version is compatible and all extension needed are loaded.
     * @param string $minVersion Min supported version.
     */
    private static function checkRequirements()
    {
        $checker = (new envChecker)
            ->requirePhpVersion('>=7.2')
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

    private static function configure_ext()
    {
        /*
        * ------------------------------------------------------
        * Important charset-related stuff
        * ------------------------------------------------------
        *
        * Configure mbstring and/or iconv if they are enabled
        * and set MB_ENABLED and ICONV_ENABLED constants, so
        * that we don't repeatedly do extension_loaded() or
        * function_exists() calls.
        *
        * Note: UTF-8 class depends on this. It used to be done
        * in it's constructor, but it's _not_ class-specific.
        *
        */
        $charset = strtoupper(Config::get('general.charset'));
        ini_set('default_charset', $charset);

        if (extension_loaded('mbstring'))
        {
            define('MB_ENABLED', TRUE);
            // mbstring.internal_encoding is deprecated starting with PHP 5.6
            // and it's usage triggers E_DEPRECATED messages.
            @ini_set('mbstring.internal_encoding', $charset);
            // This is required for mb_convert_encoding() to strip invalid characters.
            // That's utilized by CI_Utf8, but it's also done for consistency with iconv.
            mb_substitute_character('none');
        }
        else
        {
            define('MB_ENABLED', FALSE);
        }

        // There's an ICONV_IMPL constant, but the PHP manual says that using
        // iconv's predefined constants is "strongly discouraged".
        if (extension_loaded('iconv'))
        {
            define('ICONV_ENABLED', TRUE);
            // iconv.internal_encoding is deprecated starting with PHP 5.6
            // and it's usage triggers E_DEPRECATED messages.
            @ini_set('iconv.internal_encoding', $charset);
        }
        else
        {
            define('ICONV_ENABLED', FALSE);
        }


        define('UTF8_ENABLED', defined('PREG_BAD_UTF8_ERROR') AND (ICONV_ENABLED === TRUE OR MB_ENABLED === TRUE) AND $charset === 'UTF-8');

    }

	/**
     * Initializes Kint
     */
    private static function initializeKint()
    {
		if (class_exists('\Kint\Renderer\RichRenderer')) {
			RichRenderer::$folder = false;
			RichRenderer::$sort   = AbstractRenderer::SORT_FULL;
		}
    }
}
