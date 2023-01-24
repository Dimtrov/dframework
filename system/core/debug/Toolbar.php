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
 *  @license    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @link	    https://dimtrov.hebfree.org/works/dframework
 *  @version    3.4.0
 */

namespace dFramework\core\debug;

use dFramework\core\Config;
use dFramework\core\debug\toolbar\collectors\History;
use dFramework\core\http\Response;
use dFramework\core\http\ServerRequest;
use dFramework\core\loader\Service;
use dFramework\core\output\Format;
use dFramework\core\output\Parser;
use dFramework\core\router\Dispatcher;
use Psr\Http\Message\ResponseInterface;

/**
 * Debug Toolbar
 *
 * Displays a toolbar with bits of stats to aid a developer in debugging.
 *
 * Inspiration: http://prophiler.fabfuel.de
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Debug
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @since       3.3.0
 * @credit      CodeIgniter 4.0 (CodeIgniter\Debug\Toolbar)
 * @file        /system/core/debug/Toolbar.php
 */
class Toolbar
{
	private $debug_path = STORAGE_DIR . 'debugbar';

	const COLLECTORS = [
		\dFramework\core\debug\toolbar\collectors\Timers::class,
		\dFramework\core\debug\toolbar\collectors\Database::class,
		\dFramework\core\debug\toolbar\collectors\Files::class,
		\dFramework\core\debug\toolbar\collectors\Routes::class,
		\dFramework\core\debug\toolbar\collectors\Events::class,
	];

	/**
	 * Toolbar configuration settings.
	 *
	 * @var array
	 */
	protected $config = [
		/*
		|--------------------------------------------------------------------------
		| Debug Toolbar
		|--------------------------------------------------------------------------
		| The Debug Toolbar provides a way to see information about the performance
		| and state of your application during that page display. By default it will
		| NOT be displayed under production environments, and will only display if
		| CI_DEBUG is true, since if it's not, there's not much to display anyway.
		|
		| toolbarMaxHistory = Number of history files, 0 for none or -1 for unlimited
		|
		*/
		'collectors' => self::COLLECTORS,

		/*
		|--------------------------------------------------------------------------
		| Max History
		|--------------------------------------------------------------------------
		| The Toolbar allows you to view recent requests that have been made to
		| the application while the toolbar is active. This allows you to quickly
		| view and compare multiple requests.
		|
		| $maxHistory sets a limit on the number of past requests that are stored,
		| helping to conserve file space used to store them. You can set it to
		| 0 (zero) to not have any history stored, or -1 for unlimited history.
		|
		*/
		'max_history' => 20,

		/*
		|--------------------------------------------------------------------------
		| Max Queries
		|--------------------------------------------------------------------------
		| If the Database Collector is enabled, it will log every query that the
		| the system generates so they can be displayed on the toolbar's timeline
		| and in the query log. This can lead to memory issues in some instances
		| with hundreds of queries.
		|
		| $maxQueries defines the maximum amount of queries that will be stored.
		|
		*/
		'max_queries' => 100,

		/*
		|--------------------------------------------------------------------------
		| Toolbar Views Path
		|--------------------------------------------------------------------------
		| The full path to the the views that are used by the toolbar.
		| MUST have a trailing slash.
		|
		*/
		'views_path' => __DIR__ . '/toolbar/views/',
	];

	/**
	 * Collectors to be used and displayed.
	 *
	 * @var \dFramework\core\debug\toolbar\collectors\BaseCollector[]
	 */
	protected $collectors = [];

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 */
	public function __construct(array $config = [])
	{
		$this->config = array_merge($this->config, $config);

		foreach ($this->config['collectors'] As $collector)
		{
			if (! class_exists($collector))
			{
				continue;
			}

			$this->collectors[] = new $collector();
		}
    }
	/**
	 * Retourne les configuration courrante de la toolbar
	 *
	 * @return array
	 */
	public function getConfig() : array
	{
		return $this->config;
	}

	/**
	 * Prepare for debugging..
	 *
	 * @param  ServerRequest  $request
	 * @param  Response $response
	 * @return ResponseInterface
	 */
	public function prepare(Dispatcher $dispatcher, ServerRequest $request, Response $response) : ResponseInterface
	{
		$config = (object) Config::get('general');

		if (preg_match('#prod#', $config->environment) OR true !== $config->show_debugbar)
		{
			return $response;
		}
		$format = $response->getHeaderLine('content-type');

		// Non-HTML formats should not include the debugbar
		// then we send headers saying where to find the debug data
		// for this response
		if ($request->isAJAX() OR strpos($format, 'html') === false)
		{
			return $response;
		}

		$toolbar = Service::toolbar();
		$stats   = $dispatcher->getPerformanceStats();
		$data    = $toolbar->run(
				$stats['startTime'],
				$stats['totalTime'],
				$request,
				$response
		);

		// Updated to time() so we can get history
		$time = time();

		if (! is_dir($this->debug_path))
		{
			mkdir($this->debug_path, 0777);
		}

		$this->writeFile($this->debug_path . '/debugbar_' . $time . '.json', $data, 'w+');


		$_SESSION['_df_debugbar_'] = array_merge($_SESSION['_df_debugbar_'] ?? [], compact('time'));

		$debug_renderer = $this->respond();

		// Extract css
		preg_match('/<style (?:.+)>(.+)<\/style>/', $debug_renderer, $matches);
		$style = $matches[0] ?? '';
		$debug_renderer = str_replace($style, '', $debug_renderer);

		// Extract js
		preg_match('/<script (?:.+)>(.+)<\/script>/', $debug_renderer, $matches);
		$script = $matches[0] ?? '';
		$debug_renderer = str_replace($script, '', $debug_renderer);

		$response_body = $response->getBody()->getContents();
		$response_body = str_replace(['<head>', '</body>'],
			[
				'<head>'.$style,
				'<div id="toolbarContainer">'.trim(preg_replace('/\s+/', ' ', $debug_renderer)).'</div>'.$script.'<script>ciDebugBar.init();</script></body>'
			],
			$response_body);

		return $response->withBody(to_stream($response_body));
	}

	/**
	 * Returns all the data required by Debug Bar
	 *
	 * @param float                               $startTime App start time
	 * @param float                               $totalTime
	 * @param ServerRequest  $request
	 * @param Response $response
	 *
	 * @return string data
	 */
	public function run(float $startTime, float $totalTime, ServerRequest $request, Response $response): string
	{
		// Data items used within the view.
		$data['url']             = current_url();
		$data['method']          = $request->getMethod(true);
		$data['isAJAX']          = $request->isAJAX();
		$data['startTime']       = $startTime;
		$data['totalTime']       = $totalTime * 1000;
		$data['totalMemory']     = number_format((memory_get_peak_usage()) / 1024 / 1024, 3);
		$data['segmentDuration'] = $this->roundTo($data['totalTime'] / 7, 5);
		$data['segmentCount']    = (int) ceil($data['totalTime'] / $data['segmentDuration']);
		$data['dF_VERSION']      = \dFramework\core\dFramework::VERSION;
		$data['collectors']      = [];

		foreach ($this->collectors As $collector)
		{
			$data['collectors'][] = $collector->getAsArray();
		}

		foreach ($this->collectVarData() As $heading => $items)
		{
			$varData = [];

			if (is_array($items))
			{
				foreach ($items as $key => $value)
				{
					$varData[esc($key)] = is_string($value) ? esc($value) : '<pre>' . esc(print_r($value, true)) . '</pre>';
				}
			}

			$data['vars']['varData'][esc($heading)] = $varData;
		}

		if (! empty($_SESSION))
		{
			foreach ($_SESSION As $key => $value)
			{
				// Replace the binary data with string to avoid json_encode failure.
				if (is_string($value) AND preg_match('~[^\x20-\x7E\t\r\n]~', $value))
				{
					$value = 'binary data';
				}

				$data['vars']['session'][esc($key)] = is_string($value) ? esc($value) : '<pre>' . esc(print_r($value, true)) . '</pre>';
			}
		}

		foreach ($request->getQueryParams() As $name => $value)
		{
			$data['vars']['get'][esc($name)] = is_array($value) ? '<pre>' . esc(print_r($value, true)) . '</pre>' : esc($value);
		}

		foreach ($request->getParsedBody() As $name => $value)
		{
			$data['vars']['post'][esc($name)] = is_array($value) ? '<pre>' . esc(print_r($value, true)) . '</pre>' : esc($value);
		}

		foreach ($request->getHeaders() As $header => $value)
		{
			if (empty($value))
			{
				continue;
			}

			if (! is_array($value))
			{
				$value = [$value];
			}

			foreach ($value As $h)
			{
				if (is_object($h) AND method_exists($h, 'getName') AND method_exists($h, 'getValueLine'))
				{
					$data['vars']['headers'][esc($h->getName())] = esc($h->getValueLine());
				}
			}
		}

		foreach ($request->getCookieParams() As $name => $value)
		{
			$data['vars']['cookies'][esc($name)] = esc($value);
		}

		$data['vars']['request'] = ($request->is('ssl') ? 'HTTPS' : 'HTTP') . '/' . $request->getProtocolVersion();

		$data['vars']['response'] = [
			'statusCode'  => $response->getStatusCode(),
			'reason'      => esc($response->getReasonPhrase()),
			'contentType' => esc($response->getHeaderLine('content-type')),
		];

		$data['config'] = \dFramework\core\debug\toolbar\collectors\Config::display();

		return json_encode($data);
	}

	/**
	 * Returns an array of data from all of the modules
	 * that should be displayed in the 'Vars' tab.
	 *
	 * @return array
	 */
	protected function collectVarData(): array
	{
		$data = [];

		foreach ($this->collectors as $collector)
		{
			if (! $collector->hasVarData())
			{
				continue;
			}

			$dataCollected = $collector->getVarData();
			if (is_array($dataCollected))
			{
				$data = array_merge($data, $dataCollected);
			}
		}

		return $data;
	}

	/**
	 * Rounds a number to the nearest incremental value.
	 *
	 * @param float   $number
	 * @param integer $increments
	 *
	 * @return float
	 */
	protected function roundTo(float $number, int $increments = 5): float
	{
		$increments = 1 / $increments;

		return (ceil($number * $increments) / $increments);
	}


	//--------------------------------------------------------------------

	/**
	 * Inject debug toolbar into the response.
	 */
	public function respond()
	{
		$request = Service::request();

		// Otherwise, if it includes ?debugbar_time, then
		// we should return the entire debugbar.
		$debugbar_time = $_SESSION['_df_debugbar_']['time'] ?? $request->getQuery('debugbar_time');

		if ($debugbar_time)
		{
			helper('security');

			// Negotiate the content-type to format the output
			$format = $request->negotiate('media', [
				'text/html',
				'application/json',
				'application/xml',
			]);
			$format = explode('/', $format)[1];

			$file     = sanitize_filename('debugbar_' . $debugbar_time);
			$filename = $this->debug_path . '/' . $file . '.json';

			// Show the toolbar
			if (is_file($filename))
			{
				return $this->format($debugbar_time, file_get_contents($filename), $format);
			}
			return '';
		}
	}


	/**
	 * Format output
	 *
	 * @param string $data   JSON encoded Toolbar data
	 * @param string $format html, json, xml
	 *
	 * @return string
	 */
	protected function format(int $debugbar_time, string $data, string $format = 'html'): string
	{
		$data = json_decode($data, true);

		if ($this->config['max_history'] !== 0)
		{
			$history = new History();
			$history->setFiles($debugbar_time, $this->config['max_history']);

			$data['collectors'][] = $history->getAsArray();
		}

		$output = '';
		switch ($format)
		{
			case 'html':
				$data['styles'] = [];
				extract($data);
				$parser = new Parser([], '', [], [
					'view_path' => $this->config['views_path']
				]);
				ob_start();
				include($this->config['views_path'] . 'toolbar.tpl.php');
				$output = ob_get_clean();
				break;
			case 'json':
				$output    = Format::factory($data)->toJson();
				break;
			case 'xml':
				$output    = Format::factory($data)->toXml();
				break;
		}

		return $output;
	}


	//--------------------------------------------------------------------

	/**
	 * Called within the view to display the timeline itself.
	 *
	 * @param array   $collectors
	 * @param float   $startTime
	 * @param integer $segmentCount
	 * @param integer $segmentDuration
	 * @param array   $styles
	 *
	 * @return string
	 */
	protected function renderTimeline(array $collectors, float $startTime, int $segmentCount, int $segmentDuration, array &$styles): string
	{
		$displayTime = $segmentCount * $segmentDuration;
		$rows        = $this->collectTimelineData($collectors);
		$output      = '';
		$styleCount  = 0;

		foreach ($rows as $row)
		{
			$output .= '<tr>';
			$output .= "<td>{$row['name']}</td>";
			$output .= "<td>{$row['component']}</td>";
			$output .= "<td class='debug-bar-alignRight'>" . number_format($row['duration'] * 1000, 2) . ' ms</td>';
			$output .= "<td class='debug-bar-noverflow' colspan='{$segmentCount}'>";

			$offset = ((((float) $row['start'] - $startTime) * 1000) / $displayTime) * 100;
			$length = (((float) $row['duration'] * 1000) / $displayTime) * 100;

			$styles['debug-bar-timeline-' . $styleCount] = "left: {$offset}%; width: {$length}%;";
			$output                                     .= "<span class='timer debug-bar-timeline-{$styleCount}' title='" . number_format($length, 2) . "%'></span>";
			$output                                     .= '</td>';
			$output                                     .= '</tr>';

			$styleCount ++;
		}

		return $output;
	}

	/**
	 * Returns a sorted array of timeline data arrays from the collectors.
	 *
	 * @param array $collectors
	 *
	 * @return array
	 */
	protected function collectTimelineData($collectors): array
	{
		$data = [];

		// Collect it
		foreach ($collectors as $collector)
		{
			if (! $collector['hasTimelineData'])
			{
				continue;
			}

			$data = array_merge($data, $collector['timelineData']);
		}

		// Sort it

		return $data;
	}

	/**
	 * Write File
	 *
	 * Writes data to the file specified in the path.
	 * Creates a new file if non-existent.
	 *
	 * @param string $path File path
	 * @param string $data Data to write
	 * @param string $mode fopen() mode (default: 'wb')
	 *
	 * @return boolean
	 */
	protected function writeFile(string $path, string $data, string $mode = 'wb'): bool
	{
		try
		{
			$fp = fopen($path, $mode);

			flock($fp, LOCK_EX);

			for ($result = $written = 0, $length = strlen($data); $written < $length; $written += $result)
			{
				if (($result = fwrite($fp, substr($data, $written))) === false)
				{
					break;
				}
			}

			flock($fp, LOCK_UN);
			fclose($fp);

			return is_int($result);
		}
		catch (\Exception $fe)
		{
			return false;
		}
	}
}
