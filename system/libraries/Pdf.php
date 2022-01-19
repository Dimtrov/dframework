<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019 - 2021, Dimtrov Lab's
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitric Sitchet Tomkeu <devcode.dst@gmail.com>
 * @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab''s. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019 - 2021, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.4.0
 */

namespace dFramework\libraries;

use Spipu\Html2Pdf\Exception\ExceptionFormatter;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Html2Pdf;

/**
 * PDF
 *  Permet de generer les documents PDF via le PHP
 *
 * @package		dFramework
 * @subpackage	Library
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/guide/Dom.html
 * @since       3.0
 * @file        /system/librairies/Pdf.php
 */
class Pdf
{
    /**
     * @var Html2Pdf
     */
    private $generator = null;

	/**
	 * Constructor
	 */
    public function __construct()
    {
        $this->init();
    }

    /**
	 * Initialise le generateur de pdf
	 *
     * @param string $orientation
     * @param string|array $format
     * @param string $locale
	 * @param bool $unicode
     * @param string $encoding
	 * @param array $margins
	 * @param bool $pdfa
	 *
	 * @return self
     */
    public function init(string $orientation = 'P', $format = 'A4', string $locale = 'fr', bool $unicode = true, string $encoding = 'UTF-8', array $margins = [5, 5, 5, 8], bool $pdfa = false) : self
    {
        $this->generator = new Html2Pdf($orientation, $format, $locale, $unicode, $encoding, $margins, $pdfa);

		return $this;
    }

	/**
	 * Recupere l'instance de Html2Pdf
	 *
	 * @return Html2Pdf
	 */
	public function getGenerator() : Html2Pdf
	{
		return $this->generator;
	}

    /**
     * Definit le contenu a generer
     *
     * @param string $content
     * @return self
     */
    public function write(string $content) : self
    {
        $this->generator->writeHTML($content);

        return $this;
    }

    /**
	 * Defines the way the document is to be displayed by the viewer.
	 *
	 * @param mixed $zoom The zoom to use. It can be one of the following string values or a number indicating the zooming factor to use. <ul><li>fullpage: displays the entire page on screen </li><li>fullwidth: uses maximum width of window</li><li>real: uses real size (equivalent to 100% zoom)</li><li>default: uses viewer default mode</li></ul>
	 * @param string $layout The page layout. Possible values are:<ul><li>SinglePage Display one page at a time</li><li>OneColumn Display the pages in one column</li><li>TwoColumnLeft Display the pages in two columns, with odd-numbered pages on the left</li><li>TwoColumnRight Display the pages in two columns, with odd-numbered pages on the right</li><li>TwoPageLeft (PDF 1.5) Display the pages two at a time, with odd-numbered pages on the left</li><li>TwoPageRight (PDF 1.5) Display the pages two at a time, with odd-numbered pages on the right</li></ul>
	 * @param string $mode A name object specifying how the document should be displayed when opened:<ul><li>UseNone Neither document outline nor thumbnail images visible</li><li>UseOutlines Document outline visible</li><li>UseThumbs Thumbnail images visible</li><li>FullScreen Full-screen mode, with no menu bar, window controls, or any other window visible</li><li>UseOC (PDF 1.5) Optional content group panel visible</li><li>UseAttachments (PDF 1.6) Attachments panel visible</li></ul>
	 * @return self
	 */
	public function displayMode($zoom, string $layout = 'SinglePage', string $mode = 'UseNone') : self
    {
        $this->generator->pdf->SetDisplayMode($zoom, $layout, $mode);

		return $this;
    }

	/**
     * display a automatic index, from the bookmarks
     *
     * @access public
     * @param  string  $titre         index title
     * @param  int     $sizeTitle     font size of the index title, in mm
     * @param  int     $sizeBookmark  font size of the index, in mm
     * @param  boolean $bookmarkTitle add a bookmark for the index, at his beginning
     * @param  boolean $displayPage   display the page numbers
     * @param  int     $onPage        if null : at the end of the document on a new page, else on the $onPage page
     * @param  string  $fontName      font name to use
     * @param  string  $marginTop     margin top to use on the index page
     * @return self
     */
	public function createIndex(string $titre = 'Index', int $sizeTitle = 20, int $sizeBookmark = 15, bool $bookmarkTitle = true, bool $displayPage = true, ?int $onPage = null, ?string $fontName = null, ?string $marginTop = null) : self
	{
		$this->generator->CreateIndex($titre, $sizeTitle, $sizeBookmark, $bookmarkTitle, $displayPage, $onPage, $fontName, $marginTop);

		return $this;
	}

    /**
	 * Rend le pdf généré au navigateur
	 *
     * @param string $name
     */
    public function render(string $name = 'document.pdf')
    {
        $this->output($name, 'I');
    }

	/**
	 * Rend le pdf généré au navigateur et force le téléchargement du fichier
	 *
     * @param string $name
     */
	public function download(string $name = 'document.pdf')
	{
		$this->output($name, 'D');
	}

	/**
	 * Sauvegarde le pdf généré sur le serveur local
	 *
     * @param string $name
     */
	public function save(string $name = 'document.pdf')
	{
		$this->output($name, 'F');
	}

	/**
	 * Rend le pdf généré au navigateur et sauvegarde le fichier sur le serveur local
	 *
     * @param string $name
     */
	public function renderSave(string $name = 'document.pdf')
	{
		$this->output($name, 'FI');
	}

	/**
	 * Rend le pdf généré au navigateur, force le téléchargement de celui-ci et sauvegarde le fichier sur le serveur local
	 *
     * @param string $name
     */
	public function downloadSave(string $name = 'document.pdf')
	{
		$this->output($name, 'FD');
	}

	/**
	 * Transforme le pdf généré en tant que base64 pour envoyer par email comme etant une piece jointe
	 *
     * @param string $name
     */
	public function toAttachment(string $name = 'document.pdf')
	{
		$this->output($name, 'E');
	}

	/**
     * Send the document to a given destination: string, local file or browser.
     * Dest can be :
     *  I : send the file inline to the browser (default). The plug-in is used if available. The name given by name is used when one selects the "Save as" option on the link generating the PDF.
     *  D : send to the browser and force a file download with the name given by name.
     *  F : save to a local server file with the name given by name.
     *  S : return the document as a string (name is ignored).
     *  FI: equivalent to F + I option
     *  FD: equivalent to F + D option
     *  E : return the document as base64 mime multi-part email attachment (RFC 2045)
     *
     * @param string $name The name of the file when saved.
     * @param string $dest Destination where to send the document.
     *
     * @throws Html2PdfException
     * @return string content of the PDF, if $dest=S
     */
    public function output($name = 'document.pdf', $dest = 'I')
    {
		try {
			$output = $this->generator->output(preg_replace('#\.pdf$#', '', $name).'.pdf', $dest);
			if ($dest == 'S' OR $dest == 'E')
			{
				return $output;
			}
			exit;
		} catch (Html2PdfException $e) {
			$this->generator->clean();

			$formatter = new ExceptionFormatter($e);
			echo $formatter->getHtmlMessage();
		}
	}
}
