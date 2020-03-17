<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Sarl
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.0
 */

use dFramework\core\exception\Exception;
use dFramework\dependencies\others\createzip\CreateZipFile;
use dFramework\dependencies\others\dunzip\{dUnzip2, dZip};

/**
 * Zip
 *
 *
 * @package		dFramework
 * @subpackage	Library
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/Zip.html
 * @since       2.0
 */


class dF_Zipper
{
    /**
     * @param string $name
     * @param array|null $folders
     * @param array|null $files
     */
    public function createZip(string $name, ?array $folders = [], ?array $files = [])
    {
        if(!preg_match('#\.zip$#i', $name))
        {
            $name .= '.zip';
        }
        $newzip = new dZip($name);

        foreach ($folders As $k => $v)
        {
            if(is_int($k) AND is_string($v))
            {
                $newzip->addDir($v);
            }
        }

        foreach ($files As $k => $v)
        {
            if(is_string($k) AND is_string($v))
            {
                $newzip->addFile($k, $v);
            }
        }
        $newzip->save();
    }

    /**
     * @param string $name
     * @param null|string $file
     */
    public function unZip(string $name, ?string $file = null)
    {
        if(!preg_match('#\.zip$#i', $name))
        {
            $name .= '.zip';
        }
        $zip = new dUnzip2($name);

        if(!empty($file))
        {
            $zip->unzip(pathinfo($name, PATHINFO_DIRNAME).DIRECTORY_SEPARATOR.$file);
        }
        else
        {
            $zip->unzipAll(pathinfo($name, PATHINFO_DIRNAME).DIRECTORY_SEPARATOR.'uncompressed');
        }
    }


    /**
     * @param string $path
     * @param string $output
     * @param string $name
     */
    public function zipFile(string $path, string $output = '/', string $name = '')
    {
        if(!is_file($path) OR !is_readable($path))
        {
            Exception::show('Le chemin specifier pour le fichier à zipper ne correspond pas à un fichier lisible');
        }
        $file = pathinfo($path, PATHINFO_BASENAME);

        $createZipFile = new CreateZipFile;
        $createZipFile->addDirectory($output);
        $fileContents=file_get_contents($path);
        $createZipFile->addFile($fileContents, $output.$file);

        $this->makeZipFile($createZipFile, $name);
    }

    /**
     * @param string $path
     * @param string $output
     * @param string $name
     */
    public function zipFolder(string $path, string $output = '/', string $name = '')
    {
        if(!is_dir($path) OR !is_readable($path))
        {
            Exception::show('Le chemin specifier pour le dossier à zipper ne correspond pas à un dossier lisible');
        }
        $createZipFile = new CreateZipFile;
        $createZipFile->zipDirectory($path, $output);

        $this->makeZipFile($createZipFile, $name);
    }


    /**
     * @param CreateZipFile $createZipFile
     * @param $name
     */
    private function makeZipFile(CreateZipFile $createZipFile, $name)
    {
        $zipName = (empty($name)) ? 'df-zip_'.md5(microtime().rand(0, 999999)) : $name;
        if(!preg_match('#\.zip$#i', $zipName))
        {
            $zipName .= '.zip';
        }
        $fd = fopen($zipName, "wb");
        fwrite($fd, $createZipFile->getZippedfile());
        fclose($fd);
        $createZipFile->forceDownload($zipName);
    }
}