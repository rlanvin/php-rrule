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
 * Common interface for RRule and RSet objects
 */
interface RRuleInterface extends \ArrayAccess, \Countable, \IteratorAggregate
{
	/**
	 * Return all the occurrences in an array of \DateTime.
	 *
	 * @param int $limit Limit the resultset to n occurrences (0, null or false = everything)
	 * @return array An array of \DateTime objects
	 */
	public function getOccurrences($limit = null);

	/**
	 * Return all the ocurrences after a date, before a date, or between two dates.
	 *
	 * @param mixed $begin Can be null to return all occurrences before $end
	 * @param mixed $end Can be null to return all occurrences after $begin
	 * @param int|null $limit Limit the resultset to n occurrences (0, null or false = everything)
	 * @return array An array of \DateTime objects
	 */
	public function getOccurrencesBetween($begin, $end, $limit = null);

	/**
	 * Return all the occurrences after a date.
	 * 
	 * @param mixed $date
	 * @param bool $inclusive Whether or not to include $date (if date is an occurrence)
	 * @param int|null $limit Limit the resultset to n occurrences (0, null or false = everything)
	 * @return array
	 */
	public function getOccurrencesAfter($date, $inclusive = false, $limit = null);

	/**
	 * Return the Nth occurrences after a date (non inclusive)
	 * 
	 * @param mixed $date
	 * @param int $index The index (starts at 1)
	 * @return DateTimeInterface|null
	 */
	public function getNthOccurrenceAfter($date, $index);

	/**
	 * Return all the occurrences before a date.
	 * 
	 * @param mixed $date
	 * @param bool $inclusive Whether or not to include $date (if date is an occurrence)
	 * @param int|null $limit Limit the resultset to n occurrences (0, null or false = everything)
	 * @return array
	 */
	public function getOccurrencesBefore($date, $inclusive = false, $limit = null);

	/**
	 * Return the Nth occurrences before a date (non inclusive)
	 * 
	 * @param mixed $date
	 * @param int $index The index (starts at 1)
	 * @return DateTimeInterface|null
	 */
	public function getNthOccurrenceBefore($date, $index);

	/**
	 * Return the Nth occurrences before or after a date.
	 * 
	 * @param mixed $date
	 * @param int $index 0 returns the date, positive integer returns index in the future, negative in the past
	 * @return DateTimeInterface|null
	 */
	public function getNthOccurrenceFrom($date, $index);

	/**
	 * Return true if $date is an occurrence.
	 *
	 * @param mixed $date
	 * @return bool
	 */
	public function occursAt($date);

	/**
	 * Return true if the rrule has an end condition, false otherwise
	 *
	 * @return bool
	 */
	public function isFinite();

	/**
	 * Return true if the rrule has no end condition (infite)
	 *
	 * @return bool
	 */
	public function isInfinite();
}