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
 * Collection of static methods to parse RFC strings.
 *
 * This is used internally by RRule and RSet. The methods are public, BUT this
 * isn't part of the public API of this library. Therefore there is no guarantee
 * they will not break even for a minor version release.
 *
 * @internal
 */
class RfcParser
{
	static $tzdata = null;

	/**
	 * High level "line".
	 * Explode a line into property name, property parameters and property value
	 */
	static public function parseLine($line, array $default = array())
	{
		$line = trim($line);
		$property = array_merge(array(
			'name' => '',
			'params' => array(),
			'value' => null
		), $default);

		if (strpos($line,':') === false) {
			if (! $property['name']) {
				throw new \InvalidArgumentException('Failed to parse RFC line, missing property name followed by ":"');
			}
			$property['value'] = $line;
		}
		else {
			list($property['name'],$property['value']) = explode(':', $line);

			$tmp = explode(';',$property['name']);
			$property['name'] = $tmp[0];
			array_splice($tmp,0,1);
			foreach ($tmp as $pair) {
				if (strpos($pair,'=') === false) {
					throw new \InvalidArgumentException('Failed to parse RFC line, invalid property parameters: '.$pair);
				}
				list($key,$value) = explode('=',$pair);
				$property['params'][$key] = $value;
			}
		}

		return $property;
	}

	/**
	 * Parse both DTSTART and RRULE (and EXRULE).
	 *
	 * It's impossible to accuratly parse a RRULE in isolation (without the DTSTART)
	 * as some tests depends on DTSTART (notably the date format for UNTIL).
	 *
	 * @param string $string The RFC-like string
	 * @param mixed $dtstart The default dtstart to be used (if not in the string)
	 * @return array
	 */
	static public function parseRRule($string, $dtstart = null)
	{
		$string = trim($string);
		$parts = array();
		$dtstart_type = null;
		$rfc_date_regexp = '/\d{6}(T\d{6})?Z?/'; // regexp to check the date, a bit loose
		$nb_dtstart = 0;
		$nb_rrule = 0;
		$lines = explode("\n", $string);

		if ($dtstart) {
			$nb_dtstart = 1;
			if (is_string($dtstart)) {
				if (strlen($dtstart) == 10) {
					$dtstart_type = 'date';
				}
				else {
					$dtstart_type = 'localtime';
				}
			}
			else {
				$dtstart_type = 'tzid';
			}
			$parts['DTSTART'] = RRule::parseDate($dtstart);
		}

		foreach ($lines as $line) {
			$property = self::parseLine($line, array(
				'name' => sizeof($lines) > 1 ? null : 'RRULE'  // allow missing property name for single-line RRULE
			));

			switch (strtoupper($property['name'])) {
				case 'DTSTART':
					$nb_dtstart += 1;
					if ($nb_dtstart > 1) {
						throw new \InvalidArgumentException('Too many DTSTART properties (there can be only one)');
					}
					$tmp = null;
					$dtstart_type = 'date';
					if (! preg_match($rfc_date_regexp, $property['value'])) {
						throw new \InvalidArgumentException(
							'Invalid DTSTART property: date or date time format incorrect'
						);
					}
					if (isset($property['params']['TZID'])) {
						// TZID must only be specified if this is a date-time (see section 3.3.4 & 3.3.5 of RFC 5545)
						if (strpos($property['value'], 'T') === false) {
							throw new \InvalidArgumentException(
								'Invalid DTSTART property: TZID should not be specified if there is no time component'
							);
						}
						// The "TZID" property parameter MUST NOT be applied to DATE-TIME
						// properties whose time values are specified in UTC.
						if (strpos($property['value'], 'Z') !== false) {
							throw new \InvalidArgumentException(
								'Invalid DTSTART property: TZID must not be applied when time is specified in UTC'
							);
						}
						$dtstart_type = 'tzid';
						$tmp = self::parseTimeZone($property['params']['TZID']);
					}
					elseif (strpos($property['value'], 'T') !== false) {
						if (strpos($property['value'], 'Z') === false) {
							$dtstart_type = 'localtime'; // no timezone
						}
						else {
							$dtstart_type = 'utc';
						}
					}
					$parts['DTSTART'] = new \DateTime($property['value'], $tmp);
					break;
				case 'RRULE':
				case 'EXRULE':
					$nb_rrule += 1;
					if ($nb_rrule > 1) {
						throw new \InvalidArgumentException('Too many RRULE properties (there can be only one)');
					}
					foreach (explode(';',$property['value']) as $pair) {
						$pair = explode('=', $pair);
						if (! isset($pair[1]) || isset($pair[2])) {
							throw new \InvalidArgumentException("Failed to parse RFC string, malformed RRULE property: {$property['value']}");
						}
						list($key, $value) = $pair;
						if ($key === 'UNTIL') {
							if (! preg_match($rfc_date_regexp, $value)) {
								throw new \InvalidArgumentException(
									'Invalid UNTIL property: date or date time format incorrect'
								);
							}
							switch ($dtstart_type) {
								case 'date':
									if (strpos($value, 'T') !== false) {
										throw new \InvalidArgumentException(
											'Invalid UNTIL property: The value of the UNTIL rule part MUST be a date if DTSTART is a date.'
										);
									}
									break;
								case 'localtime':
									if (strpos($value, 'T') === false || strpos($value, 'Z') !== false) {
										throw new \InvalidArgumentException(
											'Invalid UNTIL property: if the "DTSTART" property is specified as a date with local time, then the UNTIL rule part MUST also be specified as a date with local time'
										);
									}
									break;
								case 'tzid':
								case 'utc':
									if (strpos($value, 'T') === false || strpos($value, 'Z') === false) {
										throw new \InvalidArgumentException(
											'Invalid UNTIL property: if the "DTSTART" property is specified as a date with UTC time or a date with local time and time zone reference, then the UNTIL rule part MUST be specified as a date with UTC time.'
										);
									}
									break;
							}

							$value = new \DateTime($value);
						}
						elseif ($key === 'DTSTART') {
							if (isset($parts['DTSTART'])) {
								throw new \InvalidArgumentException('DTSTART cannot be part of RRULE and has already been defined');
							}
							// this is an invalid rule, however we'll support it since the JS lib is broken
							// see https://github.com/rlanvin/php-rrule/issues/25
							trigger_error("This string is not compliant with the RFC (DTSTART cannot be part of RRULE). It is accepted as is for compability reasons only.", E_USER_NOTICE);
						}
						$parts[$key] = $value;
					}
					break;
				default:
					throw new \InvalidArgumentException('Failed to parse RFC string, unsupported property: '.$property['name']);
			}
		}

		return $parts;
	}

	/**
	 * Parse RDATE and return an array of DateTime
	 */
	static public function parseRDate($line)
	{
		$property = self::parseLine($line);
		if ($property['name'] !== 'RDATE') {
			throw new \InvalidArgumentException("Failed to parse RDATE line, this is a {$property['name']} property");
		}

		$period = false;
		$tz = null;
		foreach ($property['params'] as $name => $value) {
			switch (strtoupper($name)) {
				case 'TZID':
					$tz = self::parseTimeZone($value);
				break;
				case 'VALUE':
					switch ($value) {
						case 'DATE':
						case 'DATE-TIME':
						break;
						case 'PERIOD':
							$period = true;
						break;
						default:
							throw new \InvalidArgumentException("Unknown VALUE value for RDATE: $value, must be one of DATE-TIME, DATE or PERIOD");
					}
				break;
				default:
					throw new \InvalidArgumentException("Unknown property parameter: $name");
			}
		}

		$dates = array();

		foreach (explode(',',$property['value']) as $value) {
			if ($period) {
				if (strpos($value,'/') === false) {
					throw new \InvalidArgumentException('Invalid period in RDATE');
				}
				// period is unsupported!
				trigger_error('VALUE=PERIOD is not supported and ignored', E_USER_NOTICE);
			}
			else {
				if (strpos($value, 'Z')) {
					if ($tz !== null) {
						throw new \InvalidArgumentException('Invalid RDATE property: TZID must not be applied when time is specified in UTC');
					}
					$dates[] = new \DateTime($value);
				}
				else {
					$dates[] = new \DateTime($value, $tz);
				}
				// TODO should check that only dates are provided with VALUE=DATE, and so on.
			}
		}

		return $dates;
	}

	/**
	 * Parse EXDATE and return an array of DateTime
	 */
	static public function parseExDate($line)
	{
		$property = self::parseLine($line);
		if ($property['name'] !== 'EXDATE') {
			throw new \InvalidArgumentException("Failed to parse EXDATE line, this is a {$property['name']} property");
		}

		$tz = null;
		foreach ($property['params'] as $name => $value) {
			switch (strtoupper($name)) {
				case 'VALUE':
					// Ignore optional words
					break;
				case 'TZID':
					$tz = self::parseTimeZone($value);
				break;
				default:
					throw new \InvalidArgumentException("Unknown property parameter: $name");
			}
		}

		$dates = array();

		foreach (explode(',',$property['value']) as $value) {
			if (strpos($value, 'Z')) {
				if ($tz !== null) {
					throw new \InvalidArgumentException('Invalid EXDATE property: TZID must not be applied when time is specified in UTC');
				}
				$dates[] = new \DateTime($value);
			}
			else {
				$dates[] = new \DateTime($value, $tz);
			}
		}

		return $dates;
	}

	/**
	 * Create a new DateTimeZone object, converting non-standard timezone.
	 *
	 * @see https://github.com/rlanvin/php-rrule/issues/69
	 */
	static public function parseTimeZone($tzid)
	{
		if (self::$tzdata === null) {
			self::$tzdata = require __DIR__.'/tzdata/windows.php';
		}

		if (isset(self::$tzdata[$tzid])) {
			return new \DateTimeZone(self::$tzdata[$tzid]);
		}

		return new \DateTimeZone($tzid);
	}
}
