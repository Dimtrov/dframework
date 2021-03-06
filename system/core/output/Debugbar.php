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
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.3.0
 */

namespace dFramework\core\output;

use DebugBar\DataCollector\ConfigCollector;
use DebugBar\DataCollector\PDO\PDOCollector;
use DebugBar\StandardDebugBar;
use dFramework\core\Config;
use dFramework\core\utilities\Helpers;

/**
 * Debugbar
 * Class to display a debugbar to the page.
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Output
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.3.0
 * @file        /system/core/output/Debugbar.php
 */
class Debugbar extends StandardDebugBar
{
    public function __construct()
    {
        parent::__construct();

        $this->addCollector(new PDOCollector());
        $this->addCollector(new ConfigCollector(Config::get()));
    }

    /**
     * Returns a JavascriptRenderer for this instance
     * @param string $baseUrl
     * @param string $basePath
     * @return JavascriptRenderer
     */
    public function jsRenderer($baseUrl = null, $basePath = null)
    {
        $dir = explode(DIRECTORY_SEPARATOR, dirname(__DIR__, 2));
        $dir = end($dir);

        $src = Helpers::instance()->site_url($dir.'/dependencies/maximebf/debugbar/src/DebugBar/Resources');

        return parent::getJavascriptRenderer($src);
    }
}