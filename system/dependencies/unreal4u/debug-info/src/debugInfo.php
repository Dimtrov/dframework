<?php

namespace unreal4u;

/**
 * Common used functions when debugging applications
 *
 * @package debugInfo
 * @author Camilo Sperberg - http://unreal4u.com/
 * @version 2.0.0
 */
class debugInfo {
    /**
     * Version of this class
     * @var string
     */
    private $classVersion = '2.0.0';

    /**
     * The format of the timestamp that will be printed, based on strftime
     * @link http://php.net/manual/en/function.strftime.php
     * @var string
     */
    public static $timeFormat = '%F %T';

    /**
     * Private array with all the recorded data
     * @var array
     */
    private $data = array();

    /**
     * With how many decimals we want to print
     * @var int
     */
    public $decimals = 6;

    /**
     * Constructor, can also be used to immediatly record a time
     *
     * @param string $identifier
     */
    public function __construct($identifier='') {
        if (!empty($identifier)) {
            $this->beginCounter($identifier);
        }
    }

    /**
     * Magic function
     *
     * @return string
     */
    public function __toString() {
        if (PHP_SAPI == 'cli') {
            $eol = PHP_EOL;
        } else {
            $eol = '<br />';
        }
        return basename(__FILE__).' v'.$this->classVersion.' by unreal4u - Camilo Sperberg - http://unreal4u.com/'.$eol;
    }

    /**
     * Returns the current date and time to be used in the debug functions
     *
     * @see self::$timeFormat
     * @return string
     */
    private static function getDateStamp() {
        return '[' . strftime(self::$timeFormat) . '] ';
    }

    /**
     * Makes debugging a variable easier
     *
     * This function applies htmlentities so you can print whatever you want and display it nicely on-screen. It will
     * work nicely with CLI programs too formatting the output to differ a bit from the output made through HTML.
     *
     * @param mixed $a Whatever you want to print
     * @param bool $print Whether you should echo inmediatly or only return the string
     * @param string $message The message to print before the variable printing
     * @return string The formatted what-so-ever you wanted to print
     */
    public static function debug($a=null, $print=true, $message='') {
        $output = true;
        $type = gettype($a);

        // Check what action to take depending on type of data
        switch($type) {
            // Overwrite variable with string to indicate clearly what type of data we're dealing with
            case 'NULL':
                $a = '(null)';
                break;
            // Overwrite variable with string to indicate clearly what type of data we're dealing with
            case 'boolean':
                if ($a === true) {
                    $a = '('.$type.') true';
                } else {
                    $a = '('.$type.') false';
                }
                break;
            // Indicate also empty string
            case 'string':
                if ($a === '') {
                    $a = "(empty string) ''";
                }
                break;
            // In case we're printing out an array, check out what for types each component of that array is
            // @TODO Disabled for now, it produces a disastrous result when printing out nested arrays
            #case 'array':
            #    $copyOriginalArray = $a;
            #    $a = array();
            #    foreach($copyOriginalArray AS $index => $value) {
            #        $a[$index] = self::debug($value, false, $message);
            #    }
            #    break;
        }

        if (PHP_SAPI != 'cli') {
            // If outputting to browser, escape the contents
            $output = $message . htmlentities(print_r($a, true));
        } else {
            // If in CLI mode, always add current timestamp and don't escape htmlentities
            $output = self::getDateStamp() . $message . print_r($a, true) . PHP_EOL;
        }

        if ($print === true) {
            // If we aren't working in CLI mode, add <pre> and custom class name
            if (PHP_SAPI != 'cli') {
                $output = '<pre class="u4u-debug">' . $output . '</pre>';
            }
            echo $output;
        }

        // Return the output
        return $output;
    }


    /**
     * Prints a message in a file
     *
     * Don't use this function for intensive file writing because for each message it prints, it will use some expensive
     * system calls
     *
     * @param string $message What we want to print
     * @param string $filename The filename to which we want to print
     * @param string $directory The directory in which we want to save the file. Defaults to sys_get_temp_dir()
     * @return boolean Returns true if write was successfull, false otherwise
     */
    public static function debugFile($message='', $filename='', $directory='') {
        $success = false;
        if (empty($filename)) {
            $filename = 'u4u-log';
        }

        if (empty($directory)) {
            // Trailing slash always needed, check http://www.php.net/manual/en/function.sys-get-temp-dir.php#80690
            $directory = realpath(sys_get_temp_dir()).'/';
        }

        $filename = $directory.$filename;
        if (is_writable($filename)) {
            $success = file_put_contents(
                $filename, // Where to write
                self::debug($message, false) . PHP_EOL, // Write the message
                FILE_APPEND // Writing mode
            );
        }

        // file_put_contents can return number of bytes written or false in case of error, convert to boolean
        if ($success !== false) {
            $success = true;
        }

        return $success;
    }

    /**
     * Throws an ErrorException
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @throws ErrorException
     */
    public static function exceptionErrorHandler($errno=null, $errstr=null, $errfile=null, $errline=null) {
        // @TODO Do something with severity other than to pass just the errno
        throw new \ErrorException($errstr, $errno, $errno, $errfile, $errline);
    }

    /**
     * Sets the error handler to throw exceptions only
     */
    public static function throwExceptions() {
        set_error_handler(get_class().'::exceptionErrorHandler');
    }

    /**
     * Returns the exact time
     *
     * @return float Returns the exact time+microtime
     */
    public static function getExactTime() {
        return microtime(true);
    }

    /**
     * Formats a unix timestamp to a more human readable format
     *
     * @link http://www.php.net/manual/en/function.date.php
     *
     * @param number $time The time that we want to print. Leave empty for current timestamp
     * @param string $format Any format accepted by date. Defaults to ISO8601
     * @return string Returns an ISO8601 formatted string with the passed on date
     */
    public static function convertTimestamp($time=0, $format=null) {
        if (empty($time)) {
            $time = time();
        } else {
            $time = intval($time);
        }

        if (!is_string($format)) {
            $format = \DateTime::ISO8601;
        }

        $date = new \DateTime();
        $date->setTimeZone(new \DateTimeZone(date_default_timezone_get()));
        $date->setTimestamp($time);

        return $date->format($format);
    }

    /**
     * Gets the current used memory footprint
     *
     * @param string $format Choose between "B" (bytes), "KB", "KiB", "MB", "MiB", "GB", "GiB". Defaults to "B"
     * @param boolean $printUnit Print the unit suffix. Defaults to "false"
     * @return int Returns the memory usage in the requested format
     */
    public function getMemoryUsage($format='B', $printUnit=false) {
        return $this->formatNumber(memory_get_usage(), $format, $printUnit);
    }

    /**
     * Gets the peak memory usage
     *
     * @param string $format Choose between "B" (bytes), "KB", "KiB", "MB", "MiB", "GB", "GiB". Defaults to "B"
     * @param boolean $printUnit Print the unit suffix. Defaults to "false"
     * @return string Returns the peak memory usage in the requested format
     */
    public function getPeakMemoryUsage($format='B', $printUnit=false) {
        return $this->formatNumber(memory_get_peak_usage(), $format, $printUnit);
    }

    /**
     * Formats a number according to the given format
     *
     * @param float $number Any number
     * @param string $format Choose between "B" (bytes), "KB", "KiB", "MB", "MiB", "GB", "GiB". Defaults to "B"
     * @param boolean $printUnit Print the unit suffix. Defaults to "false"
     * @return string Returns the value in the requested format
     */
    protected function formatNumber($number, $format='B', $printUnit=false) {
        $multiplier = 1;
        $unit = 'B';
        switch(strtolower($format)) {
            case 'kb':
                $multiplier = 1000;
                $unit = 'KB';
            break;
            case 'kib':
                $multiplier = 1024;
                $unit = 'KiB';
            break;
            case 'mb':
                $multiplier = 1000 * 1000;
                $unit = 'MB';
            break;
            case 'mib':
                $multiplier = 1024 * 1024;
                $unit = 'MiB';
            break;
            case 'gb':
                $multiplier = 1000 * 1000 * 1000;
                $unit = 'GB';
            break;
            case 'gib':
                $multiplier = 1024 * 1024 * 1024;
                $unit = 'GiB';
            break;
        }

        $output = (string)round($number / $multiplier);
        if ($printUnit === true) {
            $output .= $unit;
        }

        return $output;
    }

    /**
     * Starts a counter
     *
     * @param string $identifier The identifier of the data we want to return
     * @return boolean Returns always true
     */
    public function beginCounter($identifier) {
        // First step: get the current exact time
        if (!empty($identifier)) {
            if (!is_array($identifier)) {
                $identifier = array($identifier);
            }

            foreach($identifier AS $id) {
                $this->data[$id] = array();
                $this->data[$id]['startTime'] = self::getExactTime();
                $this->data[$id]['startMemorySize'] = memory_get_usage();
                $this->data[$id]['startMemoryPeakSize'] = memory_get_peak_usage();
            }
        }

        return true;
    }

    /**
     * Ends a counter and returns the elapsed time between start and end
     *
     * @param string $identifier The identifier of the data we want to return
     * @return float Returns a float containing the difference between start and end time
     */
    public function endCounter($identifier='') {
        // First step: get the current exact time
        $time = self::getExactTime();

        if (!empty($this->data[$identifier]['endTime'])) {
            $totalTime = $this->data[$identifier]['endTime'] - $this->data[$identifier]['startTime'];
        } else if (array_key_exists($identifier, $this->data)) {
            $this->data[$identifier]['endTime'] = $time;
            $this->data[$identifier]['endMemorySize'] = memory_get_usage();
            $this->data[$identifier]['endMemoryPeakSize'] = memory_get_peak_usage();
        }

        return $this->getDiff($identifier, 'time');
    }

    /**
     * Delivers the memory difference of a identifier
     *
     * @param string $identifier The identifier of the data we want to return
     * @param string $type Can be "time", "memory", "peakmemory" or "all". Defaults to "all"
     */
    public function getDiff($identifier, $type='all') {
        $return = false;

        if (!empty($identifier) AND !empty($this->data[$identifier]['endMemorySize'])) {
            switch($type) {
                case 'time':
                    $return = sprintf('%.'.$this->decimals.'f', $this->data[$identifier]['endTime'] - $this->data[$identifier]['startTime']);
                break;
                case 'memory':
                    $return = $this->formatNumber($this->data[$identifier]['endMemorySize'] - $this->data[$identifier]['startMemorySize'], 'KiB', true);
                break;
                case 'peakmemory':
                    $return = $this->formatNumber($this->data[$identifier]['endMemoryPeakSize'] - $this->data[$identifier]['startMemoryPeakSize'], 'KiB', true);
                break;
                case 'all':
                    $return = array();
                    $return['time'] = $this->getDiff($identifier, 'time');
                    $return['memory'] = $this->getDiff($identifier, 'memory');
                    $return['peakmemory'] = $this->getDiff($identifier, 'peakmemory');
                break;
            }
        }

        return $return;
    }
}

include ('auxiliar-functions.php');
