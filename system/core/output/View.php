<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019, Dimtrov Sarl
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 *  @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @link	    https://dimtrov.hebfree.org/works/dframework
 *  @version    3.0
 */

 
namespace dFramework\core\output;

use \dFramework\core\Config;

/**
 * View
 *
 * Responsible for sending final output to the browser.
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Output
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       1.0
 * @file		/system/core/output/View.php
 */

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
        $this->view = preg_replace('#\.(php|tpl|html)$#i', '', $view);
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
        $content = '';

        if(stripos($this->view, '/') === 0)
        {
            $view = VIEW_DIR.str_replace(' ', '', trim($this->view, '/'));
        }
        else
        {
            $view = rtrim(VIEW_DIR.$this->controller.DS, DS).DS.str_replace(' ', '', $this->view);
        }
        $view = str_replace('/', DS, $view);

        if(true === Config::get('general.use_template_engine'))
        {
            require_once SYST_DIR.'dependencies'.DS.'smarty'.DS.'Smarty.class.php';
            
            $smarty = new \Smarty();
            $smarty->template_dir = VIEW_DIR;
            $smarty->compile_dir  = VIEW_DIR.'reserved'.DS.'compiles'.DS;
            $smarty->cache_dir    = VIEW_DIR.'reserved'.DS.'cache'.DS;
            $smarty->config_dir   = VIEW_DIR.'reserved'.DS.'conf'.DS;

            $smarty->caching = true;
            $smarty->compile_check = true;
       
            $smarty->assign($this->vars);
            $smarty->display(str_replace(VIEW_DIR, '', $view).'.tpl');
        }
        else 
        {
            $view .= '.php';
            if(!file_exists($view) OR !is_readable($view))
            {
        //            Exception::viewNotFound($view, $e);
            }
            ob_start();
            extract($this->vars);

            require_once $view;
            $content = ob_get_clean();
            $content = (Config::get('general.compress_output') === true) ? trim(preg_replace('/\s+/', ' ', $content)) : $content;
        }

        $this->content = $content;
    }


}