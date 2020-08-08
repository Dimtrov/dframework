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
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.2.1
 */

$config = require_once __DIR__ . DIRECTORY_SEPARATOR . '.bootstrap.config.php';

foreach ($config As $key => $value)
{
    $config[$key] = __DIR__.DIRECTORY_SEPARATOR.trim($value, '/');
}
extract($config);


if (($_temp = realpath($system_path)) !== FALSE)
{
    $system_path = $_temp.DIRECTORY_SEPARATOR;
}
else
{
    $system_path = strtr(rtrim($system_path, '/\\'), '/\\', DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
}
// Is the system path correct?
if (!is_dir($system_path))
{
    header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
    echo 'Your system folder path does not appear to be set correctly. ';
    echo 'Please open the following file and correct this: "'.__DIR__.DIRECTORY_SEPARATOR.'.bootstrap.config.php"';
    exit(3); // EXIT_CONFIG
}


// The path to the "application" directory
if (is_dir($application_folder))
{
    if (($_temp = realpath($application_folder)) !== FALSE)
    {
        $application_folder = $_temp;
    }
    else
    {
        $application_folder = strtr(rtrim($application_folder, '/\\'), '/\\', DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR);
    }
}
elseif (is_dir($system_path.$application_folder.DIRECTORY_SEPARATOR))
{
    $application_folder = $system_path.strtr(trim($application_folder, '/\\'), '/\\', DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR);
}
else
{
    header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
    echo 'Your application folder path does not appear to be set correctly. ';
    echo 'Please open the following file and correct this: "'.__DIR__.DIRECTORY_SEPARATOR.'.bootstrap.config.php"';
    exit(3); // EXIT_CONFIG
}



// The path to the "composer autoload" directory
if (!empty($composer_autoload_file))
{
    $composer_autoload_file = rtrim($composer_autoload_file, '/\\');

    if (is_file($composer_autoload_file))
    {
        require_once $composer_autoload_file;
    }
    else if (is_dir($composer_autoload_file) AND is_file($composer_autoload_file.DIRECTORY_SEPARATOR.'autoload.php'))
    {
        require_once $composer_autoload_file.DIRECTORY_SEPARATOR.'autoload.php';
    }
}


define('SYST_DIR', rtrim($system_path, '/\\').DIRECTORY_SEPARATOR);

define('APP_DIR', rtrim($application_folder, '/\\').DIRECTORY_SEPARATOR);

define('WEBROOT', __DIR__.DIRECTORY_SEPARATOR);

define('BASE_URL', trim(dirname($_SERVER['SCRIPT_NAME'], 2), '\\'));


require_once SYST_DIR.'Autoloader.php';
