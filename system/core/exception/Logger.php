<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019 - 2021, Dimtrov Lab's
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @copyright	Copyright (c) 2019 - 2021, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage	https://dimtrov.hebfree.org/works/dframework
 * @version     3.3.0
 */

namespace dFramework\core\exception;

use Josantonius\Json\Json;
use Throwable;

/**
 * Log
 *
 * Native recorder of errors and exceptions
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Exception
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.0.0
 * @file        /system/core/exception/Logger.php
 */
class Logger
{
	/**
     * Handled log levels
     *
     * @var string[]
     */
    protected $levels = [
        'emergency',
        'alert',
        'critical',
        'error',
        'warning',
        'notice',
        'info',
        'debug',
    ];

    /**
     * Log levels as detailed in RFC 5424
     * https://tools.ietf.org/html/rfc5424
     *
     * @var array
     */
    protected $levelMap = [
        'emergency' => 1,
		'alert'     => 2,
		'critical'  => 3,
		'error'     => 4,
		'warning'   => 5,
		'notice'    => 6,
		'info'      => 7,
		'debug'     => 8,
    ];

	/**
	 * @var self
	 */
	private static $_instance;


	public function __call($name, $arguments = [])
	{
		return self::execFacade($name, $arguments);
	}

	public static function __callStatic($name, $arguments = [])
	{
		return self::execFacade($name, $arguments);
	}

	/**
	 * Get an unique instance of class (singletton pattern)
	 *
	 * @return self
	 */
	public static function instance() : self
	{
		if (null === self::$_instance)
		{
			self::$_instance = new self;
		}
		return self::$_instance;
	}


	/**
	 * Register an exception
	 *
	 * @param Throwable $exception
	 */
	public function register(Throwable $exception)
	{
		$message  = $exception->getMessage();
		$code     = $exception->getCode();
		$file     = $exception->getFile();
		$line     = $exception->getLine();

		return $this->parseError($message, $code, $file, $line);
	}


	/**
	 * System is unusable.
	 *
	 * @param string $message
	 * @param string|null $file
	 * @param int|null $line
	 * @return void
	 */
	public static function emergency(string $message, ?string $file = null, ?int $line = null)
	{
		return self::write('emergency', $message, $file, $line);
	}

	/**
	 * Action must be taken immediately.
	 *
	 * Example: Entire website down, database unavailable, etc. This should
	 * trigger the SMS alerts and wake you up.
	 *
	 * @param string $message
	 * @param string|null $file
	 * @param int|null $line
	 * @return void
	 */
	public static function alert(string $message, ?string $file = null, ?int $line = null)
	{
		return self::write('alert', $message, $file, $line);
	}

	/**
	 * Critical conditions.
	 *
	 * Example: Application component unavailable, unexpected exception.
	 *
	 * @param string $message
	 * @param string|null $file
	 * @param int|null $line
	 * @return void
	*/
	public static function critical(string $message, ?string $file = null, ?int $line = null)
	{
		return self::write('critical', $message, $file, $line);
	}

	/**
	 * Runtime errors that do not require immediate action but should typically
	 * be logged and monitored.
	 *
	 * @param string $message
	 * @param string|null $file
	 * @param int|null $line
	 * @return void
	*/
	public static function error(string $message, ?string $file = null, ?int $line = null)
	{
		return self::write('error', $message, $file, $line);
	}

	/**
	 * Exceptional occurrences that are not errors.
	 *
	 * Example: Use of deprecated APIs, poor use of an API, undesirable things
	 * that are not necessarily wrong.
	 *
	 * @param string $message
	 * @param string|null $file
	 * @param int|null $line
	 * @return void
	 */
	public static function warning(string $message, ?string $file = null, ?int $line = null)
	{
		return self::write('warning', $message, $file, $line);
	}

	/**
	 * Normal but significant events.
	 *
	 * @param string $message
	 * @param string|null $file
	 * @param int|null $line
	 * @return void
	 */
	public static function notice(string $message, ?string $file = null, ?int $line = null)
	{
		return self::write('notice', $message, $file, $line);
	}

	/**
	 * Interesting events.
	 *
	 * Example: User logs in, SQL logs.
	 *
	 * @param string $message
	 * @param string|null $file
	 * @param int|null $line
	 * @return void
	 */
	public static function info(string $message, ?string $file = null, ?int $line = null)
	{
		return self::write('info', $message, $file, $line);
	}

	/**
	 * Detailed debug information.
	 *
	 * @param string $message
	 * @param string|null $file
	 * @param int|null $line
	 * @return void
	 */
	public static function debug(string $message, ?string $file = null, ?int $line = null)
	{
		return self::write('debug', $message, $file, $line);
	}


	/**
	 * Save PHP generated error messages
	 *
	 * @param int|string $level
	 * @param string $message
	 * @param string|null $file
	 * @param int|null $line
	 * @return	void
	 */
	private function save($level, string $message, ?string $file = null, ?int $line = null)
	{
		if (is_string($level))
		{
			if (! array_key_exists($level, $this->levelMap))
			{
				throw new Exception($level.' is an invalid log level.');
			}
			$level = $this->levelMap[$level];
		}

		$file = null === $file ? __FILE__ : $file;
		$line = null === $line ? __LINE__ : $line;

		return $this->parseError($message, (int) $level, $file, $line);
	}
	public static function write($level, string $message, ?string $file = null, ?int $line = null)
	{
		return self::instance()->save($level, $message, $file, $line);
	}

	/**
	 * Parse error to build an appropriate log
	 *
	 * @param string $message
	 * @param int $code
	 * @param string $file
	 * @param int $line
	 * @return void
	 */
	private function parseError(string $message, int $code, string $file, int $line)
	{
		switch($code)
		{
			case 1:
				$type = 'E_ERROR';
				$ertype = 'error';
				break;
			case 2:
				$type = 'E_WARNING';
				$ertype = 'warning';
				break;
			case 4:
				$type = 'E_PARSE';
				$ertype = 'error';
				break;
			case 8:
				$type = 'E_NOTICE';
				$ertype = 'warning';
				break;
			case 16:
				$type = 'E_CORE_ERROR';
				$ertype = 'error';
				break;
			case 32:
				$type = 'E_CORE_WARNING';
				$ertype = 'error';
				break;
			case 64:
				$type = 'E_COMPILE_ERROR';
				$ertype = 'error';
				break;
			case 128:
			$type = 'E_COMPILE_WARNING';
				$ertype = 'error';
				break;
			case 256:
				$type = 'E_USER_ERROR';
				$ertype = 'error';
				break;
			case 512:
				$type = 'E_USER_WARNING';
				$ertype = 'warning';
				break;
			case 1024:
				$type = 'E_USER_NOTICE';
				$ertype = 'notice';
				break;
			case 2048:
				$type = 'E_STRICT';
				$ertype = 'warning';
				break;
			case 4096:
				$type = 'E_RECOVERABLE_ERROR';
				$ertype = 'error';
				break;
			case 8192:
				$type = 'E_DEPRECATED';
				$ertype = 'notice';
				break;
			case 16384:
				$type = 'E_USER_DEPRECATED';
				$ertype = 'notice';
			case 32767:
				$type = 'E_ALL';
				$ertype = 'error';
			default:
				$type = 'ERROR';
				$ertype = 'error';
				break;
		}

		$msg = '';
		$msg .= 'An error of level '.$code.'('.$type.') was generated in file '.$file.' on line '.$line.".\n";
		$msg .= 'The error message was: "'.$message.'"';

		$this->saveError(array_merge(compact('code', 'type', 'ertype', 'code', 'message', 'file', 'line'), [
			'date'        => date('d.m.Y @ H:i'),
			'page'        => ($_SERVER['HTTP_HOST'] ?? '').($_SERVER['REQUEST_URI'] ?? null),
			'referrer'    => $_SERVER['HTTP_REFERER'] ?? null,
			'ip'          => $_SERVER['REMOTE_ADDR'] ?? null,
			'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? null,
			'description' => $msg
		]));
	}

	/**
	 * Save errors in log files
	 *
	 * @param array $errors
	 */
	private function saveError(array $errors)
	{
		$errors_file = \APP_DIR.'logs'.DS.date('Y').DS.date('md').'.json';

		$lastErrors = Json::fileToArray($errors_file);
		if (!empty($lastErrors))
		{
			$saveErrors = array_merge([$errors], $lastErrors);
		}
		else
		{
			$saveErrors = [$errors];
		}
		Json::arrayToFile($saveErrors, $errors_file);
	}
}
