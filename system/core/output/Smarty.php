<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019 - 2021, Dimtrov Lab's
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 *  @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @link	    https://dimtrov.hebfree.org/works/dframework
 *  @version    3.3.0
 */
 
namespace dFramework\core\output;

require_once SYST_DIR.'dependencies'.DS.'smarty'.DS.'Smarty.class.php';

use \Smarty As BaseSmarty;

/**
 * Smarty
 *
 * Smarty adapter for view rendering
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Output
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.3.0
 * @file		/system/core/output/Smarty.php
 */
class Smarty extends BaseSmarty
{
    public function __construct()
    {
        parent::__construct();


        $this->template_dir = VIEW_DIR;
        $this->compile_dir  = VIEW_DIR.'reserved'.DS.'compiles'.DS;
        $this->cache_dir    = VIEW_DIR.'reserved'.DS.'cache'.DS;
        $this->config_dir   = VIEW_DIR.'reserved'.DS.'conf'.DS;

        $this->caching = true;
        $this->compile_check = true;
    }
}
