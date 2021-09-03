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
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.4.0
 */

namespace dFramework\core\controllers;

use dFramework\core\output\View;
use dFramework\core\router\Dispatcher;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;

/**
 * ApplicationController
 *
 * A foundation controller for MVC application
 *
 * @package		dFramework
 * @subpackage	Core
 * @category	Controllers
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/Controller.html
 * @since       3.4.0
 * @file		/system/core/controllers/ApplicationController.php
 */
class ApplicationController extends BaseController
{
    /**
     * @var array Données partagées entre toutes les vue chargées à partir d'un controleur
     */
    protected $view_datas = [];
    /**
     * @var string Layout a utiliser
     */
    protected $layout = null;

    /**
     * Charge une vue
     *
     * @param string $view
     * @param array|null $data
     * @param array|null $options
     * @param array|null $config
     * @return View
     * @throws \ReflectionException
     */
    final protected function view(string $view, ?array $data = [], ?array $options = [], ?array $config = []) : View
    {
        $reflection = new ReflectionClass(get_called_class());
        $path = str_replace([CONTROLLER_DIR, 'Controller', '.php'], '', $reflection->getFileName());

        $object = new View($data, $path, $options, $config, $this->response);
        if (!empty($this->layout) AND is_string($this->layout))
        {
            $object->layout($this->layout);
        }

        if (!empty($this->view_datas) AND is_array($this->view_datas))
        {
            $object->addData($this->view_datas);
        }

        return $object->display($view);
    }

    /**
     * Charge et rend directement une vue
     *
     * @param string|null $view
     * @param array|null $data
     * @param array|null $options
     * @return ResponseInterface
     */
    final protected function render(?string $view = null, ?array $data = [], ?array $options = [], ?array $config = []) : ResponseInterface
    {
        if (empty($view))
        {
            $view = Dispatcher::getMethod();
        }
        $view = $this->view($view, $data, $options, $config)->get(config('general.compress_output'));

        return $this->response->withBody(to_stream($view));
    }

    /**
     * Defini des donnees à distribuer à toutes les vues
     *
     * @param string|array $key
     * @param [type] $value
     * @return self
     */
    final protected function addData($key, $value = null) : self
    {
        if (is_string($key) OR is_array($key))
        {
            $data = $key;
            if (!is_array($key))
            {
                $data = [$key => $value];
            }
            $this->view_datas = array_merge($this->view_datas, $data);
        }
        return $this;
    }
}
