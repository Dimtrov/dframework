<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019 - 2021, Dimtrov Lab's
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 *  @package	dFramework
 *  @author	    Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *  @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 *  @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @link	    https://dimtrov.hebfree.org/works/dframework
 *  @version    3.4.0
 */

namespace dFramework\core\output;

require_once SYST_DIR.'dependencies'.DS.'smarty'.DS.'SmartyBC.class.php';

/**
 * Smarty
 *
 * Smarty adapter for view rendering
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Output
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.3.0
 * @file		/system/core/output/Smarty.php
 */
class Smarty extends \SmartyBC
{
	/**
	 * @var string|null La mise en page à utiliser par la page
	 */
	protected $layout = null;

	/**
	 * constructor
	 */
    public function __construct()
    {
        parent::__construct();

		$this->setTemplateDir([
            VIEW_DIR,
            'partials' => VIEW_DIR . 'partials',
            'layouts'  => LAYOUT_DIR
        ]);
		$this->addPluginsDir([
			SYST_DIR . 'helpers',
			APP_DIR . 'helpers',
		]);
        $this->compile_dir  = SMARTY_COMPILES_DIR;
        $this->cache_dir    = SMARTY_CACHE_DIR;
        $this->config_dir   = SMARTY_CONF_DIR;

        $this->caching = self::CACHING_OFF;
        $this->compile_check = on_dev();
    }

	/**
	 * Definit le layout a utiliser par les vues
	 *
	 * @param string|null $layout
	 * @return self
	 */
	public function setLayout(?string $layout) : self
	{
		$this->layout = $layout;

		return $this;
	}

	/**
	 * Active la mise en cache des pages
	 *
	 * @return self
	 */
	public function enableCache() : self
	{
		$this->setCaching(self::CACHING_LIFETIME_SAVED);

		return $this;
	}

	/**
	 * Rend une vue en associant le layout si celui ci est defini
	 *
	 * @param string|null $template
	 * @param mixed $cache_id
	 * @param mixed $compile_id
	 * @param mixed $parent
	 * @return string
	 */
	public function render(string $template = null, $cache_id = null, $compile_id = null, $parent = null) : string
	{
		$layout = $this->layout;

		if (!empty($layout))
		{
			if (empty(pathinfo($layout, PATHINFO_EXTENSION)))
			{
				$layout .= '.tpl';
			}
			$template = 'extends:[layouts]'.$layout.'|'.$template;
		}

		return $this->fetch($template, $cache_id, $compile_id, $parent);
	}
}
