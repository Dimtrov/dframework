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
 *  @license	https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 *  @homepage	https://dimtrov.hebfree.org/works/dframework
 *  @version    3.4.0
 */

namespace dFramework\core\utilities;

use DateTime;
use DateTimeZone;
use Exception;
use InvalidArgumentException;
use DateInterval;
use DateTimeInterface;

/**
 * Date
 * This class encapsulates various date and time functionality.
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Utilities
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.3.0
 * @file        /system/core/utilities/Date.php
 *
 * @method int getDay() Get day of month.
 * @method int getMonth() Get the month.
 * @method int getYear() Get the year.
 * @method int getHour() Get the hour.
 * @method int getMinute() Get the minutes.
 * @method int getSecond() Get the seconds.
 * @method string getDayOfWeek() Get the day of the week, e.g., Monday.
 * @method int getDayOfWeekAsNumeric() Get the numeric day of week.
 * @method int getDaysInMonth() Get the number of days in the month.
 * @method int getDayOfYear() Get the day of the year.
 * @method string getDaySuffix() Get the suffix of the day, e.g., st.
 * @method bool isLeapYear() Determines if is leap year.
 * @method string isAmOrPm() Determines if time is AM or PM.
 * @method bool isDaylightSavings() Determines if observing daylight savings.
 * @method int getGmtDifference() Get difference in GMT.
 * @method int getSecondsSinceEpoch() Get the number of seconds since epoch.
 * @method string getTimezoneName() Get the timezone name.
 * @method setDay(int $day) Set the day of month.
 * @method setMonth(int $month) Set the month.
 * @method setYear(int $year) Set the year.
 * @method setHour(int $hour) Set the hour.
 * @method setMinute(int $minute) Set the minutes.
 * @method setSecond(int $second) Set the seconds.
 */
class Date extends DateTime
{
	// Default time zone used as a default parameter for date functions
	// Time zones are listed here: http://php.net/manual/en/timezones.php
	const DEFAULT_TIMEZONE = 'UTC'; // UTC&#65533;00:00 Coordinated Universal Time
	// const DEFAULT_TIMEZONE = 'America/New_York';    // Eastern
	// const DEFAULT_TIMEZONE = 'America/Chicago';     // Central
	// const DEFAULT_TIMEZONE = 'America/Denver';      // Mountain
	// const DEFAULT_TIMEZONE = 'America/Phoenix';     // Mountain no DST
	// const DEFAULT_TIMEZONE = 'America/Los_Angeles'; // Pacific

	/**
	 * Default date format used when casting object to string.
	 *
	 * @var string
	 */
	protected $defaultDateFormat = 'jS F, Y \a\\t g:ia';

	/**
	 * Starting day of the week, where 0 is Sunday and 1 is Monday.
	 *
	 * @var int
	 */
	protected $weekStartDay = 0;

	/**
	 * Create a new Date instance.
	 *
	 * @param  string|null  $time
	 * @param  string|DateTimeZone  $timezone
	 * @return void
	 */
	public function __construct(?string $time = null, $timezone = null)
	{
		$timezone = $this->parseSuppliedTimezone($timezone);

		parent::__construct($time, $timezone);
	}

	/**
	 * Make and return new Date instance.
	 *
	 * @param  string|null  $time
	 * @param  string|DateTimeZone  $timezone
	 * @return self
	 */
	public static function make(?string $time = null, $timezone = null) : self
	{
		return new static($time, $timezone);
	}

	/**
	 * Make and return a new Date instance with defined year, month, and day.
	 *
	 * @param  int|null  $year
	 * @param  int|null  $month
	 * @param  int|null  $day
	 * @param  string|DateTimeZone  $timezone
	 * @return self
	 */
	public static function makeFromDate(?int $year = null, ?int $month = null, ?int $day = null, $timezone = null)
	{
		return static::makeFromDateTime($year, $month, $day, null, null, null, $timezone);
	}

	/**
     * Takes an instance of DateTimeInterface and returns an instance of Time with it's same values.
     *
     * @param DateTimeInterface $dateTime
     * @return self
     */
    public static function createFromInstance(DateTimeInterface $dateTime) : self
    {
        return self::makeFromDateTime(
			(int) $dateTime->format('Y'),
			(int) $dateTime->format('m'),
			(int) $dateTime->format('d'),
			(int) $dateTime->format('H'),
			(int) $dateTime->format('i'),
			(int) $dateTime->format('s'),
			$dateTime->getTimezone()
		);
    }

	/**
     * Returns a new instance with the datetime set based on the provided UNIX timestamp.
     *
	 * @param int $timestamp
     * @param DateTimeZone|string|null $timezone
     * @return self
     */
    public static function createFromTimestamp(int $timestamp, $timezone = null) : self
    {
        return self::make(gmdate('Y-m-d H:i:s', $timestamp), $timezone);
    }

	/**
	 * Make and return a new ExpressiveDate instance with defined hour, minute, and second.
	 *
	 * @param  int|null  $hour
	 * @param  int|null  $minute
	 * @param  int|null  $second
	 * @param  string|DateTimeZone  $timezone
	 * @return self
	 */
	public static function makeFromTime(?int $hour = null, ?int $minute = null, ?int $second = null, $timezone = null)
	{
		return static::makeFromDateTime(null, null, null, $hour, $minute, $second, $timezone);
	}

	/**
	 * Make and return a new ExpressiveDate instance with defined year, month, day, hour, minute, and second.
	 *
	 * @param  int|null  $year
	 * @param  int|null  $month
	 * @param  int|null  $day
	 * @param  int|null  $hour
	 * @param  int|null  $minute
	 * @param  int|null  $second
	 * @param  string|DateTimeZone  $timezone
	 * @return self
	 */
	public static function makeFromDateTime(?int $year = null, ?int $month = null, ?int $day = null, ?int $hour = null, ?int $minute = null, ?int $second = null, $timezone = null)
	{
		$date = new static(null, $timezone);

		$date->setDate($year ?: $date->getYear(), $month ?: $date->getMonth(), $day ?: $date->getDay());

		// If no hour was given then we'll default the minute and second to the current
		// minute and second. If a date was given and minute or second are null then
		// we'll set them to 0, mimicking PHPs behaviour.
		if (is_null($hour))
		{
			$minute = $minute ?: $date->getMinute();
			$second = $second ?: $date->getSecond();
		}
		else
		{
			$minute = $minute ?: 0;
			$second = $second ?: 0;
		}

		$date->setTime($hour ?: $date->getHour(), $minute, $second);

		return $date;
	}

	/**
	 * Parse a supplied timezone.
	 *
	 * @param  string|DateTimeZone  $timezone
	 * @return DateTimeZone
	 */
	protected function parseSuppliedTimezone($timezone)
	{
		if ($timezone instanceof DateTimeZone OR is_null($timezone))
		{
			return $timezone;
		}

		try
		{
			$timezone = new DateTimeZone($timezone);
		}
		catch (Exception $error)
		{
			throw new InvalidArgumentException('The supplied timezone ['.$timezone.'] is not supported.');
		}

		return $timezone;
	}

	/**
	 * Returns the current date and time as a DateTime object or formatted string
	 *
	 * @param string $format [OPTIONAL] If specified, will format the date as a string
	 *                       If not specified, returns a DateTime object
	 *	                     (example: 'Y-m-d H:i:s')
	 * @param string|DateTimeZone $timezone [OPTIONAL] Default timezone
	 * @return self|string The DateTime object or a formatted string
	 */
	public static function now($format = false, $timezone = null)
	{
		$now = new static(null, $timezone);
		$now->setTimestamp(time());

		if ($format)
        {
			return $now->format($format);
		}
        return $now;
	}

	/**
	 * Returns the current date (not time) as a DateTime object or formatted string
	 *
	 * @param string $format [OPTIONAL] If specified, will format the date as a string
	 *                       If not specified, returns a DateTime object
	 *	                     (example: 'Y-m-d')
	 * @param string|DateTimeZone $timezone [OPTIONAL] Default timezone
	 * @return self|string The DateTime object or a formatted string
	 */
	public static function today($format = false, $timezone = null)
	{
		$today = self::now(false, $timezone)->setHour(0)->setMinute(0)->setSecond(0);

		if ($format)
        {
			return $today->format($format);
		}
        return $today;
	}

	/**
	 * Returns the date (not time) of tomorrow as a DateTime object or formatted string
	 *
	 * @param string $format [OPTIONAL] If specified, will format the date as a string
	 *                       If not specified, returns a DateTime object
	 *	                     (example: 'Y-m-d')
	 * @param string|DateTimeZone $timezone [OPTIONAL] Default timezone
	 * @return self|string The DateTime object or a formatted string
	 */
	public static function tomorrow($format = false, $timezone = null)
	{
		$tomorrow = self::now(false, $timezone)->addOneDay()->startOfDay();

		if ($format)
        {
			return $tomorrow->format($format);
		}
        return $tomorrow;
	}

	/**
	 * Returns the date (not time) of yesterday as a DateTime object or formatted string
	 *
	 * @param string $format [OPTIONAL] If specified, will format the date as a string
	 *                       If not specified, returns a DateTime object
	 *	                     (example: 'Y-m-d')
	 * @param string|DateTimeZone $timezone [OPTIONAL] Default timezone
	 * @return self|string The DateTime object or a formatted string
	 */
	public static function yesterday($format = false, $timezone = null)
	{
		$yesterday = self::now(false, $timezone)->minusOneDay()->startOfDay();

		if ($format)
		{
			return $yesterday->format($format);
		}
		return $yesterday;
	}

	/**
	 * Use the start of the day.
	 *
	 * @return self
	 */
	public function startOfDay() : self
	{
		$this->setHour(0)->setMinute(0)->setSecond(0);

		return $this;
	}

	/**
	 * Use the end of the day.
	 *
	 * @return self
	 */
	public function endOfDay() : self
	{
		$this->setHour(23)->setMinute(59)->setSecond(59);

		return $this;
	}

	/**
	 * Use the start of the week.
	 *
	 * @return self
	 */
	public function startOfWeek() : self
	{
		$this->minusDays($this->getDayOfWeekAsNumeric())->startOfDay();

		return $this;
	}

	/**
	 * Use the end of the week.
	 *
	 * @return self
	 */
	public function endOfWeek() : self
	{
		$this->addDays(6 - $this->getDayOfWeekAsNumeric())->endOfDay();

		return $this;
	}

	/**
	 * Use the start of the month.
	 *
	 * @return self
	 */
	public function startOfMonth() : self
	{
		$this->setDay(1)->startOfDay();

		return $this;
	}

	/**
	 * Use the end of the month.
	 *
	 * @return self
	 */
	public function endOfMonth() : self
	{
		$this->setDay($this->getDaysInMonth())->endOfDay();

		return $this;
	}

	/**
	 * Add one day.
	 *
	 * @return self
	 */
	public function addOneDay() : self
	{
		return $this->addDays(1);
	}

	/**
	 * Add a given amount of days.
	 *
	 * @param  int|float  $amount
	 * @return self
	 */
	public function addDays($amount) : self
	{
		return $this->modifyDays($amount);
	}

	/**
	 * Minus one day.
	 *
	 * @return self
	 */
	public function minusOneDay() : self
	{
		return $this->minusDays(1);
	}

	/**
	 * Minus a given amount of days.
	 *
	 * @param  int|float  $amount
	 * @return self
	 */
	public function minusDays($amount) : self
	{
		return $this->modifyDays($amount, true);
	}

	/**
	 * Modify by an amount of days.
	 *
	 * @param  int|float  $amount
	 * @param  bool  $invert
	 * @return self
	 */
	protected function modifyDays($amount, bool $invert = false) : self
	{
		if ($this->isFloat($amount))
		{
			return $this->modifyHours($amount * 24, $invert);
		}

		$interval = new DateInterval("P{$amount}D");

		$this->modifyFromInterval($interval, $invert);

		return $this;
	}

	/**
	 * Add one month.
	 *
	 * @return self
	 */
	public function addOneMonth() : self
	{
		return $this->addMonths(1);
	}

	/**
	 * Add a given amount of months.
	 *
	 * @param  int|float  $amount
	 */
	public function addMonths($amount) : self
	{
		return $this->modifyMonths($amount);
	}

	/**
	 * Minus one month.
	 *
	 * @return self
	 */
	public function minusOneMonth() : self
	{
		return $this->minusMonths(1);
	}

	/**
	 * Minus a given amount of months.
	 *
	 * @param  int|float  $amount
	 * @return self
	 */
	public function minusMonths($amount) : self
	{
		return $this->modifyMonths($amount, true);
	}

	/**
	 * Modify by an amount of months.
	 *
	 * @param  int|float  $amount
	 * @param  bool  $invert
	 * @return self
	 */
	protected function modifyMonths($amount, bool $invert = false) : self
	{
		if ($this->isFloat($amount))
		{
			return $this->modifyWeeks($amount * 4, $invert);
		}

		$interval = new DateInterval("P{$amount}M");

		$this->modifyFromInterval($interval, $invert);

		return $this;
	}

	/**
	 * Add one year.
	 *
	 * @return self
	 */
	public function addOneYear() : self
	{
		return $this->addYears(1);
	}

	/**
	 * Add a given amount of years.
	 *
	 * @param  int|float  $amount
	 * @return self
	 */
	public function addYears($amount) : self
	{
		return $this->modifyYears($amount);
	}

	/**
	 * Minus one year.
	 *
	 * @return self
	 */
	public function minusOneYear() : self
	{
		return $this->minusYears(1);
	}

	/**
	 * Minus a given amount of years.
	 *
	 * @param  int|float  $amount
	 * @return self
	 */
	public function minusYears($amount) : self
	{
		return $this->modifyYears($amount, true);
	}

	/**
	 * Modify by an amount of Years.
	 *
	 * @param  int|float  $amount
	 * @param  bool  $invert
	 * @return self
	 */
	protected function modifyYears($amount, bool $invert = false) : self
	{
		if ($this->isFloat($amount))
		{
			return $this->modifyMonths($amount * 12, $invert);
		}

		$interval = new DateInterval("P{$amount}Y");

		$this->modifyFromInterval($interval, $invert);

		return $this;
	}

	/**
	 * Add one hour.
	 *
	 * @param  int  $amount
	 * @return self
	 */
	public function addOneHour() : self
	{
		return $this->addHours(1);
	}

	/**
	 * Add a given amount of hours.
	 *
	 * @param  int|float  $amount
	 * @return self
	 */
	public function addHours($amount) : self
	{
		return $this->modifyHours($amount);
	}

	/**
	 * Minus one hour.
	 *
	 * @return self
	 */
	public function minusOneHour() : self
	{
		return $this->minusHours(1);
	}

	/**
	 * Minus a given amount of hours.
	 *
	 * @param  int|float  $amount
	 * @return self
	 */
	public function minusHours($amount) : self
	{
		return $this->modifyHours($amount, true);
	}

	/**
	 * Modify by an amount of hours.
	 *
	 * @param  int  $amount
	 * @param  bool  $invert
	 * @return self
	 */
	protected function modifyHours($amount, bool $invert = false) : self
	{
		if ($this->isFloat($amount))
		{
			return $this->modifyMinutes($amount * 60, $invert);
		}

		$interval = new DateInterval("PT{$amount}H");

		$this->modifyFromInterval($interval, $invert);

		return $this;
	}

	/**
	 * Add one minute.
	 *
	 * @return self
	 */
	public function addOneMinute() : self
	{
		return $this->addMinutes(1);
	}

	/**
	 * Add a given amount of minutes.
	 *
	 * @param  int|float  $amount
	 * @return self
	 */
	public function addMinutes($amount) : self
	{
		return $this->modifyMinutes($amount);
	}

	/**
	 * Minus one minute.
	 *
	 * @return self
	 */
	public function minusOneMinute() : self
	{
		return $this->minusMinutes(1);
	}

	/**
	 * Minus a given amount of minutes.
	 *
	 * @param  int|float  $amount
	 * @return self
	 */
	public function minusMinutes($amount) : self
	{
		return $this->modifyMinutes($amount, true);
	}

	/**
	 * Modify by an amount of minutes.
	 *
	 * @param  int|float  $amount
	 * @param  bool  $invert
	 * @return self
	 */
	protected function modifyMinutes($amount, bool $invert = false) : self
	{
		if ($this->isFloat($amount))
		{
			return $this->modifySeconds($amount * 60, $invert);
		}

		$interval = new DateInterval("PT{$amount}M");

		$this->modifyFromInterval($interval, $invert);

		return $this;
	}

	/**
	 * Add one second.
	 *
	 * @return self
	 */
	public function addOneSecond() : self
	{
		return $this->addSeconds(1);
	}

	/**
	 * Add a given amount of seconds.
	 *
	 * @param  int|float  $amount
	 * @return self
	 */
	public function addSeconds($amount) : self
	{
		return $this->modifySeconds($amount);
	}

	/**
	 * Minus one second.
	 *
	 * @return self
	 */
	public function minusOneSecond() : self
	{
		return $this->minusSeconds(1);
	}

	/**
	 * Minus a given amount of seconds.
	 *
	 * @param  int|float  $amount
	 * @return self
	 */
	public function minusSeconds($amount) : self
	{
		return $this->modifySeconds($amount, true);
	}

	/**
	 * Modify by an amount of seconds.
	 *
	 * @param  int|float  $amount
	 * @param  bool  $invert
	 * @return self
	 */
	protected function modifySeconds($amount, bool $invert = false) : self
	{
		$interval = new DateInterval("PT{$amount}S");

		$this->modifyFromInterval($interval, $invert);

		return $this;
	}

	/**
	 * Add one week.
	 *
	 * @param  int  $amount
	 * @return self
	 */
	public function addOneWeek() : self
	{
		return $this->addWeeks(1);
	}

	/**
	 * Add a given amount of weeks.
	 *
	 * @param  int|float  $amount
	 * @return self
	 */
	public function addWeeks($amount) : self
	{
		return $this->modifyWeeks($amount);
	}

	/**
	 * Minus one week.
	 *
	 * @return self
	 */
	public function minusOneWeek() : self
	{
		return $this->minusWeeks(1);
	}

	/**
	 * Minus a given amount of weeks.
	 *
	 * @param  int|float  $amount
	 * @return self
	 */
	public function minusWeeks($amount) : self
	{
		return $this->modifyWeeks($amount, true);
	}

	/**
	 * Modify by an amount of weeks.
	 *
	 * @param  int  $amount
	 * @param  bool  $invert
	 * @return self
	 */
	protected function modifyWeeks($amount, bool $invert = false) : self
	{
		if ($this->isFloat($amount))
		{
			return $this->modifyDays($amount * 7, $invert);
		}

		$interval = new DateInterval("P{$amount}W");

		$this->modifyFromInterval($interval, $invert);

		return $this;
	}

	/**
	 * Modify from a DateInterval object.
	 *
	 * @param  DateInterval  $interval
	 * @param  bool  $invert
	 * @return self
	 */
	protected function modifyFromInterval(DateInterval $interval, bool $invert = false) : self
	{
		if ($invert)
		{
			$this->sub($interval);
		}
		else
		{
			$this->add($interval);
		}

		return $this;
	}

	/**
	 * Set the timezone.
	 *
	 * @param  string|DateTimeZone  $timezone
	 * @return self
	 */
	public function setTimezone($timezone) : self
	{
		$timezone = $this->parseSuppliedTimezone($timezone);

		parent::setTimezone($timezone);

		return $this;
	}

	/**
	 * Sets the timestamp from a human readable string.
	 *
	 * @param  string  $string
	 * @return self
	 */
	public function setTimestampFromString(string $string) : self
	{
		$this->setTimestamp(strtotime($string));

		return $this;
	}

	/**
	 * Determine if day is a weekday.
	 *
	 * @return bool
	 */
	public function isWeekday() : bool
	{
		$day = $this->getDayOfWeek();

		return ! in_array($day, ['Saturday', 'Sunday']);
	}

	/**
	 * Determine if day is a weekend.
	 *
	 * @return bool
	 */
	public function isWeekend() : bool
	{
		return ! $this->isWeekday();
	}

	/**
	 * Get the difference in years.
	 *
	 * @param null|string|DateTimeInterface  $compare
	 * @return string
	 */
	public function getDifferenceInYears($compare = null)
	{
		if (!empty($compare) AND !($compare instanceof DateTimeInterface) AND !is_string($compare))
		{
			throw new InvalidArgumentException('$compare must be a string or object implemented DateTimeInterface');
		}
		if (!($compare instanceof DateTimeInterface))
		{
			$compare = new self($compare, $this->getTimezone());
		}

		return $this->diff($compare)->format('%r%y');
	}

	/**
	 * Get the difference in months.
	 *
	 * @param null|string|DateTimeInterface  $compare
	 * @return string
	 */
	public function getDifferenceInMonths($compare = null)
	{
		if (!empty($compare) AND !($compare instanceof DateTimeInterface) AND !is_string($compare))
		{
			throw new InvalidArgumentException('$compare must be a string or object implemented DateTimeInterface');
		}
		if (!($compare instanceof DateTimeInterface))
		{
			$compare = new self($compare, $this->getTimezone());
		}

		$difference = $this->diff($compare);

		list($years, $months) = explode(':', $difference->format('%y:%m'));

		return (($years * 12) + $months) * $difference->format('%r1');
	}

	/**
	 * Get the difference in days.
	 *
	 * @param null|string|DateTimeInterface  $compare
	 * @return string
	 */
	public function getDifferenceInDays($compare = null)
	{
		if (!empty($compare) AND !($compare instanceof DateTimeInterface) AND !is_string($compare))
		{
			throw new InvalidArgumentException('$compare must be a string or object implemented DateTimeInterface');
		}
		if (!($compare instanceof DateTimeInterface))
		{
			$compare = new self($compare, $this->getTimezone());
		}

		return $this->diff($compare)->format('%r%a');
	}

	/**
	 * Get the difference in hours.
	 *
	 * @param null|string|DateTimeInterface  $compare
	 * @return string
	 */
	public function getDifferenceInHours($compare = null)
	{
		return $this->getDifferenceInMinutes($compare) / 60;
	}

	/**
	 * Get the difference in minutes.
	 *
	 * @param null|string|DateTimeInterface  $compare
	 * @return string
	 */
	public function getDifferenceInMinutes($compare = null)
	{
		return $this->getDifferenceInSeconds($compare) / 60;
	}

	/**
	 * Get the difference in seconds.
	 *
	 * @param null|string|DateTimeInterface  $compare
	 * @return string
	 */
	public function getDifferenceInSeconds($compare = null)
	{
		if (!empty($compare) AND !($compare instanceof DateTimeInterface) AND !is_string($compare))
		{
			throw new InvalidArgumentException('$compare must be a string or object implemented DateTimeInterface');
		}
		if (!($compare instanceof DateTimeInterface))
		{
			$compare = new self($compare, $this->getTimezone());
		}

		$difference = $this->diff($compare);

		list($days, $hours, $minutes, $seconds) = explode(':', $difference->format('%a:%h:%i:%s'));

		// Add the total amount of seconds in all the days.
		$seconds += ($days * 24 * 60 * 60);

		// Add the total amount of seconds in all the hours.
		$seconds += ($hours * 60 * 60);

		// Add the total amount of seconds in all the minutes.
		$seconds += ($minutes * 60);

		return $seconds * $difference->format('%r1');
	}

	/**
	 * Get a relative date string, e.g., 3 days ago.
	 *
	 * @param null|string|DateTimeInterface  $compare
	 * @return string
	 */
	public function getRelativeDate($compare = null)
	{
		if (!empty($compare) AND !($compare instanceof DateTimeInterface) AND !is_string($compare))
		{
			throw new InvalidArgumentException('$compare must be a string or object implemented DateTimeInterface');
		}
		if (!($compare instanceof DateTimeInterface))
		{
			$compare = new self($compare, $this->getTimezone());
		}

		$units = ['second', 'minute', 'hour', 'day', 'week', 'month', 'year'];
		$values = [60, 60, 24, 7, 4.35, 12];

		// Get the difference between the two timestamps. We'll use this to cacluate the
		// actual time remaining.
		$difference = abs($compare->getTimestamp() - $this->getTimestamp());

		for ($i = 0; $i < count($values) AND $difference >= $values[$i]; $i++)
		{
			$difference = $difference / $values[$i];
		}

		// Round the difference to the nearest whole number.
		$difference = round($difference);

		if ($compare->getTimestamp() < $this->getTimestamp())
		{
			$suffix = 'from now';
		}
		else
		{
			$suffix = 'ago';
		}

		// Get the unit of time we are measuring. We'll then check the difference, if it is not equal
		// to exactly 1 then it's a multiple of the given unit so we'll append an 's'.
		$unit = $units[$i];

		if ($difference != 1)
		{
			$unit .= 's';
		}

		return $difference.' '.$unit.' '.$suffix;
	}

	/**
	 * Get the interval of time between two dates
	 *
	 * @param DateTime|string $date1 First date
	 * @param DateTime|string $date2 Second date
	 * @return mixed Returns an interval object
	 */
	private static function differenceInterval($date1, $date2)
    {
		// Make sure our dates are DateTime objects
		$datetime1 = self::convertToDate($date1);
		$datetime2 = self::convertToDate($date2);

		// If both variables were valid dates...
		if ($datetime1 AND $datetime2)
        {
			// Get the time interval between the two dates
			return $datetime1->diff($datetime2);
        }
		// The dates were invalid... Return false
        return false;
    }

	/**
	 * Get the number of days between two dates
	 *
	 * @param DateTime|string $date1 First date
	 * @param DateTime|string $date2 Second date
	 * @return int|false Returns the number of days or false if invalid dates
	 */
	public static function differenceDays($date1, $date2)
    {
		// Get the difference between the two dates
		$interval = self::differenceInterval($date1, $date2);
		if ($interval)
        {
            // Return the number of days
            return $interval->days;
		}
        // The passed in values were not dates
        return false;
	}

	/**
	 * Get the number of hours between two dates
	 *
	 * @param DateTime|string $date1 First date
	 * @param DateTime|string $date2 Second date
	 * @return int|false Returns the number of hours or false if invalid dates
	 */
	public static function differenceHours($date1, $date2)
    {
		// Get the difference between the two dates
		$interval = self::differenceInterval($date1, $date2);
		if ($interval)
        {
			// Return the number of hours
			return ($interval->days * 24) + $interval->h;
		}
        // The passed in values were not dates
        return false;
    }

	/**
	 * Get the number of minutes between two dates
	 *
	 * @param DateTime|string $date1 First date
	 * @param DateTime|string $date2 Second date
	 * @return int|false Returns the number of minutes or false if invalid dates
	 */
	public static function differenceMinutes($date1, $date2)
    {
		// Get the difference between the two dates
		$interval = self::differenceInterval($date1, $date2);
		if ($interval)
        {
            // Return the number of minutes
			return ((($interval->days * 24) + $interval->h) * 60) + $interval->i;
		}
        // The passed in values were not dates
        return false;
	}

	/**
	 * Get the number of months between two dates
	 *
	 * @param DateTime|string $date1 First date
	 * @param DateTime|string $date2 Second date
	 * @return int|false Returns the number of months or false if invalid dates
	 */
	public static function differenceMonths($date1, $date2)
    {
		// Get the difference between the two dates
		$interval = self::differenceInterval($date1, $date2);
		if ($interval)
        {
			// Return the number of months
			return ($interval->y * 12) + $interval->m;
		}
        // The passed in values were not dates
        return false;
	}

	/**
	 * Get the number of seconds between two dates
	 *
	 * @param DateTime|string $date1 First date
	 * @param DateTime|string $date2 Second date
	 * @return int|false Returns the number of seconds or false if invalid dates
	 */
	public static function differenceSeconds($date1, $date2)
    {
		// Get the difference between the two dates
		$interval = self::differenceInterval($date1, $date2);
		if ($interval) {
			// Return the number of minutes
			return ((((($interval->days * 24) + $interval->h) * 60) +
				$interval->i) * 60)  + $interval->s;
		}
        // The passed in values were not dates
        return false;
	}

	/**
	 * Get the number of years between two dates
	 *
	 * @param DateTime|string $date1 First date
	 * @param DateTime|string $date2 Second date
	 * @return int|false Returns the number of years or false if invalid dates
	 */
	public static function differenceYears($date1, $date2)
    {
		// Get the difference between the two dates
		$interval = self::differenceInterval($date1, $date2);
		if ($interval) {
			// Return the number of years
			return $interval->y;
		}
        // The passed in values were not dates
        return false;
	}

	/**
	 * Converts any English textual datetimes into a date object
	 *
	 * @param string|DateTime $date Date
	 * @param string $timezone [OPTIONAL] Default timezone
	 * @param boolean $forceFixDate [OPTIONAL] Force fixing all dates with dashes (this might be incompatible with some countries and may default to false)
	 * @return DateTime|false Date if valid and false if not
	 */
	public static function convertToDate($date, string $timezone = self::DEFAULT_TIMEZONE, bool $forceFixDate = true)
    {
		// If the input was not a DateTime object
		if (!$date instanceof DateTime)
        {
			// Set the timezone to default
			date_default_timezone_set($timezone);

			// If we need to use the date fix for United States dates
			// and there are no characters as in 02-JAN-03 then...
			if (($forceFixDate OR self::isTimeZoneInCountry($timezone, 'US')) AND is_string($date) AND !preg_match('/[a-z]/i', $date))
            {
				// U.S. dates with '-' do not convert correctly so replace them with '/'
				$datevalue = self::fixUSDateString($date);
			}
            else
            {
                // No fix needed..., Use the date passed in
				$datevalue = $date;
			}

			// Convert the string into a linux time stamp
			$timestamp = strtotime($datevalue);

			// If this was a valid date
			if ($timestamp)
            {
				// Convert the UNIX time stamp into a date object
				$date = DateTime::createFromFormat('U', $timestamp);

			}
            else
            {
                // Not a valid date... This was not a valid date
				$date = false;
			}
		}

		// Make sure the date isn't a converted "0000-00-00 00:00:00"
		if ($date instanceof DateTime)
        {
			if (intval($date->format('Y')) <= 0)
            {
				$date = false;
			}
		}

		// Return the date object or false if invalid
		return $date;
	}

	/**
	 * Get a date string in the format of 2012-12-04.
	 *
	 * @return string
	 */
	public function getDate() : string
	{
		return $this->format('Y-m-d');
	}

	/**
	 * Get a date and time string in the format of 2012-12-04 23:43:27.
	 *
	 * @return string
	 */
	public function getDateTime() : string
	{
		return $this->format('Y-m-d H:i:s');
	}

	/**
	 * Get a date string in the format of Jan 31, 1991.
	 *
	 * @return string
	 */
	public function getShortDate() : string
	{
		return $this->format('M j, Y');
	}

	/**
	 * Get a date string in the format of January 31st, 1991 at 7:45am.
	 *
	 * @return string
	 */
	public function getLongDate() : string
	{
		return $this->format('F jS, Y \a\\t g:ia');
	}

	/**
	 * Get a date string in the format of 07:42:32.
	 *
	 * @return string
	 */
	public function getTime() : string
	{
		return $this->format('H:i:s');
	}

	/**
	 * Get a date string in the default format.
	 *
	 * @return string
	 */
	public function getDefaultDate() : string
	{
		return $this->format($this->defaultDateFormat);
	}

	/**
	 * Set the default date format.
	 *
	 * @param  string  $format
	 * @return self
	 */
	public function setDefaultDateFormat(string $format) : self
	{
		$this->defaultDateFormat = $format;

		return $this;
	}

	/**
	 * Set the starting day of the week, where 0 is Sunday and 1 is Monday.
	 *
	 * @param int|string $weekStartDay
	 * @return self
	 */
	public function setWeekStartDay($weekStartDay) : self
	{
		if (is_numeric($weekStartDay))
		{
			$this->weekStartDay = $weekStartDay;
		}
		else
		{
			$this->weekStartDay = array_search(strtolower($weekStartDay), ['sunday', 'monday']);
		}

		return $this;
	}

	/**
	 * Get the starting day of the week, where 0 is Sunday and 1 is Monday
	 *
	 * @return int
	 */
	public function getWeekStartDay() : int
	{
		return $this->weekStartDay;
	}

	/**
	 * Get a date attribute.
	 *
	 * @param  string  $attribute
	 * @return mixed
	 */
	protected function getDateAttribute(string $attribute)
	{
		switch ($attribute)
		{
			case 'Day':
				return $this->format('d');
			case 'Month':
				return $this->format('m');
			case 'Year':
				return $this->format('Y');
			case 'Hour':
				return $this->format('G');
			case 'Minute':
				return $this->format('i');
			case 'Second':
				return $this->format('s');
			case 'DayOfWeek':
				return $this->format('l');
			case 'DayOfWeekAsNumeric':
				return (7 + $this->format('w') - $this->getWeekStartDay()) % 7;
			case 'DaysInMonth':
				return $this->format('t');
			case 'DayOfYear':
				return $this->format('z');
			case 'DaySuffix':
				return $this->format('S');
			case 'GmtDifference':
				return $this->format('O');
			case 'SecondsSinceEpoch':
				return $this->format('U');
			case 'TimezoneName':
				return $this->getTimezone()->getName();
		}

		throw new InvalidArgumentException('The date attribute ['.$attribute.'] could not be found.');
	}

	/**
	 * Syntactical sugar for determining if date object "is" a condition.
	 *
	 * @param  string  $attribute
	 * @return mixed
	 */
	protected function isDateAttribute(string $attribute)
	{
		switch ($attribute)
		{
			case 'LeapYear':
				return (bool) $this->format('L');
			case 'AmOrPm':
				return $this->format('A');
			case 'DaylightSavings':
				return (bool) $this->format('I');
		}

		throw new InvalidArgumentException('The date attribute ['.$attribute.'] could not be found.');
	}

	/**
	 * Set a date attribute.
	 *
	 * @param  string  $attribute
	 * @param  mixed  $value
	 * @return mixed
	 */
	protected function setDateAttribute(string $attribute, $value)
	{
		switch ($attribute)
		{
			case 'Day':
				return $this->setDate($this->getYear(), $this->getMonth(), $value);
			case 'Month':
				return $this->setDate($this->getYear(), $value, $this->getDay());
			case 'Year':
				return $this->setDate($value, $this->getMonth(), $this->getDay());
			case 'Hour':
				return $this->setTime($value, $this->getMinute(), $this->getSecond());
			case 'Minute':
				return $this->setTime($this->getHour(), $value, $this->getSecond());
			case 'Second':
				return $this->setTime($this->getHour(), $this->getMinute(), $value);
		}

		throw new InvalidArgumentException('The date attribute ['.$attribute.'] could not be set.');
	}

	/**
	 * Alias for ExpressiveDate::equalTo()
	 *
	 * @param string|DateTime $date
	 * @return bool
	 */
	public function sameAs($date) : bool
	{
		return $this->equalTo($date);
	}

	/**
	 * Determine if date is equal to another Expressive Date instance.
	 *
	 * @param string|DateTime $date
	 * @return bool
	 */
	public function equalTo($date) : bool
	{
		if (!($date instanceof DateTime) AND !is_string($date))
		{
			throw new InvalidArgumentException('$date must be a string or DateTime object');
		}
		if (!($date instanceof DateTime))
		{
			$date = self::make($date, $this->getTimezone());
		}

		return $this == $date;
	}

	/**
	 * Determine if date is not equal to another Expressive Date instance.
	 *
	 * @param string|DateTime $date
	 * @return bool
	 */
	public function notEqualTo($date) : bool
	{
		return ! $this->equalTo($date);
	}

	/**
	 * Determine if date is greater than another Expressive Date instance.
	 *
	 * @param string|DateTime $date
	 * @return bool
	 */
	public function greaterThan($date) : bool
	{
		if (!($date instanceof DateTime) AND !is_string($date))
		{
			throw new InvalidArgumentException('$date must be a string or DateTime object');
		}
		if (!($date instanceof DateTime))
		{
			$date = self::make($date, $this->getTimezone());
		}

		return $this > $date;
	}

	/**
	 * Determine if date is less than another Expressive Date instance.
	 *
	 * @param string|DateTime $date
	 * @return bool
	 */
	public function lessThan($date) : bool
	{
		if (!($date instanceof DateTime) AND !is_string($date))
		{
			throw new InvalidArgumentException('$date must be a string or DateTime object');
		}
		if (!($date instanceof DateTime))
		{
			$date = self::make($date, $this->getTimezone());
		}

		return $this < $date;
	}

	/**
	 * Determine if date is greater than or equal to another Expressive Date instance.
	 *
	 * @param string|DateTime $date
	 * @return bool
	 */
	public function greaterOrEqualTo($date) : bool
	{
		if (!($date instanceof DateTime) AND !is_string($date))
		{
			throw new InvalidArgumentException('$date must be a string or DateTime object');
		}
		if (!($date instanceof DateTime))
		{
			$date = self::make($date, $this->getTimezone());
		}

		return $this >= $date;
	}

	/**
	 * Determine if date is less than or equal to another Expressive Date instance.
	 *
	 * @param string|DateTime  $date
	 * @return bool
	 */
	public function lessOrEqualTo($date) : bool
	{
		if (!($date instanceof DateTime) AND !is_string($date))
		{
			throw new InvalidArgumentException('$date must be a string or DateTime object');
		}
		if (!($date instanceof DateTime))
		{
			$date = self::make($date, $this->getTimezone());
		}

		return $this <= $date;
	}

	/**
	 * Dynamically handle calls for date attributes and testers.
	 *
	 * @param  string  $method
	 * @param  array  $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		if (substr($method, 0, 3) == 'get' or substr($method, 0, 3) == 'set')
		{
			$attribute = substr($method, 3);
		}
		elseif (substr($method, 0, 2) == 'is')
		{
			$attribute = substr($method, 2);

			return $this->isDateAttribute($attribute);
		}

		if ( ! isset($attribute))
		{
			throw new InvalidArgumentException('Could not dynamically handle method call ['.$method.']');
		}

		if (substr($method, 0, 3) == 'set')
		{
			return $this->setDateAttribute($attribute, $parameters[0]);
		}

		// If not setting an attribute then we'll default to getting an attribute.
		return $this->getDateAttribute($attribute);
	}

	/**
	 * Return the default date format when casting to string.
	 *
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->getDefaultDate();
	}

	/**
	 * Determine if a given amount is a floating point number.
	 *
	 * @param  int|float  $amount
	 * @return bool
	 */
	protected function isFloat($amount) : bool
	{
		return is_float($amount) AND intval($amount) != $amount;
	}

	/**
	 * Return copy of expressive date object
	 *
	 * @return self
	 */
	public function copy() : self
	{
		return clone $this;
	}
}
