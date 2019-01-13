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
 * Helper class to work with event (a start time and a duration)
 */
class Event
{
	/**
	 * @var \DateTimeInterface
	 */
	protected $start_date;

	/**
	 * @var \DateTimeInterface
	 */
	protected $end_date;

	/**
	 * @var \DateInterval
	 */
	protected $duration;

	public function __construct(\DateTimeInterface $start_date, $duration)
	{
		$this->start_date = $start_date;
		$this->duration = self::parseDuration($duration);
	}

	/**
	 * @return bool
	 */
	public function occursAt($date)
	{
		$date = RRule::parseDate($date);

		return $this->start_date >= $date && $this->getEnd() <= $date;
	}

	/**
	 * @return \DateTimeInterface
	 */
	public function getStart()
	{
		return clone $this->start_date;
	}

	/**
	 * @return \DateInterval
	 */
	public function getDuration()
	{
		return $this->duration;
	}

	/**
	 * @return \DateTimeInterface
	 */
	public function getEnd()
	{
		if ( $this->end_date === null ) {
			$this->end_date = $this->start_date->add($this->duration);
		}

		return clone $this->end_date;
	}

	static public function parseDuration($duration)
	{
		if ( is_numeric($duration) ) {
			if ( $duration < 0 ) {
				throw new \InvalidArgumentException("Duration must be a positive integer");
			}

			// duration is a integer in seconds
			return \DateInterval::createFromDateString("$duration seconds");
		}
		
		if ( is_string($duration) ) {
			if ( $duration[0] == 'P' ) {
				// 'P3M'
				return new \DateInterval($duration);
			}
			else {
				// '3 months'
				return \DateInterval::createFromDateString($duration);
			}
		}

		throw new \InvalidArgumentException('Could not parse the duration');
	}
}