<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019, Dimtrov Sarl
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 *  @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @link	    https://dimtrov.hebfree.org/works/dframework
 *  @version    2.1
 *
 */

/**
 * View
 *
 * Responsible for sending final output to the browser.
 *
 * @class       View
 * @package		dFramework
 * @subpackage	Core
 * @category    Output
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/class_output_view.html
 * @file		/system/core/output/View.php
 */

namespace dFramework\core\output;

use \dFramework\core\Config;


class View
{
    private $view;
    /**
     * @var array
     */
    private $vars = [];

    private $content = '';
    /**
     * @var string
     */
    private $controller = '';

    /**
     * View constructor.
     * @param $view
     * @param array $vars
     * @param string $controller
     */
    public function __construct($view, array $vars = [], string $controller = '')
    {
        $this->view = preg_replace('#\.php$#i', '', $view);
        $this->vars = $vars;
        $this->controller = $controller;
        $this->create();
    }

    /**
     * show the view
     */
    public function render()
    {
        echo $this->get(Config::get('general.environment') !== 'dev');
    }

    /**
     * @param bool $compress
     * @return string
     */
    public function get($compress = true)
    {
        return ($compress) ? trim(preg_replace('/\s+/', ' ', $this->content)) : $this->content;
    }


    /**
     * Make a view
     */
    private function create()
    {
        if(stripos($this->view, '/') === 0)
        {
            $view = VIEW_DIR.str_replace(' ', '', trim($this->view, '/')).'.php';
        }
        else
        {
            $view = rtrim(VIEW_DIR.$this->controller.DS, DS).DS.str_replace(' ', '', $this->view).'.php';
        }
        $view = str_replace('/', DS, $view);

        if(!file_exists($view) OR !is_readable($view))
        {
    //            Exception::viewNotFound($view, $e);
        }
        ob_start();
        extract($this->vars, EXTR_PREFIX_ALL, 'df');

        require_once $view;
        $content = ob_get_clean();
        $content = (Config::get('general.compress_output') === true) ? trim(preg_replace('/\s+/', ' ', $content)) : $content;

        $this->content = $content;
    }


}