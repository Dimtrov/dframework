<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Sarl
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @link	    https://dimtrov.hebfree.org/works/dframework
 * @version 2.0
 */

use dFramework\core\exception\Exception;

/**
 * File
 *
 *
 * @package		dFramework
 * @subpackage	Library
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/works/dframework/docs/File.html
 */


class dF_File
{
    /**
     * @var string
     */
    private $file;
    /**
     * @var string
     */
    private $dir;


    public function file(string $file)
    {
        $this->file = $file;
    }

    public function dir(string $dir)
    {
        $this->dir = $dir;
    }

    public function createFile(string $name, ?string $dir = null, bool $absolute = false)
    {
        if(!empty($dir))
        {
            if(false === $absolute)
            {
                $dir = rtrim($this->dir, '/').'/'.$dir;
                $this->createDir($dir);
            }
            else if(true === $absolute AND !$this->isDir($dir))
            {
                Exception::show('Can\'t create <b>'.$dir.'/'.$name.'</b>: The Folder "' .$dir. '" don\'t exist.');
            }
        }
        else
        {
            $dir = $this->dir;
        }
        $file = rtrim($dir, '/').'/'.$name;

        if($this->isFile($file))
        {
            Exception::show('Can\'t create a file <b>'.$file.'</b> because he already exist');
        }
        fopen($file, 'w');
    }

    public function createDir($dir = '')
    {

    }

    public function copy(string $dest, ?string $file = null)
    {
        $file = (!empty($file) AND file_exists($file)) ? $file : $this->filename;
        if(!copy($file, $dest)) {

        }
    }


}