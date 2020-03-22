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
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.0
 */

use simplehtmldom_1_5\simple_html_dom;

/**
 * Dom
 *  Permet de manipuler l'arbre du DOM via le PHP
 *
 * @package		dFramework
 * @subpackage	Library
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/guide/Dom.html
 * @since       3.0
 * @file        /system/librairies/Dom.php
 */

class dF_Dom extends simple_html_dom
{

    /**
     * Get/Set a specific attribute like jQuery
     * 
     * @param string $attr Attribute name
     * @param mixed $value Attribute value
     * @return mixed|dF_Dom
     */
    public function attr($attr, $value = null)
    {
        if(is_array($attr))
        {
            foreach ($attr As $key => $val) 
            {
                if(is_string($key))
                {
                    $this->attr($key, $val);
                }
            }
        }
        else if(null === $value)
        {
            return $this->getAttribute($attr);
        }
        else 
        {
            $this->setAttribute($attr, $value);
        }
        
        return $this;
    }

    /**
     * Add a class on html element
     * 
     * @param string|string[] $classes A class to added
     * @return dF_Dom
     */
    public function addClass($classes)
    {
        $classes = (array) $classes;
        
        $actual_class = $this->getAttribute('class');
        $actual_class = explode(' ', $actual_class);
       
        foreach ($classes As $class) 
        {
            if(is_string($class) AND !in_array($class, $actual_class))
            {
                array_push($actual_class, $class);
            }
        }
        $this->setAttribute('class', implode(' ', $actual_class));

        return $this;
    }
    /**
     * Remove a class on html element
     * 
     * @param string|string[] $classes A class to removed
     * @return dF_Dom
     */
    public function removeClass($classes)
    {
        $classes = (array) $classes;
        
        $actual_class = $this->getAttribute('class');
        $actual_class = explode(' ', $actual_class);
       
        foreach ($classes As $key => $class) 
        {
            if(is_string($class) AND in_array($class, $actual_class))
            {
                $tmp = $actual_class[0];
                $actual_class[0] = $actual_class[$key];
                $actual_class[$key] = $tmp;
                array_shift($actual_class);
            }
        }
        $this->setAttribute('class', implode(' ', $actual_class));

        return $this;
    }

    public function html($value = null)
    {

    }

    public function text($value = null)
    {

    }


}