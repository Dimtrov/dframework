<?php

namespace dFramework\core\debug\toolbar\collectors;

use dFramework\core\loader\Service;

/**
 * Timers collector
 */
class Timers extends BaseCollector
{

	/**
	 * Whether this collector has data that can
	 * be displayed in the Timeline.
	 *
	 * @var boolean
	 */
	protected $hasTimeline = true;

	/**
	 * Whether this collector needs to display
	 * content in a tab or not.
	 *
	 * @var boolean
	 */
	protected $hasTabContent = false;

	/**
	 * The 'title' of this Collector.
	 * Used to name things in the toolbar HTML.
	 *
	 * @var string
	 */
	protected $title = 'Timers';

	//--------------------------------------------------------------------

	/**
	 * Child classes should implement this to return the timeline data
	 * formatted for correct usage.
	 *
	 * @return array
	 */
	protected function formatTimelineData(): array
	{
		$data = [];

		$benchmark = Service::timer(true);
		$rows      = $benchmark->getTimers(6);

		foreach ($rows as $name => $info)
		{
			if ($name === 'total_execution')
			{
				continue;
			}

			$data[] = [
				'name'      => ucwords(str_replace('_', ' ', $name)),
				'component' => 'Timer',
				'start'     => $info['start'],
				'duration'  => $info['end'] - $info['start'],
			];
		}

		return $data;
	}

}
