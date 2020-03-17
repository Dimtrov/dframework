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

/**
 * Captcha
 *
 *
 * @package		dFramework
 * @subpackage	Library
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/works/dframework/docs/systemlibrary/captcha
 * @since       2.0
 */


use dFramework\core\{
    Config,
    exception\Exception,
    Helpers
};


class dF_Captcha
{
    /**
     * Captcha textuel.
     */
    const TEXT = 1;
    /**
     * Captcha mathematique simple. Ex: 12 * 14 = ?
     */
    const MATH = 2;
    /**
     * Captcha natif dFramework. Utilise SimplePhpCaptcha
     */
    const IMAGE_NATIVE = 3;
    /**
     * Captcha image securimage
     */
    const IMAGE_SECURIMAGE = 4;
    /**
     * Captcha audio securimage
     */
    const AUDIO_SECURIMAGE = 5;


    /**
     * @var int Le type de captcha a utiliser
     */
    private $type = self::IMAGE_NATIVE;
    /**
     * @var array Parametres par defaut des differents types de captcha
     */
    private $params = [
        self::TEXT => [

        ],
        self::MATH => [
            'min' => 0,
            'max' => 99,
            'operation' => '+,-,x'
        ],
        self::IMAGE_NATIVE => [

        ],
        self::IMAGE_SECURIMAGE => [
            'session_name' => 'dframework',
            'image_signature' => 'dFramework',
            'signature_color' => '#76b4ee',
            'case_sensitive'  => true,
            'image_bg_color'  =>'#f6f6f6',
            'use_transparent_text' => true,
            'text_transparency_percentage' => 25,
            'line_color' => '#eaeaea',
        ],
        self::AUDIO_SECURIMAGE => [
            'session_name' => 'dframework',
         ],
    ];


    public function __construct()
    {
        Config::set('general.url_suffix', '');
    }


    /**
     * Specifie le type de captcha a utiliser
     *
     * @param int $type
     */
    public function use(int $type)
    {
        $this->type = $type;
    }

    /**
     * Initialise les parametres du captcha
     *
     * @param array|string $config
     * @param mixed|null $value
     * @throws \Exception
     */
    public function set($config, $value = null)
    {
        if(is_string($config))
        {
            if(empty($value))
            {
                Exception::show('Vous devez attribuer une valeur pour modifier la clé <b>'.$config.'</b>');
            }
            $this->checkConfig($config, $value);
        }
        else if(is_array($config))
        {
            foreach ($config As $key => $value)
            {
                $this->checkConfig($key, $value);
            }
        }
    }


    /**
     * Recupere le captcha
     *
     * @return null|string
     * @throws Exception
     */
    public function get() : ?string
    {
        switch ($this->type)
        {
            case self::MATH : 
                return $this->captchaMath();
            case self::IMAGE_NATIVE: 
                return $this->captchaImageNative();
            case self::IMAGE_SECURIMAGE: 
                return $this->captchaImageSecurimage();
            case self::AUDIO_SECURIMAGE: 
                return $this->captchaAudioSecurimage();
            default: 
                Exception::show('Unknow type of captcha');
        }
    }

    /**
     * @param string $value
     * @return bool
     */
    public function check($value) : bool
    {
        if(!in_array($this->type, [self::IMAGE_SECURIMAGE, self::AUDIO_SECURIMAGE]))
        {
            if(empty($_SESSION['df_security']['captcha']['code']))
            {
                return false;
            }
            else
            {
                $code = unserialize($_SESSION['df_security']['captcha']['code']);
                return ($code === hash('sha512', $value));
            }
        }
        else
        {
            $securimage = new Securimage($this->params[self::IMAGE_SECURIMAGE]);

            return (!($securimage->check($value) == false));
        }
    }


    /**
     * @param $config
     * @param $value
     */
    private function checkConfig($config, $value)
    {
        $config = strtolower($config);

        if($this->type === self::MATH)
        {
            if(!is_int($value) AND in_array($config, ['min', 'max']))
            {
                Exception::show('The parameter "<b>'.$config.'</b>" must be an integer for "Captcha Math"');
            }
            if($config == 'operation')
            {
                if(!is_string($value))
                {
                    Exception::show('The parameter "<b>'.$config.'</b>" must be a string for "Captcha Math"');
                }
                $value = explode(',', $value); $accepts = ['+','-','x','*','/'];
                foreach ($value As $item)
                {
                    if(!in_array($item, $accepts))
                    {
                        Exception::show('Le parametre "'.$config.'" ne peut prendre que les valeur "<b>'.join(',', $accepts).'</b>" pour  "Captcha Math"');
                    }
                }
                $value = join(',', $value);
            }
        }

        if($this->type === self::IMAGE_NATIVE)
        {
            if((!is_string($value) OR !file_exists($value)) AND in_array($config, ['bg_path', 'font_path']))
            {
                Exception::show('Le parametre "<b>'.$config.'</b>" doit etre un chemin vers un dossier existant');
            }
            if(!is_int($value) AND in_array($config, ['min_length', 'max_length', 'min_font_size', 'max_font_size', 'angle_min', 'angle_max', 'shadow_offset_x', 'shadow_offset_y']))
            {
                Exception::show('The parameter "<b>'.$config.'</b>" must be an integer for "Native Captcha Image"');
            }
            if(in_array($config, ['backgrounds', 'fonds']))
            {
                if(!is_array($value))
                {
                    Exception::show('Le parametre "<b>'.$config.'</b>" ne peut prendre q\'un tableau de caracteres pour "Native Captcha Image"');
                }
                foreach ($value As $item)
                {
                    if(empty($item) OR !is_string($item))
                    {
                        Exception::show('Le parametre "<b>'.$config.'</b>" ne peut prendre q\'un tableau de caracteres pour "Native Captcha Image"');
                    }
                }
            }
            if($config == 'characters' AND !is_string($value))
            {
                Exception::show('The parameter "<b>'.$config.'</b>" must be a string for "Native Captcha Image"');
            }
            if((!is_string($value) OR !preg_match('#^\#([a-z0-9]{3,6})$#i', $value)) AND in_array($config,['color', 'shadow_color']))
            {
                Exception::show('The parameter "<b>'.$config.'</b>" must be a valid HTML color for "Native Captcha Image"');
            }
            if($config == 'shadow' AND !is_bool($value))
            {
                Exception::show('The parameter "<b>'.$config.'</b>" must be a boolean for "Native Captcha Image"');
            }
        }

        if($this->type === self::IMAGE_SECURIMAGE OR $this->type === self::AUDIO_SECURIMAGE)
        {
            if(!is_int($value) AND in_array($config, ['image_width', 'image_height', 'text_transparency_percentage', 'code_length', 'num_lines', 'noise_level']))
            {
                Exception::show('The parameter "<b>'.$config.'</b>" must be an integer for "Securimage Captcha Image"');
            }
            if($config == 'image_type' AND (!is_int($value) OR !in_array($value, [1, 2, 3])))
            {
                Exception::show('The parameter "<b>'.$config.'</b>" must be an integer between 1, 2, 3 for "Securimage Captcha Image"');
            }
            if($config == 'captcha_type' AND (!is_int($value) OR !in_array($value, [0, 1])))
            {
                Exception::show('The parameter "<b>'.$config.'</b>" must be an integer between 0, 1 for "Securimage Captcha Image"');
            }
            if(in_array($config, ['image_bg_color', 'text_color', 'line_color', 'noise_color', 'signature_color']) AND (!is_string($value) OR !preg_match('#^\#([a-z0-9]{3,6})$#i', $value)))
            {
                Exception::show('The parameter "<b>'.$config.'</b>" must be a valid HTML color for "Securimage Captcha Image"');
            }
            if(!is_bool($value) AND in_array($config, ['use_transparent_text', 'case_sensitive', 'use_wordlist', 'use_sqlite_db']))
            {
                Exception::show('The parameter "<b>'.$config.'</b>" must be a boolean for "Securimage Captcha Image"');
            }
            if(!is_string($value) AND in_array($config, ['charset', 'session_name', 'image_signature', 'signature_font', 'namespace']))
            {
                Exception::show('The parameter "<b>'.$config.'</b>" must be a string for "Securimage Captcha Image"');
            }
            if(!is_double($value) AND in_array($config, ['perturbation']))
            {
                Exception::show('The parameter "<b>'.$config.'</b>" must be a string for "Securimage Captcha Image"');
            }
            if((!is_string($value) OR !file_exists($value)) AND in_array($config, ['ttf_file', 'wordlist_file', 'background_directory', 'sqlite_database', 'audio_path']))
            {
                if(in_array($config, ['ttf_file', 'wordlist_file']))
                {
                    Exception::show('Le parametre "<b>'.$config.'</b>" doit etre un chemin vers un fichier existant');
                }
                Exception::show('Le parametre "<b>'.$config.'</b>" doit etre un chemin vers un dossier existant');
            }
        }

        $this->params[$this->type] = array_merge($this->params[$this->type], [$config => $value]);
    }


    /**
     * @return string
     */
    private function captchaMath() : string
    {
        unset($_SESSION['df_captcha']['config']);

        $params = $this->params[self::MATH];

        $n1 = mt_rand($params['min'], $params['max']);
        $n2 = mt_rand($params['min'], $params['max']);
        $operation = explode(',', $params['operation']);

        $resultat = 0;
        $phrase = '';

        $rand = $operation[array_rand($operation)];
        switch ($rand)
        {
            case '+' : {
                $resultat = $n1 + $n2;
                $phrase = $n1.' + '.$n2;
            } break;
            case '-' : {
                if($n1 < $n2) {
                    $resultat = $n2 - $n1;
                    $phrase = $n2.' - '.$n1;
                }
                else {
                    $resultat = $n1 - $n2;
                    $phrase = $n1.' - '.$n2;
                }
            } break;
            case '*' :
            case 'x' : {
                $resultat = $n2 * $n1;
                $phrase = $n2.' x '.$n1;
            } break;
            case '/' : {
                $n1 = ($n1 == 0) ? rand(1, $params['max']) : $n1;
                $n2 = ($n2 == 0) ? rand(1, $params['max']) : $n2;

                if($n1 < $n2)
                {
                    $resultat = $n2 / $n1;
                    $phrase = $n2.' / '.$n1;
                }
                else
                {
                    $resultat = $n1 / $n2;
                    $phrase = $n1.' / '.$n2;
                }
            } break;
        }

        $_SESSION['df_captcha']['code'] = serialize(hash('sha512', $resultat));
        return $phrase;
    }

    /**
     * @return string
     * @throws Exception
     */
    private function captchaImageNative() : string
    {
        unset($_SESSION['df_security']['captcha']['config']);
        $captcha = new SimplePhpCaptcha();
        $captcha->config($this->params[self::IMAGE_NATIVE]);
        $image_src = $captcha->generate();
        unset($captcha);
        return $image_src;
    }

    /**
     * @return string
     */
    private function captchaImageSecurimage() : string
    {
        unset($_SESSION['df_security']['captcha']['config']);
        $_SESSION['df_security']['captcha']['config'] = serialize($this->params[$this->type]);

        $dir = explode(DIRECTORY_SEPARATOR, dirname(__DIR__));
        $dir = end($dir);

        return Helpers::instance()->site_url($dir.'/dependencies/securimage/show.php?df_captcha&amp;sid='.urlencode(microtime()));
    }

    /**
     * @return string
     */
    private function captchaAudioSecurimage() : string
    {
        unset($_SESSION['df_security']['captcha']['config']);
        $_SESSION['df_security']['captcha']['config'] = serialize($this->params[$this->type]);


        $dir = explode(DIRECTORY_SEPARATOR, dirname(__DIR__));
        $dir = end($dir);

        return Helpers::instance()->site_url($dir.'/dependencies/securimage/play.php?df_captcha&amp;sid='.urlencode(microtime()));
    }

}