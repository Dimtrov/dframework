<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Group Corp
 * This content is released under the MIT License (MIT) - See License.txt file
 *
 * @package	    dFramework
 * @author	    Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Group Corp. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    MIT License https://opensource.org/licenses/MIT
 * @link	    https://dimtrov.hebfree.org/works/dframework
 * @version     2.0
 */

/**
 * Lister
 *
 * List the controller files
 *
 * @class       Lister
 * @package		dFramework
 * @subpackage	Core
 * @category    Route
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/works/dframework/docs/systemcore/dic
 * @filename    /system/core/route/Lister.php
 * @credit      Web MVC Framework v.1.1.1 2016 - by Rosario Carvello <rosario.carvello@gmail.com>
 */


namespace dFramework\core\route;

use DirectoryIterator;

class Lister
{
    /**
     * Gets folders and subfolders of a given directory.
     *
     * @param string $dir Starting directory
     * @return array
     */
    public static function listFolders(string $dir=CONTROLLER_DIR) : array
    {
        $directories = [];

        $elements = new DirectoryIterator($dir);
        foreach($elements As $element)
        {
            if(false === $element->isDot() AND true === $element->isDir())
            {
                $folder = $element->getPathname();
                $directories[] = str_replace(CONTROLLER_DIR, '', $folder);
                $directories = array_merge($directories, self::listFolders($folder));
            }
        }
        return $directories;
    }

    /**
     * Gets all directories: framework and application's sub systems
     *
     * @return array|mixed
     */
    public static function getDirectories()
    {
        $directories = unserialize(CLASSES);
        $subSystems = [];
        $definedSubSystems = unserialize(SUBSYSTEMS);

        if (is_array($definedSubSystems))
        {
            foreach ($definedSubSystems as $key => $value)
            {
                $subSystems[] = CONTROLLER_DIR.$value;
                $subSystems[] = VIEW_DIR.$value;
                //$subSystems[] = MODEL_DIR .DIRECTORY_SEPARATOR . $value;
            }
        }

        // Merges arrays of subsystems and classes directories
        if (!empty($subSystems))
        {
            $directories = array_merge($subSystems, $directories);
        }
        return $directories;
    }

    /**
     * Verifies if the url contains a subsystem folder.
     *
     * @param string $url Url to parse
     * @return string|null The current subsystem folder
     */
    public static function getCurrentSubSystem(?string $url = '') : ?string
    {
        $currentSubSystem = "";
        $subSystems = unserialize(SUBSYSTEMS);

        if (is_array($subSystems))
        {
            foreach ($subSystems as $key => $value)
            {
                $value = str_replace(DS, '/', $value);
                if (substr($url, 0, strlen($value)) === $value)
                {
                    $temp = substr($url, 0, strlen($value));
                    if (strlen($temp) > strlen($currentSubSystem)) {
                        $currentSubSystem = $temp;
                    }
                }
            }
        }
        return $currentSubSystem;
    }

}