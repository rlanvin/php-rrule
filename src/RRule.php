<?php

/**
 * Implementation of RRULE as defined by RFC 5545.
 *
 * Heavily based on dateutil/rrule.py
 */

namespace RRule;

define(__NAMESPACE__.'\MAX_YEAR',date('Y', PHP_INT_MAX));

/**
 * @return bool
 */
function not_empty($var)
{
	return ! empty($var) || $var === 0 || $var === '0';
}

/**
 * closure/goog/math/math.js:modulo
 * Copyright 2006 The Closure Library Authors.
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
 */
function pymod($a, $b)
{
	$x = $a % $b;

	// If $x and $b differ in sign, add $b to wrap the result to the correct sign.
	return ($x * $b < 0) ? $x + $b : $x;
}

/**
 * @return bool
 */
function is_leap_year($year)
{
	if ( $year % 4 !== 0 ) {
		return false;
	}
	if ( $year % 100 !== 0 ) {
		return true;
	}
	if ( $year % 400 !== 0 ) {
		return false;
	}
	return true;
}

/**
 * Main class.
 */
class RRule implements \Iterator, \ArrayAccess
{
	// frequencies
	public static $frequencies = ['SECONDLY','MINUTELY','HOURLY','DAILY','WEEKLY','MONTHLY','YEARLY'];

	const SECONDLY = 0;
	const MINUTELY = 1;
	const HOURLY = 2;
	const DAILY = 3;
	const WEEKLY = 4;
	const MONTHLY = 5;
	const YEARLY = 6;

	// weekdays numbered from 1 (ISO-8601 or date('N'))
	public static $week_days =  ['MO' => 1,'TU' => 2,'WE' => 3,'TH' => 4,'FR' => 5,'SA' => 6,'SU' => 7];

	// original rule
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
	protected $dtstart_ts = null;
	protected $freq = null;
	protected $until = null;
	protected $count = null;
	protected $interval = null;
	protected $bysecond = null;
	protected $byminute = null;
	protected $byhour = null;
	protected $byweekday = null;
	protected $byweekday_relative = null;
	protected $bymonthday = null;
	protected $bymonthday_negative = null;
	protected $byyearday = null;
	protected $byweekno = null;
	protected $bymonth = null;
	protected $bysetpos = null;
	protected $wkst = null;

// Public interface

	/**
	 * Constructor
	 */
	public function __construct(array $parts)
	{
		// validate extra parts
		$unsupported = array_diff_key($parts, $this->rule);
		if ( ! empty($unsupported) ) {
			throw new \InvalidArgumentException('Unsupported parameter(s): '.implode(',',array_keys($unsupported)));
		}

		$parts = array_merge($this->rule, $parts);
		$this->rule = $parts; // save original rule

		// WKST
		$parts['WKST'] = strtoupper($parts['WKST']);
		if ( ! array_key_exists($parts['WKST'], self::$week_days) ) {
			throw new \InvalidArgumentException('The WKST rule part must be one of the following: '.implode(', ',array_keys(self::$week_days)));
		}
		$this->wkst = self::$week_days[$parts['WKST']];

		// FREQ
		$parts['FREQ'] = strtoupper($parts['FREQ']);
		if ( ! in_array($parts['FREQ'], self::$frequencies) ) {
			throw new \InvalidArgumentException('The FREQ rule part must be one of the following: '.implode(', ',self::$frequencies));
		}
		$this->freq = $parts['FREQ'];

		// INTERVAL
		$parts['INTERVAL'] = (int) $parts['INTERVAL'];
		if ( $parts['INTERVAL'] < 1 ) {
			throw new \InvalidArgumentException('The INTERVAL rule part must be a positive integer (> 0)');
		}
		$this->interval = (int) $parts['INTERVAL'];

		// DTSTART
		if ( not_empty($parts['DTSTART']) ) {
			if ( is_string($parts['DTSTART']) ) {
				$this->dtstart = $parts['DTSTART'];
				$this->dtstart_ts = strtotime($parts['DTSTART']);
			}
			elseif ( $parts['DTSTART'] instanceof DateTime ) {
				$this->dtstart = $parts['DTSTART']->format('Y-m-d');
				$this->dtstart_ts = $parts['DTSTART']->getTimestamp();
			}
			elseif ( is_integer($parts['DTSTART']) ) {
				$this->dtstart = date('Y-m-d',$parts['DTSTART']);
				$this->dtstart_ts = $parts['DTSTART'];
			}

			if ( ! $this->dtstart_ts ) {
				throw new \InvalidArgumentException('Cannot parse DTSTART - must be a valid date, timestamp or DateTime object');
			}
		} 
		else {
			$this->dtstart = date('Y-m-d');
			$this->dtstart_ts = strtotime($this->dtstart);
		}

		// UNTIL (optional)
		if ( not_empty($parts['UNTIL']) ) {
			if ( is_string($parts['UNTIL']) ) {
				$this->until = $parts['UNTIL'];
			}
			elseif ( $parts['UNTIL'] instanceof DateTime ) {
				$this->until = $parts['UNTIL']->format('Y-m-d');
			}
			elseif ( is_integer($parts['UNTIL']) ) {
				$this->until = date('Y-m-d',$parts['UNTIL']);
			}

			if ( ! strtotime($this->until) ) {
				throw new \InvalidArgumentException('Cannot parse UNTIL - must be a valid date, timestamp or DateTime object');
			}
		}

		// COUNT (optional)
		if ( not_empty($parts['COUNT']) ) {
			$parts['COUNT'] = (int) $parts['COUNT'];
			if ( $parts['COUNT'] < 1 ) {
				throw new \InvalidArgumentException('COUNT must be a positive integer (> 0)');
			}
			$this->count = (int) $parts['COUNT'];
		}

		// infer necessary BYXXX rules from DTSTART, if not provided
		if ( ! (not_empty($parts['BYWEEKNO']) || not_empty($parts['BYYEARDAY']) || not_empty($parts['BYMONTHDAY']) || not_empty($parts['BYDAY'])) ) {
			switch ( $this->freq ) {
				case 'YEARLY':
					if ( ! not_empty($parts['BYMONTH']) ) {
						$parts['BYMONTH'] = [date('m',$this->dtstart_ts)];
					}
					$parts['BYMONTHDAY'] = [date('j', $this->dtstart_ts)];
					break;
				case 'MONTHLY':
					$parts['BYMONTHDAY'] = [date('j',$this->dtstart_ts)];
					break;
				case 'WEEKLY':
					$parts['BYDAY'] = [array_search(date('N', $this->dtstart_ts), self::$week_days)];
					break;
			}
		}

		// BYSECOND
		if ( not_empty($parts['BYSECOND']) ) {
			if ( ! is_array($parts['BYSECOND']) ) {
				$parts['BYSECOND'] = explode(',',$parts['BYSECOND']);
			}

			$this->bysecond = [];
			foreach ( $parts['BYSECOND'] as $value ) {
				if ( $value < 0 || $value > 60 ) {
					throw new \InvalidArgumentException('Invalid BYSECOND value: '.$value);
				}
				$this->bysecond[] = (int) $value;
			}
		}

		if ( not_empty($parts['BYMINUTE']) ) {
			if ( ! is_array($parts['BYMINUTE']) ) {
				$parts['BYMINUTE'] = explode(',',$parts['BYMINUTE']);
			}

			$this->byminute = [];
			foreach ( $parts['BYMINUTE'] as $value ) {
				if ( $value < 0 || $value > 59 ) {
					throw new \InvalidArgumentException('Invalid BYMINUTE value: '.$value);
				}
				$this->byminute[] = (int) $value;
			}
		}

		if ( not_empty($parts['BYHOUR']) ) {
			if ( ! is_array($parts['BYHOUR']) ) {
				$parts['BYHOUR'] = explode(',',$parts['BYHOUR']);
			}

			$this->byhour = [];
			foreach ( $parts['BYHOUR'] as $value ) {
				if ( $value < 0 || $value > 23 ) {
					throw new \InvalidArgumentException('Invalid BYHOUR value: '.$value);
				}
				$this->byhour[] = (int) $value;
			}
		}

		// BYDAY (translated to byweekday for convenience)
		if ( not_empty($parts['BYDAY']) ) {
			if ( ! is_array($parts['BYDAY']) ) {
				$parts['BYDAY'] = explode(',',$parts['BYDAY']);
			}
			$this->byweekday = [];
			$this->byweekday_relative = [];
			foreach ( $parts['BYDAY'] as $value ) {
				$valid = preg_match('/^([+-]?[0-9]+)?([A-Z]{2})$/', $value, $matches);
				if ( ! $valid || (not_empty($matches[1]) && ($matches[1] == 0 || $matches[1] > 53 || $matches[1] < -53)) || ! array_key_exists($matches[2], self::$week_days) ) {
					throw new \InvalidArgumentException('Invalid BYDAY value: '.$value);
				}
				if ( $matches[1] ) {
					$this->byweekday_relative[] = [self::$week_days[$matches[2]], (int)$matches[1]];
				}
				else {
					$this->byweekday[] = self::$week_days[$matches[2]];
				}
			}

			if ( ! empty($this->weekday_relative) ) {
				if ( $this->freq !== 'MONTHLY' && $this->freq !== 'YEARLY' ) {
					throw new InvalidArgumentException('The BYDAY rule part MUST NOT be specified with a numeric value when the FREQ rule part is not set to MONTHLY or YEARLY.');
				}
				if ( $this->freq == 'YEARLY' && not_empty($parts['BYWEEKNO']) ) {
					throw new InvalidArgumentException('The BYDAY rule part MUST NOT be specified with a numeric value with the FREQ rule part set to YEARLY when the BYWEEKNO rule part is specified.');
				}
			}
		}

		// The BYMONTHDAY rule part specifies a COMMA-separated list of days
		// of the month.  Valid values are 1 to 31 or -31 to -1.  For
		// example, -10 represents the tenth to the last day of the month.
		// The BYMONTHDAY rule part MUST NOT be specified when the FREQ rule
		// part is set to WEEKLY.
		if ( not_empty($parts['BYMONTHDAY']) ) {
			if ( $this->freq == 'WEEKLY' ) {
				throw new \InvalidArgumentException('The BYMONTHDAY rule part MUST NOT be specified when the FREQ rule part is set to WEEKLY.');
			}

			if ( ! is_array($parts['BYMONTHDAY']) ) {
				$parts['BYMONTHDAY'] = explode(',',$parts['BYMONTHDAY']);
			}

			$this->bymonthday = [];
			$this->bymonthday_negative = [];
			foreach ( $parts['BYMONTHDAY'] as $value ) {
				if ( ! $value || $value < -31 || $value > 31 ) {
					throw new \InvalidArgumentException('Invalid BYMONTHDAY value: '.$value.' (valid values are 1 to 31 or -31 to -1)');
				}
				if ( $value < 0 ) {
					$this->bymonthday_negative[] = (int) $value;
				}
				else {
					$this->bymonthday[] = (int) $value;
				}
			}
		}

		if ( not_empty($parts['BYYEARDAY']) ) {
			if ( $this->freq == 'DAILY' || $this->freq == 'WEEKLY' || $this->freq == 'MONTHLY' ) {
				throw new \InvalidArgumentException('The BYYEARDAY rule part MUST NOT be specified when the FREQ rule part is set to DAILY, WEEKLY, or MONTHLY.');
			}

			if ( ! is_array($parts['BYYEARDAY']) ) {
				$parts['BYYEARDAY'] = explode(',',$parts['BYYEARDAY']);
			}

			$this->bysetpos = [];
			foreach ( $parts['BYYEARDAY'] as $value ) {
				if ( ! $value || $value < -366 || $value > 366 ) {
					throw new \InvalidArgumentException('Invalid BYSETPOS value: '.$value.' (valid values are 1 to 366 or -366 to -1)');
				}

				$this->byyearday[] = (int) $value;
			}
		}

		// BYWEEKNO
		if ( not_empty($parts['BYWEEKNO']) ) {
			if ( $this->freq !== 'YEARLY' ) {
				throw new \InvalidArgumentException('The BYWEEKNO rule part MUST NOT be used when the FREQ rule part is set to anything other than YEARLY.');
			}

			if ( ! is_array($parts['BYWEEKNO']) ) {
				$parts['BYWEEKNO'] = explode(',',$parts['BYWEEKNO']);
			}

			$this->byweekno = [];
			foreach ( $parts['BYWEEKNO'] as $value ) {
				if ( ! $value || $value < -53 || $value > 53 ) {
					throw new \InvalidArgumentException('Invalid BYWEEKNO value: '.$value.' (valid values are 1 to 53 or -53 to -1)');
				}
				$this->byweekno[] = (int) $value;
			}
		}

		// The BYMONTH rule part specifies a COMMA-separated list of months
		// of the year.  Valid values are 1 to 12.
		if ( not_empty($parts['BYMONTH']) ) {
			if ( ! is_array($parts['BYMONTH']) ) {
				$parts['BYMONTH'] = explode(',',$parts['BYMONTH']);
			}

			$this->bymonth = [];
			foreach ( $parts['BYMONTH'] as $value ) {
				if ( $value < 1 || $value > 12 ) {
					throw new \InvalidArgumentException('Invalid BYMONTH value: '.$value);
				}
				$this->bymonth[] = (int) $value;
			}
		}

		if ( not_empty($parts['BYSETPOS']) ) {
			if ( ! (not_empty($parts['BYWEEKNO']) || not_empty($parts['BYYEARDAY']) || not_empty($parts['BYMONTHDAY']) || not_empty($parts['BYDAY']) || not_empty($parts['BYMONTH'])) ) {
				throw new \InvalidArgumentException('The BYSETPOST rule part MUST only be used in conjunction with another BYxxx rule part.');
			}

			if ( ! is_array($parts['BYSETPOS']) ) {
				$parts['BYSETPOS'] = explode(',',$parts['BYSETPOS']);
			}

			$this->bysetpos = [];
			foreach ( $parts['BYSETPOS'] as $value ) {
				if ( ! $value || $value < -366 || $value > 366 ) {
					throw new \InvalidArgumentException('Invalid BYSETPOS value: '.$value.' (valid values are 1 to 366 or -366 to -1)');
				}

				$this->bysetpos[] = (int) $value;
			}
		}
	}

	public function getOccurrences()
	{
		if ( ! $this->count && ! $this->until ) {
			throw new \LogicException('Cannot get all occurences of an infinite recurrence rule.');
		}
		$res = [];
		foreach ( $this as $occurence ) {
			$res[] = $occurence;
		}
		return $res;
	}

	/**
	 * @return array
	 */
	public function getOccurrencesBetween($begin, $end)
	{
		$res = [];
		foreach ( $this as $occurence ) {
			if ( $occurence < $begin ) {
				continue;
			}
			if ( $occurence > $end ) {
				break;
			}
			$res[] = $occurence;
		}
		return $res;
	}

	/**
	 * @return bool
	 */
	public function occursOn($date)
	{

	}

// Iterator interface

	protected $position = 0;

	public function rewind()
	{
		$this->position = $this->iterate(true);
	}

	public function current()
	{
		return $this->position;
	}

	public function key()
	{
		// void
	}

	public function next()
	{
		$this->position = $this->iterate();
	}

	public function valid()
	{
		return $this->position !== null;
	}

// ArrayAccess interface

	public function offsetExists($offset)
	{
		
	}

	public function offsetGet($offset)
	{
		
	}

	public function offsetSet($offset, $value)
	{
		throw new LogicException('Setting a Date in a RRule is not supported');
	}

	public function offsetUnset($offset)
	{
		throw new LogicException('Unsetting a Date in a RRule is not supported');
	}

// private methods

	/**
	 * This method returns an array of days of the year (numbered from 0 to 365)
	 * of the current timeframe (year, month, week, day) containing the current date
	 */
	protected function getDaySet($year, $month, $day, array $masks)
	{
		switch ( $this->freq ) {
			case 'YEARLY':
				return range(0,$masks['year_len']-1);

			case 'MONTHLY':
				$start = $masks['month_to_last_day'][$month-1];
				$stop = $masks['month_to_last_day'][$month];
				return range($start, $stop - 1);

			case 'WEEKLY':
				// on first iteration, the first week will not be complete
				// we don't backtrack to the first day of the week, to avoid
				// crossing year boundary in reverse (i.e. if the week started
				// during the previous year), because that would generate
				// negative indexes (which would not work with the masks)
				$set = [];
				$i = (int) date('z', mktime(0,0,0,$month,$day,$year));
				$start = $i;
				for ( $j = 0; $j < 7; $j++ ) {
					$set[] = $i;
					$i += 1;
					if ( $masks['doy_to_weekday'][$i] == $this->wkst ) {
						break;
					}
				}
				return $set;

			case 'DAILY':
			case 'HOURLY':
			case 'MINUTELY':
			case 'SECONDLY':
				$n = (int) date('z', mktime(0,0,0,$month,$day,$year));
				return [$n];
		}
	}

	/**
	 * Some serious magic is happening here.
	 */
	protected function buildWeekdayMasks($year, $month, $day, array & $masks)
	{
		$masks['doy_to_weekday'] = array_slice(self::$WEEKDAY_MASK, date('N', mktime(0,0,0,1,1,$year))-1);
		$masks['doy_to_weekday_relative'] = array();

		if ( $this->byweekday_relative ) {
			$ranges = array();
			if ( $this->freq == 'YEARLY' ) {
				if ( $this->bymonth ) {
					foreach ( $this->bymonth as $bymonth ) {
						$ranges[] = [$masks['month_to_last_day'][$bymonth-1], $masks['month_to_last_day'][$bymonth]];
					}
				}
				else {
					$ranges = [[0,$masks['year_len']-1]];
				}
			}
			elseif ( $this->freq == 'MONTHLY') {
				$ranges[] = [$masks['month_to_last_day'][$month-1], $masks['month_to_last_day'][$month]];
			}

			if ( $ranges ) {
				foreach ( $ranges as $tmp ) {
					list($first, $last) = $tmp;
					foreach ( $this->byweekday_relative as $tmp ) {
						list($weekday, $nth) = $tmp;
						if ( $nth < 0 ) {
							$i = $last + ($nth + 1) * 7;
							$i = $i - pymod($masks['doy_to_weekday'][$i] - $weekday, 7);
						}
						else {
							$i = $first + ($nth - 1) * 7;
							$i = $i + (7 - $masks['doy_to_weekday'][$i] + $weekday) % 7;
						}
						if ( $i >= $first && $i <= $last ) {
							$masks['doy_to_weekday_relative'][$i] = 1;
						}
					}
				}
			}
		}
	}

	/**
	 * This is the main method, where all of the logic happens.
	 *
	 * This method is a generator that works for PHP 5.3/5.4 (using static variables)
	 */
	protected function iterate($reset = false)
	{
		// these are the static variables, i.e. the variables that persists
		// at every call of the method (to emulate a generator)
		static $year = null, $month = null, $day = null;
		static $current_set = null;
		static $total = 0;

		if ( $reset ) {
			$year = $month = $day = null;
			$current_set = null;
			$total = 0;
		}

		// stop once $total has reached COUNT
		if ( $this->count && $total >= $this->count ) {
// echo "\tTotal = $total ; COUNT = ".$this->count." stopping iteration\n";
			return null;
		}

		if ( $year == null ) {
			// difference from python here
			if ( $this->freq == 'WEEKLY' ) {
				// we align the start date to the WKST, so we can then
				// simply loop by adding +7 days
				$tmp = strtotime($this->dtstart);
				$tmp = strtotime('-'.pymod(date('N', $tmp) - $this->wkst,7).'days', $tmp);
				list($year,$month,$day) = explode('-',date('Y-m-d',$tmp));
			}
			else {
				list($year,$month,$day) = explode('-',$this->dtstart);
			}
		}

		while (true) {
			// 1. get an array of all days in the next interval (day, month, week, etc.)
			// we filter out from this array all days that do not match the BYXXX conditions
			// to speed things up, we use days of the year (day numbers) instead of date
			if ( $current_set === null ) {
				// rebuild the various masks and converters
				// these arrays will allow fast date operations
				// without relying on date() methods
				$masks = [];
				$masks['leap_year'] = is_leap_year($year);
				$masks['year_len'] = 365 + (int) $masks['leap_year'];
				$masks['next_year_len'] = 365 + is_leap_year($year + 1);
				if ( $masks['leap_year'] ) {
					$masks['doy_to_month'] = self::$MONTH_MASK_366;
					$masks['doy_to_monthday'] = self::$MONTHDAY_MASK_366;
					$masks['doy_to_monthday_negative'] = self::$NEGATIVE_MONTHDAY_MASK_366;
					$masks['month_to_last_day'] = self::$LAST_DAY_OF_MONTH_366;
				}
				else {
					$masks['doy_to_month'] = self::$MONTH_MASK;
					$masks['doy_to_monthday'] = self::$MONTHDAY_MASK;
					$masks['doy_to_monthday_negative'] = self::$NEGATIVE_MONTHDAY_MASK;
					$masks['month_to_last_day'] = self::$LAST_DAY_OF_MONTH;
				}
				$this->buildWeekdayMasks($year, $month, $day, $masks);

				$current_set = $this->getDaySet($year, $month, $day, $masks);
// echo"\tWorking with set=".json_encode($current_set)."\n";

// echo "\tdoy_to_weekday = ".json_encode($masks['doy_to_weekday'])."\n";
// echo "\tdoy_to_weekday_relative = ".json_encode($masks['doy_to_weekday_relative'])."\n";
// fgets(STDIN);

				$filtered_set = array();

				//  If multiple BYxxx rule parts are specified, then after evaluating the
				// specified FREQ and INTERVAL rule parts, the BYxxx rule parts are
				// applied to the current set of evaluated occurrences in the following
				// order: BYMONTH, BYWEEKNO, BYYEARDAY, BYMONTHDAY, BYDAY, BYHOUR,
				// BYMINUTE, BYSECOND and BYSETPOS; then COUNT and UNTIL are evaluated.

				// filter out (if needed)
				foreach ( $current_set as $day_of_year ) {
// echo "\t DAY OF YEAR ",$day_of_year,"\n";
// echo "\t month=",$masks['doy_to_month'][$day_of_year],"\n";
// echo "\t monthday=",$doy_to_monthday[$day_of_year],"\n";
// echo "\t -monthday=",$doy_to_monthday_negative[$day_of_year],"\n";
// echo "\t weekday=",$doy_to_weekday[$day_of_year],"\n";
// fgets(STDIN);
					if ( $this->bymonth && ! in_array($masks['doy_to_month'][$day_of_year], $this->bymonth) ) {
						continue;
					}
					if ( ($this->bymonthday || $this->bymonthday_negative)
						&& ! in_array($masks['doy_to_monthday'][$day_of_year], $this->bymonthday)
						&& ! in_array($masks['doy_to_monthday_negative'][$day_of_year], $this->bymonthday_negative) ) {
						continue;
					}
					if ( $this->byweekday && ! in_array($masks['doy_to_weekday'][$day_of_year], $this->byweekday) ) {
						continue;
					}
					if ( $this->byweekday_relative && ! isset($masks['doy_to_weekday_relative'][$day_of_year]) ) {
						continue;
					}

					if ( $this->byyearday ) {
						if ( $day_of_year < $masks['year_len'] ) {
							if ( ! in_array($day_of_year + 1, $this->byyearday) && ! in_array(- $masks['year_len'] + $day_of_year,$this->byyearday) ) {
								continue;
							}
						}
						else { // if ( ($day_of_year >= $masks['year_len']
							if ( ! in_array($day_of_year + 1 - $masks['year_len'], $this->byyearday) && ! in_array(- $masks['next_year_len'] + $day_of_year - $mask['year_len'], $this->byyearday) ) {
								continue;
							}
						}
					}

					$filtered_set[] = $day_of_year;
				}
// echo "\tFiltered set (before BYSETPOS)=".json_encode($filtered_set)."\n";

				$current_set = $filtered_set;

				// Note: if one day we decide to support time this will have to be
				// moved/rewritten to expand time *before* applying BYSETPOS
				if ( $this->bysetpos ) {
					$filtered_set = [];
					$n = sizeof($current_set);
					foreach ( $this->bysetpos as $pos ) {
						if ( $pos < 0 ) {
							$pos = $n + $pos;
						}
						else {
							$pos = $pos - 1;
						}
						if ( isset($current_set[$pos]) ) {
							$filtered_set[] = $current_set[$pos];
						}
					}
					$current_set = array_unique($filtered_set);
				}

// echo "\tFiltered set (after BYSETPOS)=".json_encode($filtered_set)."\n";
			}

			// 2. loop, generate a valid date, and return the result (fake "yield")
			// at the same time, we check the end condition and return null if
			// we need to stop
			while ( ($day_of_year = current($current_set)) !== false ) {
				$occurrence = date('Y-m-d', mktime(0, 0, 0, 1, ($day_of_year + 1), $year));

				// consider end conditions
				if ( $this->until && $occurrence > $this->until ) {
					// $this->length = $total (?)
					return null;
				}

				next($current_set);
				if ( $occurrence >= $this->dtstart ) { // ignore occurences before DTSTART
					$total += 1;
					return $occurrence; // yield
				}
			}

			// 3. we reset the loop to the next interval
			$current_set = null; // reset the loop
			switch ( $this->freq ) {
				case 'YEARLY':
					// we do not care about $month or $day not existing, they are not used in yearly frequency
					$year = $year + $this->interval;
					break;
				case 'MONTHLY':
					// we do not care about the day of the month not existing, it is not used in monthly frequency
					$month = $month + $this->interval;
					if ( $month > 12 ) {
						$delta = (int) ($month / 12);
						$mod = $month % 12;
						$month = $mod;
						$year = $year + $delta;
						if ( $month == 0 ) {
							$month = 12;
							$year = $year - 1;
						}
					}
					break;
				case 'WEEKLY':
					// here we take a little shortcut from the Python version, by using date/time methods
					list($year,$month,$day) = explode('-',date('Y-m-d',strtotime('+'.($this->interval*7).'day', mktime(0,0,0,$month,$day,$year))));
					break;
				case 'DAILY':
					// here we take a little shortcut from the Python version, by using date/time methods
					list($year,$month,$day) = explode('-',date('Y-m-d',strtotime('+'.$this->interval.'day', mktime(0,0,0,$month,$day,$year))));
					break;
				case 'HOURLY':
				case 'MINUTELY':
				case 'SECONDLY':
					throw new LogicException('Unimplemented');
			}
			// prevent overflow (especially on 32 bits system)
			if ( $year >= MAX_YEAR ) {
				return null;
			}
		}
	}

// constants
// Every mask is 7 days longer to handle cross-year weekly periods.

	public static $MONTH_MASK = array(
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
	);

	public static $MONTH_MASK_366 = array(
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
	);

	public static $MONTHDAY_MASK = array(
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
	);

	public static $MONTHDAY_MASK_366 = array(
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
	);

	public static $NEGATIVE_MONTHDAY_MASK = array(
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
	);

	public static $NEGATIVE_MONTHDAY_MASK_366 = array(
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
	);

	public static $WEEKDAY_MASK = array(
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
	);

	public static $LAST_DAY_OF_MONTH_366 = array(
		0, 31, 60, 91, 121, 152, 182, 213, 244, 274, 305, 335, 366
	);

	public static $LAST_DAY_OF_MONTH = array(
		0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334, 365
	);
}