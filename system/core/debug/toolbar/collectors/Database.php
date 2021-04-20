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

use dFramework\core\db\connection\BaseConnection;
use dFramework\core\event\Event;
use dFramework\core\loader\Service;

/**
 * Database
 * 
 * Collector for the Database tab of the Debug Toolbar.
 *
 * @package		dFramework
 * @subpackage	Core
 * @category 	Debug/toolbar
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.3.0
 * @credit		CodeIgniter 4.0 - CodeIgniter\Debug\Toolbar\Collectors\Database
 * @file		/system/core/debug/toolbar/collectors/Database.php
 */
class Database extends BaseCollector
{

	/**
	 * Whether this collector has timeline data.
	 *
	 * @var boolean
	 */
	protected $hasTimeline = true;

	/**
	 * Whether this collector should display its own tab.
	 *
	 * @var boolean
	 */
	protected $hasTabContent = true;

	/**
	 * Whether this collector has data for the Vars tab.
	 *
	 * @var boolean
	 */
	protected $hasVarData = false;

	/**
	 * The name used to reference this collector in the toolbar.
	 *
	 * @var string
	 */
	protected $title = 'Database';

	/**
	 * Array of database connections.
	 *
	 * @var array
	 */
	protected $connections;

	/**
	 * The query instances that have been collected
	 * through the DBQuery Event.
	 *
	 * @var array
	 */
	protected static $queries = [];

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->connections =BaseConnection::getAllConnections();
	}

	//--------------------------------------------------------------------

	/**
	 * The static method used during Events to collect
	 * data.
	 *
	 * @param Event $event
	 *
	 * @internal param $ array \dFramework\core\db\query\Result
	 */
	public static function collect(Event $event)
	{
		/**
		 * @var \dFramework\core\db\query\Result
		 */
		$result = $event->getTarget();
		$config = Service::toolbar()->getConfig();

		// Provide default in case it's not set
		$max = $config['max_queries'] ?: 100;

		if (count(static::$queries) < $max)
		{
			static::$queries[] = (object) $result->details();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Returns timeline data formatted for the toolbar.
	 *
	 * @return array The formatted data or an empty array.
	 */
	protected function formatTimelineData(): array
	{
		$data = [];

		foreach ($this->connections as $alias => $connection)
		{
			// Connection Time
			$data[] = [
				'name'      => 'Connecting to Database: "' . $alias . '"',
				'component' => 'Database',
				'start'     => $connection['driver']->getConnectStart(),
				'duration'  => $connection['driver']->getConnectDuration(),
			];
		}

		foreach (static::$queries as $query)
		{
			$data[] = [
				'name'          => 'Query',
				'component'     => 'Database',
				'query'         => $query->sql,
				'start'         => $query->start,
				'duration'      => $query->duration
			];
		}

		return $data;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the data of this collector to be formatted in the toolbar
	 *
	 * @return array
	 */
	public function display(): array
	{
		// Key words we want bolded
		$highlight = [
			'SELECT',
			'DISTINCT',
			'FROM',
			'WHERE',
			'AND',
			'INNER JOIN',
			'LEFT JOIN',
			'RIGHT JOIN',
			'JOIN',
			'ORDER BY',
			'ASC',
			'DESC',
			'GROUP BY',
			'LIMIT',
			'INSERT',
			'INTO',
			'VALUES',
			'UPDATE',
			'OR ',
			'HAVING',
			'OFFSET',
			'NOT IN',
			'IN',
			'NOT LIKE',
			'LIKE',
			'COUNT',
			'MAX',
			'MIN',
			'ON',
			'AS',
			'AVG',
			'SUM',
			'(',
			')',
		];

		$data = [
			'queries' => [],
		];

		foreach (static::$queries as $query)
		{
			$sql = $query->sql;

			foreach ($highlight as $term)
			{
				$sql = str_replace($term, "<strong>{$term}</strong>", $sql);
			}

			$data['queries'][] = [
				'duration' => (number_format($query->duration, 5) * 1000) . ' ms',
				'sql'      => $sql,
				'affected_rows' => $query->affected_rows
			];
		}

		return $data;
	}

	//--------------------------------------------------------------------

	/**
	 * Gets the "badge" value for the button.
	 *
	 * @return integer
	 */
	public function getBadgeValue(): int
	{
		return count(static::$queries);
	}

	//--------------------------------------------------------------------

	/**
	 * Information to be displayed next to the title.
	 *
	 * @return string The number of queries (in parentheses) or an empty string.
	 */
	public function getTitleDetails(): string
	{
		return '(' . count(static::$queries) . ' Queries across ' . ($countConnection = count($this->connections)) . ' Connection' .
				($countConnection > 1 ? 's' : '') . ')';
	}

	//--------------------------------------------------------------------

	/**
	 * Does this collector have any data collected?
	 *
	 * @return boolean
	 */
	public function isEmpty(): bool
	{
		return empty(static::$queries);
	}

	//--------------------------------------------------------------------

	/**
	 * Display the icon.
	 *
	 * Icon from https://icons8.com - 1em package
	 *
	 * @return string
	 */
	public function icon(): string
	{
		return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAADMSURBVEhLY6A3YExLSwsA4nIycQDIDIhRWEBqamo/UNF/SjDQjF6ocZgAKPkRiFeEhoYyQ4WIBiA9QAuWAPEHqBAmgLqgHcolGQD1V4DMgHIxwbCxYD+QBqcKINseKo6eWrBioPrtQBq/BcgY5ht0cUIYbBg2AJKkRxCNWkDQgtFUNJwtABr+F6igE8olGQD114HMgHIxAVDyAhA/AlpSA8RYUwoeXAPVex5qHCbIyMgwBCkAuQJIY00huDBUz/mUlBQDqHGjgBjAwAAACexpph6oHSQAAAAASUVORK5CYII=';
	}

}
