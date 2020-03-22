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
 * @link	    https://dimtrov.hebfree.org/works/dframework
 * @version     3.0
 */

use dFramework\dependencies\others\filipegomes\Upload;
use dFramework\dependencies\verot\Upload As verotUpload;

/**
 * Upload
 *
 *
 * @package		dFramework
 * @subpackage	Library
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/works/dframework/docs/Upload.html
 * @since       2.0
 */


class dF_Upload
{
    const NATIVE = 1;
    const VEROT = 2;

    private $type = self::NATIVE;

    private $params = [];

    /**
     * @var array
     */
    private $input;

    /**
     * @var string
     */
    private $folder = WEBROOT;


    private $uploader;

    /**
     * @var string
     */
    private $errors = '';

    /**
     * @var array
     */
    private $details = [];

    /**
     * @var string
     */
    private $log = '';


    /**
     * @param int $type
     */
    public function use(int $type)
    {
        $this->type = $type;
    }

    /**
     * @param array $input
     * @param string $folder
     * @param array|null $params
     */
    public function set(array $input, string $folder, ?array $params = [])
    {
        $this->input = $input;
        $this->params = $params;

        $folder = str_replace(WEBROOT, '', $folder);
        $folder = WEBROOT.ltrim($folder, '/\\');
        $this->folder = $folder;
    }

    /**
     * @return bool
     * @throws ReflectionException
     */
    public function upload() : bool
    {
        if($this->type === self::NATIVE)
        {
            return $this->nativeUpload($this->input, $this->folder);
        }
        if($this->type === self::VEROT)
        {
            return $this->verotUpload($this->input, $this->folder);
        }
    }

    /**
     * @return string
     */
    public function getErrors() : string
    {
        return $this->errors;
    }

    /**
     * @return array
     */
    public function getDetails() : array
    {
        return $this->details;
    }

    /**
     * @return string
     */
    public function getLog() : string
    {
        return $this->log;
    }


    /**
     * @param array $input
     * @param string $folder
     * @return bool
     */
    private function nativeUpload(array $input, string $folder) : bool
    {
        $this->uploader = new Upload($folder, $input);

        $params = $this->params; $name = null;

        if(isset($params['max_size']) AND is_int($params['max_size']))
        {
            $this->uploader->cl_taille_maxi = $params['max_size'];
        }
        if(isset($params['extensions']) AND is_array($params['extensions']))
        {
            foreach ($params['extensions'] As $key => $value)
            {
                if(is_string($value) AND $value[0] != '.')
                {
                    $params['extensions'][$key] = '.'.$value;
                }
            }
            $this->uploader->cl_extensions = $params['extensions'];
        }
        if(isset($params['new_name']))
        {
            if(is_string($params['new_name']))
            {
                $name = $params['new_name'];
            }
            else if (is_int($params['new_name']))
            {
                $this->uploader->cl_nb_char_aleatoire = $params['new_name'];
                $name = 'aleatoire';
            }
            else if(true === $params['new_name'])
            {
                $name = 'dFramework_uploadfile_'.date('YmdHis');
            }
        }

        $upload = $this->uploader->uploadFichier($name);

        if(!$upload)
        {
            $this->errors = $this->uploader->affichageErreur();
        }
        else
        {
            $this->details = [
                'extension' => $this->uploader->cGetExtension(),
                'filename' => $this->uploader->cGetNameFile(true),
                'basename' => $this->uploader->cGetNameFile(false),
                'final_filename' => $this->uploader->cGetNameFileFinal(true),
                'final_basename' => $this->uploader->cGetNameFileFinal(false),
                'type'  => $this->uploader->cGetTypeFile(),
                'size'  => [
                    'o' => $this->uploader->cGetSizeFile(1),
                    'ko' => $this->uploader->cGetSizeFile(2),
                    'mo' => $this->uploader->cGetSizeFile(3),
                    'go' => $this->uploader->cGetSizeFile(4),
                    'to' => $this->uploader->cGetSizeFile(5),
                ],
                'path'  => $this->uploader->cGetFolder(),
                'tmp_path'  => $this->uploader->cGetNameTemp()
            ];
        }
        return $upload;
    }


    /**
     * @param array $input
     * @param string $folder
     * @return bool
     * @throws ReflectionException
     */
    private function verotUpload(array $input, string $folder) : bool
    {
        $this->uploader = new verotUpload($input);

        $props = (new ReflectionClass($this->uploader))->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($props As $prop)
        {
            $key = $prop->getName();
            if(array_key_exists($key, $this->params))
            {
                $this->uploader->{$key} = $this->params[$key];
            }
        }

        $uploaded = $processed = false;
        $uploaded = $this->uploader->uploaded;

        if($uploaded)
        {
            $this->uploader->process($folder);
            $processed = $this->uploader->processed;

            if($processed)
            {
                $this->details = [
                    'extension' => $this->uploader->file_src_name_ext,
                    'filename' => $this->uploader->file_src_name,
                    'basename' => $this->uploader->file_src_name_body,
                    'final_filename' => $this->uploader->file_dst_name,
                    'final_basename' => $this->uploader->file_dst_name_body,
                    'type'  => $this->uploader->file_src_mime,
                    'size'  => [
                        'o' => $this->uploader->file_src_size,
                        'ko' => round($this->uploader->file_src_size / 1024 * 100) / 100,
                        'mo' => round($this->uploader->file_src_size / 1048576 * 100) / 100,
                        'go' => round($this->uploader->file_src_size / 1073741824 * 100) / 100,
                        'to' => round($this->uploader->file_src_size / 1099511627776 * 100) / 100
                    ],
                    'path'  => $this->uploader->file_dst_path,
                    'tmp_path'  => $this->uploader->file_src_pathname
                ];
            }
            else
            {
                $this->errors = $this->uploader->error;
            }
            $this->uploader->clean();
        }
        else
        {
            $this->errors = $this->uploader->error;
        }
        $this->log = $this->uploader->log;

        return ($uploaded AND $processed);
    }

}

