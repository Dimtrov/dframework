<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019 - 2021, Dimtrov Lab's
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitric Sitchet Tomkeu <dev.dst@gmail.com>
 * @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab''s. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019 - 2021, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.2.3
 */

namespace dFramework\libraries;

use Spipu\Html2Pdf\Html2Pdf;

/**
 * PDF
 *  Permet de generer les documents PDF via le PHP
 *
 * @package		dFramework
 * @subpackage	Library
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/guide/Dom.html
 * @since       3.0
 * @file        /system/librairies/Pdf.php
 */
class Pdf
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
     * Definit le contenu a generer 
     * 
     * @param string $content
     * @return self
     */
    public function write($content) : self
    {
        $this->pdf->writeHTML($content);

        return $this;
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
        $name = preg_replace('#\.pdf$#', '', $name).'.pdf';

        $this->pdf->output($name);
    }
}