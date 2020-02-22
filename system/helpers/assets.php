<?php
// ------------------------------------------------------------------------

if ( ! function_exists('css_url'))
{
    /**
     * CSS URL
     *
     * Renvoie l'url d'un fichier css.
     *
     * @param	string	$name nom du fichier dont on veut avoir l'url
     * @return	string
     */
    function css_url($name)
    {
        $name = htmlspecialchars($name);
        if(is_localfile($name))
        {
            $name .=  (!preg_match('#\.css$#i', $name) ? '.css' : '');
            $filename = WEBROOT.'css'.DS.$name;
            return base_url() . 'css/' . $name.((file_exists($filename)) ? '?_ref='.filemtime($filename) : '');
        }
        return $name . (!preg_match('#\.css$#i', $name) ? '.css' : '');
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('js_url'))
{
    /**
     * JS URL
     *
     * Renvoie l'url d'un fichier js.
     *
     * @param	string	$name nom du fichier dont on veut avoir l'url
     * @return	string
     */
    function js_url($name)
    {
        $name = htmlspecialchars($name);
        if(is_localfile($name))
        {
            $name .=  (!preg_match('#\.js$#i', $name) ? '.js' : '');
            $filename = WEBROOT.'js'.DS.$name;
            return base_url() . 'js/' . $name.((file_exists($filename)) ? '?_ref='.filemtime($filename) : '');
        }
        return $name . (!preg_match('#\.js$#i', $name) ? '.js' : '');
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('lib_css_url'))
{
    /**
     * LIB CSS URL
     *
     * Renvoie l'url d'un fichier css d'une librairie
     *
     * @param	string	$name nom du fichier dont on veut avoir l'url
     * @return	string
     */
    function lib_css_url($name)
    {
        $name = htmlspecialchars($name);
        if(is_localfile($name))
        {
            $name .=  (!preg_match('#\.css$#i', $name) ? '.css' : '');
            $filename = WEBROOT.'lib'.DS.$name;
            return base_url() . 'lib/' . $name.((file_exists($filename)) ? '?_ref='.filemtime($filename) : '');
        }
        return $name . (!preg_match('#\.css$#i', $name) ? '.css' : '');
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('lib_js_url'))
{
    /**
     * LIB JS URL
     *
     * Renvoie l'url d'un fichier js d'une librairy.
     *
     * @param	string	$name nom du fichier dont on veut avoir l'url
     * @return	string
     */
    function lib_js_url($name)
    {
        $name = htmlspecialchars($name);
        if(is_localfile($name))
        {
            $name .=  (!preg_match('#\.js$#i', $name) ? '.js' : '');
            $filename = WEBROOT.'lib'.DS.$name;
            return base_url() . 'lib/' . $name.((file_exists($filename)) ? '?_ref='.filemtime($filename) : '');
        }
        return $name . (!preg_match('#\.js$#i', $name) ? '.js' : '');
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('img_url'))
{
    /**
     * IMG URL
     *
     * Renvoie l'url d'une image
     *
     * @param	string	$name nom du fichier dont on veut avoir l'url
     * @return	string
     */
    function img_url($name)
    {
        $name = htmlspecialchars($name);
        if(is_localfile($name))
        {
            $filename = WEBROOT.'img'.DS.$name;
            return base_url() . 'img/' . $name.((file_exists($filename)) ? '?_ref='.filemtime($filename) : '');
        }
        return $name;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('img'))
{
    /**
     * IMG
     *
     * Cree une image
     *
     * @param    string $name nom du fichier dont on veut inserer
     * @param    string $alt texte alternatif
     * @param array $options
     * @return    void
     */
    function img($name, $alt = '', array $options = [])
    {
        $return = '<img src="' . img_url($name) . '" alt="' . $alt . '"';
        foreach ($options As $key => $value)
        {
            $return .= ' '.$key.'="'.$value.'"';
        }
        $return .= ' />';
        echo $return;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('lib_styles'))
{
    /**
     * LIB_STYLES
     *
     * inclu une ou plusieurs feuilles de style css
     *
     * @param	string|string[]	$name nom du fichier dont on veut inserer
     * @return	void
     */
    function lib_styles($name) : void
    {
        $name = (array) $name;
        foreach ($name As $style)
        {
            if(is_string($style))
            {
                $style = (!preg_match('#\.css$#i', $style) ? $style.'.css' : $style);
                if(is_file(WEBROOT.'lib'.DS.str_replace('/', DS, $style)))
                {
                    echo '<link rel="stylesheet" type="text/css" href="'.lib_css_url($style).'" />'; echo "\n";
                }
                else if(is_localfile($style))
                {
                    echo '<!-- The specified file do not exist. we can not load it.'; echo "\n\t";
                    echo '<link rel="stylesheet" type="text/css" href="'.lib_css_url($style).'" /> -->'; echo "\n";
                }
				else 
				{
					echo '<link rel="stylesheet" type="text/css" href="'.lib_css_url($style).'" />'; echo "\n";
				}
            }
        }
        return;
    }
}

// ------------------------------------------------------------------------
if ( ! function_exists('lib_scripts'))
{
    /**
     * LIB_SCRIPTS
     *
     * inclu un ou plusieurs scripts js
     *
     * @param	string|string[]	$name nom du fichier dont on veut inserer
     * @return	void
     */
    function lib_scripts($name)
    {
        $name = (array) $name;
        foreach ($name As $script)
        {
            if(is_string($script))
            {
                $script = (!preg_match('#\.js$#i', $script) ? $script.'.js' : $script);
                if(is_file(WEBROOT.'lib'.DS.str_replace('/', DS, $script)))
                {
                    echo '<script type="text/javascript" src="'.lib_js_url($script).'"></script>'; echo "\n";
                }
                else if(is_localfile($script))
                {
                    echo '<!-- The specified file do not exist. we can not load it.'; echo "\n\t";
                    echo '<script type="text/javascript" src="'.lib_js_url($script).'"></script> -->'; echo "\n";
                }
				else 
				{
					echo '<script type="text/javascript" src="'.lib_js_url($script).'"></script>'; echo "\n";
				}
            }
        }
        return;
    }
}

// ------------------------------------------------------------------------
if ( ! function_exists('styles'))
{
    /**
     * STYLES
     *
     * inclu une ou plusieurs feuilles de style css
     *
     * @param	string|string[]	$name nom du fichier dont on veut inserer
     * @return	void
     */
    function styles($name)
    {
        $name = (array) $name;
        foreach ($name As $style)
        {
            if(is_string($style))
            {
                $style = (!preg_match('#\.css$#i', $style) ? $style.'.css' : $style);
                if(is_file(WEBROOT.'css'.DS.str_replace('/', DS, $style)))
                {
                    echo '<link rel="stylesheet" type="text/css" href="'.css_url($style).'" />'; echo "\n";
                }
                else if(is_localfile($style))
                {
                    echo '<!-- The specified file do not exist. we can not load it.'; echo "\n\t";
                    echo '<link rel="stylesheet" type="text/css" href="'.css_url($style).'" /> -->'; echo "\n";
                }
				else 
				{
					echo '<link rel="stylesheet" type="text/css" href="'.css_url($style).'" />'; echo "\n";
				}
            }
        }
        return;
    }
}

// ------------------------------------------------------------------------
if ( ! function_exists('scripts'))
{
    /**
     * SCRIPTS
     *
     * inclu un ou plusieurs scripts js
     *
     * @param	string|string[]	$name nom du fichier dont on veut inserer
     * @return	void
     */
    function scripts($name)
    {
        $name = (array) $name;
        foreach ($name As $script)
        {
            if(is_string($script))
            {
                $script = (!preg_match('#\.js$#i', $script) ? $script.'.js' : $script);
                if(is_file(WEBROOT.'js'.DS.str_replace('/', DS, $script)))
                {
                    echo '<script type="text/javascript" src="'.js_url($script).'"></script>'; echo "\n";
                }
                else if(is_localfile($script))
                {
                    echo '<!-- The specified file do not exist. we can not load it.'; echo "\n\t";
                    echo '<script type="text/javascript" src="'.js_url($script).'"></script> -->'; echo "\n";
                }
				else 
				{
					echo '<script type="text/javascript" src="'.js_url($script).'"></script>'; echo "\n";
				}
            }
        }
        return;
    }
}

// ------------------------------------------------------------------------
