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
 * Recurrence set
 */
class RSet implements RRuleInterface
{
	protected $rdates = array();
	protected $rrules = array();

	protected $exdates = array();
	protected $exrules = array();

	// cache variable
	protected $total = null;
	protected $infinite = null;
	protected $cache = array();

	public function __construct()
	{

	}

	/**
	 * Add a RRule (or another RSet)
	 */
	public function addRRule($rrule)
	{
		if ( is_string($rrule) || is_array($rrule) ) {
			$rrule = new RRule($rrule);
		}
		elseif ( ! $rrule instanceof RRuleInterface ) {
			throw new \InvalidArgumentException('The rule must be a string, an array, or implement RRuleInterface');
		}

		// cloning because I want to iterate it without being disturbed
		$this->rrules[] = clone $rrule;

		$this->clearCache();

		return $this;
	}

	/**
	 * Add a RRule with exclusion rules.
	 * In RFC 2445 but deprecated in RFC 5545
	 */
	public function addExRule($rrule)
	{
		if ( is_string($rrule) || is_array($rrule) ) {
			$rrule = new RRule($rrule);
		}
		elseif ( ! $rrule instanceof RRuleInterface ) {
			throw new \InvalidArgumentException('The rule must be a string, an array or implement RRuleInterface');
		}

		// cloning because I want to iterate it without being disturbed
		$this->exrules[] = clone $rrule;

		$this->clearCache();

		return $this;
	}

	/**
	 * Add a RDATE (renamed Date for simplicy, since we don't support full RDATE syntax at the moment)
	 */
	public function addDate($date)
	{
		try {
			$this->rdates[] = RRule::parseDate($date);
		} catch (\Exception $e) {
			throw new \InvalidArgumentException(
				'Failed to parse RDATE - it must be a valid date, timestamp or \DateTime object'
			);
		}

		$this->clearCache();

		return $this;
	}

	/**
	 * Add a EXDATE
	 */
	public function addExDate($date)
	{
		try {
			$this->exdates[] = RRule::parseDate($date);
		} catch (\Exception $e) {
			throw new \InvalidArgumentException(
				'Failed to parse EXDATE - it must be a valid date, timestamp or \DateTime object'
			);
		}

		$this->clearCache();

		return $this;
	}

	/**
	 * Clear the cache. Do NOT use while the class is iterating
	 * @return $this
	 */
	public function clearCache()
	{
		$this->total = null;
		$this->infinite = null;
		$this->cache = array();
		return $this;
	}

///////////////////////////////////////////////////////////////////////////////
// RRule interface

	public function isFinite()
	{
		return ! $this->isInfinite();
	}

	public function isInfinite()
	{
		if ( $this->infinite === null ) {
			$this->infinite = false;
			foreach ( $this->rrules as $rrule ) {
				if ( $rrule->isInfinite() ) {
					$this->infinite = true;
					break;
				}
			}
		}
		return $this->infinite;
	}

	public function getOccurrences()
	{
		if ( $this->isInfinite() ) {
			throw new \LogicException('Cannot get all occurrences of an infinite recurrence set.');
		}

		$res = array();
		foreach ( $this as $occurrence ) {
			$res[] = $occurrence;
		}
		return $res;
	}

	public function getOccurrencesBetween($begin, $end)
	{
		throw new \Exception(__METHOD__.' is unimplemented');
	}

	public function occursAt($date)
	{
		throw new \Exception(__METHOD__.' is unimplemented');
	}

///////////////////////////////////////////////////////////////////////////////
// Iterator interface

	protected $current = 0;
	protected $key = 0;

	public function rewind()
	{
		$this->current = $this->iterate(true);
		$this->key = 0;
	}

	public function current()
	{
		return $this->current;
	}

	public function key()
	{
		return $this->key;
	}

	public function next()
	{
		$this->current = $this->iterate();
		$this->key += 1;
	}

	public function valid()
	{
		return $this->current !== null;
	}

///////////////////////////////////////////////////////////////////////////////
// ArrayAccess interface

	public function offsetExists($offset)
	{
		return is_numeric($offset) && $offset >= 0 && $offset < count($this);
	}

	public function offsetGet($offset)
	{
		// TODO: Cache

		// if ( isset($this->cache[$offset]) ) {
		// 	// found in cache
		// 	return $this->cache[$offset];
		// }
		// elseif ( $this->total !== null ) {
		// 	// cache complete and not found in cache
		// 	return null;
		// }

		// not in cache and cache not complete, we have to loop to find it
		$i = 0;
		foreach ( $this as $occurrence ) {
			if ( $i == $offset ) {
				return $occurrence;
			}
			$i++;
			if ( $i > $offset ) {
				break;
			}
		}
		return null;
	}

	public function offsetSet($offset, $value)
	{
		throw new \LogicException('Setting a Date in a RSet is not supported (use addDate)');
	}

	public function offsetUnset($offset)
	{
		throw new \LogicException('Unsetting a Date in a RSet is not supported (use addDate)');
	}

///////////////////////////////////////////////////////////////////////////////
// Countable interface

	/**
	 * Returns the number of recurrences in this set. It will have go
	 * through the whole recurrence, if this hasn't been done before, which
	 * introduces a performance penality.
	 * @return int
	 */
	public function count()
	{
		if ( $this->isInfinite() ) {
			throw new \LogicException('Cannot count an infinite recurrence set.');
		}

		if ( $this->total === null ) {
			foreach ( $this as $occurrence ) {}
		}

		return $this->total;
	}

///////////////////////////////////////////////////////////////////////////////
// Private methods

	private $_rlist = null;
	private $_rlist_iterator = null;
	private $_exlist = null;
	private $_exlist_iterator = null;
	private $_previous_occurrence = null;
	private $_total = 0;

	/**
	 * This method will iterate over a bunch of different iterators (rrules and arrays),
	 * keeping the results *in order*, while never attempting to merge or sort
	 * anything in memory. It can combine both finite and infinite rrule.
	 *
	 * What we need to do it to build two heaps: rlist and exlist
	 * Each heap contains multiple iterators (either RRule or ArrayIterator)
	 * At each step of the loop, it calls all of the iterators to generate a new item,
	 * and stores them in the heap, that keeps them in order.
	 *
	 * This is made slightly more complicated because this method is a generator.
	 */
	protected function iterate($reset = false)
	{
		$rlist = & $this->_rlist;
		$rlist_iterator = & $this->_rlist_iterator;
		$exlist = & $this->_exlist;
		$exlist_iterator = & $this->_exlist_iterator;
		$previous_occurrence = & $this->_previous_occurrence;
		$total = & $this->_total;

		if ( $reset ) {
			$this->_rlist = $this->_rlist_iterator = null;
			$this->_exlist = $this->_exlist_iterator = null;
			$this->_previous_occurrence = null;
			$this->_total = 0;
		}

		if ( $rlist === null ) {
			// rrules + rdate
			$rlist = new \SplMinHeap();
			$rlist_iterator = new \MultipleIterator(\MultipleIterator::MIT_NEED_ANY);
			$rlist_iterator->attachIterator(new \ArrayIterator($this->rdates));
			foreach ( $this->rrules as $rrule ) {
				$rlist_iterator->attachIterator($rrule);
			}
			$rlist_iterator->rewind();

			// exrules + exdate
			$exlist = new \SplMinHeap();
			$exlist_iterator = new \MultipleIterator(\MultipleIterator::MIT_NEED_ANY);

			$exlist_iterator->attachIterator(new \ArrayIterator($this->exdates));
			foreach ( $this->exrules as $rrule ) {
				$exlist_iterator->attachIterator($rrule);
			}
			$exlist_iterator->rewind();
		}

		while ( true ) {
			foreach ( $rlist_iterator->current() as $date ) {
				if ( $date !== null ) {
					$rlist->insert($date);
				}
			}
			$rlist_iterator->next(); // advance the iterator for the next call

			if ( $rlist->isEmpty() ) {
				break; // exit the loop to stop the iterator
			}

			$occurrence = $rlist->top();
			$rlist->extract(); // remove the occurence from the heap

			if ( $occurrence == $previous_occurrence ) {
				continue; // skip, was already considered
			}

			// now we need to check against exlist
			// we need to iterate exlist as long as it contains dates lower than occurrence
			// (they will be discarded), and then check if the date is the same
			// as occurence (in which case it is discarded)
			$exclude = false;
			while ( true ) {
				foreach ( $exlist_iterator->current() as $date ) {
					if ( $date !== null ) {
						$exlist->insert($date);
					}
				}
				$exlist_iterator->next(); // advance the iterator for the next call

				if ( $exlist->isEmpty() ) {
					break 1; // break this loop only
				}

				$exdate = $exlist->top();
				if ( $exdate < $occurrence ) {
					$exlist->extract();
					continue;
				}
				elseif ( $exdate == $occurrence ) {
					$exclude = true;
					break 1;
				}
				else {
					break 1; // exdate is > occurrence, so we'll keep it for later
				}
			}

			$previous_occurrence = $occurrence;

			if ( $exclude ) {
				continue;
			}

			$total += 1;
			return $occurrence; // = yield
		}

		$this->total = $total; // save total for count cache
		return null; // stop the iterator
	}
}