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

use dFramework\dependencies\others\dumpr\Core;
use dFramework\dependencies\others\dumpr\Type;
use dFramework\dependencies\others\unreal4u\debugInfo;

/**
 * Debug
 *  Collections d'outils de debugage
 *
 * @package		dFramework
 * @subpackage	Library
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/guide/Debug.html
 * @since       2.1
 * @file        /system/libraries/Debug.php
 */

class dF_Debug
{
    public function dump($raw, $ret = false, $html = true, $depth = 1e3, $expand = 1e3)
    {
        // typenode classification
        Type::hook('*', function ($raw, Type $type, $path) {
            if (is_null($raw))
                $type->class[] = 'Null0';
            else if (is_bool($raw))
                $type->class[] = 'Boolean';
            else if (is_int($raw))
                $type->class[] = 'Integer';
            else if (is_float($raw))
                $type->class[] = 'Float0';
            else if (is_resource($raw))
                $type->class[] = 'Resource';
            // avoid detecting strings with names of global functions and __invoke-able objects as callbacks
            else if (is_callable($raw) && !(is_object($raw) && !($raw instanceof \Closure)) && !(is_string($raw) && function_exists($raw)))
                $type->class[] = 'Function0';    // lang construct
            else if (is_string($raw))
                $type->class[] = 'String0';
            else if (is_array($raw))
                $type->class[] = 'Array0';    // lang construct
            else if (is_object($raw))
                $type->class[] = 'Object0';
            else
                $type->class[] = gettype($raw);

            return $type;
        });

        Type::hook('String', function ($raw, Type $type, $path) {
            if ($raw === '')
                return;
            $nonprint = preg_match('/[^\PC\s]/u', $raw);
            if ($nonprint == 1 || $nonprint === false)
                $type->class[] = 'Binary';
            else if (strlen($raw) > 5 && preg_match('#[:/-]#', $raw) && ($ts = strtotime($raw)) !== false) {
                $type->class[] = 'Datetime';
                $type->inter = $ts;
            } // SQL
            else if (
                strpos($raw, 'SELECT') === 0 ||
                strpos($raw, 'INSERT') === 0 ||
                strpos($raw, 'UPDATE') === 0 ||
                strpos($raw, 'DELETE') === 0 ||
                strpos($raw, 'BEGIN') === 0 ||
                strpos($raw, 'COMMIT') === 0 ||
                strpos($raw, 'ROLLBACK') === 0
                /* sql_extended
                strpos($raw, 'CREATE')   === 0 ||
                strpos($raw, 'DROP')     === 0 ||
                strpos($raw, 'TRUNCATE') === 0 ||
                strpos($raw, 'ALTER')    === 0 ||
                strpos($raw, 'DESCRIBE') === 0 ||
                strpos($raw, 'EXPLAIN')  === 0 ||
                strpos($raw, 'SHOW')     === 0 ||
                strpos($raw, 'GRANT')    === 0 ||
                strpos($raw, 'REVOKE')   === 0
                */
            )
                $type->class[] = 'SQL';

            // JSON
            else if ($raw{0} == '{' && $json = json_decode($raw)) {
                $type->class[] = 'JSON\Object';
                $type->inter = $json;
            } else if ($raw{0} == '[' && $json = json_decode($raw)) {
                $type->class[] = 'JSON\Array0';
                $type->inter = $json;
            }
            // jsonH

            // XML
            else if (substr($raw, 0, 5) == '<?xml') {
                // strip namespaces
                $raw = preg_replace('/<(\/?)[\w-]+?:/', '<$1', preg_replace('/\s+xmlns:.*?=".*?"/', '', $raw));

                if ($xml = simplexml_load_string($raw)) {
                    $type->class[] = 'XML';
                    $type->inter = $xml;
                }
                // XML\Array0
                // XML\Object
            }

            return $type;
        });

        Type::hook('Resource', function ($raw, Type $type, $path) {
            $kind = get_resource_type($raw);        // this is valuable for other resources

            switch ($kind) {
                case 'stream':
                    $meta = stream_get_meta_data($raw);
                    $type->class[] = 'Stream';
                    $type->inter = $meta;
            }
            return $type;
        });

        return Core::dump_r($raw, $ret, $html, $depth, $expand);
    }


    /**
     * @param $var
     * @param bool $print
     * @param string $message
     * @return string
     */
    public function var_dump($var, $print = true, $message = '')
    {
        return debugInfo::debug($var, $print, $message);
    }

    /**
     * @param string $message
     * @param string $filename
     * @param string $directory
     * @return bool
     */
    public function file_dump($message = '', $filename = '', $directory = '')
    {
        return debugInfo::debugFile($message, $filename, $directory);
    }

}