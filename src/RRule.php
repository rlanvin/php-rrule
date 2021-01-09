<?php

/**
 * Licensed under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE file.
 *
 * @author Rémi Lanvin <remi@cloudconnected.fr>
 * @link https://github.com/rlanvin/php-rrule
 */

namespace RRule;

/**
 * Check that a variable is not empty.
 *
 * 0 and '0' are considered NOT empty.
 *
 * @param mixed $var Variable to be checked
 * @return bool
 */
function not_empty($var)
{
	return ! empty($var) || $var === 0 || $var === '0';
}

/**
 * Python-like modulo.
 *
 * The % operator in PHP returns the remainder of a / b, but differs from
 * some other languages in that the result will have the same sign as the
 * dividend. For example, -1 % 8 == -1, whereas in some other languages
 * (such as Python) the result would be 7. This function emulates the more
 * correct modulo behavior, which is useful for certain applications such as
 * calculating an offset index in a circular list.
 *
 * @param int $a The dividend.
 * @param int $b The divisor.
 *
 * @return int $a % $b where the result is between 0 and $b
 *   (either 0 <= x < $b
 *     or $b < x <= 0, depending on the sign of $b).
 *
 * @copyright 2006 The Closure Library Authors.
 */
function pymod($a, $b)
{
	$x = $a % $b;

	// If $x and $b differ in sign, add $b to wrap the result to the correct sign.
	return ($x * $b < 0) ? $x + $b : $x;
}

/**
 * Check if a year is a leap year.
 *
 * @param int $year The year to be checked.
 * @return bool
 */
function is_leap_year($year)
{
	if ($year % 4 !== 0) {
		return false;
	}
	if ($year % 100 !== 0) {
		return true;
	}
	if ($year % 400 !== 0) {
		return false;
	}
	return true;
}

/**
 * Implementation of RRULE as defined by RFC 5545 (iCalendar).
 * Heavily based on python-dateutil/rrule
 *
 * Some useful terms to understand the algorithms and variables naming:
 *
 * - "yearday" = day of the year, from 0 to 365 (on leap years) - `date('z')`
 * - "weekday" = day of the week (ISO-8601), from 1 (MO) to 7 (SU) - `date('N')`
 * - "monthday" = day of the month, from 1 to 31
 * - "wkst" = week start, the weekday (1 to 7) which is the first day of week.
 *          Default is Monday (1). In some countries it's Sunday (7).
 * - "weekno" = number of the week in the year (ISO-8601)
 *
 * CAREFUL with this bug: https://bugs.php.net/bug.php?id=62476
 *
 * @link https://tools.ietf.org/html/rfc5545
 * @link https://labix.org/python-dateutil
 */
class RRule implements RRuleInterface
{
	use RRuleTrait;

	const SECONDLY = 7;
	const MINUTELY = 6;
	const HOURLY = 5;
	const DAILY = 4;
	const WEEKLY = 3;
	const MONTHLY = 2;
	const YEARLY = 1;

	/**
	 * Frequency names.
	 * Used internally for conversion but public if a reference list is needed.
	 */
	const FREQUENCIES = array(
		'SECONDLY' => self::SECONDLY,
		'MINUTELY' => self::MINUTELY,
		'HOURLY' => self::HOURLY,
		'DAILY' => self::DAILY,
		'WEEKLY' => self::WEEKLY,
		'MONTHLY' => self::MONTHLY,
		'YEARLY' => self::YEARLY
	);

	/** 
	 * Weekdays numbered from 1 (ISO-8601 or `date('N')`).
	 * Used internally but public if a reference list is needed.
	 */
	const WEEKDAYS = array(
		'MO' => 1,
		'TU' => 2,
		'WE' => 3,
		'TH' => 4,
		'FR' => 5,
		'SA' => 6,
		'SU' => 7
	);

	/**
	 * @var array original rule
	 */
	protected $rule = array(
		'DTSTART' => null,
		'FREQ' => null,
		'UNTIL' => null,
		'COUNT' => null,
		'INTERVAL' => 1,
		'BYSECOND' => null,
		'BYMINUTE' => null,
		'BYHOUR' => null,
		'BYDAY' => null,
		'BYMONTHDAY' => null,
		'BYYEARDAY' => null,
		'BYWEEKNO' => null,
		'BYMONTH' => null,
		'BYSETPOS' => null,
		'WKST' => 'MO'
	);

	// parsed and validated values
	protected $dtstart = null;
	protected $freq = null;
	protected $until = null;
	protected $count = null;
	protected $interval = null;
	protected $bysecond = null;
	protected $byminute = null;
	protected $byhour = null;
	protected $byweekday = null;
	protected $byweekday_nth = null;
	protected $bymonthday = null;
	protected $bymonthday_negative = null;
	protected $byyearday = null;
	protected $byweekno = null;
	protected $bymonth = null;
	protected $bysetpos = null;
	protected $wkst = null;
	protected $timeset = null;

	// cache variables
	protected $total = null;
	protected $cache = array();

///////////////////////////////////////////////////////////////////////////////
// Public interface

	/**
	 * The constructor needs the entire rule at once.
	 * There is no setter after the class has been instanciated,
	 * because in order to validate some BYXXX parts, we need to know
	 * the value of some other parts (FREQ or other BXXX parts).
	 *
	 * @param mixed $parts An assoc array of parts, or a RFC string.
	 */
	public function __construct($parts, $dtstart = null)
	{
		if (is_string($parts)) {
			$parts = RfcParser::parseRRule($parts, $dtstart);
			$parts = array_change_key_case($parts, CASE_UPPER);
		}
		else {
			if ($dtstart) {
				throw new \InvalidArgumentException('$dtstart argument has no effect if not constructing from a string');
			}
			if (is_array($parts)) {
				$parts = array_change_key_case($parts, CASE_UPPER);
			}
			else {
				throw new \InvalidArgumentException(sprintf(
					'The first argument must be a string or an array (%s provided)',
					gettype($parts)
				));
			}
		}

		// validate extra parts
		$unsupported = array_diff_key($parts, $this->rule);
		if (! empty($unsupported)) {
			throw new \InvalidArgumentException(
				'Unsupported parameter(s): '
				.implode(',',array_keys($unsupported))
			);
		}

		$parts = array_merge($this->rule, $parts);
		$this->rule = $parts; // save original rule

		// WKST
		$parts['WKST'] = strtoupper($parts['WKST']);
		if (! array_key_exists($parts['WKST'], self::WEEKDAYS)) {
			throw new \InvalidArgumentException(
				'The WKST rule part must be one of the following: '
				.implode(', ',array_keys(self::WEEKDAYS))
			);
		}
		$this->wkst = self::WEEKDAYS[$parts['WKST']];

		// FREQ
		if (is_integer($parts['FREQ'])) {
			if ($parts['FREQ'] > self::SECONDLY || $parts['FREQ'] < self::YEARLY) {
				throw new \InvalidArgumentException(
					'The FREQ rule part must be one of the following: '
					.implode(', ',array_keys(self::FREQUENCIES))
				);
			}
			$this->freq = $parts['FREQ'];
		}
		else { // string
			$parts['FREQ'] = strtoupper($parts['FREQ']);
			if (! array_key_exists($parts['FREQ'], self::FREQUENCIES)) {
				throw new \InvalidArgumentException(
					'The FREQ rule part must be one of the following: '
					.implode(', ',array_keys(self::FREQUENCIES))
				);
			}
			$this->freq = self::FREQUENCIES[$parts['FREQ']];
		}

		// INTERVAL
		if (filter_var($parts['INTERVAL'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) === false) {
			throw new \InvalidArgumentException(
				'The INTERVAL rule part must be a positive integer (> 0)'
			);
		}
		$this->interval = (int) $parts['INTERVAL'];

		// DTSTART
		if (not_empty($parts['DTSTART'])) {
			try {
				$this->dtstart = self::parseDate($parts['DTSTART']);
			} catch (\Exception $e) {
				throw new \InvalidArgumentException(
					'Failed to parse DTSTART ; it must be a valid date, timestamp or \DateTime object'
				);
			}
		} 
		else {
			$this->dtstart = new \DateTime(); // for PHP 7.1+ this contains microseconds which causes many problems
			if (version_compare(PHP_VERSION, '7.1.0') >= 0) {
				// remove microseconds
				$this->dtstart->setTime(
					$this->dtstart->format('H'),
					$this->dtstart->format('i'),
					$this->dtstart->format('s'),
					0
				);
			}
		}

		// UNTIL (optional)
		if (not_empty($parts['UNTIL'])) {
			try {
				$this->until = self::parseDate($parts['UNTIL']);
			} catch (\Exception $e) {
				throw new \InvalidArgumentException(
					'Failed to parse UNTIL ; it must be a valid date, timestamp or \DateTime object'
				);
			}
		}

		// COUNT (optional)
		if (not_empty($parts['COUNT'])) {
			if (filter_var($parts['COUNT'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) === false) {
				throw new \InvalidArgumentException('COUNT must be a positive integer (> 0)');
			}
			$this->count = (int) $parts['COUNT'];
		}

		if ($this->until && $this->count) {
			throw new \InvalidArgumentException('The UNTIL or COUNT rule parts MUST NOT occur in the same rule');
		}

		// infer necessary BYXXX rules from DTSTART, if not provided
		if (! (not_empty($parts['BYWEEKNO']) || not_empty($parts['BYYEARDAY']) || not_empty($parts['BYMONTHDAY']) || not_empty($parts['BYDAY']))) {
			switch ($this->freq) {
				case self::YEARLY:
					if (! not_empty($parts['BYMONTH'])) {
						$parts['BYMONTH'] = array((int) $this->dtstart->format('m'));
					}
					$parts['BYMONTHDAY'] = array((int) $this->dtstart->format('j'));
					break;
				case self::MONTHLY:
					$parts['BYMONTHDAY'] = array((int) $this->dtstart->format('j'));
					break;
				case self::WEEKLY:
					$parts['BYDAY'] = array(array_search($this->dtstart->format('N'), self::WEEKDAYS));
					break;
			}
		}

		// BYDAY (translated to byweekday for convenience)
		if (not_empty($parts['BYDAY'])) {
			if (! is_array($parts['BYDAY'])) {
				$parts['BYDAY'] = explode(',',$parts['BYDAY']);
			}
			$this->byweekday = array();
			$this->byweekday_nth = array();
			foreach ($parts['BYDAY'] as $value) {
				$value = trim(strtoupper($value));
				$valid = preg_match('/^([+-]?[0-9]+)?([A-Z]{2})$/', $value, $matches);
				if (! $valid || (not_empty($matches[1]) && ($matches[1] == 0 || $matches[1] > 53 || $matches[1] < -53)) || ! array_key_exists($matches[2], self::WEEKDAYS)) {
					throw new \InvalidArgumentException('Invalid BYDAY value: '.$value);
				}

				if ($matches[1]) {
					$this->byweekday_nth[] = array(self::WEEKDAYS[$matches[2]], (int)$matches[1]);
				}
				else {
					$this->byweekday[] = self::WEEKDAYS[$matches[2]];
				}
			}

			if (! empty($this->byweekday_nth)) {
				if (! ($this->freq === self::MONTHLY || $this->freq === self::YEARLY)) {
					throw new \InvalidArgumentException('The BYDAY rule part MUST NOT be specified with a numeric value when the FREQ rule part is not set to MONTHLY or YEARLY.');
				}
				if ($this->freq === self::YEARLY && not_empty($parts['BYWEEKNO'])) {
					throw new \InvalidArgumentException('The BYDAY rule part MUST NOT be specified with a numeric value with the FREQ rule part set to YEARLY when the BYWEEKNO rule part is specified.');
				}
			}
		}

		// The BYMONTHDAY rule part specifies a COMMA-separated list of days
		// of the month.  Valid values are 1 to 31 or -31 to -1.  For
		// example, -10 represents the tenth to the last day of the month.
		// The BYMONTHDAY rule part MUST NOT be specified when the FREQ rule
		// part is set to WEEKLY.
		if (not_empty($parts['BYMONTHDAY'])) {
			if ($this->freq === self::WEEKLY) {
				throw new \InvalidArgumentException('The BYMONTHDAY rule part MUST NOT be specified when the FREQ rule part is set to WEEKLY.');
			}

			if (! is_array($parts['BYMONTHDAY'])) {
				$parts['BYMONTHDAY'] = explode(',',$parts['BYMONTHDAY']);
			}

			$this->bymonthday = array();
			$this->bymonthday_negative = array();
			foreach ($parts['BYMONTHDAY'] as $value) {
				if (!$value || filter_var($value, FILTER_VALIDATE_INT, array('options' => array('min_range' => -31, 'max_range' => 31))) === false) {
					throw new \InvalidArgumentException('Invalid BYMONTHDAY value: '.$value.' (valid values are 1 to 31 or -31 to -1)');
				}
				$value = (int) $value;
				if ($value < 0) {
					$this->bymonthday_negative[] = $value;
				}
				else {
					$this->bymonthday[] = $value;
				}
			}
		}

		if (not_empty($parts['BYYEARDAY'])) {
			if ($this->freq === self::DAILY || $this->freq === self::WEEKLY || $this->freq === self::MONTHLY) {
				throw new \InvalidArgumentException('The BYYEARDAY rule part MUST NOT be specified when the FREQ rule part is set to DAILY, WEEKLY, or MONTHLY.');
			}

			if (! is_array($parts['BYYEARDAY'])) {
				$parts['BYYEARDAY'] = explode(',',$parts['BYYEARDAY']);
			}

			$this->bysetpos = array();
			foreach ($parts['BYYEARDAY'] as $value) {
				if (! $value || filter_var($value, FILTER_VALIDATE_INT, array('options' => array('min_range' => -366, 'max_range' => 366))) === false) {
					throw new \InvalidArgumentException('Invalid BYSETPOS value: '.$value.' (valid values are 1 to 366 or -366 to -1)');
				}

				$this->byyearday[] = (int) $value;
			}
		}

		// BYWEEKNO
		if (not_empty($parts['BYWEEKNO'])) {
			if ($this->freq !== self::YEARLY) {
				throw new \InvalidArgumentException('The BYWEEKNO rule part MUST NOT be used when the FREQ rule part is set to anything other than YEARLY.');
			}

			if (! is_array($parts['BYWEEKNO'])) {
				$parts['BYWEEKNO'] = explode(',',$parts['BYWEEKNO']);
			}

			$this->byweekno = array();
			foreach ($parts['BYWEEKNO'] as $value) {
				if (! $value || filter_var($value, FILTER_VALIDATE_INT, array('options' => array('min_range' => -53, 'max_range' => 53))) === false) {
					throw new \InvalidArgumentException('Invalid BYWEEKNO value: '.$value.' (valid values are 1 to 53 or -53 to -1)');
				}
				$this->byweekno[] = (int) $value;
			}
		}

		// The BYMONTH rule part specifies a COMMA-separated list of months
		// of the year.  Valid values are 1 to 12.
		if (not_empty($parts['BYMONTH'])) {
			if (! is_array($parts['BYMONTH'])) {
				$parts['BYMONTH'] = explode(',',$parts['BYMONTH']);
			}

			$this->bymonth = array();
			foreach ($parts['BYMONTH'] as $value) {
				if (filter_var($value, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 12))) === false) {
					throw new \InvalidArgumentException('Invalid BYMONTH value: '.$value);
				}
				$this->bymonth[] = (int) $value;
			}
		}

		if (not_empty($parts['BYSETPOS'])) {
			if (! (not_empty($parts['BYWEEKNO']) || not_empty($parts['BYYEARDAY'])
				|| not_empty($parts['BYMONTHDAY']) || not_empty($parts['BYDAY'])
				|| not_empty($parts['BYMONTH']) || not_empty($parts['BYHOUR'])
				|| not_empty($parts['BYMINUTE']) || not_empty($parts['BYSECOND']))) {
				throw new \InvalidArgumentException('The BYSETPOS rule part MUST only be used in conjunction with another BYxxx rule part.');
			}

			if (! is_array($parts['BYSETPOS'])) {
				$parts['BYSETPOS'] = explode(',',$parts['BYSETPOS']);
			}

			$this->bysetpos = array();
			foreach ($parts['BYSETPOS'] as $value) {
				if (! $value || filter_var($value, FILTER_VALIDATE_INT, array('options' => array('min_range' => -366, 'max_range' => 366))) === false) {
					throw new \InvalidArgumentException('Invalid BYSETPOS value: '.$value.' (valid values are 1 to 366 or -366 to -1)');
				}

				$this->bysetpos[] = (int) $value;
			}
		}

		if (not_empty($parts['BYHOUR'])) {
			if (! is_array($parts['BYHOUR'])) {
				$parts['BYHOUR'] = explode(',',$parts['BYHOUR']);
			}

			$this->byhour = array();
			foreach ($parts['BYHOUR'] as $value) {
				if (filter_var($value, FILTER_VALIDATE_INT, array('options' => array('min_range' => 0, 'max_range' => 23))) === false) {
					throw new \InvalidArgumentException('Invalid BYHOUR value: '.$value);
				}
				$this->byhour[] = (int) $value;
			}

			sort($this->byhour);
		}
		elseif ($this->freq < self::HOURLY) {
			$this->byhour = array((int) $this->dtstart->format('G'));
		}

		if (not_empty($parts['BYMINUTE'])) {
			if (! is_array($parts['BYMINUTE'])) {
				$parts['BYMINUTE'] = explode(',',$parts['BYMINUTE']);
			}

			$this->byminute = array();
			foreach ($parts['BYMINUTE'] as $value) {
				if (filter_var($value, FILTER_VALIDATE_INT, array('options' => array('min_range' => 0, 'max_range' => 59))) === false) {
					throw new \InvalidArgumentException('Invalid BYMINUTE value: '.$value);
				}
				$this->byminute[] = (int) $value;
			}
			sort($this->byminute);
		}
		elseif ($this->freq < self::MINUTELY) {
			$this->byminute = array((int) $this->dtstart->format('i'));
		}

		if (not_empty($parts['BYSECOND'])) {
			if (! is_array($parts['BYSECOND'])) {
				$parts['BYSECOND'] = explode(',',$parts['BYSECOND']);
			}

			$this->bysecond = array();
			foreach ($parts['BYSECOND'] as $value) {
				// yes, "60" is a valid value, in (very rare) cases on leap seconds
				//  December 31, 2005 23:59:60 UTC is a valid date...
				// so is 2012-06-30T23:59:60UTC
				if (filter_var($value, FILTER_VALIDATE_INT, array('options' => array('min_range' => 0, 'max_range' => 60))) === false) {
					throw new \InvalidArgumentException('Invalid BYSECOND value: '.$value);
				}
				$this->bysecond[] = (int) $value;
			}
			sort($this->bysecond);
		}
		elseif ($this->freq < self::SECONDLY) {
			$this->bysecond = array((int) $this->dtstart->format('s'));
		}

		if ($this->freq < self::HOURLY) {
			// for frequencies DAILY, WEEKLY, MONTHLY AND YEARLY, we can build
			// an array of every time of the day at which there should be an
			// occurrence - default, if no BYHOUR/BYMINUTE/BYSECOND are provided
			// is only one time, and it's the DTSTART time. This is a cached version
			// if you will, since it'll never change at these frequencies
			$this->timeset = array();
			foreach ($this->byhour as $hour) {
				foreach ($this->byminute as $minute) {
					foreach ($this->bysecond as $second) {
						$this->timeset[] = array($hour,$minute,$second);
					}
				}
			}
		}
	}

	/**
	 * Return the internal rule array, as it was passed to the constructor.
	 *
	 * @return array
	 */
	public function getRule()
	{
		return $this->rule;
	}

	/**
	 * Magic string converter.
	 *
	 * @see RRule::rfcString()
	 * @return string a rfc string
	 */
	public function __toString()
	{
		return $this->rfcString();
	}

	/**
	 * Format a rule according to RFC 5545
	 *
	 * @param bool $include_timezone Wether to generate a rule with timezone identifier on DTSTART (and UNTIL) or not.
	 * @return string
	 */
	public function rfcString($include_timezone = true)
	{
		$str = '';
		if ($this->rule['DTSTART']) {
			if (! $include_timezone) {
				$str = sprintf(
					"DTSTART:%s\nRRULE:",
					$this->dtstart->format('Ymd\THis')
				);
			}
			else {
				$dtstart = clone $this->dtstart;
				$timezone_name = $dtstart->getTimeZone()->getName();
				if (strpos($timezone_name,':') !== false) {
					// handle unsupported timezones like "+02:00"
					// we convert them to UTC to generate a valid string
					// note: there is possibly other weird timezones out there that we should catch
					$dtstart->setTimezone(new \DateTimeZone('UTC'));
					$timezone_name = 'UTC';
				}
				if (in_array($timezone_name, array('UTC','GMT','Z'))) {
					$str = sprintf(
						"DTSTART:%s\nRRULE:",
						$dtstart->format('Ymd\THis\Z')
					);
				}
				else {
					$str = sprintf(
						"DTSTART;TZID=%s:%s\nRRULE:",
						$timezone_name,
						$dtstart->format('Ymd\THis')
					);
				}
			}
		}

		$parts = array();
		foreach ($this->rule as $key => $value) {
			if ($key === 'DTSTART') {
				continue;
			}
			if ($key === 'INTERVAL' && $value == 1) {
				continue;
			}
			if ($key === 'WKST' && $value === 'MO') {
				continue;
			}
			if ($key === 'UNTIL' && $value) {
				if (! $include_timezone) {
					$tmp = clone $this->until;
					// put until on the same timezone as DTSTART
					$tmp->setTimeZone($this->dtstart->getTimezone());
					$parts[] = 'UNTIL='.$tmp->format('Ymd\THis');
				}
				else {
					// according to the RFC, UNTIL must be in UTC
					$tmp = clone $this->until;
					$tmp->setTimezone(new \DateTimeZone('UTC'));
					$parts[] = 'UNTIL='.$tmp->format('Ymd\THis\Z');
				}
				continue;
			}
			if ($key === 'FREQ' && $value && !array_key_exists($value, self::FREQUENCIES)) {
				$frequency_key = array_search($value, self::FREQUENCIES);
				if ($frequency_key !== false) {
					$value = $frequency_key;
				}
			}
			if ($value !== NULL) {
				if (is_array($value)) {
					$value = implode(',',$value);
				}
				$parts[] = strtoupper(str_replace(' ','',"$key=$value"));
			}
		}
		$str .= implode(';',$parts);

		return $str;
	}

	/**
	 * Take a RFC 5545 string and returns an array (to be given to the constructor)
	 *
	 * @param string $string The rule to be parsed
	 * @return array
	 *
	 * @throws \InvalidArgumentException on error
	 */
	static public function parseRfcString($string)
	{
		trigger_error('parseRfcString() is deprecated - use new RRule(), RRule::createFromRfcString() or \RRule\RfcParser::parseRRule() if necessary',E_USER_DEPRECATED);
		return RfcParser::parseRRule($string);
	}

	/**
	 * Take a RFC 5545 string and returns either a RRule or a RSet.
	 *
	 * @param string $string The RFC string
	 * @param bool $force_rset Force a RSet to be returned.
	 * @return RRule|RSet
	 *
	 * @throws \InvalidArgumentException on error
	 */
	static public function createFromRfcString($string, $force_rset = false)
	{
		$class = '\RRule\RSet';

		if (! $force_rset) {
			// try to detect if we have a RRULE or a set
			$string = strtoupper($string);
			$nb_rrule = substr_count($string, 'RRULE');
			if ($nb_rrule == 0) {
				$class = '\RRule\RRule';
			}
			elseif ($nb_rrule > 1) {
				$class = '\RRule\RSet';
			}
			else {
				$class = '\RRule\RRule';
				if (strpos($string, 'EXDATE') !== false ||  strpos($string, 'RDATE') !== false ||  strpos($string, 'EXRULE') !== false) {
					$class = '\RRule\RSet';
				}
			}
		}

		return new $class($string);
	}

	/**
	 * Clear the cache.
	 *
	 * It isn't recommended to use this method while iterating.
	 *
	 * @return $this
	 */
	public function clearCache()
	{
		$this->total = null;
		$this->cache = array();
		return $this;
	}

///////////////////////////////////////////////////////////////////////////////
// RRule interface

	/**
	 * Return true if the rrule has an end condition, false otherwise
	 *
	 * @return bool
	 */
	public function isFinite()
	{
		return $this->count || $this->until;
	}

	/**
	 * Return true if the rrule has no end condition (infite)
	 *
	 * @return bool
	 */
	public function isInfinite()
	{
		return ! $this->count && ! $this->until;
	}

	/**
	 * Return true if $date is an occurrence.
	 *
	 * This method will attempt to determine the result programmatically.
	 * However depending on the BYXXX rule parts that have been set, it might
	 * not always be possible. As a last resort, this method will loop
	 * through all occurrences until $date. This will incurr some performance
	 * penalty.
	 *
	 * @param mixed $date
	 * @return bool
	 */
	public function occursAt($date)
	{
		$date = self::parseDate($date);
		// convert timezone to dtstart timezone for comparison
		$date->setTimezone($this->dtstart->getTimezone());

		if (in_array($date, $this->cache)) {
			// in the cache (whether cache is complete or not)
			return true;
		}
		elseif ($this->total !== null) {
			// cache complete and not in cache
			return false;
		}

		// let's start with the obvious
		if ($date < $this->dtstart || ($this->until && $date > $this->until)) {
			return false;
		}

		// now the BYXXX rules (expect BYSETPOS)
		if ($this->byhour && ! in_array($date->format('G'), $this->byhour)) {
			return false;
		}
		if ($this->byminute && ! in_array((int) $date->format('i'), $this->byminute)) {
			return false;
		}
		if ($this->bysecond && ! in_array((int) $date->format('s'), $this->bysecond)) {
			return false;
		}

		// we need some more variables before we continue
		list($year, $month, $day, $yearday, $weekday) = explode(' ',$date->format('Y n j z N'));
		$masks = array();
		$masks['weekday_of_1st_yearday'] = date_create($year.'-01-01 00:00:00')->format('N');
		$masks['yearday_to_weekday'] = array_slice(self::WEEKDAY_MASK, $masks['weekday_of_1st_yearday']-1);
		if (is_leap_year($year)) {
			$masks['year_len'] = 366;
			$masks['last_day_of_month'] = self::LAST_DAY_OF_MONTH_366;
		}
		else {
			$masks['year_len'] = 365;
			$masks['last_day_of_month'] = self::LAST_DAY_OF_MONTH;
		}
		$month_len = $masks['last_day_of_month'][$month] - $masks['last_day_of_month'][$month-1];

		if ($this->bymonth && ! in_array($month, $this->bymonth)) {
			return false;
		}

		if ($this->bymonthday || $this->bymonthday_negative) {
			$monthday_negative = -1 * ($month_len - $day + 1);

			if (! in_array($day, $this->bymonthday) && ! in_array($monthday_negative, $this->bymonthday_negative)) {
				return false;
			}
		}

		if ($this->byyearday) {
			// caution here, yearday starts from 0 !
			$yearday_negative = -1*($masks['year_len'] - $yearday);

			if (! in_array($yearday+1, $this->byyearday) && ! in_array($yearday_negative, $this->byyearday)) {
				return false;
			}
		}

		if ($this->byweekday || $this->byweekday_nth) {
			// we need to summon some magic here
			$this->buildNthWeekdayMask($year, $month, $day, $masks);

			if (! in_array($weekday, $this->byweekday) && ! isset($masks['yearday_is_nth_weekday'][$yearday])) {
				return false;
			}
		}

		if ($this->byweekno) {
			// more magic
			$this->buildWeeknoMask($year, $month, $day, $masks);
			if (! isset($masks['yearday_is_in_weekno'][$yearday])) {
				return false;
			}
		}

		// so now we have exhausted all the BYXXX rules (exept bysetpos),
		// we still need to consider frequency and interval
		list($start_year, $start_month) = explode('-',$this->dtstart->format('Y-m'));
		switch ($this->freq) {
			case self::YEARLY:
				if (($year - $start_year) % $this->interval !== 0) {
					return false;
				}
				break;
			case self::MONTHLY:
				// we need to count the number of months elapsed
				$diff = (12 - $start_month) + 12*($year - $start_year - 1) + $month;

				if (($diff % $this->interval) !== 0) {
					return false;
				}
				break;
			case self::WEEKLY:
				// count nb of days and divide by 7 to get number of weeks
				// we add some days to align dtstart with wkst
				$diff = $date->diff($this->dtstart);
				$diff = (int) (($diff->days + pymod($this->dtstart->format('N') - $this->wkst,7)) / 7);
				if ($diff % $this->interval !== 0) {
					return false;
				}
				break;
			case self::DAILY:
				// count nb of days
				$diff = $date->diff($this->dtstart);
				if ($diff->days % $this->interval !== 0) {
					return false;
				}
				break;
			// XXX: I'm not sure the 3 formulas below take the DST into account...
			case self::HOURLY:
				$diff = $date->diff($this->dtstart);
				$diff = $diff->h + $diff->days * 24;
				if ($diff % $this->interval !== 0) {
					return false;
				}
				break;
			case self::MINUTELY:
				$diff = $date->diff($this->dtstart);
				$diff  = $diff->i + $diff->h * 60 + $diff->days * 1440;
				if ($diff % $this->interval !== 0) {
					return false;
				}
				break;
			case self::SECONDLY:
				$diff = $date->diff($this->dtstart);
				// XXX does not account for leap second (should it?)
				$diff  = $diff->s + $diff->i * 60 + $diff->h * 3600 + $diff->days * 86400;
				if ($diff % $this->interval !== 0) {
					return false;
				}
				break;
			default:
				throw new \Exception('Unimplemented frequency');
		}

		// now we are left with 2 rules BYSETPOS and COUNT
		//
		// - I think BYSETPOS *could* be determined without loooping by considering
		// the current set, calculating all the occurrences of the current set
		// and determining the position of $date in the result set.
		// However I'm not convinced it's worth it.
		//
		// - I don't see any way to determine COUNT programmatically, because occurrences
		// might sometimes be dropped (e.g. a 29 Feb on a normal year, or during
		// the switch to DST) and not counted in the final set

		if (! $this->count && ! $this->bysetpos) {
			return true;
		}

		// so... as a fallback we have to loop
		foreach ($this as $occurrence) {
			if ($occurrence == $date) {
				return true; // lucky you!
			}
			if ($occurrence > $date) {
				break;
			}
		}

		// we ended the loop without finding
		return false; 
	}

///////////////////////////////////////////////////////////////////////////////
// ArrayAccess interface

	/**
	 * @internal
	 */
	public function offsetExists($offset)
	{
		return is_numeric($offset) && $offset >= 0 && ! is_float($offset) && $offset < count($this);
	}

	/**
	 * @internal
	 */
	public function offsetGet($offset)
	{
		if (! is_numeric($offset) || $offset < 0 || is_float($offset)) {
			throw new \InvalidArgumentException('Illegal offset type: '.gettype($offset));
		}

		if (isset($this->cache[$offset])) {
			// found in cache
			return clone $this->cache[$offset];
		}
		elseif ($this->total !== null) {
			// cache complete and not found in cache
			return null;
		}

		// not in cache and cache not complete, we have to loop to find it
		$i = 0;
		foreach ($this as $occurrence) {
			if ($i == $offset) {
				return $occurrence;
			}
			$i++;
			if ($i > $offset) {
				break;
			}
		}
		return null;
	}

	/**
	 * @internal
	 */
	public function offsetSet($offset, $value)
	{
		throw new \LogicException('Setting a Date in a RRule is not supported');
	}

	/**
	 * @internal
	 */
	public function offsetUnset($offset)
	{
		throw new \LogicException('Unsetting a Date in a RRule is not supported');
	}

///////////////////////////////////////////////////////////////////////////////
// Countable interface

	/**
	 * Returns the number of occurrences in this rule. It will have go
	 * through the whole recurrence, if this hasn't been done before, which
	 * introduces a performance penality.
	 *
	 * @return int
	 */
	public function count()
	{
		if ($this->isInfinite()) {
			throw new \LogicException('Cannot count an infinite recurrence rule.');
		}

		if ($this->total === null) {
			foreach ($this as $occurrence) {}
		}

		return $this->total;
	}

///////////////////////////////////////////////////////////////////////////////
// Internal methods
// where all the magic happens

	/**
	 * Return an array of days of the year (numbered from 0 to 365)
	 * of the current timeframe (year, month, week, day) containing the current date
	 *
	 * @param int $year
	 * @param int $month
	 * @param int $day
	 * @param array $masks
	 * @return array
	 */
	protected function getDaySet($year, $month, $day, array $masks)
	{
		switch ($this->freq) {
			case self::YEARLY:
				return range(0,$masks['year_len']-1);

			case self::MONTHLY:
				$start = $masks['last_day_of_month'][$month-1];
				$stop = $masks['last_day_of_month'][$month];
				return range($start, $stop - 1);

			case self::WEEKLY:
				// on first iteration, the first week will not be complete
				// we don't backtrack to the first day of the week, to avoid
				// crossing year boundary in reverse (i.e. if the week started
				// during the previous year), because that would generate
				// negative indexes (which would not work with the masks)
				$set = array();
				$i = (int) date_create($year.'-'.$month.'-'.$day.' 00:00:00')->format('z');
				$start = $i;
				for ($j = 0; $j < 7; $j++) {
					$set[] = $i;
					$i += 1;
					if ($masks['yearday_to_weekday'][$i] == $this->wkst) {
						break;
					}
				}
				return $set;

			case self::DAILY:
			case self::HOURLY:
			case self::MINUTELY:
			case self::SECONDLY:
				$i = (int) date_create($year.'-'.$month.'-'.$day.' 00:00:00')->format('z');
				return array($i);
		}
	}

	/**
	 * Calculate the yeardays corresponding to each Nth weekday
	 * (in BYDAY rule part).
	 *
	 * For example, in Jan 1998, in a MONTHLY interval, "1SU,-1SU" (first Sunday
	 * and last Sunday) would be transformed into [3=>true,24=>true] because
	 * the first Sunday of Jan 1998 is yearday 3 (counting from 0) and the
	 * last Sunday of Jan 1998 is yearday 24 (counting from 0).
	 *
	 * @param int $year (not used)
	 * @param int $month
	 * @param int $day (not used)
	 * @param array $masks
	 *
	 * @return null (modifies $masks parameter)
	 */
	protected function buildNthWeekdayMask($year, $month, $day, array & $masks)
	{
		$masks['yearday_is_nth_weekday'] = array();

		if ($this->byweekday_nth) {
			$ranges = array();
			if ($this->freq == self::YEARLY) {
				if ($this->bymonth) {
					foreach ($this->bymonth as $bymonth) {
						$ranges[] = array(
							$masks['last_day_of_month'][$bymonth - 1],
							$masks['last_day_of_month'][$bymonth] - 1
						);
					}
				}
				else {
					$ranges = array(array(0, $masks['year_len'] - 1));
				}
			}
			elseif ($this->freq == self::MONTHLY) {
				$ranges[] = array(
					$masks['last_day_of_month'][$month - 1],
					$masks['last_day_of_month'][$month] - 1
				);
			}

			if ($ranges) {
				// Weekly frequency won't get here, so we may not
				// care about cross-year weekly periods.
				foreach ($ranges as $tmp) {
					list($first, $last) = $tmp;
					foreach ($this->byweekday_nth as $tmp) {
						list($weekday, $nth) = $tmp;
						if ($nth < 0) {
							$i = $last + ($nth + 1) * 7;
							$i = $i - pymod($masks['yearday_to_weekday'][$i] - $weekday, 7);
						}
						else {
							$i = $first + ($nth - 1) * 7;
							$i = $i + (7 - $masks['yearday_to_weekday'][$i] + $weekday) % 7;
						}

						if ($i >= $first && $i <= $last) {
							$masks['yearday_is_nth_weekday'][$i] = true;
						}
					}
				}
			}
		}
	}

	/**
	 * Calculate the yeardays corresponding to the week number
	 * (in the WEEKNO rule part).
	 *
	 * Because weeks can cross year boundaries (that is, week #1 can start the
	 * previous year, and week 52/53 can continue till the next year), the
	 * algorithm is quite long.
	 *
	 * @param int $year
	 * @param int $month (not used)
	 * @param int $day (not used)
	 * @param array $masks
	 *
	 * @return null (modifies $masks)
	 */
	protected function buildWeeknoMask($year, $month, $day, array & $masks)
	{
		$masks['yearday_is_in_weekno'] = array();

		// calculate the index of the first wkst day of the year
		// 0 means the first day of the year is the wkst day (e.g. wkst is Monday and Jan 1st is a Monday)
		// n means there is n days before the first wkst day of the year.
		// if n >= 4, this is the first day of the year (even though it started the year before)
		$first_wkst = (7 - $masks['weekday_of_1st_yearday'] + $this->wkst) % 7;
		if($first_wkst >= 4) {
			$first_wkst_offset = 0;
			// Number of days in the year, plus the days we got from last year.
			$nb_days = $masks['year_len'] + $masks['weekday_of_1st_yearday'] - $this->wkst;
			// $nb_days = $masks['year_len'] + pymod($masks['weekday_of_1st_yearday'] - $this->wkst,7);
		}
		else {
			$first_wkst_offset = $first_wkst;
			// Number of days in the year, minus the days we left in last year.
			$nb_days = $masks['year_len'] - $first_wkst;
		}
		$nb_weeks = (int) ($nb_days / 7) + (int) (($nb_days % 7) / 4);

		// alright now we now when the first week starts
		// and the number of weeks of the year
		// so we can generate a map of every yearday that are in the weeks
		// specified in byweekno
		foreach ($this->byweekno as $n) {
			if ($n < 0) {
				$n = $n + $nb_weeks + 1;
			}
			if ($n <= 0 || $n > $nb_weeks) {
				continue;
			}
			if ($n > 1) {
				$i = $first_wkst_offset + ($n - 1) * 7;
				if ($first_wkst_offset != $first_wkst) {
					// if week #1 started the previous year
					// realign the start of the week
					$i = $i - (7 - $first_wkst);
				}
			}
			else {
				$i = $first_wkst_offset;
			}

			// now add 7 days into the resultset, stopping either at 7 or
			// if we reach wkst before (in the case of short first week of year)
			for ($j = 0; $j < 7; $j++) {
				$masks['yearday_is_in_weekno'][$i] = true;
				$i = $i + 1;
				if ($masks['yearday_to_weekday'][$i] == $this->wkst) {
					break;
				}
			}
		}

		// if we asked for week #1, it's possible that the week #1 of next year
		// already started this year. Therefore we need to return also the matching
		// days of next year.
		if (in_array(1, $this->byweekno)) {
			// Check week number 1 of next year as well
			// TODO: Check -numweeks for next year.
			$i = $first_wkst_offset + $nb_weeks * 7;
			if ($first_wkst_offset != $first_wkst) {
				$i = $i - (7 - $first_wkst);
			}
			if ($i < $masks['year_len']) {
				// If week starts in next year, we don't care about it.
				for ($j = 0; $j < 7; $j++) {
					$masks['yearday_is_in_weekno'][$i] = true;
					$i += 1;
					if ($masks['yearday_to_weekday'][$i] == $this->wkst) {
						break;
					}
				}
			}
		}

		if ($first_wkst_offset) {
			// Check last week number of last year as well.
			// If first_wkst_offset is 0, either the year started on week start,
			// or week number 1 got days from last year, so there are no
			// days from last year's last week number in this year.
			if (! in_array(-1, $this->byweekno)) {
				$weekday_of_1st_yearday = date_create(($year-1).'-01-01 00:00:00')->format('N');
				$first_wkst_offset_last_year = (7 - $weekday_of_1st_yearday + $this->wkst) % 7;
				$last_year_len = 365 + is_leap_year($year - 1);
				if ($first_wkst_offset_last_year >= 4) {
					$first_wkst_offset_last_year = 0;
					$nb_weeks_last_year = 52 + (int) ((($last_year_len + ($weekday_of_1st_yearday - $this->wkst) % 7) % 7) / 4);
				}
				else {
					$nb_weeks_last_year = 52 + (int) ((($masks['year_len'] - $first_wkst_offset) % 7) /4);
				}
			}
			else {
				$nb_weeks_last_year = -1;
			}

			if (in_array($nb_weeks_last_year, $this->byweekno)) {
				for ($i = 0; $i < $first_wkst_offset; $i++) {
					$masks['yearday_is_in_weekno'][$i] = true;
				}
			}
		}
	}


	/**
	 * Build an array of every time of the day that matches the BYXXX time
	 * criteria.
	 *
	 * It will only process $this->frequency at one time. So:
	 * - for HOURLY frequencies it builds the minutes and second of the given hour
	 * - for MINUTELY frequencies it builds the seconds of the given minute
	 * - for SECONDLY frequencies, it returns an array with one element
	 * 
	 * This method is called everytime an increment of at least one hour is made.
	 *
	 * @param int $hour
	 * @param int $minute
	 * @param int $second
	 *
	 * @return array
	 */
	protected function getTimeSet($hour, $minute, $second)
	{
		switch ($this->freq) {
			case self::HOURLY:
				$set = array();
				foreach ($this->byminute as $minute) {
					foreach ($this->bysecond as $second) {
						// should we use another type?
						$set[] = array($hour, $minute, $second);
					}
				}
				// sort ?
				return $set;
			case self::MINUTELY:
				$set = array();
				foreach ($this->bysecond as $second) {
					// should we use another type?
					$set[] = array($hour, $minute, $second);
				}
				// sort ?
				return $set;
			case self::SECONDLY:
				return array(array($hour, $minute, $second));
			default:
				throw new \LogicException('getTimeSet called with an invalid frequency');
		}
	}

	/**
	 * This is the main method, where all of the magic happens.
	 *
	 * The main idea is: a brute force loop testing all the dates, made fast by
	 * not relying on date() functions
	 * 
	 * There is one big loop that examines every interval of the given frequency
	 * (so every day, every week, every month or every year), constructs an
	 * array of all the yeardays of the interval (for daily frequencies, the array
	 * only has one element, for weekly 7, and so on), and then filters out any
	 * day that do no match BYXXX parts.
	 *
	 * The algorithm does not try to be "smart" in calculating the increment of
	 * the loop. That is, for a rule like "every day in January for 10 years"
	 * the algorithm will loop through every day of the year, each year, generating
	 * some 3650 iterations (+ some to account for the leap years).
	 * This is a bit counter-intuitive, as it is obvious that the loop could skip
	 * all the days in February till December since they are never going to match.
	 *
	 * Fortunately, this approach is still super fast because it doesn't rely
	 * on date() or DateTime functions, and instead does all the date operations
	 * manually, either arithmetically or using arrays as converters.
	 *
	 * Another quirk of this approach is that because the granularity is by day,
	 * higher frequencies (hourly, minutely and secondly) have to have
	 * their own special loops within the main loop, making the whole thing quite
	 * convoluted.
	 * Moreover, at such frequencies, the brute-force approach starts to really
	 * suck. For example, a rule like
	 * "Every minute, every Jan 1st between 10:00 and 10:59, for 10 years" 
	 * requires a tremendous amount of useless iterations to jump from Jan 1st 10:59
	 * at year 1 to Jan 1st 10.00 at year 2.
	 *
	 * In order to make a "smart jump", we would have to have a way to determine
	 * the gap between the next occurrence arithmetically. I think that would require
	 * to analyze each "BYXXX" rule part that "Limit" the set (see the RFC page 43)
	 * at the given frequency. For example, a YEARLY frequency doesn't need "smart
	 * jump" at all; MONTHLY and WEEKLY frequencies only need to check BYMONTH;
	 * DAILY frequency needs to check BYMONTH, BYMONTHDAY and BYDAY, and so on.
	 * The check probably has to be done in reverse order, e.g. for DAILY frequencies
	 * attempt to jump to the next weekday (BYDAY) or next monthday (BYMONTHDAY)
	 * (I don't know yet which one first), and then if that results in a change of
	 * month, attempt to jump to the next BYMONTH, and so on.
	 *
	 * @return \DateTime|null
	 */
	public function getIterator()
	{
		$total = 0;
		$occurrence = null;
		$dtstart = null;
		$dayset = null;

		// go through the cache first
		foreach ($this->cache as $occurrence) {
			yield clone $occurrence; // since DateTime is not immutable, avoid any problem

			$total += 1;
		}

		// if the cache as been used up completely and we now there is nothing else,
		// we can stop the generator
		if ($total === $this->total) {
			return; // end generator
		}

		if ($occurrence) {
			$dtstart = clone $occurrence; // since DateTime is not immutable, clone to avoid any problem
			// so we skip the last occurrence of the cache
			if ($this->freq === self::SECONDLY) {
				$dtstart = $dtstart->modify('+'.$this->interval.'second');
			}
			else {
				$dtstart = $dtstart->modify('+1second');
			}
		}

		if ($dtstart === null) {
			$dtstart = clone $this->dtstart;
		}

		if ($this->freq === self::WEEKLY) {
			// we align the start date to the WKST, so we can then
			// simply loop by adding +7 days. The Python lib does some
			// calculation magic at the end of the loop (when incrementing)
			// to realign on first pass.
			$tmp = clone $dtstart;
			$tmp = $tmp->modify('-'.pymod($dtstart->format('N') - $this->wkst,7).'days');
			list($year,$month,$day,$hour,$minute,$second) = explode(' ',$tmp->format('Y n j G i s'));
			unset($tmp);
		}
		else {
			list($year,$month,$day,$hour,$minute,$second) = explode(' ',$dtstart->format('Y n j G i s'));
		}
		// remove leading zeros
		$minute = (int) $minute;
		$second = (int) $second;

		// we initialize the timeset
		if ($this->freq < self::HOURLY) {
			// daily, weekly, monthly or yearly
			// we don't need to calculate a new timeset
			$timeset = $this->timeset;
		}
		else {
			// initialize empty if it's not going to occurs on the first iteration
			if (
				($this->freq >= self::HOURLY && $this->byhour && ! in_array($hour, $this->byhour))
				|| ($this->freq >= self::MINUTELY && $this->byminute && ! in_array($minute, $this->byminute))
				|| ($this->freq >= self::SECONDLY && $this->bysecond && ! in_array($second, $this->bysecond))
			) {
				$timeset = array();
			}
			else {
				$timeset = $this->getTimeSet($hour, $minute, $second);
			}
		}

		$max_cycles = self::MAX_CYCLES[$this->freq <= self::DAILY ? $this->freq : self::DAILY];
		for ($i = 0; $i < $max_cycles; $i++) {
			// 1. get an array of all days in the next interval (day, month, week, etc.)
			// we filter out from this array all days that do not match the BYXXX conditions
			// to speed things up, we use days of the year (day numbers) instead of date
			if ($dayset === null) {
				// rebuild the various masks and converters
				// these arrays will allow fast date operations
				// without relying on date() methods
				if (empty($masks) || $masks['year'] != $year || $masks['month'] != $month) {
					$masks = array('year' => '','month'=>'');
					// only if year has changed
					if ($masks['year'] != $year) {
						$masks['leap_year'] = is_leap_year($year);
						$masks['year_len'] = 365 + (int) $masks['leap_year'];
						$masks['weekday_of_1st_yearday'] = date_create($year."-01-01 00:00:00")->format('N');
						$masks['yearday_to_weekday'] = array_slice(self::WEEKDAY_MASK, $masks['weekday_of_1st_yearday']-1);
						if ($masks['leap_year']) {
							$masks['yearday_to_month'] = self::MONTH_MASK_366;
							$masks['yearday_to_monthday'] = self::MONTHDAY_MASK_366;
							$masks['yearday_to_monthday_negative'] = self::NEGATIVE_MONTHDAY_MASK_366;
							$masks['last_day_of_month'] = self::LAST_DAY_OF_MONTH_366;
						}
						else {
							$masks['yearday_to_month'] = self::MONTH_MASK;
							$masks['yearday_to_monthday'] = self::MONTHDAY_MASK;
							$masks['yearday_to_monthday_negative'] = self::NEGATIVE_MONTHDAY_MASK;
							$masks['last_day_of_month'] = self::LAST_DAY_OF_MONTH;
						}
						if ($this->byweekno) {
							$this->buildWeeknoMask($year, $month, $day, $masks);
						}
					}
					// everytime month or year changes
					if ($this->byweekday_nth) {
						$this->buildNthWeekdayMask($year, $month, $day, $masks);
					}
					$masks['year'] = $year;
					$masks['month'] = $month;
				}

				// calculate the current set
				$dayset = $this->getDaySet($year, $month, $day, $masks);

				$filtered_set = array();
				// filter out the days based on the BYXXX rules
				foreach ($dayset as $yearday) {
					if ($this->bymonth && ! in_array($masks['yearday_to_month'][$yearday], $this->bymonth)) {
						continue;
					}

					if ($this->byweekno && ! isset($masks['yearday_is_in_weekno'][$yearday])) {
						continue;
					}

					if ($this->byyearday) {
						if (! in_array($yearday + 1, $this->byyearday) && ! in_array(- $masks['year_len'] + $yearday,$this->byyearday)) {
							continue;
						}
					}

					if (($this->bymonthday || $this->bymonthday_negative)
						&& ! in_array($masks['yearday_to_monthday'][$yearday], $this->bymonthday)
						&& ! in_array($masks['yearday_to_monthday_negative'][$yearday], $this->bymonthday_negative)) {
						continue;
					}

					if (($this->byweekday || $this->byweekday_nth)
						&& ! in_array($masks['yearday_to_weekday'][$yearday], $this->byweekday)
						&& ! isset($masks['yearday_is_nth_weekday'][$yearday])) {
						continue;
					}

					$filtered_set[] = $yearday;
				}

				$dayset = $filtered_set;

				// if BYSETPOS is set, we need to expand the timeset to filter by pos
				// so we make a special loop to return while generating
				// TODO this is not needed with a generator anymore
				// we can yield directly within the loop
				if ($this->bysetpos && $timeset) {
					$filtered_set = array();
					foreach ($this->bysetpos as $pos) {
						$n = count($timeset);
						if ($pos < 0) {
							$pos = $n * count($dayset) + $pos;
						}
						else {
							$pos = $pos - 1;
						}

						$div = (int) ($pos / $n); // daypos
						$mod = $pos % $n; // timepos
						if (isset($dayset[$div]) && isset($timeset[$mod])) {
							$yearday = $dayset[$div];
							$time = $timeset[$mod];
							// used as array key to ensure uniqueness
							$tmp = $year.':'.$yearday.':'.$time[0].':'.$time[1].':'.$time[2];
							if (! isset($filtered_set[$tmp])) {
								$occurrence = \DateTime::createFromFormat(
									'Y z',
									"$year $yearday",
									$this->dtstart->getTimezone()
								);
								$occurrence->setTime($time[0], $time[1], $time[2]);
								$filtered_set[$tmp] = $occurrence;
							}
						}
					}
					sort($filtered_set);
					$dayset = $filtered_set;
				}
			}

			// 2. loop, generate a valid date, and yield the result
			// at the same time, we check the end condition and return null if
			// we need to stop
			if ($this->bysetpos && $timeset) {
				// while ( ($occurrence = current($dayset)) !== false ) {
				foreach ($dayset as $occurrence) {
					// consider end conditions
					if ($this->until && $occurrence > $this->until) {
						$this->total = $total; // save total for count() cache
						return;
					}

					// next($dayset);
					if ($occurrence >= $dtstart) { // ignore occurrences before DTSTART
						if ($this->count && $total >= $this->count) {
							$this->total = $total;
							return;
						}
						$total += 1;
						$this->cache[] = clone $occurrence;
						yield clone $occurrence; // yield
						$i = 0; // reset the max cycles counter, since we yieled a result
					}
				}
			}
			else {
				// normal loop, without BYSETPOS
				foreach ($dayset as $yearday) {
					$occurrence = \DateTime::createFromFormat(
						'Y z',
						"$year $yearday",
						$this->dtstart->getTimezone()
					);

					// while ( ($time = current($timeset)) !== false ) {
					foreach ($timeset as $time) {
						$occurrence->setTime($time[0], $time[1], $time[2]);
						// consider end conditions
						if ($this->until && $occurrence > $this->until) {
							$this->total = $total; // save total for count() cache
							return;
						}

						// next($timeset);
						if ($occurrence >= $dtstart) { // ignore occurrences before DTSTART
							if ($this->count && $total >= $this->count) {
								$this->total = $total;
								return;
							}
							$total += 1;
							$this->cache[] = clone $occurrence;
							yield clone $occurrence; // yield
							$i = 0; // reset the max cycles counter, since we yieled a result
						}
					}
				}
			}

			// 3. we reset the loop to the next interval
			$days_increment = 0;
			switch ($this->freq) {
				case self::YEARLY:
					// we do not care about $month or $day not existing,
					// they are not used in yearly frequency
					$year = $year + $this->interval;
					break;
				case self::MONTHLY:
					// we do not care about the day of the month not existing
					// it is not used in monthly frequency
					$month = $month + $this->interval;
					if ($month > 12) {
						$div = (int) ($month / 12);
						$mod = $month % 12;
						$month = $mod;
						$year = $year + $div;
						if ($month == 0) {
							$month = 12;
							$year = $year - 1;
						}
					}
					break;
				case self::WEEKLY:
					$days_increment = $this->interval*7;
					break;
				case self::DAILY:
					$days_increment = $this->interval;
					break;

				// For the time frequencies, things are a little bit different.
				// We could just add "$this->interval" hours, minutes or seconds
				// to the current time, and go through the main loop again,
				// but since the frequencies are so high and needs to much iteration
				// it's actually a bit faster to have custom loops and only
				// call the DateTime method at the very end.

				case self::HOURLY:
					if (empty($dayset)) {
						// an empty set means that this day has been filtered out
						// by one of the BYXXX rule. So there is no need to
						// examine it any further, we know nothing is going to
						// occur anyway.
						// so we jump to one iteration right before next day
						$hour += ((int) ((23 - $hour) / $this->interval)) * $this->interval;
					}

					$found = false;
					for ($j = 0; $j < self::MAX_CYCLES[self::HOURLY]; $j++) {
						$hour += $this->interval;
						$div = (int) ($hour / 24);
						$mod = $hour % 24;
						if ($div) {
							$hour = $mod;
							$days_increment += $div;
						}
						if (! $this->byhour || in_array($hour, $this->byhour)) {
							$found = true;
							break;
						}
					}

					if (! $found) {
						$this->total = $total; // save total for count cache
						return; // stop the iterator
					}

					$timeset = $this->getTimeSet($hour, $minute, $second);
					break;
				case self::MINUTELY:
					if (empty($dayset)) {
						$minute += ((int) ((1439 - ($hour*60+$minute)) / $this->interval)) * $this->interval;
					}

					$found = false;
					for ($j = 0; $j < self::MAX_CYCLES[self::MINUTELY]; $j++) {
						$minute += $this->interval;
						$div = (int) ($minute / 60);
						$mod = $minute % 60;
						if ($div) {
							$minute = $mod;
							$hour += $div;
							$div = (int) ($hour / 24);
							$mod = $hour % 24;
							if ($div) {
								$hour = $mod;
								$days_increment += $div;
							}
						}
						if ((! $this->byhour || in_array($hour, $this->byhour)) &&
						(! $this->byminute || in_array($minute, $this->byminute))) {
							$found = true;
							break;
						}
					}

					if (! $found) {
						$this->total = $total; // save total for count cache
						return; // stop the iterator
					}

					$timeset = $this->getTimeSet($hour, $minute, $second);
					break;
				case self::SECONDLY:
					if (empty($dayset)) {
						$second += ((int) ((86399 - ($hour*3600 + $minute*60 + $second)) / $this->interval)) * $this->interval;
					}

					$found = false;
					for ($j = 0; $j < self::MAX_CYCLES[self::SECONDLY]; $j++) {
						$second += $this->interval;
						$div = (int) ($second / 60);
						$mod = $second % 60;
						if ($div) {
							$second = $mod;
							$minute += $div;
							$div = (int) ($minute / 60);
							$mod = $minute % 60;
							if ($div) {
								$minute = $mod;
								$hour += $div;
								$div = (int) ($hour / 24);
								$mod = $hour % 24;
								if ($div) {
									$hour = $mod;
									$days_increment += $div;
								}
							}
						}
						if ((! $this->byhour || in_array($hour, $this->byhour))
							&& (! $this->byminute || in_array($minute, $this->byminute)) 
							&& (! $this->bysecond || in_array($second, $this->bysecond))) {
							$found = true;
							break;
						}
					}

					if (! $found) {
						$this->total = $total; // save total for count cache
						return; // stop the iterator
					}

					$timeset = $this->getTimeSet($hour, $minute, $second);
					break;
			}
			// here we take a little shortcut from the Python version, by using DateTime
			if ($days_increment) {
				list($year,$month,$day) = explode('-',date_create("$year-$month-$day")->modify("+ $days_increment days")->format('Y-n-j'));
			}
			$dayset = null; // reset the loop
		}

		$this->total = $total; // save total for count cache
		return; // stop the iterator
	}

///////////////////////////////////////////////////////////////////////////////
// constants
// Every mask is 7 days longer to handle cross-year weekly periods.

	const MONTH_MASK = [
		1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,
		2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,
		3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,
		4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,
		5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,
		6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,
		7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,
		8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,
		9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,
		10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,
		11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,
		12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,
		1,1,1,1,1,1,1
	];

	const MONTH_MASK_366 = [
		1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,
		2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,
		3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,
		4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,
		5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,
		6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,
		7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,
		8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,
		9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,
		10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,
		11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,
		12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,
		1,1,1,1,1,1,1
	];

	const MONTHDAY_MASK = [
		1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,
		1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,
		1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,
		1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,
		1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,
		1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,
		1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,
		1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,
		1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,
		1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,
		1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,
		1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,
		1,2,3,4,5,6,7
	];

	const MONTHDAY_MASK_366 = [
		1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,
		1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,
		1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,
		1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,
		1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,
		1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,
		1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,
		1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,
		1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,
		1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,
		1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,
		1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,
		1,2,3,4,5,6,7
	];

	const NEGATIVE_MONTHDAY_MASK = [
		-31,-30,-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,
		-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,
		-31,-30,-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,
		-30,-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,
		-31,-30,-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,
		-30,-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,
		-31,-30,-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,
		-31,-30,-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,
		-30,-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,
		-31,-30,-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,
		-30,-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,
		-31,-30,-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,
		-31,-30,-29,-28,-27,-26,-25
	];

	const NEGATIVE_MONTHDAY_MASK_366 = [
		-31,-30,-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,
		-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,
		-31,-30,-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,
		-30,-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,
		-31,-30,-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,
		-30,-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,
		-31,-30,-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,
		-31,-30,-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,
		-30,-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,
		-31,-30,-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,
		-30,-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,
		-31,-30,-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,
		-31,-30,-29,-28,-27,-26,-25
	];

	const WEEKDAY_MASK = [
		1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,
		1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,
		1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,
		1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,
		1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,
		1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,
		1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,
		1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,
		1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,
		1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,
		1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7,1,2,3,4,5,6,7
	];

	const LAST_DAY_OF_MONTH_366 = [
		0, 31, 60, 91, 121, 152, 182, 213, 244, 274, 305, 335, 366
	];

	const LAST_DAY_OF_MONTH = [
		0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334, 365
	];

	/**
	 * @var array
	 * Maximum number of cycles after which a calendar repeats itself. This
	 * is used to detect infinite loop: if no occurrence has been found 
	 * after this numbers of cycles, we can abort.
	 *
	 * The Gregorian calendar cycle repeat completely every 400 years
	 * (146,097 days or 20,871 weeks).
	 * A smaller cycle would be 28 years (1,461 weeks), but it only works
	 * if there is no dropped leap year in between.
	 * 2100 will be a dropped leap year, but I'm going to assume it's not
	 * going to be a problem anytime soon, so at the moment I use the 28 years
	 * cycle.
	 */
	const MAX_CYCLES = [
		// self::YEARLY => 400,
		// self::MONTHLY => 4800,
		// self::WEEKLY => 20871,
		// self::DAILY =>  146097, // that's a lot of cycles, it takes a few seconds to detect infinite loop
		self::YEARLY => 28,
		self::MONTHLY => 336,
		self::WEEKLY => 1461,
		self::DAILY => 10227,

		self::HOURLY => 24,
		self::MINUTELY => 1440,
		self::SECONDLY => 86400 // that's a lot of cycles too
	];

///////////////////////////////////////////////////////////////////////////////
// i18n methods
// these could be moved into a separate class maybe, since it's not always necessary

	/**
	 * @var array Stores translations once loaded (so we don't have to reload them all the time)
	 */
	static protected $i18n = array();

	/**
	 * @var bool if intl extension is loaded
	 */
	static protected $intl_loaded = null;

	/** 
	 * Select a translation in $array based on the value of $n
	 *
	 * Used for selecting plural forms.
	 *
	 * @param mixed $array Array with multiple forms or a string
	 * @param string $n
	 *
	 * @return string
	 */
	static protected function i18nSelect($array, $n)
	{
		if (! is_array($array)) {
			return $array;
		}

		if (array_key_exists($n, $array)) {
			return $array[$n];
		}
		elseif (array_key_exists('else', $array)) {
			return $array['else'];
		}
		else {
			return ''; // or throw?
		}
	}

	/**
	 * Create a comma-separated list, with the last item added with an " and "
	 * Example: Monday, Tuesday and Friday
	 *
	 * @param array $array
	 * @param string $and Translation for "and"
	 *
	 * @return string
	 */
	static protected function i18nList(array $array, $and = 'and')
	{
		if (count($array) > 1) {
			$last = array_splice($array, -1);
			return sprintf(
				'%s %s %s',
				implode(', ',$array),
				$and,
				implode('',$last)
			);
		}
		else {
			return $array[0];
		}
	}

	/** 
	 * Test if intl extension is loaded
	 * @return bool
	 */
	static protected function intlLoaded()
	{
		if (self::$intl_loaded === null) {
			self::$intl_loaded = extension_loaded('intl');
		}
		return self::$intl_loaded;
	}

	/**
	 * Parse a locale and returns a list of files to load.
	 * For example "fr_FR" will produce "fr" and "fr_FR"
	 *
	 * @param $locale
	 * @param null $use_intl
	 *
	 * @return array
	 */
	static protected function i18nFilesToLoad($locale, $use_intl = null)
	{
		if ($use_intl === null) {
			$use_intl = self::intlLoaded();
		}
		$files = array();
		
		if ($use_intl) {
			$parsed = \Locale::parseLocale($locale);
			$files[] = $parsed['language'];
			if (isset($parsed['region'])) {
				$files[] = $parsed['language'].'_'.$parsed['region'];
			}
		}
		else {
			if (! preg_match('/^([a-z]{2})(?:(?:_|-)[A-Z][a-z]+)?(?:(?:_|-)([A-Za-z]{2}))?(?:(?:_|-)[A-Z]*)?(?:\.[a-zA-Z\-0-9]*)?$/', $locale, $matches)) {
				throw new \InvalidArgumentException("The locale option does not look like a valid locale: $locale. For more option install the intl extension.");
			}

			$files[] = $matches[1];
			if (isset($matches[2])) {
				$files[] = $matches[1].'_'.strtoupper($matches[2]);
			}
		}

		return $files;
	}

	/**
	 * Load a translation file in memory.
	 * Will load the basic first (e.g. "en") and then the region-specific if any
	 * (e.g. "en_GB"), merging as necessary.
	 * So region-specific translation files don't need to redefine every strings.
	 *
	 * @param string      $locale
	 * @param string|null $fallback
	 * @param bool        $use_intl
	 * @param string      $custom_path
	 *
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	static protected function i18nLoad($locale, $fallback = null, $use_intl = null, $custom_path = null)
	{
		$files = self::i18nFilesToLoad($locale, $use_intl);

		$base_path = __DIR__.'/i18n';

		$result = array();
		foreach ($files as $file) {

			// if the file exists in $custom_path, it overrides the default
			if ($custom_path && is_file("$custom_path/$file.php")) {
				$path = "$custom_path/$file.php";
			}
			else {
				$path = "$base_path/$file.php";
			}

			if (isset(self::$i18n[$path])) {
				$result = array_merge($result, self::$i18n[$path]);
			}
			elseif (is_file($path) && is_readable($path)) {
				self::$i18n[$path] = include $path;
				$result = array_merge($result, self::$i18n[$path]);
			}
			else {
				self::$i18n[$path] = array();
			}
		}

		if (empty($result)) {
			if (!is_null($fallback)) {
				return self::i18nLoad($fallback, null, $use_intl);
			}
			throw new \RuntimeException("Failed to load translations for '$locale'");
		}

		return $result;
	}

	/**
	 * Format a rule in a human readable string
	 * `intl` extension is required.
	 *
	 * Available options
	 *
	 * | Name              | Type    | Description
	 * |-------------------|---------|------------
	 * | `use_intl`        | bool    | Use the intl extension or not (autodetect)
	 * | `locale`          | string  | The locale to use (autodetect)
	 * | `fallback`        | string  | Fallback locale if main locale is not found (default en)
	 * | `date_formatter`  | callable| Function used to format the date (takes date, returns formatted)
	 * | `explicit_inifite`| bool    | Mention "forever" if the rule is infinite (true)
	 * | `dtstart`         | bool    | Mention the start date (true)
	 * | `include_start`   | bool    |
	 * | `include_until`   | bool    |
	 * | `custom_path`     | string  |
	 *
	 * @param array  $opt
	 *
	 * @return string
	 */
	public function humanReadable(array $opt = array())
	{
		if (! isset($opt['use_intl'])) {
			$opt['use_intl'] = self::intlLoaded();
		}

		$default_opt = array(
			'use_intl' => self::intlLoaded(),
			'locale' => null,
			'date_formatter' => null,
			'fallback' => 'en',
			'explicit_infinite' => true,
			'include_start' => true,
			'include_until' => true,
			'custom_path' => null
		);

		// attempt to detect default locale
		if ($opt['use_intl']) {
			$default_opt['locale'] = \Locale::getDefault();
		} else {
			$default_opt['locale'] = setlocale(LC_CTYPE, 0);
			if ($default_opt['locale'] == 'C') {
				$default_opt['locale'] = 'en';
			}
		}

		if ($opt['use_intl']) {
			$default_opt['date_format'] = \IntlDateFormatter::SHORT;
			if ($this->freq >= self::SECONDLY || not_empty($this->rule['BYSECOND'])) {
				$default_opt['time_format'] = \IntlDateFormatter::LONG;
			}
			elseif ($this->freq >= self::HOURLY || not_empty($this->rule['BYHOUR']) || not_empty($this->rule['BYMINUTE'])) {
				$default_opt['time_format'] = \IntlDateFormatter::SHORT;
			}
			else {
				$default_opt['time_format'] = \IntlDateFormatter::NONE;
			}
		}

		$opt = array_merge($default_opt, $opt);

		$i18n = self::i18nLoad($opt['locale'], $opt['fallback'], $opt['use_intl'], $opt['custom_path']);

		if ($opt['date_formatter'] && ! is_callable($opt['date_formatter'])) {
			throw new \InvalidArgumentException('The option date_formatter must callable');
		}

		if (! $opt['date_formatter']) {
			if ($opt['use_intl']) {
				$timezone = $this->dtstart->getTimezone()->getName();

				if ($timezone === 'Z') {
					$timezone = 'GMT'; // otherwise IntlDateFormatter::create fails because... reasons.
				} elseif (preg_match('/[-+]\d{2}/',$timezone)) {
					$timezone = 'GMT'.$timezone; // otherwise IntlDateFormatter::create fails because... other reasons.
				}
				$formatter = \IntlDateFormatter::create(
					$opt['locale'],
					$opt['date_format'],
					$opt['time_format'],
					$timezone
				);
				if (! $formatter) {
					throw new \RuntimeException('IntlDateFormatter::create() failed. Error Code: '.intl_get_error_code().' "'. intl_get_error_message().'" (this should not happen, please open a bug report!)');
				}
				$opt['date_formatter'] = function($date) use ($formatter) {
					return $formatter->format($date);
				};
			}
			else {
				$opt['date_formatter'] = function($date) {
					return $date->format('Y-m-d H:i:s');
				};
			}
		}

		$parts = array(
			'freq' => '',
			'byweekday' => '',
			'bymonth' => '',
			'byweekno' => '',
			'byyearday' => '',
			'bymonthday' => '',
			'byhour' => '',
			'byminute' => '',
			'bysecond' => '',
			'bysetpos' => ''
		);

		// Every (INTERVAL) FREQ...
		$freq_str = strtolower(array_search($this->freq, self::FREQUENCIES));
		$parts['freq'] = strtr(
			self::i18nSelect($i18n[$freq_str], $this->interval),
			array(
				'%{interval}' => $this->interval
			)
		);

		// BYXXX rules
		if (not_empty($this->rule['BYMONTH'])) {
			$tmp = $this->bymonth;
			foreach ($tmp as & $value) {
				$value = $i18n['months'][$value];
			}
			$parts['bymonth'] = strtr(self::i18nSelect($i18n['bymonth'], count($tmp)), array(
				'%{months}' => self::i18nList($tmp, $i18n['and'])
			));
		}

		if (not_empty($this->rule['BYWEEKNO'])) {
			// XXX negative week number are not great here
			$tmp = $this->byweekno;
			foreach ($tmp as & $value) {
				$value = strtr($i18n['nth_weekno'], array(
					'%{n}' => $value
				));
			}
			$parts['byweekno'] = strtr(
				self::i18nSelect($i18n['byweekno'], count($this->byweekno)),
				array(
					'%{weeks}' => self::i18nList($tmp, $i18n['and'])
				)
			);
		}

		if (not_empty($this->rule['BYYEARDAY'])) {
			$tmp = $this->byyearday;
			foreach ($tmp as & $value) {
				$value = strtr(self::i18nSelect($i18n[$value>0?'nth_yearday':'-nth_yearday'],$value), array(
					'%{n}' => abs($value)
				));
			}
			$tmp = strtr(self::i18nSelect($i18n['byyearday'], count($tmp)), array(
				'%{yeardays}' => self::i18nList($tmp, $i18n['and'])
			));
			// ... of the month
			$tmp = strtr(self::i18nSelect($i18n['x_of_the_y'], 'yearly'), array(
				'%{x}' => $tmp
			));
			$parts['byyearday'] = $tmp;
		}

		if (not_empty($this->rule['BYMONTHDAY'])) {
			$parts['bymonthday'] = array();
			if ($this->bymonthday) {
				$tmp = $this->bymonthday;
				foreach ($tmp as & $value) {
					$value = strtr(self::i18nSelect($i18n['nth_monthday'],$value), array(
						'%{n}' => $value
					));
				}
				$tmp = strtr(self::i18nSelect($i18n['bymonthday'], count($tmp)), array(
					'%{monthdays}' => self::i18nList($tmp, $i18n['and'])
				));
				// ... of the month
				$tmp = strtr(self::i18nSelect($i18n['x_of_the_y'], 'monthly'), array(
					'%{x}' => $tmp
				));
				$parts['bymonthday'][] = $tmp;
			}
			if ($this->bymonthday_negative) {
				$tmp = $this->bymonthday_negative;
				foreach ($tmp as & $value) {
					$value = strtr(self::i18nSelect($i18n['-nth_monthday'],$value), array(
						'%{n}' => -$value
					));
				}
				$tmp = strtr(self::i18nSelect($i18n['bymonthday'], count($tmp)), array(
					'%{monthdays}' => self::i18nList($tmp, $i18n['and'])
				));
				// ... of the month
				$tmp = strtr(self::i18nSelect($i18n['x_of_the_y'], 'monthly'), array(
					'%{x}' => $tmp
				));
				$parts['bymonthday'][] = $tmp;
			}
			$parts['bymonthday'] = implode(' '.$i18n['and'],$parts['bymonthday']);
		}

		if (not_empty($this->rule['BYDAY'])) {
			$parts['byweekday'] = array();
			if ($this->byweekday) {
				$tmp = $this->byweekday;
				foreach ($tmp as & $value) {
					$value = $i18n['weekdays'][$value];
				}
				$parts['byweekday'][] = strtr(self::i18nSelect($i18n['byweekday'], count($tmp)), array(
					'%{weekdays}' =>  self::i18nList($tmp, $i18n['and'])
				));
			}
			if ($this->byweekday_nth) {
				$tmp = $this->byweekday_nth;
				foreach ($tmp as & $value) {
					list($day, $n) = $value;
					$value = strtr(self::i18nSelect($i18n[$n>0?'nth_weekday':'-nth_weekday'], $n), array(
						'%{weekday}' => $i18n['weekdays'][$day],
						'%{n}' => abs($n)
					));
				}
				$tmp = strtr(self::i18nSelect($i18n['byweekday'], count($tmp)), array(
					'%{weekdays}' => self::i18nList($tmp, $i18n['and'])
				));
				// ... of the year|month
				$tmp = strtr(self::i18nSelect($i18n['x_of_the_y'], $freq_str), array(
					'%{x}' => $tmp
				));
				$parts['byweekday'][] = $tmp;
			}
			$parts['byweekday'] = implode(' '.$i18n['and'],$parts['byweekday']);
		}

		if (not_empty($this->rule['BYHOUR'])) {
			$tmp = $this->byhour;
			foreach ($tmp as &$value) {
				$value = strtr($i18n['nth_hour'], array(
					'%{n}' => $value
				));
			}
			$parts['byhour'] = strtr(self::i18nSelect($i18n['byhour'],count($tmp)), array(
				'%{hours}' => self::i18nList($tmp, $i18n['and'])
			));
		}

		if (not_empty($this->rule['BYMINUTE'])) {
			$tmp = $this->byminute;
			foreach ($tmp as &$value) {
				$value = strtr($i18n['nth_minute'], array(
					'%{n}' => $value
				));
			}
			$parts['byminute'] = strtr(self::i18nSelect($i18n['byminute'],count($tmp)), array(
				'%{minutes}' => self::i18nList($tmp, $i18n['and'])
			));
		}

		if (not_empty($this->rule['BYSECOND'])) {
			$tmp = $this->bysecond;
			foreach ($tmp as &$value) {
				$value = strtr($i18n['nth_second'], array(
					'%{n}' => $value
				));
			}
			$parts['bysecond'] = strtr(self::i18nSelect($i18n['bysecond'],count($tmp)), array(
				'%{seconds}' => self::i18nList($tmp, $i18n['and'])
			));
		}

		if ($this->bysetpos) {
			$tmp = $this->bysetpos;
			foreach ($tmp as & $value) {
				$value = strtr(self::i18nSelect($i18n[$value>0?'nth_setpos':'-nth_setpos'],$value), array(
					'%{n}' => abs($value)
				));
			}
			$tmp = strtr(self::i18nSelect($i18n['bysetpos'], count($tmp)), array(
				'%{setpos}' => self::i18nList($tmp, $i18n['and'])
			));
			$parts['bysetpos'] = $tmp;
		}

		if ($opt['include_start']) {
			// from X
			$parts['start'] = strtr($i18n['dtstart'], array(
				'%{date}' => $opt['date_formatter']($this->dtstart)
			));
		}

		// to X, or N times, or indefinitely
		if ($opt['include_until']) {
			if (! $this->until && ! $this->count) {
				if ($opt['explicit_infinite']) {
					$parts['end'] = $i18n['infinite'];
				}
			}
			elseif ($this->until) {
				$parts['end'] = strtr($i18n['until'], array(
					'%{date}' => $opt['date_formatter']($this->until)
				));
			}
			elseif ($this->count) {
				$parts['end'] = strtr(
					self::i18nSelect($i18n['count'], $this->count),
					array(
						'%{count}' => $this->count
					)
				);
			}
		}

		$parts = array_filter($parts);
		$str = implode('',$parts);
		return $str;
	}
}
