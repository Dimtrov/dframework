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


namespace dFramework\core\security;

/**
 * Xss
 *
 * Anti XSS Atacks class
 *
 * @package		dFramework
 * @subpackage	Core
 * @category	Security
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api
 * @since       1.2
 * @file        /system/core/seurity/Xss.php
 * @credit      wArLeY_AntiXSS v1.0 - by Evert Ulises German Soto <GoPanga.com>
 */

class Xss
{
    /**
     * Clean your string with the specifieds methods
     *
     * @param string $str La chaine a netoyer
     * @param array $filter Les filtres a appliquer
     * @return string
     */
    public static function clean(string $str, array $filter = []) : string
    {
        $filter = array_merge(['html_special_character'], $filter);
        foreach ($filter As $method)
        {
            $str = self::i()->$method($str);
        }
        return $str;
    }

    /**
     * Call native PHP function "htmlspecialchars"
     *
     * @param string $str
     * @return string
     */
    private function html_special_character(string $str) : string
    {
        return htmlspecialchars($str, ENT_QUOTES, 'utf-8');
    }
    /**
     * Call native PHP function "strip_tags"
     *
     * @param string $str
     * @return string
     */
    private function stripe_tags(string $str) : string
    {
        return strip_tags($str);
    }
    /**
     * Call native PHP function "stripe_slashes"
     *
     * @param string $str
     * @return string
     */
    private function stripe_slashes(string $str) : string 
    {
        return stripslashes($str);
    }

    //Call native PHP function "filter_var" and "FILTER_SANITIZE_STRING"
    private function filter_sanitize($string_arg)
    {
        $string_arg = filter_var($string_arg, FILTER_SANITIZE_STRING);

        return $string_arg;
    }

    //Call native PHP function "filter_var" and "FILTER_VALIDATE_EMAIL"
    private function filter_email($string_arg)
    {
        $string_arg = filter_var($string_arg, FILTER_VALIDATE_EMAIL);

        return $string_arg;
    }

    //Clean accents from string and other characters
    private function rare_accent($string_arg)
    {
        $string_arg = str_replace(array("á","à","â","ã","ª","ä"),"a",$string_arg);
        $string_arg = str_replace(array("Á","À","Â","Ã","Ä"),"A",$string_arg);
        $string_arg = str_replace(array("Í","Ì","Î","Ï"),"I",$string_arg);
        $string_arg = str_replace(array("í","ì","î","ï"),"i",$string_arg);
        $string_arg = str_replace(array("é","è","ê","ë"),"e",$string_arg);
        $string_arg = str_replace(array("É","È","Ê","Ë"),"E",$string_arg);
        $string_arg = str_replace(array("ó","ò","ô","õ","ö","º"),"o",$string_arg);
        $string_arg = str_replace(array("Ó","Ò","Ô","Õ","Ö"),"O",$string_arg);
        $string_arg = str_replace(array("ú","ù","û","ü"),"u",$string_arg);
        $string_arg = str_replace(array("Ú","Ù","Û","Ü"),"U",$string_arg);
        $string_arg = str_replace(array("[","^","´","`","¨","~","]"),"",$string_arg);
        $string_arg = str_replace("ç", "c",$string_arg);
        $string_arg = str_replace("Ç", "C",$string_arg);
        $string_arg = str_replace("ñ", "n",$string_arg);
        $string_arg = str_replace("Ñ", "N",$string_arg);
        $string_arg = str_replace("Ý", "Y",$string_arg);
        $string_arg = str_replace("ý", "y",$string_arg);
        $string_arg = str_replace("&", "-",$string_arg);
        $string_arg = str_replace('"', "",$string_arg);
        $string_arg = str_replace("'", "",$string_arg);

        return $string_arg;
    }

    //Clean special characters from string
    private function special_character($string_arg)
    {
        $string_arg = str_replace(" ", "-", $string_arg);
        $string_arg = str_replace("×", "x",$string_arg);
        $string_arg = str_replace("°", "", $string_arg);
        $string_arg = str_replace("'", "_", $string_arg);
        $string_arg = str_replace('"', "_", $string_arg);
        $string_arg = str_replace("+", "-",$string_arg);
        $string_arg = str_replace(",", "-",$string_arg);
        $string_arg = str_replace(":", "-",$string_arg);
        $string_arg = str_replace("--", "-", $string_arg);
        $string_arg = str_replace("---", "-",$string_arg);
        $string_arg = str_replace("{", "(",$string_arg);
        $string_arg = str_replace("}", ")",$string_arg);
        $string_arg = str_replace("[", "(",$string_arg);
        $string_arg = str_replace("]", ")",$string_arg);
        $string_arg = str_replace("<", "(",$string_arg);
        $string_arg = str_replace(">", ")",$string_arg);

        return $string_arg;
    }

    //Clean characters not allowed for name file in Windows and others
    private function allowed_by_os($string_arg)
    {
        $string_arg = str_replace("*", "+", $string_arg);
        $string_arg = str_replace("|", "+",$string_arg);
        $string_arg = str_replace("\\", "+", $string_arg);
        $string_arg = str_replace(":", "+", $string_arg);
        $string_arg = str_replace('"', "+", $string_arg);
        $string_arg = str_replace("'", "+", $string_arg);
        $string_arg = str_replace("<", "(",$string_arg);
        $string_arg = str_replace(">", ")",$string_arg);
        $string_arg = str_replace("?", ".",$string_arg);
        $string_arg = str_replace("/", "+", $string_arg);

        return $string_arg;
    }

    //Clean dangerous characters for prevent XSS Attacks
    private function prevent_basic_xss($string_arg)
    {
        $string_arg = str_replace("<", "[eugsxss]+",$string_arg);
        $string_arg = str_replace(">", "[eugsxss]-", $string_arg);
        $string_arg = str_replace("'", "", $string_arg);
        $string_arg = str_replace('"', "", $string_arg);
        $string_arg = str_replace("(", "-",$string_arg);
        $string_arg = str_replace(")", "-",$string_arg);
        $string_arg = str_replace("%3C", "[eugsxss]+",$string_arg);
        $string_arg = str_replace("%3E", "[eugsxss]-",$string_arg);

        if(strpos($string_arg,'[eugsxss]')!==false)
        {
            $tmp_arr = explode("[eugsxss]", $string_arg);
            $string_arg = $tmp_arr[0];
        }

        return $string_arg;
    }


    private static $_instance = null;
    private static function i()
    {
        if(null === self::$_instance)
        {
            self::$_instance = new Xss;
        }
        return self::$_instance;
    }
}