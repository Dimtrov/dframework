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
 * @version    2.1
 */

//
//  A simple PHP CAPTCHA script
//
//  Copyright 2011 by Cory LaViska for A Beautiful Site, LLC
//
//  See readme.md for usage, demo, and licensing info
//


use dFramework\core\exception\Exception;
use dFramework\core\Helpers;

class SimplePhpCaptcha
{
    /**
     * @var array  Defaults configurations
     */
    private $config = [
        'bg_path' => __DIR__ . DS . 'backgrounds' .DS,
        'font_path' => __DIR__ . DS . 'fonts' . DS,
        'min_length' => 5,
        'max_length' => 5,
        'backgrounds' => [
            '45-degree-fabric.png',
            'cloth-alike.png',
            'grey-sandbag.png',
            'kinda-jean.png',
            'polyester-lite.png',
            'stitched-wool.png',
            'white-carbon.png',
            'white-wave.png'
        ],
        'fonts' => [
            'times_new_yorker.ttf'
        ],
        'characters' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',
        'min_font_size' => 28,
        'max_font_size' => 28,
        'color' => '#666',
        'angle_min' => 0,
        'angle_max' => 10,
        'shadow' => true,
        'shadow_color' => '#fff',
        'shadow_offset_x' => -1,
        'shadow_offset_y' => 1
    ];



    public function __construct()
    {
        if( !function_exists('gd_info') ) {
            throw new Exception('Required GD library is missing');
        }
    }


    public function config(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * @return mixed
     */
    public function generate()
    {
        $captcha_config = $this->config;

        // Restrict certain values
        if( $captcha_config['min_length'] > $captcha_config['max_length'])
        {
            $tmp = $captcha_config['min_length'];
            $captcha_config['min_length'] = $captcha_config['max_length'];
            $captcha_config['max_length'] = $tmp;
        }
        if( $captcha_config['min_length'] < 1 ) $captcha_config['min_length'] = 1;
        if( $captcha_config['angle_min'] < 0 ) $captcha_config['angle_min'] = 0;
        if( $captcha_config['angle_max'] > 10 ) $captcha_config['angle_max'] = 10;
        if( $captcha_config['angle_max'] < $captcha_config['angle_min'] ) $captcha_config['angle_max'] = $captcha_config['angle_min'];
        if( $captcha_config['min_font_size'] < 10 ) $captcha_config['min_font_size'] = 10;
        if( $captcha_config['max_font_size'] < $captcha_config['min_font_size'] ) $captcha_config['max_font_size'] = $captcha_config['min_font_size'];

        // Generate CAPTCHA code
        $captcha_code = '';
        $length = mt_rand($captcha_config['min_length'], $captcha_config['max_length']);
        while( strlen($captcha_code) < $length )
        {
            $captcha_code .= substr($captcha_config['characters'], mt_rand() % (strlen($captcha_config['characters'])), 1);
        }

        $dir = explode(DIRECTORY_SEPARATOR, dirname(__DIR__, 3));
        $dir = end($dir);

        $image_src = Helpers::instance()->site_url($dir.'/dependencies/others/simplephpcaptcha/show.php?df_captcha&amp;sid='.urlencode(microtime()));

        $captcha_config['code'] = base64_encode(md5(uniqid()).$captcha_code);

        $_SESSION['df_security']['captcha'] = [
            'code' => serialize(hash('sha512', $captcha_code)),
            'config' => serialize($captcha_config)
        ];

        return $image_src;

    }
}
