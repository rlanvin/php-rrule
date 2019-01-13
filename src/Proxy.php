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
 * Simple proxy class to convert the results into something else.
 *
 * Designed as an example how to work with events (date+duration) instead of occurrences (date only).
 *
 * Example:
 * $rrule = new Proxy(
 *     new RRule("DTSTART:20170101\nRRULE:FREQ=DAILY;COUNT=2;INTERVAL=10"),
 *     function (\DateTimeInterface $occurrence) {
 *          return new Event($occurrence, 3600);
 *     }
 * );
 */
class Proxy implements \ArrayAccess, \Countable, \IteratorAggregate
{
	/**
	 * @var RRuleInterface
	 */
	protected $rrule;

	/**
	 * @var \Callable
	 */
	protected $factory;

	public function __construct(RRuleInterface $rrule, callable $factory)
	{
		$this->rrule = $rrule;
		$this->factory = $factory;
	}

	/**
	 * @return \Iterator
	 */
	public function getOccurrences($limit = null)
	{
		$occurrences = $this->rrule->getOccurrences($limit);
		return new ProxyIterator(new \ArrayIterator($occurrences), $this->factory);
	}

	/**
	 * @return \Iterator
	 */
	public function getOccurrencesBetween($begin, $end, $limit = null)
	{
		$occurrences = $this->rrule->getOccurrencesBetween($begin, $end, $limit);
		return new ProxyIterator(new \ArrayIterator($occurrences), $this->factory);
	}

	/**
	 * @return \Iterator
	 */
	public function getOccurrencesAfter($date, $inclusive = false, $limit = null)
	{
		$occurrences = $this->rrule->getNthOccurrenceAfter($date, $inclusive, $limit);
		return new ProxyIterator(new \ArrayIterator($occurrences), $this->factory);
	}

	public function getNthOccurrenceAfter($date, $index)
	{
		$occurrence = $this->rrule->getNthOccurrenceAfter($date, $index);
		return call_user_func_array($this->factory, [$occurrence]);
	}

	/**
	 * @return \Iterator
	 */
	public function getOccurrencesBefore($date, $inclusive = false, $limit = null)
	{
		$occurrences = $this->rrule->getOccurrencesBefore($date, $inclusive, $limit);
		return new ProxyIterator(new \ArrayIterator($occurrences), $this->factory);
	}

	public function getNthOccurrenceBefore($date, $index)
	{
		$occurrence = $this->rrule->getNthOccurrenceBefore($date, $index);
		return call_user_func_array($this->factory, [$occurrence]);
	}

	public function getNthOccurrenceFrom($date, $index)
	{
		$occurrence = $this->rrule->getNthOccurrenceFrom($date, $index);
		return call_user_func_array($this->factory, [$occurrence]);
	}

	public function isFinite()
	{
		return $this->rrule->isFinite();
	}

	public function isInfinite()
	{
		return $this->rrule->isInfinite();
	}

	public function occursAt($date)
	{
		return $this->rrule->occursAt();
	}

	public function offsetExists($offset)
	{
		return $this->rrule->offsetExists($offset);
	}

	public function offsetGet($offset)
	{
		$occurrence = $this->rrule->offsetGet($offset);
		return call_user_func_array($this->factory, [$occurrence]);
	}

	public function offsetSet($offset, $value)
	{
		return $this->rrule->offsetSet($offset, $value);
	}

	public function offsetUnset($offset)
	{
		return $this->rrule->offsetUnset($offset);
	}

	public function count()
	{
		return $this->rrule->count();
	}

	public function getIterator()
	{
		return new ProxyIterator($this->rrule, $this->factory);
	}

}