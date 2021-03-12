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
 *  @license    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @link	    https://dimtrov.hebfree.org/works/dframework
 *  @version    3.3.0
 */

namespace dFramework\core\debug;

/**
 * Class Timer
 *
 * Provides a simple way to measure the amount of time
 * that elapses between two points.
 *
 * NOTE: All methods are static since the class is intended
 * to measure throughout an entire application's life cycle.
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Utilities
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @since       3.3.0
 * @credit      CodeIgniter 4.0 (CodeIgniter\Debug\Timer)
 * @file        /system/core/debug/Timer.php
 */
class Timer
{

	/**
	 * List of all timers.
	 *
	 * @var array
	 */
	protected $timers = [];

	//--------------------------------------------------------------------

	/**
	 * Starts a timer running.
	 *
	 * Multiple calls can be made to this method so that several
	 * execution points can be measured.
	 *
	 * @param string $name The name of this timer.
	 * @param float  $time Allows user to provide time.
	 *
	 * @return self
	 */
	public function start(string $name, float $time = null) : self
	{
		$this->timers[strtolower($name)] = [
			'start' => ! empty($time) ? $time : microtime(true),
			'end'   => null,
		];

		return $this;
	}

	/**
	 * Stops a running timer.
	 *
	 * If the timer is not stopped before the timers() method is called,
	 * it will be automatically stopped at that point.
	 *
	 * @param string $name The name of this timer.
	 *
	 * @return self
	 */
	public function stop(string $name) : self
	{
		$name = strtolower($name);

		if (empty($this->timers[$name]))
		{
			throw new \RuntimeException('Cannot stop timer: invalid name given.');
		}

		$this->timers[$name]['end'] = microtime(true);

		return $this;
	}

	/**
	 * Returns the duration of a recorded timer.
	 *
	 * @param string  $name     The name of the timer.
	 * @param integer $decimals Number of decimal places.
	 *
	 * @return null|float       Returns null if timer exists by that name.
	 *                          Returns a float representing the number of
	 *                          seconds elapsed while that timer was running.
	 */
	public function getElapsedTime(string $name, int $decimals = 4) : ?float
	{
		$name = strtolower($name);

		if (empty($this->timers[$name]))
		{
			return null;
		}

        return $this->getDuration($this->timers[$name], $decimals);
	}

	/**
	 * Returns the array of timers, with the duration pre-calculated for you.
	 *
	 * @param integer $decimals Number of decimal places
	 *
	 * @return array
	 */
	public function getTimers(int $decimals = 4): array
	{
		$timers = $this->timers;

		foreach ($timers as &$timer)
		{
            $timer['duration'] = $this->getDuration($timer, $decimals);
		}

		return $timers;
	}

	/**
	 * Checks whether or not a timer with the specified name exists.
	 *
	 * @param string $name
	 *
	 * @return boolean
	 */
	public function has(string $name): bool
	{
		return array_key_exists(strtolower($name), $this->timers);
	}

    //--------------------------------------------------------------------

    /**
	 * Returns the duration of a recorded timer.
	 *
	 * @param array  $timer     The timer.
	 * @param integer $decimals Number of decimal places.
	 *
	 * @return float       Returns a float representing the number of
	 *                     seconds elapsed while that timer was running.
	 */
    private function getDuration(array $timer, int $decimals = 4) : float
    {
        if (empty($timer['end']))
		{
			$timer['end'] = microtime(true);
		}

		return (float) number_format($timer['end'] - $timer['start'], $decimals);
    }
}
