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
			sort($this->rdates);
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
			sort($this->exdates);
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

		$this->rlist_heap = null;
		$this->rlist_iterator = null;
		$this->exlist_heap = null;
		$this->exlist_iterator = null;

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

		// cached version already computed
		if ( $this->total !== null ) {
			$res = array();
			foreach ( $this->cache as $occurrence ) {
				$res[] = clone $occurrence; // we have to clone because DateTime is not immutable
			}
			return $res;
		}

		$res = array();
		foreach ( $this as $occurrence ) {
			$res[] = $occurrence;
		}
		return $res;
	}

	public function getOccurrencesBetween($begin, $end)
	{
		if ( $begin !== null ) {
			$begin = RRule::parseDate($begin);
		}

		if ( $end !== null ) {
			$end = RRule::parseDate($end);
		}
		elseif ( $this->isInfinite() ) {
			throw new \LogicException('Cannot get all occurrences of an infinite recurrence rule.');
		}

		$iterator = $this;
		if ( $this->total !== null ) {
			$iterator = $this->cache;
		}

		$res = array();
		foreach ( $iterator as $occurrence ) {
			if ( $begin !== null && $occurrence < $begin ) {
				continue;
			}
			if ( $end !== null && $occurrence > $end ) {
				break;
			}
			$res[] = clone $occurrence;
		}
		return $res;
	}

	public function occursAt($date)
	{
		$date = RRule::parseDate($date);

		if ( in_array($date, $this->cache) ) {
			// in the cache (whether cache is complete or not)
			return true;
		}
		elseif ( $this->total !== null ) {
			// cache complete and not in cache
			return false;
		}

		// test if it *should* occur (before exclusion)
		$occurs = false;
		foreach ( $this->rdates as $rdate ) {
			if ( $rdate == $date ) {
				$occurs = true;
				break;
			}
		}
		if ( ! $occurs ) {
			foreach ( $this->rrules as $rrule ) {
				if ( $rrule->occursAt($date) ) {
					$occurs = true;
					break;
				}
			}
		}

		// if it should occur, test if it's excluded
		if ( $occurs ) {
			foreach ( $this->exdates as $exdate ) {
				if ( $exdate == $date ) {
					return false;
				}
			}
			foreach ( $this->exrules as $exrule ) {
				if ( $exrule->occursAt($date) ) {
					return false;
				}
			}
		}

		return $occurs;
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
		if ( isset($this->cache[$offset]) ) {
			// found in cache
			return clone $this->cache[$offset];
		}
		elseif ( $this->total !== null ) {
			// cache complete and not found in cache
			return null;
		}

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

	// cache variables
	protected $rlist_heap = null;
	protected $rlist_iterator = null;
	protected $exlist_heap = null;
	protected $exlist_iterator = null;

	// local variables for iterate() (see comment in RRule about that)
	private $_previous_occurrence = null;
	private $_total = 0;
	private $_use_cache = 0;

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
		// $rlist = & $this->_rlist;
		// $rlist_iterator = & $this->_rlist_iterator;
		// $exlist = & $this->_exlist;
		// $exlist_iterator = & $this->_exlist_iterator;
		$previous_occurrence = & $this->_previous_occurrence;
		$total = & $this->_total;
		$use_cache = & $this->_use_cache;

		if ( $reset ) {
			// $this->_rlist = $this->_rlist_iterator = null;
			// $this->_exlist = $this->_exlist_iterator = null;
			$this->_previous_occurrence = null;
			$this->_total = 0;
			$this->_use_cache = true;
			reset($this->cache);
		}

		// go through the cache first
		if ( $use_cache ) {
			while ( ($occurrence = current($this->cache)) !== false ) {
			// 	// echo "Cache hit\n";
			// 	$dtstart = $occurrence;
				next($this->cache);
				$total += 1;
				return clone $occurrence;
			}
			reset($this->cache);
			// now set use_cache to false to skip the all thing on next iteration
			// and start filling the cache instead
			$use_cache = false;
			// if the cache as been used up completely and we now there is nothing else
			if ( $total === $this->total ) {
			// 	// echo "Cache used up, nothing else to compute\n";
				return null;
			}
			// // echo "Cache used up with occurrences remaining\n";
			// if ( $dtstart ) {
			// 	$dtstart = clone $dtstart; // since DateTime is not immutable, avoid any problem
			// 	// so we skip the last occurrence of the cache
			// 	if ( $this->freq === self::SECONDLY ) {
			// 		$dtstart->modify('+'.$this->interval.'second');
			// 	}
			// 	else {
			// 		$dtstart->modify('+1second');
			// 	}
			// }
		}

		if ( $this->rlist_heap === null ) {
			// rrules + rdate
			$this->rlist_heap = new \SplMinHeap();
			$this->rlist_iterator = new \MultipleIterator(\MultipleIterator::MIT_NEED_ANY);
			$this->rlist_iterator->attachIterator(new \ArrayIterator($this->rdates));
			foreach ( $this->rrules as $rrule ) {
				$this->rlist_iterator->attachIterator($rrule);
			}
			$this->rlist_iterator->rewind();

			// exrules + exdate
			$this->exlist_heap = new \SplMinHeap();
			$this->exlist_iterator = new \MultipleIterator(\MultipleIterator::MIT_NEED_ANY);

			$this->exlist_iterator->attachIterator(new \ArrayIterator($this->exdates));
			foreach ( $this->exrules as $rrule ) {
				$this->exlist_iterator->attachIterator($rrule);
			}
			$this->exlist_iterator->rewind();
		}

		while ( true ) {
			foreach ( $this->rlist_iterator->current() as $date ) {
				if ( $date !== null ) {
					$this->rlist_heap->insert($date);
				}
			}
			$this->rlist_iterator->next(); // advance the iterator for the next call

			if ( $this->rlist_heap->isEmpty() ) {
				break; // exit the loop to stop the iterator
			}

			$occurrence = $this->rlist_heap->top();
			$this->rlist_heap->extract(); // remove the occurence from the heap

			if ( $occurrence == $previous_occurrence ) {
				continue; // skip, was already considered
			}

			// now we need to check against exlist
			// we need to iterate exlist as long as it contains dates lower than occurrence
			// (they will be discarded), and then check if the date is the same
			// as occurence (in which case it is discarded)
			$excluded = false;
			while ( true ) {
				foreach ( $this->exlist_iterator->current() as $date ) {
					if ( $date !== null ) {
						$this->exlist_heap->insert($date);
					}
				}
				$this->exlist_iterator->next(); // advance the iterator for the next call

				if ( $this->exlist_heap->isEmpty() ) {
					break 1; // break this loop only
				}

				$exdate = $this->exlist_heap->top();
				if ( $exdate < $occurrence ) {
					$this->exlist_heap->extract();
					continue;
				}
				elseif ( $exdate == $occurrence ) {
					$excluded = true;
					break 1;
				}
				else {
					break 1; // exdate is > occurrence, so we'll keep it for later
				}
			}

			$previous_occurrence = $occurrence;

			if ( $excluded ) {
				continue;
			}

			$total += 1;
			$this->cache[] = $occurrence;
			return clone $occurrence; // = yield
		}

		$this->total = $total; // save total for count cache
		return null; // stop the iterator
	}
}