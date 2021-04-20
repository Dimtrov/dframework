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
use Psr\EventManager\EventManagerInterface;

/**
 * EventManager
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Events
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.2.3
 * @credit      https://www.phpclasses.org/package/9961-PHP-Manage-events-implementing-PSR-14-interface.html - Kiril Savchev <k.savchev@gmail.com>
 * @file        /system/core/events/EventManager.php
 */
class EventManager implements EventManagerInterface
{
    /**
     *
     * @var array
     */
    protected $events;

    /**
     * @var self
     */
    private static $_instance = null;

    /**
	 * Stores information about the events
	 * for display in the debug toolbar.
	 *
	 * @var array
	 */
	protected static $performanceLog = [];

    /**
     * The wildcard event name
     */
    const WILDCARD = '*';

    /**
     * Create event manager object
     *
     * @param array $events [Optional]
     */
    public function __construct(array $events = [])
    {
        $this->events = $events;
        if (!array_key_exists(self::WILDCARD, $this->events)) 
        {
            $this->events[self::WILDCARD] = [];
        }
    }
    public static function instance() : self
    {
        if (self::$_instance === null) 
        {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * Clear all listeners for event
     *
     * @param string $event
     */
    public function clearListeners($event)
    {
        $this->events[$event] = [];
    }
     
    /**
     * Attach a listener to event
     *
     * @param string $event
     * @param callable $callback
     * @param int $priority [Optional]
     * @return bool
     */
    public function attach($event, $callback, $priority = 0)
    {
        if (!array_key_exists($event, $this->events)) 
        {
            $this->events[$event] = [];
        }
        if (!array_key_exists($priority, $this->events[$event])) 
        {
            $this->events[$event][$priority] = [];
        }
        if (!in_array($callback, $this->events[$event][$priority])) 
        {
            $this->events[$event][$priority][] = $callback;
            return true;
        }
        return false;
    }
    /**
     * Alias of attach method
     *
     * @param string $event
     * @param callable $callback
     * @param integer $priority
     * @return boolean
     */
    public function on(string $event, callable $callback, int $priority = 0) : bool
    {
        return $this->attach($event, $callback, $priority);
    }

    /**
     * Remove a listener for event
     *
     * @param string $event
     * @param callable $callback
     * @return boolean
     */
    public function detach($event, $callback)
    {
        if (!array_key_exists($event, $this->events) OR !$this->events[$event]) 
        {
            return false;
        }
        $eventsAgregation = $this->events[$event];
        
        foreach ($eventsAgregation as $priority => $events) 
        {
            if (is_array($events) AND in_array($callback, $events)) 
            {
                $key = array_search($callback, $events);
                unset($this->events[$event][$priority][$key]);
            }
        }

        return true;
    }

    /**
     * Fires an event
     *
     * @param string|EventInterface $event
     * @param object|string $target [Optional]
     * @param array|object $argv [Optional]
     * @return mixed
     */
    public function trigger($event, $target = null, $argv = [])
    {
        if (!($event instanceof EventInterface)) 
        {
            $event = new Event($event, $target, $argv);
        } 
        else 
        {
            if ($target) 
            {
                $event->setTarget($target);
            }
            if ($argv) 
            {
                $event->setParams($argv);
            }
        }
        $eventName = $event->getName();

        if (!array_key_exists($eventName, $this->events)) 
        {
            $this->events[$eventName] = [];
        }
        
        $events = array_merge($this->events[self::WILDCARD], $this->events[$eventName]);
        $result = null;
        
        foreach ($events as $priority) 
        {
            if (!is_array($priority)) 
            {
                continue;
            }
            foreach ($priority as $callback) 
            {
                if ($event->isPropagationStopped()) 
                {
                    break 2;
                }
                
                $start = microtime(true);

                $result = call_user_func($callback, $event, $result);

                static::$performanceLog[] = [
					'start' => $start,
					'end'   => microtime(true),
					'event' => strtolower($eventName),
				];
            }
        }

        return $result;
    }

    /**
	 * Getter for the performance log records.
	 *
	 * @return array
	 */
	public static function getPerformanceLogs()
	{
		return static::$performanceLog;
	}
}
