<?php
/**
 * Copyright (c) 2019. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

namespace dFramework\core\output;


use dFramework\core\Config;
use dFramework\core\exception\Exception;
use dFramework\core\route\Dispatcher;

define('CACHE_LITE_ERROR_RETURN', 1);
define('CACHE_LITE_ERROR_DIE', 8);


class Cache
{
    /**
     * @var string Repertoire de stockage des fichiers mis en cache
     */
    private $cache_dir = APP_DIR.'cache'.DS;

    /**
     * @var int duree de validite du cache en minute
     */
    private $cache_time = 1;

    /**
     * @var string extension des fichiers de cache
     */
    private $cache_ext = '.cache';

    /**
     * @var string
     */
    private $buffer = '';

    public function __construct() {}


    /**
     * @param array $options
     * @throws Exception
     */
    public function set(array $options = [])
    {
        if(!empty($options['cache_dir']) AND is_string($options['cache_dir']))
        {
            if(!is_dir($options['cache_dir']))
            {
                throw new Exception('Le chemin spécifié pour la sauvegarde des fichiers de cache n\'existe pas');
            }
            $options['cache_dir'] = str_replace('/', DS, $options['cache_dir']);
            $this->cache_dir = trim($options['cache_dir'], DS).DS;
        }
        if(!empty($options['cache_time']) AND is_int($options['cache_time']))
        {
            $this->cache_time = $options['cache_time'];
        }
    }

    /**
     * @param string $label
     * @param string $content
     * @return bool|int
     */
    public function write(string $label, string $content)
    {
        return file_put_contents($this->cache_dir . $this->safe_filename($label) . $this->cache_ext, $content);
    }

    /**
     * @param string $label
     * @return bool|string
     */
    public function read(string $label)
    {
        if($this->is_cached($label))
        {
            $filename = $this->cache_dir . $this->safe_filename($label) . $this->cache_ext;
            return file_get_contents($filename);
        }
        return false;
    }

    /**
     * Supprime un fichier mis en cache
     * @param string $label
     */
    public function remove(string $label)
    {
        $filename = $this->cache_dir . $this->safe_filename($label) . $this->cache_ext;
        if(file_exists($filename))
        {
            unlink($filename);
        }
    }
    /**
     * Vide tous les fichiers mis en cache
     */
    public function clear()
    {
        $files = glob($this->cache_dir.'/*');
        foreach ($files As $file)
        {
            unlink($file);
        }
    }

    /**
     * Permet d'inclure une page en cache
     *
     * @param string $file
     * @param array $vars
     * @param null|string $label
     */
    public function inc(string $file, array $vars = [], ?string $label = null)
    {
        $class = Dispatcher::getClass();
        $class = (!empty($class)) ? $class : Config::get('route.default_controller');

        if(empty($label))
        {
            $label = str_replace('.php', '', basename($file));
        }
        if(! $content = $this->read($label))
        {
            $content = (new View($file, $vars, $class))->get(true);
            $this->write($label, $content);
        }
        echo $content;
    }

    /**
     * Initialise le tampon de sortie
     *
     * @param string $label
     * @return bool
     */
    public function start(string $label)
    {
        if($content = $this->read($label))
        {
            echo $content;
            $this->buffer = false;
            return true;
        }
        ob_start();
        $this->buffer = $label;
    }

    /**
     * Termine le tampon de sortie et affiche le contenu
     *
     * @return bool
     */
    public function end()
    {
        if(empty($this->buffer))
        {
            return false;
        }
        $content = ob_get_clean();
        echo $content;
        $this->write($this->buffer, $content);
    }


    /**
     * @param $label
     * @return bool
     */
    private function is_cached($label)
    {
        $filename = $this->cache_dir . $this->safe_filename($label) . $this->cache_ext;

        return !(!file_exists($filename) OR ((time() - filemtime($filename))) / 60 >= $this->cache_time);
    }

    /**
     * Helper function to validate filenames
     * @param string $filename
     * @return string
     */
    private function safe_filename(string $filename) : string
    {
        return preg_replace('/[^0-9a-z\.\_\-]/i','', strtolower($filename));
    }
}
