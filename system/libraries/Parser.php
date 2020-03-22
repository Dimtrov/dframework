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

use \Codedepp\BBCode\BBCode;


/**
 * dF_Parser
 *
 * Parse du bbcode ou le markdown en html
 *
 * @package		dFramework
 * @subpackage	Library
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/Parser.html
 * @since       2.1
 * @file        /system/libraries/Parser.php
 */

class dF_Parser
{   
    /**
     * @var bool Specifie si on echappe les entités HTML (pour eviter les failles XSS)
     */
    private $safeMode = true;
    /**
     * @var bool
     */
    private $markupEscaped = true;
    /**
     * @var int  Type de parsing a utiliser self::MARKDOWN | self::BBCODE
     */
    private $type = self::MARKDOWN;

    /**
     * Parseur markdown
     */
    const MARKDOWN = 1;
    /**
     * Parseur bbcode
     */
    const BBCODE = 2;



    /**
     * @var Parsedown Instance markdown
     */
    private $markdown_engine;
    /**
     * @var BBCode Instance bbcode
     */
    private $bbcode_engine;


    public function __construct()
    {
        $this->markdown_engine = new Parsedown;

        $this->bbcode_engine = new BBCode;
    }


    /**
     * Parse un texte markdown ou bbcode en son equivalent html
     *
     * @param string $text
     * @return string
     */
    public function parse(string $text) : string
    {
        if(self::BBCODE === $this->type)
        {
            return $this->bbcode($text);
        }
        return $this->markdown($text);
    }

    /**
     * Parse un code html en son equivalent markdown ou bbcode
     *
     * @param string $text
     * @return string
     */
    public function uglify(string $text) : string
    {
        if(self::BBCODE === $this->type) 
        {
            return $this->bbcode_engine->convertFromHtml($text);
        }
        return $text;
    }

    /**
     * Converti un texte markdown en son equivalent html
     *
     * @param string $text le texte au format markdown
     * @return string le texte transformé
     */
    public function markdown(string $text) : string
    {
        return $this->markdown_engine
            ->setSafeMode($this->safeMode)
            ->setMarkupEscaped($this->markupEscaped)
            ->line($text);
    }


    /**
     * Converti un texte bbcode en son equivalent html
     *
     * @param string $text le texte au format bbcode
     * @return string le texte transformé
     */
    public function bbcode(string $text) : string
    {
        return $this->bbcode_engine->convertToHtml($text);
    }


    
    
    /**
     * Specifie le type de parsing a utilise (markdown ou bbcode)
     *
     * @param int $type
     * @return dF_Parser
     */
    public function type(int $type) : self 
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Recupere l'instance du parseur utiliser
     *
     * @param int|null $type
     * @return Parsedown|BBCode
     */
    public function instance(?int $type = self::MARKDOWN)
    {
        if(self::BBCODE === $type) 
        {
            return $this->bbcode_engine;
        }
        return $this->markdown_engine;
    }

    /**
     * @param bool $safeMode
     * @return dF_Parser
     */
    public function setSafeMode(bool $safeMode) : self
    {
        $this->safeMode = $safeMode;
        return $this;
    }

    /**
     * @param bool $markupEscaped
     * @return dF_Parser
     */
    public function setMarkupEscaped(bool $markupEscaped) : self
    {
        $this->markupEscaped = $markupEscaped;
        return $this;
    }
}