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
 * Implement the common methods of the RRuleInterface used by RRule and RSet
 *
 * @internal
 */
trait RRuleTrait
{
	/**
	 * Return all the occurrences in an array of \DateTime.
	 *
	 * @param int $limit Limit the resultset to n occurrences (0, null or false = everything)
	 * @return array An array of \DateTime objects
	 */
	public function getOccurrences($limit = null)
	{
		if (! $limit && $this->isInfinite()) {
			throw new \LogicException('Cannot get all occurrences of an infinite recurrence rule.');
		}
		if ($limit !== null && $limit !== false && $limit < 0) {
			throw new \InvalidArgumentException('$limit cannot be negative');
		}

		// cached version already computed
		$iterator = $this;
		if ($this->total !== null) {
			$iterator = $this->cache;
		}

		$res = array();
		$n = 0;
		foreach ($iterator as $occurrence) {
			$res[] = clone $occurrence; // we have to clone because DateTime is not immutable
			$n += 1;
			if ($limit && $n >= $limit) {
				break;
			}
		}
		return $res;
	}

	/**
	 * Return all the ocurrences after a date, before a date, or between two dates.
	 *
	 * @param mixed $begin Can be null to return all occurrences before $end
	 * @param mixed $end Can be null to return all occurrences after $begin
	 * @param int $limit Limit the resultset to n occurrences (0, null or false = everything)
	 * @return array An array of \DateTime objects
	 */
	public function getOccurrencesBetween($begin, $end, $limit = null)
	{
		if ($begin !== null) {
			$begin = self::parseDate($begin);
		}

		if ($end !== null) {
			$end = self::parseDate($end);
		}
		elseif (! $limit && $this->isInfinite()) {
			throw new \LogicException('Cannot get all occurrences of an infinite recurrence rule.');
		}

		if ($limit !== null && $limit !== false && $limit < 0) {
			throw new \InvalidArgumentException('$limit cannot be negative');
		}

		$iterator = $this;
		if ($this->total !== null) {
			$iterator = $this->cache;
		}

		$res = array();
		$n = 0;
		foreach ($iterator as $occurrence) {
			if ($begin !== null && $occurrence < $begin) {
				continue;
			}
			if ($end !== null && $occurrence > $end) {
				break;
			}
			$res[] = clone $occurrence;
			$n += 1;
			if ($limit && $n >= $limit) {
				break;
			}
		}
		return $res;
	}

	public function getOccurrencesAfter($date, $inclusive = false,  $limit = null)
	{
		if ($inclusive || ! $this->occursAt($date)) {
			return $this->getOccurrencesBetween($date, null, $limit);
		}

		$limit += 1;
		$occurrences = $this->getOccurrencesBetween($date, null, $limit);
		return array_slice($occurrences, 1);
	}

	public function getNthOccurrenceAfter($date, $index)
	{
		if ($index <= 0) {
			throw new \InvalidArgumentException("Index must be a positive integer");
		}

		$occurrences = $this->getOccurrencesAfter($date, false, $index);

		return isset($occurrences[$index-1]) ? $occurrences[$index-1] : null;
	}

	public function getOccurrencesBefore($date, $inclusive = false, $limit = null)
	{
		// we need to get everything
		$occurrences = $this->getOccurrencesBetween(null, $date);

		if (! $inclusive && $this->occursAt($date)) {
			array_pop($occurrences);
		}

		// the limit is counted from $date
		if ($limit) {
			$occurrences = array_slice($occurrences, -1 * $limit);
		}

		return $occurrences;
	}

	public function getNthOccurrenceBefore($date, $index)
	{
		if ($index <= 0) {
			throw new \InvalidArgumentException("Index must be a positive integer");
		}

		$occurrences = $this->getOccurrencesBefore($date, false, $index);

		if (sizeof($occurrences) < $index) {
			return null;
		}

		return $occurrences[0];
	}

	public function getNthOccurrenceFrom($date, $index)
	{
		if (! is_numeric($index)) {
			throw new \InvalidArgumentException('Malformed index (must be a numeric)');
		}

		if ($index == 0) {
			return $this->occursAt($date) ? self::parseDate($date) : null;
		}
		elseif ($index > 0) {
			return $this->getNthOccurrenceAfter($date, $index);
		}
		else {
			return $this->getNthOccurrenceBefore($date, -1*$index);
		}
	}
	/**
	 * Convert any date into a DateTime object.
	 *
	 * @param mixed $date
	 * @return \DateTime
	 *
	 * @throws \InvalidArgumentException on error
	 */
	static public function parseDate($date)
	{
		if (! $date instanceof \DateTime) {
			try {
				if (is_integer($date)) {
					$date = \DateTime::createFromFormat('U',$date);
					$date->setTimezone(new \DateTimeZone('UTC')); // default is +00:00 (see issue #15)
				}
                                elseif($date instanceof \DateTimeImmutable) {
                                    if(method_exists(\DateTime::class,'createFromImmutable')) { // php 7.3
                                        $date = \DateTime::createFromImmutable($date);
                                    } else {
                                        $date = new \DateTime($date->format(\DateTime::ATOM));
                                    }
                                }
				else {
					$date = new \DateTime($date);
				}
			} catch (\Exception $e) { // PHP 5.6
				throw new \InvalidArgumentException(
					"Failed to parse the date ({$e->getMessage()})"
				);
			} catch (\Throwable $e) { // PHP 7+
				throw new \InvalidArgumentException(
					"Failed to parse the date ({$e->getMessage()})"
				);
			}
		}
		else {
			$date = clone $date; // avoid reference problems
		}
		return $date;
	}
}
