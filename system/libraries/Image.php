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

use dFramework\core\exception\Exception;
use Grafika\Color;
use Grafika\Grafika;

/**
 * Image
 *  Bibliotheque de manipulation d'images via php
 *
 * @package		dFramework
 * @subpackage	Library
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/guide/Image.html
 * @since       3.0
 * @file        /system/libraries/Image.php
 */

class dF_Image
{
    /**
     * @var 
     */
    private $editor;

    /**
     * @var
     */
    private $image;

    /**
     * @var string
     */
    private $img_src = '';


    const RESIZE_EXACT  = 'exact';
    const RESIZE_FILL   = 'fill';
    const RESIZE_FIT    = 'fit';
    const RESIZE_HEIGHT = 'exactHeight';
    const RESIZE_WIDTH  = 'exactWidth';
    
    const POSITION_BOTTOMLEFT   = 'bottom-left';
    const POSITION_BOTTOMCENTER = 'bottom-center';
    const POSITION_BOTTOMRIGHT  = 'bottom-right';
    const POSITION_CENTERLEFT   = 'center-left';
    const POSITION_CENTER       = 'center';
    const POSITION_CENTERRIGHT  = 'center-right';
    const POSITION_TOPLEFT      = 'top-left';
    const POSITION_TOPCENTER    = 'top-center';
    const POSITION_TOPRIGHT     = 'top-right';
    const POSITION_SMART        = 'smart';

    const FILTER_BLUR       = 1;
    const FILTER_BRIGHTNESS = 2;
    const FILTER_COLORIZE   = 3;
    const FILTER_CONTRAST   = 4;
    const FILTER_DITHER     = 5;
    const FILTER_GAMMA      = 6;
    const FILTER_GRAYSCALE  = 7;
    const FILTER_INVERT     = 8;
    const FILTER_PIXELATE   = 9;
    const FILTER_SHARPEN    = 10;
    const FILTER_SOBEL      = 11;

    const BLEND_MULTIPLY    = 'multiply';
    const BLEND_NORMAL      = 'normal';
    const BLEND_OVERLAY     = 'overlay';
    const BLEND_SCREEN      = 'screen';

    const FLIP_V = 'v';
    const FLIP_H = 'h';


    /**
     * dF_Image constructor.
     */
    public function __construct()
    {
        try {
            $this->editor = Grafika::createEditor();
        }
        catch(\Exception $e) {
            Exception::Throw($e);
        }
    }


    /**
     * Ouvre une image pour commencer a y effectuer des traitement
     * 
     * @param string $src Le chemin vers l'image a traiter
     * @return dF_Image
     */
    public function open(string $src) : self
    {
        $this->img_src= $this->imgPath($src);
        $this->editor->open($this->image, $this->img_src);
        
        return $this;
    }

    /**
     * Ferme une image et libere la memoire
     * 
     * @return dF_Image
     */
    public function close() : self 
    {
        $this->editor->free($this->image);

        return $this;
    }

    /**
     * Cree une image a partir d'une autre image ou une image  vide avec des dimensions precise
     * 
     * @param string|int[] Le chemin vers l'image a cloner ou les dimensions de la nouvelle image
     * @return dF_Image
     */
    public function create($img) : self
    {
        if(is_string($img))
        {
            $this->image = Grafika::createImage($this->imgPath($img));
        }
        else if(is_array($img))
        {
            if(count($img) != 2) 
            {
                Exception::show('Entrez les dimensions de l\'image a creer (X * Y). <br> Consultez le guide d\'utilisation pour plus d\'informations');
            }
            foreach($img As $value)
            {
                if(!is_int($value))
                {
                    Exception::show('Les dimensions de l\'image a creer doivent etre des entiers');
                }
            }
            $this->image = Grafika::createBlankImage($img[0], $img[1]);
        }
        else 
        {
            Exception::show('Invalid parameters for dF_Image::create() method. Please visit user guide for more informations');
        }

        return $this;
    }

    /**
     * Sauvegarde l'image a un format specifique
     * 
     * @param string $file Le nom de fichier de sauvegarde
     * @param string|null $type Le type de l'image a sauvegarder (png, gif, jpeg). Utiliser pour forcer la conversion de type
     * @param string|null $quality La qualite de l'iamge (appliquer uniquement aux JPEG)
     * @param bool $interlace
     * @param int $permission
     */
    public function save(string $file, ?string $type = null, ?string $quality = null, bool $interlace = false, int $permission = 0755 ) : self 
    {
        $this->editor->save($this->image, $this->imgPath($file), $type, $quality, $interlace, $permission);

        return $this;
    }

    /**
     * Rogne une image
     * 
     * @param int $width La largeur de la nouvelle image
     * @param int $height La hauteur de la nouvelle image
     * @param string $position La position a partir de laquele on veut effectuer  le rognage (point de rognage)
     * @param int $offsetX L'abscisse l'image rogee par rapport au point de rognage 
     * @param int $offsetY L'ordonnee l'image rogee par rapport au point de rognage 
     * @return dF_Image
     */
    public function crop(int $width, int $height, string $position = self::POSITION_CENTER, int $offsetX = 0, int $offsetY = 0) : self
    {
        $this->editor->crop($this->image, $width, $height, $position, $offsetX, $offsetY);

        return $this;
    }

    /**
     * Redimensionne une image
     * 
     * @param int|int[] $dimensions Les dimensions a donner a la nouvelle image
     * @param int $mode Le type de redimentionnement a appliquer
     * @return dF_Image
     */
    public function resize($dimensions, int $mode = self::RESIZE_FILL) : self
    {
        $dimensions = (array) $dimensions;
        $dimensions[1] = $dimensions[1] ?? $dimensions[0];
        
        if(count($dimensions) != 2) 
        {
            Exception::show('Vous devez reseignez 1 ou 2 entiers comme dimensions pour redimensionner l\'image');
        }
        foreach($dimensions As $value)
        {
            if(!is_int($value))
            {
                Exception::show('Les dimensions du redimensionnement doivent etre des entiers');
            }
        }
        $this->editor->resize($this->image, $dimensions[0], $dimensions[1], $mode);
        
        return $this;
    }

    /**
     * Retire les animations sur un GIF
     * 
     * @return dF_Image
     */
    public function flatten() : self
    {
        $this->editor->flatten($this->image);

        return $this;
    }

    /**
     * Compare deux images et renvoie le taux de difference entre elles
     * 
     * @param string $img_1 URI de la premiere image
     * @param string $img_2 URI de la deuxieme image
     * @return int
     */
    public function compare(string $img_1, ?string $img_2 = null) : int
    {
        if(empty($this->img_src) AND empty($img_2))
        {
            Exception::show('Second parameter is required for make the comparison');
        }
        $img_2 = (empty($img_2)) ? $this->img_src : $img_2;
        $img_1 = $this->imgPath($img_1);
        $img_2 = $this->imgPath($img_2);

        return $this->editor->compare($img_1, $img_2);
    }

    /**
     * Verifie si deux images sont egales ou pas
     * 
     * @param string $img_1 URI de la premiere image
     * @param string $img_2 URI de la deuxieme image
     * @return bool
     */
    public function equal(string $img_1, ?string $img_2 = null) : bool
    {
        if(empty($this->img_src) AND empty($img_2))
        {
            Exception::show('Second parameter is required for make the comparison');
        }
        $img_2 = (empty($img_2)) ? $this->img_src : $img_2;
        $img_1 = $this->imgPath($img_1);
        $img_2 = $this->imgPath($img_2);

        return $this->editor->equal($img_1, $img_2);
    }

    /**
     * Applique un filtre a une image
     * 
     * @param int $filter Le type de filtre a appliquer
     * @param mixed $params La liste des parametres potentiels a joindre au filtre
     * @return dF_Image
     */
    public function filter(int $filter, $params = null) : self
    {
        $params = func_get_args();
        $filter = array_shift($params);
        
        $between100_filters = [
            self::FILTER_BLUR => 'Blur',
            self::FILTER_SHARPEN => 'Sharpen',
        ];
        $simple_filters = [
            self::FILTER_BRIGHTNESS => 'Brightness',
            self::FILTER_CONTRAST => 'Contrast',
        ];
        $noparams_filters = [
            self::FILTER_GRAYSCALE => 'Grayscale',
            self::FILTER_INVERT => 'Invert',
            self::FILTER_SOBEL => 'Sobel',
        ];

        if(array_key_exists($filter, $between100_filters))
        {
            if(empty($params[0]) OR !is_int($params[0]) OR $params[0] < 1 OR $params[0] > 100)
            {
                Exception::show('The "'.$between100_filters[$filter].'" filter require an integer parameter between 1 and 100');
            }
            $this->editor->apply($this->image, Grafika::createFilter($between100_filters[$filter], $params[0]));
        }
        else if(array_key_exists($filter, $simple_filters))
        {
            if(empty($params[0]) OR !is_int($params[0]))
            {
                Exception::show('The "'.$simple_filters[$filter].'" filter require an integer parameter');
            }
            $this->editor->apply($this->image, Grafika::createFilter($simple_filters[$filter], $params[0]));
        }
        else if(array_key_exists($filter, $noparams_filters))
        {
            $this->editor->apply($this->image, Grafika::createFilter($noparams_filters[$filter]));
        }
        else 
        {
            switch($filter)
            {
                case self::FILTER_COLORIZE :
                    {
                        if(count($params) != 3)
                        {
                            Exception::show('The "Colorize" filter require 3 parameters');
                        }
                        if(!is_int($params[0]) OR !is_int($params[1]) OR !is_int($params[2]))
                        {
                            Exception::show('The "Colorize" filter require integer parameter\s');
                        }
                        $this->editor->apply($this->image, Grafika::createFilter('Colorize', $params[0], $params[1], $params[2]));
                    }
                    break;
                case self::FILTER_DITHER :
                    {
                        if(empty($params[0]) OR !in_array(strtolower($params[0]), ['diffusion', 'ordered']))
                        {
                            Exception::show('The "Dither" filter require a parameter between \'diffusion\' and \'ordered\'');
                        }
                        $this->editor->apply($this->image, Grafika::createFilter('Dither', strtolower($params[0])));
                    }
                    break;
                case self::FILTER_GAMMA : 
                    {
                        if(empty($params[0]) OR !is_float($params[0]) OR $params[0] < 1.0)
                        {
                            Exception::show('The "Gamma" filter require a float parameter greatter than or equal to 1.0');
                        }
                        $this->editor->apply($this->image, Grafika::createFilter('Gamma', $params[0]));
                    }
                    break;
                case self::FILTER_PIXELATE : 
                    {
                        if(empty($params[0]) OR !is_int($params[0]) OR $params[0] < 1)
                        {
                            Exception::show('The "Pixelate" filter require an integer parameter greatter than 1');
                        }
                        $this->editor->apply($this->image, Grafika::createFilter('Pixelate', $params[0]));
                    }
                    break;
                default: 
                    {
                        Exception::show('The filter "'.$filter.'" is not supported. Read the user guide for more informations');
                    }
                break;
            }  
        }

        return $this;
    }

    /**
     * Ajoute une image en filligrance e
     */
    public function blend(string $img_src, string $type = self::BLEND_NORMAL, float $opacity = 1, string $position = self::POSITION_TOPLEFT, int $offsetX = 0, int $offsetY = 0 ): self
    {
        $image2 = Grafika::createImage($this->imgPath($img_src));
        $this->editor->blend($this->image, $image2, $type, $opacity, $position, $offsetX, $offsetY);

        return $this;
    }

    /**
     * Retourne une image verticalement ou horizontalement
     * 
     * @param string $mode Le sens de retournement
     * @return dF_Image
     */
    public function flip(string $mode) : self
    {
        $this->editor->flip($this->image, $mode);

        return $this;
    }

    /**
     * Rote  l'image d'un angle donné
     * 
     * @param int $angle L'angle de rotation en degre
     * @return dF_Image
     */
    public function rotate(int $angle) : self
    {
        $this->editor->rotate($this->image, $angle);

        return $this;
    }

    /**
     * Change l'opacite d'une image
     * 
     * @param float $opacity L'opacite a affecter a l'image
     * @return dF_Image
     */
    public function opacity(float $opacity) : self
    {
        if($opacity < 0.0 OR $opacity > 1.0)
        {
            Exception::show('The opacity of image has a float between 0.0 and 1.0');
        }
        $this->editor->opacity($this->image, $opacity);

        return $this;
    }

    /**
     * Ecrit un texte sur une image
     * 
     * @param string $text Le texte a ecrire
     * @param int $size La taille de l'ecriture
     * @param int $x L'abscisse du texte par rapport au coin haut de gauche (top-left)
     * @param int $y L'ordonnee du texte par rapport au coin haut de gauche (top-left)
     * @param string|null $color La couleur du texte en hexadecimal
     * @param string|null $font Le chemin absolue vers la police a utiliser
     * @param int $angle L'angle de rotation du texte ecrit
     * @return dF_Image
     */
    public function write(string $text, int $size = 12, int $x = 0, int $y = 12, ?string $color = null, string $font = '', int $angle = 0) : self
    {
        $color = ($color[0] != '#') ? '#'.$color : $color;
         $this->editor->text($this->image, $text, $size, $x, $y, new Color($color), $font, $angle);

        return $this;
    }


    /**
     * Renvoie la ressource binaire d'une image pour l'afficher dans le navigateur
     * 
     * @param string|null $type Le type de sortie de l'image (png,jpeg, gif)
     * @param string|null $img_src Le chemin vers l'image
     */
    public function iShow(string $type = null, string $img_src = null)
    {
        if(empty($img_src) AND empty($this->image))
        {
            Exception::show('The image file is require for run dF_Image::iShow() method');
        }
        $image = (empty($img_src)) ? $this->image : Grafika::createImage($this->imgPath($img_src));

        $type = (empty($type) OR !in_array(strtolower($type), ['png', 'jpeg', 'gif'])) ? 'png' : strtolower($type);
        header('Content-type: image/'.$type);

        $image->blob(strtoupper($type));
    }

    /**
     * Renvoie la hauteur d'une image
     * 
     * @param string|null $img_src Le chemin vers l'image
     * @return int
     */
    public function iHeight(string $img_src = null) : int
    {
        if(empty($img_src) AND empty($this->image))
        {
            Exception::show('The image file is require for run dF_Image::iHeight() method');
        }
        $image = (empty($img_src)) ? $this->image : Grafika::createImage($this->imgPath($img_src));

        return $image->getHeight();
    }
    
    /**
     * Renvoie a largeur d'une image
     * 
     * @param string|null $img_src Le chemin vers l'image
     * @return int
     */
    public function iWidth(string $img_src = null) : int
    {
        if(empty($img_src) AND empty($this->image))
        {
            Exception::show('The image file is require for run dF_Image::iWidth() method');
        }
        $image = (empty($img_src)) ? $this->image : Grafika::createImage($this->imgPath($img_src));

        return $image->getWidth();
    }
    
    /**
     * Renvoie le type d'une image
     * 
     * @param string|null $img_src Le chemin vers l'image
     * @return string
     */
    public function iType(string $img_src = null) : string
    {
        if(empty($img_src) AND empty($this->image))
        {
            Exception::show('The image file is require for run dF_Image::iType() method');
        }
        $image = (empty($img_src)) ? $this->image : Grafika::createImage($this->imgPath($img_src));

        return $image->getType();
    }
    
    /**
     * Verifie si une image est animee ou pas
     * 
     * @param string|null $img_src Le chemin vers l'image
     * @return bool
     */
    public function isAnimated(string $img_src = null) : bool
    {
        if(empty($img_src) AND empty($this->image))
        {
            Exception::show('The image file is require for run dF_Image::isAnimated() method');
        }
        $image = (empty($img_src)) ? $this->image : Grafika::createImage($this->imgPath($img_src));

        return $image->isAnimated();
    }
    
    

    private function imgPath(string $src) : string
    {
        if($src[0] === '-') 
        {
            $src = substr($src, 1, strlen($src));
            
            return $src;
        }
        if($src[0] === '/')
        {
            $src = substr($src, 1, strlen($src));
            $src = str_replace(WEBROOT, '', $src);
            
            return WEBROOT.$src;
        }
        $src = str_replace(WEBROOT.'img', '', $src);
            
        return WEBROOT.'img'.DS.$src;
    }
}