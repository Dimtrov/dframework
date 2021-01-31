<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Sarl
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage	https://dimtrov.hebfree.org/works/dframework
 * @version     3.2
 */


namespace dFramework\core\exception;

use Josantonius\Json\Json;

/**
 * Log
 *
 * Native recorder of errors and exceptions
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Exception
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.0
 * @file        /system/core/exception/Log.php
 */

class Log
{
	/**
     * Handled log levels
     *
     * @var string[]
     */
    protected static $_levels = [
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
    protected static $_levelMap = [
        'emergency' => LOG_EMERG,
        'alert'     => LOG_ALERT,
        'critical'  => LOG_CRIT,
        'error'     => LOG_ERR,
        'warning'   => LOG_WARNING,
        'notice'    => LOG_NOTICE,
        'info'      => LOG_INFO,
        'debug'     => LOG_DEBUG,
    ];


	public  function register($exception)
	{
		$message  = $exception->getMessage();
		$code     = $exception->getCode();
		$file     = $exception->getFile();
		$line     = $exception->getLine();
		
		$this->parseError($message, $code, $file, $line);	
	}


	public static function write(string $level, string $message, ?string $file = null, ?int $line = null)
	{
		
	}

	public static function warning(string $message, ?string $file = null, ?int $line = null)
	{
		$file = null === $file ? __FILE__ : $file;
		$line = null === $line ? __LINE__ : $line;

		return self::save($message, 2, $file, $line);
	}
	
	/**
	 * Save PHP generated error messages
	 *
	 * @param string $message
	 * @param int $code
	 * @param string $file
	 * @param int $line
	 * @return	void
	 */
	public static function save($message, $code, $file, $line)
	{
		if(null === self::$instance) 
		{
			self::$instance = new self;
		}
		self::$instance->parseError($message, $code, $file, $line);
	}
	private static $instance = null;


	private function parseError($message, $code, $file, $line)
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
			'ip'          => $_SERVER['REMOTE_ADDR'],
			'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? null,
			'description' => $msg
		]));

	}


	private function saveError(array $errors)
	{
		$errors_file = \APP_DIR.'logs'.DS.date('Y').DS.date('md').'.json';

		$lastErrors = Json::fileToArray($errors_file);
		if(!empty($lastErrors))
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