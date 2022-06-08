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
	use RRuleTrait;

	/**
	 * @var array List of RDATE (single dates)
	 */
	protected $rdates = array();

	/**
	 * @var array List of RRULE
	 */
	protected $rrules = array();

	/**
	 * @var array List of EXDATE (single dates to be excluded)
	 */
	protected $exdates = array();

	/**
	 * @var array List of EXRULES (single rules to be excluded)
	 */
	protected $exrules = array();

	// cache variable

	/**
	 * @var int|null Cache for the total number of occurrences
	 */
	protected $total = null;

	/**
	 * @var int|null Cache for the finite status of the RSet
	 */
	protected $infinite = null;

	/**
	 * @var array Cache for all the occurrences
	 */
	protected $cache = array();

	/**
	 * Constructor
	 *
	 * @param string $string a RFC compliant text block
	 */
	public function __construct($string = null, $default_dtstart = null)
	{
		if ($string && is_string($string)) {
			$string = trim($string);
			$rrules = array();
			$exrules = array();
			$rdates = array();
			$exdates = array();
			$dtstart = null;

			// parse
			$lines = explode("\n", $string);
			foreach ($lines as $line) {
				$line = trim($line);

				if (strpos($line,':') === false) {
					throw new \InvalidArgumentException('Failed to parse RFC string, line is not starting with a property name followed by ":"');
				}

				list($property_name,$property_value) = explode(':',$line);
				$tmp = explode(";",$property_name);
				$property_name = $tmp[0];
				switch (strtoupper($property_name)) {
					case 'DTSTART':
						if ($default_dtstart || $dtstart !== null) {
							throw new \InvalidArgumentException('Failed to parse RFC string, multiple DTSTART found');
						}
						$dtstart = $line;
					break;
					case 'RRULE':
						$rrules[] = $line;
					break;
					case 'EXRULE':
						$exrules[] = $line;
					break;
					case 'RDATE':
						$rdates = array_merge($rdates, RfcParser::parseRDate($line));
					break;
					case 'EXDATE':
						$exdates = array_merge($exdates, RfcParser::parseExDate($line));
					break;
					default:
						throw new \InvalidArgumentException("Failed to parse RFC, unknown property: $property_name");
				}
			}
			foreach ($rrules as $rrule) {
				if ($dtstart) {
					$rrule = $dtstart."\n".$rrule;
				}

				$this->addRRule(new RRule($rrule, $default_dtstart));
			}

			foreach ($exrules as $rrule) {
				if ($dtstart) {
					$rrule = $dtstart."\n".$rrule;
				}
				$this->addExRule(new RRule($rrule, $default_dtstart));
			}

			foreach ($rdates as $date) {
				$this->addDate($date);
			}

			foreach ($exdates as $date) {
				$this->addExDate($date);
			}
		}
	}

	/**
	 * Add a RRule (or another RSet)
	 *
	 * @param mixed $rrule an instance of RRuleInterface or something that can be transformed into a RRule (string or array)
	 * @return $this
	 */
	public function addRRule($rrule)
	{
		if (is_string($rrule) || is_array($rrule)) {
			$rrule = new RRule($rrule);
		}
		elseif (! $rrule instanceof RRuleInterface) {
			throw new \InvalidArgumentException('The rule must be a string, an array, or implement RRuleInterface');
		}

		// cloning because I want to iterate it without being disturbed
		$this->rrules[] = clone $rrule;

		$this->clearCache();

		return $this;
	}

	/**
	 * Return the RRULE(s) contained in this set
	 *
	 * @todo check if a deep copy is needed.
	 *
	 * @return array Array of RRule
	 */
	public function getRRules()
	{
		return $this->rrules;
	}

	/**
	 * Add a RRule with exclusion rules.
	 * In RFC 2445 but deprecated in RFC 5545
	 *
	 * @param mixed $rrule an instance of RRuleInterface or something that can be transformed into a RRule (string or array)
	 * @return $this
	 */
	public function addExRule($rrule)
	{
		if (is_string($rrule) || is_array($rrule)) {
			$rrule = new RRule($rrule);
		}
		elseif (! $rrule instanceof RRuleInterface) {
			throw new \InvalidArgumentException('The rule must be a string, an array or implement RRuleInterface');
		}

		// cloning because I want to iterate it without being disturbed
		$this->exrules[] = clone $rrule;

		$this->clearCache();

		return $this;
	}

	/**
	 * Return the EXRULE(s) contained in this set
	 *
	 * @todo check if a deep copy is needed.
	 *
	 * @return array Array of RRule
	 */
	public function getExRules()
	{
		return $this->exrules;
	}

	/**
	 * Add a RDATE (renamed Date for simplicy, since we don't support full RDATE syntax at the moment)
	 *
	 * @param mixed $date a valid date representation or a \DateTime object
	 * @return $this
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
	 * Remove an RDATE
	 *
	 * @param mixed $date a valid date representation or a \DateTime object
	 * @return $this
	 */
	public function removeDate($date)
	{
		try {
			$date_to_remove = RRule::parseDate($date);
			$index = array_search($date_to_remove, $this->rdates);

			if ($index !== false) {
				unset($this->rdates[$index]);
				$this->rdates = array_values($this->rdates);
			}
		} catch (\Exception $e) {
			throw new \InvalidArgumentException(
				'Failed to parse RDATE - it must be a valid date, timestamp or \DateTime object'
			);
		}

		$this->clearCache();

		return $this;
	}

	/**
	 * Remove all RDATEs
	 *
	 * @return $this
	 */
	public function clearDates()
	{
		$this->rdates = [];
		$this->clearCache();

		return $this;
	}

	/**
	 * Return the RDATE(s) contained in this set
	 *
	 * @todo check if a deep copy is needed.
	 *
	 * @return array Array of \DateTime
	 */
	public function getDates()
	{
		return $this->rdates;
	}

	/**
	 * Add a EXDATE
	 *
	 * @param mixed $date a valid date representation or a \DateTime object
	 * @return $this
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
	 * Remove an EXDATE
	 *
	 * @param mixed $date a valid date representation or a \DateTime object
	 * @return $this
	 */
	public function removeExDate($date)
	{
		try {
			$date_to_remove = RRule::parseDate($date);
			$index = array_search($date_to_remove, $this->exdates);

			if ($index !== false) {
				unset($this->exdates[$index]);
				$this->exdates = array_values($this->exdates);
			}
		} catch (\Exception $e) {
			throw new \InvalidArgumentException(
				'Failed to parse EXDATE - it must be a valid date, timestamp or \DateTime object'
			);
		}

		$this->clearCache();

		return $this;
	}

	/**
	 * Removes all EXDATEs
	 *
	 * @return $this
	 */
	public function clearExDates()
	{
		$this->exdates = [];
		$this->clearCache();

		return $this;
	}

	/**
	 * Return the EXDATE(s) contained in this set
	 *
	 * @todo check if a deep copy is needed.
	 *
	 * @return array Array of \DateTime
	 */
	public function getExDates()
	{
		return $this->exdates;
	}

	/**
	 * Clear the cache.
	 * Do NOT use while the class is iterating.
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

	/**
	 * Return true if the rrule has an end condition, false otherwise
	 *
	 * @return bool
	 */
	public function isFinite()
	{
		return ! $this->isInfinite();
	}

	/**
	 * Return true if the rrule has no end condition (infite)
	 *
	 * @return bool
	 */
	public function isInfinite()
	{
		if ($this->infinite === null) {
			$this->infinite = false;
			foreach ($this->rrules as $rrule) {
				if ($rrule->isInfinite()) {
					$this->infinite = true;
					break;
				}
			}
		}
		return $this->infinite;
	}

	/**
	 * Return all the occurrences in an array of \DateTime.
	 *
	 * @param int $limit Limit the resultset to n occurrences (0, null or false = everything)
	 * @return array An array of \DateTime objects
	 */
	public function getOccurrences($limit = null)
	{
		if (!$limit && $this->isInfinite()) {
			throw new \LogicException('Cannot get all occurrences of an infinite recurrence set.');
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
	 * Return true if $date is an occurrence.
	 *
	 * @param mixed $date
	 * @return bool
	 */
	public function occursAt($date)
	{
		$date = RRule::parseDate($date);

		if (in_array($date, $this->cache)) {
			// in the cache (whether cache is complete or not)
			return true;
		}
		elseif ($this->total !== null) {
			// cache complete and not in cache
			return false;
		}

		// test if it *should* occur (before exclusion)
		$occurs = false;
		foreach ($this->rdates as $rdate) {
			if ($rdate == $date) {
				$occurs = true;
				break;
			}
		}
		if (! $occurs) {
			foreach ($this->rrules as $rrule) {
				if ($rrule->occursAt($date)) {
					$occurs = true;
					break;
				}
			}
		}

		// if it should occur, test if it's excluded
		if ($occurs) {
			foreach ($this->exdates as $exdate) {
				if ($exdate == $date) {
					return false;
				}
			}
			foreach ($this->exrules as $exrule) {
				if ($exrule->occursAt($date)) {
					return false;
				}
			}
		}

		return $occurs;
	}

///////////////////////////////////////////////////////////////////////////////
// ArrayAccess interface

	/**
	 * @internal
	 * @return bool
	 */
	#[\ReturnTypeWillChange]
	public function offsetExists($offset)
	{
		return is_numeric($offset) && $offset >= 0 && ! is_float($offset) && $offset < count($this);
	}

	/**
	 * @internal
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
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
	 * @return void
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet($offset, $value)
	{
		throw new \LogicException('Setting a Date in a RSet is not supported (use addDate)');
	}

	/**
	 * @internal
	 * @return void
	 */
	#[\ReturnTypeWillChange]
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
	#[\ReturnTypeWillChange]
	public function count()
	{
		if ($this->isInfinite()) {
			throw new \LogicException('Cannot count an infinite recurrence set.');
		}

		if ($this->total === null) {
			foreach ($this as $occurrence) {}
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
	 *
	 * @param $reset (bool) Whether to restart the iteration, or keep going
	 * @return \DateTime|null
	 */
	#[\ReturnTypeWillChange]
	public function getIterator()
	{
		$previous_occurrence = null;
		$total = 0;

		foreach ($this->cache as $occurrence) {
			yield clone $occurrence; // since DateTime is not immutable, avoid any problem

			$total += 1;
		}

		if ($this->rlist_heap === null) {
			// rrules + rdate
			$this->rlist_heap = new \SplMinHeap();
			$this->rlist_iterator = new \MultipleIterator(\MultipleIterator::MIT_NEED_ANY);
			$this->rlist_iterator->attachIterator(new \ArrayIterator($this->rdates));
			foreach ($this->rrules as $rrule) {
				$this->rlist_iterator->attachIterator($rrule->getIterator());
			}
			$this->rlist_iterator->rewind();

			// exrules + exdate
			$this->exlist_heap = new \SplMinHeap();
			$this->exlist_iterator = new \MultipleIterator(\MultipleIterator::MIT_NEED_ANY);

			$this->exlist_iterator->attachIterator(new \ArrayIterator($this->exdates));
			foreach ($this->exrules as $rrule) {
				$this->exlist_iterator->attachIterator($rrule->getIterator());
			}
			$this->exlist_iterator->rewind();
		}

		while (true) {
			foreach ($this->rlist_iterator->current() as $date) {
				if ($date !== null) {
					$this->rlist_heap->insert($date);
				}
			}
			$this->rlist_iterator->next(); // advance the iterator for the next call

			if ($this->rlist_heap->isEmpty()) {
				break; // exit the loop to stop the iterator
			}

			$occurrence = $this->rlist_heap->top();
			$this->rlist_heap->extract(); // remove the occurrence from the heap

			if ($occurrence == $previous_occurrence) {
				continue; // skip, was already considered
			}

			// now we need to check against exlist
			// we need to iterate exlist as long as it contains dates lower than occurrence
			// (they will be discarded), and then check if the date is the same
			// as occurrence (in which case it is discarded)
			$excluded = false;
			while (true) {
				foreach ($this->exlist_iterator->current() as $date) {
					if ($date !== null) {
						$this->exlist_heap->insert($date);
					}
				}
				$this->exlist_iterator->next(); // advance the iterator for the next call

				if ($this->exlist_heap->isEmpty()) {
					break 1; // break this loop only
				}

				$exdate = $this->exlist_heap->top();
				if ($exdate < $occurrence) {
					$this->exlist_heap->extract();
					continue;
				}
				elseif ($exdate == $occurrence) {
					$excluded = true;
					break 1;
				}
				else {
					break 1; // exdate is > occurrence, so we'll keep it for later
				}
			}

			$previous_occurrence = $occurrence;

			if ($excluded) {
				continue;
			}

			$total += 1;
			$this->cache[] = clone $occurrence;
			yield clone $occurrence; // = yield
		}

		$this->total = $total; // save total for count cache
		return; // stop the iterator
	}
}