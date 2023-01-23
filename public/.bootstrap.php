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
 * @version     3.4.0
 */


define('DS', DIRECTORY_SEPARATOR);

$config = require_once __DIR__ . DS . '.bootstrap.config.php';

foreach ($config As $key => $value)
{
    $config[$key] = __DIR__.DS.trim($value, '/');
}
extract($config);


if (($_temp = realpath($system_path)) !== FALSE)
{
    $system_path = $_temp.DS;
}
else
{
    $system_path = strtr(rtrim($system_path, '/\\'), '/\\', DS.DS).DS;
}
// Is the system path correct?
if (!is_dir($system_path))
{
    header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
    echo 'Your system folder path does not appear to be set correctly. ';
    echo 'Please open the following file and correct this: "'.__DIR__.DS.'.bootstrap.config.php"';
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
        $application_folder = strtr(rtrim($application_folder, '/\\'), '/\\', DS.DS);
    }
}
elseif (is_dir($system_path.$application_folder.DS))
{
    $application_folder = $system_path.strtr(trim($application_folder, '/\\'), '/\\', DS.DS);
}
else
{
    header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
    echo 'Your application folder path does not appear to be set correctly. ';
    echo 'Please open the following file and correct this: "'.__DIR__.DS.'.bootstrap.config.php"';
    exit(3); // EXIT_CONFIG
}

// The path to the "storage" directory
if (is_dir($storage_dir))
{
    if (($_temp = realpath($storage_dir)) !== FALSE)
    {
        $storage_dir = $_temp;
    }
    else
    {
        $storage_dir = strtr(rtrim($storage_dir, '/\\'), '/\\', DS.DS);
    }
}
elseif (is_dir($system_path.$storage_dir.DS))
{
    $storage_dir = $system_path.strtr(trim($storage_dir, '/\\'), '/\\', DS.DS);
}
elseif (is_dir($application_folder.$storage_dir.DS))
{
    $storage_dir = $application_folder.strtr(trim($storage_dir, '/\\'), '/\\', DS.DS);
}
else
{
    header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
    echo 'Your storage folder path does not appear to be set correctly. ';
    echo 'Please open the following file and correct this: "'.__DIR__.DS.'.bootstrap.config.php"';
    exit(3); // EXIT_CONFIG
}

// The path to the "composer autoload" directory
if (!empty($composer_autoload_file))
{
    $composer_autoload_file = rtrim($composer_autoload_file, '/\\');

    if (is_dir($composer_autoload_file))
    {
        $composer_autoload_file .= DS.'autoload.php';
    }
    if (!is_file($composer_autoload_file))
    {
        $composer_autoload_file = dirname(__DIR__).DS.'vendor'.DS.'auoload.php';
    }
    if (is_file($composer_autoload_file))
    {
        require_once $composer_autoload_file;
    }
}


define('SYST_DIR', rtrim($system_path, '/\\').DS);

define('APP_DIR', rtrim($application_folder, '/\\').DS);

define('STORAGE_DIR', rtrim($storage_dir, '/\\').DS);

define('WEBROOT', __DIR__.DS);

define('BASE_URL', trim(dirname($_SERVER['SCRIPT_NAME'], 2), '\\'));

return require_once SYST_DIR . 'initializer' . DS . 'bootstrap.php';
