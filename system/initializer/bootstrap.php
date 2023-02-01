<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019 - 2021, Dimtrov Lab's
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.4.1
 */

use dFramework\core\Autoloader;
use dFramework\core\dFramework;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Autoloader.php';

Autoloader::load();

if (file_exists(APP_DIR . 'config' . DIRECTORY_SEPARATOR . 'constants.php'))
{
	require_once APP_DIR . 'config' . DIRECTORY_SEPARATOR . 'constants.php';
}
require_once SYST_DIR . 'constants' . DIRECTORY_SEPARATOR . 'constants.php';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'kint.php';

return (new dFramework)->init();
