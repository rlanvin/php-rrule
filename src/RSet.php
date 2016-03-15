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
class RSet implements \Iterator, \ArrayAccess, \Countable
{
	protected $rdates = array();
	protected $rrules = array();

	protected $exdates = array();
	protected $exrules = array();

	public function __construct()
	{

	}

	public function addRRule($rrule)
	{
		if ( is_string($rrule) || is_array($rrule) ) {
			$rrule = new RRule($rrule);
		}
		elseif ( ! $rrule instanceof \Iterator ) {
			throw new \InvalidArgumentException('The rule must be a string, an array, an instance of RRule or an Iterator');
		}

		// cloning because I want to iterate it without being disturbed
		$this->rrules[] = clone $rrule;

		return $this;
	}

	/**
	 * In RFC 2445 but deprecated in RFC 5545
	 */
	public function addExRule($rrule)
	{
		if ( is_string($rrule) || is_array($rrule) ) {
			$rrule = new RRule($rrule);
		}
		elseif ( ! $rrule instanceof \Iterator ) {
			throw new \InvalidArgumentException('The rule must be a string, an array, an instance of RRule or an Iterator');
		}

		// cloning because I want to iterate it without being disturbed
		$this->exrules[] = clone $rrule;

		return $this;
	}

	public function addRDate($date)
	{
		try {
			$this->rdates[] = RRule::parseDate($date);
		} catch (\Exception $e) {
			throw new \InvalidArgumentException(
				'Failed to parse RDATE - it must be a valid date, timestamp or \DateTime object'
			);
		}

		return $this;
	}

	public function addExDate($date)
	{
		try {
			$this->exdates[] = RRule::parseDate($date);
		} catch (\Exception $e) {
			throw new \InvalidArgumentException(
				'Failed to parse EXDATE - it must be a valid date, timestamp or \DateTime object'
			);
		}

		return $this;
	}

	public function getOccurrences()
	{
		// TODO: need a wait to test the presence of infinite RRULE

		$res = array();
		foreach ( $this as $occurrence ) {
			$res[] = $occurrence;
		}
		return $res;
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
	throw new \Exception(__METHOD__.' is unimplemented');
		// return is_numeric($offset) && $offset >= 0 && $offset < count($this);
	}

	public function offsetGet($offset)
	{
		throw new \Exception(__METHOD__.' is unimplemented');
		// if ( isset($this->cache[$offset]) ) {
		// 	// found in cache
		// 	return $this->cache[$offset];
		// }
		// elseif ( $this->total !== null ) {
		// 	// cache complete and not found in cache
		// 	return null;
		// }

		// // not in cache and cache not complete, we have to loop to find it
		// $i = 0;
		// foreach ( $this as $occurrence ) {
		// 	if ( $i == $offset ) {
		// 		return $occurrence;
		// 	}
		// 	$i++;
		// 	if ( $i > $offset ) {
		// 		break;
		// 	}
		// }
		// return null;
	}

	public function offsetSet($offset, $value)
	{
		throw new \Exception(__METHOD__.' is unimplemented');
		// throw new \LogicException('Setting a Date in a RRule is not supported');
	}

	public function offsetUnset($offset)
	{
		throw new \Exception(__METHOD__.' is unimplemented');
		// throw new \LogicException('Unsetting a Date in a RRule is not supported');
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
		throw new \Exception(__METHOD__.' is unimplemented');
		// if ( ! $this->count && ! $this->until ) {
		// 	throw new \LogicException('Cannot count an infinite recurrence rule.');
		// }

		// if ( $this->total === null ) {
		// 	foreach ( $this as $occurrence ) {}
		// }

		// return $this->total;
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
		if ( $reset ) {
			$this->_rlist = $this->_rlist_iterator = null;
			$this->_exlist = $this->_exlist_iterator = null;
			$this->_previous_occurrence = null;
		}

		if ( $this->_rlist === null ) {
			// rrules + rdate
			$this->_rlist = new \SplMinHeap();
			$this->_rlist_iterator = new \MultipleIterator(\MultipleIterator::MIT_NEED_ANY);
			$this->_rlist_iterator->attachIterator(new \ArrayIterator($this->rdates));
			foreach ( $this->rrules as $rrule ) {
				$this->_rlist_iterator->attachIterator($rrule);
			}
			$this->_rlist_iterator->rewind();

			// exrules + exdate
			$this->_exlist = new \SplMinHeap();
			$this->_exlist_iterator = new \MultipleIterator(\MultipleIterator::MIT_NEED_ANY);

			$this->_exlist_iterator->attachIterator(new \ArrayIterator($this->exdates));
			foreach ( $this->exrules as $rrule ) {
				$this->_exlist_iterator->attachIterator($rrule);
			}
			$this->_exlist_iterator->rewind();
		}

		while ( true ) {
			foreach ( $this->_rlist_iterator->current() as $date ) {
				if ( $date !== null ) {
					$this->_rlist->insert($date);
				}
			}
			$this->_rlist_iterator->next(); // advance the iterator for the next call

			if ( $this->_rlist->isEmpty() ) {
				break; // exit the loop to stop the iterator
			}

			$occurrence = $this->_rlist->top();
			$this->_rlist->extract(); // remove the occurence from the heap

			if ( $occurrence == $this->_previous_occurrence ) {
				continue; // skip, was already considered
			}

			// now we need to check against exlist
			// we need to iterate exlist as long as it contains dates lower than occurrence
			// (they will be discarded), and then check if the date is the same
			// as occurence (in which case it is discarded)
			$exclude = false;
			while ( true ) {
				foreach ( $this->_exlist_iterator->current() as $date ) {
					if ( $date !== null ) {
						$this->_exlist->insert($date);
					}
				}
				$this->_exlist_iterator->next(); // advance the iterator for the next call

				if ( $this->_exlist->isEmpty() ) {
					break; // break this loop only
				}

				$exdate = $this->_exlist->top();
				if ( $exdate < $occurrence ) {
					$this->_exlist->extract();
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

			$this->_previous_occurrence = $occurrence;

			if ( $exclude ) {
				continue;
			}

			$this->_total += 1;
			return $occurrence; // = yield
		}

		$this->total = $this->_total; // save total for count cache
		return null; // stop the iterator
	}
}