<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019, Dimtrov Sarl
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 *  @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.2.2
 */

namespace dFramework\core\loader;

use dFramework\core\exception\LoadException;

/**
 * FileLocator
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Loader
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework
 * @since       3.2.1
 * @file		/system/core/loader/FileLocator.php
 */
class FileLocator 
{
    /**
     * @param string $lang
     * @param string $locale
     * @return array
     */
    public static function lang(string $lang, string $locale) : array
    {
        $file = self::ensureExt($lang, 'json');
        $paths = [
            // Path to system languages
            SYST_DIR . 'constants' . DS . 'lang' . DS . $locale . DS . $file, 
            
            // Path to app languages
            RESOURCE_DIR . 'reserved' . DS . 'lang' . DS . $locale . DS . $file
        ];
        $file_exist = false;
        $languages = [];

        foreach ($paths As $path) 
        {
            if (file_exists($path) AND false !== ($lang = file_get_contents($path)))
            {
                $languages = array_merge($languages, json_decode($lang, true));
                $file_exist = true;
            }
        }
        
        if (true !== $file_exist) 
        {
            LoadException::except('
                Impossible de charger les langues  <b>'.$lang.'</b>. 
                <br> 
                Aucun fichier accessible en lecture et correspondant à cette langue n\'a été trouvé.
            ');
        }

        return $languages;
    }

    /**
     * @param string $helper
     * @return void
     */
    public static function helper(string $helper)
    {
        $file = self::ensureExt($helper, 'php');
        $paths = [
            // Path to system helpers
            SYST_DIR . 'helpers' . DS . $file,
            
            // Path to app helpers
            APP_DIR . 'helpers' . DS . $file
        ]; 
        $file_exist = false;

        foreach ($paths As $path) 
        {
            if (file_exists($path))
            {
                require_once $path;
                $file_exist = true;
            }
        }

        if (true !== $file_exist)
        {
            LoadException::except('
                Impossible de charger les fonctions d\'aide <b>'.$helper.'</b>. 
                <br> 
                Aucun fichier accessible en lecture et correspondant à ce helper n\'a été trouvé.
            ');
        }
    }

    /**
     * @param string $library
     * @return mixed
     */
    public static function library(string $library)
    {
        $library = explode('/', $library);
        $lib = ucfirst(end($library));
        $library[count($library) - 1] = $lib;
        
        $file = self::ensureExt(implode(DS, $library), 'php');
        $paths = [
            // Path to system helpers
            SYST_DIR . 'libraries' . DS . $file,
            
            // Path to app helpers
            APP_DIR . 'libraries' . DS . $file
        ]; 
        $file_syst = $file_exist = false;

        if (file_exists($paths[0])) 
        {
            $lib = "dFramework\\libraries\\$lib";
            $file_syst = $file_exist = true;
        }
        else if (file_exists($paths[1]))
        {
            require_once $paths[1];
            $file_exist = true;
        }

        if (true !== $file_exist)
        {
            LoadException::except(
                'Library file not found',
                'Impossible de charger la librairie <b>'.$lib.'</b>. 
                <br/>
                Aucun fichier accessible en lecture n\'a été trouvé pour cette librairie'
            );
        }

        if (true !== $file_syst AND !class_exists($lib))
        {
            LoadException::except(
                'Library class do not exist',
                'Impossible de charger la librarie <b>'.$lib.'</b>. 
                <br> 
                Le fichier correspondant à cette librairie ne contient pas de classe <b>'.$lib.'</b>'
            );
        }

        return Injector::factory($lib);
    }
    
    /**
     * @param string $model
     * @return dFramework\core\Model
     */
    public static function model(string $model)
    {
        $model = explode('/', $model);
        $mod = ucfirst(end($model));
        $mod = (!preg_match('#Model$#', $mod)) ? $mod.'Model' : $mod;
        $model[count($model) - 1] = $mod;

        $path = MODEL_DIR.self::ensureExt(implode(DS, $model), 'php');

        if (!file_exists($path))
        {
            LoadException::except(
                'Model file not found',
                'Impossible de charger le modele <b>'.str_replace('Model', '', $mod).'</b> souhaité. 
                <br/>
                Le fichier &laquo; '.$path.' &raquo; n\'existe pas'
            );
        }
        
        require_once $path;

        if (!class_exists($mod, false))
        {
            LoadException::except(
                'Model class do not exist',
                'Impossible de charger le model <b>'.str_replace('Model', '', $mod).'</br> souhaité. 
                <br/>
                Le fichier &laquo; '.$path.' &raquo; ne contient pas de classe <b>'.$mod.'</br>
            ');
        }

        return Injector::factory($mod);
    }

    /**
     * @param string $controller
     * @return dFramework\core\Controller
     */
    public static function controller(string $controller)
    {
        $controller = explode('/', $controller);
        $con = ucfirst(end($controller));
        $con = (!preg_match('#Controller$#', $con)) ? $con.'Controller' : $con;
        $controller[count($controller) - 1] = $con;

        $path = CONTROLLER_DIR.self::ensureExt(implode(DS, $controller), 'php');

        if (!file_exists($path))
        {
            LoadException::except(
                'Controller file not found',
                'Impossible de charger le controleur <b>'.str_replace('Controller', '', $con).'</b> souhaité. 
                <br/>
                Le fichier &laquo; '.$path.' &raquo; n\'existe pas'
            );
        }
        
        require_once $path;

        if (!class_exists($con, false))
        {
            LoadException::except(
                'Controller class do not exist',
                'Impossible de charger le controleur <b>'.str_replace('Controller', '', $con).'</br> souhaité. 
                <br> 
                Le fichier &laquo; '.$path.' &raquo; ne contient pas de classe <b>'.$con.'</b>'
            );
        }

        return Injector::factory($con);
    }

    
    /**
	 * Ensures a extension is at the end of a filename
	 *
	 * @param string $path
	 * @param string $ext
	 * @return string
	 */
	private static function ensureExt(string $path, string $ext = 'php'): string
	{
		if ($ext)
		{
			$ext = '.' . $ext;

			if (substr($path, -strlen($ext)) !== $ext)
			{
				$path .= $ext;
			}
		}

        return trim($path);
	}
}
