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
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.4.0
 */


 /**
 * dFramework Path Helpers
 *
 * @package		dFramework
 * @subpackage	Helpers
 * @category	Directory
 * @since 		3.4.0
 * @file 		/system/helpers/path.php
 */


// ------------------------------------------------------------------------


if (!function_exists('css_path'))
{
    /**
     * CSS PATH
     *
     * Renvoie le chemin absolu d'un fichier css.
     *
     * @param	string	$name nom du fichier dont on veut avoir le chemin
     * @return	string
     */
    function css_path(string $name = '') : string
    {
		if (!empty($name))
		{
			$name = DS . ltrim($name, '/\\');
		}
		return WEBROOT . 'css' . str_replace('/', DS, $name);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('js_path'))
{
    /**
     * JS PATH
     *
     * Renvoie le chemin absolu d'un fichier js.
     *
     * @param	string	$name nom du fichier dont on veut avoir le chemin
     * @return	string
     */
    function js_path(string $name = '') : string
    {
		if (!empty($name))
		{
			$name = DS . ltrim($name, '/\\');
		}
        return WEBROOT. 'js' . str_replace('/', DS, $name);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('lib_path'))
{
    /**
     * LIB PATH
     *
     * Renvoie le chemin absolu d'un fichier d'une librairie
     *
     * @param	string	$name nom du fichier dont on veut avoir le chemin
     * @return	string
     */
    function lib_path(string $name = '') : string
    {
		if (!empty($name))
		{
			$name = DS . ltrim($name, '/\\');
		}
        return WEBROOT. 'lib' . str_replace('/', DS, $name);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('less_path'))
{
    /**
     * LESS PATH
     *
     * Renvoie le chemin absolu d'un fichier less.
     *
     * @param	string	$name nom du fichier dont on veut avoir le chemin
     * @return	string
     */
    function less_path(string $name = '') : string
    {
		if (!empty($name))
		{
			$name = DS . ltrim($name, '/\\');
		}
        return WEBROOT. 'less' . str_replace('/', DS, $name);
    }
}

// ------------------------------------------------------------------------


if (!function_exists('img_path'))
{
    /**
     * IMG PATH
     *
     * Renvoie le chemin absolu d'une image
     *
     * @param	string	$name nom du fichier dont on veut avoir le chemin
     * @return	string
     */
    function img_path(string $name = '') : string
    {
		if (!empty($name))
		{
			$name = DS . ltrim($name, '/\\');
		}
        return WEBROOT. 'img' . str_replace('/', DS, $name);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('docs_path'))
{
    /**
     * DOCS PATH
     *
     * Renvoie le chemin absolu d'un document
     *
     * @param	string	$name nom du fichier dont on veut avoir le chemin
     * @return	string
     */
    function docs_path(string $name = '') : string
    {
		if (!empty($name))
		{
			$name = DS . ltrim($name, '/\\');
		}
        return WEBROOT . 'docs' . str_replace('/', DS, $name);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('video_path'))
{
    /**
     * VIDEO PATH
     *
     * Renvoie le chemin absolu d'une vidéo
     *
     * @param	string	$name nom du fichier dont on veut avoir le chemin
     * @return	string
     */
    function video_path(string $name = '') : string
    {
		if (!empty($name))
		{
			$name = DS . ltrim($name, '/\\');
		}
        return WEBROOT. 'videos'. str_replace('/', DS, $name);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('public_path'))
{
    /**
     * PUBLIC PATH
     *
     * Renvoie le chemin absolu du dossier public
     *
     * @param	string	$name nom du fichier dont on veut avoir le chemin
     * @return	string
     */
    function public_path(string $name = '') : string
    {
		if (!empty($name))
		{
			$name = ltrim($name, '/\\');
		}
        return WEBROOT. str_replace('/', DS, $name);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('class_path'))
{
    /**
     * CLASS PATH
     *
     * Renvoie le chemin absolu d'une classe applicative
     *
     * @param	string	$name nom du fichier dont on veut avoir le chemin
     * @return	string
     */
    function class_path(string $name = '') : string
    {
		if (!empty($name))
		{
			$name = DS . ltrim($name, '/\\');
		}
        return APP_DIR. 'class'. str_replace('/', DS, $name);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('config_path'))
{
    /**
     * CONFIG PATH
     *
     * Renvoie le chemin absolu d'un fichier de configuration
     *
     * @param	string	$name nom du fichier dont on veut avoir le chemin
     * @return	string
     */
    function config_path(string $name = '') : string
    {
		if (!empty($name))
		{
			$name = DS . ltrim($name, '/\\');
		}
        return APP_DIR. 'config'. str_replace('/', DS, $name);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('controller_path'))
{
    /**
     * CONTROLLER PATH
     *
     * Renvoie le chemin absolu d'un contrôleur
     *
     * @param	string	$name nom du fichier dont on veut avoir le chemin
	 * @param	bool	$only specifie si on veut seulement les contrôleurs (fichier php ayant le suffixe Controller)
     * @return	string
     */
    function controller_path(string $name = '', bool $only = true) : string
    {
		if (!empty($name))
		{
			$name = ltrim($name, '/\\');

			if ($only === true AND !preg_match('#Controller\.php$#', $name))
			{
				$name = ucfirst(strtolower($name)).'Controller.php';
			}
		}
        return CONTROLLER_DIR . str_replace('/', DS, $name);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('entity_path'))
{
    /**
     * ENTITY PATH
     *
     * Renvoie le chemin absolu d'une entité
     *
     * @param	string	$name nom du fichier dont on veut avoir le chemin
	 * @param	bool	$only specifie si on veut seulement les fichiers d'entité (fichiers php ayant le suffixe Entity)
     * @return	string
     */
    function entity_path(string $name = '', bool $only = true) : string
    {
		if (!empty($name))
		{
			$name = ltrim($name, '/\\');

			if ($only === true AND !preg_match('#Entity\.php$#', $name))
			{
				$name = ucfirst($name).'Entity.php';
			}
		}
        return ENTITY_DIR . str_replace('/', DS, $name);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('helper_path'))
{
    /**
     * HELPER PATH
     *
     * Renvoie le chemin absolu d'un helper
     *
     * @param	string	$name nom du fichier dont on veut avoir le chemin
     * @param	bool	$system specifie s'il s'agit des helpers systeme ou applicatif
     * @return	string
     */
    function helper_path(string $name = '', bool $system = false) : string
    {
		if (!empty($name))
		{
			$name = DS . ltrim($name, '/\\');
		}
		if ($system === true) {
			return SYST_DIR. 'helpers'. str_replace('/', DS, $name);
		}
        return APP_DIR. 'helpers'. str_replace('/', DS, $name);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('library_path'))
{
    /**
     * LIBRARY PATH
     *
     * Renvoie le chemin absolu d'une librairie
     *
     * @param	string	$name nom du fichier dont on veut avoir le chemin
     * @param	bool	$system specifie s'il s'agit des librairies systeme ou applicatif
     * @return	string
     */
    function library_path(string $name = '', bool $system = false) : string
    {
		if (!empty($name))
		{
			$name = DS . ltrim($name, '/\\');
		}
		if ($system === true) {
			return SYST_DIR. 'libraries'. str_replace('/', DS, $name);
		}
        return APP_DIR. 'libraries'. str_replace('/', DS, $name);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('middleware_path'))
{
    /**
     * MIDDLEWARE PATH
     *
     * Renvoie le chemin absolu d'un middleware
     *
     * @param	string	$name nom du fichier dont on veut avoir le chemin
     * @param	bool	$system specifie s'il s'agit des middlewares systeme ou applicatif
	 * @param	bool	$only specifie si on veut seulement les middlewares (fichiers php ayant le suffixe Middleware)
     * @return	string
     */
    function middleware_path(string $name = '', bool $system = false, bool $only = true) : string
    {
		if (!empty($name))
		{
			$name = DS . ltrim($name, '/\\');

			if ($only === true AND !preg_match('#Middleware\.php$#', $name))
			{
				$name = ucfirst($name).'Middleware.php';
			}
		}
		if ($system === true) {
			return SYST_DIR. 'middlewares'. str_replace('/', DS, $name);
		}
        return APP_DIR. 'middlewares'. str_replace('/', DS, $name);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('model_path'))
{
    /**
     * MODEL PATH
     *
     * Renvoie le chemin absolu d'un modèle
     *
     * @param	string	$name nom du fichier dont on veut avoir le chemin
	 * @param	bool	$only specifie si on veut seulement les modèles (fichiers php ayant le suffixe Model)
     * @return	string
     */
    function model_path(string $name = '', bool $only = true) : string
    {
		if (!empty($name))
		{
			$name = ltrim($name, '/\\');

			if ($only === true AND !preg_match('#Model\.php$#', $name))
			{
				$name = ucfirst(strtolower($name)).'Model.php';
			}
		}
        return MODEL_DIR . str_replace('/', DS, $name);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('resource_path'))
{
    /**
     * RESOURCE PATH
     *
     * Renvoie le chemin absolu d'un fichier ressource
     *
     * @param	string	$name nom du fichier dont on veut avoir le chemin
     * @return	string
     */
    function resource_path(string $name = '') : string
    {
		if (!empty($name))
		{
			$name = ltrim($name, '/\\');
		}
        return RESOURCE_DIR . str_replace('/', DS, $name);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('migration_path'))
{
    /**
     * MIGRATION PATH
     *
     * Renvoie le chemin absolu d'un fichier de migration
     *
     * @param	string	$name nom du fichier dont on veut avoir le chemin
     * @return	string
     */
    function migration_path(string $name = '') : string
    {
		if (!empty($name))
		{
			$name = ltrim($name, '/\\');
		}
        return DB_MIGRATION_DIR . str_replace('/', DS, $name);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('seed_path'))
{
    /**
     * SEED PATH
     *
     * Renvoie le chemin absolu d'un fichier de seed
     *
     * @param	string	$name nom du fichier dont on veut avoir le chemin
     * @return	string
     */
    function seed_path(string $name = '') : string
    {
		if (!empty($name))
		{
			$name = ltrim($name, '/\\');
		}
        return DB_SEED_DIR . str_replace('/', DS, $name);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('lang_path'))
{
    /**
     * LANG PATH
     *
     * Renvoie le chemin absolu d'un fichier de langue
     *
     * @param	string	$name nom du fichier dont on veut avoir le chemin
	 * @param	bool	$system specifie s'il s'agit des langues systeme ou applicatif
     * @return	string
     */
    function lang_path(string $name = '', bool $system = false) : string
    {
		if (!empty($name))
		{
			$name = DS . ltrim($name, '/\\');
		}
		if ($system === true) {
			return SYST_DIR. 'constants' . DS . 'lang' . str_replace('/', DS, $name);
		}
        return RESOURCE_DIR. 'lang'. str_replace('/', DS, $name);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('service_path'))
{
    /**
     * SERVICE PATH
     *
     * Renvoie le chemin absolu d'un service
     *
     * @param	string	$name nom du fichier dont on veut avoir le chemin
	 * @param	bool	$only specifie si on veut seulement les services (fichier php ayant le suffixe Service)
     * @return	string
     */
    function service_path(string $name = '', bool $only = true) : string
    {
		if (!empty($name))
		{
			$name = ltrim($name, '/\\');

			if ($only === true AND !preg_match('#Service\.php$#', $name))
			{
				$name = ucfirst($name).'Service.php';
			}
		}
        return SERVICE_DIR . str_replace('/', DS, $name);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('view_path'))
{
    /**
     * VIEW PATH
     *
     * Renvoie le chemin absolu d'une vue
     *
     * @param	string	$name nom du fichier dont on veut avoir le chemin
     * @return	string
     */
    function view_path(string $name = '') : string
    {
		if (!empty($name))
		{
			$name = ltrim($name, '/\\');
		}
        return VIEW_DIR . str_replace('/', DS, $name);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('layout_path'))
{
    /**
     * LAYOUT PATH
     *
     * Renvoie le chemin absolu d'un layout
     *
     * @param	string	$name nom du fichier dont on veut avoir le chemin
     * @return	string
     */
    function layout_path(string $name = '') : string
    {
		if (!empty($name))
		{
			$name = ltrim($name, '/\\');
		}
        return LAYOUT_DIR . str_replace('/', DS, $name);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('partial_path'))
{
    /**
     * PARTIAL PATH
     *
     * Renvoie le chemin absolu d'une partie de vue
     *
     * @param	string	$name nom du fichier dont on veut avoir le chemin
     * @return	string
     */
    function partial_path(string $name = '') : string
    {
		if (!empty($name))
		{
			$name = DS . ltrim($name, '/\\');
		}
        return VIEW_DIR . 'partials' . str_replace('/', DS, $name);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('app_path'))
{
    /**
     * APP PATH
     *
     * Renvoie le chemin absolu d'un fichier du dossier app
     *
     * @param	string	$name nom du fichier dont on veut avoir le chemin
     * @return	string
     */
    function app_path(string $name = '') : string
    {
		if (!empty($name))
		{
			$name = ltrim($name, '/\\');
		}
        return APP_DIR . str_replace('/', DS, $name);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('cache_path'))
{
    /**
     * CACHE PATH
     *
     * Renvoie le chemin absolu d'un fichier mis en cache
     *
     * @param	string	$name nom du fichier dont on veut avoir le chemin
     * @return	string
     */
    function cache_path(string $name = '') : string
    {
		if (!empty($name))
		{
			$name = ltrim($name, '/\\');
		}
        return VIEW_CACHE_DIR . str_replace('/', DS, $name);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('dump_path'))
{
    /**
     * DUMP PATH
     *
     * Renvoie le chemin absolu d'un fichier de sauvegarde (dump) de la base de donnees
     *
     * @param	string	$name nom du fichier dont on veut avoir le chemin
     * @return	string
     */
    function dump_path(string $name = '') : string
    {
		if (!empty($name))
		{
			$name = ltrim($name, '/\\');
		}
        return DB_DUMP_DIR . str_replace('/', DS, $name);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('storage_path'))
{
    /**
     * STORAGE PATH
     *
     * Renvoie le chemin absolu d'un fichier du dossier storage
     *
     * @param	string	$name nom du fichier dont on veut avoir le chemin
     * @return	string
     */
    function storage_path(string $name = '') : string
    {
		if (!empty($name))
		{
			$name = ltrim($name, '/\\');
		}
        return STORAGE_DIR . str_replace('/', DS, $name);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('css_exist'))
{
    /**
     * CSS EXIST
     *
     * Verifie si un fichier css ou un sous dossier du repertoire /public/css existe
     *
     * @param	string	$name nom du fichier dont on veut verifier
     * @return	bool
     */
    function css_exist(string $name = '') : bool
    {
		return file_exists(css_path($name));
    }
}

// ------------------------------------------------------------------------

if (!function_exists('js_exist'))
{
    /**
     * JS EXIST
     *
     * Verifie si un fichier js ou un sous dossier du repertoire /public/js existe
     *
     * @param	string	$name nom du fichier dont on veut verifier
     * @return	bool
     */
    function js_exist(string $name = '') : bool
    {
		return file_exists(js_path($name));
    }
}

// ------------------------------------------------------------------------

if (!function_exists('lib_exist'))
{
    /**
     * LIB EXIST
     *
     * Verifie si un fichier un sous dossier du repertoire /public/lib existe
     *
     * @param	string	$name nom du fichier dont on veut verifier
     * @return	bool
     */
    function lib_exist(string $name = '') : bool
    {
		return file_exists(lib_path($name));
    }
}

// ------------------------------------------------------------------------

if (!function_exists('less_exist'))
{
    /**
     * LESS EXIST
     *
     * Verifie si un fichier less ou un sous dossier du repertoire /public/less existe
     *
     * @param	string	$name nom du fichier dont on veut verifier
     * @return	bool
     */
    function less_exist(string $name = '') : bool
    {
		return file_exists(less_path($name));
    }
}

// ------------------------------------------------------------------------

if (!function_exists('img_exist'))
{
    /**
     * IMG EXIST
     *
     * Verifie si un fichier ou un sous dossier du repertoire /public/img existe
     *
     * @param	string	$name nom du fichier dont on veut verifier
     * @return	bool
     */
    function img_exist(string $name = '') : bool
    {
		return file_exists(img_path($name));
    }
}

// ------------------------------------------------------------------------

if (!function_exists('doc_exist'))
{
    /**
     * DOC EXIST
     *
     * Verifie si un fichier ou un sous dossier du repertoire /public/docs existe
     *
     * @param	string	$name nom du fichier dont on veut verifier
     * @return	bool
     */
    function doc_exist(string $name = '') : bool
    {
		return file_exists(docs_path($name));
    }
}

// ------------------------------------------------------------------------

if (!function_exists('video_exist'))
{
    /**
     * VIDEO EXIST
     *
     * Verifie si un fichier ou un sous dossier du repertoire /public/videos existe
     *
     * @param	string	$name nom du fichier dont on veut verifier
     * @return	bool
     */
    function videos_exist(string $name = '') : bool
    {
		return file_exists(video_path($name));
    }
}

// ------------------------------------------------------------------------

if (!function_exists('public_exist'))
{
    /**
     * PUBLIC EXIST
     *
     * Verifie si un fichier ou un sous dossier du repertoire /public existe
     *
     * @param	string	$name nom du fichier dont on veut verifier
     * @return	bool
     */
    function public_exist(string $name = '') : bool
    {
		return file_exists(public_path($name));
    }
}

// ------------------------------------------------------------------------

if (!function_exists('class_exist'))
{
    /**
     * CLASS EXIST
     *
     * Verifie si un fichier ou un sous dossier du repertoire /app/class existe
     *
     * @param	string	$name nom du fichier dont on veut verifier
     * @return	bool
     */
    function class_exist(string $name = '') : bool
    {
		return file_exists(class_path($name));
    }
}

// ------------------------------------------------------------------------

if (!function_exists('config_exist'))
{
    /**
     * CONFIG EXIST
     *
     * Verifie si un fichier ou un sous dossier du repertoire /app/config existe
     *
     * @param	string	$name nom du fichier dont on veut verifier
     * @return	bool
     */
    function config_exist(string $name = '') : bool
    {
		return file_exists(config_path($name));
    }
}

// ------------------------------------------------------------------------

if (!function_exists('controller_exist'))
{
    /**
     * CONTROLLER EXIST
     *
     * Verifie si un fichier ou un sous dossier du repertoire /app/controllers existe
     *
     * @param	string	$name nom du fichier dont on veut verifier
	 * @param	bool	$only specifie si on veut seulement les contrôleurs (fichiers php ayant le suffixe Controller)
     * @return	bool
     */
    function controller_exist(string $name = '', bool $only = true) : bool
    {
		return file_exists(controller_path($name, $only));
    }
}

// ------------------------------------------------------------------------

if (!function_exists('entity_exist'))
{
    /**
     * ENTITY EXIST
     *
     * Verifie si un fichier ou un sous dossier du repertoire /app/entities existe
     *
     * @param	string	$name nom du fichier dont on veut verifier
     * @param	bool	$only specifie si on veut seulement les fichiers d'entité (fichiers php ayant le suffixe Entity)
     * @return	bool
     */
    function entity_exist(string $name = '', bool $only = true) : bool
    {
		return file_exists(entity_path($name, $only));
    }
}

// ------------------------------------------------------------------------

if (!function_exists('helper_exist'))
{
    /**
     * HELPER EXIST
     *
     * Verifie si un fichier ou un sous dossier du repertoire /app/helpers ou /system/helpers existe
     *
     * @param	string	$name nom du fichier dont on veut verifier
     * @param	bool	$system specifie s'il s'agit des helpers systeme ou applicatif
     * @return	bool
     */
    function helper_exist(string $name = '', bool $system = false) : bool
    {
		return file_exists(helper_path($name, $system));
    }
}

// ------------------------------------------------------------------------

if (!function_exists('library_exist'))
{
    /**
     * LIBRARY EXIST
     *
     * Verifie si un fichier ou un sous dossier du repertoire /app/libraries ou /system/libraries existe
     *
     * @param	string	$name nom du fichier dont on veut verifier
     * @param	bool	$system specifie s'il s'agit des librairies systeme ou applicatif
     * @return	bool
     */
    function library_exist(string $name = '', bool $system = false) : bool
    {
		return file_exists(library_path($name, $system));
    }
}

// ------------------------------------------------------------------------

if (!function_exists('middleware_exist'))
{
    /**
     * MIDDLEWARE EXIST
     *
     * Verifie si un fichier ou un sous dossier du repertoire /app/middlewares ou /system/middlewares existe
     *
     * @param	string	$name nom du fichier dont on veut verifier
     * @param	bool	$system specifie s'il s'agit des middlewares systeme ou applicatif
	 * @param	bool	$only specifie si on veut seulement les middlewares (fichiers php ayant le suffixe Middleware)
     * @return	bool
     */
    function middleware_exist(string $name = '', bool $system = false, bool $only = true) : bool
    {
		return file_exists(middleware_path($name, $system, $only));
    }
}

// ------------------------------------------------------------------------

if (!function_exists('model_exist'))
{
    /**
     * MODEL EXIST
     *
     * Verifie si un fichier ou un sous dossier du repertoire /app/models existe
     *
     * @param	string	$name nom du fichier dont on veut verifier
	 * @param	bool	$only specifie si on veut seulement les modeles (fichiers php ayant le suffixe Model)
     * @return	bool
     */
    function model_exist(string $name = '', bool $only = true) : bool
    {
		return file_exists(model_path($name, $only));
    }
}

// ------------------------------------------------------------------------

if (!function_exists('resource_exist'))
{
    /**
     * RESOURCE EXIST
     *
     * Verifie si un fichier ou un sous dossier du repertoire /app/resources/ existe
     *
     * @param	string	$name nom du fichier dont on veut verifier
     * @return	bool
     */
    function resource_exist(string $name = '') : bool
    {
		return file_exists(resource_path($name));
    }
}

// ------------------------------------------------------------------------

if (!function_exists('migration_exist'))
{
    /**
     * MIGRATION EXIST
     *
     * Verifie si un fichier ou un sous dossier du repertoire /app/resources/database/migrations existe
     *
     * @param	string	$name nom du fichier dont on veut verifier
     * @return	bool
     */
    function migration_exist(string $name = '') : bool
    {
		return file_exists(migration_path($name));
    }
}

// ------------------------------------------------------------------------

if (!function_exists('seed_exist'))
{
    /**
     * SEED EXIST
     *
     * Verifie si un fichier ou un sous dossier du repertoire /app/resources/database/seeds existe
     *
     * @param	string	$name nom du fichier dont on veut verifier
     * @return	bool
     */
    function seed_exist(string $name = '') : bool
    {
		return file_exists(seed_path($name));
    }
}

// ------------------------------------------------------------------------

if (!function_exists('lang_exist'))
{
    /**
     * LANG EXIST
     *
     * Verifie si un fichier ou un sous dossier du repertoire /app/resources/lang ou /system/constants/lang
     *
     * @param	string	$name nom du fichier dont on veut verifier
	 * @param	bool	$system specifie s'il s'agit des langues systeme ou applicatif
     * @return	bool
     */
    function lang_exist(string $name = '', bool $system = false) : bool
    {
		return file_exists(lang_path($name, $system));
    }
}

// ------------------------------------------------------------------------

if (!function_exists('service_exist'))
{
    /**
     * SERVICE EXIST
     *
     * Verifie si un fichier ou un sous dossier du repertoire /app/services existe
     *
     * @param	string	$name nom du fichier dont on veut verifier
	 * @param	bool	$only specifie si on veut seulement les services (fichier php ayant le suffixe Service)
     * @return	bool
     */
    function service_exist(string $name = '', bool $only = true) : bool
    {
		return file_exists(service_path($name, $only));
    }
}

// ------------------------------------------------------------------------

if (!function_exists('view_exist'))
{
    /**
     * VIEW EXIST
     *
     * Verifie si un fichier ou un sous dossier du repertoire /app/views existe
     *
     * @param	string	$name nom du fichier dont on veut verifier
     * @return	bool
     */
    function view_exist(string $name = '') : bool
    {
		return file_exists(view_path($name));
    }
}

// ------------------------------------------------------------------------

if (!function_exists('layout_exist'))
{
    /**
     * LAYOUT EXIST
     *
     * Verifie si un fichier ou un sous dossier du repertoire /app/views/layouts existe
     *
     * @param	string	$name nom du fichier dont on veut verifier
     * @return	bool
     */
    function layout_exist(string $name = '') : bool
    {
		return file_exists(layout_path($name));
    }
}

// ------------------------------------------------------------------------

if (!function_exists('partial_exist'))
{
    /**
     * PARTIAL EXIST
     *
     * Verifie si un fichier ou un sous dossier du repertoire /app/views/partials existe
     *
     * @param	string	$name nom du fichier dont on veut verifier
     * @return	bool
     */
    function partial_exist(string $name = '') : bool
    {
		return file_exists(partial_path($name));
    }
}

// ------------------------------------------------------------------------

if (!function_exists('app_exist'))
{
    /**
     * APP EXIST
     *
     * Verifie si un fichier ou un sous dossier du repertoire /app/ existe
     *
     * @param	string	$name nom du fichier dont on veut verifier
     * @return	bool
     */
    function app_exist(string $name = '') : bool
    {
		return file_exists(app_path($name));
    }
}

// ------------------------------------------------------------------------

if (!function_exists('cache_exist'))
{
    /**
     * CACHE EXIST
     *
     * Verifie si un fichier ou un sous dossier du repertoire /storage/cache existe
     *
     * @param	string	$name nom du fichier dont on veut verifier
     * @return	bool
     */
    function cache_exist(string $name = '') : bool
    {
		return file_exists(cache_path($name));
    }
}

// ------------------------------------------------------------------------

if (!function_exists('dump_exist'))
{
    /**
     * DUMP EXIST
     *
     * Verifie si un fichier ou un sous dossier du repertoire /storage/database/dump existe
     *
     * @param	string	$name nom du fichier dont on veut verifier
     * @return	bool
     */
    function dump_exist(string $name = '') : bool
    {
		return file_exists(dump_path($name));
    }
}

// ------------------------------------------------------------------------

if (!function_exists('storage_exist'))
{
    /**
     * STORAGE EXIST
     *
     * Verifie si un fichier ou un sous dossier du repertoire /storage existe
     *
     * @param	string	$name nom du fichier dont on veut verifier
     * @return	bool
     */
    function storage_exist(string $name = '') : bool
    {
		return file_exists(storage_path($name));
    }
}

// ------------------------------------------------------------------------

if (!function_exists('include_config'))
{
    /**
     * INCLUDE CONFIG
     *
     * inclus un fichier de configuration
     *
     * @param	string	$name nom du fichier dont on veut inclure
	 * @param	array 	$data les données à transferer dans le fichier inclus
	 * @param	bool	$required specifie si le fichier est obligatoire ou pas
     * @return	void
     */
    function include_config(string $name, array $data = [], bool $required = true)
    {
		_include_path(config_path($name), $data, $required);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('include_controller'))
{
    /**
     * INCLUDE CONTROLLER
     *
     * inclus un contrôleur
     *
     * @param	string	$name nom du fichier dont on veut inclure
	 * @param	array 	$data les données à transferer dans le fichier inclus
	 * @param	bool	$required specifie si le fichier est obligatoire ou pas
	 * @param	bool	$only specifie si on veut seulement les contrôleurs (fichier php ayant le suffixe Controller)
     * @return	void
     */
    function include_controller(string $name, array $data = [], bool $required = true, bool $only = true)
    {
		_include_path(controller_path($name, $only), $data, $required);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('include_class'))
{
    /**
     * INCLUDE CLASS
     *
     * inclus une classe applicative
     *
     * @param	string	$name nom du fichier dont on veut inclure
	 * @param	array 	$data les données à transferer dans le fichier inclus
	 * @param	bool	$required specifie si le fichier est obligatoire ou pas
     * @return	void
     */
    function include_class(string $name, array $data = [], bool $required = true)
    {
		_include_path(class_path($name), $data, $required);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('include_entity'))
{
    /**
     * INCLUDE ENTITY
     *
     * inclus une entité
     *
     * @param	string	$name nom du fichier dont on veut inclure
	 * @param	array 	$data les données à transferer dans le fichier inclus
	 * @param	bool	$required specifie si le fichier est obligatoire ou pas
	 * @param	bool	$only specifie si on veut seulement les entités (fichier php ayant le suffixe Entity)
     * @return	void
     */
    function include_entity(string $name, array $data = [], bool $required = true, bool $only = true)
    {
		_include_path(entity_path($name, $only), $data, $required);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('include_helper'))
{
    /**
     * INCLUDE HELPER
     *
     * inclus un helper
     *
     * @param	string	$name nom du fichier dont on veut inclure
	 * @param	array 	$data les données à transferer dans le fichier inclus
	 * @param	bool	$required specifie si le fichier est obligatoire ou pas
     * @return	void
     */
    function include_helper(string $name, array $data = [], bool $required = true)
    {
		_include_path(helper_path($name), $data, $required);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('include_library'))
{
    /**
     * INCLUDE LIBRARY
     *
     * inclus une librarie
     *
     * @param	string	$name nom du fichier dont on veut inclure
	 * @param	array 	$data les données à transferer dans le fichier inclus
	 * @param	bool	$required specifie si le fichier est obligatoire ou pas
     * @param	bool	$system specifie s'il s'agit des librairies systeme ou applicatif
     * @return	void
     */
    function include_library(string $name, array $data = [], bool $required = true, bool $system = false)
    {
		_include_path(library_path($name, $system), $data, $required);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('include_middleware'))
{
    /**
     * INCLUDE MIDDLEWARE
     *
     * inclus un middleware
     *
     * @param	string	$name nom du fichier dont on veut inclure
	 * @param	array 	$data les données à transferer dans le fichier inclus
	 * @param	bool	$required specifie si le fichier est obligatoire ou pas
     * @param	bool	$system specifie s'il s'agit des middlewares systeme ou applicatif
	 * @param	bool	$only specifie si on veut seulement les middlewares (fichiers php ayant le suffixe Middleware)
     * @return	void
     */
    function include_middleware(string $name, array $data = [], bool $required = true, bool $system = false, bool $only = true)
    {
		_include_path(middleware_path($name, $system, $only), $data, $required);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('include_model'))
{
    /**
     * INCLUDE MODEL
     *
     * inclus un modeèle
     *
     * @param	string	$name nom du fichier dont on veut inclure
	 * @param	array 	$data les données à transferer dans le fichier inclus
	 * @param	bool	$required specifie si le fichier est obligatoire ou pas
	 * @param	bool	$only specifie si on veut seulement les modèles (fichier php ayant le suffixe Model)
     * @return	void
     */
    function include_model(string $name, array $data = [], bool $required = true, bool $only = true)
    {
		_include_path(model_path($name, $only), $data, $required);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('include_resource'))
{
    /**
     * INCLUDE RESOURCE
     *
     * inclus un fichier resource
     *
     * @param	string	$name nom du fichier dont on veut inclure
	 * @param	array 	$data les données à transferer dans le fichier inclus
	 * @param	bool	$required specifie si le fichier est obligatoire ou pas
     * @return	void
     */
    function include_resource(string $name, array $data = [], bool $required = true)
    {
		_include_path(resource_path($name), $data, $required);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('include_service'))
{
    /**
     * INCLUDE SERVICE
     *
     * inclus un service
     *
     * @param	string	$name nom du fichier dont on veut inclure
	 * @param	array 	$data les données à transferer dans le fichier inclus
	 * @param	bool	$required specifie si le fichier est obligatoire ou pas
	 * @param	bool	$only specifie si on veut seulement les services (fichier php ayant le suffixe Service)
     * @return	void
     */
    function include_service(string $name, array $data = [], bool $required = true, bool $only = true)
    {
		_include_path(service_path($name, $only), $data, $required);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('include_view'))
{
    /**
     * INCLUDE VIEW
     *
     * inclus une vue
     *
     * @param	string	$name nom du fichier dont on veut inclure
	 * @param	array 	$data les données à transferer dans le fichier inclus
	 * @param	bool	$required specifie si le fichier est obligatoire ou pas
     * @return	void
     */
    function include_view(string $name, array $data = [], bool $required = true)
    {
		_include_path(view_path($name), $data, $required);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('include_layout'))
{
    /**
     * INCLUDE LAYOUT
     *
     * inclus un template de vue
     *
     * @param	string	$name nom du fichier dont on veut inclure
	 * @param	array 	$data les données à transferer dans le fichier inclus
	 * @param	bool	$required specifie si le fichier est obligatoire ou pas
     * @return	void
     */
    function include_layout(string $name, array $data = [], bool $required = true)
    {
		_include_path(layout_path($name), $data, $required);
    }
}

// ------------------------------------------------------------------------

if (!function_exists('include_partial'))
{
    /**
     * INCLUDE PARTIAL
     *
     * inclus une partie de vue
     *
     * @param	string	$name nom du fichier dont on veut inclure
	 * @param	array 	$data les données à transferer dans le fichier inclus
	 * @param	bool	$required specifie si le fichier est obligatoire ou pas
     * @return	void
     */
    function include_partial(string $name, array $data = [], bool $required = true)
    {
		_include_path(partial_path($name), $data, $required);
    }
}


// ------------------------------------------------------------------------


if (!function_exists('_include_path'))
{
    /**
     * inclus un fichier
     *
     * @param	string	$path chemin du fichier dont on veut inclure
	 * @param	array 	$data les données à transferer dans le fichier inclus
	 * @param	bool	$required specifie si le fichier est obligatoire ou pas
     * @return	void
     */
    function _include_path(string $path, array $data = [], bool $required = true)
    {
		if (file_exists($path))
		{
			extract($data);
			require_once($path);
		}
		else if (true === $required)
		{
			throw new Exception("The file '{$path}' does not exist");
		}
    }
}
