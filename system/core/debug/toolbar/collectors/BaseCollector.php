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
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.3.0
 */

namespace dFramework\core\debug\toolbar\collectors;

/**
 * Base Toolbar collectorTimers collector for debug toolbar
 *
 * @package		dFramework
 * @subpackage	Core
 * @category 	Debug/toolbar
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.3.0
 * @credit		CodeIgniter 4.0 - CodeIgniter\Debug\Toolbar\Collectors\BaseCollector
 * @file		/system/core/debug/toolbar/collectors/BaseCollector.php
 */
class BaseCollector
{

	/**
	 * Whether this collector has data that can
	 * be displayed in the Timeline.
	 *
	 * @var boolean
	 */
	protected $hasTimeline = false;

	/**
	 * Whether this collector needs to display
	 * content in a tab or not.
	 *
	 * @var boolean
	 */
	protected $hasTabContent = false;

	/**
	 * Whether this collector needs to display
	 * a label or not.
	 *
	 * @var boolean
	 */
	protected $hasLabel = false;

	/**
	 * Whether this collector has data that
	 * should be shown in the Vars tab.
	 *
	 * @var boolean
	 */
	protected $hasVarData = false;

	/**
	 * The 'title' of this Collector.
	 * Used to name things in the toolbar HTML.
	 *
	 * @var string
	 */
	protected $title = '';

	//--------------------------------------------------------------------

	/**
	 * Gets the Collector's title.
	 *
	 * @param  boolean $safe
	 * @return string
	 */
	public function getTitle(bool $safe = false): string
	{
		if ($safe)
		{
			return str_replace(' ', '-', strtolower($this->title));
		}

		return $this->title;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns any information that should be shown next to the title.
	 *
	 * @return string
	 */
	public function getTitleDetails(): string
	{
		return '';
	}

	//--------------------------------------------------------------------

	/**
	 * Does this collector need it's own tab?
	 *
	 * @return boolean
	 */
	public function hasTabContent(): bool
	{
		return (bool) $this->hasTabContent;
	}

	//--------------------------------------------------------------------

	/**
	 * Does this collector have a label?
	 *
	 * @return boolean
	 */
	public function hasLabel(): bool
	{
		return (bool) $this->hasLabel;
	}

	//--------------------------------------------------------------------

	/**
	 * Does this collector have information for the timeline?
	 *
	 * @return boolean
	 */
	public function hasTimelineData(): bool
	{
		return (bool) $this->hasTimeline;
	}

	//--------------------------------------------------------------------

	/**
	 * Grabs the data for the timeline, properly formatted,
	 * or returns an empty array.
	 *
	 * @return array
	 */
	public function timelineData(): array
	{
		if (! $this->hasTimeline)
		{
			return [];
		}

		return $this->formatTimelineData();
	}

	//--------------------------------------------------------------------

	/**
	 * Does this Collector have data that should be shown in the
	 * 'Vars' tab?
	 *
	 * @return boolean
	 */
	public function hasVarData(): bool
	{
		return (bool) $this->hasVarData;
	}

	//--------------------------------------------------------------------

	/**
	 * Gets a collection of data that should be shown in the 'Vars' tab.
	 * The format is an array of sections, each with their own array
	 * of key/value pairs:
	 *
	 *  $data = [
	 *      'section 1' => [
	 *          'foo' => 'bar,
	 *          'bar' => 'baz'
	 *      ],
	 *      'section 2' => [
	 *          'foo' => 'bar,
	 *          'bar' => 'baz'
	 *      ],
	 *  ];
	 *
	 * @return null
	 */
	public function getVarData()
	{
		return null;
	}

	//--------------------------------------------------------------------

	/**
	 * Child classes should implement this to return the timeline data
	 * formatted for correct usage.
	 *
	 * Timeline data should be formatted into arrays that look like:
	 *
	 *  [
	 *      'name'      => 'Database::Query',
	 *      'component' => 'Database',
	 *      'start'     => 10       // milliseconds
	 *      'duration'  => 15       // milliseconds
	 *  ]
	 *
	 * @return array
	 */
	protected function formatTimelineData(): array
	{
		return [];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the data of this collector to be formatted in the toolbar
	 *
	 * @return array|string
	 */
	public function display()
	{
		return [];
	}

	//--------------------------------------------------------------------

	/**
	 * Clean Path
	 *
	 * This makes nicer looking paths for the error output.
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	public function cleanPath(string $file): string
	{
		if (strpos($file, APP_DIR) === 0)
		{
			$file = 'APP_DIR/' . substr($file, strlen(APP_DIR));
		}
		elseif (strpos($file, SYST_DIR) === 0)
		{
			$file = 'SYST_DIR/' . substr($file, strlen(SYST_DIR));
		}
		elseif (strpos($file, WEBROOT) === 0)
		{
			$file = 'WEBROOT/' . substr($file, strlen(WEBROOT));
		}

		return str_replace(['/', '\\'], DS, $file);
	}

	/**
	 * Gets the "badge" value for the button.
	 *
	 * @return null
	 */
	public function getBadgeValue()
	{
		return null;
	}

	/**
	 * Does this collector have any data collected?
	 *
	 * If not, then the toolbar button won't get shown.
	 *
	 * @return boolean
	 */
	public function isEmpty(): bool
	{
		return false;
	}

	/**
	 * Returns the HTML to display the icon. Should either
	 * be SVG, or a base-64 encoded.
	 *
	 * Recommended dimensions are 24px x 24px
	 *
	 * @return string
	 */
	public function icon(): string
	{
		return '';
	}

	/**
	 * Return settings as an array.
	 *
	 * @return array
	 */
	public function getAsArray(): array
	{
		return [
			'title'           => $this->getTitle(),
			'titleSafe'       => $this->getTitle(true),
			'titleDetails'    => $this->getTitleDetails(),
			'display'         => $this->display(),
			'badgeValue'      => $this->getBadgeValue(),
			'isEmpty'         => $this->isEmpty(),
			'hasTabContent'   => $this->hasTabContent(),
			'hasLabel'        => $this->hasLabel(),
			'icon'            => $this->icon(),
			'hasTimelineData' => $this->hasTimelineData(),
			'timelineData'    => $this->timelineData(),
		];
	}

}
