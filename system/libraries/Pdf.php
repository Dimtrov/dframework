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

use Spipu\Html2Pdf\Html2Pdf;

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

class dF_Pdf
{
    /**
     * @var Html2Pdf 
     */
    private $pdf = null;

    public function __construct()
    {
        $this->init();
    }
    
    /**
     * @param string $orientation
     * @param string|array $format
     * @param string $locale
     */
    public function init(string $orientation = 'P', $format = 'A4', string $locale = 'fr')
    {
        $this->pdf = new Html2Pdf($orientation, $format, $locale);
    }

    /**
     * @param string $content
     */
    public function write($content)
    {
        $this->pdf->writeHTML($content);
    }

    /**
     * @param string $mode
     */
    public function displayMode($mode)
    {
        $this->pdf->pdf->SetDisplayMode($mode);
    }

    /**
     * @param string|null $name
     */
    public function render(?string $name = 'document.pdf')
    {
        $this->pdf->output($name);
    }
}