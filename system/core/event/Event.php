<?php 
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019 - 2021, Dimtrov Lab's
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.2.3
 */

namespace dFramework\core\event;

use Psr\EventManager\EventInterface;

/**
 * Event
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Events
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.2.3
 * @credit      https://www.phpclasses.org/package/9961-PHP-Manage-events-implementing-PSR-14-interface.html - Kiril Savchev <k.savchev@gmail.com>
 * @file        /system/core/events/Event.php
 */
class Event implements EventInterface
{
    /**
     * The event name
     *
     * @var string
     */
    protected $name = '';

    /**
     * The event target
     *
     * @var mixed
     */
    protected $target;

    /**
     * The event parameters
     *
     * @var array
     */
    protected $params = [];

    /**
     * Flag that show whether the event must be stopped while triggering
     *
     * @var bool
     */
    protected $isPropagationStopped = false;

    /**
     * Create event object
     *
     * @param string $name [Optional] Event name
     * @param mixed $target [Optional] Event target
     * @param array $params [Optional] Event parameters
     */
    public function __construct(?string $name = '', $target = null, array $params = [])
    {
        $this->name = $name;
        $this->target = $target;
        $this->params = $params;
    }

    /**
     * Gets the event name
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
    /**
     * Sets the event name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Gets all parameters
     *
     * @return array
     */
    public function getParams() : array
    {
        return $this->params;
    }
    /**
     * Gets single parameter
     *
     * @param string $name
     * @return mixed
     */
    public function getParam($name)
    {
        return (array_key_exists($name, $this->params)) ? $this->params[$name] : null;
    }
    /**
     * Sets the event parameters
     *
     * @param array $params
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }
    
    /**
     * Gets event target
     *
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }
    /**
     * Sets the event target
     *
     * @param mixed $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * Checks whetner the event is stopped
     *
     * @return bool
     */
    public function isPropagationStopped() : bool
    {
        return $this->isPropagationStopped;
    }
    /**
     * Stops or resumes the event triggering
     *
     * @param bool $flag
     */
    public function stopPropagation($flag)
    {
        $this->isPropagationStopped = (bool) $flag;
    }
}
