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


namespace dFramework\core\utilities;

/**
 * Debbuger
 *
 * Contains a debug class used as a helper during development.
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Utilities
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/Debugger.html
 * @since       3.0
 * @file        /system/core/utilities/Debugger.php
 * @credit      php-jcktraker - by Stéphane Bouvry (jacksay@jacksay.com) - https://github.com/Jacksay/php-jcktraker
 */

class Debugger
{
    /**
     * @var string[] Ip autorisées  pour les deboguage distants
     */
    private static $allow_IP = [
       '::1', '127.0.0.1'
    ];

    /**
     * Ajoute une adresse Ip aux adresses autorisees
     * 
     * @param string|string[] $ip
     */
    public static function addIp($ip)
    {
        $ip = (array) $ip;
        foreach($ip As $value)
        {
            if(is_string($value))
            {
                array_push(self::$allow_IP, $value);
            }
        }
    }
    /**
     * Retire une adresse Ip des adresses autorisees
     * 
     * @param string|string[] $ip
     */
    public static function removeIp($ip)
    {
        $ip = (array) $ip;
        $final = [];
        foreach(self::$allow_IP As $value)
        {
            if(!in_array($value, $ip))
            {
                array_push($final, $value);
            }
        }
        self::$allow_IP = $final;
    }
    /**
     * Recupere la liste des adresses Ip autorisees
     * 
     * @return array
     */
    public static function getIp() : array
    {
        return self::$allow_IP;
    }


    /**
     * Trace une variable quelconque
     * @author  Jacksay<studio@jacksay.com>
     * @author  Cyril MAGUIRE<contact@ecyseo.net>
     * @version 2.0
     * 
     * @param mixed $mixedvar
     * @param string $comment
     * @param int $sub
     */
    public static function trac($mixedvar, string $comment = '', int $sub = 0) 
    {
        $debug = debug_backtrace();
        $r = self::_trac($mixedvar, $comment, $sub);
        $r .= "\n\n\n"; 
        self::getInstance()->OUTPUT .= '<pre>'."\n\n".'<p class="jcktraker-backtrace">'."\n".'&nbsp;Appel du Debugger à la ligne '.$debug[0]['line']. ' du fichier'."\n".'&nbsp;<strong><em>'.$debug[0]['file'].'</em></strong>'."\n\n".'</p>'."\n\n".'<strong class="jcktraker-blue">'.$comment.'</strong> = '. $r ."</pre>\n";
        self::getInstance()->TRAC_NUM++;
    }

    /**
     * Affiche une petite ligne pour suivre le fil de l'exécution.
     * A utiliser dans un foreach par exemple pour savoir quel valeur prend une variable
     *
     * @author  Jacksay<studio@jacksay.com>
     * @version 1.0
     * 
     * @param string $message
     * @param int $type
     */
    public static function flow($message, $type = 1)
    {
        self::getInstance()->OUTPUT .= '<p class="jcktraker-flow-'.$type.'">'.htmlentities($message)."</p>\n";
        self::getInstance()->TRAC_NUM++;
    }

 
    /**
     * Initialise le deboguage ou non suivant que l'IP soit autorisee au pas
     *
     * @author  Jacksay<studio@jacksay.com>
     * @author  Cyril MAGUIRE<contact@ecyseo.net>
     * @version 2.0
     */
    public static function init()
    {
        self::getInstance()->debug = (in_array($_SERVER['REMOTE_ADDR'], self::$allow_IP));
    }
    /**
     * Instance de la classe
     *
     * @author  Jacksay<studio@jacksay.com>
     * @author  Cyril MAGUIRE<contact@ecyseo.net>
     * @version 2.0
     */
    public static function getInstance()
    {
        if(null === self::$instance)
        {
            $class = __CLASS__;
            self::$instance = new $class();
            self::init();
        }
        return self::$instance;
    }

    private $OUTPUT = "";
    private $TRAC_NUM = 0;
    private static $instance;
    private $debug = false;
    private $is_printed = false;
   
 
    
  /****************************************************************************/
 
    /**
     * Equivalent à un var_dump mais en version sécurisée et en couleur.
     *
     * @author  Cyril MAGUIRE<contact@ecyseo.net>
     * @author  Dimitri Sitchet Tomkeu<dev.dimitrisitchet@gmail.com>
     * @version 1.0
     */
    private static function _trac($mixedvar, $comment='',  $sub = 0, $index = false)
    {
        $type = htmlentities(gettype($mixedvar));
        $r ='';
        switch ($type) 
        {
            case 'NULL':
                $r .= '<em style="color: #0000a0; font-weight: bold;">NULL</em>';
            break;
            case 'boolean':
                if($mixedvar)
                {
                    $r .= '<span style="color: #327333; font-weight: bold;">TRUE</span>';
                } 
                else 
                {
                    $r .= '<span style="color: #327333; font-weight: bold;">FALSE</span>';
                }
            break;
            case 'integer':
                $r .= '<span style="color: red; font-weight: bold;">'.$mixedvar.'</span>';
            break;
            case 'double':
                $r .= '<span style="color: #e8008d; font-weight: bold;">'.$mixedvar.'</span>';
            break;
            case 'string':
                $r .= '<span style="color: '.($index === true ? '#e84a00':'#000').';">\''.$mixedvar.'\'</span>';
            break;
            case 'array':
                $r .= 'Tableau('.count($mixedvar).') &nbsp;{'."\r\n\n";
                foreach($mixedvar AS $k => $e) 
                {
                    $r .= str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $sub+1).'['.self::_trac($k, $comment, $sub+1, true).'] =&gt; '.($k === 'GLOBALS' ? '* RECURSION *':self::_trac($e, $comment, $sub+1)).",\r\n";
                }
                $r .= str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $sub).'}';
            break;
            case 'object':
                $r .= 'Objet «<strong>'.htmlentities(get_class($mixedvar)).'</strong>»&nbsp;{'."\r\n\n";
                $prop = get_object_vars($mixedvar);
                foreach($prop AS $name => $val){
                    if($name == 'privates_variables'){ # Hack (PS: il existe des biblio interne permettant de tuer une classe)
                        for($i = 0, $count = count($mixedvar->privates_variables); $i < $count; $i++)
                        {
                            $r .= str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $sub+1).'<strong>'.htmlentities($get = $mixedvar->privates_variables[$i]).'</strong> =&gt; '.self::_trac($mixedvar->$get, $comment, $sub+1)."\r\n\n";
                        }
                        continue;
                    }
                    $r .= str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $sub+1).'<strong>'.htmlentities($name).'</strong> =&gt; '.self::_trac($val, $comment, $sub+1)."\r\n\n";
                }
                $r .= str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $sub).'}';
            break;
            default:
                $r .= 'Variable de type <strong>'.$type.'</strong>.';
            break;
        }
        $r = preg_replace('/\[(.*)\]/', '[<span class="jcktraker-id">$1</span>]', $r);
        return $r;
    }  
    /**
     * Pour décomposer une variable globale
     * @author  Cyril MAGUIRE<contact@ecyseo.net>
     * @version 1.0
     */
    private static function _color($value) {
        echo "\n\n".self::_trac($value)."\n\n\n";
    }
    

 
    /**
     * Elément clef, va afficher la barre de debug dans votre page.
     * A placer juste avant la balise </body>
     *
     * @author  Jacksay<studio@jacksay.com>
     * @author  Cyril MAGUIRE<contact@ecyseo.net>
     * @author  Dimitri Sitchet Tomkeu<dev.dimitrisitchet@gmail.com>
     * @version 2.0
     */
    public static function printBar() 
    {
        $instance = self::getInstance();
        if(true !== $instance->debug OR true === $instance->is_printed) 
        {
            return;
        }
        $instance->is_printed = true;
        ?>
        <!-- JCK TRAKER BOX v2.1 | Refactor by Dimitri Sitchet Tomkeu for dFramework -->
        <script type="text/javascript">
            function jcktraker_hide(){
                var sections = document.querySelectorAll('#jcktraker-box .jcktraker-section'),
                    dispatchers = document.querySelectorAll('#jcktraker-menu li:not(:first-child)'),
                    num_sections = sections.length,
                    num_dispatchers = dispatchers.length;
                for( var i=0; i<num_sections; i++ ){
                    sections[i].style.display = 'none';
                }
                for( var i=0; i<num_dispatchers; i++ ){
                    dispatchers[i].style.fontWeight = "normal";
                    dispatchers[i].style.backgroundColor = '#000000';
                    dispatchers[i].style.color = '#FFFFFF';
                }
            }
            function jcktraker_toogle(section, dispatcher){
                var section_blk = document.getElementById(section);
                if( section_blk.style.display != 'block'){
                    jcktraker_hide();
                    section_blk.style.display = 'block';
                    dispatcher.style.fontWeight = 'bold';
                    dispatcher.style.backgroundColor = '#990000';
                    dispatcher.style.color = '#FFFFFF';
                }
                else {
                    section_blk.style.display = 'none';
                    dispatcher.style.fontWeight = "normal";
                    dispatcher.style.backgroundColor = '#000000';
                    dispatcher.style.color = '#FFFFFF';
                }
            }
	    </script>
        <style type="text/css">
            .jcktraker-blue {
                color:#76b4ee;
            }
            .jcktraker-id {
                color:#e8008d;
            }
            #jcktraker-box {
                z-index:99999;
                position: fixed;
                bottom: 0;
                right: 0;
                font-size: 10px;
                font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
                max-height: 100%;
                width: 58%;
                margin: 0;
                padding: 0;
                border-radius: .4em;
                box-shadow: 0 0 1em #000;
            }
            #jcktraker-box *{
                margin: 0;
                padding: 0;
                border-radius: .4em;
            }
            #jcktraker-box .jcktraker-section {
                margin-bottom: -0.5em;
                height: 500px;
                width: 100%;
                overflow: hidden;
                background: #111;
                color: #fff;
                opacity: .7;
                padding: .5em;
                display: none;
            }
            #jcktraker-box .jcktraker-section:hover {
                opacity: 1;
            }
            #jcktraker-box .jcktraker-title {
                display: inline-block;
                margin: .25em;
                padding: .25em;
                font-size: 1.25em;
                font-weight: bold;
                border-bottom: 2px solid;
                width: 100%;
            }
            #jcktraker-box .jcktraker-content {
                overflow-y: auto;
                width: 96%;
                height: 90%;
                padding: 1em;
            }
            #jcktraker-box .jcktraker-content pre {
                overflow-x: auto;
                color:#000;
                margin: 1em 0;
                border: dotted thin #999;
                border-radius: .4em;
                box-shadow: 0 0 1em #000 inset;
                padding: .4em .6em;
                background-color: #e4e4e4;
                font-size: 1.2em;
                white-space: pre;           /* CSS 2.0 */
                white-space: pre-wrap;      /* CSS 2.1 */
                white-space: pre-line;      /* CSS 3.0 */
                word-wrap: break-word;      /* IE 5+ */
            }
            #jcktraker-box .jcktraker-content pre:first-child {
                margin-top: 0;
            } #jcktraker-box .jcktraker-content pre:last-child {
                margin-bottom: 0;
            }
            ul#jcktraker-menu li {
                display: inline;
                padding: .25em .5em;
                line-height: 2em;
            }
            ul#jcktraker-menu li[onclick]:hover {
                background: #990000;cursor: pointer;
            } 
            #jcktraker-menu {
                margin: 0;
                padding: 0;
                padding-right: .5em;
                background: #000;
                color: #fff;
                white-space:nowrap;
                text-align: right;
                border-radius: .4em 0 0 0;
            }
            .jcktraker-backtrace {
                background-color: #e4a504;
            }
        </style>

        <div id="jcktraker-box">
            <div id="jcktraker-post" class="jcktraker-section">
                <span class="jcktraker-title">$_POST</span>
                <div class="jcktraker-content"><pre><?php self::_color($_POST); ?></pre></div>
            </div>
            <div id="jcktraker-files" class="jcktraker-section">
                <span class="jcktraker-title">$_FILES</span>
                <div class="jcktraker-content"><pre><?php self::_color($_FILES); ?></pre></div>
            </div>
            <div id="jcktraker-get" class="jcktraker-section">
                <span class="jcktraker-title">$_GET</span>
                <div class="jcktraker-content"><pre><?php self::_color($_GET); ?></pre></div>
            </div>
            <div id="jcktraker-server" class="jcktraker-section">
                <span class="jcktraker-title">$_SERVER</span>
                <div class="jcktraker-content"><pre><?php self::_color($_SERVER); ?></pre></div>
            </div>
            <div id="jcktraker-session" class="jcktraker-section">
                <span class="jcktraker-title">$_SESSION</span>
                <div class="jcktraker-content"><pre><?php self::_color($_SESSION); ?></pre></div>
            </div>
            <div id="jcktraker-cookie" class="jcktraker-section">
                <span class="jcktraker-title">$_COOKIE</span>
                <div class="jcktraker-content"><pre><?php self::_color($_COOKIE); ?></pre></div>
            </div>
            <div id="jcktraker-request" class="jcktraker-section">
                <span class="jcktraker-title">$_REQUEST</span>
                <div class="jcktraker-content"><pre><?php self::_color($_REQUEST); ?></pre></div>
            </div>
            <div id="jcktraker-own" class="jcktraker-section">
                <span class="jcktraker-title">YOUR TRAC</span>
                <div class="jcktraker-content"><?php echo $instance->OUTPUT; ?></div>
            </div>
            <ul id="jcktraker-menu">
                <li style="color:cornflowerblue"><strong>dFramework | ToolBarDebug <span>v2.0</span></strong></li>
                <li id="jacktraker_own_button" onclick="jcktraker_toogle('jcktraker-own', this)">TRAC(<?php echo $instance->TRAC_NUM ?>)</li>
                <li onclick="jcktraker_toogle('jcktraker-post', this)">$_POST(<?php echo count($_POST) ?>)</li>
                <li onclick="jcktraker_toogle('jcktraker-files', this)">$_FILES(<?php echo count($_FILES) ?>)</li>
                <li onclick="jcktraker_toogle('jcktraker-get', this)">$_GET(<?php echo count($_GET) ?>)</li>
                <li onclick="jcktraker_toogle('jcktraker-server', this)">$_SERVER(<?php echo count($_SERVER) ?>)</li>
                <li onclick="jcktraker_toogle('jcktraker-session', this)"><?php if(isset ($_SESSION)) { echo '$_SESSION(',count($_SESSION),')'; } else { echo '<del>$_SESSION</del>';} ?></li>
                <li onclick="jcktraker_toogle('jcktraker-cookie', this)">$_COOKIE(<?php echo count($_COOKIE) ?>)</li>
                <li onclick="jcktraker_toogle('jcktraker-request', this)">$_REQUEST(<?php echo count($_REQUEST) ?>)</li>
            </ul>
        </div>
        <?php if(!empty ($instance->OUTPUT) ): ?>
            <script type="text/javascript">jcktraker_toogle('jcktraker-own', document.getElementById('jacktraker_own_button'));</script>
        <?php endif;
    
    }
}