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

namespace dFramework\core\utilities;

use DateTime;
use DateInterval;
use DateTimeZone;

/**
 * Dates
 * This class encapsulates various date and time functionality.
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Utilities
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.3.0
 * @file        /system/core/utilities/Dates.php
 */
class Dates
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
	 * Converts any English textual datetimes into a date object
	 *
	 * @param string|DateTime $date Date
	 * @param string $timezone [OPTIONAL] Default timezone
	 * @param boolean $forceFixDate [OPTIONAL] Force fixing all dates with dashes
	 * (this might be incompatible with some countries and may default to false)
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
	 * Add or subtract an interval of time to a date
	 *
	 * @param DateTime|string $date1 Date to be modified
	 * @param int $interval The amount of time
	 * @param string $type The DateInterval format
	 * @param bool $is_time TRUE if interval is hours, minutes, or seconds
	 * @return DateTime Returns the new date
	 */
	private static function modifyDateInterval($date, int $interval, string $type, bool $is_time) : DateTime
    {
		// Let's make sure we have a date
		if ($date instanceof DateTime)
        {
			// Don't make changes to the original date
			$new_date = clone $date;
		}
        else
        {
			// Convert the date
			$new_date = self::convertToDate($date);
		}
		// If we have a valid date
		if ($new_date)
        {
			if ($is_time)
            {
                // Is this an hour, minute, or second?
				$pre = 'PT';
			}
            else
            {
                // This is a year, month, or day
				$pre = 'P';
			}

			// If the interval of time is negative
			if (intval($interval) < 0)
            {
				// Subtract the interval
				$new_date->sub(new DateInterval($pre . strval($interval * -1) . $type));
			}
            else
            {
				// Add the interval
				$new_date->add(new DateInterval($pre . strval($interval) . $type));
			}
		}

		return $new_date;
	}

	/**
	 * Add days to a date
	 *
	 * @param DateTime|string $date1 Date to be modified
	 * @param int $days The number of days to add (negative subtracts)
	 * @return DateTime Returns the new date
	 */
	public static function addDays($date, int $days) : DateTime
    {
		return self::modifyDateInterval($date, intval($days), 'D', false);
	}

	/**
	 * Add hours to a date
	 *
	 * @param DateTime|string $date1 Date to be modified
	 * @param int $hours The number of hours to add (negative subtracts)
	 * @return DateTime Returns the new date
	 */
	public static function addHours($date, int $hours) : DateTime
    {
		return self::modifyDateInterval($date, intval($hours), 'H', true);
	}

	/**
	 * Add minutes to a date
	 *
	 * @param DateTime|string $date1 Date to be modified
	 * @param int $minutes The number of minutes to add (negative subtracts)
	 * @return DateTime Returns the new date
	 */
	public static function addMinutes($date, int $minutes)
    {
		return self::modifyDateInterval($date, intval($minutes), 'M', true);
	}

	/**
	 * Add months to a date
	 *
	 * @param DateTime|string $date1 Date to be modified
	 * @param int $months The number of months to add (negative subtracts)
	 * @return DateTime Returns the new date
	 */
	public static function addMonths($date, int $months) : DateTime
    {
		return self::modifyDateInterval($date, intval($months), 'M', false);
	}

	/**
	 * Add seconds to a date
	 *
	 * @param DateTime|string $date1 Date to be modified
	 * @param int $seconds The number of seconds to add (negative subtracts)
	 * @return DateTime Returns the new date
	 */
	public static function addSeconds($date, int $seconds) : DateTime
    {
		return self::modifyDateInterval($date, intval($seconds), 'S', true);
	}

	/**
	 * Add years to a date
	 *
	 * @param DateTime|string $date1 Date to be modified
	 * @param int $years The number of years to add (negative subtracts)
	 * @return DateTime Returns the new date
	 */
	public static function addYears($date, int $years) : DateTime
    {
		return self::modifyDateInterval($date, intval($years), 'Y', false);
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
	 * U.S. dates with - do not convert correctly in PHP so replace them with /
	 * (See comments http://php.net/manual/en/function.strtotime.php)
	 *
	 * @param string $date A date string
	 * @return string If the passed in value is a string, returns fixed date
	 *	               otherwise return the value passed in to the function
	 */
	public static function fixUSDateString($date)
    {
		// If the passed in value is a string and there are not alpha
		// characters that hold month names (as in 02-JAN-03) then...
		if (is_string($date) AND !preg_match('/[a-z]/i', $date))
        {
			// Replace '-' with '/'
			return str_replace('-', '/', $date);
		}
        // No fix needed... Use the date passed in
		return $date;
	}

	/**
	 * Formats a date
	 *
	 * @param string|DateTime $date A string or date object
	 * @param string $format A valid PHP date format
	 *                       http://php.net/manual/en/function.date.php
	 *                       'Y-m-d H:i:s' is the MySQL date format
	 * @param string $timezone [OPTIONAL] The timezone to use
	 * @return string|false The date formatted as a string or false if not a date
	 */
	public static function formatDate($date, string $format, string $timezone = self::DEFAULT_TIMEZONE)
    {
		// Convert the string to a date
		$new_date = self::convertToDate($date, $timezone);

		// If the string was successfully converted into a date
		if ($new_date)
        {
			// Format it
			return $new_date->format($format);

		}
        return false;
	}

	/**
	 * Get the age of a person using their birth date
	 *
	 * @param string|DateTime $dob Date string or DateTime object
	 * @param string $timezone Default timezone
	 * @return int The age in number of years
	 */
	public static function getAge($dob, string $timezone = self::DEFAULT_TIMEZONE) : int
    {
		$date     = self::convertToDate($dob, $timezone);
		$now      = new DateTime();
		$interval = $now->diff($date);
		return $interval->y;
	}

	/**
	 * Returns the current year
	 *
	 * @param string $timezone [OPTIONAL] The timezone to use
	 * @return integer The current year formatted as an integer
	 */
	public static function getCurrentYear(string $timezone = self::DEFAULT_TIMEZONE) : int
    {
		return intval(self::formatDate('Today', 'Y', $timezone));
	}

	/**
	 * Returns the last day of this month or for a given date
	 *
	 * @param string|DateTime $date [OPTIONAL] A date
	 * @return int|false The number of the last day of the month
	 */
	public static function getLastDayOfMonth($date = 'Today')
    {
		$datetime = self::convertToDate($date);
		if ($datetime)
        {
			return intval($datetime->format('t'));
		}
        return false;
	}

	/**
	 * Get a list of time zones for a specified country
	 *
	 * @param string $country The name of the country
	 * @return array Returns an array with a list of valid time zones
	 */
	public static function getTimeZonesInCountry(string $country) : array
    {
		// Get an array of all the timezones for the specified country
		return DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, $country);
	}

	/**
	 * Determines if a string contains a valid date
	 *
	 * @param string|DateTime $value The value to inspect
	 * @param string $timezone
	 * @return bool TRUE if the value is a date, FALSE if not
	 */
	public static function isDate($value, string $timezone = self::DEFAULT_TIMEZONE) : bool
    {
		return (self::convertToDate($value, $timezone) instanceof DateTime);
	}

	/**
	 * Determines if a time zone is in the specified country
	 *
	 * @param string $timezone Time zone from http://php.net/manual/en/timezones.php
	 * @param string $country The name of the country
	 * @return bool TRUE if in the country and FALSE if not
	 */
	public static function isTimeZoneInCountry(string $timezone, string $country) : bool
    {
		// Get an array of all the timezones (for example, in the U.S.)
		$timezone_identifiers = array_map('strtolower',
			DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, $country));

		// Determine if the passed in time zone is in that list
		return in_array(strtolower(trim($timezone)), $timezone_identifiers);
	}

	/**
	 * Builds a DateTime object from date parts
	 *
	 * @param integer $day [OPTIONAL] The day or if not specified today
	 * @param integer $month [OPTIONAL] The month or if not specified this month
	 * @param integer $year [OPTIONAL] The year or if not specified this year
	 * @return DateTime|false Returns a DateTime object with the specified date
	 */
	public static function makeDate($day = false, $month = false, $year = false)
    {
		if ($day === false OR $month === false OR $year === false)
        {
			$date_parts  = explode('-', self::convertToDate('Today')->format('d-m-Y'));
			if ($day === false)
            {
				$day = $date_parts[0];
			}
			if ($month === false)
            {
				$month = $date_parts[1];
			}
			if ($year === false) {
				$year =  $date_parts[2];
			}
		}
		return self::convertToDate($year . '/' . $month . '/' . $day);
	}

	/**
	 * Returns the current date and time as a DateTime object or formatted string
	 *
	 * @param string $format [OPTIONAL] If specified, will format the date as a string
	 *                       If not specified, returns a DateTime object
	 *	                     (example: 'Y-m-d H:i:s')
	 * @param string $timezone [OPTIONAL] Default timezone
	 * @return DateTime|string The DateTime object or a formatted string
	 */
	public static function now($format = false, $timezone = self::DEFAULT_TIMEZONE)
    {
		$now = new DateTime();
		$now->setTimeZone(new DateTimeZone($timezone));
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
	 * @param string $timezone [OPTIONAL] Default timezone
	 * @return DateTime|string The DateTime object or a formatted string
	 */
	public static function today($format = false, $timezone = self::DEFAULT_TIMEZONE)
    {
		$today = self::convertToDate('Today', $timezone);
		if ($format)
        {
			return $today->format($format);
		}
        return $today;
	}
}
