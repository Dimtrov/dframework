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
 * @version     3.0
 */

/**
 * dF_Ua
 *
 * Donne les informations relatives au user agent utilisé par le visiteur
 *
 * @package		dFramework
 * @subpackage	Library
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/Ua.html
 * @since       2.0
 * @file        /system/libraries/Ua.php
 */

use dFramework\core\Config;
use dFramework\core\utilities\Chaine;

class dF_Ua
{
    /**
     * @var Browser instance de la dependance Browser
     */
    private $_browser;

    /**
     * dF_Ua constructor.
     *
     * Initialise an instance of Browser Class for the general's method of class
     */
    public function __construct()
    {
        $this->_browser = new Browser();
    }


    /**
     * Reset all properties of Browser dependency object
     * 
     * @return void
     */
    public function reset() : void 
    {
        $this->_browser->reset();
    }

    /**
     * Check to see if the specific browser is the actual browser used by visitor
     *
     * @param string $browserName The name of browser that we want to check
     * @return bool True if the browser is the specified browser otherwise false
     */
    public function isBrowser(string $browserName) : bool
    {
        return $this->_browser->isBrowser($browserName);
    }

    /**
     * Return the name of the browser used by visitor.
     *
     * @return string Name of the browser
     */
    public function browser() : string
    {
        return $this->_browser->getBrowser();
    }

    /**
     * Return the name of the platform (Operating system) used by visitor.
     *
     * @return string Name of the platform
     */
    public function platform() : string
    {
        return $this->_browser->getPlatform();
    }

    /**
     * Return the version of the browser used by visitor.
     *
     * @return string Version of the browser (will only contain alpha-numeric characters and a period)
     */
    public function version() : string
    {
        return $this->_browser->getVersion();
    }

    /**
     * Check if the equipment used by visitor is a mobile device?
     *
     * @return bool True if the browser is from a mobile device otherwise false
     */
    public function isMobile() : bool
    {
        return $this->_browser->isMobile();
    }

    /**
     * Check if the equipment used by visitor is a tablet device?
     *
     * @return bool True if the browser is from a tablet device otherwise false
     */
    public function isTablet() : bool
    {
        return $this->_browser->isTablet();
    }

    /**
     * Is the browser from a robot (ex Slurp,GoogleBot)?
     *
     * @return bool True if the browser is from a robot otherwise false
     */
    public function isRobot() : bool
    {
        return $this->_browser->isRobot();
    }

    /**
     * Is the browser from facebook?
     *
     * @return bool True if the browser is from facebook otherwise false
     */
    public function isFacebook() : bool
    {
        return $this->_browser->isFacebook();
    }

    /**
     * Is the browser from AOL?
     *
     * @return bool True if the browser is from AOL otherwise false
     */
    public function isAol() : bool
    {
        return $this->_browser->isAol();
    }

    /**
     * Get the user agent value in use to determine the browser
     *
     * @return string The user agent from the HTTP header
     */
    public function userAgent() : string
    {
        return $this->_browser->getUserAgent();
    }

    /**
     * Set the user agent value (the construction will use the HTTP header value - this will overwrite it)
     *
     * @param string $agent_string The value for the User Agent
     * @return void
     */
    public function setUserAgent(string $agent_string) : void
    {
        $this->_browser->setUserAgent($agent_string);
    }


    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        $method = Chaine::toCamelCase($name);
        if(method_exists($this, $method))
        {
            return $this->$method();
        }
        $method = Chaine::toCamelCase('is'.$name);
        if(method_exists($this, $method))
        {
            return $this->$method();
        }
        $method = Chaine::toCamelCase('get'.$name);
        if(method_exists($this, $method))
        {
            return $this->$method();
        }
    }


    /* ---------------------------------------------------------------------------------------------------------------- */


    /**
     * Is this a referral from another site?
     *
     * @return	bool True if the user agent is a referral, false if not
     */
    public function isReferral() : bool
    {
        if ( ! isset($this->referer))
        {
            if (empty($_SERVER['HTTP_REFERER']))
            {
                $this->referer = FALSE;
            }
            else
            {
                $referer_host = @parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
                $own_host = parse_url(Config::get('general.base_url'), PHP_URL_HOST);

                $this->referer = ($referer_host AND $referer_host !== $own_host);
            }
        }
        return $this->referer;
    }

    /**
     * Get the referrer
     *
     * @return	string|null The referrer, if the user agent was referred from another site or null
     */
    public function referrer() : ?string
    {
        return empty($_SERVER['HTTP_REFERER']) ? null : trim($_SERVER['HTTP_REFERER']);
    }

    /**
     * Get the accepted languages
     *
     * @return	array list of accepted languages
     */
    public function languages() : array
    {
        if (count($this->_languages) === 0)
        {
            $this->_set_languages();
        }
        return $this->_languages;
    }

    /**
     * Lets you determine if the user agent accepts a particular language.
     *
     * @param	string	$lang language key
     * @return	bool True if provided language is accepted, false if not
     */
    public function accept_lang(string $lang = 'en') : bool
    {
        return in_array(strtolower($lang), $this->languages(), TRUE);
    }

    /**
     * Get the accepted Character Sets
     *
     * @return	array list of accepted character sets
     */
    public function charsets() : array
    {
        if (count($this->_charsets) === 0)
        {
            $this->_set_charsets();
        }
        return $this->_charsets;
    }

    /**
     * Lets you determine if the user agent accepts a particular character set
     *
     * @param	string	$charset Character set
     * @return	bool True if provided character set is accepted, false if not
     */
    public function accept_charset(string $charset = 'utf-8') : bool
    {
        return in_array(strtolower($charset), $this->charsets(), TRUE);
    }


    private $_languages = [];
    /**
     * Set the accepted languages
     *
     * @return	void
     */
    private function _set_languages()
    {
        if ((count($this->_languages) === 0) AND ! empty($_SERVER['HTTP_ACCEPT_LANGUAGE']))
        {
            $this->_languages = explode(',', preg_replace('/(;\s?q=[0-9\.]+)|\s/i', '', strtolower(trim($_SERVER['HTTP_ACCEPT_LANGUAGE']))));
        }
        if (count($this->_languages) === 0)
        {
            $this->_languages = ['Undefined'];
        }
    }

    /**
     * @var array
     */
    private $_charsets = [];
    /**
     * Set the accepted character sets
     *
     * @return	void
     */
    private function _set_charsets()
    {
        if ((count($this->_charsets) === 0) AND ! empty($_SERVER['HTTP_ACCEPT_CHARSET']))
        {
            $this->_charsets = explode(',', preg_replace('/(;\s?q=.+)|\s/i', '', strtolower(trim($_SERVER['HTTP_ACCEPT_CHARSET']))));
        }
        if (count($this->_charsets) === 0)
        {
            $this->_charsets = ['Undefined'];
        }
    }

}